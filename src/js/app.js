import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';
import GLightbox from 'glightbox';

function supportsAvif() {
    return new Promise(resolve => {
        const img = new Image();
        img.onload = () => resolve(true);
        img.onerror = () => resolve(false);
        img.src = 'data:image/avif;base64,AAAAIGZ0eXBhdmlmAAAAAGF2aWZtaWYxbWlhZk1BMUIAAADybWV0YQAAAAAAAAAoaGRscgAAAAAAAAAAcGljdAAAAAAAAAAAAAAAAGxpYmF2aWYAAAAADnBpdG0AAAAAAAEAAAAeaWxvYwAAAABEAAABAAEAAAABAAABGgAAAB0AAAAoaWluZgAAAAAAAQAAABppbmZlAgAAAAABAABhdjAxQ29sb3IAAAAAamlwcnAAAABLaXBjbwAAABRpc3BlAAAAAAAAAAIAAAACAAAAEHBpeGkAAAAAAwgICAAAAAxhdjFDgQ0MAAAAABNjb2xybmNseAACAAIAAYAAAAAXaXBtYQAAAAAAAAABAAEEAQKDBAAAACVtZGF0EgAKBzgABhAQ0AIy';
    });
}

document.addEventListener('DOMContentLoaded', async () => {
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
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        };

        if (heroEl.dataset.autoplay === '1') {
            opts.autoplay = { delay: 2500, disableOnInteraction: false };
        }

        new Swiper(heroEl, opts);
    }

    // Works slider
    const worksEl = document.querySelector('.works-slider');
    if (worksEl) {
        const d = worksEl.dataset;
        const perMobile  = parseInt(d.perMobile) || 1;
        const perDesktop = parseInt(d.perDesktop) || 3;
        const gapMobile  = parseInt(d.gapMobile) || 16;
        const gapDesktop = parseInt(d.gapDesktop) || 24;

        const worksOpts = {
            modules: [Navigation, Pagination, Autoplay],
            slidesPerView: perMobile,
            spaceBetween: gapMobile,
            navigation: {
                nextEl: '.works-next',
                prevEl: '.works-prev',
            },
            pagination: {
                el: '.works-pagination',
                clickable: true,
            },
            breakpoints: {
                1024: { slidesPerView: perDesktop, spaceBetween: gapDesktop },
            },
        };

        if (d.autoplay === '1') {
            worksOpts.autoplay = { delay: 2500, disableOnInteraction: false };
        }

        new Swiper(worksEl, worksOpts);
    }

    // Reviews slider
    const reviewsEl = document.querySelector('.reviews-slider');
    if (reviewsEl) {
        new Swiper(reviewsEl, {
            modules: [Navigation, Autoplay],
            slidesPerView: 1,
            spaceBetween: 16,
            autoplay: { delay: 2500, disableOnInteraction: false },
            navigation: {
                nextEl: '.reviews-next',
                prevEl: '.reviews-prev',
            },
            breakpoints: {
                1024: { slidesPerView: 2, spaceBetween: 24 },
            },
        });
    }

    // About page tabs
    const tabs = document.querySelectorAll('.about-tab');
    if (tabs.length) {
        function switchTab(tabName) {
            tabs.forEach(t => t.classList.toggle('active', t.dataset.tab === tabName));
            document.querySelectorAll('.about-tab-content').forEach(c => {
                c.classList.toggle('hidden', c.id !== 'tab-' + tabName);
            });
        }

        tabs.forEach(t => t.addEventListener('click', () => switchTab(t.dataset.tab)));

        if (location.hash === '#reviews') switchTab('reviews');
    }

    // Lightbox: swap to avif if supported
    const avif = await supportsAvif();
    if (avif) {
        document.querySelectorAll('.glightbox[data-avif]').forEach(el => {
            el.setAttribute('href', el.dataset.avif);
        });
    }

    GLightbox({ selector: '.glightbox' });
});
