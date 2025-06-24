<?php
/**
 * Handles the admin settings page and options for BlockXpert.
 */
class BlockXpert_Admin_Settings {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('wp_ajax_save_blockxpert_invoice_settings', [$this, 'save_invoice_settings_ajax']);
        add_action('wp_ajax_blockxpert_invoice_live_preview', [$this, 'ajax_invoice_live_preview']);
    }

    public function add_admin_page() {
        add_menu_page(
            __('BlockXpert Settings', 'blockxpert'),
            __('BlockXpert', 'blockxpert'),
            'manage_options',
            'blockxpert-settings',
            [$this, 'render_admin_page'],
            'dashicons-editor-code',
            60
        );
    }

    public function register_settings() {
        register_setting('blockxpert_settings', 'blockxpert_active', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_active_blocks'],
            'default' => ['product-slider', 'ai-faq', 'ai-product-recommendations', 'pdf-invoice'],
        ]);
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        echo '<div class="wrap"><h1>' . __('BlockXpert Settings', 'blockxpert') . '</h1>';
        
        // Add tabs navigation
        echo '<h2 class="nav-tab-wrapper">';
        echo '<a href="#blocks" class="nav-tab nav-tab-active" data-tab="blocks">' . __('Blocks', 'blockxpert') . '</a>';
        echo '<a href="#pdf-invoice" class="nav-tab" data-tab="pdf-invoice">' . __('PDF Invoice', 'blockxpert') . '</a>';
        echo '</h2>';
        
        // Blocks Settings Section
        echo '<div id="blocks-section" class="blockxpert-section active">';
        $blocks = ['product-slider', 'ai-faq', 'ai-product-recommendations', 'pdf-invoice'];
        $active_blocks = get_option('blockxpert_active', $blocks);
        
        echo '<form method="post" action="options.php">';
        settings_fields('blockxpert_settings');
        echo '<div class="blockxpert-tabs" id="blockxpert-tabs">
            <button type="button" class="blockxpert-tab active" data-tab="all">All</button>
            <button type="button" class="blockxpert-tab" data-tab="active">Active</button>
            <button type="button" class="blockxpert-tab" data-tab="inactive">Inactive</button>
        </div>';
        echo '<div class="blockxpert-search-bar"><input type="text" class="blockxpert-search-input" id="blockxpert-search" placeholder="Search blocks..."></div>';
        echo '<div class="blockxpert-block-list" id="blockxpert-block-list">';
        foreach ($blocks as $block) {
            $checked = in_array($block, $active_blocks) ? 'checked' : '';
            $yesno = in_array($block, $active_blocks) ? 'Yes' : 'No';
            $status = in_array($block, $active_blocks) ? 'active' : 'inactive';
            echo '<div class="blockxpert-block-card" data-block-name="' . esc_attr($block) . '" data-status="' . $status . '">
                <span class="blockxpert-block-label">' . esc_html(ucwords(str_replace('-', ' ', $block))) . '</span>
                <div>
                    <label class="blockxpert-toggle-switch">
                        <input type="checkbox" name="blockxpert_active[]" value="' . esc_attr($block) . '" ' . $checked . '>
                        <span class="blockxpert-toggle-slider"></span>
                    </label>
                    <span class="blockxpert-toggle-yesno">' . $yesno . '</span>
                </div>
            </div>';
        }
        echo '</div>';
        submit_button();
        echo '</form>';
        echo '</div>';
        
        // PDF Invoice Settings Section
        echo '<div id="pdf-invoice-section" class="blockxpert-section">';
        echo '<div class="blockxpert-invoice-layout">';
        
        // Settings Column
        echo '<div class="blockxpert-settings-column">';
        echo '<form method="post" action="options.php" id="invoice-settings-form">';
        wp_nonce_field('blockxpert_invoice_settings', 'blockxpert_invoice_nonce');
        settings_fields('blockxpert-settings-group');
        do_settings_sections('blockxpert-settings');
        echo '<div class="submit-wrapper">';
        echo '<div id="save-status" style="display:none;margin-left:10px;"></div>';
        submit_button(__('Save Changes', 'blockxpert'), 'primary', 'submit', false);
        echo '</div>';
        echo '</form>';
        echo '</div>';
        
        // Preview Column
        echo '<div class="blockxpert-preview-column">';
        echo '<div class="preview-header">';
        echo '<h3>' . __('Live Preview', 'blockxpert') . '</h3>';
        echo '<div class="preview-controls">';
        echo '<button type="button" class="button" id="refresh-preview">' . __('Refresh Preview', 'blockxpert') . '</button>';
        echo '</div>';
        echo '</div>';
        echo '<div class="preview-container">';
        // Sample invoice preview
        echo '<div class="invoice-preview" id="invoice-preview">';
        $company_name = get_option('blockxpert_company_name', '');
        $company_address = get_option('blockxpert_company_address', '');
        $company_email = get_option('blockxpert_company_email', '');
        $company_logo = get_option('blockxpert_company_logo', '');
        $footer_text = get_option('blockxpert_company_footer', '');
        $font_size = get_option('blockxpert_invoice_font_size', '16px');
        $primary_color = get_option('blockxpert_invoice_primary_color', '#007cba');
        echo '<style>
            .invoice-preview {
                font-size: ' . esc_attr($font_size) . ';
                --invoice-primary-color: ' . esc_attr($primary_color) . ';
            }
        </style>';
        echo '<div class="preview-invoice-container">';
        echo '<header>';
        if ($company_logo) {
            echo '<div class="company-logo"><img src="' . esc_url($company_logo) . '" alt="Company Logo" style="max-height: 60px;"></div>';
        }
        echo '<div class="company-info">';
        echo '<h2>' . esc_html($company_name ?: 'Your Company Name') . '</h2>';
        echo '<p>' . esc_html($company_address ?: 'Company Address') . '</p>';
        echo '<p>' . esc_html($company_email ?: 'company@email.com') . '</p>';
        echo '</div>';
        echo '</header>';
        echo '<section class="invoice-details">';
        echo '<h1>Invoice</h1>';
        echo '<p><strong>Invoice #:</strong> SAMPLE-001</p>';
        echo '<p><strong>Date:</strong> ' . date('Y-m-d') . '</p>';
        echo '</section>';
        echo '<section class="order-items">';
        echo '<table width="100%">';
        echo '<thead><tr><th>Product</th><th>Quantity</th><th>Price</th><th>Total</th></tr></thead>';
        echo '<tbody>';
        echo '<tr><td>Sample Product 1</td><td>2</td><td>$49.99</td><td>$99.98</td></tr>';
        echo '<tr><td>Sample Product 2</td><td>1</td><td>$29.99</td><td>$29.99</td></tr>';
        echo '</tbody>';
        echo '</table>';
        echo '</section>';
        echo '<section class="order-summary">';
        echo '<p><strong>Subtotal:</strong> $129.97</p>';
        echo '<p><strong>Total:</strong> $129.97</p>';
        echo '</section>';
        echo '<footer>';
        echo '<p>' . esc_html($footer_text ?: 'Thank you for your business!') . '</p>';
        echo '</footer>';
        echo '</div>'; // .preview-invoice-container
        echo '</div>'; // .invoice-preview
        echo '</div>'; // .preview-container
        echo '</div>'; // .blockxpert-preview-column
        echo '</div>'; // .blockxpert-invoice-layout
        echo '</div>'; // #pdf-invoice-section
        
        // Add styles for the layout
        echo '<style>
            .blockxpert-invoice-layout {
                display: flex;
                gap: 30px;
                margin-top: 20px;
            }
            .blockxpert-settings-column {
                flex: 0 0 40%;
            }
            .blockxpert-preview-column {
                flex: 0 0 50%;
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 20px;
            }
            .preview-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 1px solid #eee;
            }
            .preview-container {
                background: #fff;
                padding: 20px;
                border: 1px solid #eee;
                border-radius: 4px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }
            .preview-invoice-container {
                max-width: 800px;
                margin: 0 auto;
            }
            .invoice-preview header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 30px;
                padding-bottom: 20px;
                border-bottom: 2px solid var(--invoice-primary-color);
            }
            .invoice-preview .company-info h2 {
                color: var(--invoice-primary-color);
                margin: 0 0 10px 0;
            }
            .invoice-preview .company-info p {
                margin: 5px 0;
            }
            .invoice-preview table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            .invoice-preview th {
                background: var(--invoice-primary-color);
                color: #fff;
                padding: 10px;
                text-align: left;
            }
            .invoice-preview td {
                padding: 10px;
                border-bottom: 1px solid #eee;
            }
            .invoice-preview .order-summary {
                text-align: right;
                margin-top: 20px;
            }
            .invoice-preview footer {
                margin-top: 40px;
                padding-top: 20px;
                border-top: 2px solid var(--invoice-primary-color);
                text-align: center;
                color: #666;
            }
        </style>';
        
        // Add JavaScript for live preview updates
        echo '<script>
        jQuery(document).ready(function($) {
            function updatePreview() {
                var companyName = $("[name=blockxpert_company_name]").val() || "Your Company Name";
                var companyAddress = $("[name=blockxpert_company_address]").val() || "Company Address";
                var companyEmail = $("[name=blockxpert_company_email]").val() || "company@email.com";
                var companyLogo = $("[name=blockxpert_company_logo]").val();
                var footerText = $("[name=blockxpert_company_footer]").val() || "Thank you for your business!";
                var fontSize = $("[name=blockxpert_invoice_font_size]").val();
                var primaryColor = $("[name=blockxpert_invoice_primary_color]").val();
                
                // Update preview styles
                $(".invoice-preview").css("font-size", fontSize);
                document.documentElement.style.setProperty("--invoice-primary-color", primaryColor);
                
                // Update preview content
                $(".invoice-preview .company-info h2").text(companyName);
                $(".invoice-preview .company-info p").first().text(companyAddress);
                $(".invoice-preview .company-info p").last().text(companyEmail);
                $(".invoice-preview footer p").text(footerText);
                
                if (companyLogo) {
                    $(".invoice-preview .company-logo").html(`<img src="${companyLogo}" alt="Company Logo" style="max-height: 60px;">`);
                } else {
                    $(".invoice-preview .company-logo").empty();
                }
            }
            
            // Update preview on input changes
            $("#invoice-settings-form input, #invoice-settings-form select").on("input change", updatePreview);
            
            // Update preview on refresh button click
            $("#refresh-preview").on("click", updatePreview);
            
            // Initial preview update
            updatePreview();
        });
        </script>';
        
        // Add tab switching JavaScript
        echo '<style>
            .blockxpert-section { display: none; padding-top: 20px; }
            .blockxpert-section.active { display: block; }
            .nav-tab { cursor: pointer; }
            .blockxpert-tabs { display: flex; gap: 16px; margin-bottom: 16px; }
            .blockxpert-tab { padding: 8px 20px; border-radius: 20px; background: #f1f1f1; cursor: pointer; font-weight: 500; color: #2271b1; border: none; outline: none; transition: background 0.2s; }
            .blockxpert-tab.active, .blockxpert-tab:focus { background: #2271b1; color: #fff; }
            .blockxpert-search-bar { margin-bottom: 20px; }
            .blockxpert-search-input { width: 300px; padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc; font-size: 16px; }
            .blockxpert-block-list { display: flex; flex-wrap: wrap; gap: 24px; }
            .blockxpert-block-card { background: #fff; border: 1px solid #e1e5e9; border-radius: 8px; padding: 18px 24px; min-width: 260px; max-width: 320px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 8px rgba(0,0,0,0.03); }
            .blockxpert-block-label { font-size: 1.1em; font-weight: 500; }
            .blockxpert-toggle-switch { position: relative; display: inline-block; width: 48px; height: 24px; margin-right: 8px; vertical-align: middle; }
            .blockxpert-toggle-switch input { opacity: 0; width: 0; height: 0; }
            .blockxpert-toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 24px; }
            .blockxpert-toggle-slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
            .blockxpert-toggle-switch input:checked + .blockxpert-toggle-slider { background-color: #2271b1; }
            .blockxpert-toggle-switch input:checked + .blockxpert-toggle-slider:before { transform: translateX(24px); }
            .blockxpert-toggle-yesno { font-weight: 500; color: #2271b1; }
            @media (max-width: 700px) { .blockxpert-block-list { flex-direction: column; gap: 12px; } .blockxpert-block-card { min-width: 0; width: 100%; } }
        </style>';
        echo '<script>
        jQuery(document).ready(function($) {
            // Show initial tab
            $("#blocks-section").show();
            
            // Handle tab clicks
            $(".nav-tab").on("click", function(e) {
                e.preventDefault();
                var tab = $(this).data("tab");
                
                // Update tabs
                $(".nav-tab").removeClass("nav-tab-active");
                $(this).addClass("nav-tab-active");
                
                // Update sections
                $(".blockxpert-section").removeClass("active").hide();
                if(tab === "blocks") {
                    $("#blocks-section").addClass("active").show();
                } else if(tab === "pdf-invoice") {
                    $("#pdf-invoice-section").addClass("active").show();
                }
            });
            // Tabs
            const tabs = document.querySelectorAll(".blockxpert-tab");
            const blockCards = document.querySelectorAll(".blockxpert-block-card");
            tabs.forEach(tab => {
                tab.addEventListener("click", function() {
                    tabs.forEach(t => t.classList.remove("active"));
                    this.classList.add("active");
                    const tabType = this.getAttribute("data-tab");
                    blockCards.forEach(card => {
                        if (tabType === "all") {
                            card.style.display = "flex";
                        } else if (tabType === "active") {
                            card.style.display = card.getAttribute("data-status") === "active" ? "flex" : "none";
                        } else if (tabType === "inactive") {
                            card.style.display = card.getAttribute("data-status") === "inactive" ? "flex" : "none";
                        }
                    });
                });
            });
            // Search
            const searchInput = document.getElementById("blockxpert-search");
            searchInput.addEventListener("input", function() {
                const val = this.value.toLowerCase();
                blockCards.forEach(card => {
                    const name = card.getAttribute("data-block-name").replace(/-/g, " ").toLowerCase();
                    card.style.display = name.includes(val) ? "flex" : "none";
                });
            });
            // Toggle Yes/No
            document.querySelectorAll(".blockxpert-toggle-switch input[type=checkbox]").forEach(function(input) {
                input.addEventListener("change", function() {
                    var span = this.closest(".blockxpert-block-card").querySelector(".blockxpert-toggle-yesno");
                    span.textContent = this.checked ? "Yes" : "No";
                    this.closest(".blockxpert-block-card").setAttribute("data-status", this.checked ? "active" : "inactive");
                });
            });
        });
        </script>';

        // Update JavaScript for AJAX saving
        echo '<script>
        jQuery(document).ready(function($) {
            var saveTimeout;
            var $form = $("#invoice-settings-form");
            var $saveStatus = $("#save-status");
            
            function showSaveStatus(message, type) {
                $saveStatus.html(message)
                    .removeClass("notice-success notice-error")
                    .addClass("notice-" + type)
                    .fadeIn()
                    .delay(2000)
                    .fadeOut();
            }
            
            function saveSettings() {
                var formData = {
                    action: "save_blockxpert_invoice_settings",
                    nonce: $("#blockxpert_invoice_nonce").val(),
                    company_name: $("[name=blockxpert_company_name]").val(),
                    company_address: $("[name=blockxpert_company_address]").val(),
                    company_email: $("[name=blockxpert_company_email]").val(),
                    company_logo: $("[name=blockxpert_company_logo]").val(),
                    footer_text: $("[name=blockxpert_company_footer]").val(),
                    font_size: $("[name=blockxpert_invoice_font_size]").val(),
                    primary_color: $("[name=blockxpert_invoice_primary_color]").val()
                };

                $.post(ajaxurl, formData, function(response) {
                    if (response.success) {
                        showSaveStatus("✓ ' . esc_js(__('Settings saved', 'blockxpert')) . '", "success");
                    } else {
                        showSaveStatus("✕ ' . esc_js(__('Error saving settings', 'blockxpert')) . '", "error");
                    }
                }).fail(function() {
                    showSaveStatus("✕ ' . esc_js(__('Error saving settings', 'blockxpert')) . '", "error");
                });
            }

            // Auto-save on input changes
            $form.on("input change", "input, select", function() {
                clearTimeout(saveTimeout);
                updatePreview(); // Update preview immediately
                saveTimeout = setTimeout(saveSettings, 1000); // Save after 1 second of no changes
            });

            // Handle form submission
            $form.on("submit", function(e) {
                e.preventDefault();
                clearTimeout(saveTimeout);
                saveSettings();
            });

            // ... existing updatePreview function ...
        });
        </script>';
        
        // Add status message styles
        echo '<style>
            .submit-wrapper {
                display: flex;
                align-items: center;
            }
            #save-status {
                padding: 5px 10px;
                border-radius: 4px;
                font-weight: 500;
            }
            .notice-success {
                color: #008a20;
                background: #edfaef;
            }
            .notice-error {
                color: #d63638;
                background: #fcefef;
            }
            // ... existing styles ...
        </style>';
        echo '</div>';
    }

    public function enqueue_admin_assets($hook) {
        if ($hook !== 'toplevel_page_blockxpert-settings') {
            return;
        }
        $css_path = BLOCKXPERT_PATH . 'includes/assets/css/admin.css';
        $css_url = BLOCKXPERT_URL . 'includes/assets/css/admin.css';
        if (file_exists($css_path)) {
            wp_enqueue_style(
                'blockxpert-admin',
                $css_url,
                [],
                filemtime($css_path)
            );
        }
    }

    public function sanitize_active_blocks($input) {
        if (!is_array($input)) {
            return [];
        }
        return array_filter(array_map(function ($block) {
            return preg_replace('/[^a-z0-9_-]/', '', strtolower($block));
        }, $input));
    }

    public function save_invoice_settings_ajax() {
        // Check nonce
        if (!check_ajax_referer('blockxpert_invoice_settings', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $settings = [
            'blockxpert_company_name' => sanitize_text_field($_POST['company_name'] ?? ''),
            'blockxpert_company_address' => sanitize_text_field($_POST['company_address'] ?? ''),
            'blockxpert_company_email' => sanitize_email($_POST['company_email'] ?? ''),
            'blockxpert_company_logo' => esc_url_raw($_POST['company_logo'] ?? ''),
            'blockxpert_company_footer' => sanitize_text_field($_POST['footer_text'] ?? ''),
            'blockxpert_invoice_font_size' => sanitize_text_field($_POST['font_size'] ?? '16px'),
            'blockxpert_invoice_primary_color' => sanitize_hex_color($_POST['primary_color'] ?? '#007cba'),
        ];

        foreach ($settings as $key => $value) {
            update_option($key, $value);
        }

        wp_send_json_success([
            'message' => __('Settings saved successfully!', 'blockxpert'),
            'settings' => $settings
        ]);
    }

    /**
     * AJAX handler to generate live invoice preview HTML using the same template as the PDF
     */
    public function ajax_invoice_live_preview() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        // Gather data from POST
        $company = [
            'name' => sanitize_text_field($_POST['company_name'] ?? ''),
            'address' => sanitize_text_field($_POST['company_address'] ?? ''),
            'email' => sanitize_email($_POST['company_email'] ?? ''),
            'logo' => esc_url_raw($_POST['company_logo'] ?? ''),
            'footer' => sanitize_text_field($_POST['footer_text'] ?? ''),
            'font_size' => sanitize_text_field($_POST['font_size'] ?? '16px'),
            'primary_color' => sanitize_hex_color($_POST['primary_color'] ?? '#007cba'),
        ];
        $order = null; // No real order for preview
        // Start output buffering
        ob_start();
        echo '<style>';
        include dirname(__DIR__, 1) . '/pdf-invoice/style-invoice.css';
        echo '</style>';
        // Set CSS variables inline
        echo '<style>:root {';
        echo '--invoice-font-size: ' . esc_attr($company['font_size']) . ';';
        echo '--invoice-primary-color: ' . esc_attr($company['primary_color']) . ';';
        echo '}</style>';
        $template_file = dirname(__DIR__, 1) . '/pdf-invoice/template-basic-content.php';
        if (file_exists($template_file)) {
            include $template_file;
        } else {
            echo '<div style="color:red">Template not found.</div>';
        }
        $html = ob_get_clean();
        wp_send_json_success(['html' => $html]);
    }
} 