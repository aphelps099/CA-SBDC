<?php

namespace SB\SocialWall\Admin;

class Feed_Saver_Manager {

    public static function maybe_feed_customizer_data() {
        $feed_id  = $_GET['feed_id'];
        $feed_saver = new Feed_Saver( $feed_id );
        $settings = $feed_saver->get_feed_settings();
        $feed_db_data = $feed_saver->get_feed_db_data();
        
        if($settings != false){
            $return = array(
                'feed_info' => $feed_db_data,
                'settings' => $settings,
                'posts' => array()
            );
            return $return;
        }

		return false;
    }
}