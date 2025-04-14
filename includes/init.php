<?php
class Gutenberg_Blocks_Init {
    public function __construct() {
        add_action('init', [$this, 'register_blocks']);
        add_filter('block_categories_all', [$this, 'add_block_category']);
    }

    public function register_blocks() {
        $this->register_block('block-one');
        $this->register_block('block-two');
        $this->register_block('block-three');
    }

    private function register_block($block_name) {
        $block_path = GB_PATH . "blocks/{$block_name}";
        if (file_exists("{$block_path}/block.json")) {
            register_block_type($block_path);
        }
    }

    public function add_block_category($categories) {
        return array_merge($categories, [[
            'slug' => 'custom-blocks',
            'title' => __('Custom Blocks', 'gutenberg-blocks')
        ]]);
    }
}

new Gutenberg_Blocks_Init();