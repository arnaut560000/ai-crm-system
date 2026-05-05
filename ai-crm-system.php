<?php
/**
 * Plugin Name: AI CRM System
 * Description: A modern CRM workspace inside WordPress for tracking leads, pipeline status, follow-ups, and notes.
 * Version: 1.1.0
 * Author: Arnaut
 */

if (!defined('ABSPATH')) {
    exit;
}

define('AI_CRM_VERSION', '1.1.0');
define('AI_CRM_PLUGIN_FILE', __FILE__);
define('AI_CRM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AI_CRM_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once AI_CRM_PLUGIN_DIR . 'includes/helpers.php';
require_once AI_CRM_PLUGIN_DIR . 'includes/database.php';
require_once AI_CRM_PLUGIN_DIR . 'includes/actions.php';
require_once AI_CRM_PLUGIN_DIR . 'admin/dashboard.php';

register_activation_hook(__FILE__, 'ai_crm_install');

add_action('admin_menu', function () {
    add_menu_page(
        'AI CRM',
        'AI CRM',
        'manage_options',
        'ai-crm',
        'ai_crm_dashboard',
        'dashicons-groups',
        6
    );
});

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_ai-crm') {
        return;
    }

    wp_enqueue_style(
        'ai-crm-admin',
        AI_CRM_PLUGIN_URL . 'admin/admin.css',
        array(),
        AI_CRM_VERSION
    );
});
