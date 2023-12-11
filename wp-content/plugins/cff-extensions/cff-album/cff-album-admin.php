<?php 

function cff_register_setting_license_album(){
    register_setting('cff_license', 'cff_license_key_album', 'cff_sanitize_license_album' );
}
add_action('cff_register_setting_license', 'cff_register_setting_license_album');

function cff_sanitize_license_album( $new_album ) {
    $old_album = get_option( 'cff_license_key_album' );
    if( $old_album && $old_album != $new_album ) {
        delete_option( 'cff_license_status_album' ); // new license has been entered, so must reactivate
    }
    return $new_album;
}
function cff_activate_license_album() {
    // listen for our activate button to be clicked
    if( isset( $_POST['cff_license_activate_album'] ) ) {
        // run a quick security check 
        if( ! check_admin_referer( 'cff_nonce_album', 'cff_nonce_album' ) )   
            return; // get out if we didn't click the Activate button
        // retrieve the license from the database
        $license_album = trim( get_option( 'cff_license_key_album' ) );
            
        // data to send in our API request
        $api_params = array( 
            'edd_action'=> 'activate_license', 
            'license'   => $license_album, 
            'item_name' => urlencode( SB_ITEM_NAME_ALBUM ) // the name of our product in EDD
        );
        
        // Call the custom API.
        $response_album = wp_remote_get( add_query_arg( $api_params, 'http://smashballoon.com/' ), array( 'timeout' => 60, 'sslverify' => false ) );
        // make sure the response came back okay
        if ( is_wp_error( $response_album ) )
            return false;
        // decode the license data
        $license_data_album = json_decode( wp_remote_retrieve_body( $response_album ) );
        
        // $license_data->license will be either "active" or "inactive"
        update_option( 'cff_license_status_album', $license_data_album->license );
    }
}
add_action('admin_init', 'cff_activate_license_album');
function cff_deactivate_license_album() {
    // listen for our activate button to be clicked
    if( isset( $_POST['cff_license_deactivate_album'] ) ) {
        // run a quick security check 
        if( ! check_admin_referer( 'cff_nonce_album', 'cff_nonce_album' ) )   
            return; // get out if we didn't click the Activate button
        // retrieve the license from the database
        $license_album = trim( get_option( 'cff_license_key_album' ) );
            
        // data to send in our API request
        $api_params = array( 
            'edd_action'=> 'deactivate_license', 
            'license'   => $license_album, 
            'item_name' => urlencode( SB_ITEM_NAME_ALBUM ) // the name of our product in EDD
        );
        
        // Call the custom API.
        $response_album = wp_remote_get( add_query_arg( $api_params, 'http://smashballoon.com/' ), array( 'timeout' => 15, 'sslverify' => false ) );
        // make sure the response came back okay
        if ( is_wp_error( $response_album ) )
            return false;
        // decode the license data
        $license_data_album = json_decode( wp_remote_retrieve_body( $response_album ) );
        
        // $license_data->license will be either "deactivated" or "failed"
        if( $license_data_album->license == 'deactivated' )
            delete_option( 'cff_license_status_album' );
    }
}
add_action('admin_init', 'cff_deactivate_license_album');


//Return license key field
function cff_album_license(){

    $license_album = get_option( 'cff_license_key_album' );
    $status_album  = get_option( 'cff_license_status_album' ); ?>

    <tr valign="top">   
        <th scope="row" valign="top">Album Extension</th>
        <td>
            <input id="cff_license_key_album" name="cff_license_key_album" type="text" class="regular-text" value="<?php echo $license_album ?>" />
    
            <?php if( false !== $license_album ) {
                if( $status_album !== false && $status_album == 'valid' ) {
                    wp_nonce_field( 'cff_nonce_album', 'cff_nonce_album' ); ?>
                    <input type="submit" class="button-secondary" name="cff_license_deactivate_album" value="Deactivate License"/>
                    <span style="color:green; padding-left: 4px;">Active</span>
                <?php } else { 
                    wp_nonce_field( 'cff_nonce_album', 'cff_nonce_album' ); ?>
                    <input type="submit" class="button-secondary" name="cff_license_activate_album" value="Activate License"/>
                    <span style="color:red; padding-left: 4px;">Inactive</span>
                <?php }
            }  ?>
            <br /><i style="color: #666; font-size: 11px;">The license key you received when purchasing the Album extension.</i>
        </td>
    </tr>

    <?php 
}
// add_action('cff_admin_license', 'cff_album_license');

?>