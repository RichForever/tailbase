<?php

/**
 * @package WordPress
 * @subpackage Tailbase
 * @since Tailbase 1.0.0
 */

get_header();
?>

<main class="container mx-auto">
  <?php while (have_posts()): the_post(); ?>
    <?php the_content(); ?>
  <?php endwhile; ?>
</main>

<?php get_footer(); ?>
