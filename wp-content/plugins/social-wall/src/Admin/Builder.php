<?php

namespace SB\SocialWall\Admin;

use SB\SocialWall\Database;
use SB\SocialWall\Feed_Locator;

class Builder {

    public static function sbsw_builder_update() {
        check_ajax_referer( 'sbsw_admin_settings' , 'nonce');

		$settings_data = $_POST;
		unset( $settings_data['nonce'] );

		$feed_id = false;
		$is_new_feed = isset($settings_data['new_insert'] ) ? true : false;
		$update_feed = isset( $settings_data['update_feed'] ) ? true : false;
		if ( ! empty( $settings_data['feed_id'] ) ) {
			$feed_id = sanitize_text_field( $settings_data['feed_id'] );
			unset( $settings_data['feed_id'] );
		} elseif ( isset( $settings_data['feed_id'] ) ) {
			unset( $settings_data['feed_id'] );
		}
		unset( $settings_data['action'] );
		unset( $settings_data['new_insert'] );
		unset( $settings_data['update_feed'] );

		$feed_name = '';
		if ( $update_feed ) {
			$feed_name = $settings_data['feed_name'];
    		unset( $settings_data['feed_name'] );
			$settings_data = json_decode( stripslashes( $settings_data['settings'] ), true );
		}

        // Update the num option
        if ( isset( $settings_data['numdesktop'] ) ) {
            $settings_data['num'] = $settings_data['numdesktop'];
        }
        
        $feed_saver = new Feed_Saver( $feed_id );
		$feed_saver->set_feed_name( $feed_name );
		$feed_saver->set_data( $settings_data );

		$return = array(
			'success' => false,
			'feed_id' => false
		);

        if ( $feed_saver->update() ) {
			$return = array(
				'success' => true,
				'feed_id' => $feed_id
			);
            echo wp_json_encode( $return );
            wp_die();
        }

        echo wp_json_encode( $return );
        wp_die();
    }

    /**
     * Clear feed cache
     * 
     * @since 2.0
     */
    public static function sbsw_clear_feed_cache() {
        check_ajax_referer( 'sbsw_admin_settings' , 'nonce');
        
        // clear feed cache
        sbsw_clear_cache();
        // refresh feed with new posts
        if ( isset( $_POST['feed_id'] ) ) {
            $feed_id = filter_var($_POST['feed_id'], FILTER_VALIDATE_INT);
            $preview_settings = json_decode(stripslashes($_POST['settings']), true);
            $customizer_data = SW_Feed_Builder::customizer_feed_data( $feed_id, $preview_settings );
            // send back ajax response
            wp_send_json_success(array(
                'feedData' => $customizer_data
            ));
        }

        wp_send_json_success();
    }

    /**
     * Refresh wall plugins
     * 
     * @since 2.0
     */
    public static function refresh_wall_plugins() {
		check_ajax_referer( 'sbsw_admin_settings', 'nonce' );
        // Get updated social wall plugins feeds
        $feeds = Localize::get_plugins_feeds();
        
        wp_send_json_success(array(
            'feeds' => $feeds
        ));
    }

    /**
     * Get social wall feeds 
     * 
     * @since 2.0
     */
    public static function get_feeds() {
        $queried_feeds = Database::query_feeds();
        $feeds = array();
        foreach( $queried_feeds as $key => $feed ) {
            $plugins_name = (array) json_decode($feed['feeds']);
            $locations = self::get_feed_location_summary($feed);    
            $feeds[$key] = array(
                'id' => $feed['id'],
                'feed_name' => $feed['feed_name'],
                'feeds' => implode('+', array_keys($plugins_name)),
                'settings' => $feed['settings'],
                'instance_count' => count($locations),
                'location_summary' => $locations,
            );
        }
        return $feeds;
    }

