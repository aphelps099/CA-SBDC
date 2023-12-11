<?php 

function cff_register_setting_license_ext_reviews(){
    register_setting('cff_license', 'cff_license_key_ext_reviews', 'cff_sanitize_license_ext_reviews' );
}
add_action('cff_register_setting_license', 'cff_register_setting_license_ext_reviews');

function cff_sanitize_license_ext_reviews( $new_ext_reviews ) {
    $old_ext_reviews = get_option( 'cff_license_key_ext_reviews' );
    if( $old_ext_reviews && $old_ext_reviews != $new_ext_reviews ) {
        delete_option( 'cff_license_status_ext_reviews' ); // new license has been entered, so must reactivate
    }
    return $new_ext_reviews;
}
function cff_activate_license_ext_reviews() {
    // listen for our activate button to be clicked
    if( isset( $_POST['cff_license_activate_ext_reviews'] ) ) {
        // run a quick security check 
        if( ! check_admin_referer( 'cff_nonce_ext_reviews', 'cff_nonce_ext_reviews' ) )   
            return; // get out if we didn't click the Activate button
        // retrieve the license from the database
        $license_ext_reviews = trim( get_option( 'cff_license_key_ext_reviews' ) );
            
        // data to send in our API request
        $api_params = array( 
            'edd_action'=> 'activate_license', 
            'license'   => $license_ext_reviews, 
            'item_name' => urlencode( SB_ITEM_NAME_EXT_REVIEWS ) // the name of our product in EDD
        );
        
        // Call the custom API.
        $response_ext_reviews = wp_remote_get( add_query_arg( $api_params, 'http://smashballoon.com/' ), array( 'timeout' => 60, 'sslverify' => false ) );
        // make sure the response came back okay
        if ( is_wp_error( $response_ext_reviews ) )
            return false;
        // decode the license data
        $license_data_ext_reviews = json_decode( wp_remote_retrieve_body( $response_ext_reviews ) );
        
        // $license_data->license will be either "active" or "inactive"
        update_option( 'cff_license_status_ext_reviews', $license_data_ext_reviews->license );
    }
}
add_action('admin_init', 'cff_activate_license_ext_reviews');
function cff_deactivate_license_ext_reviews() {
    // listen for our activate button to be clicked
    if( isset( $_POST['cff_license_deactivate_ext_reviews'] ) ) {
        // run a quick security check 
        if( ! check_admin_referer( 'cff_nonce_ext_reviews', 'cff_nonce_ext_reviews' ) )   
            return; // get out if we didn't click the Activate button
        // retrieve the license from the database
        $license_ext_reviews = trim( get_option( 'cff_license_key_ext_reviews' ) );
            
        // data to send in our API request
        $api_params = array( 
            'edd_action'=> 'deactivate_license', 
            'license'   => $license_ext_reviews, 
            'item_name' => urlencode( SB_ITEM_NAME_EXT_REVIEWS ) // the name of our product in EDD
        );
        
        // Call the custom API.
        $response_ext_reviews = wp_remote_get( add_query_arg( $api_params, 'http://smashballoon.com/' ), array( 'timeout' => 15, 'sslverify' => false ) );
        // make sure the response came back okay
        if ( is_wp_error( $response_ext_reviews ) )
            return false;
        // decode the license data
        $license_data_ext_reviews = json_decode( wp_remote_retrieve_body( $response_ext_reviews ) );
        
        // $license_data->license will be either "deactivated" or "failed"
        if( $license_data_ext_reviews->license == 'deactivated' )
            delete_option( 'cff_license_status_ext_reviews' );
    }
}
add_action('admin_init', 'cff_deactivate_license_ext_reviews');


