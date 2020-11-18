<?php get_header(); ?>

<?php
	$page_title = __( 'Latest', 'crown_theme' );
	$featured_post_ids = array();

	if ( is_category() ) {
		$page_title = single_cat_title( '', false );
	} else if ( is_tag() ) {
		$page_title = __( 'Tag', 'crown_theme' ) . ': ' . single_tag_title( '', false );
	} else if ( is_date() ) {
		$month = trim( single_month_title( ' ', false ) );
		$page_title = ! empty( $month ) ? $month : trim( $wp_query->query_vars['year'] );
	} else if ( is_author() ) {
		$author = get_queried_object();
		$page_title = __( 'Author', 'crown_theme' ) . ': ' . $author->display_name;
	} else if ( is_search() ) {
		$page_title = __( 'Search Results', 'crown_theme' );
	} else if ( is_404() ) {
		$page_title = __( '404: Page Not Found', 'crown_theme' );
	}
	

	if ( ! is_paged() ) {
		if ( is_category() ) {
			$category = get_queried_object();
			$featured_post_ids = get_term_meta( $category->term_id, 'category_featured_post_ids', true );
			if ( empty( $featured_post_ids ) ) $featured_post_ids = array();
		}
		$featured_post_ids = array_slice( $featured_post_ids, 0, 3 );
	}

?>

<?php if ( ! empty( $featured_post_ids ) ) { ?>
	<div id="featured-content" class="wp-block-crown-blocks-container alignfull text-color-light" style="background-color:#24282F">
		<div class="container-bg"></div>
		<div class="inner">
			<div class="container-contents">
				<div class="inner">

					<?php if ( ! empty( $page_title ) ) { ?>
						<h1 class="has-text-align-center is-style-section-heading"><?php echo $page_title; ?></h1>
					<?php } ?>

					<?php echo render_block( array(
						'blockName' => 'crown-blocks/recent-posts',
						'attrs' => array(
							'manuallySelectPosts' => true,
							'filterPostsInclude' => array_map( function( $n ) { return array( 'id' => $n ); }, $featured_post_ids )
						),
						'innerContent' => array()
					) ); ?>

				</div>
			</div>
		</div>
	</div>
<?php } ?>

<div id="index-entries" class="wp-block-crown-blocks-container alignfull" style="background-color:#FFFFFF">
	<div class="container-bg"></div>
	<div class="inner">
		<div class="container-contents">
			<div class="inner">

				<?php if ( ! empty( $page_title ) && empty( $featured_post_ids ) ) { ?>
					<h1 class="has-text-align-center is-style-section-heading"><?php echo $page_title; ?></h1>
				<?php } else if ( ! empty( $featured_post_ids ) ) { ?>
					<h2 class="has-text-align-center is-style-section-heading"><?php _e( 'Latest', 'crown_theme' ); ?></h2>
				<?php } ?>

				<?php if ( have_posts() ) { ?>

					<div class="entries-list item-count-<?php echo $wp_query->post_count; ?>">
						<div class="inner">

							<?php while ( have_posts() ) { ?>
								<?php the_post(); ?>
								<?php get_template_part( 'template-parts/index-entry-content', get_post_type() ); ?>
							<?php } ?>
							
							<?php if ( $wp_query->post_count >= 4 ) { ?>
								<?php $cta_block_id = get_option( 'theme_config_category_tpl_cta_block_id' ); ?>
								<?php if ( ! empty( $cta_block_id ) && ( $cta_block = get_post( $cta_block_id ) ) ) { ?>
									<aside class="cta-container">
										<?php echo apply_filters( 'the_content', $cta_block->post_content ); ?>
									</aside>
								<?php } ?>
							<?php } ?>

						</div>
					</div>

				<?php } else { ?>

					<div class="alert alert-warning">
						<p class="mb-0">Nothing found, please try adjusting your search.</p>
					</div>

				<?php } ?>

				<?php get_template_part( 'template-parts/pagination' ); ?>

			</div>
		</div>
	</div>
</div>

<?php get_footer(); ?>