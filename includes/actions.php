<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_init', 'ai_crm_handle_actions');

function ai_crm_handle_actions() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['ai_crm_save_lead'])) {
        check_admin_referer('ai_crm_save_lead');
        ai_crm_save_lead();
        wp_safe_redirect(ai_crm_admin_url(array('message' => 'saved')));
        exit;
    }

    if (isset($_POST['ai_crm_update_status'])) {
        check_admin_referer('ai_crm_update_status');
        ai_crm_update_status();
        wp_safe_redirect(ai_crm_admin_url(array('message' => 'updated')));
        exit;
    }

    if (isset($_GET['ai_crm_delete'], $_GET['_wpnonce'])) {
        $lead_id = absint($_GET['ai_crm_delete']);
        $nonce = sanitize_text_field(wp_unslash($_GET['_wpnonce']));

        if ($lead_id && wp_verify_nonce($nonce, 'ai_crm_delete_' . $lead_id)) {
            ai_crm_delete_lead($lead_id);
            wp_safe_redirect(ai_crm_admin_url(array('message' => 'deleted')));
            exit;
        }
    }
}
