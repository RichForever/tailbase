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
    <?php if (!tailbase_security_enabled("disable_xmlrpc")): ?>
    <link rel="pingback" href="<?php bloginfo("pingback_url"); ?>">
    <?php endif; ?>
    <?php wp_head(); ?>
  </head>

  <body <?php body_class(is_404() ? "h-full" : ""); ?>>
    <?php wp_body_open(); ?>
<nav
  class="navbar mt-6 mb-16 bg-white-500 px-6 md:mb-20 lg:px-0 xl:mb-24 2xl:mb-20 <?php echo is_404()
      ? "hidden"
      : "block"; ?>"
  x-data="HeaderData"
  :class="{'navbar--open': isMenuOpen, 'navbar--sticky': isSticky}"
>
  <div class="mx-auto flex flex-wrap items-center justify-between lg:container">
    <?php if (has_custom_logo()): ?>
      <div class="max-w-[80px] lg:max-w-[140px] xl:max-w-[180px]">
        <?php the_custom_logo(); ?>
      </div>
    <?php else: ?>
      <a href="<?php echo esc_url(
          home_url("/"),
      ); ?>" class="navbar__brand" aria-label="Przejdź do strony głównej">
        <div class="inline-block">
          <hq class="font-brico text-picton-blue text-fluid-xl leading-none font-bold whitespace-nowrap">Planet English</hq>
        </div>
      </a>
    <?php endif; ?>
    <button class="navbar__hamburger relative lg:hidden" aria-label="Open menu" @click="openMenuClickHandler">
      <div class="relative flex h-[50px] w-[50px] transform items-center justify-center overflow-hidden bg-white-500 transition-transform duration-200">
        <div class="flex h-[20px] w-[20px] origin-center transform flex-col justify-around overflow-hidden transition-transform duration-300">
          <span class="span__item h-[2px] w-7 origin-left transform bg-purple-500 transition-transform duration-300"></span>
          <span
            class="span__item h-[2px] w-7 transform rounded bg-purple-500 transition-transform delay-75 duration-300"
          ></span>
          <span
            class="span__item h-[2px] w-7 origin-left transform bg-purple-500 transition-transform delay-150 duration-300"
          ></span>
    
          <div class="navbar__close absolute top-2.5 flex w-0 -translate-x-10 transform items-center justify-between transition-transform duration-500">
            <span
              class="absolute h-[2px] w-5 rotate-0 transform bg-purple-500 transition-transform delay-200 duration-500"
            ></span>
            <span
              class="absolute h-[2px] w-5 -rotate-0 transform bg-purple-500 transition-transform delay-200 duration-500"
            ></span>
          </div>
        </div>
      </div>
    </button>
    <div class="navbar__menu hidden w-full lg:block lg:w-auto" id="navbar-default">
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
