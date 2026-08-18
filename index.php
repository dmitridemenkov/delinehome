<?php
/**
 * Fallback template
 * Используется только если не найден специфичный шаблон
 * 
 * ВНИМАНИЕ: Если вы видите этот шаблон - значит в /templates/ 
 * не хватает нужного файла!
 */

// Определяем какой шаблон нужен
$needed_template = 'Неизвестный тип страницы';
$template_file = '';

if (is_single()) {
    $needed_template = 'Одиночная запись (пост)';
    $template_file = 'single.php';
} elseif (is_page()) {
    $needed_template = 'Страница';
    $template_file = 'page.php';
} elseif (is_archive()) {
    if (is_category()) {
        $needed_template = 'Архив категории';
        $template_file = 'archive.php (или category.php)';
    } elseif (is_tag()) {
        $needed_template = 'Архив тега';
        $template_file = 'archive.php (или tag.php)';
    } elseif (is_author()) {
        $needed_template = 'Архив автора';
        $template_file = 'archive.php (или author.php)';
    } elseif (is_date()) {
        $needed_template = 'Архив по дате';
        $template_file = 'archive.php';
    } else {
        $needed_template = 'Архив';
        $template_file = 'archive.php';
    }
} elseif (is_search()) {
    $needed_template = 'Результаты поиска';
    $template_file = 'search.php';
} elseif (is_404()) {
    $needed_template = 'Страница 404';
    $template_file = '404.php';
} elseif (is_home()) {
    $needed_template = 'Главная страница (блог)';
    $template_file = 'front-page.php';
}

// Логируем для разработчиков
if (WP_DEBUG) {
    error_log('INDEX.PHP FALLBACK USED!');
    error_log('Needed template: ' . $needed_template);
    error_log('Expected file: /templates/' . $template_file);
}

get_header();
?>

<main class="site-main py-12 md:py-16 bg-light min-h-screen">
    <div class="container mx-auto px-4">
        <div class="bg-white rounded-lg shadow-lg p-8 md:p-12">
            
            <?php if (WP_DEBUG || current_user_can('manage_options')) : ?>
                <!-- Подсказка для разработчиков -->
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 mb-8">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-yellow-400 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <h3 class="text-lg font-bold text-yellow-800 mb-2">
                                Режим разработки: Шаблон не найден
                            </h3>
                            <p class="text-yellow-700 mb-3">
                                <strong>Тип страницы:</strong> <?php echo $needed_template; ?>
                            </p>
                            <p class="text-yellow-700 mb-4">
                                <strong>Ожидаемый файл:</strong> <code class="bg-yellow-100 px-2 py-1 rounded">/themes/wp-kickstarter/templates/<?php echo $template_file; ?></code>
                            </p>
                            
                            <details class="text-sm">
                                <summary class="cursor-pointer font-semibold text-yellow-800 hover:text-yellow-900 mb-2">
                                    Список доступных шаблонов
                                </summary>
                                <div class="bg-white p-4 rounded mt-2 space-y-2">
                                    <p><code>front-page.php</code> — Главная страница</p>
                                    <p><code>single.php</code> — Одиночная запись (пост)</p>
                                    <p><code>page.php</code> — Страница</p>
                                    <p><code>archive.php</code> — Архивы (категории, теги, даты)</p>
                                    <p><code>search.php</code> — Результаты поиска</p>
                                    <p><code>404.php</code> — Страница "не найдено"</p>
                                    <p class="pt-2 border-t text-gray-600">
                                        <strong>Все шаблоны создаются в:</strong><br>
                                        <code>/themes/wp-kickstarter/templates/</code>
                                    </p>
                                </div>
                            </details>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Страница для обычных пользователей -->
            <div class="text-center">
                <svg class="w-24 h-24 mx-auto text-gray-400 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                
                <h1 class="text-3xl font-bold text-dark mb-4">
                    Упс! Что-то пошло не так
                </h1>
                
                <p class="text-gray-600 mb-6">
                    Шаблон для этой страницы не найден. Пожалуйста, свяжитесь с администратором сайта.
                </p>
                
                <a href="<?php echo home_url(); ?>" class="inline-block bg-primary text-white px-8 py-4 rounded-lg font-semibold hover:bg-primary/90 transition">
                    Вернуться на главную
                </a>
            </div>
        </div>
    </div>
</main>
<?php get_footer(); ?>