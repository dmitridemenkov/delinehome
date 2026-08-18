<?php
// functions/core/admin.php

// Убираем виджеты с главной
add_action('wp_dashboard_setup', function() {
    remove_meta_box('dashboard_quick_press', 'dashboard', 'side');    // Быстрый черновик
    remove_meta_box('dashboard_primary', 'dashboard', 'side');        // Новости WordPress
    remove_meta_box('dashboard_site_health', 'dashboard', 'normal');  // Здоровье сайта
    remove_meta_box('dashboard_activity', 'dashboard', 'normal');     // Активность
});

// Убираем "Спасибо за использование WordPress"
add_filter('admin_footer_text', '__return_empty_string');