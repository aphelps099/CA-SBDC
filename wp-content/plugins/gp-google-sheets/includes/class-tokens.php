<?php
/**
 * @package gp-google-sheets
 * @copyright Copyright (c) 2022, Gravity Wiz, LLC
 * @author Gravity Wiz <support@gravitywiz.com>
 * @license GPLv2
 * @link https://gravitywiz.com/documentation/gravity-forms-google-sheets/
 */

use \GP_Google_Sheets\Dependencies\Google\Client as Google_Client;

class GP_Google_Sheets_Tokens {
	private static $_instance = null;

	/**
	 * A map of spreadsheet_id => feed_id => google_account_email.
	 *
	 * This can be used to look up the email address of the Google account that is connected to a given feed
	 * and then consequently the access token for that account via the $this->_emails_to_tokens map.
	 *
	 * In other words, this can be used to get the access token for a given feed or spreadsheet.
	 */
	private $_spreadsheets_to_emails = null;

	/**
	 * A map of google account emails => token pairs for all users who have connected to Google Sheets.
	 *
	 * Notes:
	 *     * No more than one of these should have is_global_token set to true. This
	 *       property indicates that the token was used to connect the plugin to Google from the GF settings page.
	 */
	private $_emails_to_tokens = null;

	/**
	 * A map of wordpress_user_id => google_account_email arrays.
	 *
	 * The google_account_email arrays should only ever contain one email at the moment. In the future, we might change this to allow WordPress users to connect themselves to multiple Google
	 * Accounts.
	 */
	private $_user_ids_to_emails = null;


	function __construct() {
		$this->_spreadsheets_to_emails = get_option( 'gp_google_sheets_spreadsheets_to_emails', array() );
		$this->_emails_to_tokens       = get_option( 'gp_google_sheets_emails_to_tokens', array() );
		$this->_user_ids_to_emails     = get_option( 'gp_google_sheets_user_ids_to_emails', array() );
	}

