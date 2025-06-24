<?php
class Gutenberg_Blocks_Settings
{
    public static function init()
    {
        add_action('admin_init', [self::class, 'register_settings']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_media']);
    }

    public static function enqueue_media()
    {
        wp_enqueue_media();
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

        add_settings_section(
            'blockxpert_pdf_invoice_section',
            __('PDF Invoice Settings', 'blockxpert'),
            null,
            'blockxpert-settings'
        );

        // Company Name
        add_settings_field('blockxpert_company_name', __('Company Name', 'blockxpert'), function() {
            echo '<input type="text" name="blockxpert_company_name" value="' . esc_attr(get_option('blockxpert_company_name', '')) . '" class="regular-text">';
        }, 'blockxpert-settings', 'blockxpert_pdf_invoice_section');
        register_setting('blockxpert-settings-group', 'blockxpert_company_name');

        // Company Address
        add_settings_field('blockxpert_company_address', __('Company Address', 'blockxpert'), function() {
            echo '<input type="text" name="blockxpert_company_address" value="' . esc_attr(get_option('blockxpert_company_address', '')) . '" class="regular-text">';
        }, 'blockxpert-settings', 'blockxpert_pdf_invoice_section');
        register_setting('blockxpert-settings-group', 'blockxpert_company_address');

        // Company Email
        add_settings_field('blockxpert_company_email', __('Company Email', 'blockxpert'), function() {
            echo '<input type="email" name="blockxpert_company_email" value="' . esc_attr(get_option('blockxpert_company_email', '')) . '" class="regular-text">';
        }, 'blockxpert-settings', 'blockxpert_pdf_invoice_section');
        register_setting('blockxpert-settings-group', 'blockxpert_company_email');

        // Company Logo
        add_settings_field('blockxpert_company_logo', __('Company Logo', 'blockxpert'), function() {
            $logo_url = esc_attr(get_option('blockxpert_company_logo', ''));
            ?>
            <div id="blockxpert-logo-upload-wrapper">
                <img id="blockxpert-logo-preview" src="<?php echo $logo_url; ?>" style="max-height:60px;max-width:200px;<?php echo $logo_url ? 'display:block;' : 'display:none;'; ?>margin-bottom:8px;" />
                <input type="hidden" id="blockxpert_company_logo" name="blockxpert_company_logo" value="<?php echo $logo_url; ?>" />
                <button type="button" class="button" id="blockxpert-logo-upload-btn"><?php esc_html_e('Select/Upload Logo', 'blockxpert'); ?></button>
                <button type="button" class="button" id="blockxpert-logo-remove-btn" style="<?php echo $logo_url ? '' : 'display:none;'; ?>"><?php esc_html_e('Remove Logo', 'blockxpert'); ?></button>
            </div>
            <script>
            jQuery(document).ready(function($){
                var frame;
                $('#blockxpert-logo-upload-btn').on('click', function(e){
                    e.preventDefault();
                    if(frame){ frame.open(); return; }
                    frame = wp.media({
                        title: '<?php echo esc_js(__('Select or Upload Logo', 'blockxpert')); ?>',
                        button: {
                            text: '<?php echo esc_js(__('Use this logo', 'blockxpert')); ?>'
                        },
                        multiple: false
                    });
                    frame.on('select', function(){
                        var attachment = frame.state().get('selection').first().toJSON();
                        $('#blockxpert_company_logo').val(attachment.url);
                        $('#blockxpert-logo-preview').attr('src', attachment.url).show();
                        $('#blockxpert-logo-remove-btn').show();
                    });
                    frame.open();
                });

                $('#blockxpert-logo-remove-btn').on('click', function(e){
                    e.preventDefault();
                    $('#blockxpert_company_logo').val('');
                    $('#blockxpert-logo-preview').hide();
                    $(this).hide();
                });
            });
            </script>
            <?php
        }, 'blockxpert-settings', 'blockxpert_pdf_invoice_section');
        register_setting('blockxpert-settings-group', 'blockxpert_company_logo');

        // Footer Text
        add_settings_field('blockxpert_company_footer', __('Footer Text', 'blockxpert'), function() {
            echo '<input type="text" name="blockxpert_company_footer" value="' . esc_attr(get_option('blockxpert_company_footer', '')) . '" class="regular-text">';
        }, 'blockxpert-settings', 'blockxpert_pdf_invoice_section');
        register_setting('blockxpert-settings-group', 'blockxpert_company_footer');

        // Font Size
        add_settings_field('blockxpert_invoice_font_size', __('Font Size', 'blockxpert'), function() {
            $value = get_option('blockxpert_invoice_font_size', '16px');
            echo '<select name="blockxpert_invoice_font_size">
                <option value="14px"' . selected($value, '14px', false) . '>' . __('Small', 'blockxpert') . '</option>
                <option value="16px"' . selected($value, '16px', false) . '>' . __('Medium', 'blockxpert') . '</option>
                <option value="18px"' . selected($value, '18px', false) . '>' . __('Large', 'blockxpert') . '</option>
            </select>';
        }, 'blockxpert-settings', 'blockxpert_pdf_invoice_section');
        register_setting('blockxpert-settings-group', 'blockxpert_invoice_font_size');

        // Primary Color
        add_settings_field('blockxpert_invoice_primary_color', __('Primary Color', 'blockxpert'), function() {
            $value = get_option('blockxpert_invoice_primary_color', '#007cba');
            echo '<input type="color" name="blockxpert_invoice_primary_color" value="' . esc_attr($value) . '">';
        }, 'blockxpert-settings', 'blockxpert_pdf_invoice_section');
        register_setting('blockxpert-settings-group', 'blockxpert_invoice_primary_color');
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
        return [];
    }
}

// Initialize
Gutenberg_Blocks_Settings::init();
