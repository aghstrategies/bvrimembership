<?php
declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
require_once 'bvrimembership.civix.php';
// phpcs:enable

use CRM_Bvrimembership_ExtensionUtil as E;

function bvrimembership_civicrm_preProcess($formName, &$form) {
   if ($formName == "CRM_Contribute_Form_Contribution_ThankYou" && isset($form->_id)) {
     $value = \Civi\Api4\ContributionPage::get(FALSE)
       ->addSelect('Additional_Details.Redirect_url')
       ->addWhere('id', '=', $form->_id)
       ->execute()
       ->first();
     if (!empty($value['Additional_Details.Redirect_url'])) {
       CRM_Utils_System::redirect($value['Additional_Details.Redirect_url']);
     }
   }
   CRM_Core_Session::setStatus(E::ts("Please take a minute to update the Owners on file for your property."), 'Thank You for your Membership', 'no-popup');
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
