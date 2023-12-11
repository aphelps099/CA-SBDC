<?php
/**
 * @package gp-google-sheets
 * @copyright Copyright (c) 2022, Gravity Wiz, LLC
 * @author Gravity Wiz <support@gravitywiz.com>
 * @license GPLv2
 * @link https://gravitywiz.com/documentation/gravity-forms-google-sheets/
 */
defined( 'ABSPATH' ) or exit;

use \GP_Google_Sheets\Dependencies\Google\Client as Google_Client;
use \GP_Google_Sheets\Dependencies\Google\Service\Sheets as Google_Service_Sheets;
use \GP_Google_Sheets\Dependencies\Google\Service\Drive as Google_Service_Drive;

/**
 * Implements the OAuth2 handshake with Google to obtain permission to write and
 * write to a Google Sheet.
 */
class GP_Google_Sheets_Authenticator {
	/**
	 * Gets the API client and construct the service object.
	 *
	 * @param array $feed A Gravity Forms feed instance
	 * @return Google_Service_Sheets|\WP_Error
	 */
	public static function create_service( $feed, $token = null ) {
		try {
			$google_client = self::get_google_client( $feed, $token );
		} catch ( \Exception $e ) {
			return new \WP_Error( $e->getCode(), $e->getMessage() );
		}

		return new Google_Service_Sheets( $google_client );
	}

	/**
	 * Gets the spreadsheet resource of an existing service object.
	 *
	 * This method is mostly in place so it can be properly typed.
	 *
	 * @param Google_Service_Sheets $service Existing service object
	 *
	 * @return \GP_Google_Sheets\Dependencies\Google\Service\Sheets\Resource\Spreadsheets
	 */
	public static function get_spreadsheets_resource( $service ) {
		return $service->spreadsheets;
	}

	/**
	 * Gets the spreadsheet values resource of an existing service object.
	 *
	 * This method is mostly in place so it can be properly typed.
	 *
	 * @param Google_Service_Sheets $service Existing service object
	 *
	 * @return \GP_Google_Sheets\Dependencies\Google\Service\Sheets\Resource\SpreadsheetsValues
	 */
	public static function get_spreadsheets_values_resource( $service ) {
		return $service->spreadsheets_values;
	}

	/**
	 * Gets the spreadsheet developer metadata resource of an existing service object.
	 *
	 * This method is mostly in place so it can be properly typed.
	 *
	 * @param Google_Service_Sheets $service Existing service object
	 *
	 * @return \GP_Google_Sheets\Dependencies\Google\Service\Sheets\Resource\SpreadsheetsDeveloperMetadata
	 */
	public static function get_spreadsheets_developer_metadata_resource( $service ) {
		return $service->spreadsheets_developerMetadata;
	}

	/**
	 * Gets the API client and construct the service object for Google Drive.
	 *
	 * @param array $token An access token to use to create the drive service.
	 * @return Google_Service_Drive|WP_Error
	 */
	public static function create_drive_service( $token ) {
		try {
			$google_client = self::get_google_client( array(), $token );
		} catch ( \Exception $e ) {
			return new \WP_Error( $e->getCode(), $e->getMessage() );
		}

		return new Google_Service_Drive( $google_client );
	}

	/**
	 * get_google_client
	 *
	 * Returns an authorized API client.
	 *
	 * @param  array $feed A Gravity Forms feed instance
	 * @param  array $token An optional token to use instead of the one in the feed. This is primarily used during feed creation when a token hasn't yet been associated with the feed.
	 * @return Google_Client the authorized client object
	 *
	 * @throws InvalidArgumentException
	 */
	public static function get_google_client( $feed, $token = null ) {
		$client = new Google_Client();

		// Fallback to global token if an empty feed is provided (e.g. for GPPA Object Type)
		if ( empty( $feed ) && $token === null ) {
			$token = gp_google_sheets()->tokens->get_global_token() ?? GP_Google_Sheets_Legacy_Tokens::get_global_token();
		}

		if ( $token === null ) {
			$token = GP_Google_Sheets_Tokens::get_instance()->get_feed_access_token( $feed );
		}

		if ( $token === null ) {
			$token = GP_Google_Sheets_Legacy_Tokens::get_feed_access_token( $feed );
		}

		if ( $token === null ) {
			return $client;
		}

		if ( isset( $token['access_token'] ) ) {
			$client->setAccessToken( $token['access_token'] );
		}

		if ( rgar( $token, 'gwiz_oauth' ) ) {
			$client_id = GP_Google_Sheets_Tokens::get_google_client_id();
		} else {
			$client_id = GP_Google_Sheets_Legacy_Tokens::get_google_client_id();
		}

		$client->setClientId( $client_id );

		$timeout    = apply_filters( 'gpgs_http_request_timeout', 15, null );
		$httpClient = new \GP_Google_Sheets\Dependencies\GuzzleHttp\Client( array( 'timeout' => $timeout ) );
		$client->setHttpClient( $httpClient );

		return $client;
	}

