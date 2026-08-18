<footer class="bg-dark text-light mt-auto">
    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Column 1 -->
            <div>
                <h3 class="text-xl font-bold mb-4"><?php bloginfo('name'); ?></h3>
                <p class="text-gray-400">
                    <?php bloginfo('description'); ?>
                </p>
            </div>
            
            <!-- Column 2 -->
            <div>
                <h3 class="text-xl font-bold mb-4">Навигация</h3>
                <?php
                wp_nav_menu([
                    'theme_location' => 'footer',
                    'container' => false,
                    'menu_class' => 'space-y-2',
                    'fallback_cb' => false,
                ]);
                ?>
            </div>
            
            <!-- Column 3 -->
            <div>
                <h3 class="text-xl font-bold mb-4">Контакты</h3>
                <div class="space-y-2 text-gray-400">
                    <p>Email: info@example.com</p>
                    <p>Телефон: +7 (999) 123-45-67</p>
                </div>
            </div>
        </div>
        
        <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
            <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. Все права защищены.</p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>

<script>
// Mobile menu toggle
document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
    const menu = document.getElementById('mobile-menu');
    menu.classList.toggle('hidden');
});
</script>

</body>
</html>