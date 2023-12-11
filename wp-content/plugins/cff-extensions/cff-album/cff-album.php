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

define( 'ALBUM_VER', '1.0.5' );
define( 'SB_ITEM_NAME_ALBUM', 'Album Extension' );
if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
    // load our custom updater if it doesn't already exist
    include( dirname( __FILE__ ) . '/plugin_updater.php' );
}

// retrieve our license key from the DB
$license_key_album = trim( get_option( 'cff_license_key_album' ) );

//Include admin
include dirname( __FILE__ ) .'/cff-album-admin.php';


//Album Extension
function cff_album_id($cff_album_id, $access_token, $cff_post_limit, $cff_date_range){
    return 'https://graph.facebook.com/'.$cff_album_id.'/photos?fields=source,name,images&access_token='. $access_token .'&limit=' . $cff_post_limit . $cff_date_range;
}

function cff_get_album_details($feed_options, $cff_header_styles, $cff_header_outside){

	//Set required variables
	$cff_album_id = $feed_options['album'];
	$access_token = $feed_options['accesstoken'];
	$cff_cache_time = $feed_options['cachetime'];
    //Posted ago strings
    $cff_date_translate_strings = array(
        'cff_translate_second' => $feed_options['secondtext'],
        'cff_translate_second' => $feed_options['secondtext'],
        'cff_translate_seconds' => $feed_options['secondstext'],
        'cff_translate_minute' => $feed_options['minutetext'],
        'cff_translate_minutes' => $feed_options['minutestext'],
        'cff_translate_hour' => $feed_options['hourtext'],
        'cff_translate_hours' => $feed_options['hourstext'],
        'cff_translate_day' => $feed_options['daytext'],
        'cff_translate_days' => $feed_options['daystext'],
        'cff_translate_week' => $feed_options['weektext'],
        'cff_translate_weeks' => $feed_options['weekstext'],
        'cff_translate_month' => $feed_options['monthtext'],
        'cff_translate_months' => $feed_options['monthstext'],
        'cff_translate_year' => $feed_options['yeartext'],
        'cff_translate_years' => $feed_options['yearstext'],
        'cff_translate_ago' => $feed_options['agotext']
    );

	//Create the API URL
	$album_details_json_url = 'https://graph.facebook.com/v4.0/'.$cff_album_id.'?fields=description,name,count,updated_time,created_time&access_token='. $access_token;

	//Create the transient name
    $transient_name = 'cff_album_details_' . $cff_album_id;
	$transient_name = substr($transient_name, 0, 45);

	//Get the data
	if( class_exists('CustomFacebookFeed\CFF_Utils') ){
		$album_details = CustomFacebookFeed\CFF_Utils::cff_get_set_cache($album_details_json_url, $transient_name, $cff_cache_time, 6*MONTH_IN_SECONDS, $data_att_html='', $cff_show_access_token=true, $access_token);
	}else{
		$album_details = cff_get_set_cache($album_details_json_url, $transient_name, $cff_cache_time, 6*MONTH_IN_SECONDS, $data_att_html='', $cff_show_access_token=true, $access_token);
	}

	$album_details = json_decode($album_details);

	//Create the album extension header
	$cff_album_header = '<div class="cff-header cff-album-details';
    if ($cff_header_outside) $cff_album_header .= ' cff-outside';
    $cff_album_header .= '" ' . $cff_header_styles . '>';

		if( isset($album_details->name) ) $cff_album_header .= '<h3 class="cff-album-title" '.$cff_header_styles.'>' . $album_details->name . '</h3>';
		if( isset($album_details->count) || isset($album_details->updated_time) ){
			if( class_exists('CustomFacebookFeed\CFF_Utils') ){
				$cff_timeSince = CustomFacebookFeed\CFF_Utils::cff_timeSince(strtotime($album_details->updated_time), $cff_date_translate_strings);
			}else{
				$cff_timeSince = cff_timeSince(strtotime($album_details->updated_time), $cff_date_translate_strings);
			}
			$cff_album_header .= '<p class="cff-album-desc-meta">';
			if( isset($album_details->count) ) $cff_album_header .= '<span class="cff-album-photo-count">'. $album_details->count . ' Photos &middot;</span>';
			if( isset($album_details->updated_time) ) $cff_album_header .= '<span class="cff-album-updated-time"> Updated '. $cff_timeSince .'</span>';
			$cff_album_header .= '</p>';
		}
		if( isset($album_details->description) ) $cff_album_header .= '<p class="cff-album-desc">'. $album_details->description . '</p>';

	$cff_album_header .= '</div>';

	return $cff_album_header;
}

?>