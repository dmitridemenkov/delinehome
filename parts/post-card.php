<?php
/**
 * Карточка записи в списках: архив, поиск.
 */
$thumb_id = get_post_thumbnail_id();
?>

<a href="<?php the_permalink(); ?>" class="post-card" title="<?php the_title_attribute(); ?>">
    <?php if ($thumb_id): ?>
    <div class="post-card__media">
        <?php the_post_thumbnail('medium_large', [
            'alt'     => the_title_attribute(['echo' => false]),
            'loading' => 'lazy',
        ]); ?>
    </div>
    <?php endif; ?>

    <span class="post-card__date"><?php echo esc_html(get_the_date()); ?></span>
    <span class="post-card__title"><?php the_title(); ?></span>

    <?php if ($excerpt = get_the_excerpt()): ?>
    <span class="post-card__excerpt"><?php echo esc_html(wp_trim_words($excerpt, 20)); ?></span>
    <?php endif; ?>
</a>
