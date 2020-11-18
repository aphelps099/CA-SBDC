<article <?php echo is_singular() ? 'id="main-article"' : ''; ?> <?php post_class(); ?> id="post-<?php the_ID(); ?>">

	<div <?php echo is_singular() ? 'id="main-content"' : ''; ?> class="entry-content">

		<?php the_content( __( 'Continue reading', 'crown_theme' ) ); ?>

	</div><!-- .entry-content -->

	<?php
		wp_link_pages( array(
			'before' => '<nav class="post-nav-links" aria-label="' . esc_attr__( 'Page', 'crown_theme' ) . '"><span class="label">' . __( 'Pages:', 'crown_theme' ) . '</span>',
			'after' => '</nav>',
			'link_before' => '<span class="page-number">',
			'link_after' => '</span>'
		) );
	?>

	<?php if ( is_singular( array( 'post' ) ) ) { ?>
		<?php ct_social_sharing_links(); ?>
	<?php } ?>

	<?php if ( is_singular( array( 'post' ) ) ) { ?>
		<?php get_template_part( 'template-parts/entry-footer', get_post_type() ); ?>
	<?php } ?>

</article><!-- .post -->