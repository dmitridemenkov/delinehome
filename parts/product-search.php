<?php
/**
 * Поиск по товарам.
 */
?>

<form role="search" method="get" class="product-search relative w-full sm:w-[260px]"
      action="<?php echo esc_url(home_url('/')); ?>">
    <label class="sr-only" for="product-search-field">Поиск по каталогу</label>
    <input type="search"
           id="product-search-field"
           name="s"
           value="<?php echo esc_attr(get_search_query()); ?>"
           placeholder="Поиск"
           class="w-full rounded-full border border-[#E6E6E6] bg-white py-2 ps-4 pe-10 text-sm text-black placeholder:text-[#BFBFBF] focus:outline-none focus:border-[#20436C] transition">
    <input type="hidden" name="post_type" value="product">
    <button type="submit"
            class="absolute end-3 top-1/2 -translate-y-1/2 text-[#BFBFBF] hover:text-[#20436C] transition"
            title="Найти" aria-label="Найти">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.3-4.3"></path>
        </svg>
    </button>
</form>
