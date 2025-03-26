<?php
/**
* Class Request
*
* Performs a request to the Smash Balloon Twitter API.
*
* @since 2.1
*/
namespace TwitterFeed\SmashTwitter;

use TwitterFeed\CtfFeedPro;
use TwitterFeed\Pro\CTF_Feed_Pro;
use TwitterFeed\Builder\CTF_Feed_Builder;
use TwitterFeed\SB_Twitter_Cron_Updater_Pro;

class CronUpdaterManager
{
	private $max_batch;
	private $max_requests;

	private $api_call_log;

	private $api_log_obj;

	private $options;
	private $request_counter;

	public function __construct() {
		$this->max_batch = 5;
		$this->max_requests = 12;


		$this->options = ctf_get_database_settings();

		$ctf_statuses_option = get_option( 'ctf_statuses', array() );
		$this->request_counter = 0;
		if ( ! empty( $ctf_statuses_option['smash_twitter_cron']['request_counter'] ) ) {
			$this->request_counter = $ctf_statuses_option['smash_twitter_cron']['request_counter'];
		}

		$this->api_log_obj = new APILog();

		$this->api_call_log = $this->api_log_obj->get();


	}

	public function hooks() {
		add_action('ctf_smash_twitter_feed_update', [$this, 'maybe_do_updates']);
		add_action('ctf_smash_twitter_additional_batch', [$this, 'init_additional_batch']);
	}

	public function calculate_frequency() {
		$defaults = array(
			'ctf_cache_cron_interval'           => '12hours',
			'ctf_cache_cron_time'               => '1',
			'ctf_cache_cron_am_pm'              => 'am',
		);
		$ctf_cache_cron_interval = isset( $this->options['ctf_cache_cron_interval'] ) ? $this->options['ctf_cache_cron_interval'] : $defaults['ctf_cache_cron_interval'];
		$ctf_cache_cron_time     = isset( $this->options['ctf_cache_cron_time'] ) ? $this->options['ctf_cache_cron_time'] : $defaults['ctf_cache_cron_time'];
		$ctf_cache_cron_am_pm    = isset( $this->options['ctf_cache_cron_am_pm'] ) ? $this->options['ctf_cache_cron_am_pm'] : $defaults['ctf_cache_cron_am_pm'];

		if ( $ctf_cache_cron_interval === '12hours' ) {
			return .5 * DAY_IN_SECONDS;
		} elseif ( $ctf_cache_cron_interval === '24hours' ) {
			return DAY_IN_SECONDS;
		} else {
			// daily update limit is still at 12 so min interval calculates to every 2 hours.
			return 2 * HOUR_IN_SECONDS;
		}

	}

	public function can_update() {
		return true;
	}

	public function update_api_call_log( $type, $term ) {
		$this->api_log_obj->update( $type, $term, 'background' );
	}

	public function get_api_call_log() {
		return $this->api_call_log;
	}

	public function init_additional_batch() {
		$this->do_updates( true );
	}

	public function should_do_updates() {
		if ( ! $this->is_past_first_allowed_update() ) {
			return false;
		}

		$time_with_minute_buffer = time() + 60;

		if ( $this->get_last_update_process_time() < ($time_with_minute_buffer - $this->calculate_frequency()) ) {
			if ( $this->request_counter < $this->max_requests ) {
				return true;
			}
		}

		return false;
	}

	public function get_last_update_process_time() {
		$ctf_statuses_option = get_option( 'ctf_statuses', array() );
		if ( empty( $ctf_statuses_option['smash_twitter_cron']['last_update_process_time'] ) ) {
			return 0;
		}
		return $ctf_statuses_option['smash_twitter_cron']['last_update_process_time'];
	}

	public function update_last_update_process( $time ) {
		$ctf_statuses_option = get_option( 'ctf_statuses', array() );
		$ctf_statuses_option['smash_twitter_cron']['last_update_process_time'] = $time;

		update_option( 'ctf_statuses', $ctf_statuses_option );
	}

	public function reset_request_counter() {
		$ctf_statuses_option = get_option( 'ctf_statuses', array() );
		$ctf_statuses_option['smash_twitter_cron']['request_counter'] = 0;
		$ctf_statuses_option['smash_twitter_cron']['request_counter_reset_time'] = time();

		$this->request_counter = 0;
		update_option( 'ctf_statuses', $ctf_statuses_option );
	}

