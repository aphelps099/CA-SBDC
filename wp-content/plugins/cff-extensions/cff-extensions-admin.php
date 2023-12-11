<?php 

function cff_register_setting_license_extensions(){
    register_setting('cff_license', 'cff_license_key_extensions', 'cff_sanitize_license_extensions' );
}
add_action('cff_register_setting_license', 'cff_register_setting_license_extensions');

function cff_sanitize_license_extensions( $new_extensions ) {
    $old_extensions = get_option( 'cff_license_key_extensions' );
    if( $old_extensions && $old_extensions != $new_extensions ) {
        delete_option( 'cff_license_status_extensions' ); // new license has been entered, so must reactivate
    }
    return $new_extensions;
}
function cff_activate_license_extensions() {
    // listen for our activate button to be clicked
    if( isset( $_POST['cff_license_activate_extensions'] ) ) {
        // run a quick security check 
        if( ! check_admin_referer( 'cff_nonce_extensions', 'cff_nonce_extensions' ) )   
            return; // get out if we didn't click the Activate button
        // retrieve the license from the database
        $license_extensions = trim( get_option( 'cff_license_key_extensions' ) );
            
        // data to send in our API request
        $api_params = array( 
            'edd_action'=> 'activate_license', 
            'license'   => $license_extensions, 
            'item_name' => urlencode( SB_ITEM_NAME_EXTENSIONS ) // the name of our product in EDD
        );
        
        // Call the custom API.
        $response_extensions = wp_remote_get( add_query_arg( $api_params, 'http://smashballoon.com/' ), array( 'timeout' => 60, 'sslverify' => false ) );
        // make sure the response came back okay
        if ( is_wp_error( $response_extensions ) )
            return false;
        // decode the license data
        $license_data_extensions = json_decode( wp_remote_retrieve_body( $response_extensions ) );
        
        // $license_data->license will be either "active" or "inactive"
        update_option( 'cff_license_status_extensions', $license_data_extensions->license );
    }
}
add_action('admin_init', 'cff_activate_license_extensions');
function cff_deactivate_license_extensions() {
    // listen for our activate button to be clicked
    if( isset( $_POST['cff_license_deactivate_extensions'] ) ) {
        // run a quick security check 
        if( ! check_admin_referer( 'cff_nonce_extensions', 'cff_nonce_extensions' ) )   
            return; // get out if we didn't click the Activate button
        // retrieve the license from the database
        $license_extensions = trim( get_option( 'cff_license_key_extensions' ) );
            
        // data to send in our API request
        $api_params = array( 
            'edd_action'=> 'deactivate_license', 
            'license'   => $license_extensions, 
            'item_name' => urlencode( SB_ITEM_NAME_EXTENSIONS ) // the name of our product in EDD
        );
        
        // Call the custom API.
        $response_extensions = wp_remote_get( add_query_arg( $api_params, 'http://smashballoon.com/' ), array( 'timeout' => 15, 'sslverify' => false ) );
        // make sure the response came back okay
        if ( is_wp_error( $response_extensions ) )
            return false;
        // decode the license data
        $license_data_extensions = json_decode( wp_remote_retrieve_body( $response_extensions ) );
        
        // $license_data->license will be either "deactivated" or "failed"
        if( $license_data_extensions->license == 'deactivated' )
            delete_option( 'cff_license_status_extensions' );
    }
}
add_action('admin_init', 'cff_deactivate_license_extensions');


//Return license key field
function cff_extensions_license(){

    $license_extensions = get_option( 'cff_license_key_extensions' );
    $status_extensions  = get_option( 'cff_license_status_extensions' ); ?>

    <tr valign="top">   
        <th scope="row" valign="top">Custom Facebook Feed Extensions</th>
        <td>
            <input id="cff_license_key_extensions" name="cff_license_key_extensions" type="text" class="regular-text" value="<?php echo $license_extensions ?>" />
    
            <?php if( false !== $license_extensions ) {
                if( $status_extensions !== false && $status_extensions == 'valid' ) {
                    wp_nonce_field( 'cff_nonce_extensions', 'cff_nonce_extensions' ); ?>
                    <input type="submit" class="button-secondary" name="cff_license_deactivate_extensions" value="Deactivate License"/>
                    <span style="color:green; padding-left: 4px;">Active</span>
                <?php } else { 
                    wp_nonce_field( 'cff_nonce_extensions', 'cff_nonce_extensions' ); ?>
                    <input type="submit" class="button-secondary" name="cff_license_activate_extensions" value="Activate License"/>
                    <span style="color:red; padding-left: 4px;">Inactive</span>
                <?php }
            }  ?>
            <br /><i style="color: #666; font-size: 11px;">The license key you received for the Custom Facebook Feed Extensions plugin.</i>
        </td>
    </tr>

    <?php 
}
// add_action('cff_admin_license', 'cff_extensions_license');

?>