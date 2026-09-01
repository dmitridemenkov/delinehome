<?php
/**
 * Выбор параметров вариативного товара.
 *
 * Матчинг вариаций делаем сами, а не через wc-add-to-cart-variation.js:
 * корзины нет, нужны только цена, фото и описание, а скрипт WooCommerce
 * ждёт свою разметку формы и тянет за собой jQuery.
 *
 * @param WC_Product $args['product']
 */
$product = $args['product'] ?? null;
if (!$product || !$product->is_type('variable')) return;

$attributes = $product->get_variation_attributes();
if (empty($attributes)) return;

$defaults = $product->get_default_attributes();

// Отдаём на фронт только то, что реально используется при выборе
$variations = [];
foreach ($product->get_available_variations() as $variation) {
    $variations[] = [
        'attributes'  => $variation['attributes'],
        'price'       => $variation['price_html'] ?: wc_price($variation['display_price']),
        'image'       => [
            'src'    => $variation['image']['src'] ?? '',
            'srcset' => $variation['image']['srcset'] ?? '',
            'sizes'  => $variation['image']['sizes'] ?? '',
            'alt'    => $variation['image']['alt'] ?? '',
        ],
        'description' => $variation['variation_description'] ?? '',
    ];
}
?>

<div class="variations" data-variations="<?php echo esc_attr(wp_json_encode($variations)); ?>">
    <?php foreach ($attributes as $taxonomy => $options):
        if (empty($options)) continue;

        $is_taxonomy = taxonomy_exists($taxonomy);
        $label       = wc_attribute_label($taxonomy, $product);
        $field_name  = 'attribute_' . sanitize_title($taxonomy);
        $selected    = $defaults[sanitize_title($taxonomy)] ?? '';

        // Собираем пункты заранее: нужно знать, есть ли хоть один образец,
        // от этого зависит высота поля
        $items = [];
        foreach ($options as $option) {
            $item = ['value' => $option, 'label' => $option, 'swatch' => ''];

            if ($is_taxonomy) {
                $term = get_term_by('slug', $option, $taxonomy);
                if (!$term) continue;
                $item['label']  = $term->name;
                $item['swatch'] = deline_swatch_markup($term->term_id);
            }

            $items[] = $item;
        }
        if (!$items) continue;

        // Без явного значения по умолчанию берём первое — в макете цена показана сразу
        if ($selected === '' || !in_array($selected, array_column($items, 'value'), true)) {
            $selected = $items[0]['value'];
        }

        $has_swatch  = (bool) array_filter(array_column($items, 'swatch'));
        $current     = null;
        foreach ($items as $item) {
            if ($item['value'] === $selected) { $current = $item; break; }
        }
        $current = $current ?: $items[0];
    ?>
    <div class="variation">
        <span class="variation__label" id="label-<?php echo esc_attr($field_name); ?>"><?php echo esc_html($label); ?></span>

        <div class="vselect<?php echo $has_swatch ? ' vselect--swatch' : ''; ?>" data-attribute="<?php echo esc_attr($field_name); ?>">
            <input type="hidden" name="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr($selected); ?>">

            <button type="button" class="vselect__toggle" aria-haspopup="listbox" aria-expanded="false"
                    aria-labelledby="label-<?php echo esc_attr($field_name); ?>">
                <?php echo $current['swatch']; ?>
                <span class="vselect__value"><?php echo esc_html($current['label']); ?></span>
                <svg class="vselect__chevron" width="18" height="11" viewBox="0 0 18 11" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m1 1 8 8 8-8"/>
                </svg>
            </button>

            <ul class="vselect__list" role="listbox" hidden>
                <?php foreach ($items as $item): ?>
                <li class="vselect__option<?php echo $item['value'] === $selected ? ' is-selected' : ''; ?>"
                    role="option" tabindex="-1"
                    aria-selected="<?php echo $item['value'] === $selected ? 'true' : 'false'; ?>"
                    data-value="<?php echo esc_attr($item['value']); ?>">
                    <?php echo $item['swatch']; ?>
                    <span class="vselect__value"><?php echo esc_html($item['label']); ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endforeach; ?>
</div>
