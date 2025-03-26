<?php
/**
 * Twitter Feed Cache
 *
 * A bridge to use the new feed caches table
 *
 * @since 2.0
 */
namespace TwitterFeed;

class CtfCache {

	/**
	 * @var string
	 */
	public $feed_id;

	/**
	 * @var int
	 */
	private $page;

	/**
	 * @var int
	 */
	private $cache_time;

	public function __construct( $feed_id, $cache_time, $page ) {
		$this->feed_id = $feed_id;

		$this->page = $page;

		$this->cache_time = $cache_time;

		if ( is_admin() ) {
			$this->feed_id .= $this->maybe_customizer_suffix();
		}
	}

	/**
	 * Mimic WP get_transient
	 *
	 * @param string $transient_name
	 *
	 * @return false|string
	 */
	public function get_transient( $transient_name ) {
		$results = $this->query_ctf_feed_caches( $transient_name );

		if ( empty( $results ) ) {
			return false;
		}

		if ( empty( $results[0]['cache_value'] ) ) {
			return false;
		}

		if ( strtotime( $results[0]['last_updated'] ) < time() - $this->cache_time ) {
			return false;
		}

		return $results[0]['cache_value'];
	}

	/**
	 * Mimic WP get_option for persistent caches
	 *
	 * @param string $transient_name
	 *
	 * @return false|string
	 */
	public function get_persistent( $transient_name ) {
		$results = $this->query_ctf_feed_caches( $transient_name );

		if ( empty( $results ) ) {
			$results = get_option( $transient_name, false );

			if ( ! empty( $results ) ) {
				return $results;
			}
		}

		if ( empty( $results ) ) {
			return false;
		}

		if ( empty( $results[0]['cache_value'] ) ) {
			return false;
		}

		return $results[0]['cache_value'];
	}

	/**
	 * Mimic WP set_transient
	 *
	 * @param $transient_name
	 * @param $value
	 *
	 * @return bool|int
	 */
	public function set_transient( $transient_name, $value, $backup = false, $cron_update = true ) {
		$this->simultaneous_cache_update($transient_name, $value, $backup, $cron_update);
		return $this->update_or_insert( $transient_name, $value, $backup, $cron_update );
	}

	/**
	 * Mimic WP update_option for persistent
	 *
	 * @param $transient_name
	 * @param $value
	 *
	 * @return bool|int
	 */
	public function set_persistent( $transient_name, $value ) {
		return $this->update_or_insert( $transient_name, $value );
	}

	/**
	 * Update or insert cache data
	 *
	 * @param string $transient_name
	 * @param string $cache_value
	 * @param bool $include_backup
	 * @param bool $cron_update
	 *
	 * @return bool|int|\mysqli_result|resource|null
	 */
	public function update_or_insert($transient_name, $cache_value, $include_backup = true, $cron_update = true, $custom_feed_id = null)
	{
		$feed_to_update = $custom_feed_id !== null ? $custom_feed_id : $this->feed_id;

		if (strpos($feed_to_update, '_CUSTOMIZER') !== false) {
			$cron_update = false;
		}

		if ( ! empty( $this->page ) ) {
			$cron_update = false;
		}

		if ($feed_to_update === 'legacy') {
			$cron_update = false;
		}

		global $wpdb;
		$cache_table_name = $wpdb->prefix . 'ctf_feed_caches';

		$sql = $wpdb->prepare(
			"
			SELECT * FROM $cache_table_name
			WHERE feed_id = %s
			AND cache_key = %s",
			$feed_to_update,
			$transient_name
		);

		$existing = $wpdb->get_results( $sql, ARRAY_A );
		$data     = array();
		$where    = array();
		$format   = array();

		$data['cache_value'] = $cache_value;
		$format[]            = '%s';

		$data['last_updated'] = date( 'Y-m-d H:i:s' );
		$format[]             = '%s';

		if ( ! empty( $existing[0] ) ) {
			$where['feed_id'] = $feed_to_update;
			$where_format[]   = '%s';

			$where['cache_key'] = $transient_name;
			$where_format[]     = '%s';

			$affected = $wpdb->update( $cache_table_name, $data, $where, $format, $where_format );
		} else {
			$data['cache_key'] = $transient_name;
			$format[]          = '%s';

			$data['cron_update'] = $cron_update === true ? 'yes' : '';
			$format[]            = '%s';

			$data['feed_id'] = $feed_to_update;
			$format[]        = '%s';

			$affected = $wpdb->insert( $cache_table_name, $data, $format );
		}

		return $affected;
	}

	/**
	 * @param $transient_name
	 *
	 * @return array|false
	 */
	private function query_ctf_feed_caches( $transient_name ) {
		$feed_cache = wp_cache_get( $transient_name );
		if ( false === $feed_cache || ! is_array( $feed_cache ) ) {
			global $wpdb;
			$cache_table_name = $wpdb->prefix . 'ctf_feed_caches';

			$sql = $wpdb->prepare(
				"
				SELECT * FROM $cache_table_name
				WHERE cache_key = %s
				ORDER BY last_updated DESC",
				$transient_name
			);

			$feed_cache = $wpdb->get_results( $sql, ARRAY_A );

			wp_cache_set( $transient_name, $feed_cache );
		}

		return $feed_cache;
	}

	/**
	 * @return string
	 */
	private function maybe_customizer_suffix() {
		$additional_suffix = '';
		$in_customizer     = ! empty( $_POST['previewSettings'] ) || ( isset( $_GET['page'] ) && $_GET['page'] === 'ctf-feed-builder' );
		if ( $in_customizer ) {
			$additional_suffix .= '_CUSTOMIZER';
		}

		return $additional_suffix;
	}

	/**
	 * Update or insert
	 * In case of default cache update
	 * We will update the Customizer cache too & vice versa
	 * Reduce the number of API calls
	 *
	 * @param string $transient_name
	 * @param string $cache_value
	 * @param bool $include_backup
	 * @param bool $cron_update
	 *
	 * @return void
	 */
	private function simultaneous_cache_update($transient_name, $value, $backup, $cron_update)
	{
		$feed_to_update = $this->feed_id;
		//Check the Type of Transient, we create the other
		if (strpos( $this->feed_id, '_CUSTOMIZER' ) !== false) {
			$feed_to_update = str_replace('_CUSTOMIZER', '', $this->feed_id);
		} else {
			$feed_to_update .= '_CUSTOMIZER';
		}
		$this->update_or_insert($transient_name, $value, $backup, $cron_update, $feed_to_update);
	}
}
