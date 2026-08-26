<?php
/**
 * Табы раздела «О Компании» — обычные ссылки на отдельные страницы.
 * Активный таб определяется по текущей странице.
 */
$current_id = get_the_ID();
?>

<nav class="about-tabs" aria-label="Разделы о компании">
    <?php foreach (deline_about_tabs() as $tab):
        $page = get_page_by_path($tab['slug']);
        if (!$page) continue;

        $is_active = ($page->ID === $current_id);
    ?>
    <a href="<?php echo esc_url(get_permalink($page)); ?>"
       class="about-tab<?php echo $is_active ? ' active' : ''; ?>"
       <?php if ($is_active): ?>aria-current="page"<?php endif; ?>
       title="<?php echo esc_attr($tab['label']); ?>"><span><?php echo esc_html($tab['label']); ?></span></a>
    <?php endforeach; ?>
</nav>
