<?php
declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
require_once 'bvrimembership.civix.php';
// phpcs:enable

use CRM_Bvrimembership_ExtensionUtil as E;


function bvrimembership_civicrm_buildForm($formName, &$form) {
  if ($form->getVar('_id') == 2 && $formName == 'CRM_Contribute_Form_Contribution_Main') {
    // Set up Owner Fields
    $fieldNames = [];
    $owners = [2, 3, 4];
    foreach ($owners as $okey => $ownerId) {
      $ownerFields = [
        "first_name_$ownerId" => "Owner $ownerId First Name",
        "last_name_$ownerId" => "Owner $ownerId Last Name",
        "email_$ownerId" => "Owner $ownerId Email",
      ];
      foreach ($ownerFields as $fieldName => $fieldLabel) {
        $fieldNames[] = $fieldName;
        $form->add('text', $fieldName, $fieldLabel);
      }
    }
    $form->assign("ownerInfo", $owners);
    $form->assign("ownerFields", $fieldNames);

    // $form->setDefaults(['match_grant_id' => array_key_first($grants)]);
    $templatePath = realpath(dirname(__FILE__)."/templates");
    CRM_Core_Region::instance('page-body')->add([
      'template' => "{$templatePath}/otherOwners.tpl"
    ]);

    // TODO Set Defaults if they exist
  }
}

function bvrimembership_findorg($params) {
  $orgId = NULL;
  $possibleIds = [];
  $fieldsToCheck = ['onbehalf_contact_id', 'contactID', 'organization_id'];
  foreach ($fieldsToCheck as $key => $fieldName) {
    if (isset($params[$fieldName])) {
      $possibleIds[] = $params[$fieldName];
    }
  }

  $contacts = \Civi\Api4\Contact::get(FALSE)
    ->addWhere('id', 'IN', $possibleIds)
    ->addWhere('contact_type', '=', 'Organization')
    ->setLimit(25)
    ->execute();
  foreach ($contacts as $contact) {
    $orgId = $contact['id'];
  }
  return $orgId;
}

function bvrimembership_civicrm_postProcess($formName, &$form) {
  if ($form->getVar('_id') == 2 && $formName == 'CRM_Contribute_Form_Contribution_Confirm') {
    // echo "<pre>";
    // print_R($form); echo "</pre>"; die();
    // TODO find the organization id which could be a variety of places depending on if someone is new etc.

    $owners = [];
    // TODO validate that 2 out of three fields are filled
    foreach ($form->_params as $fieldName => $fieldValue) {
      if (isset($form->_params[$fieldName]) && $fieldValue != "") {
        if (substr($fieldName, 0, 11) == "first_name_") {
          $owners[substr($fieldName, 11, 12)]['first_name'] = $fieldValue;
        }
        if (substr($fieldName, 0, 10) == "last_name_") {
          $owners[substr($fieldName, 10, 11)]['last_name'] = $fieldValue;
        }
        if (substr($fieldName, 0, 6) == "email_") {
          $owners[substr($fieldName, 6, 7)]['email_primary.email'] = $fieldValue;
        }
      }
    }
    foreach ($owners as $ownerNumber => &$ownerDetails) {
      if (count($ownerDetails) > 1) {
        $ownerDetails['contact_type'] = "Individual";
        $duplicates = civicrm_api4('Contact', 'getDuplicates', [
          'dedupeRule' => 'Individual.Unsupervised',
          'values' => $ownerDetails,
          'checkPermissions' => FALSE,
        ]);
        foreach ($duplicates as $duplicate) {
          if (isset($duplicate['id'])) {
            $ownerDetails['contact_id'] = $duplicate['id'];
          }
        }
        if (!isset($ownerDetails['contact_id'])) {
          $newContacts = civicrm_api4('Contact', 'create', [
            'values' => $ownerDetails,
            'checkPermissions' => FALSE,
          ]);
          foreach ($newContacts as $newContact) {
            $ownerDetails['contact_id'] = $newContact['id'];
          }
        }
        $orgId = bvrimembership_findorg($form->_params);

        if (isset($ownerDetails['contact_id']) && $orgId != NULL) {
          $relId = NULL;
          $relationships = \Civi\Api4\Relationship::get(FALSE)
            ->addWhere('contact_id_a', '=', $ownerDetails['contact_id'])
            ->addWhere('contact_id_b', '=', $orgId)
            ->addWhere('relationship_type_id', '=', 5)
            ->addWhere('is_active', '=', TRUE)
            ->addWhere('is_current', '=', TRUE)
            ->setLimit(25)
            ->execute();
          foreach ($relationships as $relationship) {
            $relId = $relationship['id'];
          }
          if ($relId == NULL) {
            $results = \Civi\Api4\Relationship::create(FALSE)
              ->addValue('contact_id_a', $ownerDetails['contact_id'])
              ->addValue('contact_id_b', $orgId)
              ->addValue('relationship_type_id', 5)
              ->addValue('is_active', TRUE)
              ->execute();
          }
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
