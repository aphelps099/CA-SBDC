<?php
/**
 * Class Request
 *
 * Performs a request to the Smash Balloon Twitter API.
 *
 * @since 2.1
 */
namespace TwitterFeed\SmashTwitter;

class Request
{
	protected $endpoint;

	protected $term;

	protected $args;

	protected $auth_token;

	protected $license;

	protected $error_code;

	protected $error_message;
	protected $user_rest_id = null;

	public function __construct($endpoint, $term, $args = array(), $auth_token = false )
	{
		$this->endpoint = $endpoint;

		$this->term = $term;

		$this->args = $args;

		$this->auth_token = $auth_token;

		$this->check_user_timeline();

	}

	/**
	 * Check User Timeline ID
	 *
	 * @return void
	 *
	 * @since XX
	 */
	public function check_user_timeline()
	{
		if ( $this->endpoint === 'usertimeline' ) {
			$this->user_rest_id = UserTimelineManager::get_user_id($this->term);
		}
	}



	public function fetch()
	{

		$headers = array(
			'Accept' => 'application/json',
			'Content-Type' => 'application/json'
		);

		if ($this->auth_token) {
			$headers['Authorization'] = 'Bearer ' . $this->auth_token;
		}

		$this->args['headers'] = $headers;

		$endpoint_relative_url = '';
		$method = 'post';
		$this->args['method']  = 'POST';
		if ( empty( $this->args['timeout'] ) ) {
			$this->args['timeout']  = 60;
		}
		if (!$this->should_make_call($this->endpoint)) {
			return [];
		}

		if ( $this->endpoint === 'usertimeline' ) {
			$method = 'get';
			$this->args['method']  = 'GET';
			$endpoint_relative_url = SMASH_TWITTER_TIMELINE_PATH . '?tweet_mode=extended&screen_name=' . urlencode( trim( $this->term ) );
			if($this->user_rest_id) {
				$endpoint_relative_url .= '&user_rest_id=' . urlencode(trim($this->user_rest_id));
			}

		} elseif ( $this->endpoint === 'search' ) {
			$method = 'get';
			$this->args['method']  = 'GET';
			$endpoint_relative_url = SMASH_TWITTER_SEARCH_PATH . '?q=' . urlencode( trim( $this->term ) );
		} elseif ( $this->endpoint === 'lists' ) {
			$method = 'get';
			$this->args['method']  = 'GET';
			$endpoint_relative_url = SMASH_TWITTER_SEARCH_PATH . '?q=list:' . urlencode( trim( $this->term ) );
		} elseif ( $this->endpoint === 'register' ) {
			$endpoint_relative_url = '1.1/auth/register';
		} elseif ( $this->endpoint === 'license' ) {
			$endpoint_relative_url = '1.1/auth/license';
		} elseif ($this->endpoint === 'apistats') {
			$this->args['method']  = 'GET';
			$endpoint_relative_url = '1.1/apistats?site_token=' . $this->term['body']['site_token'];
		}

		$endpoint_url = SMASH_TWITTER_URL . $endpoint_relative_url . SMASH_TWITTER_URL_EXTRA_GET_PARAMS;

		$args = $this->args;

		if ( $method === 'get' ) {
			$return = wp_remote_get( $endpoint_url, $args );
		} else {
			$return = wp_remote_post( $endpoint_url, $args );
		}

		if ( is_wp_error( $return ) ) {
			return $return;
		}

		$response_code = wp_remote_retrieve_response_code( $return );
		if ( $response_code >= 200 && $response_code < 300 ) {
			if ( isset( $return['body'] )) {
				$body = $return['body'];
				$decoded = json_decode( $body, true );

				if ( $decoded ) {
					return $decoded;
				}
			}
		} else {
			if ( isset( $return['body'] )) {
				$this->error_code = $response_code;
				$this->error_message = $return['body'];
			}
		}
		return $return;
	}

	public function get_error() {
		if ( empty( $this->error_code ) ) {
			return array();
		}

		return array(
			'code' => $this->error_code,
			'message' => $this->error_message
		);
	}

	/**
	 * List of endpoints
	 * Prevent making API calls When there is no Credits
	 * Since it will always cause a 429 code error
	 *
	 * @return array
	 */
	public function resticted_endpoints()
	{
		return [
			'usertimeline',
			'search',
			'lists'
		];
	}

	/**
	 * Check Should Make call
	 *
	 * @return boolean
	 */
	public function should_make_call($endpoint)
	{
		$options 	= get_option('ctf_statuses', []);
		$retricterd = $this->resticted_endpoints();

		$count = isset($options['smash_twitter']['apistats']['remaining_api_counts']) ?
	            $options['smash_twitter']['apistats']['remaining_api_counts'] : null;

		if (
			!in_array($endpoint, $retricterd) ||
			($count !== null && $count > 0)
		) {
			return true;
		}

		return false;
	}
}
