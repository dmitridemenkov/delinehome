<?php
/**
 * Переопределение иерархии шаблонов WordPress
 * Все шаблоны ищем в папке /templates/
 */

add_filter('template_include', function($template) {
    // Если это главная страница (независимо от настроек)
    if (is_front_page() || (is_home() && !is_front_page())) {
        $front_page = get_template_directory() . '/templates/front-page.php';
        if (file_exists($front_page)) {
            return $front_page;
        }
    }
    
    // 404
    if (is_404()) {
        $error_page = get_template_directory() . '/templates/404.php';
        if (file_exists($error_page)) {
            return $error_page;
        }
    }
    
    // Одиночная запись
    if (is_single()) {
        $single = get_template_directory() . '/templates/single.php';
        if (file_exists($single)) {
            return $single;
        }
    }
    
    // Страница
    if (is_page()) {
        $page = get_template_directory() . '/templates/page.php';
        if (file_exists($page)) {
            return $page;
        }
    }
    
    // Архив
    if (is_archive()) {
        $archive = get_template_directory() . '/templates/archive.php';
        if (file_exists($archive)) {
            return $archive;
        }
    }
    
    // Поиск
    if (is_search()) {
        $search = get_template_directory() . '/templates/search.php';
        if (file_exists($search)) {
            return $search;
        }
    }
    
    // Если ничего не подошло - стандартный шаблон
    return $template;
}, 99);