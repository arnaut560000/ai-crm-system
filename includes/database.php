<?php
if (!defined('ABSPATH')) exit;

function ai_crm_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'ai_crm_leads';
}

function ai_crm_activity_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'ai_crm_activities';
}

function ai_crm_install() {
    global $wpdb;

    $leads_table = ai_crm_table_name();
    $activity_table = ai_crm_activity_table_name();
    $charset = $wpdb->get_charset_collate();

    $leads_sql = "CREATE TABLE $leads_table (
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
    ) $charset;";

    $activity_sql = "CREATE TABLE $activity_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        lead_id BIGINT(20) UNSIGNED NOT NULL,
        activity_type VARCHAR(40) NOT NULL DEFAULT 'note',
        activity_note TEXT NOT NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY  (id),
        KEY lead_id (lead_id)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($leads_sql);
    dbDelta($activity_sql);

    if (!get_option('ai_crm_settings')) {
        add_option('ai_crm_settings', ai_crm_default_settings());
    } else {
        update_option('ai_crm_settings', ai_crm_get_settings());
    }
}

function ai_crm_prepare_lead_data($data) {
    $source = sanitize_text_field(wp_unslash($data['source'] ?? 'Website'));
    if (!in_array($source, ai_crm_sources(), true)) {
        $source = 'Other';
    }

    return array(
        'name' => sanitize_text_field(wp_unslash($data['name'] ?? '')),
        'email' => sanitize_email(wp_unslash($data['email'] ?? '')),
        'phone' => sanitize_text_field(wp_unslash($data['phone'] ?? '')),
        'company' => sanitize_text_field(wp_unslash($data['company'] ?? '')),
        'status' => ai_crm_validate_status($data['status'] ?? ''),
        'source' => $source,
        'deal_value' => (float) ($data['deal_value'] ?? 0),
        'next_follow_up' => isset($data['next_follow_up']) ? ai_crm_clean_date($data['next_follow_up']) : null,
        'notes' => sanitize_textarea_field(wp_unslash($data['notes'] ?? '')),
    );
}

function ai_crm_validate_lead_data($lead) {
    if (empty($lead['name'])) {
        return new WP_Error('ai_crm_missing_name', __('Lead name is required.', 'ai-crm-system'));
    }

    if (empty($lead['email']) || !is_email($lead['email'])) {
        return new WP_Error('ai_crm_invalid_email', __('A valid lead email is required.', 'ai-crm-system'));
    }

    return true;
}

function ai_crm_save_lead($data = null) {
    global $wpdb;

    $lead = ai_crm_prepare_lead_data($data ?? $_POST);
    $validation = ai_crm_validate_lead_data($lead);
    if (is_wp_error($validation)) {
        return $validation;
    }

    $lead['created_at'] = current_time('mysql');
    $lead['updated_at'] = current_time('mysql');

    $wpdb->insert(ai_crm_table_name(), $lead, array('%s', '%s', '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s'));
    $lead_id = (int) $wpdb->insert_id;

    if ($lead_id && $lead['notes'] !== '') {
        ai_crm_add_activity($lead_id, 'note', $lead['notes']);
    }

    return $lead_id;
}

function ai_crm_update_lead($lead_id, $data = null) {
    global $wpdb;

    $lead_id = absint($lead_id);
    if (!$lead_id) {
        return false;
    }

    $lead = ai_crm_prepare_lead_data($data ?? $_POST);
    $validation = ai_crm_validate_lead_data($lead);
    if (is_wp_error($validation)) {
        return $validation;
    }

    $lead['updated_at'] = current_time('mysql');

    $updated = $wpdb->update(
        ai_crm_table_name(),
        $lead,
        array('id' => $lead_id),
        array('%s', '%s', '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%s'),
        array('%d')
    );

    if ($updated !== false && $lead['notes'] !== '') {
        ai_crm_add_activity($lead_id, 'note', $lead['notes']);
    }

    return $updated !== false;
}

function ai_crm_update_status($lead_id, $status) {
    global $wpdb;

    $lead_id = absint($lead_id);
    $status = ai_crm_validate_status($status);
    if (!$lead_id) {
        return false;
    }

    $updated = $wpdb->update(
        ai_crm_table_name(),
        array('status' => $status, 'updated_at' => current_time('mysql')),
        array('id' => $lead_id),
        array('%s', '%s'),
        array('%d')
    );

    if ($updated !== false) {
        $statuses = ai_crm_statuses();
        ai_crm_add_activity($lead_id, 'status', 'Status changed to ' . $statuses[$status]['label'] . '.');
    }

    return $updated !== false;
}

function ai_crm_delete_lead($lead_id) {
    global $wpdb;

    $lead_id = absint($lead_id);
    $wpdb->delete(ai_crm_activity_table_name(), array('lead_id' => $lead_id), array('%d'));
    return $wpdb->delete(ai_crm_table_name(), array('id' => $lead_id), array('%d'));
}

function ai_crm_get_lead($lead_id) {
    global $wpdb;
    return $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . ai_crm_table_name() . ' WHERE id = %d', absint($lead_id)));
}