	/**
	 * @return GP_Google_Sheets_Tokens
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new GP_Google_Sheets_Tokens();
		}

		return self::$_instance;
	}

	public function get_spreadsheets_to_emails() {
		return $this->_spreadsheets_to_emails;
	}

	public function get_user_ids_to_emails() {
		return $this->_user_ids_to_emails;
	}
	public function get_emails_to_tokens() {
		return $this->_emails_to_tokens;
	}

	public function set_email_to_token( $email, $token, $is_global_token = false ) {
		if ( empty( $email ) ) {
			return;
		}

		if ( $is_global_token ) {
			// TODO handle the case that a token with is_global_token = true already exists.
			$token['is_global_token'] = true;
		}

		$token['gwiz_oauth']               = true;
		$this->_emails_to_tokens[ $email ] = $token;
		update_option( 'gp_google_sheets_emails_to_tokens', $this->_emails_to_tokens );
		return $token;
	}

	public function delete_email_to_token_mapping( $email ) {
		if ( ! rgar( $this->_emails_to_tokens, $email ) ) {
			return;
		}

		unset( $this->_emails_to_tokens[ $email ] );
		update_option( 'gp_google_sheets_emails_to_tokens', $this->_emails_to_tokens );
	}

	public function set_user_id_to_email( $user_id, $email ) {
		// TODO make sure that this will only ever be one for now (since we only support one token per user at the moment).
		if ( empty( $this->_user_ids_to_emails[ $user_id ] ) ) {
			$this->_user_ids_to_emails[ $user_id ] = array();
		}

		if ( ! in_array( $email, $this->_user_ids_to_emails[ $user_id ] ) ) {
			$this->_user_ids_to_emails[ $user_id ][] = $email;
			update_option( 'gp_google_sheets_user_ids_to_emails', $this->_user_ids_to_emails );
		}
	}

	public function set_spreadsheet_id_to_email( $spreadsheet_id, $feed_id, $email ) {
		if ( empty( $this->_spreadsheets_to_emails[ $spreadsheet_id ] ) ) {
			$this->_spreadsheets_to_emails[ $spreadsheet_id ] = array();
		}

		$this->_spreadsheets_to_emails[ $spreadsheet_id ][ $feed_id ] = $email;

		update_option( 'gp_google_sheets_spreadsheets_to_emails', $this->_spreadsheets_to_emails );
	}

	public function delete_feed_mapping( $feed ) {
		$spreadsheet_id = GP_Google_Sheets_Writer::spreadsheet_id( $feed );

		if ( ! rgar( $this->_spreadsheets_to_emails, $spreadsheet_id ) ) {
			return;
		}

		unset( $this->_spreadsheets_to_emails[ $spreadsheet_id ][ $feed['id'] ] );

		// cleanup the map if the given spreadsheet is no longer connected to any feeds.
		if ( empty( $this->_spreadsheets_to_emails[ $spreadsheet_id ] ) ) {
			unset( $this->_spreadsheets_to_emails[ $spreadsheet_id ] );
		}

		update_option( 'gp_google_sheets_spreadsheets_to_emails', $this->_spreadsheets_to_emails );
	}

	public static function refresh_access_token( $token ) {
		$refresh_token_url = GP_Google_Sheets::GWIZ_OAUTH_SERVICE_URL . '/oauth/google/refresh';

		$license_info = gp_google_sheets()->get_gp_license_info();

		$response = wp_remote_post(
			$refresh_token_url,
			array(
				'headers'     => array( 'Content-Type' => 'application/json; charset=utf-8' ),
				'body'        => json_encode( array(
					'refreshToken' => $token['refresh_token'],
					'licenseId'    => $license_info['id'],
				) ),
				'method'      => 'POST',
				'data_format' => 'body',
			),
		);

		gp_google_sheets()->log_debug( __METHOD__ . '(): Access token refresh response code: ' . wp_remote_retrieve_response_code( $response ) );

		if ( is_wp_error( $response ) ) {
			gp_google_sheets()->log_error( __METHOD__ . '(): Failed to refresh token. ' . $response->get_error_message() );
			return null;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! rgar( $body, 'token' ) ) {
			gp_google_sheets()->log_error( __METHOD__ . '(): Failed to refresh token. Token missing from response. ' . json_encode( $body ) );
		} else {
			gp_google_sheets()->log_debug( __METHOD__ . '(): Refreshed access token.' );
		}

		return rgar( $body, 'token' );
	}

	public function maybe_refresh_token( $token ) {
		if ( ! self::should_refresh_access_token( $token ) ) {
			return $token;
		}

		return $this->refresh_and_persist_token( $token );
	}

	public function refresh_and_persist_token( $token ) {
		$is_global_token = rgar( $token, 'is_global_token', false );
		$token           = self::refresh_access_token( $token );

		if ( empty( $token ) ) {
			return null;
		}

		$email = $this->get_token_email( $token );

		return $this->set_email_to_token( $email, $token, $is_global_token );
	}

	public static function get_google_client_id() {
		$transient_key = GP_Google_Sheets::get_addon_slug() . '_google-oauth-client-id';

		// Check for a cached client id and use that if it exists
		$client_id = get_transient( $transient_key );
		if ( $client_id !== false ) {
			return $client_id;
		}

		$response = wp_remote_get(
			GP_Google_Sheets::GWIZ_OAUTH_SERVICE_URL . '/oauth/google/client-id',
			array(
				'headers'     => array(
					'Content-Type' => 'application/json; charset=utf-8',
				),
				'method'      => 'GET',
				'data_format' => 'body',
			),
		);

		$body = json_decode( $response['body'], true );

		if ( ! empty( $body['client_id'] ) ) {
			// Cache the client id for a week.
			$one_week = 60 * 60 * 24 * 7;
			set_transient( $transient_key, $body['client_id'], $one_week );

			return $body['client_id'];
		}

		return null;
	}

	/**
	 * @param $token The token to use to create the Google client instance.
	 * @throws InvalidArgumentException
	 */
	public static function get_google_client( $token ) {
		$client    = new Google_Client();
		$client_id = self::get_google_client_id();
		$client->setClientId( $client_id );
		$client->setAccessToken( $token );

		return $client;
	}

	public static function get_token_email( $token ) {
		$url      = add_query_arg( 'access_token', $token['access_token'], 'https://www.googleapis.com/oauth2/v1/userinfo' );
		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$data = GFCommon::maybe_decode_json( wp_remote_retrieve_body( $response ) );

		return rgar( $data, 'email' );
	}