//Return license key field
function cff_ext_reviews_license(){

	$license_ext_reviews = get_option( 'cff_license_key_ext_reviews' );
    $status_ext_reviews  = get_option( 'cff_license_status_ext_reviews' ); ?>

	<tr valign="top">   
	    <th scope="row" valign="top">Reviews Extension</th>
	    <td>
	        <input id="cff_license_key_ext_reviews" name="cff_license_key_ext_reviews" type="text" class="regular-text" value="<?php echo $license_ext_reviews ?>" />
	
			<?php if( false !== $license_ext_reviews ) {
	            if( $status_ext_reviews !== false && $status_ext_reviews == 'valid' ) {
	                wp_nonce_field( 'cff_nonce_ext_reviews', 'cff_nonce_ext_reviews' ); ?>
	                <input type="submit" class="button-secondary" name="cff_license_deactivate_ext_reviews" value="Deactivate License"/>
	                <span style="color:green; padding-left: 4px;">Active</span>
	            <?php } else { 
	                wp_nonce_field( 'cff_nonce_ext_reviews', 'cff_nonce_ext_reviews' ); ?>
	                <input type="submit" class="button-secondary" name="cff_license_activate_ext_reviews" value="Activate License"/>
	                <span style="color:red; padding-left: 4px;">Inactive</span>
	            <?php }
	        }  ?>
	        <br /><i style="color: #666; font-size: 11px;">The license key you received when purchasing the Reviews extension.</i>
	    </td>
	</tr>

    <?php 
}
// add_action('cff_admin_license', 'cff_ext_reviews_license');

function cff_ext_reviews_token($page_access_token_val) {
    $cff_ext_reviews_token_field = '
        <tr valign="top" class="cff-reviews-access-token">
            <th scope="row" style="padding-bottom: 10px;"><label>Reviews Access Token</label><code class="cff_shortcode"> pagetoken
            Eg: pagetoken="ACCESS_TOKEN"</code></th>
            <td>
                <textarea name="cff_page_access_token" id="cff_page_access_token" style="min-width: 60%;">'. $page_access_token_val .'</textarea><br /><a class="cff-tooltip-link" href="JavaScript:void(0);" style="margin-left: 2px;">What is this and where do I get it?</a>
                <p class="cff-tooltip cff-more-info">In order to display Facebook Reviews you are required to obtain an Access Token from the page that you want to display reviews from. To get an Access Token for your page just click the blue Facebook login button at the top of this settings page and follow the directions. You can then copy and paste that token into this settings field.</br></br>This token can only be used to display reviews from this specific Facebook page, so if you\'re displaying multiple review feeds from different Facebook pages then you will need to set the Access Token in the shortcode for each feed: <code>[custom-facebook-feed pagetoken=\'ACCESS_TOKEN_FOR_THIS_PAGE\']</code></p>
            </td>
        </tr>';

    return $cff_ext_reviews_token_field;
}

