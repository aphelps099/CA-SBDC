<?php get_header(); ?>

<?php if ( have_posts() ) { ?>
	<?php while ( have_posts() ) { ?>
		<?php the_post(); ?>
		<?php get_template_part( 'template-parts/content', get_post_type() ); ?>
		<?php get_template_part( 'template-parts/related-content', get_post_type() ); ?>
	<?php } ?>
<?php } ?>

<?php get_footer(); ?>