document.addEventListener('DOMContentLoaded', () => {
    const burger = document.getElementById('burger-toggle');
    const menu = document.getElementById('mobile-menu');
    if (!burger || !menu) return;

    burger.addEventListener('click', () => {
        const isOpen = burger.classList.toggle('is-active');
        menu.classList.toggle('is-open', isOpen);
        burger.setAttribute('aria-expanded', isOpen);
        document.body.classList.toggle('overflow-hidden', isOpen);
    });
});
