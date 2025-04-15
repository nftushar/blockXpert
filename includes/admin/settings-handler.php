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
                'default' => []
            ]
        );
    }

    public static function sanitize_blocks($input)
    {
        if (!is_array($input)) return [];

        $valid_blocks = array_filter(
            scandir(GB_PATH . 'blocks/'),
            function ($item) {
                return $item !== '.' &&
                    $item !== '..' &&
                    is_dir(GB_PATH . 'blocks/' . $item);
            }
        );

        return array_intersect($input, $valid_blocks);
    }
}

Gutenberg_Blocks_Settings::init();
