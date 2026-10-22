<?php
/**
 * @package WordPress
 * @subpackage Tailbase
 * @since Tailbase 1.0.0
 */
?>
<button
  id="scrollUp"
  class="fixed right-6 bottom-6 w-16 h-16 z-10 rounded-full bg-[#fff5e6] shadow-[rgba(250,_158,_13,_0.25)_0px_4px_17px] items-center justify-center p-[.35rem] xl:p-[.3rem]"
  style="display: none;"
  aria-label="Kliknij i przewiń stronę na górę"
  x-data
  @click="window.scrollTo({top: 0, behavior: 'smooth'})"
  @click.stop
>
  <div class="w-full h-full flex items-center justify-center bg-white rounded-full [&>*]:h-4 [&>*]:w-4 [&>*]:xl:h-5 [&>*]:xl:w-5 [&>*]:rotate-90 text-california-500">
    <?php echo tailbase_get_icon("chevron-big"); ?>
  </div>
</button>
    <?php wp_footer(); ?>
  </body>
</html>
