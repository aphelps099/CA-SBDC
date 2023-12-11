<?php
function cff_register_setting_license_ext_carousel(){
    register_setting('cff_license', 'cff_license_key_ext_carousel', 'cff_sanitize_license_ext_carousel' );
}
add_action('cff_register_setting_license', 'cff_register_setting_license_ext_carousel');

function cff_sanitize_license_ext_carousel( $new_ext_carousel ) {
    $old_ext_carousel = get_option( 'cff_license_key_ext_carousel' );
    if( $old_ext_carousel && $old_ext_carousel != $new_ext_carousel ) {
        delete_option( 'cff_license_status_ext_carousel' ); // new license has been entered, so must reactivate
    }
    return $new_ext_carousel;
}

function cff_activate_license_ext_carousel() {
    // listen for our activate button to be clicked
    if( isset( $_POST['cff_license_activate_ext_carousel'] ) ) {
        // run a quick security check
        if( ! check_admin_referer( 'cff_nonce_ext_carousel', 'cff_nonce_ext_carousel' ) )
            return; // get out if we didn't click the Activate button
        // retrieve the license from the database
        $license_ext_carousel = trim( get_option( 'cff_license_key_ext_carousel' ) );

        // data to send in our API request
        $api_params = array(
            'edd_action'=> 'activate_license',
            'license'   => $license_ext_carousel,
            'item_name' => urlencode( SB_ITEM_NAME_CAROUSEL ) // the name of our product in EDD
        );

        // Call the custom API.
        $response_ext_carousel = wp_remote_get( add_query_arg( $api_params, 'http://smashballoon.com/' ), array( 'timeout' => 60, 'sslverify' => false ) );
        // make sure the response came back okay
        if ( is_wp_error( $response_ext_carousel ) )
            return false;
        // decode the license data
        $license_data_ext_carousel = json_decode( wp_remote_retrieve_body( $response_ext_carousel ) );

        // $license_data->license will be either "active" or "inactive"
        update_option( 'cff_license_status_ext_carousel', $license_data_ext_carousel->license );
    }

    //cff_car_reset();
}
add_action('admin_init', 'cff_activate_license_ext_carousel');

/**
 * Used for testing the 3.x to 4.x update sequence
 */
function cff_car_reset() {
	$cff_carousel_options = array(
        'cff_carousel_enabled' => true,
        'cff_carousel_height' => 'clickexpand',
        'cff_carousel_desktop_cols' => 5,
        'cff_carousel_mobile_cols' => 2,
        'cff_carousel_navigation' => 'onhover',
        'cff_carousel_pagination' => true,
        'cff_carousel_autoplay' => true,
        'cff_carousel_interval' => 2223
    );
	update_option( 'cff_carousel_options', $cff_carousel_options );

}

function cff_deactivate_license_ext_carousel() {
    // listen for our activate button to be clicked
    if( isset( $_POST['cff_license_deactivate_ext_carousel'] ) ) {
        // run a quick security check
        if( ! check_admin_referer( 'cff_nonce_ext_carousel', 'cff_nonce_ext_carousel' ) )
            return; // get out if we didn't click the Activate button
        // retrieve the license from the database
        $license_ext_carousel = trim( get_option( 'cff_license_key_ext_carousel' ) );

        // data to send in our API request
        $api_params = array(
            'edd_action'=> 'deactivate_license',
            'license'   => $license_ext_carousel,
            'item_name' => urlencode( SB_ITEM_NAME_CAROUSEL ) // the name of our product in EDD
        );

        // Call the custom API.
        $response_ext_carousel = wp_remote_get( add_query_arg( $api_params, 'http://smashballoon.com/' ), array( 'timeout' => 15, 'sslverify' => false ) );
        // make sure the response came back okay
        if ( is_wp_error( $response_ext_carousel ) )
            return false;
        // decode the license data
        $license_data_ext_carousel = json_decode( wp_remote_retrieve_body( $response_ext_carousel ) );

        // $license_data->license will be either "deactivated" or "failed"
        if( $license_data_ext_carousel->license == 'deactivated' )
            delete_option( 'cff_license_status_ext_carousel' );
    }
}
add_action('admin_init', 'cff_deactivate_license_ext_carousel');

