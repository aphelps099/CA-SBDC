<?php


add_filter( 'crown_theme_styles', 'ctc_filter_styles' );
function ctc_filter_styles( $styles ) {
	$styles[] = array(
		'handle' => 'crown-child-theme-style',
		'src' => get_stylesheet_directory_uri() . '/assets/css/style' . ( ! WP_DEBUG ? '.min' : '' ) . '.css',
		'ver' => filemtime( get_stylesheet_directory() . '/assets/css/style' . ( ! WP_DEBUG ? '.min' : '' ) . '.css' ),
		'deps' => array( 'crown-theme-style' )
	);
	return $styles;
}

add_action( 'wp_enqueue_scripts', 'ctc_enqueue_styles', 12 );
function ctc_enqueue_styles() {
	wp_enqueue_style( 'crown-child-theme-style' );
}

add_action( 'enqueue_block_editor_assets', 'ctc_enqueue_block_editor_assets' );
function ctc_enqueue_block_editor_assets() {
	wp_enqueue_style('ptac-admin-style', Crown_Theme::get_child_uri() . '/assets/css/editor-style.css?ver=' . filemtime( Crown_Theme::get_child_dir() . '/assets/css/editor-style.css' ));
}

add_action( 'after_setup_theme', 'ctc_setup_editor_stylesheet', 2);
function ctc_setup_editor_stylesheet() {
	add_editor_style( 'assets/css/editor-style.css' );
	add_editor_style( Crown_Theme::get_child_uri() . '/assets/css/editor-style.css?ver=' . filemtime( Crown_Theme::get_child_dir() . '/assets/css/editor-style.css' ) );
}


add_filter( 'crown_block_event_header_index_title', 'ctc_filter_crown_block_event_header_index_title', 10, 2 );
function ctc_filter_crown_block_event_header_index_title( $title, $post_id ) {
	return '<span>' . __( 'NorCal PTAC', 'crown_child_theme' ) . '</span><span>' . __( 'Events', 'crown_child_theme' ) . '</span>';
}


add_filter( 'crown_block_featured_post_slider_link_subject', function( $subject ) { return 'Norcal PTAC Updates'; }, 10, 1 );


add_filter( 'crown_webinars_can_unpublish_syndicated', '__return_true' );
add_filter( 'crown_syndication_enabled', function( $enabled, $post_type ) { return false; }, 10, 2 );


add_action( 'admin_init', function() {
	if ( isset( $_GET['convert_webinars'] ) && boolval( $_GET['convert_webinars'] ) ) {
		$posts = get_posts( array(
			'posts_per_page' => -1,
			'post_type' => 'resource',
			'tax_query' => array(
				array( 'taxonomy' => 'resource_type', 'terms' => 7 )
			)
		) );
		foreach ( $posts as $post ) {
			wp_update_post( array(
				'ID' => $post->ID,
				'post_type' => 'webinar',
				'post_content' => str_replace( '<!-- wp:crown-blocks/resource-header /-->', '<!-- wp:crown-blocks/webinar-header /-->', $post->post_content )
			) );
		}
	}
} );