<?php
/**
 * Uninstall cleanup for AI CRM System.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/database.php';

$settings = ai_crm_get_settings();
$constant_allows_cleanup = defined('AI_CRM_DELETE_DATA_ON_UNINSTALL') && AI_CRM_DELETE_DATA_ON_UNINSTALL;

if ($constant_allows_cleanup || !empty($settings['delete_data_on_uninstall'])) {
    ai_crm_drop_data();
}
