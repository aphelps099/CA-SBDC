<?php 
/* 
Copyright 2019  Smash Balloon  (email: hey@smashballoon.com)
This program is paid software; you may not redistribute it under any
circumstances without the expressed written consent of the plugin author.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

define( 'REVIEWS_VER', '1.1.1' );
define( 'SB_ITEM_NAME_EXT_REVIEWS', 'Reviews Extension' );
if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
    // load our custom updater if it doesn't already exist
    include( dirname( __FILE__ ) . '/plugin_updater.php' );
}

// retrieve our license key from the DB
$license_key_ext_reviews = trim( get_option( 'cff_license_key_ext_reviews' ) );

//Include admin
include dirname( __FILE__ ) .'/cff-reviews-admin.php';


//Reviews URL
function cff_reviews_url( $page_id, $page_access_token, $cff_post_limit, $cff_locale, $cff_date_range ){
    return 'https://graph.facebook.com/' . $page_id . '/ratings?fields=created_time,rating,review_text,reviewer{name,id,picture},recommendation_type&access_token=' . $page_access_token . '&limit=' . $cff_post_limit. '&locale=' . $cff_locale . $cff_date_range; //Date Range doesn't work on reviews as API doesn't respect the since/until parameters;
}

//Reviews posts
function cff_ext_reviews($news, $cff_reviews_string, $atts, $page_id, $target, $cff_nofollow, $cff_author_styles, $cff_show_date, $cff_date_position, $cff_title_format, $cff_title_styles, $cff_posttext_link_color, $cff_see_more_text, $cff_date, $cff_title_link, $cff_see_less_text, $cff_show_facebook_link, $cff_post_bg_color_check, $post_time, $cff_item_styles, $cff_show_author, $cff_show_link, $cff_post_type, $link, $cff_link_styles, $cff_show_text, $cff_show_post, $cff_filter_string, $cff_exclude_string, $page_access_token=null){

    //Get rating of this review
    if( isset($news->rating) ){
        $cff_rating = intval($news->rating);
    }

    //Set reviews link
    $cff_reviews_link = 'https://www.facebook.com/' . $page_id . '/reviews';

    //Star size
    $cff_star_size = intval($atts['starsize']);

    //Reviews rated string
    $cff_reviews_string = $atts[ 'reviewsrated' ];
    $cff_reviews_string_arr = explode(',', $cff_reviews_string);
    if( isset($news->rating) ){
        if( empty($cff_reviews_string) || !isset($cff_reviews_string) ){
            $cff_show_post = true;
        } else {
            $cff_show_post = false;
            foreach( $cff_reviews_string_arr as $rating ) {
                if($rating == $cff_rating) $cff_show_post = true;
            }
        }
    }

    //Hide reviews with no text
    isset($atts[ 'reviewshidenotext' ]) ? $cff_reviews_no_text = $atts[ 'reviewshidenotext' ] : $cff_reviews_no_text = false;
                                    
    //RATING HTML
    $cff_stars = '';
    $cff_stars_html = '';
    if( isset($cff_rating) ){
        for ($i = 1; $i <= $cff_rating; $i++) {
            // $cff_stars .= '<i class="fa fa-star"></i>';
            $cff_stars .= '<span class="cff-star" style="font-size: '.$cff_star_size.'px;">&#x2605;</span>';
        }
        $cff_stars_html .= '<span class="cff-rating">'. $cff_stars . '<span class="cff-rating-num" style="font-size: '.(intval($cff_star_size)).'px;">' . $cff_rating . '</span></span>';
    }
    
    //POST AUTHOR HTML
    $cff_author = '';
    if( isset($news->reviewer) ){
        $cff_author .= '<div class="cff-author" '.$cff_author_styles.'>';
        
        //Author text
        $cff_author .= '<div class="cff-author-text">';
        if($cff_show_date && $cff_date_position !== 'above' && $cff_date_position !== 'below'){

            $cff_author .= '<p class="cff-page-name cff-author-date"><a href="' . $cff_reviews_link . '" '.$target.$cff_nofollow.' title="'.$news->reviewer->name.'"><b class="cff-reviewer-name">'.$news->reviewer->name.'</b>'.$cff_stars_html.'</a></p>';
            $cff_author .= $cff_date;
        } else {
            $cff_author .= '<span class="cff-page-name"><a href="' . $cff_reviews_link . '" '.$target.$cff_nofollow.' title="'.$news->reviewer->name.'"><b class="cff-reviewer-name">'.$news->reviewer->name.'</b>'.$cff_stars_html.'</a></span>';
        }
        $cff_author .= '</div>';
        
        //Get author avatar
        $cff_author_src = '';
        if( isset($news->reviewer->picture->data->url) ){
            $cff_author_src = $news->reviewer->picture->data->url;
        } else {
            //Check whether the profile pic exists in a transient
            $pic_transient_name = "fb_avatar_" . $news->reviewer->id;
            if ( false !== get_transient( $pic_transient_name ) ) $cff_author_src = get_transient( $pic_transient_name );
        }

        $cff_author .= '<div class="cff-author-img"><img src="'.$cff_author_src.'" alt="'.$news->reviewer->name.'" width=40 height=40 onerror="this.style.display=\'none\'"></div>';
        $cff_author .= '</div>'; //End .cff-author
    }


    //POST TEXT HTML
    $cff_post_text = '';
    if( isset($news->review_text) ){

        $cff_review_text = $news->review_text;
        //Replace line breaks in text (needed for IE8 and to prevent lost line breaks in HTML minification)
        $cff_review_text = preg_replace( "/\r\n|\r|\n/",'<br/>', htmlspecialchars($cff_review_text) );

        //Start HTML
        $cff_post_text = '<' . $cff_title_format . ' class="cff-post-text" ' . $cff_title_styles . '>';
        $cff_post_text .= '<span class="cff-text" data-color="'.$cff_posttext_link_color.'">';

        if ($cff_title_link){
            //Link to the Facebook post if it's a link or a video;
            ($cff_post_type == 'link' || $cff_post_type == 'video') ? $text_link = "https://www.facebook.com/" . $page_id . "/posts/" . $PostID[1] : $text_link = $link;

            $cff_post_text .= '<a class="cff-post-text-link" '.$cff_title_styles.' href="'.$cff_reviews_link.'" '.$target.$cff_nofollow.'>';
        }

        //Add the post text
        $cff_post_text .= $cff_review_text;
        
        if ($cff_title_link) $cff_post_text .= '</a>';
        $cff_post_text .= '</span>';

        //'See More' link
        $cff_post_text .= '<span class="cff-expand">... <a href="#" style="color: #'.$cff_posttext_link_color.'"><span class="cff-more">' . $cff_see_more_text . '</span><span class="cff-less">' . $cff_see_less_text . '</span></a></span>';
        $cff_post_text .= '</' . $cff_title_format . '>';
    }
    if( !isset($news->review_text) && $cff_reviews_no_text ){
        $cff_show_post = false;
    }


    //FILTERING
    if ( $cff_filter_string != '' ){
        //Explode it into multiples
        $cff_filter_strings_array = explode(',', $cff_filter_string);
        if ( cff_stripos_arr($cff_post_text, $cff_filter_strings_array) === false ) $cff_show_post = false;
    }
    if ( $cff_exclude_string != '' ){
        //Explode it into multiples
        $cff_exclude_strings_array = explode(',', $cff_exclude_string);
        if ( cff_stripos_arr($cff_post_text, $cff_exclude_strings_array) !== false ) $cff_show_post = false;
    }

    
    //POST ACTION LINK HTML
    $cff_link = '';
    if($cff_show_facebook_link){
        $cff_link .= '<div class="cff-post-links"';
        if( !isset($news->review_text) ) $cff_link .= ' style="margin-top: 0;"';
        $cff_link .= '>';
        //Default link
        $cff_reviews_link_text = $atts['reviewslinktext'];
        if ($cff_reviews_link_text == '') $cff_reviews_link_text = 'View all Reviews';
        //View on Facebook link
        if($cff_show_facebook_link) $cff_link .= '<a class="cff-viewpost-facebook" href="'.$cff_reviews_link.'" ' . $target . $cff_nofollow.' ' . $cff_link_styles . '>' . $cff_reviews_link_text . '</a>';
        $cff_link .= '</div>'; 
    }

    //Hide negative reviews
    $cff_reviews_hide_negative = isset($atts['hidenegative']) ? $atts['hidenegative'] : '';
    ( $cff_reviews_hide_negative == 'true' || $cff_reviews_hide_negative == 'on' ) ? $cff_reviews_hide_negative = true : $cff_reviews_hide_negative = false;
    if( isset($news->recommendation_type) && $cff_reviews_hide_negative ){
        if($news->recommendation_type == 'negative') $cff_show_post = false;
    }
    
    //REVIEW POST HTML
    $cff_post_item = '';
    if($cff_show_post){

        if( isset($cff_rating) ){
            $cff_review_class = 'cff-' . $cff_rating . 'stars';
        } else {
            $cff_review_class = 'cff-recommendation';
        }
        if( isset($news->recommendation_type) ) $cff_review_class .= ' cff-' . $news->recommendation_type;

        $cff_post_item .= '<div class="cff-item cff-review cff-new ' . $cff_review_class;

        $cff_post_style = isset($atts['poststyle']) ? $atts['poststyle'] : '';
        $cff_box_shadow = isset($atts['boxshadow']) ? $atts['boxshadow'] : '';
        ( ($cff_box_shadow == 'true' || $cff_box_shadow == 'on') && $cff_post_style == 'boxed' ) ? $cff_box_shadow = true : $cff_box_shadow = false;

        if ($cff_post_bg_color_check || $cff_post_style == 'boxed') $cff_post_item .= ' cff-box';
        if( $cff_box_shadow ) $cff_post_item .= ' cff-shadow';

        if( isset($news->reviewer) ){
            $cff_post_item .= ' author-';
            if(isset($news->reviewer->name)) $cff_post_item .= cff_to_slug($news->reviewer->name);
        }
        $cff_post_item .= '" id="cff_';

        if( isset($news->reviewer) ) $cff_post_item .= cff_to_slug($news->reviewer->name) .'_';

        $cff_post_item .= strtotime($post_time) . '" ' . $cff_item_styles . '>';

        //POST AUTHOR
        if($cff_show_author) $cff_post_item .= $cff_author;
        //DATE ABOVE
        if ($cff_show_date && $cff_date_position == 'above') $cff_post_item .= $cff_date;
        
        //POST TEXT
        if($cff_show_text) $cff_post_item .= $cff_post_text;

        //DATE BELOW
        if ( $cff_show_date && $cff_date_position == 'below' ) $cff_post_item .= $cff_date;

        //POST ACTIONS LINK
        if ($cff_show_link) $cff_post_item .= $cff_link;
    
        $cff_post_item .= '</div>';
    }

	return $cff_post_item;
}


//Uninstall
function cff_ext_reviews_uninstall()
{
    if ( ! current_user_can( 'activate_plugins' ) )
        return;
    //Settings
    delete_option( 'page_access_token_val' );

    //Deactivate and delete license
    // retrieve the license from the database
    $license_key_ext_reviews = trim( get_option( 'cff_license_key_ext_reviews' ) );
    // data to send in our API request
    $api_params = array( 
        'edd_action'=> 'deactivate_license', 
        'license'   => $license_key_ext_reviews, 
        'item_name' => urlencode( SB_ITEM_NAME_EXT_REVIEWS ) // the name of our product in EDD
    );
    // Call the custom API.
    $response = wp_remote_get( add_query_arg( $api_params, 'https://smashballoon.com/' ), array( 'timeout' => 15, 'sslverify' => false ) );
    delete_option( 'cff_license_status_ext_reviews' );
    delete_option( 'cff_license_key_ext_reviews' );
}
register_uninstall_hook( __FILE__, 'cff_ext_reviews_uninstall' );

?>