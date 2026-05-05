<?php
if (!defined('ABSPATH')) exit;

function ai_crm_dashboard() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to access AI CRM.', 'ai-crm-system'));
    }

    ai_crm_install();

    $leads = ai_crm_get_leads();
    $lead_count = ai_crm_get_lead_count();
    $stats = ai_crm_get_stats();
    $statuses = ai_crm_statuses();
    $filters = ai_crm_get_filters();
    $search = $filters['search'];
    $current_status = $filters['status'];
    $edit_lead_id = absint($_GET['edit_lead'] ?? 0);
    $edit_lead = $edit_lead_id ? ai_crm_get_lead($edit_lead_id) : null;
    $activities = $edit_lead ? ai_crm_get_activities($edit_lead->id) : ai_crm_get_activities();
    $export_url = wp_nonce_url(ai_crm_admin_url(array('ai_crm_export' => 1, 's' => $search, 'status' => $current_status)), 'ai_crm_export');
    ?>
    <div class="wrap ai-crm-wrap">
        <?php ai_crm_render_notice(); ?>

        <section class="ai-crm-hero">
            <div>
                <p class="ai-crm-kicker"><?php esc_html_e('WordPress CRM Workspace', 'ai-crm-system'); ?></p>
                <h1><?php esc_html_e('AI CRM Dashboard', 'ai-crm-system'); ?></h1>
                <p><?php esc_html_e('Track leads, follow-ups, notes, and pipeline status in one clean admin screen.', 'ai-crm-system'); ?></p>
            </div>
            <div class="ai-crm-hero-actions">
                <a class="ai-crm-primary-link" href="#ai-crm-lead-form"><?php echo $edit_lead ? esc_html__('Edit Lead', 'ai-crm-system') : esc_html__('Add Lead', 'ai-crm-system'); ?></a>
                <a class="ai-crm-secondary-link" href="<?php echo esc_url($export_url); ?>"><?php esc_html_e('Export CSV', 'ai-crm-system'); ?></a>
            </div>
        </section>

        <section class="ai-crm-metrics" aria-label="<?php esc_attr_e('CRM summary', 'ai-crm-system'); ?>">
            <?php ai_crm_metric('Total Leads', number_format_i18n($stats['total']), 'All contacts'); ?>
            <?php ai_crm_metric('Open Pipeline', number_format_i18n($stats['open']), 'Active leads'); ?>
            <?php ai_crm_metric('Won Deals', number_format_i18n($stats['won']), 'Closed wins'); ?>
            <?php ai_crm_metric('Pipeline Value', ai_crm_money($stats['pipeline']), 'Open value'); ?>
            <?php ai_crm_metric('Due Follow-ups', number_format_i18n($stats['followups']), 'Needs action'); ?>
        </section>

        <div class="ai-crm-grid">
            <section class="ai-crm-side-stack">
            <details class="ai-crm-panel ai-crm-lead-details" id="ai-crm-lead-form" <?php echo $edit_lead ? 'open' : ''; ?>>
                <summary>
                    <span><?php echo $edit_lead ? esc_html__('Edit Lead', 'ai-crm-system') : esc_html__('Add New Lead', 'ai-crm-system'); ?></span>
                    <small><?php echo $edit_lead ? esc_html__('Update selected record', 'ai-crm-system') : esc_html__('Open form', 'ai-crm-system'); ?></small>
                </summary>

                <form method="post" class="ai-crm-form">
                    <?php if ($edit_lead) : ?>
                        <?php wp_nonce_field('ai_crm_update_lead'); ?>
                        <input type="hidden" name="ai_crm_update_lead" value="1">
                        <input type="hidden" name="lead_id" value="<?php echo esc_attr($edit_lead->id); ?>">
                    <?php else : ?>
                        <?php wp_nonce_field('ai_crm_save_lead'); ?>
                        <input type="hidden" name="ai_crm_save_lead" value="1">
                    <?php endif; ?>

                    <?php ai_crm_text_field('name', 'Name', $edit_lead->name ?? '', 'Jane Cooper', true); ?>
                    <?php ai_crm_text_field('email', 'Email', $edit_lead->email ?? '', 'jane@example.com', true, 'email'); ?>
                    <?php ai_crm_text_field('phone', 'Phone', $edit_lead->phone ?? '', '+1 555 123 4567'); ?>
                    <?php ai_crm_text_field('company', 'Company', $edit_lead->company ?? '', 'Acme Studio'); ?>

                    <label>
                        <span><?php esc_html_e('Status', 'ai-crm-system'); ?></span>
                        <select name="status">
                            <?php foreach ($statuses as $key => $status) : ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php selected($edit_lead->status ?? ai_crm_get_setting('default_status'), $key); ?>>
                                    <?php echo esc_html($status['label']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>
                        <span><?php esc_html_e('Source', 'ai-crm-system'); ?></span>
                        <select name="source">
                            <?php foreach (ai_crm_sources() as $source) : ?>
                                <option value="<?php echo esc_attr($source); ?>" <?php selected($edit_lead->source ?? 'Website', $source); ?>>
                                    <?php echo esc_html($source); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>
                        <span><?php esc_html_e('Deal Value', 'ai-crm-system'); ?></span>
                        <input type="number" name="deal_value" value="<?php echo esc_attr($edit_lead->deal_value ?? ''); ?>" min="0" step="0.01" placeholder="2500">
                    </label>

                    <label>
                        <span><?php esc_html_e('Next Follow-up', 'ai-crm-system'); ?></span>
                        <input type="date" name="next_follow_up" value="<?php echo esc_attr($edit_lead->next_follow_up ?? ''); ?>">
                    </label>

                    <label class="ai-crm-full">
                        <span><?php esc_html_e('Notes', 'ai-crm-system'); ?></span>
                        <textarea name="notes" rows="4" placeholder="<?php esc_attr_e('Conversation notes or next steps', 'ai-crm-system'); ?>"><?php echo esc_textarea($edit_lead->notes ?? ''); ?></textarea>
                    </label>

                    <div class="ai-crm-form-actions">
                        <button type="submit" class="button button-primary"><?php echo $edit_lead ? esc_html__('Update Lead', 'ai-crm-system') : esc_html__('Save Lead', 'ai-crm-system'); ?></button>
                        <?php if ($edit_lead) : ?>
                            <a class="button" href="<?php echo esc_url(ai_crm_admin_url()); ?>"><?php esc_html_e('Cancel', 'ai-crm-system'); ?></a>
                        <?php endif; ?>
                    </div>
                </form>

                <?php ai_crm_render_activity($activities, $edit_lead); ?>
            </details>

            <?php ai_crm_render_import_panel(); ?>
            </section>

            <section class="ai-crm-panel ai-crm-list-panel">
                <div class="ai-crm-panel-heading ai-crm-list-heading">
                    <div>
                        <h2><?php esc_html_e('Leads', 'ai-crm-system'); ?></h2>
                        <span><?php echo esc_html($lead_count); ?> <?php esc_html_e('matching records', 'ai-crm-system'); ?></span>
                    </div>
                    <form method="get" class="ai-crm-filters">
                        <input type="hidden" name="page" value="ai-crm">
                        <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Search leads', 'ai-crm-system'); ?>">
                        <select name="status">
                            <option value=""><?php esc_html_e('All Statuses', 'ai-crm-system'); ?></option>
                            <?php foreach ($statuses as $key => $status) : ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php selected($current_status, $key); ?>>
                                    <?php echo esc_html($status['label']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="button"><?php esc_html_e('Filter', 'ai-crm-system'); ?></button>
                    </form>
                </div>

                <?php if ($leads) : ?>
                    <form method="post" class="ai-crm-bulk-form">
                        <?php wp_nonce_field('ai_crm_bulk_action'); ?>
                        <input type="hidden" name="ai_crm_bulk_action" value="1">
                        <div class="ai-crm-bulk-bar">
                            <select name="bulk_action">
                                <option value=""><?php esc_html_e('Bulk Actions', 'ai-crm-system'); ?></option>
                                <option value="status"><?php esc_html_e('Change Status', 'ai-crm-system'); ?></option>
                                <option value="delete"><?php esc_html_e('Delete', 'ai-crm-system'); ?></option>
                            </select>
                            <select name="bulk_status">
                                <?php foreach ($statuses as $key => $status) : ?>
                                    <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($status['label']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="button"><?php esc_html_e('Apply', 'ai-crm-system'); ?></button>
                        </div>
                    <div class="ai-crm-table-scroll">
                        <table class="ai-crm-table">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" class="ai-crm-check-all" aria-label="<?php esc_attr_e('Select all leads', 'ai-crm-system'); ?>"></th>
                                    <th><?php esc_html_e('Lead', 'ai-crm-system'); ?></th>
                                    <th><?php esc_html_e('Status', 'ai-crm-system'); ?></th>
                                    <th><?php esc_html_e('Value', 'ai-crm-system'); ?></th>
                                    <th><?php esc_html_e('Follow-up', 'ai-crm-system'); ?></th>
                                    <th><?php esc_html_e('Source', 'ai-crm-system'); ?></th>
                                    <th><?php esc_html_e('Notes', 'ai-crm-system'); ?></th>
                                    <th><?php esc_html_e('Actions', 'ai-crm-system'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($leads as $lead) : ?>
                                    <?php ai_crm_render_lead_row($lead, $statuses); ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    </form>
                <?php else : ?>
                    <div class="ai-crm-empty">
                        <h3><?php esc_html_e('No leads found', 'ai-crm-system'); ?></h3>
                        <p><?php esc_html_e('Add your first lead or adjust the filters.', 'ai-crm-system'); ?></p>
                    </div>
                <?php endif; ?>
                <?php ai_crm_render_pagination($lead_count); ?>
            </section>
        </div>
    </div>
    <script>
    document.addEventListener('change', function (event) {
        if (!event.target.classList.contains('ai-crm-check-all')) {
            return;
        }

        document.querySelectorAll('input[name="lead_ids[]"]').forEach(function (checkbox) {
            checkbox.checked = event.target.checked;
        });
    });
    </script>
    <?php
}

function ai_crm_render_notice() {
    $messages = array(
        'saved' => array('text' => 'Lead saved.', 'type' => 'success'),
        'updated' => array('text' => 'Lead updated.', 'type' => 'success'),
        'deleted' => array('text' => 'Lead deleted.', 'type' => 'success'),
        'activity_added' => array('text' => 'Activity note added.', 'type' => 'success'),
        'bulk_updated' => array('text' => 'Bulk action completed.', 'type' => 'success'),
        'imported' => array('text' => 'CSV import completed.', 'type' => 'success'),
        'import_failed' => array('text' => 'CSV import failed. Check the file format.', 'type' => 'error'),
    );
    $key = sanitize_key($_GET['message'] ?? '');
    if (isset($messages[$key])) {
        $notice = $messages[$key];
        echo '<div class="ai-crm-alert ai-crm-alert-' . esc_attr($notice['type']) . '" role="status">';
        echo '<span class="ai-crm-alert-dot" aria-hidden="true"></span>';
        echo '<p>' . esc_html($notice['text']) . '</p>';
        echo '</div>';
    }
}

function ai_crm_metric($label, $value, $caption) {
    ?>
    <article class="ai-crm-metric">
        <span><?php echo esc_html($label); ?></span>
        <strong><?php echo esc_html($value); ?></strong>
        <small><?php echo esc_html($caption); ?></small>
    </article>
    <?php
}

function ai_crm_render_import_panel() {
    ?>
    <details class="ai-crm-panel ai-crm-import-panel">
        <summary>
            <span><?php esc_html_e('Import Leads', 'ai-crm-system'); ?></span>
            <small><?php esc_html_e('CSV upload', 'ai-crm-system'); ?></small>
        </summary>
        <form method="post" enctype="multipart/form-data" class="ai-crm-import-form">
            <?php wp_nonce_field('ai_crm_import_csv'); ?>
            <input type="hidden" name="ai_crm_import_csv" value="1">
            <input type="file" name="ai_crm_csv" accept=".csv" required>
            <p class="description"><?php esc_html_e('Required columns: name, email. Optional: phone, company, status, source, deal_value, next_follow_up, notes.', 'ai-crm-system'); ?></p>
            <button type="submit" class="button"><?php esc_html_e('Import CSV', 'ai-crm-system'); ?></button>
        </form>
    </details>
    <?php
}

function ai_crm_text_field($name, $label, $value = '', $placeholder = '', $required = false, $type = 'text') {
    ?>
    <label>
        <span><?php echo esc_html($label); ?></span>
        <input type="<?php echo esc_attr($type); ?>" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr($placeholder); ?>" <?php echo $required ? 'required' : ''; ?>>
    </label>
    <?php
}

function ai_crm_render_activity($activities, $edit_lead) {
    ?>
    <div class="ai-crm-activity">
        <h3><?php echo $edit_lead ? esc_html__('Lead Activity', 'ai-crm-system') : esc_html__('Recent Activity', 'ai-crm-system'); ?></h3>
        <?php if ($edit_lead) : ?>
            <form method="post" class="ai-crm-activity-form">
                <?php wp_nonce_field('ai_crm_add_activity'); ?>
                <input type="hidden" name="ai_crm_add_activity" value="1">
                <input type="hidden" name="lead_id" value="<?php echo esc_attr($edit_lead->id); ?>">
                <textarea name="activity_note" rows="3" placeholder="<?php esc_attr_e('Add a call note, meeting update, or next step', 'ai-crm-system'); ?>" required></textarea>
                <button type="submit" class="button"><?php esc_html_e('Add Note', 'ai-crm-system'); ?></button>
            </form>
        <?php endif; ?>
        <?php if ($activities) : ?>
            <ul>
                <?php foreach ($activities as $activity) : ?>
                    <li>
                        <strong><?php echo esc_html(ucfirst($activity->activity_type)); ?></strong>
                        <span><?php echo esc_html($activity->activity_note); ?></span>
                        <small><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($activity->created_at))); ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p class="ai-crm-muted"><?php esc_html_e('No activity yet.', 'ai-crm-system'); ?></p>
        <?php endif; ?>
    </div>
    <?php
}

function ai_crm_render_lead_row($lead, $statuses) {
    $status = $statuses[$lead->status] ?? $statuses['new'];
    $delete_url = wp_nonce_url(ai_crm_admin_url(array('ai_crm_delete' => absint($lead->id))), 'ai_crm_delete_' . absint($lead->id));
    $follow_up_time = $lead->next_follow_up ? strtotime($lead->next_follow_up) : false;
    $is_due = $follow_up_time && $follow_up_time <= current_time('timestamp');
    ?>
    <tr>
        <td><input type="checkbox" name="lead_ids[]" value="<?php echo esc_attr($lead->id); ?>" aria-label="<?php echo esc_attr(sprintf(__('Select %s', 'ai-crm-system'), $lead->name)); ?>"></td>
        <td>
            <div class="ai-crm-lead-name"><?php echo esc_html($lead->name); ?></div>
            <a href="mailto:<?php echo esc_attr($lead->email); ?>"><?php echo esc_html($lead->email); ?></a>
            <small><?php echo esc_html(trim($lead->company . ' ' . $lead->phone)); ?></small>
        </td>
        <td><span class="ai-crm-status ai-crm-status-<?php echo esc_attr($status['tone']); ?>"><?php echo esc_html($status['label']); ?></span></td>
        <td><?php echo esc_html(ai_crm_money($lead->deal_value)); ?></td>
        <td>
            <?php if ($follow_up_time) : ?>
                <span class="<?php echo esc_attr($is_due ? 'ai-crm-due' : ''); ?>"><?php echo esc_html(date_i18n(get_option('date_format'), $follow_up_time)); ?></span>
            <?php else : ?>
                <span class="ai-crm-muted"><?php esc_html_e('Not set', 'ai-crm-system'); ?></span>
            <?php endif; ?>
        </td>
        <td><?php echo esc_html($lead->source); ?></td>
        <td class="ai-crm-notes"><?php echo esc_html(wp_trim_words((string) $lead->notes, 14)); ?></td>
        <td>
            <form method="post" class="ai-crm-row-actions">
                <?php wp_nonce_field('ai_crm_update_status'); ?>
                <input type="hidden" name="ai_crm_update_status" value="1">
                <input type="hidden" name="lead_id" value="<?php echo esc_attr($lead->id); ?>">
                <select name="status">
                    <?php foreach ($statuses as $key => $item) : ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected($lead->status, $key); ?>><?php echo esc_html($item['label']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="button"><?php esc_html_e('Update', 'ai-crm-system'); ?></button>
                <a class="button" href="<?php echo esc_url(ai_crm_admin_url(array('edit_lead' => absint($lead->id)))); ?>"><?php esc_html_e('Edit', 'ai-crm-system'); ?></a>
                <a class="ai-crm-delete" href="<?php echo esc_url($delete_url); ?>" onclick="return confirm('Delete this lead?');"><?php esc_html_e('Delete', 'ai-crm-system'); ?></a>
            </form>
        </td>
    </tr>
    <?php
}

function ai_crm_render_pagination($lead_count) {
    $per_page = ai_crm_records_per_page();
    $total_pages = (int) ceil($lead_count / $per_page);
    if ($total_pages < 2) {
        return;
    }

    $current_page = ai_crm_current_page();
    $filters = ai_crm_get_filters();
    ?>
    <nav class="ai-crm-pagination" aria-label="<?php esc_attr_e('Lead pagination', 'ai-crm-system'); ?>">
        <?php for ($page = 1; $page <= $total_pages; $page++) : ?>
            <?php
            $url = ai_crm_admin_url(array_filter(array(
                'paged' => $page,
                's' => $filters['search'],
                'status' => $filters['status'],
            )));
            ?>
            <a class="<?php echo esc_attr($page === $current_page ? 'is-active' : ''); ?>" href="<?php echo esc_url($url); ?>"><?php echo esc_html($page); ?></a>
        <?php endfor; ?>
    </nav>
    <?php
}
