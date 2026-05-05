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
    );
}

function ai_crm_get_settings() {
    $settings = get_option('ai_crm_settings', array());
    return wp_parse_args(is_array($settings) ? $settings : array(), ai_crm_default_settings());
}

function ai_crm_get_setting($key) {
    $settings = ai_crm_get_settings();
    return $settings[$key] ?? ai_crm_default_settings()[$key] ?? null;
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

    $default_status = sanitize_key((string) ai_crm_get_setting('default_status'));
    return isset(ai_crm_statuses()[$default_status]) ? $default_status : 'new';
}
