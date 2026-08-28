<?php
/**
 * Уведомление об обработке cookie.
 * Скрыто по умолчанию — показывает его JS, если согласие ещё не дано.
 * Так баннер не мигает на каждой загрузке у тех, кто уже согласился.
 */
$privacy_url = get_privacy_policy_url();
?>

<div class="cookie-notice" id="cookie-notice" role="region" aria-label="Уведомление об использовании cookie" hidden>
    <p class="cookie-notice__text">
        Продолжая пользоваться настоящим сайтом вы выражаете свое согласие на обработку файлов cookie.
        Порядок обработки персональных данных и требование по их защите описаны в
        <?php if ($privacy_url): ?>
            <a href="<?php echo esc_url($privacy_url); ?>">политике, в отношении обработки персональных данных</a>.
        <?php else: ?>
            политике, в отношении обработки персональных данных.
        <?php endif; ?>
    </p>

    <button type="button" class="cookie-notice__btn" id="cookie-accept">Принять</button>
</div>
