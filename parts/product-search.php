<?php
/**
 * Поиск по товарам.
 * На десктопе — поле-пилюля с иконкой внутри.
 * На мобильных — круглая кнопка, по тапу разворачивается в поле.
 */
?>

<form role="search" method="get" class="product-search"
      action="<?php echo esc_url(home_url('/')); ?>">
    <label class="sr-only" for="product-search-field">Поиск по каталогу</label>
    <input type="search"
           id="product-search-field"
           name="s"
           value="<?php echo esc_attr(get_search_query()); ?>"
           placeholder="Поиск"
           class="product-search__input">
    <input type="hidden" name="post_type" value="product">
    <button type="submit" class="product-search__btn"
            title="Найти" aria-label="Найти" aria-expanded="false">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.3-4.3"></path>
        </svg>
    </button>
</form>
