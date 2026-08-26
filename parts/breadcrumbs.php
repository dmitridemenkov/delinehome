<?php
/**
 * Хлебные крошки.
 * @param string $args['title'] — название текущей страницы
 */
$title = $args['title'] ?? get_the_title();
?>

<nav class="text-sm mb-6" aria-label="Хлебные крошки">
    <a href="<?php echo home_url(); ?>" class="text-[#BFBFBF] hover:text-black transition" title="Главная">Главная</a>
    <span class="text-[#BFBFBF]"> / </span>
    <span class="text-black font-medium"><?php echo esc_html($title); ?></span>
</nav>
