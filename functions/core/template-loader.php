<?php
/**
 * Переопределение иерархии шаблонов WordPress
 * Все шаблоны ищем в папке /templates/
 */

add_filter('template_include', function($template) {
    // Страницы WooCommerce отдаём ему самому: иначе is_single() перехватит
    // карточку товара, а is_archive() — архив категории
    if (function_exists('is_woocommerce') && (
        is_woocommerce() || is_cart() || is_checkout() || is_account_page()
    )) {
        return $template;
    }

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
    
    // Страница — но не трогаем шаблон, выбранный в атрибутах страницы
    if (is_page() && !get_page_template_slug()) {
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