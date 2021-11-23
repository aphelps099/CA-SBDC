<?php


add_filter( 'crown_theme_styles', 'ctc_filter_styles' );
function ctc_filter_styles( $styles ) {
	foreach ( $styles as $i => $style ) {
		if ( $style['handle'] == 'crown-theme-style' ) {
			unset( $styles[ $i ] );
			break;
		}
	}
	$styles[] = array(
		'handle' => 'crown-child-theme-style',
		'src' => Crown_Theme::get_child_uri() . '/assets/css/style' . ( ! WP_DEBUG ? '.min' : '' ) . '.css',
		'ver' => filemtime( Crown_Theme::get_child_dir() . '/assets/css/style' . ( ! WP_DEBUG ? '.min' : '' ) . '.css' ),
		'deps' => array( 'crown-theme-typekit', 'slick', 'blueimp-gallery', 'odometer-theme-default', 'jquery-oembed' )
	);
	return $styles;
}

add_action( 'wp_enqueue_scripts', 'ctc_enqueue_styles', 12 );
function ctc_enqueue_styles() {
	wp_enqueue_style( 'crown-child-theme-style' );
}

add_filter( 'crown_theme_scripts', 'ctc_filter_scripts' );
function ctc_filter_scripts( $scripts ) {
	$scripts[] = array(
		'handle' => 'crown-child-theme-main',
		'src' => Crown_Theme::get_child_uri() . '/assets/js/main' . ( ! WP_DEBUG ? '.min' : '' ) . '.js',
		'ver' => filemtime( Crown_Theme::get_child_dir() . '/assets/js/main' . ( ! WP_DEBUG ? '.min' : '' ) . '.js' ),
		'deps' => array( 'crown-theme-main' )
	);
	return $scripts;
}

add_action( 'wp_enqueue_scripts', 'ctc_enqueue_scripts', 12 );
function ctc_enqueue_scripts() {
	wp_enqueue_script( 'crown-child-theme-main' );
}

add_action( 'after_setup_theme', 'ctc_setup_editor_stylesheet', 2);
function ctc_setup_editor_stylesheet() {
	add_editor_style( 'assets/css/editor-style.css' );
	add_editor_style( Crown_Theme::get_child_uri() . '/assets/css/editor-style.css?ver=' . filemtime( Crown_Theme::get_child_dir() . '/assets/css/editor-style.css' ) );
}

