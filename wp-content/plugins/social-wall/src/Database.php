<?php

namespace SB\SocialWall;

/**
 * Class Database
 */
class Database {

    const RESULTS_PER_PAGE = 20;

	protected static $feeds_table = 'sw_feeds';
	
    public static function create_tables() {
        if ( !function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . '/wp-admin/includes/upgrade.php';
		}

		global $wpdb;
		$max_index_length = 191;
		$charset_collate = '';
		if ( method_exists( $wpdb, 'get_charset_collate' ) ) { // get_charset_collate introduced in WP 3.5
			$charset_collate = $wpdb->get_charset_collate();
		}

		$feeds_table_name = $wpdb->prefix . self::$feeds_table;
		if ( $wpdb->get_var( "show tables like '$feeds_table_name'" ) != $feeds_table_name ) {
			$sql = "
			CREATE TABLE $feeds_table_name (
			 id bigint(20) unsigned NOT NULL auto_increment,
			 feed_name text NOT NULL default '',
			 feeds longtext NOT NULL default '',
			 settings longtext NOT NULL default '',
			 author bigint(20) unsigned NOT NULL default '1',
			 status varchar(255) NOT NULL default '',
			 last_modified datetime NOT NULL,
			 PRIMARY KEY  (id),
			 KEY author (author)
			) $charset_collate;
			";
			$wpdb->query( $sql );
		}

		$feeds_table_name = $wpdb->prefix . 'sw_feed_locator';
		if ( $wpdb->get_var( "show tables like '$feeds_table_name'" ) != $feeds_table_name ) {
			$sql = "
			CREATE TABLE $feeds_table_name (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                feed_id VARCHAR(50) DEFAULT '' NOT NULL,
                post_id BIGINT(20) UNSIGNED NOT NULL,
                html_location VARCHAR(50) DEFAULT 'unknown' NOT NULL,
                shortcode_atts LONGTEXT NOT NULL,
                last_update DATETIME
			) $charset_collate;
			";
			$wpdb->query( $sql );
		}
    }

	/**
	 * Count the sby_feeds table
	 *
	 * @return int
	 *
	 * @since 2.0
	 */
	public static function feeds_count() {
		global $wpdb;
		$feeds_table_name = $wpdb->prefix . self::$feeds_table;
		$results          = $wpdb->get_results(
			"SELECT COUNT(*) AS num_entries FROM $feeds_table_name",
			ARRAY_A
		);
		return isset( $results[0]['num_entries'] ) ? (int) $results[0]['num_entries'] : 0;
	}

	/**
	 * New feed data is added to the sw_feeds table and
	 * the new insert ID is returned
	 *
	 * @param array $to_insert
	 *
	 * @return false|int
	 *
	 * @since 2.0
	 */
	public static function feeds_insert( $to_insert ) {
		global $wpdb;
		$feeds_table_name = $wpdb->prefix  . self::$feeds_table;

		$data = array();
		$format = array();
		foreach ( $to_insert as $single_insert ) {
			if ( $single_insert['key'] ) {
				$data[ $single_insert['key'] ] = $single_insert['value'];
				$format[] = '%s';
			}
		}

		$data['last_modified'] = date( 'Y-m-d H:i:s' );
		$format[] = '%s';

		$data['author'] = get_current_user_id();
		$format[] = '%d';

		$wpdb->insert( $feeds_table_name, $data, $format );
		return $wpdb->insert_id;
	}

	/**
	 * New feed data is added to the sw_feeds table and
	 * the new insert ID is returned
	 *
	 * @param array $to_insert
	 *
	 * @return false|int
	 *
	 * @since 2.0
	 */
	public static function feeds_update( $to_update, $where_data ) {
		global $wpdb;
		$feeds_table_name = $wpdb->prefix . self::$feeds_table;

		$data = array();
		$where = array();
		$format = array();
		foreach ( $to_update as $single_update ) {
			if ( $single_update['key'] ) {
				$data[ $single_update['key'] ] = $single_update['value'];
				$format[] = '%s';
			}
		}

		if ( isset( $where_data['id'] ) ) {
			$where['id'] = $where_data['id'];
			$where_format = array( '%d' );
		} elseif ( isset( $where_data['feed_name'] ) ) {
			$where['feed_name'] = $where_data['feed_name'];
			$where_format = array( '%s' );
		} else {
			return false;
		}

		$data['last_modified'] = date( 'Y-m-d H:i:s' );
		$format[] = '%s';

		$data['author'] = get_current_user_id();
		$format[] = '%d';

		$affected = $wpdb->update( $feeds_table_name, $data, $where, $format, $where_format );
		return $affected;
	}

