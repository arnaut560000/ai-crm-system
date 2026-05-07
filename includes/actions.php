<?php
if (!defined('ABSPATH')) exit;

add_action('admin_init', 'ai_crm_handle_actions');

function ai_crm_handle_actions() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['ai_crm_save_lead'])) {
        check_admin_referer('ai_crm_save_lead');
        $result = ai_crm_save_lead($_POST);
        $message = is_wp_error($result) ? 'lead_invalid' : 'saved';
        wp_safe_redirect(ai_crm_admin_url(array('message' => $message)));
        exit;
    }

    if (isset($_POST['ai_crm_update_lead'])) {
        check_admin_referer('ai_crm_update_lead');
        $lead_id = absint($_POST['lead_id'] ?? 0);
        $result = ai_crm_update_lead($lead_id, $_POST);
        $message = is_wp_error($result) || !$result ? 'lead_invalid' : 'updated';
        wp_safe_redirect(ai_crm_admin_url(array_filter(array('message' => $message, 'edit_lead' => $lead_id))));
        exit;
    }

    if (isset($_POST['ai_crm_update_status'])) {
        check_admin_referer('ai_crm_update_status');
        ai_crm_update_status(absint($_POST['lead_id'] ?? 0), sanitize_key($_POST['status'] ?? ''));
        wp_safe_redirect(ai_crm_admin_url(array('message' => 'updated')));
        exit;
    }

    if (isset($_POST['ai_crm_add_activity'])) {
        check_admin_referer('ai_crm_add_activity');
        $lead_id = absint($_POST['lead_id'] ?? 0);
        ai_crm_add_activity($lead_id, 'note', sanitize_textarea_field(wp_unslash($_POST['activity_note'] ?? '')));
        wp_safe_redirect(ai_crm_admin_url(array('message' => 'activity_added', 'edit_lead' => $lead_id)));
        exit;
    }

    if (isset($_POST['ai_crm_bulk_action'])) {
        check_admin_referer('ai_crm_bulk_action');
        ai_crm_handle_bulk_action();
        wp_safe_redirect(ai_crm_admin_url(array('message' => 'bulk_updated')));
        exit;
    }

    if (isset($_POST['ai_crm_import_csv'])) {
        check_admin_referer('ai_crm_import_csv');
        $result = ai_crm_import_csv();
        wp_safe_redirect(ai_crm_admin_url(array('message' => $result ? 'imported' : 'import_failed')));
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

    nocache_headers();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=ai-crm-leads-' . gmdate('Y-m-d') . '.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, array('Name', 'Email', 'Phone', 'Company', 'Status', 'Source', 'Deal Value', 'Next Follow-up', 'Notes', 'Created At', 'Updated At'));

    $statuses = ai_crm_statuses();
    foreach (ai_crm_get_export_leads() as $lead) {
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

function ai_crm_handle_bulk_action() {
    $lead_ids = array_filter(array_map('absint', (array) ($_POST['lead_ids'] ?? array())));
    $bulk_action = sanitize_key($_POST['bulk_action'] ?? '');

    if (!$lead_ids || $bulk_action === '') {
        return;
    }

    if ($bulk_action === 'delete') {
        foreach ($lead_ids as $lead_id) {
            ai_crm_delete_lead($lead_id);
        }
        return;
    }

    if ($bulk_action === 'status') {
        $status = sanitize_key($_POST['bulk_status'] ?? '');
        foreach ($lead_ids as $lead_id) {
            ai_crm_update_status($lead_id, $status);
        }
    }
}

function ai_crm_import_csv() {
    if (empty($_FILES['ai_crm_csv']['tmp_name'])) {
        return false;
    }

    $file = $_FILES['ai_crm_csv'];
    $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    if ($extension !== 'csv' || !is_uploaded_file($file['tmp_name'])) {
        return false;
    }

    $handle = fopen($file['tmp_name'], 'r');
    if (!$handle) {
        return false;
    }

    $headers = fgetcsv($handle);
    if (!$headers) {
        fclose($handle);
        return false;
    }

    $headers = array_map('ai_crm_normalize_csv_header', $headers);
    $imported = 0;

    while (($row = fgetcsv($handle)) !== false) {
        $data = array();
        foreach ($headers as $index => $header) {
            $data[$header] = $row[$index] ?? '';
        }

        if (empty($data['name']) || empty($data['email'])) {
            continue;
        }

        $result = ai_crm_save_lead(array(
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? '',
            'company' => $data['company'] ?? '',
            'status' => $data['status'] ?? ai_crm_get_setting('default_status'),
            'source' => $data['source'] ?? 'Other',
            'deal_value' => $data['deal_value'] ?? 0,
            'next_follow_up' => $data['next_follow_up'] ?? '',
            'notes' => $data['notes'] ?? '',
        ));
        if (!is_wp_error($result) && $result) {
            $imported++;
        }
    }

    fclose($handle);
    return $imported > 0;
}

function ai_crm_normalize_csv_header($header) {
    $header = strtolower(trim((string) $header));
    $header = str_replace(array(' ', '-'), '_', $header);
    $aliases = array(
        'value' => 'deal_value',
        'follow_up' => 'next_follow_up',
    );
    return $aliases[$header] ?? $header;
}
