<?php
/**
 * Образцы для значений атрибутов WooCommerce: картинка текстуры или плашка цвета.
 * Хранятся в мете термина, поэтому задаются один раз и работают во всех товарах.
 */

function deline_swatch_taxonomies() {
    if (!function_exists('wc_get_attribute_taxonomies')) return [];

    $taxonomies = [];
    foreach (wc_get_attribute_taxonomies() as $attribute) {
        $taxonomies[] = wc_attribute_taxonomy_name($attribute->attribute_name);
    }
    return $taxonomies;
}

add_action('admin_init', function () {
    foreach (deline_swatch_taxonomies() as $taxonomy) {
        add_action("{$taxonomy}_add_form_fields", 'deline_swatch_add_fields');
        add_action("{$taxonomy}_edit_form_fields", 'deline_swatch_edit_fields');
        add_filter("manage_edit-{$taxonomy}_columns", 'deline_swatch_column');
        add_filter("manage_{$taxonomy}_custom_column", 'deline_swatch_column_content', 10, 3);
    }
});

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'edit-tags.php' && $hook !== 'term.php') return;

    $taxonomy = $_GET['taxonomy'] ?? '';
    if (!in_array($taxonomy, deline_swatch_taxonomies(), true)) return;

    wp_enqueue_media();
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
});

function deline_swatch_get($term_id) {
    return [
        'image_id' => (int) get_term_meta($term_id, 'deline_swatch_image', true),
        'color'    => (string) get_term_meta($term_id, 'deline_swatch_color', true),
    ];
}

/**
 * Что показывать образцом: приоритет у картинки, затем цвет.
 */
function deline_swatch_markup($term_id, $class = 'vselect__swatch') {
    $swatch = deline_swatch_get($term_id);

    if ($swatch['image_id']) {
        $url = wp_get_attachment_image_url($swatch['image_id'], 'thumbnail');
        if ($url) {
            return sprintf('<span class="%s"><img src="%s" alt="" aria-hidden="true" loading="lazy"></span>',
                esc_attr($class), esc_url($url));
        }
    }

    if ($swatch['color']) {
        return sprintf('<span class="%s" style="background:%s"></span>',
            esc_attr($class), esc_attr($swatch['color']));
    }

    return '';
}

function deline_swatch_fields_markup($image_id = 0, $color = '') {
    $preview = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : '';
    ?>
    <div class="deline-swatch-fields">
        <p>
            <label style="display:block;margin-bottom:6px;font-weight:600;">Образец — картинка</label>
            <span class="deline-swatch-preview" style="display:inline-flex;align-items:center;justify-content:center;width:60px;height:60px;border:1px dashed #c3c4c7;border-radius:6px;overflow:hidden;background:#f9f9f9;vertical-align:middle;">
                <?php if ($preview): ?><img src="<?php echo esc_url($preview); ?>" style="max-width:100%;max-height:100%;object-fit:cover;"><?php endif; ?>
            </span>
            <input type="hidden" name="deline_swatch_image" class="deline-swatch-image" value="<?php echo esc_attr($image_id); ?>">
            <button type="button" class="button deline-swatch-upload" style="margin-inline-start:8px;">Выбрать</button>
            <button type="button" class="button deline-swatch-clear">Убрать</button>
        </p>
        <p>
            <label style="display:block;margin-bottom:6px;font-weight:600;">Образец — цвет</label>
            <input type="text" name="deline_swatch_color" class="deline-swatch-color" value="<?php echo esc_attr($color); ?>" data-default-color="">
            <span class="description" style="display:block;margin-top:6px;">
                Используется, если картинка не выбрана. Для текстур берите картинку, для однотонных фасадов — цвет.
            </span>
        </p>
    </div>

    <script>
    jQuery(function($) {
        var $root = $('.deline-swatch-fields').last();

        if ($.fn.wpColorPicker) $root.find('.deline-swatch-color').wpColorPicker();

        $root.on('click', '.deline-swatch-upload', function(e) {
            e.preventDefault();
            var frame = wp.media({ multiple: false });
            frame.on('select', function() {
                var att = frame.state().get('selection').first().toJSON();
                var thumb = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
                $root.find('.deline-swatch-image').val(att.id);
                $root.find('.deline-swatch-preview').html('<img src="' + thumb + '" style="max-width:100%;max-height:100%;object-fit:cover;">');
            });
            frame.open();
        });

        $root.on('click', '.deline-swatch-clear', function(e) {
            e.preventDefault();
            $root.find('.deline-swatch-image').val('');
            $root.find('.deline-swatch-preview').empty();
        });
    });
    </script>
    <?php
}

function deline_swatch_add_fields() {
    echo '<div class="form-field">';
    deline_swatch_fields_markup();
    echo '</div>';
}

function deline_swatch_edit_fields($term) {
    $swatch = deline_swatch_get($term->term_id);
    echo '<tr class="form-field"><th scope="row">Образец</th><td>';
    deline_swatch_fields_markup($swatch['image_id'], $swatch['color']);
    echo '</td></tr>';
}

function deline_save_swatch($term_id) {
    // Поля приходят только с формы термина, у которой свой nonce-контроль в ядре
    if (isset($_POST['deline_swatch_image'])) {
        $image_id = absint($_POST['deline_swatch_image']);
        $image_id
            ? update_term_meta($term_id, 'deline_swatch_image', $image_id)
            : delete_term_meta($term_id, 'deline_swatch_image');
    }

    if (isset($_POST['deline_swatch_color'])) {
        $color = sanitize_hex_color($_POST['deline_swatch_color']);
        $color
            ? update_term_meta($term_id, 'deline_swatch_color', $color)
            : delete_term_meta($term_id, 'deline_swatch_color');
    }
}
add_action('created_term', 'deline_save_swatch');
add_action('edited_term', 'deline_save_swatch');

function deline_swatch_column($columns) {
    $new = [];
    foreach ($columns as $key => $label) {
        $new[$key] = $label;
        if ($key === 'cb') {
            $new['deline_swatch'] = 'Образец';
        }
    }
    return $new;
}

function deline_swatch_column_content($content, $column, $term_id) {
    if ($column !== 'deline_swatch') return $content;

    $swatch = deline_swatch_get($term_id);

    if ($swatch['image_id'] && $url = wp_get_attachment_image_url($swatch['image_id'], 'thumbnail')) {
        return sprintf('<img src="%s" style="width:36px;height:36px;object-fit:cover;border-radius:6px;">', esc_url($url));
    }
    if ($swatch['color']) {
        return sprintf('<span style="display:inline-block;width:36px;height:36px;border-radius:6px;border:1px solid #dcdcde;background:%s"></span>', esc_attr($swatch['color']));
    }
    return '—';
}
