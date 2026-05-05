<?php

if (!defined('ABSPATH')) exit;

function ai_crm_dashboard() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to access AI CRM.', 'ai-crm-system'));
    }

    ai_crm_install();

    $leads = ai_crm_get_leads();
    $stats = ai_crm_get_stats();
    $statuses = ai_crm_statuses();
    $default_status = ai_crm_get_setting('default_status');
    $current_status = isset($_GET['status']) ? sanitize_key($_GET['status']) : '';
    $search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
    $edit_lead_id = isset($_GET['edit_lead']) ? absint($_GET['edit_lead']) : 0;
    $edit_lead = $edit_lead_id ? ai_crm_get_lead($edit_lead_id) : null;
    $activities = $edit_lead ? ai_crm_get_activities($edit_lead->id) : ai_crm_get_activities();
    $export_url = wp_nonce_url(ai_crm_admin_url(array('ai_crm_export' => 1, 's' => $search, 'status' => $current_status)), 'ai_crm_export');
    ?>
    <div class="wrap ai-crm-wrap">
        <?php ai_crm_render_notice(); ?>

        <section class="ai-crm-hero">
            <div>
                <p class="ai-crm-kicker">WordPress CRM Workspace</p>
                <h1>AI CRM Dashboard</h1>
                <p>Track every lead, follow-up, and deal signal from one focused command center.</p>
            </div>
            <div class="ai-crm-hero-actions">
                <a class="ai-crm-primary-link" href="#ai-crm-lead-form"><?php echo $edit_lead ? esc_html__('Edit Lead', 'ai-crm-system') : esc_html__('Add Lead', 'ai-crm-system'); ?></a>
                <a class="ai-crm-secondary-link" href="<?php echo esc_url($export_url); ?>"><?php esc_html_e('Export CSV', 'ai-crm-system'); ?></a>
            </div>
        </section>

        <section class="ai-crm-metrics" aria-label="CRM summary">
            <?php ai_crm_metric('Total Leads', number_format_i18n($stats['total']), 'All captured contacts'); ?>
            <?php ai_crm_metric('Open Pipeline', number_format_i18n($stats['open']), 'Leads still in motion'); ?>
            <?php ai_crm_metric('Won Deals', number_format_i18n($stats['won']), 'Closed successfully'); ?>
            <?php ai_crm_metric('Pipeline Value', ai_crm_money($stats['pipeline']), 'Estimated open value'); ?>
            <?php ai_crm_metric('Due Follow-ups', number_format_i18n($stats['followups']), 'Needs attention'); ?>
        </section>

        <div class="ai-crm-grid">
            <section class="ai-crm-panel" id="ai-crm-lead-form">
                <div class="ai-crm-panel-heading">
                    <h2><?php echo $edit_lead ? esc_html__('Edit Lead', 'ai-crm-system') : esc_html__('Add New Lead', 'ai-crm-system'); ?></h2>
                    <span><?php echo $edit_lead ? esc_html__('Update contact details and notes', 'ai-crm-system') : esc_html__('Capture the next opportunity', 'ai-crm-system'); ?></span>
                </div>

                <form method="post" class="ai-crm-form">
                    <?php if ($edit_lead) : ?>
                        <?php wp_nonce_field('ai_crm_update_lead'); ?>
                        <input type="hidden" name="ai_crm_update_lead" value="1">
                        <input type="hidden" name="lead_id" value="<?php echo esc_attr($edit_lead->id); ?>">
                    <?php else : ?>
                        <?php wp_nonce_field('ai_crm_save_lead'); ?>
                        <input type="hidden" name="ai_crm_save_lead" value="1">
                    <?php endif; ?>

                    <label>
                        <span>Name</span>
                        <input type="text" name="name" value="<?php echo esc_attr($edit_lead->name ?? ''); ?>" placeholder="Jane Cooper" required>
                    </label>
                    <label>
                        <span>Email</span>
                        <input type="email" name="email" value="<?php echo esc_attr($edit_lead->email ?? ''); ?>" placeholder="jane@example.com" required>
                    </label>
                    <label>
                        <span>Phone</span>
                        <input type="text" name="phone" value="<?php echo esc_attr($edit_lead->phone ?? ''); ?>" placeholder="+1 555 123 4567">
                    </label>
                    <label>
                        <span>Company</span>
                        <input type="text" name="company" value="<?php echo esc_attr($edit_lead->company ?? ''); ?>" placeholder="Acme Studio">
                    </label>
                    <label>
                        <span>Status</span>
                        <select name="status">
                            <?php foreach ($statuses as $key => $status) : ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php selected($edit_lead->status ?? $default_status, $key); ?>>
                                    <?php echo esc_html($status['label']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <span>Source</span>
                        <select name="source">
                            <?php foreach (ai_crm_sources() as $source) : ?>
                                <option value="<?php echo esc_attr($source); ?>" <?php selected($edit_lead->source ?? 'Website', $source); ?>>
                                    <?php echo esc_html($source); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <span>Deal Value</span>
                        <input type="number" name="deal_value" value="<?php echo esc_attr($edit_lead->deal_value ?? ''); ?>" min="0" step="0.01" placeholder="2500">
                    </label>
                    <label>
                        <span>Next Follow-up</span>
                        <input type="date" name="next_follow_up" value="<?php echo esc_attr($edit_lead->next_follow_up ?? ''); ?>">
                    </label>
                    <label class="ai-crm-full">
                        <span>Notes</span>
                        <textarea name="notes" rows="4" placeholder="What should the next conversation focus on?"><?php echo esc_textarea($edit_lead->notes ?? ''); ?></textarea>
                    </label>
                    <div class="ai-crm-form-actions">
                        <button type="submit" class="button button-primary ai-crm-button">
                            <?php echo $edit_lead ? esc_html__('Update Lead', 'ai-crm-system') : esc_html__('Save Lead', 'ai-crm-system'); ?>
                        </button>
                        <?php if ($edit_lead) : ?>
                            <a class="button" href="<?php echo esc_url(ai_crm_admin_url()); ?>"><?php esc_html_e('Cancel Edit', 'ai-crm-system'); ?></a>
                        <?php endif; ?>
                    </div>
                </form>

                <div class="ai-crm-activity">
                    <h3><?php echo $edit_lead ? esc_html__('Lead Activity', 'ai-crm-system') : esc_html__('Recent Activity', 'ai-crm-system'); ?></h3>
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
            </section>

            <section class="ai-crm-panel ai-crm-list-panel">
                <div class="ai-crm-panel-heading ai-crm-list-heading">
                    <div>
                        <h2>Leads</h2>
                        <span><?php echo esc_html(count($leads)); ?> visible records</span>
                    </div>
                    <form method="get" class="ai-crm-filters">
                        <input type="hidden" name="page" value="ai-crm">
                        <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Search leads">
                        <select name="status">
                            <option value="">All Statuses</option>
                            <?php foreach ($statuses as $key => $status) : ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php selected($current_status, $key); ?>>
                                    <?php echo esc_html($status['label']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="button">Filter</button>
                    </form>
                </div>

                <?php if ($leads) : ?>
                    <div class="ai-crm-table-scroll">
                        <table class="ai-crm-table">
                            <thead>
                                <tr>
                                    <th>Lead</th>
                                    <th>Status</th>
                                    <th>Value</th>
                                    <th>Follow-up</th>
                                    <th>Source</th>
                                    <th>Notes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($leads as $lead) : ?>
                                    <?php ai_crm_render_lead_row($lead, $statuses); ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else : ?>
                    <div class="ai-crm-empty">
                        <h3>No leads found</h3>
                        <p>Add your first lead or adjust the filters to see more records.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
    <?php
}

function ai_crm_render_notice() {
    if (empty($_GET['message'])) {
        return;
    }

    $messages = array(
        'saved' => 'Lead saved.',
        'updated' => 'Lead status updated.',
        'deleted' => 'Lead deleted.',
    );
    $key = sanitize_key($_GET['message']);

    if (!isset($messages[$key])) {
        return;
    }

    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($messages[$key]) . '</p></div>';
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

function ai_crm_render_lead_row($lead, $statuses) {
    $status = $statuses[$lead->status] ?? $statuses['new'];
    $delete_url = wp_nonce_url(ai_crm_admin_url(array('ai_crm_delete' => absint($lead->id))), 'ai_crm_delete_' . absint($lead->id));
    ?>
    <tr>
        <td>
            <div class="ai-crm-lead-name"><?php echo esc_html($lead->name); ?></div>
            <a href="mailto:<?php echo esc_attr($lead->email); ?>"><?php echo esc_html($lead->email); ?></a>
            <?php if ($lead->company || $lead->phone) : ?>
                <small><?php echo esc_html(trim($lead->company . ' ' . $lead->phone)); ?></small>
            <?php endif; ?>
        </td>
        <td>
            <span class="ai-crm-status ai-crm-status-<?php echo esc_attr($status['tone']); ?>">
                <?php echo esc_html($status['label']); ?>
            </span>
        </td>
        <td><?php echo esc_html(ai_crm_money((float) $lead->deal_value)); ?></td>
        <td>
            <?php if ($lead->next_follow_up) : ?>
                <span class="<?php echo esc_attr(strtotime($lead->next_follow_up) <= current_time('timestamp') ? 'ai-crm-due' : ''); ?>">
                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($lead->next_follow_up))); ?>
                </span>
            <?php else : ?>
                <span class="ai-crm-muted">Not set</span>
            <?php endif; ?>
        </td>
        <td><?php echo esc_html($lead->source); ?></td>
        <td class="ai-crm-notes"><?php echo esc_html(wp_trim_words((string) $lead->notes, 16)); ?></td>
        <td>
            <form method="post" class="ai-crm-row-actions">
                <?php wp_nonce_field('ai_crm_update_status'); ?>
                <input type="hidden" name="ai_crm_update_status" value="1">
                <input type="hidden" name="lead_id" value="<?php echo esc_attr($lead->id); ?>">
                <select name="status">
                    <?php foreach ($statuses as $key => $item) : ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected($lead->status, $key); ?>>
                            <?php echo esc_html($item['label']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="button">Update</button>
                <a class="button" href="<?php echo esc_url(ai_crm_admin_url(array('edit_lead' => absint($lead->id)))); ?>">Edit</a>
                <a class="ai-crm-delete" href="<?php echo esc_url($delete_url); ?>" onclick="return confirm('Delete this lead?');">Delete</a>
            </form>
        </td>
    </tr>
    <?php
}
