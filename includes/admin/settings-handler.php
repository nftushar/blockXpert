<?php
class Gutenberg_Blocks_Settings
{
    public static function init()
    {
        add_action('admin_init', [self::class, 'register_settings']);
    }

    public static function register_settings()
    {
        register_setting(
            'gutenberg_blocks_settings',
            'gutenberg_blocks_active',
            [
                'type' => 'array',
                'sanitize_callback' => [self::class, 'sanitize_blocks'],
                'default' => self::get_default_blocks()
            ]
        );

        add_action('admin_init', function() {
            add_settings_section('blockxpert_pdf_invoice_section', __('PDF Invoice Settings', 'blockxpert'), null, 'blockxpert-settings');

            add_settings_field('blockxpert_company_name', __('Company Name', 'blockxpert'), function() {
                echo '<input type="text" name="blockxpert_company_name" value="' . esc_attr(get_option('blockxpert_company_name', '')) . '" class="regular-text">';
            }, 'blockxpert-settings', 'blockxpert_pdf_invoice_section');
            register_setting('blockxpert-settings-group', 'blockxpert_company_name');

            add_settings_field('blockxpert_company_address', __('Company Address', 'blockxpert'), function() {
                echo '<input type="text" name="blockxpert_company_address" value="' . esc_attr(get_option('blockxpert_company_address', '')) . '" class="regular-text">';
            }, 'blockxpert-settings', 'blockxpert_pdf_invoice_section');
            register_setting('blockxpert-settings-group', 'blockxpert_company_address');

            add_settings_field('blockxpert_company_email', __('Company Email', 'blockxpert'), function() {
                echo '<input type="email" name="blockxpert_company_email" value="' . esc_attr(get_option('blockxpert_company_email', '')) . '" class="regular-text">';
            }, 'blockxpert-settings', 'blockxpert_pdf_invoice_section');
            register_setting('blockxpert-settings-group', 'blockxpert_company_email');

            add_settings_field('blockxpert_company_logo', __('Company Logo', 'blockxpert'), function() {
                $logo_url = esc_attr(get_option('blockxpert_company_logo', ''));
                echo '<div id="blockxpert-logo-upload-wrapper">';
                echo '<img id="blockxpert-logo-preview" src="' . ($logo_url ? $logo_url : '') . '" style="max-height:60px;max-width:200px;display:' . ($logo_url ? 'block' : 'none') . ';margin-bottom:8px;" />';
                echo '<input type="hidden" id="blockxpert_company_logo" name="blockxpert_company_logo" value="' . $logo_url . '" />';
                echo '<button type="button" class="button" id="blockxpert-logo-upload-btn">' . __('Select/Upload Logo', 'blockxpert') . '</button> ';
                echo '<button type="button" class="button" id="blockxpert-logo-remove-btn" style="' . ($logo_url ? '' : 'display:none;') . '">' . __('Remove Logo', 'blockxpert') . '</button>';
                echo '</div>';
                ?>
                <script>
                jQuery(document).ready(function($){
                    var frame;
                    $('#blockxpert-logo-upload-btn').on('click', function(e){
                        e.preventDefault();
                        if(frame){ frame.open(); return; }
                        frame = wp.media({
                            title: '
            });
            register_setting('blockxpert-settings-group', 'blockxpert_company_logo');

            add_settings_field('blockxpert_company_footer', __('Footer Text', 'blockxpert'), function() {
                echo '<input type="text" name="blockxpert_company_footer" value="' . esc_attr(get_option('blockxpert_company_footer', '')) . '" class="regular-text">';
            }, 'blockxpert-settings', 'blockxpert_pdf_invoice_section');
            register_setting('blockxpert-settings-group', 'blockxpert_company_footer');

            add_settings_field('blockxpert_invoice_font_size', __('Font Size', 'blockxpert'), function() {
                $value = get_option('blockxpert_invoice_font_size', '16px');
                echo '<select name="blockxpert_invoice_font_size">
                    <option value="14px"' . selected($value, '14px', false) . '>Small</option>
                    <option value="16px"' . selected($value, '16px', false) . '>Medium</option>
                    <option value="18px"' . selected($value, '18px', false) . '>Large</option>
                </select>';
            }, 'blockxpert-settings', 'blockxpert_pdf_invoice_section');
            register_setting('blockxpert-settings-group', 'blockxpert_invoice_font_size');

            add_settings_field('blockxpert_invoice_primary_color', __('Primary Color', 'blockxpert'), function() {
                $value = get_option('blockxpert_invoice_primary_color', '#007cba');
                echo '<input type="color" name="blockxpert_invoice_primary_color" value="' . esc_attr($value) . '">';
            }, 'blockxpert-settings', 'blockxpert_pdf_invoice_section');
            register_setting('blockxpert-settings-group', 'blockxpert_invoice_primary_color');
        });
    }

    public static function sanitize_blocks($input)
    {
        if (!is_array($input)) {
            return [];
        }

        $valid_blocks = [];
        $blocks_dir = GB_PATH . 'blocks/';

        foreach (scandir($blocks_dir) as $item) {
            if ($item !== '.' && $item !== '..' && is_dir($blocks_dir . $item)) {
                $valid_blocks[] = $item;
            }
        }

        return array_intersect($input, $valid_blocks);
    }

    public static function get_default_blocks()
    {
        return ['block-one', 'block-two', 'block-three'];
    }
}

// Initialize the settings class
Gutenberg_Blocks_Settings::init();
