<?php
if (!defined('ABSPATH')) exit;

function ai_crm_statuses() {
    return array(
        'new' => array('label' => 'New', 'tone' => 'blue'),
        'contacted' => array('label' => 'Contacted', 'tone' => 'violet'),
        'qualified' => array('label' => 'Qualified', 'tone' => 'green'),
        'proposal' => array('label' => 'Proposal', 'tone' => 'amber'),
        'won' => array('label' => 'Won', 'tone' => 'emerald'),
        'lost' => array('label' => 'Lost', 'tone' => 'slate'),
    );
}

function ai_crm_sources() {
    return array('Website', 'Referral', 'Social Media', 'Cold Outreach', 'Event', 'Other');
}

function ai_crm_default_settings() {
    return array(
        'currency_symbol' => '$',
        'default_status' => 'new',
        'delete_data_on_uninstall' => 0,
        'records_per_page' => 25,
    );
}

function ai_crm_get_settings() {
    $settings = get_option('ai_crm_settings', array());
    return wp_parse_args(is_array($settings) ? $settings : array(), ai_crm_default_settings());
}

function ai_crm_get_setting($key) {
    $settings = ai_crm_get_settings();
    $defaults = ai_crm_default_settings();
    return $settings[$key] ?? $defaults[$key] ?? null;
}

function ai_crm_admin_url($args = array()) {
    return add_query_arg(array_merge(array('page' => 'ai-crm'), $args), admin_url('admin.php'));
}

function ai_crm_money($amount) {
    return ai_crm_get_setting('currency_symbol') . number_format_i18n((float) $amount, 2);
}

function ai_crm_clean_date($date) {
    $date = sanitize_text_field(wp_unslash($date));
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : null;
}

function ai_crm_validate_status($status) {
    $status = sanitize_key($status);
    if (isset(ai_crm_statuses()[$status])) {
        return $status;
    }

    $default = sanitize_key((string) ai_crm_get_setting('default_status'));
    return isset(ai_crm_statuses()[$default]) ? $default : 'new';
}

function ai_crm_records_per_page() {
    $per_page = absint(ai_crm_get_setting('records_per_page'));
    return min(100, max(10, $per_page));
}

function ai_crm_current_page() {
    return max(1, absint($_GET['paged'] ?? 1));
}

function ai_crm_get_filters() {
    return array(
        'search' => sanitize_text_field(wp_unslash($_GET['s'] ?? '')),
        'status' => sanitize_key($_GET['status'] ?? ''),
    );
}
