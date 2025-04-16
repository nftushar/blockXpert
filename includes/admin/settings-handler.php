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
