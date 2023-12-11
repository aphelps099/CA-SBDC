<?php 

/* 
Copyright 2017  Smash Balloon  (email: hey@smashballoon.com)
This program is paid software; you may not redistribute it under any
circumstances without the expressed written consent of the plugin author.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/


define( 'SB_ITEM_NAME_MULTIFEED', 'Multifeed Extension' );
if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
    // load our custom updater if it doesn't already exist
    include( dirname( __FILE__ ) . '/plugin_updater.php' );
}
// retrieve our license key from the DB
$license_key_multifeed = trim( get_option( 'cff_license_key_multifeed' ) );

//Include admin
include dirname( __FILE__ ) .'/cff-multifeed-admin.php';


//Multifeed Extension
function cff_multifeed_ids($page_id){
	$cff_multifeed_ids = explode(",", str_replace(' ', '', $page_id) );
	
	//Send it back
	return array_filter($cff_multifeed_ids);
}


//Uninstall
function cff_multifeed_uninstall()
{
    if ( ! current_user_can( 'activate_plugins' ) )
        return;
    //Settings
    delete_option( 'cff_date_from' );
    delete_option( 'cff_date_until' );

    //Deactivate and delete license
    // retrieve the license from the database
    $license_key_multifeed = trim( get_option( 'cff_license_key_multifeed' ) );
    // data to send in our API request
    $api_params = array( 
        'edd_action'=> 'deactivate_license', 
        'license'   => $license_key_multifeed, 
        'item_name' => urlencode( SB_ITEM_NAME_FEATURED ) // the name of our product in EDD
    );
    // Call the custom API.
    $response = wp_remote_get( add_query_arg( $api_params, 'http://smashballoon.com/' ), array( 'timeout' => 15, 'sslverify' => false ) );
    delete_option( 'cff_license_status_multifeed' );
    delete_option( 'cff_license_key_multifeed' );
}
register_uninstall_hook( __FILE__, 'cff_multifeed_uninstall' );

?>