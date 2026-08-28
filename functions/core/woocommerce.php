<?php
/**
 * Настройки WooCommerce под тему.
 */

// В категории показываем все товары сразу, без пагинации
add_filter('loop_shop_per_page', function () {
    return -1;
}, 20);
