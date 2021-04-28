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