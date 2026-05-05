<?php

if (!defined('ABSPATH')) {
    exit;
}

function ai_crm_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'ai_crm_leads';
}

function ai_crm_install() {
    global $wpdb;

    $table = ai_crm_table_name();
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(160) NOT NULL,
        email VARCHAR(190) NOT NULL,
        phone VARCHAR(80) DEFAULT '',
        company VARCHAR(160) DEFAULT '',
        status VARCHAR(40) NOT NULL DEFAULT 'new',
        source VARCHAR(80) DEFAULT 'Website',
        deal_value DECIMAL(12,2) DEFAULT 0,
        next_follow_up DATE DEFAULT NULL,
        notes TEXT DEFAULT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        PRIMARY KEY  (id),
        KEY status (status),
        KEY email (email),
        KEY next_follow_up (next_follow_up)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

function ai_crm_save_lead() {
    global $wpdb;

    $statuses = ai_crm_statuses();
    $status = isset($_POST['status']) ? sanitize_key($_POST['status']) : 'new';
    if (!isset($statuses[$status])) {
        $status = 'new';
    }

    $source = isset($_POST['source']) ? sanitize_text_field(wp_unslash($_POST['source'])) : 'Website';
    if (!in_array($source, ai_crm_sources(), true)) {
        $source = 'Other';
    }

    $next_follow_up = isset($_POST['next_follow_up']) ? sanitize_text_field(wp_unslash($_POST['next_follow_up'])) : '';
    $next_follow_up = preg_match('/^\d{4}-\d{2}-\d{2}$/', $next_follow_up) ? $next_follow_up : null;

    $wpdb->insert(
        ai_crm_table_name(),
        array(
            'name' => sanitize_text_field(wp_unslash($_POST['name'] ?? '')),
            'email' => sanitize_email(wp_unslash($_POST['email'] ?? '')),
            'phone' => sanitize_text_field(wp_unslash($_POST['phone'] ?? '')),
            'company' => sanitize_text_field(wp_unslash($_POST['company'] ?? '')),
            'status' => $status,
            'source' => $source,
            'deal_value' => (float) ($_POST['deal_value'] ?? 0),
            'next_follow_up' => $next_follow_up,
            'notes' => sanitize_textarea_field(wp_unslash($_POST['notes'] ?? '')),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ),
        array('%s', '%s', '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s')
    );
}

function ai_crm_update_status() {
    global $wpdb;

    $lead_id = absint($_POST['lead_id'] ?? 0);
    $status = isset($_POST['status']) ? sanitize_key($_POST['status']) : '';

    if (!$lead_id || !isset(ai_crm_statuses()[$status])) {
        return;
    }

    $wpdb->update(
        ai_crm_table_name(),
        array(
            'status' => $status,
            'updated_at' => current_time('mysql'),
        ),
        array('id' => $lead_id),
        array('%s', '%s'),
        array('%d')
    );
}

function ai_crm_delete_lead($lead_id) {
    global $wpdb;

    $wpdb->delete(ai_crm_table_name(), array('id' => absint($lead_id)), array('%d'));
}

function ai_crm_get_leads() {
    global $wpdb;

    $table = ai_crm_table_name();
    $where = array('1=1');
    $params = array();

    $search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
    $status = isset($_GET['status']) ? sanitize_key($_GET['status']) : '';

    if ($search !== '') {
        $like = '%' . $wpdb->esc_like($search) . '%';
        $where[] = '(name LIKE %s OR email LIKE %s OR company LIKE %s OR phone LIKE %s)';
        array_push($params, $like, $like, $like, $like);
    }

    if ($status !== '' && isset(ai_crm_statuses()[$status])) {
        $where[] = 'status = %s';
        $params[] = $status;
    }

    $sql = "SELECT * FROM $table WHERE " . implode(' AND ', $where) . ' ORDER BY updated_at DESC, id DESC LIMIT 100';

    if ($params) {
        $sql = $wpdb->prepare($sql, $params);
    }

    return $wpdb->get_results($sql);
}

function ai_crm_get_stats() {
    global $wpdb;

    $table = ai_crm_table_name();
    $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
    $open = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status NOT IN ('won', 'lost')");
    $won = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'won'");
    $pipeline = (float) $wpdb->get_var("SELECT COALESCE(SUM(deal_value), 0) FROM $table WHERE status NOT IN ('won', 'lost')");
    $followups = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE next_follow_up IS NOT NULL AND next_follow_up <= %s AND status NOT IN ('won', 'lost')", current_time('Y-m-d')));

    return compact('total', 'open', 'won', 'pipeline', 'followups');
}
