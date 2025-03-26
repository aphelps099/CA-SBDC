<?php
/*
Plugin Name: Smash Balloon Social Wall
Plugin URI: https://smashballoon.com/social-wall
Description: Combine social media feeds from all Smash Balloon social media plugins.
Version: 2.3.0
Author: Smash Balloon
Author URI: https://smashballoon.com/
License: GPLv2 or later
Text Domain: social-wall
*/

/*
Copyright 2024  Smash Balloon  (email: hey@smashballoon.com)
This program is paid software; you may not redistribute it under any
circumstances without the expressed written consent of the plugin author.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

use SB\SocialWall\SocialWall;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

define( 'SBSW_PLUGIN_EDD_NAME', 'Social Wall' );

if (!defined('SWVER')) {
	define('SWVER', '2.3.0');
}
// Db version.
if ( ! defined( 'SW_DBVERSION' ) ) {
	define( 'SW_DBVERSION', '1.1' );
}

define( 'SBSW_STORE_URL', 'https://smashballoon.com/' );

if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	// load custom updater
	include dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php';
}
// Upload folder name for local image files for posts
if ( ! defined( 'SBSW_UPLOADS_NAME' ) ) {
	define( 'SBSW_UPLOADS_NAME', 'sb-instagram-feed-images' );
}
// Name of the database table that contains instagram posts
if ( ! defined( 'SBSW_INSTAGRAM_POSTS_TYPE' ) ) {
	define( 'SBSW_INSTAGRAM_POSTS_TYPE', 'sbi_instagram_posts' );
}
// Name of the database table that contains feed ids and the ids of posts
if ( ! defined( 'SBSW_INSTAGRAM_FEEDS_POSTS' ) ) {
	define( 'SBSW_INSTAGRAM_FEEDS_POSTS', 'sbi_instagram_feeds_posts' );
}
if ( ! defined( 'SBSW_REFRESH_THRESHOLD_OFFSET' ) ) {
	define( 'SBSW_REFRESH_THRESHOLD_OFFSET', 40 * 86400 );
}
if ( ! defined( 'SBSW_MINIMUM_INTERVAL' ) ) {
	define( 'SBSW_MINIMUM_INTERVAL', 600 );
}
if ( ! defined( 'SBSW_TEXT_DOMAIN' ) ) {
	define( 'SBSW_TEXT_DOMAIN', 'social-wall' );
}
if ( ! defined( 'SBSW_SLUG' ) ) {
	define( 'SBSW_SLUG', 'sbsw' );
}
if ( ! defined( 'SBSW_PLUGIN_NAME' ) ) {
	define( 'SBSW_PLUGIN_NAME', 'Social Wall' );
}
if ( ! defined( 'SBSW_SETUP_URL' ) ) {
	define( 'SBSW_SETUP_URL', 'https://smashballoon.com/social-wall/docs/setup' );
}
if ( ! defined( 'SBSW_SUPPORT_URL' ) ) {
	define( 'SBSW_SUPPORT_URL', 'https://smashballoon.com/social-wall/support' );
}
if ( ! defined( 'SBSW_MIN_IF_VERSION' ) ) {
	define( 'SBSW_MIN_IF_VERSION', '5.6.5' );
}
if ( ! defined( 'SBSW_MIN_FB_VERSION' ) ) {
	define( 'SBSW_MIN_FB_VERSION', '3.18' );
}
if ( ! defined( 'SBSW_MIN_TW_VERSION' ) ) {
	define( 'SBSW_MIN_TW_VERSION', '1.8.2' );
}
if ( ! defined( 'SBSW_MIN_YT_VERSION' ) ) {
	define( 'SBSW_MIN_YT_VERSION', '1.1.5' );
}
if (!defined('SBSW_MIN_TT_VERSION')) {
	define('SBSW_MIN_TT_VERSION', '1.0.2');
}

// Plugin file.
if ( ! defined( 'SBSW_PLUGIN_FILE' ) ) {
	define( 'SBSW_PLUGIN_FILE', __FILE__ );
}

// Plugin folder path.
if ( ! defined( 'SBSW_PLUGIN_DIR' ) ) {
	define( 'SBSW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

// Plugin Folder URL.
if ( ! defined( 'SBSW_PLUGIN_URL' ) ) {
	define( 'SBSW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
// Plugin Base Name
if ( ! defined( 'SBSW_PLUGIN_BASENAME' ) ) {
	define( 'SBSW_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}
// Plugin Base Name
if ( ! defined( 'SBSW_BACKUP_PREFIX' ) ) {
	define( 'SBSW_BACKUP_PREFIX', '!' );
}
// Plugin Base Name
if ( ! defined( 'SBSW_FPL_PREFIX' ) ) {
	define( 'SBSW_FPL_PREFIX', '$' );
}
// Plugin Base Name
if ( ! defined( 'SBSW_USE_BACKUP_PREFIX' ) ) {
	define( 'SBSW_USE_BACKUP_PREFIX', '&' );
}
// Cron Updating Cache Time 60 days
if ( ! defined( 'SBSW_CRON_UPDATE_CACHE_TIME' ) ) {
	define( 'SBSW_CRON_UPDATE_CACHE_TIME', 60 * 60 * 24 * 60 );
}
// Max Records in Database for Image Resizing
if ( ! defined( 'SBSW_MAX_RECORDS' ) ) {
	define( 'SBSW_MAX_RECORDS', 100 );
}
if ( ! defined( 'SBSW_BUILDER_DIR' ) ) {
	define( 'SBSW_BUILDER_DIR', SBSW_PLUGIN_DIR . 'admin/builder/'  );
}
// Kick off Social Wall
if ( ! class_exists( SocialWall::class ) && is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

class_exists( SocialWall::class ) && SocialWall::instance();

function sbsw_cron_custom_interval( $schedules ) {
	$schedules['sw30mins'] = array(
		'interval' => 30 * 60,
		'display'  => __( 'Every 30 minutes', 'social-wall' ),
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'sbsw_cron_custom_interval' );

// Add a Settings link to the plugin on the Plugins page
$plugin_file = 'social-wall/social-wall.php';
add_filter( "plugin_action_links_{$plugin_file}", 'sbsw_add_settings_link', 10, 2 );
function sbsw_add_settings_link( $links, $file ) {
	$sbsw_settings_link = '<a href="' . admin_url( 'admin.php?page=sbsw' ) . '">' . __( 'Settings', 'social-wall' ) . '</a>';
	array_unshift( $links, $sbsw_settings_link );
	return $links;
}

function sbsw_plugin_updates() {
	// retrieve our license key from the DB
	$sbsw_license_key = trim( get_option( 'sbsw_license_key' ) );
	// setup the updater
	$edd_updater = new EDD_SL_Plugin_Updater(
        SBSW_STORE_URL, __FILE__, array(
			'version'   => SWVER,                   // current version number
			'license'   => $sbsw_license_key,       // license key (used get_option above to retrieve from DB)
			'item_name' => SBSW_PLUGIN_EDD_NAME,    // name of this plugin
			'author'    => 'Smash Balloon',         // author of this plugin
        )
	);
}
add_action( 'admin_init', 'sbsw_plugin_updates', 0 );
