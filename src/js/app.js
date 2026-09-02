import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay, Thumbs } from 'swiper/modules';
import GLightbox from 'glightbox';

// Маска телефона: +7 (999) 123-45-67
function formatPhone(value) {
    let d = value.replace(/\D/g, '');
    if (!d) return '';
    // 8 в начале — привычный способ набора, приводим к 7
    if (d[0] === '8') d = '7' + d.slice(1);
    if (d[0] !== '7') d = '7' + d;

    const n = d.slice(1, 11);
    let out = '+7';
    if (n.length) out += ' (' + n.slice(0, 3);
    if (n.length > 3) out += ') ' + n.slice(3, 6);
    if (n.length > 6) out += '-' + n.slice(6, 8);
    if (n.length > 8) out += '-' + n.slice(8, 10);
    return out;
}

function initPhoneMask(input) {
    let prevDigits = '';

    input.addEventListener('input', e => {
        let digits = input.value.replace(/\D/g, '');

        // Стёрли символ форматирования — цифры не изменились, снимаем ещё одну,
        // иначе маска тут же вернёт скобку и удалить ничего не получится
        if (e.inputType === 'deleteContentBackward' && digits === prevDigits) {
            digits = digits.slice(0, -1);
        }

        const caretDigits = input.value.slice(0, input.selectionStart).replace(/\D/g, '').length;
        const formatted = formatPhone(digits);
        input.value = formatted;

        // Возвращаем каретку после того же по счёту разряда
        let seen = 0, caret = formatted.length;
        for (let i = 0; i < formatted.length; i++) {
            if (/\d/.test(formatted[i])) seen++;
            if (seen === caretDigits) { caret = i + 1; break; }
        }
        input.setSelectionRange(caret, caret);

        prevDigits = input.value.replace(/\D/g, '');
    });

    input.addEventListener('focus', () => {
        if (!input.value) input.value = '+7 (';
    });

    input.addEventListener('blur', () => {
        // Ушли, ничего не набрав — не оставляем болтаться заготовку
        if (input.value.replace(/\D/g, '').length <= 1) input.value = '';
    });
}

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

    // Вариативный товар: свои выпадашки с образцами + пересчёт цены и фото
    const variationsRoot = document.querySelector('.variations');
    if (variationsRoot) {
        let variations = [];
        try { variations = JSON.parse(variationsRoot.dataset.variations || '[]'); } catch (e) { variations = []; }

        const selects = [...variationsRoot.querySelectorAll('.vselect')];
        const priceEl = document.querySelector('.product-price__value');
        const featuresEl = document.querySelector('.product-summary__features');
        const mainImg = document.querySelector('.product-gallery__main .swiper-slide img');
        const baseFeatures = featuresEl ? featuresEl.innerHTML : '';

        const closeAll = except => {
            selects.forEach(sel => {
                if (sel === except) return;
                sel.querySelector('.vselect__list').hidden = true;
                sel.querySelector('.vselect__toggle').setAttribute('aria-expanded', 'false');
                sel.classList.remove('is-open');
            });
        };

        const currentChoice = () => {
            const chosen = {};
            selects.forEach(sel => {
                chosen[sel.dataset.attribute] = sel.querySelector('input[type="hidden"]').value;
            });
            return chosen;
        };

        const findVariation = chosen => variations.find(v =>
            Object.entries(chosen).every(([name, value]) => {
                const has = v.attributes[name];
                // Пустое значение у вариации означает «любой вариант»
                return !has || has === value;
            })
        );

        const apply = () => {
            const match = findVariation(currentChoice());
            if (!match) return;

            if (priceEl && match.price) priceEl.innerHTML = match.price;

            if (mainImg && match.image.src) {
                // srcset и sizes выставляем всегда, даже пустыми: при наличии srcset
                // браузер выбирает картинку из него и подменённый src игнорирует
                mainImg.srcset = match.image.srcset || '';
                mainImg.sizes = match.image.sizes || '';
                mainImg.src = match.image.src;
                if (match.image.alt) mainImg.alt = match.image.alt;

                // Ссылка лайтбокса должна вести на фото выбранного варианта
                const lightbox = mainImg.closest('a.glightbox');
                if (lightbox && (match.image.full || match.image.src)) {
                    lightbox.href = match.image.full || match.image.src;
                }
            }

            if (featuresEl) {
                featuresEl.innerHTML = match.description ? match.description : baseFeatures;
            }
        };

        selects.forEach(sel => {
            const toggle = sel.querySelector('.vselect__toggle');
            const list   = sel.querySelector('.vselect__list');
            const input  = sel.querySelector('input[type="hidden"]');

            toggle.addEventListener('click', () => {
                const willOpen = list.hidden;
                closeAll(sel);
                list.hidden = !willOpen;
                sel.classList.toggle('is-open', willOpen);
                toggle.setAttribute('aria-expanded', String(willOpen));
            });

            list.addEventListener('click', e => {
                const option = e.target.closest('.vselect__option');
                if (!option) return;

                input.value = option.dataset.value;

                list.querySelectorAll('.vselect__option').forEach(o => {
                    const active = o === option;
                    o.classList.toggle('is-selected', active);
                    o.setAttribute('aria-selected', String(active));
                });

                // Переносим содержимое пункта в кнопку вместе с образцом
                toggle.innerHTML = option.innerHTML + toggle.querySelector('.vselect__chevron').outerHTML;

                list.hidden = true;
                sel.classList.remove('is-open');
                toggle.setAttribute('aria-expanded', 'false');
                apply();
            });
        });

        document.addEventListener('click', e => {
            if (!e.target.closest('.vselect')) closeAll(null);
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeAll(null);
        });

        apply();
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
        const phoneEl   = form.querySelector('[name="phone"]');
        const consentEl = form.querySelector('[name="consent"]');
        let lastFocused = null;

        initPhoneMask(phoneEl);

        // Кнопка неактивна, пока не отмечено согласие
        const syncConsent = () => { submitBtn.disabled = !consentEl.checked; };
        consentEl.addEventListener('change', syncConsent);
        syncConsent();

        const openModal = name => {
            lastFocused = document.activeElement;
            titleEl.textContent = name;
            form.querySelector('[name="form"]').value = name;
            form.querySelector('[name="page_url"]').value = location.href;
            form.querySelector('[name="page_title"]').value = document.title;
            resultEl.textContent = '';
            resultEl.className = 'lead-form__result';
            syncConsent();
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

            const name = form.querySelector('[name="name"]').value.trim();
            if (!name) {
                resultEl.textContent = 'Укажите имя.';
                resultEl.className = 'lead-form__result is-error';
                return;
            }
            if (phoneEl.value.replace(/\D/g, '').length !== 11) {
                resultEl.textContent = 'Введите телефон полностью.';
                resultEl.className = 'lead-form__result is-error';
                phoneEl.focus();
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
                // reset() снимает галочку согласия — кнопка должна снова стать неактивной
                syncConsent();
            }
        });
    }

    // Уведомление о cookie
    const cookieNotice = document.getElementById('cookie-notice');
    if (cookieNotice) {
        // Версия в ключе: если поменяется политика, баннер покажется заново
        const KEY = 'deline_cookie_consent_v1';
        let accepted = false;

        // В приватном режиме обращение к localStorage может бросить исключение
        try { accepted = localStorage.getItem(KEY) === '1'; } catch (e) { accepted = false; }

        if (!accepted) {
            cookieNotice.hidden = false;
            document.getElementById('cookie-accept')?.addEventListener('click', () => {
                try { localStorage.setItem(KEY, '1'); } catch (e) { /* не критично */ }
                cookieNotice.hidden = true;
            });
        }
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
