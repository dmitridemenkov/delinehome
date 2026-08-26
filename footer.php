<footer class="bg-[#272727] mt-6 lg:mt-12">
    <div class="container mx-auto px-3">
        <div class="flex items-start justify-between">
            <!-- Logo -->
            <div class="flex-shrink-0">
                <div class="flex items-center">
                    <div class="flex items-center lg:border-r border-white lg:pr-4 lg:me-4 w-[90px] sm:w-auto">
                        <a href="<?php echo home_url(); ?>"
                            class="inline-block w-[122px] bg-white rounded-[8px] py-[4px] px-[8px] transition hover:-translate-y-[2px]"
                            title="Перейти на Главную">
                            <?php if ($site['logo_id'] && $logo_url = wp_get_attachment_url($site['logo_id'])): ?>
                                <img src="<?php echo esc_url($logo_url); ?>"
                                    alt="<?php echo esc_attr($site['logo_alt']); ?>"
                                    title="<?php echo esc_attr($site['logo_title']); ?>">
                            <?php else: ?>
                                <span class="text-2xl font-bold text-primary">
                                    <?php bloginfo('name'); ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</footer>

<?php wp_footer(); ?>

<script>
    // Mobile menu toggle
    document.getElementById('mobile-menu-btn')?.addEventListener('click', function () {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
    });
</script>

</body>

</html>