	public function get_feed_access_token( $feed ) {
		$token = GP_Google_Sheets_Tokens::get_instance()->get_feed_access_token( $feed );

		if ( $token ) {
			return $token;
		}

		$token = GP_Google_Sheets_Legacy_Tokens::get_feed_access_token( $feed );

		if ( ! $token ) {
			return null;
		}

		// if token is a legacy token, just return it.
		if ( rgar( $token, 'gwiz_oauth' ) !== true ) {
			return $token;
		}

		// if token is _not_ legacy, just go ahead an migrate it.
		$tokens         = GP_Google_Sheets_Tokens::get_instance();
		$email          = GP_Google_Sheets_Tokens::get_token_email( $token );
		$spreadsheet_id = GP_Google_Sheets_Writer::spreadsheet_id( $feed );

		// do not overwrite a user's token as it could be the global token and we do not want to overwrite that one.
		if ( ! $tokens->get_token_by_google_email( $email ) ) {
			$tokens->set_email_to_token( $email, $token, false );
		}

		$tokens->set_spreadsheet_id_to_email( $spreadsheet_id, $feed['id'], $email );
	}

	function hooks() {
		if ( ! class_exists( 'GP_Google_Sheets' ) ) {
			return;
		}

		/**
		 * Save the token provided by Google after an authentication happens.
		 * Also warn users if the plugin is enabled and we have a Sheet URL
		 * but no authorization token. New rows cannot be appended without a
		 * token.
		 */

		/**
		 * Handle a Google OAuth callback for the global plugin settings.
		 */
		add_action( 'wp_ajax_gpgs_plugin_settings_oauth_callback', array( $this, 'ajax_plugin_settings_google_oauth_redirect_handler' ) );
		add_action( 'wp_ajax_nopriv_gpgs_plugin_settings_oauth_callback', array( $this, 'ajax_plugin_settings_google_oauth_redirect_handler' ) );

		/**
		 * Handle reconnection requests from the global plugin settings.
		 */
		add_action( 'wp_ajax_gpgs_plugin_settings_reconnect_oauth_callback', array( $this, 'ajax_plugin_settings_reconnect_google_oauth_redirect_handler' ) );
		add_action( 'wp_ajax_nopriv_gpgs_plugin_settings_reconnect_oauth_callback', array( $this, 'ajax_plugin_settings_reconnect_google_oauth_redirect_handler' ) );

		/**
		 * Handle a Google OAuth callback for a feed settings.
		 */
		add_action( 'wp_ajax_gpgs_feed_settings_oauth_callback', array( $this, 'ajax_feed_settings_google_oauth_redirect_handler' ) );
		add_action( 'wp_ajax_nopriv_gpgs_feed_settings_oauth_callback', array( $this, 'ajax_feed_settings_google_oauth_redirect_handler' ) );

		add_action( 'wp_ajax_gpgs_picker_callback', array( $this, 'ajax_google_picker_redirect_handler' ) );
		add_action( 'wp_ajax_nopriv_gpgs_picker_callback', array( $this, 'ajax_google_picker_redirect_handler' ) );

	}

	public function ajax_plugin_settings_google_oauth_redirect_handler() {
		$state   = $this->get_state_from_post();
		$user_id = rgar( $state, 'user_id' );

		$is_timestamp_expired = ! $this->is_authorized_oauth_callback_request( $state );
		if ( $is_timestamp_expired ) {
			$_POST['gwiz_oauth_success'] = '0';
			$_POST['message']            = 'The authorization request has expired. Please refresh the page and try again.';

			return $this->generate_google_oauth_redirect_script( null, $state, true );
		} elseif ( $user_id === 0 ) {
			$_POST['gwiz_oauth_success'] = '0';

			if ( empty( $_POST['message'] ) ) {
				$_POST['message'] = 'You are not authorized to make this request.';
			}
			return $this->generate_google_oauth_redirect_script( null, $state, true );
		}

		$token = $this->get_token_from_post();

		GP_Google_Sheets_Tokens::get_instance()->set_token( $token, $user_id, true );

		$this->generate_google_oauth_redirect_script( $token, $state, true );
	}

	public function ajax_plugin_settings_reconnect_google_oauth_redirect_handler() {
		$state   = $this->get_state_from_post();
		$user_id = rgar( $state, 'user_id' );

		$is_timestamp_expired = ! $this->is_authorized_oauth_callback_request( $state );
		if ( $is_timestamp_expired ) {
			$_POST['gwiz_oauth_success'] = '0';
			$_POST['message']            = 'The authorization request has expired. Please refresh the page and try again.';

			return $this->generate_google_oauth_redirect_script( null, $state, true );
		} elseif ( $user_id === 0 ) {
			$_POST['gwiz_oauth_success'] = '0';

			if ( empty( $_POST['message'] ) ) {
				$_POST['message'] = 'You are not authorized to make this request.';
			}

			return $this->generate_google_oauth_redirect_script( null, $state, true );
		}

		$token = $this->get_token_from_post();

		$tokens = GP_Google_Sheets_Tokens::get_instance();

		$email      = rgars( $tokens->get_user_ids_to_emails(), "{$user_id}/0" );
		$prev_token = rgar( $tokens->get_emails_to_tokens(), $email );

		$tokens->set_token( $token, $user_id, rgar( $prev_token, 'is_global_token', false ) );

		$this->generate_google_oauth_redirect_script( $token, $state, true );
	}