add_filter( 'render_block', 'ctc_filter_render_block_crown_blocks_featured_post_slider', 10, 2 );
function ctc_filter_render_block_crown_blocks_featured_post_slider( $block_content, $block ) {
	if ( $block['blockName'] !== 'crown-blocks/featured-post-slider' ) return $block_content;

	$atts = isset( $block['attrs'] ) ? $block['attrs'] : array();
	$atts = array_merge(array(
		'className' => '',
		'maxPostCount' => '9',
		'manuallySelectPosts' => false,
		'excludePrevPosts' => false,
		'filterCategories' => array(),
		'filterTags' => array(),
		'filterTopics' => array(),
		'filterPostsExclude' => array(),
		'filterPostsInclude' => array()
	), $atts);

	global $post;

	$queryArgs = array(
		'post_type' => 'post',
		'posts_per_page' => $atts['maxPostCount'],
		'tax_query' => array(),
		'post__not_in' => array(),
		'post__in' => array()
	);

	if ( boolval( $atts['excludePrevPosts'] ) ) {
		$prev_post_ids = apply_filters( 'crown_blocks_prev_post_ids', array() );
		$queryArgs['post__not_in'] = array_unique( array_merge( $queryArgs['post__not_in'], $prev_post_ids ) );
	}

	if ( ! empty( $atts['filterCategories'] ) ) {
		$queryArgs['tax_query'][] = array( 'taxonomy' => 'category', 'terms' => array_map( function( $n ) { return $n['id']; }, $atts['filterCategories'] ) );
	}

	if ( ! empty( $atts['filterTags'] ) ) {
		$queryArgs['tax_query'][] = array( 'taxonomy' => 'post_tag', 'terms' => array_map( function( $n ) { return $n['id']; }, $atts['filterTags'] ) );
	}

	if ( ! empty( $atts['filterTopics'] ) ) {
		$queryArgs['tax_query'][] = array( 'taxonomy' => 'post_topic', 'terms' => array_map( function( $n ) { return $n['id']; }, $atts['filterTopics'] ) );
	}

	if ( ! empty( $atts['filterPostsExclude'] ) ) {
		$queryArgs['post__not_in'] = array_unique( array_merge( $queryArgs['post__not_in'], array_map( function( $n ) { return $n['id']; }, $atts['filterPostsExclude'] ) ) );
	}

	if ( $atts['manuallySelectPosts'] ) {
		if ( empty( $atts['filterPostsInclude'] ) ) return '';
		$queryArgs['tax_query'] = array();
		$queryArgs['post__not_in'] = array();
		$queryArgs['post__in'] = array_unique( array_merge( $queryArgs['post__not_in'], array_map( function( $n ) { return $n['id']; }, $atts['filterPostsInclude'] ) ) );
		$queryArgs['orderby'] = 'post__in';
		$queryArgs['order'] = 'ASC';
	}

	$query = new WP_Query( $queryArgs );
	if ( ! $query->have_posts() ) return '';

	$block_class = array( 'wp-block-crown-blocks-featured-post-slider', $atts['className'] );

	ob_start();
	// print_r($atts);
	?>

		<div class="<?php echo implode( ' ', $block_class ); ?>">
			<div class="inner">

				<div class="post-feed item-count-<?php echo $query->post_count; ?>" data-item-count="<?php echo $query->post_count; ?>">
					<div class="inner">

						<?php while ( $query->have_posts() ) { ?>
							<?php $query->the_post(); ?>
							<article <?php post_class(); ?>>

								<a href="<?php the_permalink(); ?>">
									<div class="inner">

										<?php $topics = get_the_terms( get_the_ID(), 'post_topic' ); ?>
										<h6 class="entry-topics is-style-display">
											<strong>News</strong>
											<?php if ( $topics ) { ?>
												<?php foreach ( $topics as $term ) { ?>
													<?php echo $term->name; ?>
												<?php } ?>
											<?php } ?>
										</h6>

										<h3 class="entry-title"><?php the_title(); ?></h3>

										<?php $relative_time = ctc_get_relative_time( get_the_time( 'Y-m-d H:i:s', get_the_ID() ) ); ?>
										<?php if ( $relative_time && ! in_array( $relative_time->units, array( 'years', 'months' ) ) ) { ?>
											<p class="entry-date"><?php echo abs( $relative_time->value ) . ' ' . $relative_time->units . ( $relative_time->value <= 0 ? ' ago' : '' ); ?></p>
										<?php } else { ?>
											<p class="entry-date"><?php echo get_the_time( 'j F, Y', get_the_ID() ); ?></p>
										<?php } ?>

									</div>
								</a>

							</article>
						<?php } ?>
						<?php wp_reset_postdata(); ?>

					</div>
				</div>

			</div>
		</div>

	<?php
	$output = ob_get_clean();

	// self::add_output_post_ids( array_map( function($n) { return $n->ID; }, $query->posts ) );

	return $output;
}

function ctc_get_relative_time( $timestamp, $current_timestamp = null ) {

	$date = strtotime( $timestamp ) !== false ? new DateTime( $timestamp ) : false;
	if ( ! $date ) return false;

	$current_date = $current_timestamp !== null ? ( strtotime( $current_timestamp ) !== false ? new DateTime( $current_timestamp ) : false ) : new DateTime();
	if ( ! $current_date ) return false;

	$diff = $current_date->diff( $date );

	$time = (object) array(
		'value' => 0,
		'units' => '',
		'units_contextual' => ''
	);

	if ( intval( $diff->format( '%y' ) ) > 0 ) {
		$time->value = intval( $diff->format( '%r%y' ) );
		$time->units = 'years';
		$time->units_contextual = abs( $time->value ) == 1 ? 'year' : 'years';
	} else if ( intval( $diff->format( '%m' ) ) > 0 ) {
		$time->value = intval( $diff->format( '%r%m' ) );
		$time->units = 'months';
		$time->units_contextual = abs( $time->value ) == 1 ? 'month' : 'months';
	} else if ( intval( $diff->format( '%d' ) ) > 0 ) {
		$time->value = intval( $diff->format( '%r%d' ) );
		$time->units = 'days';
		$time->units_contextual = abs( $time->value ) == 1 ? 'day' : 'days';
	} else {
		$time->value = intval( $diff->format( '%r%i' ) );
		$time->units = 'minutes';
		$time->units_contextual = abs( $time->value ) == 1 ? 'minute' : 'minutes';
	}

	return $time;

}