<?php
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'kickstarter-style',
        get_template_directory_uri() . '/assets/app.css',
        [],
        filemtime(get_template_directory() . '/assets/app.css')
    );
    
    wp_enqueue_script(
        'kickstarter-script',
        get_template_directory_uri() . '/assets/main.js',
        [],
        filemtime(get_template_directory() . '/assets/main.js'),
        true
    );
});