	public function maybe_reset_request_counter() {
		$ctf_statuses_option = get_option( 'ctf_statuses', array() );
		$reset_time = ! empty( $ctf_statuses_option['smash_twitter_cron']['request_counter_reset_time'] ) ? $ctf_statuses_option['smash_twitter_cron']['request_counter_reset_time'] : 0;
		if ( $reset_time < time() - DAY_IN_SECONDS ) {
			$this->reset_request_counter();
			return true;
		}

		return false;
	}

	public function get_request_counter() {
		return $this->request_counter;
	}

	public function add_to_request_counter( $to_add ) {
		$ctf_statuses_option = get_option( 'ctf_statuses', array() );
		$request_counter = $this->request_counter + $to_add;

		$ctf_statuses_option['smash_twitter_cron']['request_counter'] = $request_counter;

		update_option( 'ctf_statuses', $ctf_statuses_option );
	}

	private function is_past_first_allowed_update() {
		return true;
		$ctf_statuses_option = get_option( 'ctf_statuses', array() );
		if ( empty( $ctf_statuses_option['first_cron_update'] ) ) {
			return true;
		}
		return time() > $ctf_statuses_option['first_cron_update'];
	}

	public function do_updates( $is_additional_batch = false ) {
		$can_auth = $this->maybe_setup_auth();
		if ( ! $can_auth ) {
			return;
		}

		$batch_feeds = $this->get_next_batch_of_updatable_feeds( $is_additional_batch );

		$num_found = count($batch_feeds);

		if (! $num_found) {
			return;
		}

		if ( $num_found > $this->max_batch ) {
			wp_schedule_single_event( time() + 120, 'ctf_smash_twitter_additional_batch' );
		}
		$return = array();
		$requests_made = $this->request_counter;
		$source_data_to_update = array();
		foreach ( $batch_feeds as $batch_feed ) {
			if ( $this->max_requests <= $requests_made ) {
				continue;
			}
			$updatable_feed_id = ! empty( $batch_feed['feed_id'] ) ? $batch_feed['feed_id'] : 'legacy';

			$sources_to_update = 1;
			if ( ! empty( $batch_feed['settings'] ) ) {
				$settings = json_decode( $batch_feed['settings'], true );

				$sources_to_update = $this->calc_sources_to_update( $settings );
			}
			// get feed types and terms
			$feed_types_and_terms = $this->get_feed_types_and_terms($updatable_feed_id);
			// at the end of this $batch_feeds loop, $source_data_to_update will have list of all unique types and terms
			$source_data_to_update = $this->update_sources_data($source_data_to_update, $feed_types_and_terms, $updatable_feed_id);

			$this->add_to_request_counter( $sources_to_update );


			$this->update_cache_last_updated( $updatable_feed_id );

		}

		// update posts cache
		$this->update_posts_cache($source_data_to_update);

		// Set cache for the feeds without making new api request
		foreach ($batch_feeds as $batch_feed) {
			$updatable_feed_id = ! empty($batch_feed['feed_id']) ? $batch_feed['feed_id'] : 'legacy';
			$make_api_request = false;
			$return = SB_Twitter_Cron_Updater_Pro::do_single_feed_cron_update($updatable_feed_id, $make_api_request);
		}

		if ( ! empty( $return['data'] ) ) {
			foreach ( $return['data'] as $item ) {
				if ( ! empty( $item ) ) {
					foreach ( $item as $feed_type_and_terms ) {
						$this->update_api_call_log( $feed_type_and_terms[0], $feed_type_and_terms[1]);
					}
				}
			}
		}

		// work around object caches
		wp_cache_flush();
	}

	/**
	 * Get feed types and terms
	 * @since 2.4
	 *
	 * @param int $feed_id
	 *
	 * @return array $feed_types_and_terms
	 */
	public function get_feed_types_and_terms($feed_id)
	{
		// init feed
		$feed_ctf = CtfFeedPro::init(['feed' => $feed_id]);
		$feed_types_and_terms = $feed_ctf->feed_options['feed_types_and_terms'];

		return $feed_types_and_terms;
	}