    /**
     * Get legacy feeds info
     * 
     * This determines whether the user had legacy feeds 
     * @since 2.0
     */
    public static function legacy_feeds_info() {
        $sbsw_legacy_support = get_option( 'sbsw_legacy_support', false );
        $legacy_feed_exists = Database::check_legacy_feed();

        return array(
            'legacy_feed_exists' => $sbsw_legacy_support && count( $legacy_feed_exists ) > 0
        );
    }

    /**
     * Get the feed location summary for specific feed
     * 
     * @since 2.0
     */
    public static function get_feed_location_summary( $feed ) {
        $legacy = false;
        $args              = array(
            'html_location'  => array( 'content' ),
            'group_by' => 'post_id'
        );
        $args['feed_id'] = $feed['id'];
        $content_locations = Feed_Locator::legacy_feed_locator_query( $args );
        if ( count( $content_locations ) < Database::RESULTS_PER_PAGE ) {
            $args            = array(
                'feed_id'       => $legacy ? $feed['feed_id'] : $feed['id'],
                'html_location' => array( 'header', 'footer', 'sidebar' ),
                'group_by'      => 'html_location',
            );
            $other_locations = Feed_Locator::legacy_feed_locator_query( $args );
            $combined_locations = array_merge( $other_locations, $content_locations );
        } else {
            $combined_locations = $content_locations;
        }
        $locations = array();
        foreach ( $combined_locations as $location ) {
            $page_text = get_the_title( $location['post_id'] );
            if ( $location['html_location'] === 'header' ) {
                $html_location = __( 'Header', 'social-wall' );
            } elseif ( $location['html_location'] === 'footer' ) {
                $html_location = __( 'Footer', 'social-wall' );
            } elseif ( $location['html_location'] === 'sidebar' ) {
                $html_location = __( 'Sidebar', 'social-wall' );
            } else {
                $html_location = __( 'Content', 'social-wall' );
            }
            $full_shortcode_string = '[social-wall feed='. $feed['id'] .']';

            $locations[] = array(
                'link'          => esc_url( get_the_permalink( $location['post_id'] ) ),
                'page_text'     => $page_text,
                'html_location' => $html_location,
                'shortcode'     => $full_shortcode_string,
            );
        }

        return $locations;
    }

    /**
	 * Used to retrieve Feed Posts for preview screen
	 * Returns Feed info or false!
	 *
	 *
	 *
	 * @since 2.0
	 */
	public static function feed_customizer_fly_preview() {
        check_ajax_referer( 'sbsw_admin_settings' , 'nonce');
        
		if ( isset( $_POST['feedID'] ) && isset( $_POST['previewSettings'] ) ) {
            $return = [
                'posts' => []
            ];
			$preview_settings = json_decode(stripslashes($_POST['previewSettings']), true);

            $feed_id = sanitize_text_field($_POST['feedID']);
            sbsw_clear_cache();
            $customizer_data = SW_Feed_Builder::customizer_feed_data( $feed_id, $preview_settings );
            wp_send_json_success(array(
                'feedData' => $customizer_data
            ));
		}
        wp_send_json_error();
		wp_die();
	}

    public static function create_feed() {
        check_ajax_referer( 'sbsw_admin_settings', 'nonce' );
        $feeds = (array) json_decode(stripslashes( sanitize_text_field( $_POST['feeds'] ) ));
        if ( empty( $feeds ) ) {
            wp_send_json_error();
        }
        $feed_name = self::generate_feed_name();
        $data = array(
            array(
                'key' => 'feed_name',
                'value' => $feed_name,
            ),
            array(
                'key' => 'feeds',
                'value' => json_encode($feeds),
            ),
            array(
                'key' => 'settings',
                'value' => json_encode(array()),
            ),
            array(
                'key' => 'status',
                'value' => 'published',
            ),
        );

        $feed_id = Database::feeds_insert( $data );

        wp_send_json_success(array(
            'success' => true,
            'feed_id' => $feed_id
        ));
    }

    public static function generate_feed_name() {
        return 'Social Wall';
    }

