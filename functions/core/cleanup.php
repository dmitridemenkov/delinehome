<?php
// functions/core/cleanup.php

// Убираем лишнее из <head>
add_action('init', function() {
    remove_action('wp_head', 'wp_generator');                // Версия WP
    remove_action('wp_head', 'wlwmanifest_link');           // Windows Live Writer
    remove_action('wp_head', 'rsd_link');                    // Really Simple Discovery
    remove_action('wp_head', 'wp_shortlink_wp_head');       // Короткие ссылки
    remove_action('wp_head', 'rest_output_link_wp_head');   // REST API ссылка
    remove_action('wp_head', 'wp_oembed_add_discovery_links'); // oEmbed
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
});

// Отключаем XML-RPC
add_filter('xmlrpc_enabled', '__return_false');