//Return license key field
function cff_ext_carousel_license(){
    $license_ext_carousel = get_option( 'cff_license_key_ext_carousel' );
    $status_ext_carousel  = get_option( 'cff_license_status_ext_carousel' ); ?>

    <tr valign="top">
        <th scope="row" valign="top">Carousel Extension</th>
        <td>
            <input id="cff_license_key_ext_carousel" name="cff_license_key_ext_carousel" type="text" class="regular-text" value="<?php echo $license_ext_carousel; ?>" />

            <?php if( false !== $license_ext_carousel ) {
                if( $status_ext_carousel !== false && $status_ext_carousel == 'valid' ) {
                    wp_nonce_field( 'cff_nonce_ext_carousel', 'cff_nonce_ext_carousel' ); ?>
                    <input type="submit" class="button-secondary" name="cff_license_deactivate_ext_carousel" value="Deactivate License"/>
                    <span style="color:green; padding-left: 4px;">Active</span>
                <?php } else {
                    wp_nonce_field( 'cff_nonce_ext_carousel', 'cff_nonce_ext_carousel' ); ?>
                    <input type="submit" class="button-secondary" name="cff_license_activate_ext_carousel" value="Activate License"/>
                    <span style="color:red; padding-left: 4px;">Inactive</span>
                <?php }
            }  ?>
            <br /><i style="color: #666; font-size: 11px;">The license key you received when purchasing the carousel extension.</i>
        </td>
    </tr>

    <?php
}
add_action('cff_admin_license', 'cff_ext_carousel_license');

/**
 * Generates HTML for the Settings page
 *
 * Creates the html for the carousel extension options in the admin area. Hooks
 * into the layout options page
 */
function cff_carousel_options() {

    //Check whether the Carousel extension is active when used in the Extensions plugin for the Smash version
    $cff_ext_options = get_option('cff_extensions_status');
    ( isset($cff_ext_options[ 'cff_extensions_carousel_active' ]) ) ? $cff_carousel_active = $cff_ext_options[ 'cff_extensions_carousel_active' ] : $cff_carousel_active = '';
    ( is_plugin_active( 'cff-carousel/cff-carousel.php' ) ) ? $cff_carousel_active = true : $cff_carousel_active = false;
    if( !$cff_carousel_active && empty($cff_ext_options[ 'cff_extensions_carousel_active' ]) ) return;
    
    // Get just carousel options
    $cff_carousel_options = get_option('cff_carousel_options');
    // If an option is set, use the saved value, otherwise use the default
    $enabled = isset( $cff_carousel_options['cff_carousel_enabled'] ) ? $cff_carousel_options['cff_carousel_enabled'] : false;
    $height = isset( $cff_carousel_options['cff_carousel_height'] ) ? $cff_carousel_options['cff_carousel_height'] : 'tallest';
    $desktop_cols = isset( $cff_carousel_options['cff_carousel_desktop_cols'] ) ? $cff_carousel_options['cff_carousel_desktop_cols'] : 1;
    $mobile_cols = isset( $cff_carousel_options['cff_carousel_mobile_cols'] ) ? $cff_carousel_options['cff_carousel_mobile_cols'] : 1;
    $arrows = isset( $cff_carousel_options['cff_carousel_navigation'] ) ? $cff_carousel_options['cff_carousel_navigation'] : 'none';
    $pagination = isset( $cff_carousel_options['cff_carousel_pagination'] ) ? $cff_carousel_options['cff_carousel_pagination'] : true;
    $autoplay = isset( $cff_carousel_options['cff_carousel_autoplay'] ) ? $cff_carousel_options['cff_carousel_autoplay'] : false;
    $interval = isset( $cff_carousel_options['cff_carousel_interval'] ) ? $cff_carousel_options['cff_carousel_interval'] : 5000;
    ?>
    <hr />
    <h3><?php _e('Carousel'); ?></h3>

    <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row"><label><?php _e("Set Carousel as Default"); ?></label><code class="cff_shortcode"> carousel
                        Eg: carousel=true</code></th>
                <td>
                    <input type="checkbox" name="cff_carousel_enabled" id="cff_carousel_enabled" <?php if($enabled == 'on') echo 'checked="checked"' ?> />
                    &nbsp;<a class="cff-tooltip-link" href="JavaScript:void(0);"><?php _e("What is this?"); ?></a>
                    <p class="cff-tooltip cff-more-info"><?php _e("All feeds will be carousels unless set to carousel=false in the shortcode."); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php _e("Height of Carousel"); ?></label><code class="cff_shortcode"> carouselheight
                        Eg: carouselheight="autoexpand"</code></th>
                <td class="cff-wide">
                    <select name="cff_carousel_height" id="cff_carousel_height">
                        <option value="tallest" <?php if( $height == 'tallest' ) { echo 'selected'; } ?>>Always set to tallest post</option>
                        <option value="clickexpand" <?php if( $height == 'clickexpand' ) { echo 'selected'; } ?>>Set to shortest post, button to expand</option>
                        <option value="autoexpand" <?php if( $height == 'autoexpand' ) { echo 'selected'; } ?>>Automatically set to post height (forces single column)</option>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php _e("Carousel Desktop Columns"); ?></label><code class="cff_shortcode"> carouselcols
                        Eg: carouselcols=3</code></th>
                <td class="cff-short">
                    <input name="cff_carousel_desktop_cols" type="number" value="<?php esc_attr_e( $desktop_cols ); ?>" size="6" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php _e("Carousel Mobile Columns"); ?></label><code class="cff_shortcode"> carouselmobilecols
                        Eg: carouselmobilecols=2</code></th>
                <td class="cff-short">
                    <input name="cff_carousel_mobile_cols" type="number" value="<?php esc_attr_e( $mobile_cols ); ?>" size="6" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php _e("Navigation Arrows Style"); ?></label><code class="cff_shortcode"> carouselarrows
                        Eg: carouselarrows="onhover"</code></th>
                <td class="cff-wide">
                    <select name="cff_carousel_navigation" id="cff_carousel_navigation" style="width: 400px;">
                        <option value="none" <?php if( $arrows == 'none' ) { echo 'selected'; } ?>>Hide arrows</option>
                        <option value="onhover" <?php if( $arrows == 'onhover' ) { echo 'selected'; } ?>>Display on sides of feed on hover</option>
                        <option value="below" <?php if( $arrows == 'below' ) { echo 'selected'; } ?>>Below feed, on sides of pagination</option>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php _e("Show Pagination"); ?></label><code class="cff_shortcode"> carouselpag
                        Eg: carouselpag=true</code></th>
                <td>
                    <input type="checkbox" name="cff_carousel_pagination" id="cff_carousel_pagination" <?php if($pagination == true) echo 'checked="checked"' ?> />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php _e("Enable Autoplay"); ?></label><code class="cff_shortcode"> carouselautoplay
                        Eg: carouselautoplay=true</code></th>
                <td>
                    <input type="checkbox" name="cff_carousel_autoplay" id="cff_carousel_autoplay" <?php if($autoplay == true) echo 'checked="checked"' ?> />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php _e("Interval Time"); ?></label><code class="cff_shortcode"> carouseltime
                        Eg: carouseltime=5000</code></th>
                <td>
                    <input name="cff_carousel_interval" type="text" value="<?php esc_attr_e( $interval ); ?>" size="6" /><?php _e("miliseconds"); ?>
                </td>
            </tr>
        </tbody>
    </table>
<?php
}
add_action('cff_post_layout_add_settings_area', 'cff_carousel_options');