    public static function sanitize_and_sort_data( $feeds ) {

    }

    /**
     * Refresh the feeds tables
     * 
     * @since 2.0
     */
    public static function refresh_feeds() {
		check_ajax_referer( 'sbsw_admin_settings', 'nonce' );
        // Get updated feeds
        $feeds = self::get_feeds();
        
        wp_send_json_success(array(
            'feeds' => $feeds
        ));
    }

    /**
     * Delete feed and send new feeds
     * 
     * @since 2.0
     */
	public static function delete_feed() {
		check_ajax_referer( 'sbsw_admin_settings', 'nonce' );
		$feed = sanitize_text_field( $_POST['feed'] );
        if ( !$feed ) {
            wp_send_json_error();
        }
        // Delete the feed
        Database::delete_feed( $feed );
        // Get updated feeds
        $feeds = self::get_feeds();
        
        wp_send_json_success(array(
            'has_feeds' => count( $feeds ),
            'feeds' => $feeds
        ));
	}

    /**
     * Bulk delete feed and send new feeds
     * 
     * @since 2.0
     */
	public static function bulk_delete_feed() {
		check_ajax_referer( 'sbsw_admin_settings', 'nonce' );
		$feeds = sanitize_text_field( $_POST['feeds'] );
        if ( !$feeds ) {
            wp_send_json_error();
        }
        // Delete the feed
        Database::bulk_delete_feeds( $feeds );
        // Get updated feeds
        $feeds = self::get_feeds();
        
        wp_send_json_success(array(
            'has_feeds' => count( $feeds ),
            'feeds' => $feeds
        ));
	}

    /**
     * Duplicate the feed and send new feeds
     * 
     * @since 2.0
     */
	public static function duplicate_feed() {
		check_ajax_referer( 'sbsw_admin_settings', 'nonce' );
		$feed = sanitize_text_field( $_POST['feed'] );
        
        // Delete the feed
        Database::duplicate_feed( $feed );
        // Get updated feeds
        $feeds = self::get_feeds();
        wp_send_json_success(array(
            'feeds' => $feeds
        ));
	}

    /**
     * Duplicate the feed and send new feeds
     * 
     * @since 2.0
     */
	public static function update_source() {
		check_ajax_referer( 'sbsw_admin_settings', 'nonce' );
		
        if ( !isset( $_POST['feed_id'] ) && filter_var($_POST['feed_id'], FILTER_VALIDATE_INT) == false ) {
            return;
        }
        $feed_id = sanitize_text_field( $_POST['feed_id'] );
        $wall_plugin = sanitize_text_field( $_POST['wall_plugin'] );
        $wall_plugin = \json_decode( \stripslashes( $wall_plugin ), true );
        // remove source from db
        $updated = Database::update_source( $feed_id, $wall_plugin );

        if ( !$updated ) {
            wp_send_json_error();
        }

        // generate new preview
        $customizer_data = SW_Feed_Builder::customizer_feed_data( $feed_id );
        wp_send_json_success(array(
            'feedData' => $customizer_data
        ));
	}

    /**
     * Duplicate the feed and send new feeds
     * 
     * @since 2.0
     */
	public static function remove_source() {
		check_ajax_referer( 'sbsw_admin_settings', 'nonce' );
		
        if ( !isset( $_POST['feed_id'] ) && filter_var($_POST['feed_id'], FILTER_VALIDATE_INT) == false ) {
            return;
        }
        $feed_id = sanitize_text_field( $_POST['feed_id'] );
        $feed_plugin = sanitize_text_field( $_POST['feed_plugin'] );

        // remove source from db
        $removed = Database::remove_source( $feed_id, $feed_plugin );
        if ( !$removed ) {
            wp_send_json_error();
        }

        // generate new preview
        $customizer_data = SW_Feed_Builder::customizer_feed_data( $feed_id );
        wp_send_json_success(array(
            'feedData' => $customizer_data
        ));
	}
}