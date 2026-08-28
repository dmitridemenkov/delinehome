import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay, Thumbs } from 'swiper/modules';
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

    // Галерея товара: главное фото + лента миниатюр
    const galleryMain = document.querySelector('.product-gallery__main');
    if (galleryMain) {
        const thumbsEl = document.querySelector('.product-gallery__thumbs');
        let thumbsSwiper = null;

        if (thumbsEl) {
            thumbsSwiper = new Swiper(thumbsEl, {
                modules: [Navigation],
                slidesPerView: 3,
                spaceBetween: 12,
                watchSlidesProgress: true,
                navigation: {
                    prevEl: '.product-gallery__nav--prev',
                    nextEl: '.product-gallery__nav--next',
                },
                breakpoints: {
                    768: { slidesPerView: 5, spaceBetween: 12 },
                },
            });
        }

        new Swiper(galleryMain, {
            modules: [Navigation, Thumbs],
            slidesPerView: 1,
            spaceBetween: 0,
            // Swiper падает, если передать уничтоженный инстанс — отдаём только живой
            thumbs: thumbsSwiper ? { swiper: thumbsSwiper } : undefined,
        });
    }

    // Product search: на мобилке первый тап раскрывает поле, второй — отправляет
    const searchForm = document.querySelector('.product-search');
    if (searchForm) {
        const searchBtn = searchForm.querySelector('.product-search__btn');
        const searchInput = searchForm.querySelector('.product-search__input');
        const isMobile = () => window.matchMedia('(max-width: 767px)').matches;

        const closeSearch = () => {
            searchForm.classList.remove('is-open');
            searchBtn.setAttribute('aria-expanded', 'false');
        };

        searchBtn.addEventListener('click', e => {
            if (isMobile() && !searchForm.classList.contains('is-open')) {
                e.preventDefault();
                searchForm.classList.add('is-open');
                searchBtn.setAttribute('aria-expanded', 'true');
                searchInput.focus();
            }
        });

        // Схлопываем обратно, только если ничего не введено
        document.addEventListener('click', e => {
            if (!searchForm.contains(e.target) && !searchInput.value.trim()) {
                closeSearch();
            }
        });

        searchInput.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                closeSearch();
                searchInput.blur();
            }
        });
    }

    // Модальная форма заявки
    const modal = document.getElementById('lead-modal');
    if (modal) {
        const form      = modal.querySelector('.lead-form');
        const titleEl   = modal.querySelector('.modal__title');
        const resultEl  = modal.querySelector('.lead-form__result');
        const submitBtn = modal.querySelector('.lead-form__submit');
        let lastFocused = null;

        const openModal = name => {
            lastFocused = document.activeElement;
            titleEl.textContent = name;
            form.querySelector('[name="form"]').value = name;
            form.querySelector('[name="page_url"]').value = location.href;
            form.querySelector('[name="page_title"]').value = document.title;
            resultEl.textContent = '';
            resultEl.className = 'lead-form__result';
            modal.hidden = false;
            document.body.classList.add('overflow-hidden');
            form.querySelector('[name="name"]').focus();
        };

        const closeModal = () => {
            modal.hidden = true;
            document.body.classList.remove('overflow-hidden');
            if (lastFocused) lastFocused.focus();
        };

        document.querySelectorAll('[data-form-open]').forEach(btn => {
            btn.addEventListener('click', () => openModal(btn.dataset.formOpen));
        });

        modal.querySelectorAll('[data-modal-close]').forEach(el => {
            el.addEventListener('click', closeModal);
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && !modal.hidden) closeModal();
        });

        form.addEventListener('submit', async e => {
            e.preventDefault();

            const name  = form.querySelector('[name="name"]').value.trim();
            const phone = form.querySelector('[name="phone"]').value.trim();
            if (!name || !phone) {
                resultEl.textContent = 'Укажите имя и телефон.';
                resultEl.className = 'lead-form__result is-error';
                return;
            }

            submitBtn.disabled = true;
            resultEl.textContent = 'Отправляем…';
            resultEl.className = 'lead-form__result';

            const data = new FormData(form);
            data.append('action', 'deline_form');
            data.append('nonce', delineForm.nonce);

            try {
                const res = await fetch(delineForm.ajaxUrl, { method: 'POST', body: data });
                const json = await res.json();

                if (json.success) {
                    form.reset();
                    resultEl.textContent = json.data.message;
                    resultEl.className = 'lead-form__result is-ok';
                } else {
                    resultEl.textContent = (json.data && json.data.message) || 'Не удалось отправить заявку.';
                    resultEl.className = 'lead-form__result is-error';
                }
            } catch (err) {
                resultEl.textContent = 'Не удалось отправить заявку. Попробуйте позвонить нам.';
                resultEl.className = 'lead-form__result is-error';
            } finally {
                submitBtn.disabled = false;
            }
        });
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