	public function update_sources_data($current_unique_sources, $feed_types_and_terms, $feed_id)
	{
		foreach ($feed_types_and_terms as $feed_type_and_term) {
			$type_and_term_exists = array_filter($current_unique_sources, function ($source) use ($feed_type_and_term) {
				return $source['type'] === $feed_type_and_term[0] && $source['term'] === $feed_type_and_term[1];
			});

			if (!$type_and_term_exists) {
				$current_unique_sources[] = array(
					'feed_id' => $feed_id,
					'type' => $feed_type_and_term[0],
					'term' => $feed_type_and_term[1],
				);
			}
		}

		return $current_unique_sources;
	}

	public function update_posts_cache($source_data_to_update)
	{
		foreach ($source_data_to_update as $source) {
			$endpoint = $source['type'];
			$term = $source['term'];
			$feed_id = $source['feed_id'];
			// get remote posts
			$remote_posts = $this->get_remote_posts($endpoint, $term, $doing_cron_update = false);
			if (isset($remote_posts[0]['id_str'])) {
				$remote_posts = CTF_Feed_Pro::reduceTweetSetData($remote_posts);
				$this->cache_single_posts_from_set($remote_posts, $endpoint, $term);
				// Update last_api_updated column
				CTF_Feed_Pro::update_last_api_updated_data($feed_id);

				// Updated remaining API data count and next reset time
				CTF_Feed_Pro::update_api_statistics();
			}
		}
	}

	public function calc_sources_to_update( $settings ) {
		$sources_to_update = 1;
		if ( ! empty( $settings['type'] ) ) {
			if ( $settings['type'] === 'usertimeline' ) {
				if ( ! empty( $settings['usertimeline_text'] ) ) {
					if ( is_array( $settings['usertimeline_text'] ) ) {
						$sources_to_update = count( $settings['usertimeline_text'] );
					} elseif ( is_string( $settings['usertimeline_text'] ) ) {
						$sources_array = explode( ',',  $settings['usertimeline_text'] );
						$sources_to_update = count( $sources_array );
					}
				}
			} elseif ( $settings['type'] === 'search' ) {
				if ( ! empty( $settings['search_text'] ) ) {
					if ( is_array( $settings['search_text'] ) ) {
						$sources_to_update = count( $settings['search_text'] );
					} elseif ( is_string( $settings['search_text'] ) ) {
						$sources_array = explode( ',',  $settings['search_text'] );
						$sources_to_update = count( $sources_array );
					}
				}
			} elseif ( $settings['type'] === 'hashtag' ) {
				if ( ! empty( $settings['hashtag_text'] ) ) {
					if ( is_array( $settings['hashtag_text'] ) ) {
						$sources_to_update = count( $settings['hashtag_text'] );
					} elseif ( is_string( $settings['hashtag_text'] ) ) {
						$sources_array = explode( ',',  $settings['hashtag_text'] );
						$sources_to_update = count( $sources_array );
					}
				}
			}
		}
		return $sources_to_update;
	}

	public function maybe_setup_auth() {
		$ctf_options = get_option( 'ctf_options', array() );

		$ctf_license = get_option( 'ctf_license_key', '' );

		if ( empty( $ctf_license ) ) {
			return false;
		}

		if ( empty( $ctf_options['site_access_token'] ) ) {
			$auth_routine = new AuthRoutine();
			$result_token = $auth_routine->run_register( $ctf_license );
			$results = $auth_routine->run_license_activation( $result_token );
			if ( ! empty( $results['data']['api_data'] ) ) {
				update_option( 'ctf_license_data', $results['data']['api_data'] );
				return true;
			} else {
				return false;
			}
		}

		return true;
	}

	public function fetch_all_feeds() {
		$feeds = CTF_Feed_Builder::get_feed_list();

		$builder = new CTF_Feed_Builder();
		$legacy_feeds = $builder->get_legacy_feed_list();

		return array_merge($feeds, $legacy_feeds);
	}

