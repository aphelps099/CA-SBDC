<?php 
/* 
Copyright 2021 Smash Balloon  (email: hey@smashballoon.com)
This program is paid software; you may not redistribute it under any
circumstances without the expressed written consent of the plugin author.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/

define( 'FEATURED_VER', '1.4.3' );
define( 'SB_ITEM_NAME_FEATURED', 'Featured Post Extension' );
if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
    // load our custom updater if it doesn't already exist
    include( dirname( __FILE__ ) . '/plugin_updater.php' );
}

// retrieve our license key from the DB
$license_key_featured_post = trim( get_option( 'cff_license_key_featured_post' ) );

//Include admin
include dirname( __FILE__ ) .'/cff-featured-post-admin.php';


//Featured Post Extension
function cff_featured_post_id($cff_featured_post_id, $access_token){
    return 'https://graph.facebook.com/v4.0/'.$cff_featured_post_id.'?fields=id,from{picture,id,name,link},message,message_tags,story,story_tags,picture,full_picture,status_type,created_time,backdated_time,attachments{title,description,media_type,unshimmed_url,target{id},multi_share_end_card,media{source,image},subattachments},shares,comments.summary(true){message,created_time},likes.summary(true).limit(0),call_to_action,privacy&access_token=' . $access_token;
}
function cff_featured_event_id($cff_featured_post_id, $access_token){
    return 'https://graph.facebook.com/v4.0/'.$cff_featured_post_id.'?fields=id,name,attending_count,ticket_uri,cover,start_time,end_time,timezone,place,description,interested_count&access_token='.$access_token;
}


//Uninstall
function cff_featured_post_uninstall()
{
    if ( ! current_user_can( 'activate_plugins' ) )
        return;
    //Settings
    delete_option( 'cff_date_from' );
    delete_option( 'cff_date_until' );

    //Deactivate and delete license
    // retrieve the license from the database
    $license_key_featured_post = trim( get_option( 'cff_license_key_featured_post' ) );
    // data to send in our API request
    $api_params = array( 
        'edd_action'=> 'deactivate_license', 
        'license'   => $license_key_featured_post, 
        'item_name' => urlencode( SB_ITEM_NAME_FEATURED ) // the name of our product in EDD
    );
    // Call the custom API.
    $response = wp_remote_get( add_query_arg( $api_params, 'https://smashballoon.com/' ), array( 'timeout' => 15, 'sslverify' => false ) );
    delete_option( 'cff_license_status_featured_post' );
    delete_option( 'cff_license_key_featured_post' );
}
register_uninstall_hook( __FILE__, 'cff_featured_post_uninstall' );

?>