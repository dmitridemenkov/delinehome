import Swiper from 'swiper';
import { Navigation, Pagination } from 'swiper/modules';

document.addEventListener('DOMContentLoaded', () => {
    // Burger menu
    const burger = document.getElementById('burger-toggle');
    const menu = document.getElementById('mobile-menu');
    if (burger && menu) {
        burger.addEventListener('click', () => {
            const isOpen = burger.classList.toggle('is-active');
            menu.classList.toggle('is-open', isOpen);
            burger.setAttribute('aria-expanded', isOpen);
            document.body.classList.toggle('overflow-hidden', isOpen);
        });
    }

    // Hero slider
    const sliderEl = document.querySelector('.hero-slider');
    if (sliderEl) {
        new Swiper(sliderEl, {
            modules: [Navigation, Pagination],
            loop: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });
    }
});
