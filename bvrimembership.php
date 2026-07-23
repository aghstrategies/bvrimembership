<?php
declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
require_once 'bvrimembership.civix.php';
// phpcs:enable

use CRM_Bvrimembership_ExtensionUtil as E;

function bvrimembership_civicrm_preProcess($formName, &$form) {
   if ($formName == "CRM_Contribute_Form_Contribution_ThankYou" && isset($form->_id)) {

     // Set primary owner when contribution form id 2 is submitted
     if ($form->_id == 2 && isset($form->_contactID)) {
       $orgId = NULL;

       // If create find org id
       if (isset($form->_params['membershipID'])) {
        $memberships = \Civi\Api4\Membership::get(FALSE)
          ->addSelect('contact_id')
          ->addWhere('id', '=', $form->_params['membershipID'])
          ->addWhere('is_primary_member', '=', TRUE)
          ->setLimit(1)
          ->execute();
        foreach ($memberships as $membership) {
          $orgId = $membership['contact_id'];
        }
       }
       // IF update find org id
       elseif (isset($form->_params['onbehalfof_id'])) {
         $orgId = $form->_params['onbehalfof_id'];
       }

       // If we have an org id
       if ($orgId) {
         $relationships = \Civi\Api4\Relationship::get(FALSE)
           ->addSelect('Owner_Details.Is_Primary_', 'id')
           ->addWhere('contact_id_a', '=', $form->_contactID)
           ->addWhere('contact_id_b', '=', $orgId)
           // ->addClause('OR', ['AND', [['contact_id_a', '=', $form->_params['onbehalf_contact_id']], ['contact_id_b', '=', $form->_params['contactID']]]],
           // ['AND', [['contact_id_a', '=', $form->_params['contactID']], ['contact_id_b', '=', $form->_params['onbehalf_contact_id']]]])
           ->setLimit(1)
           ->execute();
         foreach ($relationships as $relationship) {
           if (isset($relationship['id'])) {
             $results = \Civi\Api4\Relationship::update(FALSE)
               ->addValue('Owner_Details.Is_Primary_', TRUE)
               ->addWhere('id', '=', $relationship['id'])
               ->execute();
           }
         }
       }

       // Show some help text after redirecting - Doesn't show up on formbuilder form at the moment
       // CRM_Core_Session::setStatus(E::ts("Please take a minute to update the Owners on file for your property."), 'Thank You for your Membership', 'no-popup');

       // For Testing
       // echo "<pre>";
       // print_r($form->_params);
       // echo "</pre>";
       // die();
     }

     $value = \Civi\Api4\ContributionPage::get(FALSE)
       ->addSelect('Additional_Details.Redirect_url')
       ->addWhere('id', '=', $form->_id)
       ->execute()
       ->first();
     if (!empty($value['Additional_Details.Redirect_url'])) {
       CRM_Utils_System::redirect($value['Additional_Details.Redirect_url']);
     }
   }
}

function bvrimembership_civicrm_custom( $op, $groupID, $entityID, &$params ) {
  if ( $op == 'create' || $op == 'edit' ) {
    foreach ($params as $key => $fieldInfo) {
      if ($fieldInfo['entity_table'] == 'civicrm_relationship' && $fieldInfo['custom_field_id'] == 14 && $fieldInfo['custom_group_id'] == 6 && $fieldInfo['value'] == 1) {

        // disable the old primary
        $primRelationships = \Civi\Api4\Relationship::get(FALSE)
          ->addWhere('id', '=', $fieldInfo['entity_id'])
          ->setLimit(1)
          ->execute();
        foreach ($primRelationships as $rel) {
          $results = \Civi\Api4\Relationship::update(FALSE)
            ->addValue('Owner_Details.Is_Primary_', FALSE)
            ->addWhere('contact_id_b', '=', $rel['contact_id_b'])
            ->addWhere('Owner_Details.Is_Primary_', '=', TRUE)
            ->addWhere('id', '!=', $fieldInfo['entity_id'])
            ->execute();
        }

        // Check if the new primary has a user
        $uFMatches = \Civi\Api4\UFMatch::get(FALSE)
          ->selectRowCount()
          ->addWhere('contact_id', '=', $rel['contact_id_a'])
          ->setLimit(1)
          ->execute();

        // Get Contact Details for email
        $contacts = \Civi\Api4\Contact::get(FALSE)
          ->addSelect('email_primary.email', 'first_name', 'last_name', 'display_name')
          ->addWhere('id', '=', $rel['contact_id_a'])
          ->setLimit(1)
          ->execute();
        foreach ($contacts as $contact) {
          // Toggle Language based on whether or not they have an account or need to create one
          if ($uFMatches->rowCount > 0) {
            $html = "<p>You have been designated the Primary Owner for your Bear Valley Property. To access your an account <a href='https://sandbox.bvri.aghstrategies.net/wp-login.php'>login</a> or <a href='https://sandbox.bvri.aghstrategies.net/wp-login.php?action=lostpassword'>reset your password</a> the email on file is {$contact['email_primary.email']}.</p>";
          }
          elseif ($uFMatches->rowCount == 0) {
            $html = "<p>You have been designated the Primary Owner for your Bear Valley Property. To create an account go to: https://sandbox.bvri.aghstrategies.net/civicrm/profile/create/?gid=22&reset=1 and enter:</p> <p>First Name: {$contact['first_name']}</p><p>Last Name: {$contact['last_name']}</p><p>Email: {$contact['email_primary.email']}</p>";
          }
          // Send email about creating an account if they don't have one already
          list($domainEmailName, $domainEmailAddress) = CRM_Core_BAO_Domain::getNameAndEmail();
          // $html = "<p>You have been designated the Primary Owner for your Bear Valley Property. To access your an account reset your password <a href='https://sandbox.bvri.aghstrategies.net/civicrm/profile/create/?gid=22&reset=1'>here</a> the email on file is {$contact['email_primary.email']}.</p>";
          // $text = "You have been designated the Primary Owner for your Bear Valley Property. To create an account go to: https://sandbox.bvri.aghstrategies.net/civicrm/profile/create/?gid=22&reset=1 and enter First Name: {$contact['first_name']}, Last Name: {$contact['last_name']}, Email: {$contact['email_primary.email']}";
          $mailParams = [
            // 'groupName' => 'Mailing Event ' . $component->component_type,
            'subject' => "You have been designated the Primary Owner for your Bear Valley Property",
            'from' => "\"{$domainEmailName}\" <{$domainEmailAddress}>",
            'toEmail' => $contact['email_primary.email'],
            'toName' => $contact['display_name'],
            'replyTo' => CRM_Core_BAO_Domain::getNoReplyEmailAddress(),
            'returnPath' => CRM_Core_BAO_Domain::getNoReplyEmailAddress(),
            'html' => $html,
            // 'text' => $text,
            'contactId' => $rel['contact_id_a'],
          ];
          CRM_Utils_Mail::send($mailParams);
        }
      }
    }
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function bvrimembership_civicrm_config(\CRM_Core_Config $config): void {
  _bvrimembership_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function bvrimembership_civicrm_install(): void {
  _bvrimembership_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function bvrimembership_civicrm_enable(): void {
  _bvrimembership_civix_civicrm_enable();
}
