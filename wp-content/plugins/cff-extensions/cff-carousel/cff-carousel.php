<?php
/*
Copyright 2021  Smash Balloon LLC (email : hey@smashballoon.com)
This program is paid software; you may not redistribute it under any
circumstances without the expressed written consent of the plugin author.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/

define( 'CAROUSEL_VER', '1.2.3' );
define( 'SB_ITEM_NAME_CAROUSEL', 'Carousel Extension' );
if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	// load our custom updater if it doesn't already exist
	include( dirname( __FILE__ ) . '/plugin_updater.php' );
}

// retrieve our license key from the DB
$license_key_carousel = trim( get_option( 'cff_license_key_ext_carousel' ) );

//Include admin
if( is_admin() ) {
	include dirname(__FILE__) . '/cff-carousel-admin.php';
}

/**
 * Enqueues css and js resources
 *
 * The feed requires css and javascript so these resources are
 * loaded if the plugin is active
 */
function cff_carousel_scripts_and_styles()
{
	wp_enqueue_style( 'cff_carousel_css', plugins_url( '/css/carousel.css', __FILE__ ), 'cff_carousel_js', CAROUSEL_VER );
	wp_enqueue_script( 'cff_carousel_js', plugins_url('/js/carousel.js', __FILE__), array('jquery'), CAROUSEL_VER, true);
}
add_action( 'wp_enqueue_scripts', 'cff_carousel_scripts_and_styles' );

/**
 * Filters shortcode arguments
 *
 * This function utilizes a WordPress hook to support new shortcode arguments
 * in the main custom-facebook-feed shortcode for extensions
 *
 * @param array $out | The output array of shortcode attributes
 * @param array $pairs | The supported attributes and their defaults
 * @param array $atts | The user defined shortcode attributes
 * @return array mixed | Modified array
 */
function cff_carousel_filter_custom_facebook_feed_shortcode( $out, $pairs, $atts )
{
	// Get just carousel options
	$cff_carousel_options = get_option('cff_carousel_options');
	$cff_masonry_options = get_option('cff_masonry_options');

	// Shortcode arguments override options on the settings page which override defaults, this is determined here
	$carousel = !empty( $cff_carousel_options['cff_carousel_enabled'] ) ? $cff_carousel_options['cff_carousel_enabled'] : false;
	$masonry = !empty( $cff_masonry_options['cff_masonry_enabled'] ) ? $cff_masonry_options['cff_masonry_enabled'] : false;
	$carouselheight = isset( $cff_carousel_options['cff_carousel_height'] ) ? $cff_carousel_options['cff_carousel_height'] : 'tallest';
	$carouselcols = isset( $cff_carousel_options['cff_carousel_desktop_cols'] ) ? $cff_carousel_options['cff_carousel_desktop_cols'] : 1;
	$carouselmobilecols = isset( $cff_carousel_options['cff_carousel_mobile_cols'] ) ? $cff_carousel_options['cff_carousel_mobile_cols'] : 1;
	$carouselarrows = isset( $cff_carousel_options['cff_carousel_navigation'] ) ? $cff_carousel_options['cff_carousel_navigation'] : 'none';
	$carouselpag = isset( $cff_carousel_options['cff_carousel_pagination'] ) ? $cff_carousel_options['cff_carousel_pagination'] : false;
	$carouselautoplay = isset( $cff_carousel_options['cff_carousel_autoplay'] ) ? $cff_carousel_options['cff_carousel_autoplay'] : true;
	$carouseltime = isset( $cff_carousel_options['cff_carousel_interval'] ) ? $cff_carousel_options['cff_carousel_interval'] : 5000;

	$out['carousel'] = isset( $atts['carousel'] ) ? $atts['carousel'] : $carousel;
	$out['masonry'] = isset( $atts['masonry'] ) ? $atts['masonry'] : $masonry;
	$out['carouselheight'] = isset( $atts['carouselheight'] ) ? $atts['carouselheight'] : $carouselheight;
	$out['carouselcols'] = isset( $atts['carouselcols'] ) ? $atts['carouselcols'] : $carouselcols;
	$out['carouselmobilecols'] = isset( $atts['carouselmobilecols'] ) ? $atts['carouselmobilecols'] : $carouselmobilecols;
	$out['carouselarrows'] = isset( $atts['carouselarrows'] ) ? $atts['carouselarrows'] : $carouselarrows;
	$out['carouselpag'] = isset( $atts['carouselpag'] ) ? $atts['carouselpag'] : $carouselpag;
	$out['carouselautoplay'] = isset( $atts['carouselautoplay'] ) ? $atts['carouselautoplay'] : $carouselautoplay;
	$out['carouseltime'] = isset( $atts['carouseltime'] ) ? $atts['carouseltime'] : $carouseltime;

	// Masonry and Carousel feeds are incompatible so we check to see if carousel is active
	// and set Masonry to false if it is
	if( $out['carousel'] === 'on' || $out['carousel'] === "true" || $out['carousel'] === true ) {
		$out['headeroutside'] = true;
		$out['likeboxoutside'] = true;
		$out['masonry'] = false;

		// Carousel feeds are incompatible with the columns setting for the main plugin
		$out['columnscompatible'] = false;
	}
	return $out;
}
add_filter( 'shortcode_atts_custom_facebook_feed', 'cff_carousel_filter_custom_facebook_feed_shortcode', 10, 3 );

