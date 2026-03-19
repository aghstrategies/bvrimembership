<?php
declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
require_once 'bvrimembership.civix.php';
// phpcs:enable

use CRM_Bvrimembership_ExtensionUtil as E;

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
