<?php


add_filter( 'crown_theme_styles', 'ctc_filter_styles' );
function ctc_filter_styles( $styles ) {
	$is_test_env = get_site_url() == 'https://californiasbdc.norcalsbdc.org.test';
	if ( $is_test_env ) {
		$styles[] = array( 'handle' => 'uaf-ca', 'src' => 'https://norcalsbdc.org/wp-content/uploads/sites/34/useanyfont/uaf.css' );
	}
	$styles[] = array(
		'handle' => 'crown-child-theme-style',
		'src' => get_stylesheet_directory_uri() . '/assets/css/style' . ( ! WP_DEBUG ? '.min' : '' ) . '.css',
		'ver' => filemtime( get_stylesheet_directory() . '/assets/css/style' . ( ! WP_DEBUG ? '.min' : '' ) . '.css' ),
		'deps' => $is_test_env ? array( 'crown-theme-style', 'uaf-ca' ) : array( 'crown-theme-style' )
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


add_filter( 'crown_webinars_can_unpublish_syndicated', '__return_true' );
add_filter( 'crown_syndication_enabled', function( $enabled, $post_type ) { return false; }, 10, 2 );
add_filter( 'crown_client_story_region_taxonomy_enabled', '__return_true' );




add_filter( 'nav_menu_css_class', 'ctc_filter_nav_menu_css_class', 10, 4 );
function ctc_filter_nav_menu_css_class( $classes, $menu_item, $args, $depth ) {
	$thumbnail_id = get_post_meta( $menu_item->ID, 'thumbnail', true );
	if ( ! empty( $thumbnail_id ) ) {
		$classes[] = 'has-thumbnail';
	}
	return $classes;
}

add_filter( 'nav_menu_link_attributes', 'ctc_filter_nav_menu_link_attributes', 10, 4 );
function ctc_filter_nav_menu_link_attributes( $atts, $menu_item, $args, $depth ) {
	$thumbnail_id = get_post_meta( $menu_item->ID, 'thumbnail', true );
	if ( ! empty( $thumbnail_id ) ) {
		$atts['data-thumbnail-img'] = wp_get_attachment_image( $thumbnail_id, 'medium-large' );
	}
	return $atts;
}


add_filter( 'crown_block_client_story_index_filters', function( $filters ) {
	$filters = (object) array(
		'region' => (object) array( 'key' => 'cs_region', 'queried' => null, 'options' => array() )
	);
	$filters->region->queried = isset( $_GET[ $filters->region->key ] ) ? ( is_array( $_GET[ $filters->region->key ] ) ? $_GET[ $filters->region->key ] : array_filter( array_map( 'trim', explode( ',', $_GET[ $filters->region->key ] ) ), function( $n ) { return ! empty( $n ); } ) ) : array();
	$filters->region->options = array_map( function( $n ) use ( $filters ) {
		return (object) array( 'value' => $n->term_id, 'label' => $n->name, 'selected' => in_array( $n->term_id, $filters->region->queried ) );
	}, get_terms( array( 'taxonomy' => 'client_story_region' ) ) );
	return $filters;
} );

add_filter( 'crown_block_client_story_index_query_args', function( $query_args, $filters ) {
	$query_args['tax_query'] = array();
	if ( ! empty( $filters->region->queried ) ) $query_args['tax_query'][] = array( 'taxonomy' => 'client_story_region', 'terms' => $filters->region->queried );
	return $query_args;
}, 10, 2 );