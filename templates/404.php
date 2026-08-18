<?php
/**
 * Шаблон страницы 404
 */

get_header();
?>

<main class="site-main py-12 md:py-24 bg-light min-h-screen flex items-center">
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto text-center">
            
            <!-- Большая 404 -->
            <div class="mb-8">
                <h1 class="text-[150px] md:text-[200px] font-bold text-primary/20 leading-none">
                    404
                </h1>
            </div>
            
            <!-- Иконка -->
            <svg class="w-24 h-24 mx-auto text-gray-400 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            
            <!-- Заголовок -->
            <h2 class="text-3xl md:text-5xl font-bold text-dark mb-6">
                Страница не найдена
            </h2>
            
            <!-- Описание -->
            <p class="text-xl text-gray-600 mb-8">
                К сожалению, страница которую вы ищете не существует или была перемещена.
            </p>
            
            <!-- Кнопки -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12">
                <a href="<?php echo home_url(); ?>" 
                   class="bg-primary text-white px-8 py-4 rounded-lg font-semibold hover:bg-primary/90 transition">
                    Вернуться на главную
                </a>
                
                <button onclick="history.back()" 
                        class="bg-white text-dark border-2 border-gray-300 px-8 py-4 rounded-lg font-semibold hover:bg-gray-50 transition">
                    Назад
                </button>
            </div>
            
            <!-- Поиск -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h3 class="text-xl font-bold text-dark mb-4">
                    Или попробуйте найти то, что ищете:
                </h3>
                <?php get_search_form(); ?>
            </div>
            
        </div>
    </div>
</main>

<?php get_footer(); ?>