<?php 

function cff_register_setting_license_ext_date(){
    register_setting('cff_license', 'cff_license_key_ext_date', 'cff_sanitize_license_ext_date' );
}
add_action('cff_register_setting_license', 'cff_register_setting_license_ext_date');

function cff_sanitize_license_ext_date( $new_ext_date ) {
    $old_ext_date = get_option( 'cff_license_key_ext_date' );
    if( $old_ext_date && $old_ext_date != $new_ext_date ) {
        delete_option( 'cff_license_status_ext_date' ); // new license has been entered, so must reactivate
    }
    return $new_ext_date;
}
function cff_activate_license_ext_date() {
    // listen for our activate button to be clicked
    if( isset( $_POST['cff_license_activate_ext_date'] ) ) {
        // run a quick security check 
        if( ! check_admin_referer( 'cff_nonce_ext_date', 'cff_nonce_ext_date' ) )   
            return; // get out if we didn't click the Activate button
        // retrieve the license from the database
        $license_ext_date = trim( get_option( 'cff_license_key_ext_date' ) );
            
        // data to send in our API request
        $api_params = array( 
            'edd_action'=> 'activate_license', 
            'license'   => $license_ext_date, 
            'item_name' => urlencode( SB_ITEM_NAME_EXT_DATE ) // the name of our product in EDD
        );
        
        // Call the custom API.
        $response_ext_date = wp_remote_get( add_query_arg( $api_params, 'http://smashballoon.com/' ), array( 'timeout' => 60, 'sslverify' => false ) );
        // make sure the response came back okay
        if ( is_wp_error( $response_ext_date ) )
            return false;
        // decode the license data
        $license_data_ext_date = json_decode( wp_remote_retrieve_body( $response_ext_date ) );
        
        // $license_data->license will be either "active" or "inactive"
        update_option( 'cff_license_status_ext_date', $license_data_ext_date->license );
    }
}
add_action('admin_init', 'cff_activate_license_ext_date');
function cff_deactivate_license_ext_date() {
    // listen for our activate button to be clicked
    if( isset( $_POST['cff_license_deactivate_ext_date'] ) ) {
        // run a quick security check 
        if( ! check_admin_referer( 'cff_nonce_ext_date', 'cff_nonce_ext_date' ) )   
            return; // get out if we didn't click the Activate button
        // retrieve the license from the database
        $license_ext_date = trim( get_option( 'cff_license_key_ext_date' ) );
            
        // data to send in our API request
        $api_params = array( 
            'edd_action'=> 'deactivate_license', 
            'license'   => $license_ext_date, 
            'item_name' => urlencode( SB_ITEM_NAME_EXT_DATE ) // the name of our product in EDD
        );
        
        // Call the custom API.
        $response_ext_date = wp_remote_get( add_query_arg( $api_params, 'http://smashballoon.com/' ), array( 'timeout' => 15, 'sslverify' => false ) );
        // make sure the response came back okay
        if ( is_wp_error( $response_ext_date ) )
            return false;
        // decode the license data
        $license_data_ext_date = json_decode( wp_remote_retrieve_body( $response_ext_date ) );
        
        // $license_data->license will be either "deactivated" or "failed"
        if( $license_data_ext_date->license == 'deactivated' )
            delete_option( 'cff_license_status_ext_date' );
    }
}
add_action('admin_init', 'cff_deactivate_license_ext_date');


//Return license key field
function cff_ext_date_license(){

	$license_ext_date = get_option( 'cff_license_key_ext_date' );
    $status_ext_date  = get_option( 'cff_license_status_ext_date' ); ?>

	<tr valign="top">   
	    <th scope="row" valign="top">Date Range Extension</th>
	    <td>
	        <input id="cff_license_key_ext_date" name="cff_license_key_ext_date" type="text" class="regular-text" value="<?php echo $license_ext_date ?>" />
	
			<?php if( false !== $license_ext_date ) {
	            if( $status_ext_date !== false && $status_ext_date == 'valid' ) {
	                wp_nonce_field( 'cff_nonce_ext_date', 'cff_nonce_ext_date' ); ?>
	                <input type="submit" class="button-secondary" name="cff_license_deactivate_ext_date" value="Deactivate License"/>
	                <span style="color:green; padding-left: 4px;">Active</span>
	            <?php } else { 
	                wp_nonce_field( 'cff_nonce_ext_date', 'cff_nonce_ext_date' ); ?>
	                <input type="submit" class="button-secondary" name="cff_license_activate_ext_date" value="Activate License"/>
	                <span style="color:red; padding-left: 4px;">Inactive</span>
	            <?php }
	        }  ?>
	        <br /><i style="color: #666; font-size: 11px;">The license key you received when purchasing the Date Range extension.</i>
	    </td>
	</tr>

    <?php 
}
// add_action('cff_admin_license', 'cff_ext_date_license');


//Add Date Range Extension options to the settings page
function cff_ext_date_options($cff_date_from_val, $cff_date_until_val) {
    
    $cff_ext_date_fields = '
    <tr valign="top">
        <th scope="row">Display posts from date range</th>
        <td>
            <span>From: </span> <input name="cff_date_from" type="text" value="'. $cff_date_from_val .'" size="10" class="cff-datepicker" />
            <span>Until: </span> <input name="cff_date_until" type="text" value="'. $cff_date_until_val .'" size="10"  class="cff-datepicker" />
        </td>
    </tr>
    ';

    return $cff_ext_date_fields;

}

function cff_ext_date_scripts(){
    // Add jQuery calender
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('cff-jquery-ui-css', plugin_dir_url( __FILE__ ) . 'css/jqueryui.lightness.min.css');
    wp_enqueue_script( 'cff_ext_date_range', plugin_dir_url( __FILE__ ) . 'js/cff-date-admin.js' );
}
add_action( 'admin_enqueue_scripts', 'cff_ext_date_scripts' );
?>