/**
 * Adds classes to the main #cff element in the feed
 *
 * Allows extensions to dynamically add classes to assist with css
 * and javascript use
 *
 * @param string $cff_content | The content of the feed
 * @param array $atts | The user defined shortcode attributes
 * @return string | CSS classes that you wish to add to the main #cff element
 */
function cff_carousel_add_class( $classes, $atts )
{
	if( isset( $atts['carousel'] ) ) {
		if( $atts['carousel'] === "on" || $atts['carousel'] === true || $atts['carousel'] === "true" ) {
			$classes .= 'cff-carousel';
		}
	}
	return $classes;
}
add_filter( 'cff_feed_class', 'cff_carousel_add_class', 10, 2 );

/**
 * Adds data attributes to the #cff element
 *
 * User defined carousel options are used in the javascript file
 * with the use of data attributes and jQuery to read them
 *
 * @param array $cff_content | the html that generates the feed
 * @param array $atts | all user defined options for the feed
 * @return string | modified html that generates the feed
 */
function cff_carousel_data_atts( $atts )
{
	if( isset( $atts['carousel'] ) ) {
		if( $atts['carousel'] === 'on' || $atts['carousel'] === true || $atts['carousel'] === 'true' ) {
			$data = '';

			if( $atts['carouselautoplay'] == 'on' || $atts['carouselautoplay'] === 'true' || $atts['carouselautoplay'] === true ) {
                    $atts['carouseltime'] = ( $atts['carouseltime'] >= 1000 ) ? $atts['carouseltime']  : 1000;
				    $data .= sprintf(' data-cff-interval="%s"', $atts['carouseltime']);                
			}

			$data .= sprintf( ' data-cff-height="%s"', $atts['carouselheight'] );
			$data .= sprintf( ' data-cff-cols="%s"', $atts['carouselcols'] );
			$data .= sprintf( ' data-cff-mobilecols="%s"', $atts['carouselmobilecols'] );
			$data .= sprintf( ' data-cff-arrows="%s"', $atts['carouselarrows'] );

			if( $atts['carouselpag'] == 'on' || $atts['carouselpag'] === 'true' || $atts['carouselpag'] === true ) {
				$data .= sprintf( ' data-cff-pag="%s"', 'true' );
			} else {
				$data .= sprintf( ' data-cff-pag="%s"', 'false' );
			}

			return $data;
		}
	}
}

//Uninstall
function cff_carousel_uninstall()
{
	if ( ! current_user_can( 'activate_plugins' ) )
		return;
	//Settings
	delete_option( 'cff_carousel_options' );

	//Deactivate and delete license
	// retrieve the license from the database
	$license_key_carousel = trim( get_option( 'cff_license_key_ext_carousel' ) );
	// data to send in our API request
	$api_params = array(
		'edd_action'=> 'deactivate_license',
		'license'   => $license_key_carousel,
		'item_name' => urlencode( SB_ITEM_NAME_CAROUSEL ) // the name of our product in EDD
	);
	// Call the custom API.
	$response = wp_remote_get( add_query_arg( $api_params, 'https://smashballoon.com/' ), array( 'timeout' => 15, 'sslverify' => false ) );
	delete_option( 'cff_license_status_ext_carousel' );
	delete_option( 'cff_license_key_ext_carousel' );
}
register_uninstall_hook( __FILE__, 'cff_carousel_uninstall' );