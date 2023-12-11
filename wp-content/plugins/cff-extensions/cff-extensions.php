<?php 
/*
Plugin Name: Custom Facebook Feed Pro - Extensions
Plugin URI: http://smashballoon.com/custom-facebook-feed/extensions/
Description: Contains a range of extensions which extend the functionality of the Custom Facebook Feed Pro plugin.
Version: 1.7.3
Author: Smash Balloon
Author URI: http://smashballoon.com/
License: GPLv2 or later
*/
/* 
Copyright 2021  Smash Balloon  (email: hey@smashballoon.com)
This program is paid software; you may not redistribute it under any
circumstances without the expressed written consent of the plugin author.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/


define( 'SB_ITEM_NAME_EXTENSIONS', 'Custom Facebook Feed Extensions' );
if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
    // load our custom updater if it doesn't already exist
    include( dirname( __FILE__ ) . '/plugin_updater.php' );
}

// setup the updater
function cff_ext_plugin_updates() {
    // retrieve our license key from the DB
    $license_key_extensions = trim( get_option( 'cff_license_key_extensions' ) );
    // setup the updater
    $edd_updater = new EDD_SL_Plugin_Updater( 'https://smashballoon.com/', __FILE__, array(
            'version'   => '1.7.3',           			// current version number
            'license'   => $license_key_extensions,		// license key (used get_option above to retrieve from DB)
            'item_name' => SB_ITEM_NAME_EXTENSIONS, 	// name of this plugin
            'author'    => 'Smash Balloon'      		// author of this plugin
        )
    );
}
add_action( 'admin_init', 'cff_ext_plugin_updates', 0 );

//Include admin
include dirname( __FILE__ ) .'/cff-extensions-admin.php';

//Include extensions
include dirname( __FILE__ ) .'/cff-date-range/cff-date-range.php';
include dirname( __FILE__ ) .'/cff-featured-post/cff-featured-post.php';
include dirname( __FILE__ ) .'/cff-multifeed/cff-multifeed.php';
include dirname( __FILE__ ) .'/cff-album/cff-album.php';
include dirname( __FILE__ ) .'/cff-carousel/cff-carousel.php';
include dirname( __FILE__ ) .'/cff-reviews/cff-reviews.php';


//Uninstall
function cff_extensions_uninstall()
{
    if ( ! current_user_can( 'activate_plugins' ) )
        return;

    //Deactivate and delete license
    //Delete options
    delete_option( 'cff_extensions_status' );

    // retrieve the license from the database
    $license_key_extensions = trim( get_option( 'cff_license_key_extensions' ) );
    // data to send in our API request
    $api_params = array( 
        'edd_action'=> 'deactivate_license', 
        'license'   => $license_key_extensions, 
        'item_name' => urlencode( SB_ITEM_NAME_EXTENSIONS ) // the name of our product in EDD
    );
    // Call the custom API.
    $response = wp_remote_get( add_query_arg( $api_params, 'https://smashballoon.com/' ), array( 'timeout' => 15, 'sslverify' => false ) );
    delete_option( 'cff_license_status_extensions' );
    delete_option( 'cff_license_key_extensions' );
}
register_uninstall_hook( __FILE__, 'cff_extensions_uninstall' );

?>