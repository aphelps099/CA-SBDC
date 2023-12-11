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


define( 'SB_ITEM_NAME_EXT_DATE', 'Date Range Extension' );
if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
    // load our custom updater if it doesn't already exist
    include( dirname( __FILE__ ) . '/plugin_updater.php' );
}
// retrieve our license key from the DB
$license_key_ext_date = trim( get_option( 'cff_license_key_ext_date' ) );

//Include admin
include dirname( __FILE__ ) .'/cff-date-range-admin.php';


//Date Extension posts
function cff_ext_date($cff_date_from, $cff_date_until){
	$cff_ext_date_range = '&since='.$cff_date_from.'&until='.$cff_date_until;
	return $cff_ext_date_range;
}
//Date Extension events
function cff_ext_date_events($cff_date_from, $cff_date_until){
    $cff_start_time_string = "start_time>=".$cff_date_from."%20AND%20start_time<=".$cff_date_until;
    return $cff_start_time_string;
}


//Uninstall
function cff_ext_date_uninstall()
{
    if ( ! current_user_can( 'activate_plugins' ) )
        return;
    //Settings
    delete_option( 'cff_date_from' );
    delete_option( 'cff_date_until' );

    //Deactivate and delete license
    // retrieve the license from the database
    $license_key_ext_date = trim( get_option( 'cff_license_key_ext_date' ) );
    // data to send in our API request
    $api_params = array( 
        'edd_action'=> 'deactivate_license', 
        'license'   => $license_key_ext_date, 
        'item_name' => urlencode( SB_ITEM_NAME_EXT_DATE ) // the name of our product in EDD
    );
    // Call the custom API.
    $response = wp_remote_get( add_query_arg( $api_params, 'http://smashballoon.com/' ), array( 'timeout' => 15, 'sslverify' => false ) );
    delete_option( 'cff_license_status_ext_date' );
    delete_option( 'cff_license_key_ext_date' );
}
register_uninstall_hook( __FILE__, 'cff_ext_date_uninstall' );

?>