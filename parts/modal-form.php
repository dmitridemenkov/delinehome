<?php
/**
 * Одно модальное окно на страницу. Заголовок подставляется по нажатой кнопке,
 * он же уходит в письмо как название формы.
 */
$privacy_url = get_privacy_policy_url();
?>

<div class="modal" id="lead-modal" hidden>
    <div class="modal__overlay" data-modal-close></div>

    <div class="modal__dialog" role="dialog" aria-modal="true" aria-labelledby="lead-modal-title">
        <button type="button" class="modal__close" data-modal-close aria-label="Закрыть">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <path d="M6 6l12 12M18 6L6 18"/>
            </svg>
        </button>

        <h2 class="modal__title" id="lead-modal-title">Оставить заявку</h2>

        <form class="lead-form" novalidate>
            <input type="hidden" name="form" value="">
            <input type="hidden" name="page_url" value="">
            <input type="hidden" name="page_title" value="">

            <label class="lead-form__field">
                <span>Имя</span>
                <input type="text" name="name" required autocomplete="name" placeholder="Как к вам обращаться">
            </label>

            <label class="lead-form__field">
                <span>Телефон</span>
                <input type="tel" name="phone" required autocomplete="tel" placeholder="+7 (___) ___-__-__">
            </label>

            <label class="lead-form__field">
                <span>Комментарий</span>
                <textarea name="comment" rows="3" placeholder="Необязательно"></textarea>
            </label>

            <!-- Ловушка для ботов: скрыта и от глаз, и от скринридеров -->
            <div class="lead-form__trap" aria-hidden="true">
                <label>Сайт<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <label class="lead-form__consent">
                <input type="checkbox" name="consent" value="1" required>
                <span>
                    Даю согласие на обработку моих персональных данных на условиях и в целях,
                    определённых в настоящей форме согласия, а также подтверждаю ознакомление с
                    <?php if ($privacy_url): ?>
                        <a href="<?php echo esc_url($privacy_url); ?>" target="_blank" rel="noopener">Политикой конфиденциальности</a>.
                    <?php else: ?>
                        Политикой конфиденциальности.
                    <?php endif; ?>
                </span>
            </label>

            <button type="submit" class="lead-form__submit" disabled>Отправить</button>

            <p class="lead-form__result" role="status" aria-live="polite"></p>
        </form>
    </div>
</div>
