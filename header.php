<?php
/**
 * @package WordPress
 * @subpackage Tailbase
 * @since Tailbase 1.0.0
 */
?>
<!doctype html>
<html <?php language_attributes(); ?> class="scroll-smooth <?php echo is_404()
     ? "h-full"
     : ""; ?>">
  <head>
    <meta charset="<?php bloginfo("charset"); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="pingback" href="<?php bloginfo("pingback_url"); ?>">
    <?php wp_head(); ?>
  </head>

  <body <?php body_class(is_404() ? "h-full" : ""); ?>>
    <?php wp_body_open(); ?>
<nav
  class="bg-white-500 mt-6 mb-16 md:mb-20 xl:mb-24 2xl:mb-20 px-6 lg:px-0 navbar <?php echo is_404()
      ? "hidden"
      : "block"; ?>"
  x-data="HeaderData"
  :class="{'navbar--open': isMenuOpen, 'navbar--sticky': isSticky}"
>
  <div class="flex flex-wrap items-center justify-between mx-auto lg:container">
    <?php if (has_custom_logo()): ?>
      <div class="max-w-[80px] lg:max-w-[140px] xl:max-w-[180px]">
        <?php the_custom_logo(); ?>
      </div>
    <?php else: ?>
      <a href="<?php echo esc_url(
          home_url("/"),
      ); ?>" class="navbar__brand" aria-label="Przejdź do strony głównej">
        <div class="inline-block">
          <hq class="text-xl font-bold font-brico whitespace-nowrap text-picton-blue leading-none">Planet English</hq>
        </div>
      </a>
    <?php endif; ?>
    <button class="lg:hidden relative navbar__hamburger" aria-label="Open menu" @click="openMenuClickHandler">
      <div class="relative flex overflow-hidden items-center justify-center w-[50px] h-[50px] transform transition-transform bg-white-500 duration-200">
        <div class="flex flex-col justify-around w-[20px] h-[20px] transform transition-transform duration-300 origin-center overflow-hidden">
          <span class="bg-purple-500 h-[2px] w-7 transform transition-transform duration-300 origin-left span__item"></span>
          <span
            class="bg-purple-500 h-[2px] w-7 rounded transform transition-transform duration-300 delay-75 span__item"
          ></span>
          <span
            class="bg-purple-500 h-[2px] w-7 transform transition-transform duration-300 origin-left delay-150 span__item"
          ></span>
    
          <div class="absolute items-center justify-between transform transition-transform duration-500 top-2.5 -translate-x-10 flex w-0 navbar__close">
            <span
              class="absolute bg-purple-500 h-[2px] w-5 transform transition-transform duration-500 rotate-0 delay-200"
            ></span>
            <span
              class="absolute bg-purple-500 h-[2px] w-5 transform transition-transform duration-500 -rotate-0 delay-200"
            ></span>
          </div>
        </div>
      </div>
    </button>
    <div class="hidden w-full lg:block lg:w-auto navbar__menu" id="navbar-default">
      <?php wp_nav_menu([
          "theme_location" => "main_menu",
          "container" => false,
          "fallback_cb" => false,
          "items_wrap" =>
              '<ul class="font-semibold flex flex-col mt-4 lg:flex-row lg:space-x-6">%3$s</ul>',
      ]); ?>
    </div>
  </div>
</nav>

<script>
  document.addEventListener('alpine:init', () => {
    Alpine.data('HeaderData', () => ({
      isMenuOpen: false,
      isSticky: false,
      navbarHeight: '96px',
      openMenuClickHandler() {
        this.isMenuOpen = !this.isMenuOpen;
      },
      updateStickyState() {
        this.isSticky = window.scrollY >= 150;
      },
      init() {
        this.navbarHeight = this.$refs.HeaderNavbar ? this.$refs.HeaderNavbar.clientHeight + 'px' : 0;
        window.addEventListener('scroll', () => {
          this.updateStickyState();
        });
        this.updateStickyState();

        let root = document.documentElement;
        root.style.setProperty('--navbar-height', this.navbarHeight);
      },
    }));
  });
</script>
