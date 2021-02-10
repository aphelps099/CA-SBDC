<?php

get_header();

$page_title = __( 'News & Updates', 'crown_theme' );

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

if ( is_home() && ( $page_for_posts = get_post( get_option( 'page_for_posts' ) ) ) ) {

	$post = $page_for_posts;
	setup_postdata( $post );
	get_template_part( 'template-parts/content', get_post_type() );
	wp_reset_postdata();

} else {
	?>

		<div id="index-entries">

			<h1><?php echo $page_title; ?></h1>

			<?php if ( have_posts() ) { ?>

				<div class="entries-list item-count-<?php echo $wp_query->post_count; ?>">
					<div class="inner">

						<?php while ( have_posts() ) { ?>
							<?php the_post(); ?>
							<?php get_template_part( 'template-parts/index-entry-content', get_post_type() ); ?>
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

	<?php
}

get_footer();