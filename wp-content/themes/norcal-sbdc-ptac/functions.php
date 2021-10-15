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

add_action( 'after_setup_theme', 'ctc_setup_editor_stylesheet', 2);
function ctc_setup_editor_stylesheet() {
	add_editor_style( 'assets/css/editor-style.css' );
	add_editor_style( Crown_Theme::get_child_uri() . '/assets/css/editor-style.css?ver=' . filemtime( Crown_Theme::get_child_dir() . '/assets/css/editor-style.css' ) );
}


add_filter( 'crown_block_event_header_index_title', 'ctc_filter_crown_block_event_header_index_title', 10, 2 );
function ctc_filter_crown_block_event_header_index_title( $title, $post_id ) {
	return '<span>' . __( 'NorCal PTAC', 'crown_child_theme' ) . '</span><span>' . __( 'Events', 'crown_child_theme' ) . '</span>';
}


add_filter( 'crown_webinars_can_unpublish_syndicated', '__return_true' );