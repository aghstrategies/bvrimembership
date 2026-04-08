<?php
declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
require_once 'bvrimembership.civix.php';
// phpcs:enable

use CRM_Bvrimembership_ExtensionUtil as E;

function bvrimembership_civicrm_preProcess($formName, &$form) {
   if ($formName == "CRM_Contribute_Form_Contribution_ThankYou" && isset($form->_id)) {

     // Set primary owner when contribution form id 2 is submitted
     if ($form->_id == 2) {
       if (isset($form->_params['onbehalfof_id']) && isset($form->_contactID)) {
         $relationships = \Civi\Api4\Relationship::get(FALSE)
           ->addSelect('Owner_Details.Is_Primary_', 'id')
           ->addWhere('contact_id_a', '=', $form->_contactID)
           ->addWhere('contact_id_b', '=', $form->_params['onbehalfof_id'])
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
