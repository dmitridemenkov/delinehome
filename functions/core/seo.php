<?php
/**
 * SEO: табы «О Компании», meta description, canonical
 */

/**
 * Страницы-табы раздела «О Компании».
 * Каждая — отдельная страница со своим URL, h1 и title.
 */
function deline_about_tabs() {
    return [
        ['slug' => 'about',     'label' => 'Наше Производство'],
        ['slug' => 'suppliers', 'label' => 'Поставщики Материалов'],
        ['slug' => 'reviews',   'label' => 'Отзывы Клиентов'],
    ];
}

/**
 * Ссылка на страницу по слагу. Пустая строка, если страница не создана.
 */
function deline_page_url($slug) {
    $page = get_page_by_path($slug);
    return $page ? get_permalink($page) : '';
}

/**
 * Микроразметка отзывов (JSON-LD).
 * Даёт звёздный рейтинг в результатах поиска.
 */
function deline_reviews_schema($reviews) {
    if (empty($reviews)) return;

    $items = [];
    $sum   = 0;

    foreach ($reviews as $review) {
        $rating = (int)(get_post_meta($review->ID, '_review_rating', true) ?: 5);
        $name   = get_post_meta($review->ID, '_review_author_name', true) ?: 'Аноним';
        $sum   += $rating;

        $items[] = [
            '@type'         => 'Review',
            'author'        => ['@type' => 'Person', 'name' => $name],
            'reviewRating'  => [
                '@type'       => 'Rating',
                'ratingValue' => $rating,
                'bestRating'  => 5,
                'worstRating' => 1,
            ],
            'reviewBody'    => wp_strip_all_tags($review->post_content),
            'datePublished' => get_the_date('c', $review),
        ];
    }

    $count = count($items);

    $schema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'LocalBusiness',
        'name'            => get_bloginfo('name'),
        'url'             => home_url(),
        'aggregateRating' => [
            '@type'       => 'AggregateRating',
            'ratingValue' => round($sum / $count, 1),
            'reviewCount' => $count,
            'bestRating'  => 5,
            'worstRating' => 1,
        ],
        'review'          => $items,
    ];

    echo '<script type="application/ld+json">'
        . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . '</script>' . "\n";
}

/**
 * Meta description: описание страницы (excerpt) → описание сайта.
 */
add_action('wp_head', function () {
    if (is_singular()) {
        $desc = get_the_excerpt();
    } else {
        $desc = get_bloginfo('description');
    }

    $desc = trim(wp_strip_all_tags($desc));
    if (!$desc) return;

    $desc = wp_trim_words($desc, 30, '');
    echo '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";
}, 1);