function cff_ext_reviews_options($cff_reviews_rated_5, $cff_reviews_rated_4, $cff_reviews_rated_3, $cff_reviews_rated_2, $cff_reviews_rated_1,  $cff_star_size, $cff_reviews_link_text, $cff_reviews_no_text = false, $cff_reviews_method = 'auto', $cff_reviews_hide_negative = false) {

    $cff_ext_reviews_fields = '
        <hr id="reviews" />
            <table class="form-table">
                <tbody>
                    <h3>Reviews</h3>

                    <tr>
                        <th class="bump-left" scope="row"><label>Show reviews rated:</label><code class="cff_shortcode"> reviewsrated
            Eg: reviewsrated="4,5"</code></th>
                        <td>
                            <div>
                                <input name="cff_reviews_rated_5" type="checkbox" id="cff_reviews_rated_5" '. ($cff_reviews_rated_5 == true ? 'checked' : '') .' />
                                <label for="cff_reviews_rated_5">5 star</label>
                            </div>
                            <div>
                                <input name="cff_reviews_rated_4" type="checkbox" id="cff_reviews_rated_4" '. ($cff_reviews_rated_4 == true ? 'checked' : '') .' />
                                <label for="cff_reviews_rated_4">4 star</label>
                            </div>
                            <div>
                                <input name="cff_reviews_rated_3" type="checkbox" id="cff_reviews_rated_3" '. ($cff_reviews_rated_3 == true ? 'checked' : '') .' />
                                <label for="cff_reviews_rated_3">3 star</label>
                            </div>
                            <div>
                                <input name="cff_reviews_rated_2" type="checkbox" id="cff_reviews_rated_2" '. ($cff_reviews_rated_2 == true ? 'checked' : '') .' />
                                <label for="cff_reviews_rated_2">2 star</label>
                            </div>
                            <div>
                                <input name="cff_reviews_rated_1" type="checkbox" id="cff_reviews_rated_1" '. ($cff_reviews_rated_1 == true ? 'checked' : '') .' />
                                <label for="cff_reviews_rated_1">1 star</label>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th class="bump-left" scope="row"><label for="cff_reviews_hide_negative" class="bump-left">Hide negative recommendations</label><code class="cff_shortcode"> hidenegative
                Eg: hidenegative=true</code></th>
                        <td>
                            <input name="cff_reviews_hide_negative" type="checkbox" id="cff_reviews_hide_negative" '. ($cff_reviews_hide_negative == true ? 'checked' : '') .' />
                        </td>
                    </tr>
                    <tr>

                    <tr>
                        <th class="bump-left" scope="row"><label>Star icon size</label><code class="cff_shortcode"> starsize
            Eg: starsize=28</code></th>
                        <td>
                            <select name="cff_star_size">
                                <option value="inherit"'. ($cff_star_size == "inherit" ? 'selected="selected"' : '') .' >Inherit</option>
                                <option value="10"'. ($cff_star_size == "10" ? 'selected="selected"' : '') .' >10px</option>
                                <option value="11"'. ($cff_star_size == "11" ? 'selected="selected"' : '') .' >11px</option>
                                <option value="12"'. ($cff_star_size == "12" ? 'selected="selected"' : '') .' >12px</option>
                                <option value="13"'. ($cff_star_size == "13" ? 'selected="selected"' : '') .' >13px</option>
                                <option value="14"'. ($cff_star_size == "14" ? 'selected="selected"' : '') .' >14px</option>
                                <option value="16"'. ($cff_star_size == "16" ? 'selected="selected"' : '') .' >16px</option>
                                <option value="18"'. ($cff_star_size == "18" ? 'selected="selected"' : '') .' >18px</option>
                                <option value="20"'. ($cff_star_size == "20" ? 'selected="selected"' : '') .' >20px</option>
                                <option value="24"'. ($cff_star_size == "24" ? 'selected="selected"' : '') .' >24px</option>
                                <option value="28"'. ($cff_star_size == "28" ? 'selected="selected"' : '') .' >28px</option>
                                <option value="32"'. ($cff_star_size == "32" ? 'selected="selected"' : '') .' >32px</option>
                                <option value="36"'. ($cff_star_size == "36" ? 'selected="selected"' : '') .' >36px</option>
                                <option value="42"'. ($cff_star_size == "42" ? 'selected="selected"' : '') .' >42px</option>
                                <option value="48"'. ($cff_star_size == "48" ? 'selected="selected"' : '') .' >48px</option>
                                <option value="54"'. ($cff_star_size == "54" ? 'selected="selected"' : '') .' >54px</option>
                                <option value="60"'. ($cff_star_size == "60" ? 'selected="selected"' : '') .' >60px</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th class="bump-left"><label for="cff_reviews_link_text" class="bump-left">"View all Reviews" text</label><code class="cff_shortcode"> reviewslinktext
                Eg: reviewslinktext="All Reviews"</code></th>
                        <td>
                            <input name="cff_reviews_link_text" type="text" value="'. stripslashes( esc_attr( $cff_reviews_link_text ) ) .'" size="25" />
                        </td>
                    </tr>
                    <tr>
                        <th class="bump-left"><label for="cff_reviews_no_text" class="bump-left">Hide reviews with no text</label><code class="cff_shortcode"> reviewshidenotext
                Eg: reviewshidenotext=true</code></th>
                        <td>
                            <input name="cff_reviews_no_text" type="checkbox" id="cff_reviews_no_text" '. ($cff_reviews_no_text == true ? 'checked' : '') .' />
                        </td>
                    </tr>
                    <tr>
                        <th class="bump-left"><label for="cff_reviews_method" class="bump-left">API retrieval method</label><code class="cff_shortcode"> reviewsmethod
                Eg: reviewsmethod=batch
                reviewsmethod=all</code></th>
                        <td>
                            <select name="cff_reviews_method">
                                <option value="auto"'. ($cff_reviews_method == "auto" ? 'selected="selected"' : '') .' >Auto</option>
                                <option value="batch"'. ($cff_reviews_method == "batch" ? 'selected="selected"' : '') .' >Batch</option>
                                <option value="all"'. ($cff_reviews_method == "all" ? 'selected="selected"' : '') .' >All</option>
                            </select>
                            <a class="cff-tooltip-link" href="JavaScript:void(0);">What is this?</a>
                            <p class="cff-tooltip cff-more-info">Most users should leave this set as "Auto" and only change it if directed by a member of the support team.</p>
                        </td>
                    </tr>
                </tbody>
            </table>';

    return $cff_ext_reviews_fields;

}

?>