<?php 

namespace SB\SocialWall\Admin;

use SB\SocialWall\Database;
use SB\SocialWall\Utility\Helpers;

class Feed_Saver {

	/**
	 * @var int
	 *
	 * @since 2.0
	 */
	private $insert_id;

	/**
	 * @var array
	 *
	 * @since 2.0
	 */
	private $data;

	/**
	 * @var array
	 *
	 * @since 2.0
	 */
	private $sanitized_and_sorted_data;

	/**
	 * @var array
	 *
	 * @since 2.0
	 */
	private $feed_db_data;


	/**
	 * @var string
	 *
	 * @since 2.0
	 */
	private $feed_name;

	/**
	 * @var bool
	 *
	 * @since 2.0
	 */
	private $is_legacy;

	/**
	 * CTF_Feed_Saver constructor.
	 *
	 * @param int $insert_id
	 *
	 * @since 2.0
	 */
	public function __construct( $insert_id ) {
		if ( $insert_id === 'legacy' ) {
			$this->is_legacy = true;
			$this->insert_id = 0;
		} else {
			$this->is_legacy = false;
			$this->insert_id = $insert_id;
		}
	}


    /**
     *
     * @return array
     *
     * @since 2.0
     */
    public function get_feed_db_data()
    {
		if ( $this->is_legacy ) {
			return array(
				'feed_name' => __( 'Legacy Feed', 'social-wall' ),
				'feed_title' => __( 'Legacy Feed', 'social-wall' ),
			);
		}

        return $this->feed_db_data;
    }

	/**
	 * @param array $data
	 *
	 * @since 2.0
	 */
	public function get_data() {
		return $this->data;
	}
	
	/**
	 * @param array $data
	 *
	 * @since 2.0
	 */
	public function set_data( $data ) {
		$this->data = $data;
	}
	
	/**
	 * @param string $feed_name
	 *
	 * @since 2.0
	 */
	public function set_feed_name( $feed_name ) {
		$this->feed_name = $feed_name;
	}

	/**
	 * Get the list of plugins for the social feed integrated
	 * 
	 * @since 2.0
	 */
	public function get_feed_plugins() {
		// get feed plugins for legacy feed
		if ( $this->is_legacy ) {
			return Helpers::get_legacy_feed_plugins();
		}

		$args = array(
			'id' => $this->insert_id,
		);
		$settings_db_data = Database::feeds_query( $args );
		if ( isset( $settings_db_data[0]['feeds'] ) ) {
			return (array) json_decode($settings_db_data[0]['feeds']);
		}
	}

	/**
	 * Retrieves and organizes feed setting data for easy use in
	 * the builder
	 *
	 * @return array|bool
	 *
	 * @since 2.0
	 */
	public function get_feed_settings() {
		if ( $this->is_legacy ) {
			$settings = get_option( 'sbsw_settings', [] );
			$return = $settings;

			$this->feed_db_data = array(
				'id' => 'legacy',
				'feed_name' => __( 'Legacy Feed', 'social-wall' ),
				'feed_title' => __( 'Legacy Feed', 'social-wall' ),
				'status' => 'publish',
				'last_modified' => date( 'Y-m-d H:i:s' ),
			);
		} else if ( empty( $this->insert_id ) ) {
			return false;
		} else {
			$args = array(
				'id' => $this->insert_id,
			);
			$settings_db_data = Database::feeds_query( $args );
			if ( false === $settings_db_data || sizeof($settings_db_data) == 0) {
				return false;
			}
			$this->feed_db_data = array(
				'id' => $settings_db_data[0]['id'],
				'feed_name' => $settings_db_data[0]['feed_name'],
				'status' => $settings_db_data[0]['status'],
				'last_modified' => $settings_db_data[0]['last_modified'],
			);

			$return = json_decode( stripslashes( $settings_db_data[0]['settings'] ), true );
			$return['feed_name'] = $settings_db_data[0]['feed_name'];
		}
		$return = wp_parse_args( $return, sbsw_settings_defaults() );
		return $return;
	}

	public function update() {
		$args = array(
			'id' => $this->insert_id
		);
		$settings_array = $this->get_data();

		if ( $this->is_legacy ) {
			return update_option( 'sbsw_settings', $settings_array, false );
		}

		$to_update = array();
		$to_update[] = array(
			'key' => 'settings',
			'value' => json_encode( $settings_array )
		);
		$to_update[] = array(
			'key' => 'feed_name',
			'value' => sanitize_text_field($this->feed_name)
		);

		$success = Database::feeds_update( $to_update, $args );

		return $success;
	}
}