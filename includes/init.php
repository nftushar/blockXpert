<?php
// Deprecated: All BlockXpert logic has been refactored into classes in includes/classes/.
// This file is kept for legacy reference only.

add_action('wp_enqueue_scripts', function() {
    // Example for block-one
    wp_enqueue_style(
        'blockxpert-block-one-style',
        plugins_url('build/block-one/style-index.css', __FILE__),
        [],
        filemtime(__DIR__ . '/build/block-one/style-index.css')
    );
    // Repeat for each block as needed...
});
