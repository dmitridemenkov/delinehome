<?php
/**
 * Базовая настройка темы
 */

// Регистрация меню
add_action('after_setup_theme', function() {
    register_nav_menus([
        'primary' => 'Основное меню',
        'footer'  => 'Меню в футере',
    ]);
    
    // Поддержка заголовков страниц
    add_theme_support('title-tag');
    
    // Поддержка миниатюр
    add_theme_support('post-thumbnails');
    
    // Размеры изображений
    add_image_size('hero', 1920, 1080, true);
    add_image_size('card', 600, 400, true);
    add_image_size('thumbnail-large', 400, 400, true);
    
    // HTML5 поддержка
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ]);
});

// Отключаем комментарии полностью
add_action('admin_menu', function() {
    remove_menu_page('edit-comments.php');
});

add_action('admin_bar_menu', function($wp_admin_bar) {
    $wp_admin_bar->remove_node('comments');
}, 999);

add_action('init', function() {
    remove_post_type_support('post', 'comments');
    remove_post_type_support('page', 'comments');
});

add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);
add_filter('comments_array', '__return_empty_array', 10, 2);