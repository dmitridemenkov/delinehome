<?php $site = deline_get_settings(); ?>

<footer class="mt-auto">
    <div class="bg-[#272727] mt-6 lg:mt-12 py-6 lg:py-12">

        <div class="container mx-auto px-3">
            <div class="flex items-start justify-between">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <div class="flex items-center">
                        <div class="flex items-center">
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
                <!-- Navigation -->
                <nav class="footer-nav">
                    <?php
                    wp_nav_menu([
                        'theme_location' => 'primary',
                        'container' => false,
                        'menu_class' => 'columns-1 md:columns-2 lg:columns-3 gap gap-3',
                        'fallback_cb' => false,
                    ]);
                    ?>
                </nav>
                <div class="flex gap-4 lg:gap-6 flex-col justify-center">
                    <?php if (!empty($site['contacts'])): ?>
                        <!-- Contacts -->
                        <div class="flex items-center justify-between flex-wrap gap-[12px] xl:gap-[44px] border-right">
                            <?php foreach ($site['contacts'] as $contact):
                                $icon_url = $contact['icon_id'] ? wp_get_attachment_url($contact['icon_id']) : '';
                            ?>
                                <a href="<?php echo esc_url($contact['url']); ?>" title="<?php echo esc_attr($contact['label']); ?>"
                                    class="flex gap-[6px] xl:gap-[18px] items-center transition hover:-translate-y-[2px]">
                                    <?php if ($icon_url): ?>
                                        <div>
                                            <img src="<?php echo esc_url($icon_url); ?>"
                                                alt="<?php echo esc_attr($contact['label']); ?>"
                                                title="<?php echo esc_attr($contact['label']); ?>"
                                                class="w-[18px] h-[18px] md:w-[28px] md:h-[28px]">
                                        </div>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($site['phone'] || $site['address']): ?>
                        <!-- Phone & Address -->
                        <div
                            class="flex items-center justify-between">
                            <a href="tel:<?php echo esc_attr(preg_replace('/[^\d+]/', '', $site['phone'])); ?>"
                                title="Телефон"
                                class="flex gap-[6px] xl:gap-[18px] items-center transition hover:-translate-y-[2px]">
                                <div>
                                    <svg width="28" height="28" viewBox="0 0 28 28" fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="w-[18px] h-[18px] md:w-[28px] md:h-[28px]">
                                        <path
                                            d="M5.63111 12.1178C7.87111 16.52 11.48 20.1133 15.8822 22.3689L19.3044 18.9467C19.7244 18.5267 20.3467 18.3867 20.8911 18.5733C22.6333 19.1489 24.5156 19.46 26.4444 19.46C27.3 19.46 28 20.16 28 21.0156V26.4444C28 27.3 27.3 28 26.4444 28C11.8378 28 0 16.1622 0 1.55556C0 0.7 0.7 0 1.55556 0H7C7.85556 0 8.55555 0.7 8.55555 1.55556C8.55555 3.5 8.86667 5.36667 9.44222 7.10889C9.61333 7.65333 9.48889 8.26 9.05333 8.69556L5.63111 12.1178Z"
                                            fill="white" />
                                    </svg>
                                </div>
                                <div class="hidden md:inline-block">
                                    <?php if ($site['phone']): ?>
                                        <div class="phone text-white text-xs"><?php echo esc_html($site['phone']); ?></div>
                                    <?php endif; ?>
                                    <?php if ($site['address']): ?>
                                        <div class="address text-white text-xs"><?php echo esc_html($site['address']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="text-center text-white text-xs lg:text-base mt-6 lg:mt-10">
                Отправляя любую форму на сайте, вы соглашаетесь <a href="/" title="Ознакомиться с Политикой конфиденциальности" class="font-bold transition hover:text-[#216CC3]">с политикой конфиденциальности</a> данного сайта
            </div>
            <div class="flex justify-between gap-4">
                <div class="text-white text-xs">
                    ООО "ПРОФТАНДЕМ-СЕРВИС"<br />
                    ИНН: 4345237431<br />
                </div>
                <div>
                    <a href="http://webmaster-kirov.ru" title="создание сайта Киров, раскрутка сайта Киров" target="_blank">
                            <img src="/wp-content/uploads/2026/08/webmaster.svg" title="Вебмастер - разработка и продвижение сайтов г. Киров" alt="Вебмастер - разработка и продвижение сайтов г. Киров">
                        </a>
                </div>
            </div>
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