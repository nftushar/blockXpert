<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class BlockXpert_Settings_Handler
{
    public static function init()
    {
        add_action('admin_init', [self::class, 'register_settings']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_media']);
    }

    public static function enqueue_media()
    {
        wp_enqueue_media();
        
        // Enqueue media upload JavaScript
        $media_js_path = BLOCKXPERT_PATH . 'includes/assets/js/media-upload.js';
        $media_js_url = BLOCKXPERT_URL . 'includes/assets/js/media-upload.js';
        if (file_exists($media_js_path)) {
            wp_enqueue_script(
                'blockxpert-media-upload',
                $media_js_url,
                ['jquery', 'media-upload'],
                filemtime($media_js_path),
                true
            );
            
            // Localize script for translations
            wp_localize_script('blockxpert-media-upload', 'blockxpert_media', [
                'select_logo_title' => esc_html__('Select or Upload Logo', 'BlockXpert'),
                'use_logo_text' => esc_html__('Use this logo', 'BlockXpert')
            ]);
        }
    }

    public static function register_settings()
    {
        register_setting(
            'blockxpert_settings_handler',
            'blockxpert_blocks_active',
            [
                'type' => 'array',
                'sanitize_callback' => [self::class, 'sanitize_blocks'],
                'default' => self::get_default_blocks()
            ]
        );


    }

    public static function sanitize_blocks($input)
    {
        if (!is_array($input)) {
            return [];
        }

        $valid_blocks = [];
        $blocks_dir = BLOCKXPERT_PATH . 'blocks/';

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
BlockXpert_Settings_Handler::init();
