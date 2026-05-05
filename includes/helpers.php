<?php

if (!defined('ABSPATH')) {
    exit;
}

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

function ai_crm_admin_url($args = array()) {
    return add_query_arg(array_merge(array('page' => 'ai-crm'), $args), admin_url('admin.php'));
}

function ai_crm_money($amount) {
    return '$' . number_format_i18n((float) $amount, 2);
}
