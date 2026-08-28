<?php
/**
 * Хлебные крошки.
 *
 * @param string $args['title'] — название текущей страницы
 * @param array  $args['items'] — промежуточные звенья между «Главная» и текущей
 *                                страницей: [['label' => '…', 'url' => '…'], …]
 */
$title = $args['title'] ?? get_the_title();
$items = $args['items'] ?? [];
?>

<nav class="mt-4 lg:mt-0 text-sm mb-6" aria-label="Хлебные крошки">
    <a href="<?php echo home_url(); ?>" class="text-[#BFBFBF] hover:text-black transition" title="Главная">Главная</a>
    <?php foreach ($items as $item):
        if (empty($item['label'])) continue;
    ?>
        <span class="text-[#BFBFBF]"> / </span>
        <?php if (!empty($item['url'])): ?>
            <a href="<?php echo esc_url($item['url']); ?>"
               class="text-[#BFBFBF] hover:text-black transition"
               title="<?php echo esc_attr($item['label']); ?>"><?php echo esc_html($item['label']); ?></a>
        <?php else: ?>
            <span class="text-[#BFBFBF]"><?php echo esc_html($item['label']); ?></span>
        <?php endif; ?>
    <?php endforeach; ?>
    <span class="text-[#BFBFBF]"> / </span>
    <span class="text-black font-medium"><?php echo esc_html($title); ?></span>
</nav>