function ai_crm_get_leads() {
    global $wpdb;

    $query = ai_crm_build_leads_query();
    $per_page = ai_crm_records_per_page();
    $offset = (ai_crm_current_page() - 1) * $per_page;
    $sql = $query['sql'] . ' ORDER BY updated_at DESC, id DESC LIMIT %d OFFSET %d';
    $params = array_merge($query['params'], array($per_page, $offset));

    return $wpdb->get_results($wpdb->prepare($sql, $params));
}

function ai_crm_get_lead_count() {
    global $wpdb;

    $query = ai_crm_build_leads_query('COUNT(*)');
    return (int) ($query['params'] ? $wpdb->get_var($wpdb->prepare($query['sql'], $query['params'])) : $wpdb->get_var($query['sql']));
}

function ai_crm_get_export_leads() {
    global $wpdb;

    $query = ai_crm_build_leads_query();
    $sql = $query['sql'] . ' ORDER BY updated_at DESC, id DESC';

    return $query['params'] ? $wpdb->get_results($wpdb->prepare($sql, $query['params'])) : $wpdb->get_results($sql);
}

function ai_crm_build_leads_query($select = '*') {
    global $wpdb;

    $table = ai_crm_table_name();
    $where = array('1=1');
    $params = array();
    $filters = ai_crm_get_filters();
    $search = $filters['search'];
    $status = $filters['status'];

    if ($search !== '') {
        $like = '%' . $wpdb->esc_like($search) . '%';
        $where[] = '(name LIKE %s OR email LIKE %s OR company LIKE %s OR phone LIKE %s)';
        array_push($params, $like, $like, $like, $like);
    }

    if ($status !== '' && isset(ai_crm_statuses()[$status])) {
        $where[] = 'status = %s';
        $params[] = $status;
    }

    return array(
        'sql' => "SELECT $select FROM $table WHERE " . implode(' AND ', $where),
        'params' => $params,
    );
}

function ai_crm_add_activity($lead_id, $type, $note) {
    global $wpdb;

    $lead_id = absint($lead_id);
    $note = sanitize_textarea_field($note);
    if (!$lead_id || $note === '') {
        return false;
    }

    return $wpdb->insert(
        ai_crm_activity_table_name(),
        array(
            'lead_id' => $lead_id,
            'activity_type' => sanitize_key($type),
            'activity_note' => $note,
            'created_at' => current_time('mysql'),
        ),
        array('%d', '%s', '%s', '%s')
    );
}

function ai_crm_get_activities($lead_id = 0) {
    global $wpdb;

    $table = ai_crm_activity_table_name();
    if ($lead_id) {
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE lead_id = %d ORDER BY created_at DESC, id DESC LIMIT 25", absint($lead_id)));
    }

    return $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC, id DESC LIMIT 25");
}

function ai_crm_get_stats() {
    global $wpdb;

    $table = ai_crm_table_name();
    return array(
        'total' => (int) $wpdb->get_var("SELECT COUNT(*) FROM $table"),
        'open' => (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status NOT IN ('won', 'lost')"),
        'won' => (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'won'"),
        'pipeline' => (float) $wpdb->get_var("SELECT COALESCE(SUM(deal_value), 0) FROM $table WHERE status NOT IN ('won', 'lost')"),
        'followups' => (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE next_follow_up IS NOT NULL AND next_follow_up <= %s AND status NOT IN ('won', 'lost')", current_time('Y-m-d'))),
    );
}

function ai_crm_get_status_analytics() {
    global $wpdb;

    $table = ai_crm_table_name();
    $rows = $wpdb->get_results("SELECT status, COUNT(*) AS lead_count, COALESCE(SUM(deal_value), 0) AS total_value FROM $table GROUP BY status");
    $statuses = ai_crm_statuses();
    $analytics = array();

    foreach ($statuses as $key => $status) {
        $analytics[$key] = array(
            'key' => $key,
            'label' => $status['label'],
            'tone' => $status['tone'],
            'count' => 0,
            'value' => 0.0,
        );
    }

    foreach ($rows as $row) {
        $key = sanitize_key($row->status);
        if (!isset($analytics[$key])) {
            continue;
        }

        $analytics[$key]['count'] = (int) $row->lead_count;
        $analytics[$key]['value'] = (float) $row->total_value;
    }

    return $analytics;
}

function ai_crm_get_followup_focus($limit = 5) {
    global $wpdb;

    $limit = min(10, max(1, absint($limit)));
    $table = ai_crm_table_name();

    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT id, name, email, company, status, next_follow_up
            FROM $table
            WHERE next_follow_up IS NOT NULL
                AND status NOT IN ('won', 'lost')
            ORDER BY next_follow_up ASC, updated_at DESC
            LIMIT %d",
            $limit
        )
    );
}

function ai_crm_drop_data() {
    global $wpdb;

    $wpdb->query('DROP TABLE IF EXISTS ' . ai_crm_activity_table_name());
    $wpdb->query('DROP TABLE IF EXISTS ' . ai_crm_table_name());
    delete_option('ai_crm_settings');
}