	/**
	 * Query the feeds
	 * 
	 * @since 2.0
	 */
	public static function query_feeds() {
		global $wpdb;
		$feeds_table_name = $wpdb->prefix . self::$feeds_table;

		$sql = "SELECT id, feed_name, feeds, settings FROM {$feeds_table_name}";
		return $wpdb->get_results( $sql, ARRAY_A );
	}

    /**
     * Query the sw_feeds table
     *
     * @param array $args
     *
     * @return array|bool
     *
     * @since 2.0
     */
    public static function feeds_query( $args = array() ) {
        global $wpdb;
		$feeds_table_name = $wpdb->prefix . self::$feeds_table;

        $page = 0;
        if (isset($args['page'])) {
            $page = (int) $args['page'] - 1;
            unset($args['page']);
        }
        $offset = \max(0, $page * self::RESULTS_PER_PAGE);
        if (isset($args['id'])) {
            $sql = $wpdb->prepare("\n\t\t\tSELECT * FROM {$feeds_table_name}\n\t\t\tWHERE id = %d;\n\t\t ", $args['id']);
        } else {
            $sql = $wpdb->prepare("\n\t\t\tSELECT * FROM {$feeds_table_name}\n\t\t\tLIMIT %d\n\t\t\tOFFSET %d;", self::RESULTS_PER_PAGE, $offset);
        }
        return $wpdb->get_results($sql, ARRAY_A);
    }

	/**
	 * Delete the feed in the database
	 * 
	 * @since 2.0
	 */
	public static function delete_feed( $feed_id ) {
		global $wpdb;
		$feeds_table_name = $wpdb->prefix . 'sw_feeds';

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $feeds_table_name WHERE id = %d; ", $feed_id
			)
		);
	}

	/**
	 * Bulk delete the feeds in the database
	 * 
	 * @since 2.0
	 */
	public static function bulk_delete_feeds( $feeds ) {
		global $wpdb;
		$feeds_table_name = $wpdb->prefix . self::$feeds_table;

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $feeds_table_name WHERE id IN ($feeds);"
			)
		);
	}

	/**
	 * Duplicate the feed in the database
	 * 
	 * @since 2.0
	 */
	public static function duplicate_feed( $feed_id ) {
		global $wpdb;
		$feeds_table_name = $wpdb->prefix . self::$feeds_table;

		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO $feeds_table_name (feed_name, feeds, settings, author, status)
				SELECT CONCAT(feed_name, ' (copy)'), feeds, settings, author, status
				FROM $feeds_table_name
				WHERE id = %d; ", $feed_id
			)
		);
	}

	/**
	 * Remove source
	 * 
	 * @since 2.0
	 */
	public static function remove_source( $feed_id, $feed_plugin ) {
		global $wpdb;
		$feeds_table_name = $wpdb->prefix . self::$feeds_table;

		// get the current plugins list for the feed
		$args = array( 'id' => $feed_id );
		$feed = self::feeds_query( $args );
		$db_feed_plugin = (array) \json_decode($feed[0]['feeds']);
		// remove the plugin from the feed
		unset($db_feed_plugin[$feed_plugin]);

		$to_update = array();
		$to_update[] = array(
			'key' => 'feeds',
			'value' => json_encode( $db_feed_plugin )
		);

		$success = Database::feeds_update( $to_update, $args );

		return $success;
	}

	/**
	 * Remove source
	 * 
	 * @since 2.0
	 */
	public static function update_source( $feed_id, $wall_plugin ) {
		global $wpdb;
		$feeds_table_name = $wpdb->prefix . self::$feeds_table;

		// get the current plugins list for the feed
		$args = array( 'id' => $feed_id );
		$feed = self::feeds_query( $args );
		$db_feed_plugin = (array) \json_decode($feed[0]['feeds']);
		// update the plugin source to the feed
		$db_feed_plugin[$wall_plugin['wallPlugin']] = (object) array(
			'id' => $wall_plugin['id'],
			'feedName' => $wall_plugin['feedName']
		);

		$to_update = array();
		$to_update[] = array(
			'key' => 'feeds',
			'value' => json_encode( $db_feed_plugin )
		);

		$success = Database::feeds_update( $to_update, $args );

		return $success;
	}

	public static function check_legacy_feed() {
		global $wpdb;
		$feeds_table_name = $wpdb->prefix . 'options';

		$sql = $wpdb->prepare("\n\t\t\tSELECT * FROM {$feeds_table_name}\n\t\t\tWHERE `option_name` LIKE ('%\_transient\_sbsw\_%');\n\t\t ");
        return $wpdb->get_results($sql, ARRAY_A);
	}
}