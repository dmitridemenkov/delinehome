import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';
import GLightbox from 'glightbox';

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
    const heroEl = document.querySelector('.hero-slider');
    if (heroEl) {
        const opts = {
            modules: [Navigation, Pagination, Autoplay],
            loop: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-pagination-next',
                prevEl: '.swiper-button-prev',
            },
        };

        if (heroEl.dataset.autoplay === '1') {
            opts.autoplay = { delay: 5000, disableOnInteraction: false };
        }

        new Swiper(heroEl, opts);
    }

    // Works slider
    const worksEl = document.querySelector('.works-slider');
    if (worksEl) {
        new Swiper(worksEl, {
            modules: [Navigation],
            slidesPerView: 1,
            spaceBetween: 16,
            navigation: {
                nextEl: '.works-next',
                prevEl: '.works-prev',
            },
            breakpoints: {
                768: { slidesPerView: 2, spaceBetween: 20 },
                1024: { slidesPerView: 3, spaceBetween: 24 },
            },
        });
    }

    // Lightbox
    GLightbox({ selector: '.glightbox' });
});