	public function ajax_feed_settings_google_oauth_redirect_handler() {
		$state   = $this->get_state_from_post();
		$user_id = rgar( $state, 'user_id' );

		$is_timestamp_expired = ! $this->is_authorized_oauth_callback_request( $state );
		if ( $is_timestamp_expired ) {
			$_POST['gwiz_oauth_success'] = '0';
			$_POST['message']            = 'The authorization request has expired. Please refresh the page and try again.';

			return $this->generate_google_oauth_redirect_script( null, $state, true );
		} elseif ( $user_id === 0 ) {
			$_POST['gwiz_oauth_success'] = '0';

			if ( empty( $_POST['message'] ) ) {
				$_POST['message'] = 'You are not authorized to make this request.';
			}

			return $this->generate_google_oauth_redirect_script( null, $state, true );
		}

		$token = $this->get_token_from_post();

		GP_Google_Sheets_Tokens::get_instance()->set_token( $token, $user_id );

		$this->generate_google_oauth_redirect_script( $token, $state, false );
	}

	public function is_authorized_oauth_callback_request( $state ) {
		if ( ! $state ) {
			return false;
		}

		$token_timestamp = intval( GFCommon::openssl_decrypt( rgar( $state, 'oauth_validation_token' ) ) );
		$fifteen_minutes = 15 * 60;

		return time() - $token_timestamp < $fifteen_minutes;
	}

	public function get_token_from_post() {
		$token = null;

		if ( rgpost( 'access_token' ) && rgpost( 'gwiz_oauth_success' ) === '1' ) {
			$token = array(
				'access_token'  => rgpost( 'access_token' ),
				'refresh_token' => rgpost( 'refresh_token' ),
				'id_token'      => rgpost( 'id_token' ),
				'expiry_date'   => rgpost( 'expiry_date' ),
				'scope'         => rgpost( 'scope' ),
				'token_type'    => rgpost( 'token_type' ),
				'gwiz_oauth'    => true,
			);

		}

		return $token;
	}

	public function get_state_from_post() {
		$state = null;

		if ( rgpost( 'state' ) ) {
			$state = json_decode( rgpost( 'state' ), true );
		}

		return $state;
	}

	/**
	 * Generate Javascript to save token to localstorage and optionally close the tab
	 *
	 * @param array|null $token The token from the Google OAuth callback
	 * @param array $state The state from the Google OAuth callback
	 * @param boolean $should_close_tab whether or not the tab should be closed after the script is run.
	 *
	 */
	public function generate_google_oauth_redirect_script( $token, $state, $should_close_tab ) {
		?>
			<html>
			<head>
				<title>Google Connected</title>
			</html>
			<body>
			<script>
				var gpgsShouldCloseWindow = <?php echo $should_close_tab ? 'true' : 'false'; ?>;
				window.localStorage.setItem( '<?php echo 'gpgs_google_oauth_data_' . rgar( $state, 'oauth_random_string' ); ?>',
					<?php
						$data = array(
							'success' => rgpost( 'gwiz_oauth_success' ) === '1' ? '1' : '0',
							'message' => rgpost( 'message' ),
							'token'   => $token,
						);

						$json = json_encode( $data );
						echo '"' . addslashes( $json ) . '"';
						?>
				);

				if (gpgsShouldCloseWindow) {
					window.close();
				}
			</script>
			</body>
			</html>
		<?php
		die();
	}

	/**
	 * Generate Javascript to save token to localstorage and close the tab
	 */
	public function ajax_google_picker_redirect_handler() {
		$state = $this->get_state_from_post();
		?>
			<html>
			<head>
				<title>Google Sheet Connected</title>
			</html>
			<body>
			<script>
				window.localStorage.setItem( '<?php echo 'gpgs_google_picker_data_' . rgar( $state, 'oauth_random_string' ); ?>',
					<?php
						$sheet_url = rgpost( 'sheet_url' );
						$data      = array(
							'success'   => $sheet_url ? '1' : '0',
							'sheet_url' => $sheet_url,
							'message'   => rgpost( 'message' ),
						);

						$json = json_encode( $data );
						echo '"' . addslashes( $json ) . '"';
						?>
				);

				window.close();
			</script>
			</body>
			</html>
		<?php
		die();
	}

	public static function random_string( $length = 30 ) {
		$characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen( $characters );
		$randomString     = '';
		for ( $i = 0; $i < $length; $i++ ) {
			/** @phpstan-ignore-next-line */
			$randomString .= $characters[ random_int( 0, $charactersLength - 1 ) ];
		}
		return $randomString;
	}
}
