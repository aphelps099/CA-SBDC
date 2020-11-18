<?php get_header(); ?>

<?php if ( have_posts() ) { ?>
	<?php while ( have_posts() ) { ?>
		<?php the_post(); ?>
		<?php $gated_content_settings = ct_get_post_gated_content_settings(); ?>
		<?php if ( $gated_content_settings->active ) { ?>
			<?php get_template_part( 'template-parts/gated-content', get_post_type() ); ?>
		<?php } else { ?>
			<?php get_template_part( 'template-parts/content', get_post_type() ); ?>
		<?php } ?>
		<?php get_template_part( 'template-parts/related-content', get_post_type() ); ?>
	<?php } ?>
<?php } ?>

<?php get_footer(); ?>