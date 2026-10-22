<?php
/**
 * @package WordPress
 * @subpackage Tailbase
 * @since Tailbase 1.0.0
 */
?>
<button
  id="scrollUp"
  class="fixed right-6 bottom-6 z-10 h-16 w-16 items-center justify-center rounded-full bg-[#fff5e6] p-[.35rem] shadow-[rgba(250,_158,_13,_0.25)_0px_4px_17px] xl:p-[.3rem]"
  style="display: none;"
  aria-label="Kliknij i przewiń stronę na górę"
  x-data
  @click="window.scrollTo({top: 0, behavior: 'smooth'})"
  @click.stop
>
  <div class="text-california-500 flex h-full w-full items-center justify-center rounded-full bg-white [&>*]:h-4 [&>*]:w-4 [&>*]:rotate-90 [&>*]:xl:h-5 [&>*]:xl:w-5">
    <?php echo tailbase_get_icon("chevron-big"); ?>
  </div>
</button>
    <?php wp_footer(); ?>
  </body>
</html>
