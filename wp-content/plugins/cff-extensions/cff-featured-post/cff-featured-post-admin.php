<?php 

function cff_register_setting_license_featured_post(){
    register_setting('cff_license', 'cff_license_key_featured_post', 'cff_sanitize_license_featured_post' );
}
add_action('cff_register_setting_license', 'cff_register_setting_license_featured_post');

function cff_sanitize_license_featured_post( $new_featured_post ) {
    $old_featured_post = get_option( 'cff_license_key_featured_post' );
    if( $old_featured_post && $old_featured_post != $new_featured_post ) {
        delete_option( 'cff_license_status_featured_post' ); // new license has been entered, so must reactivate
    }
    return $new_featured_post;
}
function cff_activate_license_featured_post() {
    // listen for our activate button to be clicked
    if( isset( $_POST['cff_license_activate_featured_post'] ) ) {
        // run a quick security check 
        if( ! check_admin_referer( 'cff_nonce_featured_post', 'cff_nonce_featured_post' ) )   
            return; // get out if we didn't click the Activate button
        // retrieve the license from the database
        $license_featured_post = trim( get_option( 'cff_license_key_featured_post' ) );
            
        // data to send in our API request
        $api_params = array( 
            'edd_action'=> 'activate_license', 
            'license'   => $license_featured_post, 
            'item_name' => urlencode( SB_ITEM_NAME_FEATURED ) // the name of our product in EDD
        );
        
        // Call the custom API.
        $response_featured_post = wp_remote_get( add_query_arg( $api_params, 'http://smashballoon.com/' ), array( 'timeout' => 60, 'sslverify' => false ) );
        // make sure the response came back okay
        if ( is_wp_error( $response_featured_post ) )
            return false;
        // decode the license data
        $license_data_featured_post = json_decode( wp_remote_retrieve_body( $response_featured_post ) );
        
        // $license_data->license will be either "active" or "inactive"
        update_option( 'cff_license_status_featured_post', $license_data_featured_post->license );
    }
}
add_action('admin_init', 'cff_activate_license_featured_post');
function cff_deactivate_license_featured_post() {
    // listen for our activate button to be clicked
    if( isset( $_POST['cff_license_deactivate_featured_post'] ) ) {
        // run a quick security check 
        if( ! check_admin_referer( 'cff_nonce_featured_post', 'cff_nonce_featured_post' ) )   
            return; // get out if we didn't click the Activate button
        // retrieve the license from the database
        $license_featured_post = trim( get_option( 'cff_license_key_featured_post' ) );
            
        // data to send in our API request
        $api_params = array( 
            'edd_action'=> 'deactivate_license', 
            'license'   => $license_featured_post, 
            'item_name' => urlencode( SB_ITEM_NAME_FEATURED ) // the name of our product in EDD
        );
        
        // Call the custom API.
        $response_featured_post = wp_remote_get( add_query_arg( $api_params, 'http://smashballoon.com/' ), array( 'timeout' => 15, 'sslverify' => false ) );
        // make sure the response came back okay
        if ( is_wp_error( $response_featured_post ) )
            return false;
        // decode the license data
        $license_data_featured_post = json_decode( wp_remote_retrieve_body( $response_featured_post ) );
        
        // $license_data->license will be either "deactivated" or "failed"
        if( $license_data_featured_post->license == 'deactivated' )
            delete_option( 'cff_license_status_featured_post' );
    }
}
add_action('admin_init', 'cff_deactivate_license_featured_post');


//Return license key field
function cff_featured_post_license(){

    $license_featured_post = get_option( 'cff_license_key_featured_post' );
    $status_featured_post  = get_option( 'cff_license_status_featured_post' ); ?>

    <tr valign="top">   
        <th scope="row" valign="top">Featured Post Extension</th>
        <td>
            <input id="cff_license_key_featured_post" name="cff_license_key_featured_post" type="text" class="regular-text" value="<?php echo $license_featured_post ?>" />
    
            <?php if( false !== $license_featured_post ) {
                if( $status_featured_post !== false && $status_featured_post == 'valid' ) {
                    wp_nonce_field( 'cff_nonce_featured_post', 'cff_nonce_featured_post' ); ?>
                    <input type="submit" class="button-secondary" name="cff_license_deactivate_featured_post" value="Deactivate License"/>
                    <span style="color:green; padding-left: 4px;">Active</span>
                <?php } else { 
                    wp_nonce_field( 'cff_nonce_featured_post', 'cff_nonce_featured_post' ); ?>
                    <input type="submit" class="button-secondary" name="cff_license_activate_featured_post" value="Activate License"/>
                    <span style="color:red; padding-left: 4px;">Inactive</span>
                <?php }
            }  ?>
            <br /><i style="color: #666; font-size: 11px;">The license key you received when purchasing the Featured Post extension.</i>
        </td>
    </tr>

    <?php 
}
// add_action('cff_admin_license', 'cff_featured_post_license');


function cff_featured_post_options($cff_featured_post_id_val){
    $cff_featured_post_fields = '
    <tr valign="top">
        <th scope="row">Featured Post ID</th>
        <td>
            <input name="cff_featured_post_id" type="text" value="'. $cff_featured_post_id_val .'" size="45" />
            <br /><i style="color: #666; font-size: 11px;">The ID of the featured post or event you want to display. Eg. 1234567890123</i>
        </td>
    </tr>
    ';

    return $cff_featured_post_fields;
}

?>