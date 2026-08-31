<?php
/**
 * Разделы страницы «Наше производство»: заголовок, текст, группа фотографий.
 * Панорамные кадры занимают строку целиком, остальные — половину.
 */
$sections = deline_get_production();
if (empty($sections)) return;
?>

<div class="production">
    <?php foreach ($sections as $section):
        $photos = $section['photos'] ?? [];
        $title  = $section['title'] ?? '';
        $text   = $section['text'] ?? '';
    ?>
    <section class="production-section">
        <?php if ($title): ?>
        <h2 class="production-section__title"><?php echo esc_html($title); ?></h2>
        <?php endif; ?>

        <?php if (trim(wp_strip_all_tags($text))): ?>
        <div class="production-section__text page-content"><?php echo wp_kses_post($text); ?></div>
        <?php endif; ?>

        <?php if ($photos): ?>
        <div class="production-grid">
            <?php foreach ($photos as $photo):
                $img_url = wp_get_attachment_url($photo['image_id']);
                if (!$img_url) continue;

                $avif_url = ($photo['avif_id'] ?? 0) ? wp_get_attachment_url($photo['avif_id']) : '';
                $full_url = wp_get_attachment_image_url($photo['image_id'], 'full');
                $caption  = $photo['caption'] ?? '';
                $alt      = $caption ?: ($title ?: 'Наше производство');
                $layout   = deline_production_layout($photo);
            ?>
            <a href="<?php echo esc_url($full_url ?: $img_url); ?>"
               class="production-item production-item--<?php echo esc_attr($layout); ?> glightbox"
               data-gallery="production"
               <?php if ($avif_url): ?>data-avif="<?php echo esc_url($avif_url); ?>"<?php endif; ?>
               title="<?php echo esc_attr($alt); ?>">
                <picture>
                    <?php if ($avif_url): ?>
                    <source srcset="<?php echo esc_url($avif_url); ?>" type="image/avif">
                    <?php endif; ?>
                    <img src="<?php echo esc_url($img_url); ?>"
                         alt="<?php echo esc_attr($alt); ?>"
                         title="<?php echo esc_attr($alt); ?>"
                         loading="lazy">
                </picture>
                <?php if ($caption): ?>
                <span class="production-item__caption"><?php echo esc_html($caption); ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>
    <?php endforeach; ?>
</div>