/**
 * Updates the carousel options. Creates the option if none exists.
 *
 * This function runs right before the standard layout options are updated and
 * utilizes the same verification method
 *
 * @param array $post | $_POST data from a form submission on the post layout page
 */
function cff_ext_carousel_options_update( $post ) {
    $cff_carousel_options = array();
    isset( $post['cff_carousel_enabled'] ) ? $cff_carousel_options['cff_carousel_enabled'] = $post['cff_carousel_enabled'] : $cff_carousel_options['cff_carousel_enabled'] = '';
    isset( $post['cff_carousel_height'] ) ? $cff_carousel_options['cff_carousel_height'] = $post['cff_carousel_height'] : $cff_carousel_options['cff_carousel_height'] = '';
    isset( $post['cff_carousel_desktop_cols'] ) ? $cff_carousel_options['cff_carousel_desktop_cols'] = $post['cff_carousel_desktop_cols'] : $cff_carousel_options['cff_carousel_desktop_cols'] = '';
    isset( $post['cff_carousel_mobile_cols'] ) ? $cff_carousel_options['cff_carousel_mobile_cols'] = $post['cff_carousel_mobile_cols'] : $cff_carousel_options['cff_carousel_mobile_cols'] = '';
    isset( $post['cff_carousel_navigation'] ) ? $cff_carousel_options['cff_carousel_navigation'] = $post['cff_carousel_navigation'] : $cff_carousel_options['cff_carousel_navigation'] = '';
    isset( $post['cff_carousel_pagination'] ) ? $cff_carousel_options['cff_carousel_pagination'] = $post['cff_carousel_pagination'] : $cff_carousel_options['cff_carousel_pagination'] = '';
    isset( $post['cff_carousel_autoplay'] ) ? $cff_carousel_options['cff_carousel_autoplay'] = $post['cff_carousel_autoplay'] : $cff_carousel_options['cff_carousel_autoplay'] = '';
    isset( $post['cff_carousel_interval'] ) ? $cff_carousel_options['cff_carousel_interval'] = $post['cff_carousel_interval'] : $cff_carousel_options['cff_carousel_interval'] = '';

    update_option( 'cff_carousel_options', $cff_carousel_options );
}
add_action('cff_extension_post_layout_options_update', 'cff_ext_carousel_options_update' );