	public function get_next_batch_of_updatable_feeds( $is_additional_batch )
	{
		/*$feeds = get_option('ctf_smash_twitter_feeds_to_update', []);

		if (!is_array($feeds)) {
			return [];
		}

		if (!count($feeds)) {
			return [];
		}

		$updatable_feeds = array_slice($feeds, 0, $this->max_batch);
*/
		return $this->get_updatable_feeds( $is_additional_batch );
	}

	public function get_updatable_feeds( $is_additional_batch ) {
		global $wpdb;
		$feeds_table_name = $wpdb->prefix . 'ctf_feeds';
		$cache_table_name = $wpdb->prefix . 'ctf_feed_caches';

		if ( $is_additional_batch ) {
			$sql = $wpdb->prepare( "
			SELECT * FROM $cache_table_name as c
			LEFT JOIN $feeds_table_name as f ON c.feed_id = f.id
			WHERE c.cron_update = 'yes'
			AND c.last_updated < %s
			GROUP BY c.feed_id
			ORDER BY c.last_updated ASC
			LIMIT %d;
		 ", date('Y-m-d H:i:s', $this->get_last_update_process_time() ), $this->max_batch + 1 );
		} else {
			$sql = $wpdb->prepare( "
			SELECT * FROM $cache_table_name as c
			LEFT JOIN $feeds_table_name as f ON c.feed_id = f.id
			WHERE c.cron_update = 'yes'
			GROUP BY c.feed_id
			ORDER BY c.last_updated ASC
			LIMIT %d;
		 ", $this->max_batch + 1 );
		}



		return $wpdb->get_results( $sql, ARRAY_A );
	}

	public function update_cache_last_updated( $feed_id ) {
		global $wpdb;
		$cache_table_name = $wpdb->prefix . 'ctf_feed_caches';

		return $wpdb->query( $wpdb->prepare(
			"
			UPDATE $cache_table_name
			SET last_updated = %s
			WHERE feed_id = %s",
			date( 'Y-m-d H:i:s'),
			$feed_id
		) );
	}

	public static function schedule_cron_job() {
		if ( ! wp_next_scheduled( 'ctf_smash_twitter_feed_update' ) ) {
			wp_schedule_event( time(), 'hourly', 'ctf_smash_twitter_feed_update' );
		}
	}

	public function get_remote_posts($endpoint, $term, $doing_cron_update = false)
	{
		$ctf_options = get_option('ctf_options', array());
		$site_access_token = ! empty($ctf_options['site_access_token']) ? $ctf_options['site_access_token'] : false;

		if (empty($site_access_token)) {
			$auth_routine = new AuthRoutine();
			$site_access_token = $auth_routine->run_register();
			$results = $auth_routine->run_license_activation($site_access_token);
			if (!empty($results['data']['api_data'])) {
				update_option('ctf_license_data', $results['data']['api_data']);
			}
		}

		$request = new Request($endpoint, $term, array(), $site_access_token);
		$response = $request->fetch();

		// Prevent showing fatal error if the site access token is invalid.
		if (is_wp_error($response)) {
			return [];
		}
		if (!empty($response[0]['id_str'])) {
			$tweet_modifier = new TweetSetModifier();
			$tweet_modifier->set_tweet_set($response);
			$tweet_modifier->hydrate_tweet_set();
			$response = $tweet_modifier->get_hydrated_tweet_set();
		}

		return $response;
	}

	public function cache_single_posts_from_set($posts, $endpoint, $term)
	{
		foreach ($posts as $single_tweet) {
			$single_post_cache = new SinglePostCache($single_tweet, $endpoint, $term);

			if (!$single_post_cache->db_record_exists()) {
				$single_post_cache->store();
			} else {
				if (!$single_post_cache->db_record_exists_for_endpoint_and_term()) {
					$single_post_cache->update_single(true);
				} else {
					$single_post_cache->update_single(false);
				}
			}
		}
	}

	/**
	 * Maybe do updates if enough time has passed since the plugin was updated.
	 *
	 * @return boolean
	 */
	public function maybe_do_updates() {
		CTF_Feed_Pro::should_update_stats();
		$this->maybe_reset_request_counter();

		if ( $this->should_do_updates() ) {
			$this->update_last_update_process( time() );

			$this->do_updates();
			return true;
		}
		return false;
	}
}
