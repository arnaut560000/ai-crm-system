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

    if (isset($_POST['ai_crm_update_lead'])) {
        check_admin_referer('ai_crm_update_lead');
        $lead_id = absint($_POST['lead_id'] ?? 0);
        ai_crm_update_lead($lead_id);
        wp_safe_redirect(ai_crm_admin_url(array('message' => 'updated', 'edit_lead' => $lead_id)));
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

    if (isset($_GET['ai_crm_export'], $_GET['_wpnonce'])) {
        $nonce = sanitize_text_field(wp_unslash($_GET['_wpnonce']));
        if (wp_verify_nonce($nonce, 'ai_crm_export')) {
            ai_crm_export_csv();
            exit;
        }
    }
}

function ai_crm_export_csv() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to export CRM data.', 'ai-crm-system'));
    }

    $leads = ai_crm_get_leads();

    nocache_headers();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=ai-crm-leads-' . gmdate('Y-m-d') . '.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, array('Name', 'Email', 'Phone', 'Company', 'Status', 'Source', 'Deal Value', 'Next Follow-up', 'Notes', 'Created At', 'Updated At'));

    foreach ($leads as $lead) {
        $statuses = ai_crm_statuses();
        fputcsv($output, array(
            $lead->name,
            $lead->email,
            $lead->phone,
            $lead->company,
            $statuses[$lead->status]['label'] ?? $lead->status,
            $lead->source,
            $lead->deal_value,
            $lead->next_follow_up,
            $lead->notes,
            $lead->created_at,
            $lead->updated_at,
        ));
    }

    fclose($output);
}
