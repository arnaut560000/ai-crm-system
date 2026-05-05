<?php
if (!defined('ABSPATH')) exit;

add_action('admin_init', 'ai_crm_register_settings');

function ai_crm_register_settings() {
    register_setting(
        'ai_crm_settings_group',
        'ai_crm_settings',
        array(
            'sanitize_callback' => 'ai_crm_sanitize_settings',
            'default' => ai_crm_default_settings(),
        )
    );
}

function ai_crm_sanitize_settings($input) {
    $defaults = ai_crm_default_settings();
    $input = is_array($input) ? $input : array();
    $currency = sanitize_text_field(wp_unslash($input['currency_symbol'] ?? $defaults['currency_symbol']));

    return array(
        'currency_symbol' => $currency !== '' ? substr($currency, 0, 8) : $defaults['currency_symbol'],
        'default_status' => ai_crm_validate_status($input['default_status'] ?? $defaults['default_status']),
        'delete_data_on_uninstall' => !empty($input['delete_data_on_uninstall']) ? 1 : 0,
    );
}

function ai_crm_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to manage AI CRM settings.', 'ai-crm-system'));
    }

    $settings = ai_crm_get_settings();
    ?>
    <div class="wrap ai-crm-wrap">
        <section class="ai-crm-hero ai-crm-settings-hero">
            <div>
                <p class="ai-crm-kicker"><?php esc_html_e('Configuration', 'ai-crm-system'); ?></p>
                <h1><?php esc_html_e('AI CRM Settings', 'ai-crm-system'); ?></h1>
                <p><?php esc_html_e('Set defaults for your CRM workspace.', 'ai-crm-system'); ?></p>
            </div>
        </section>

        <section class="ai-crm-panel ai-crm-settings-panel">
            <form method="post" action="options.php" class="ai-crm-settings-form">
                <?php settings_fields('ai_crm_settings_group'); ?>

                <label>
                    <span><?php esc_html_e('Currency Symbol', 'ai-crm-system'); ?></span>
                    <input type="text" name="ai_crm_settings[currency_symbol]" value="<?php echo esc_attr($settings['currency_symbol']); ?>" maxlength="8">
                </label>

                <label>
                    <span><?php esc_html_e('Default Status', 'ai-crm-system'); ?></span>
                    <select name="ai_crm_settings[default_status]">
                        <?php foreach (ai_crm_statuses() as $key => $status) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($settings['default_status'], $key); ?>><?php echo esc_html($status['label']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="ai-crm-checkbox">
                    <input type="checkbox" name="ai_crm_settings[delete_data_on_uninstall]" value="1" <?php checked((int) $settings['delete_data_on_uninstall'], 1); ?>>
                    <span><?php esc_html_e('Delete CRM data when the plugin is uninstalled', 'ai-crm-system'); ?></span>
                </label>

                <p class="description"><?php esc_html_e('Leave this off if you want to keep leads after uninstalling.', 'ai-crm-system'); ?></p>
                <?php submit_button(__('Save Settings', 'ai-crm-system')); ?>
            </form>
        </section>
    </div>
    <?php
}