	public function set_token( $token, $user_id, $is_global_token = false ) {
		$email = self::get_token_email( $token );

		$this->set_email_to_token( $email, $token, $is_global_token );
		$this->set_user_id_to_email( $user_id, $email );
	}

	public function get_global_token() {
		foreach ( $this->_emails_to_tokens as $email => $token ) {
			if ( rgar( $token, 'is_global_token' ) === true ) {
				$token = $this->maybe_refresh_token( $token );

				// Update runtime cache with possibly-refreshed token.
				$this->_emails_to_tokens[ $email ] = $token;

				return $token;
			}
		}

		return null;
	}

	public function delete_global_token() {
		$global_token = null;

		// TODO should handle disconnectring all feeds using the global token as well?

		foreach ( $this->_emails_to_tokens as $email => $token ) {
			if ( rgar( $token, 'is_global_token' ) === true ) {
				$this->delete_email_to_token_mapping( $email );
			}
		}
	}

	public function get_token_by_google_email( $email ) {
		return rgar( $this->_emails_to_tokens, $email );
	}

	public function get_token_by_user_id( $user_id ) {
		$email = rgars( $this->_user_ids_to_emails, "{$user_id}/0" );
		$token = rgar( $this->_emails_to_tokens, $email );

		$token = $this->maybe_refresh_token( $token );

		// Update runtime cache with possibly-refreshed token.
		$this->_emails_to_tokens[ $email ] = $token;

		return $token;
	}

	public function get_feed_access_token( $feed ) {
		$spreadsheet_id    = GP_Google_Sheets_Writer::spreadsheet_id( $feed );
		$token_owner_email = rgars( $this->_spreadsheets_to_emails, "{$spreadsheet_id}/{$feed['id']}" );

		if ( $token_owner_email === null ) {
			return null;
		}

		$token = rgar( $this->_emails_to_tokens, $token_owner_email );

		if ( $token === null ) {
			return null;
		}

		$token = $this->maybe_refresh_token( $token );

		// Update runtime cache with possibly-refreshed token.
		$this->_emails_to_tokens[ $token_owner_email ] = $token;

		return $token;
	}

	public function get_global_token_owner_email() {
		foreach ( $this->_emails_to_tokens as $email => $token ) {
			if ( rgar( $token, 'is_global_token' ) === true ) {
				return $email;
			}
		}

		return null;
	}

	public function set_spreadsheet_with_global_token( $spreadsheet_id, $feed_id ) {
		foreach ( $this->_emails_to_tokens as $email => $token ) {
			if ( rgar( $token, 'is_global_token' ) === true ) {
				$this->set_spreadsheet_id_to_email( $spreadsheet_id, $feed_id, $email );
				break;
			}
		}
	}

	public function set_spreadsheet_with_user_token( $spreadsheet_id, $feed_id, $user_id ) {
		$email = $this->get_token_email(
			$this->get_token_by_user_id( $user_id )
		);

		$this->set_spreadsheet_id_to_email( $spreadsheet_id, $feed_id, $email );
	}

	public static function should_refresh_access_token( $token ) {
		$current_milliseconds = floor( microtime( true ) * 1000 );
		// if the expiry token has expired or will do so within the next 20 seconds, it should be refreshed.
		$expiry_time = rgar( $token, 'expiry_date' );

		return $expiry_time !== null && $expiry_time < ( $current_milliseconds + 20000 );
	}

	public function get_feeds_connected_to_email( $email ) {
		$feeds = array();

		foreach ( $this->_spreadsheets_to_emails as $spreadsheet_id => $feed_ids ) {
			foreach ( $feed_ids as $feed_id => $feed_email ) {
				if ( $feed_email === $email ) {
					$feed = gp_google_sheets()->get_feed( $feed_id );
					$form = \GFAPI::get_form( $feed['form_id'] );

					$feeds[] = array(
						'spreadsheet_id' => $spreadsheet_id,
						'feed_id'        => $feed_id,
						'form_id'        => $feed['form_id'],
						'form_title'     => $form['title'],
						'feed_name'      => $feed['meta']['feed_name'],
						'feed_url'       => admin_url( 'admin.php?page=gf_edit_forms&view=settings&subview=gp-google-sheets&id=' . $feed['form_id'] . '&fid=' . $feed_id ),
					);
				}
			}
		}

		return $feeds;
	}
}
