<?php get_header(); ?>

<article id="main-article" <?php post_class(); ?>>

	<div id="main-content" class="entry-content">

		<?php $index_page_id = get_option( 'theme_config_index_page_faq' ); ?>
		<?php if ( $index_page_id && ( $index_page = get_post( $index_page_id ) ) ) { ?>
			<?php echo do_blocks( $index_page->post_content ); ?>
		<?php } ?>

	</div><!-- .entry-content -->

</article><!-- .post -->

<?php get_footer(); ?>