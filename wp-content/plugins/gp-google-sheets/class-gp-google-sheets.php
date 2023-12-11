<?php
/**
 * @package gp-google-sheets
 * @copyright Copyright (c) 2022, Gravity Wiz, LLC
 * @author Gravity Wiz <support@gravitywiz.com>
 * @license GPLv2
 * @link https://gravitywiz.com/documentation/gravity-forms-google-sheets/
 */
defined( 'ABSPATH' ) or exit;

use \GP_Google_Sheets\Dependencies\Google\Service\Sheets\Spreadsheet as Google_Service_Sheets_Spreadsheet;
use \GP_Google_Sheets\Dependencies\Google\Service\Sheets\BatchUpdateSpreadsheetRequest as Google_Service_Sheets_BatchUpdateSpreadsheetRequest;
use \GP_Google_Sheets\Dependencies\Google\Service\Sheets\Sheet as Google_Service_Sheets_Sheet;
use \GP_Google_Sheets\Dependencies\Google\Service\Sheets\SheetProperties as Google_Service_Sheets_SheetProperties;

if ( class_exists( 'GP_Feed_Plugin' ) ) {
	class GP_Google_Sheets extends GP_Feed_Plugin {

		/**
		 * Defines the version the add-On.
		 *
		 * @since 1.0
		 * @var string $_version Contains the version.
		 */
		protected $_version = GP_GOOGLE_SHEETS_VERSION;

		/**
		 * Defines the minimum Gravity Forms version required.
		 *
		 * @since 1.0
		 * @var string $_min_gravityforms_version The minimum version required.
		 */
		protected $_min_gravityforms_version = '2.5';

		/**
		 * @var string $_slug The add-on slug doubles as the key in which all the settings are stored. If this changes, also change uninstall.php where the string is hard-coded.
		 * @see get_slug()
		 */
		protected $_slug = 'gp-google-sheets';

		/**
		 * Defines the main plugin file.
		 *
		 * @since 1.0
		 * @var string $_path The path to the main plugin file, relative to the plugins folder.
		 */
		protected $_path = 'gp-google-sheets/gp-google-sheets.php';

		/**
		 * Defines the full path to this class file.
		 *
		 * @since 1.0
		 * @var string $_full_path The full path.
		 */
		protected $_full_path = __FILE__;

		/**
		 * Defines the URL where this add-on can be found.
		 *
		 * @since 1.0
		 * @var string
		 */
		protected $_url = 'https://gravitywiz.com';

		/**
		 * Defines the title of this add-on.
		 *
		 * @since 1.0
		 * @var string $_title The title of the add-on.
		 */
		protected $_title = 'GP Google Sheets';

		/**
		 * Defines the short title of the add-on.
		 *
		 * @since 1.0
		 * @var string $_short_title The short title.
		 */
		protected $_short_title = 'Google Sheets';

		/**
		 * Contains an instance of this class, if available.
		 *
		 * @since 1.0
		 * @var GP_Google_Sheets $_instance If available, contains an instance of this class
		 */
		private static $_instance = null;

		/**
		 * Even though we use Action Scheduler, we still want async feed processing as we immediately run the action.
		 *
		 * @since 2.2
		 * @var bool
		 */
		protected $_async_feed_processing = true;

		/**
		 * Defines if Add-On should use Gravity Forms servers for update data.
		 *
		 * @since  1.0
		 * @var    bool
		 */
		protected $_enable_rg_autoupgrade = false;

		/**
		 * Defines the capabilities needed for the Add-On. Ensures compatibility
		 * with Members plugin.
		 *
		 * @since  1.0
		 * @var    array $_capabilities The capabilities needed for the Add-On
		 */
		protected $_capabilities = array(
			'gp-google-sheets',
			'gp-google-sheets_uninstall',
			'gp-google-sheets_results',
			'gp-google-sheets_settings',
			'gp-google-sheets_form_settings',
		);

		/**
		 * Defines the capability needed to access the Add-On settings page.
		 *
		 * @since  1.0
		 * @var    string $_capabilities_settings_page The capability needed to access the Add-On settings page.
		 */
		protected $_capabilities_settings_page = 'gp-google-sheets_settings';

		/**
		 * Defines the capability needed to access the Add-On form settings page.
		 *
		 * @since  1.0
		 * @var    string $_capabilities_form_settings The capability needed to access the Add-On form settings page.
		 */
		protected $_capabilities_form_settings = 'gp-google-sheets_form_settings';

		/**
		 * Defines the capability needed to uninstall the Add-On.
		 *
		 * @since  1.0
		 * @var    string $_capabilities_uninstall The capability needed to uninstall the Add-On.
		 */
		protected $_capabilities_uninstall = 'gp-google-sheets_uninstall';

		/**
		 * Instance of the GP_Google_Sheets_Tokens class.
		 */
		public $tokens;

		const NONCE_AJAX             = 'gpgs_settings_nonce';
		const SCRIPT_HANDLE          = 'gpgs_settings';
		const GWIZ_OAUTH_SERVICE_URL = 'https://oauth.gravitywiz.com';

		/**
		 * @credit https://github.com/google/site-kit-wp
		 */
		public function setup_autoload() {
			try {
				spl_autoload_register(
					function ( $class_path ) {
						$prefix = 'GP_Google_Sheets_';
						if ( $prefix != substr( $class_path, 0, strlen( $prefix ) ) ) {
							return;
						}

						//Replace the prefix with 'class-'
						$class_path = strtolower( str_replace( $prefix, 'class-', $class_path ) );

						$file_path = plugin_dir_path( __FILE__ ) . 'includes'
							. DIRECTORY_SEPARATOR
							. str_replace( '_', '-', $class_path . '.php' );

						file_exists( $file_path ) && require $file_path;
					}
				);

				$class_map = array_merge(
					include plugin_dir_path( __FILE__ ) . 'third-party/vendor/composer/autoload_classmap.php'
				);

				spl_autoload_register(
					function ( $class ) use ( $class_map ) {
						if ( isset( $class_map[ $class ] ) && substr( $class, 0, 29 ) === 'GP_Google_Sheets\\Dependencies' ) {
							require_once $class_map[ $class ];
						}
					},
					true,
					true
				);
			} catch ( \TypeError $e ) {
				$this->log_error( __METHOD__ . '(): Could not initialize autoloader. ' . $e->getMessage() );
			}
		}

		/**
		 * Handles hooks and loading of language files.
		 */
		public function init() {
			$this->setup_autoload();

			$authenticator = new GP_Google_Sheets_Authenticator();
			$authenticator->hooks();

			parent::init();

			$this->tokens = GP_Google_Sheets_Tokens::get_instance();

			/**
			 * Check if our setting to edit & delete rows in the Sheet is enabled
			 * each time an entry is edited or deleted.
			 */
			add_action( 'gform_after_update_entry', array( $this, 'handle_after_update_entry' ), 10, 2 ); //legacy save_lead() calls
			add_action( 'gform_post_update_entry', array( $this, 'handle_post_update_entry' ), 10, 2 ); //newer update_entry() calls
			add_action( 'gform_update_status', array( $this, 'entry_status_changed' ), 10, 3 );
			add_action( 'gform_post_payment_action', array( $this, 'entry_payment_status_changed' ), 10, 2 );

			//Add additional tooltips to Gravity Forms for our settings page
			add_filter( 'gform_tooltips', array( $this, 'add_tooltips' ) );

			/**
			 * AJAX handlers
			 */

			//Handle an AJAX call behind the Disconnect buttons
			add_action( 'wp_ajax_gpgs_disconnect', array( $this, 'disconnect' ) );
			add_action( 'wp_ajax_gpgs_disconnect_plugin', array( $this, 'disconnect_plugin' ) );

			//Handle an AJAX call behind the Insert Test Row button
			add_action( 'wp_ajax_gpgs_insert_test_row', array( $this, 'insert_test_row' ) );

			// Endpoint to be used after a spreadsheet is selected
			add_action( 'wp_ajax_gpgs_select_spreadsheet', array( $this, 'ajax_select_spreadsheet' ) );

			// Endpoint to be used after a sheet is selected
			add_action( 'wp_ajax_gpgs_select_sheet', array( $this, 'ajax_select_sheet' ) );

			add_action( 'wp_ajax_gpgs_token_health', array( $this, 'ajax_token_health' ) );

			add_action( 'wp_ajax_gpgs_get_spreadsheets_markup', array( $this, 'ajax_get_gppa_spreadsheets_markup' ) );

			/**
			 * We need more complexity in the way we make translatable strings
			 * available to this add-on's settings page. We're adding Gravity Forms
			 * tooltips to the page with JavaScript.
			 */
			add_action( 'admin_enqueue_scripts', array( $this, 'more_strings_for_javascript' ), 100 );

			//Add more core fields to our field map list of fields
			add_filter( 'gform_field_map_choices', array( $this, 'add_more_core_fields_to_field_map' ), 10, 4 );

			//The sheets are created during the feed settings validation
			//No easy way to edit the settings values at that time, so
			add_action( 'gform_post_save_feed_settings', array( $this, 'save_sheet_url_after_create_sheet' ), 10, 4 );
			//Handles changes a user makes to the field map in feed settings
			add_action( 'gform_post_save_feed_settings', array( $this, 'update_sheet_after_field_map_change' ), 10, 4 );

			add_action( 'gform_pre_delete_feed', array( $this, 'handle_feed_deleted' ), 10, 2 );

			// Add entry meta box for seeing the status of each feed
			add_filter( 'gform_entry_detail_meta_boxes', array( $this, 'register_entry_meta_box' ), 10, 3 );
			add_action( 'gform_pre_entry_detail', array( $this, 'entry_details_maybe_process_feed' ), 10, 2 );

			// Feed error notifications
			add_filter( 'gform_notification_events', array( $this, 'add_feed_error_notification_event' ), 10, 2 );

			add_action( 'gpgs_refresh_access_tokens_cron_hook', array( $this, 'refresh_access_tokens' ) );

			// add notice if no plugin token is set (e.g. aauthentication with Google has not happened yet.)
			add_action( 'admin_notices', array( $this, 'maybe_display_disconnected_notice' ) );

			// add notice if no plugin token is set (e.g. aauthentication with Google has not happened yet.)
			add_action( 'admin_notices', array( $this, 'maybe_display_http_warning' ) );

			add_filter( 'gform_settings_save_button', array( $this, 'remove_plugin_settings_save_button' ), 10, 2 );

			add_filter( 'install_plugin_complete_actions', array( $this, 'add_back_to_plugin_settings_action' ), 10, 3 );
			add_filter( 'wp_redirect', array( $this, 'redirect_to_plugin_settings' ) );

			// Add delayed payment support to the feed
			$this->add_delayed_payment_support( array() );

			// Check to see if the refresh token cron needs to be run.
			$this->maybe_schedule_access_token_refresh_cron();

			// Load the GP Populate Anything integration
			if ( class_exists( 'GPPA_Object_Type' ) ) {
				require_once plugin_dir_path( __FILE__ ) . 'includes/class-object-type-google-sheet.php';

				gp_populate_anything()->register_object_type( 'gpgs_sheet', 'GP_Google_Sheets_GPPA_Object_Type_Google_Sheet' );
			}

			GP_Google_Sheets_Compatibility_GravityView::get_instance();

			// Action Scheduler hooks
			add_action( 'gp_google_sheets_add_entry_to_sheet', array( $this, 'add_entry_to_sheet' ), 10, 4 );
			add_action( 'gp_google_sheets_delete_entry_from_sheet', array( $this, 'delete_entry_from_sheet' ), 10, 4 );
			add_action( 'gp_google_sheets_edit_entry_in_sheet', array( $this, 'edit_entry_in_sheet' ), 10, 4 );
			add_action( 'gp_google_sheets_migrate_google_tokens', array( $this, 'upgrade_to_1_0' ), 10, 0 );

			add_action( 'gravityview/delete-entry/deleted', array( $this, 'gpgs_gravityview_delete' ), 25, 2 );
		}

		public function init_admin() {
			parent::init_admin();

			add_action( 'admin_init', array( $this, 'maybe_purge_action_scheduler' ) );
		}

		public function maybe_schedule_access_token_refresh_cron() {
			if ( wp_next_scheduled( 'gpgs_refresh_access_tokens_cron_hook' ) ) {
				return;
			}

			$cron_interval = 'weekly';
			/**
			 * Filter the interval at which the plugin will refresh access tokens.
			 *
			 * @param string $cron_interval The interval in seconds to run the cron job. See wp_get_schedules() for the accepted values. https://developer.wordpress.org/reference/functions/wp_get_schedules/
			 */
			$cron_interval = apply_filters( 'gpgs_refresh_access_tokens_cron_interval', $cron_interval );

			wp_schedule_event( time(), $cron_interval, 'gpgs_refresh_access_tokens_cron_hook' );
		}

		public function refresh_access_tokens() {
			foreach ( $this->tokens->get_emails_to_tokens() as $email => $token ) {
				$this->tokens->refresh_and_persist_token( $token );
			}

			// ------------------------------------------
			// Legacy token refreshing
			// ------------------------------------------
			$global_token = GP_Google_Sheets_Legacy_Tokens::get_global_token();

			if ( $global_token ) {
				if ( ! GP_Google_Sheets_Legacy_Tokens::refresh_and_persist_global_token( $global_token ) ) {
					$this->log_error( __METHOD__ . '(): Failed to refresh global access token due to a null response from refresh_access_token().' );
				}
			}

			$feeds = $this->get_feeds();

			foreach ( $feeds as $feed ) {
				$token = rgars( $feed, 'meta/token' );

				if ( ! $token || rgar( $token, 'gwiz_oauth' ) !== true ) {
					continue;
				}

				GP_Google_Sheets_Legacy_Tokens::refresh_and_persist_normal_token( $token, $feed );
			}
		}

		public function upgrade( $previous_version ) {
			if ( version_compare( $previous_version, '1.0', '<' ) ) {
				as_schedule_single_action(
					time(),
					'gp_google_sheets_migrate_google_tokens',
					array(),
					'gpgs-migrate-google-tokens',
					false, // Do not use unique as it does not appear to take the arguments into consideration.
					10
				);
			}
		}

		/**
		 * Handles migrating the plugin to version 1.0.
		 *
		 * This primarily involves migrating tokens to a new storage schema:
		 *
		 *  - In Beta 1.0, users created their own OAuth application and then tokens were stored
		 *    in feed meta _or_ the plugin setting (the latter is known as the "global token").
		 *    Any token created during this time is now known as a "legacy token" and must be refreshed
		 *    using the personal OAuth application.
		 *
		 * - In Beta 2.0, we created our own OAuth application but continued to store tokens in the same
		 *   way. That is, in feed meta and plugin settings.
		 *
		 * - In 1.0, we are migrating to a new storage schema that is more robust and allows us to only
		 *   over have a single token per WordPress user. Implementation of this new schema is primarily
		 *   in the GP_Google_Sheets_Tokens class.
		 */
		public function upgrade_to_1_0() {
			$feeds  = $this->get_feeds();
			$tokens = GP_Google_Sheets_Tokens::get_instance();

			$settings               = $this->get_plugin_settings();
			$global_token           = $settings['token'];
			$global_token_email     = null;
			$global_token_not_empty = ! empty( $global_token ) || ( ! is_array( $global_token ) && ! GFCommon::is_json( $global_token ) );

			/**
			 * Check for a global token and move it over to the email -> token mappings _if_ it is not a legacy token.
			 * The reason for this is that legacy tokens do not have the correct scopes to get the associated google
			 * account email and thus we have no way of creating the email -> token mapping if it is a legacy token.
			 */
			if ( rgar( $global_token, 'gwiz_oauth' ) === true && $global_token_not_empty ) {
				$global_token_email = $tokens::get_token_email( $global_token );
				$tokens->set_email_to_token( $global_token_email, $global_token, true );

				unset( $settings['token'] );

				$this->update_plugin_settings( $settings );
			}

			foreach ( $feeds as $feed ) {
				if ( ! empty( $feed['meta']['sheet_was_picked'] ) && $feed['meta']['sheet_was_picked'] === '1' ) {
					$token = rgars( $feed, 'meta/picked_token' );

					// this is a legacy token and should not be migrated.
					if ( rgar( $token, 'gwiz_oauth' ) !== true ) {
						continue;
					}

					// token already migrated
					if ( empty( $token ) ) {
						continue;
					}

					$email          = $tokens::get_token_email( $token );
					$spreadsheet_id = GP_Google_Sheets_Writer::spreadsheet_id( $feed );

					// Do not overwrite a user's token if it has already been set since it could be the
					// global token and we do not want to overwrite that one.
					if ( ! $tokens->get_token_by_google_email( $email ) ) {
						$tokens->set_email_to_token( $email, $token, false );
					}

					// update the spreadsheet_id -> feed_id -> email mapping so that we know which token
					// belongs to this spreadsheet/feed.
					$tokens->set_spreadsheet_id_to_email( $spreadsheet_id, $feed['id'], $email );

					// Clear out the old meta fields that are no longer used.
					$deprecated_feed_meta_fields = array(
						'token',
						'picked_token',
						'sheet_was_picked',
					);

					foreach ( $deprecated_feed_meta_fields as $field_name ) {
						if ( rgar( $feed['meta'], $field_name ) ) {
							unset( $feed['meta'][ $field_name ] );
						}
					}

					$this->update_feed_meta( $feed['id'], $feed['meta'] );
				} elseif ( $global_token_email ) {
					// if there is a global token that is _not_ a legacy token, then we've already migrated it.
					// now we just need to create to spreadsheet -> email mapping.
					$spreadsheet_id = GP_Google_Sheets_Writer::spreadsheet_id( $feed );
					$tokens->set_spreadsheet_id_to_email( $spreadsheet_id, $feed['id'], $global_token_email );
				}
			}
		}

		public function remove_plugin_settings_save_button( $button, $settings ) {
			if ( rgget( 'page' ) === 'gf_settings' && rgget( 'subview' ) === 'gp-google-sheets' ) {
				return '';
			}

			return $button;
		}

		public function add_more_core_fields_to_field_map( $choices, $form_id, $input_type, $excluded_types ) {
			foreach ( $choices as $key => $arr ) {
				if ( __( 'Entry Properties', 'gravityforms' ) != $arr['label'] ) {
					continue;
				}

				$core_fields = array(
					array(
						'label' => __( 'Created By (User Id)', 'gravityforms' ),
						'value' => 'created_by',
					),
					array(
						'label' => __( 'Transaction Id', 'gravityforms' ),
						'value' => 'transaction_id',
					),
					array(
						'label' => __( 'Payment Amount', 'gravityforms' ),
						'value' => 'payment_amount',
					),
					array(
						'label' => __( 'Payment Date', 'gravityforms' ),
						'value' => 'payment_date',
					),
					array(
						'label' => __( 'Payment Status', 'gravityforms' ),
						'value' => 'payment_status',
					),
					array(
						'label' => __( 'Post Id', 'gravityforms' ),
						'value' => 'post_id',
					),
					array(
						'label' => __( 'User Agent', 'gravityforms' ),
						'value' => 'user_agent',
					),
				);

				$choices[ $key ]['choices'] = array_merge( $choices[ $key ]['choices'], $core_fields );
				break;
			}
			return $choices;
		}

		/**
		 * Filter callback that adds tooltips to Gravity Forms
		 */
		function add_tooltips( $tooltips ) {
			$new_tips = array(
				array(
					'slug'    => 'google_sheet_create_sheet',
					'title'   => __( 'Create New Sheet', 'gp-google-sheets' ),
					'content' => __( 'Create a new sheet inside the configured Google Drive account.', 'gp-google-sheets' ),
				),
				array(
					'slug'    => 'google_sheet_disconnect',
					'title'   => __( 'Disconnect', 'gp-google-sheets' ),
					'content' => __( 'Deletes the authorization token that grants us permission to edit the Google Sheet.', 'gp-google-sheets' ),
				),
				array(
					'slug'    => 'google_sheet_insert_test_row',
					'title'   => __( 'Insert Test Row', 'gp-google-sheets' ),
					'content' => __( 'Creates a new row in the Google Sheet containing sample data.', 'gp-google-sheets' ),
				),
				array(
					'slug'    => 'google_sheet_only_user_who_connected',
					'title'   => __( 'Add Spreadsheet', 'gp-google-sheets' ),
					'content' => __( '<p>Add a spreadsheet to give Populate Anything access to populate data from that spreadsheet.</p><p><b>Important:</b> Only the Google account connected in the Google Sheets Settings can be used to add new spreadsheets.</p>', 'gp-google-sheets' ),
				),
				array(
					'slug'    => 'google_sheet_legacy_token_info',
					'title'   => __( 'Legacy Token', 'gp-google-sheets' ),
					'content' => __( '<p>Legacy tokens were created if connecting to Google on Google Sheets Beta 1</p><p><b>Important:</b> It is recommended that you reconnect any feeds with a legacy token as these will be fully deprecated in a future release.</p>', 'gp-google-sheets' ),
				),
			);

			foreach ( $new_tips as $new_tip ) {
				$tooltips[ $new_tip['slug'] ] = sprintf(
					'<h6>%s</h6>%s',
					$new_tip['title'],
					$new_tip['content']
				);
			}
			return $tooltips;
		}

		public function ajax_select_sheet() {
			if ( empty( $_GET['_ajax_nonce'] )
				|| ! wp_verify_nonce( $_GET['_ajax_nonce'], self::NONCE_AJAX ) ) {
				return;
			}

			$spreadsheet_url  = sanitize_text_field( $_GET['sheet_url'] );
			$spreadsheet_name = sanitize_text_field( $_GET['spreadsheet_name'] );
			$sheet_id         = sanitize_text_field( $_GET['sheet_id'] );
			$user_id          = get_current_user_id();
			$sheet_name       = sanitize_text_field( $_GET['sheet_name'] );
			$spreadsheet_id   = GP_Google_Sheets_Writer::spreadsheet_id( array( 'meta' => array( 'google_sheet_url' => $spreadsheet_url ) ) );
			$fake_feed        = $this->create_feed_from_get();
			$service          = GP_Google_Sheets_Authenticator::create_service( $fake_feed, $this->tokens->get_token_by_user_id( $user_id ) );

			if ( is_wp_error( $service ) ) {
				wp_send_json_error( array(
					'message' => 'Could not connect to Google Sheets. Please check your credentials and try again.',
				) );
			}

			wp_send_json_success( array(
				'spreadsheetLinkMarkup' => $this->generate_sheet_link_markup( $spreadsheet_url, $spreadsheet_name, $sheet_id, $sheet_name ),
				'controlsHTML'          => $this->force_field_markup_field_map( $fake_feed, $service ),
				'sheets'                => GP_Google_Sheets_Reader::get_spreadsheet_sheets( $service, $spreadsheet_id ),
			) );
		}

		public function ajax_select_spreadsheet() {
			if ( empty( $_GET['_ajax_nonce'] )
				|| ! wp_verify_nonce( $_GET['_ajax_nonce'], self::NONCE_AJAX ) ) {
				return;
			}

			$fake_feed        = $this->create_feed_from_get();
			$spreadsheet_url  = sanitize_text_field( $_GET['sheet_url'] );
			$user_id          = get_current_user_id();
			$spreadsheet_id   = GP_Google_Sheets_Writer::spreadsheet_id( array( 'meta' => array( 'google_sheet_url' => $spreadsheet_url ) ) );
			$spreadsheet_name = __( 'Open the sheet', 'gp-google-sheets' );
			$service          = GP_Google_Sheets_Authenticator::create_service( $fake_feed, $this->tokens->get_token_by_user_id( $user_id ) );

			if ( is_wp_error( $service ) ) {
				wp_send_json_error( array(
					'message' => 'Could not connect to Google Sheets. Please check your credentials and try again.',
				) );
			}

			$fetched_spreadsheet_name = GP_Google_Sheets_Reader::get_spreadsheet_name( $service, $spreadsheet_id );
			if ( is_string( $fetched_spreadsheet_name ) ) {
				$spreadsheet_name = $fetched_spreadsheet_name;
			}

			$sheet_id   = 0;
			$sheet_name = '';
			$sheets     = GP_Google_Sheets_Reader::get_spreadsheet_sheets( $service, $spreadsheet_id );

			/**
			 * If the spreadsheet has only one sheet, we auto select it and thus need
			 * to include the sheet name in the link markup.
			 */
			if ( count( $sheets ) === 1 ) {
				$sheet_id   = $sheets[0]['id'];
				$sheet_name = $sheets[0]['title'];
			}

			// Generate field map markup containing existing columns in the Sheet
			wp_send_json_success( array(
				'spreadsheetLinkMarkup' => $this->generate_sheet_link_markup( $spreadsheet_url, $spreadsheet_name, $sheet_id, $sheet_name ),
				'spreadsheetName'       => $spreadsheet_name,
				'sheets'                => GP_Google_Sheets_Reader::get_spreadsheet_sheets( $service, $spreadsheet_id ),
			) );
		}

		public function ajax_token_health() {
			if ( empty( $_GET['_ajax_nonce'] ) || ! wp_verify_nonce( $_GET['_ajax_nonce'], self::NONCE_AJAX ) ) {
				wp_send_json_error( array(
					'message'    => 'You do not have permission to access this page.',
					'token_data' => array(),
				) );
			}

			if ( ! GFCommon::current_user_can_any( 'gp-google-sheets_settings' ) ) {
				wp_send_json_error( array(
					'message'    => 'You do not have permission to access this page.',
					'token_data' => array(),
				) );
			}

			$token_data         = array();
			$user_ids_to_emails = $this->tokens->get_user_ids_to_emails();
			$emails_to_user_ids = array();

			foreach ( $user_ids_to_emails as $user_id => $emails ) {
				$emails_to_user_ids[ $emails[0] ] = $user_id;
			}

			foreach ( $this->tokens->get_emails_to_tokens() as $email => $token ) {
				$token            = $this->tokens->maybe_refresh_token( $token );
				$token_is_healthy = true;

				if ( empty( $token ) || GP_Google_Sheets_Tokens::should_refresh_access_token( $token ) ) {
					$token_is_healthy = false;
				}

				$user_id           = $emails_to_user_ids[ $email ];
				$user_data         = get_userdata( $user_id );
				$user_display_name = null;

				if ( $user_data ) {
					$user_display_name = $user_data->display_name;
				}

				$token_data[] = array(
					'tokenIsHealthy'       => $token_is_healthy,
					'googleEmail'          => $email,
					'userDisplayName'      => $user_display_name,
					'userEditLink'         => get_edit_user_link( $user_id ),
					'belongsToCurrentUser' => $emails_to_user_ids[ $email ] === get_current_user_id(),
					'connectedFeeds'       => $this->tokens->get_feeds_connected_to_email( $email ),
					'legacyToken'          => false,
				);
			}

			$feeds                    = $this->get_feeds();
			$feeds_using_global_token = array();

			foreach ( $feeds as $feed ) {
				// Beta 2 and Legacy tokens that used the picker
				$legacy_token = rgars( $feed, 'meta/picked_token' );

				$form = \GFAPI::get_form( $feed['form_id'] );

				$feed_specific_token = $this->tokens->get_feed_access_token( $feed );

				if ( $legacy_token ) {
					// some of the legacy tokens do not have an "expiry_date" attached so we have to actually try and refresh them
					// to see if they are still valid.
					$legacy_token = GP_Google_Sheets_Legacy_Tokens::refresh_access_token( $legacy_token );

					$token_is_healthy = true;
					if ( $legacy_token === null ) {
						$token_is_healthy = false;
					}

					$token_data[] = array(
						'tokenIsHealthy'       => $token_is_healthy,
						'googleEmail'          => null,
						'userDisplayName'      => null,
						'userEditLink'         => null,
						'belongsToCurrentUser' => null,
						'connectedFeeds'       => array(
							array(
								'feed_id'        => $feed['id'],
								'spreadsheet_id' => GP_Google_Sheets_Writer::spreadsheet_id( $feed ),
								'form_id'        => $feed['form_id'],
								'form_title'     => $form['title'],
								'feed_name'      => $feed['meta']['feed_name'],
								'feed_url'       => admin_url( 'admin.php?page=gf_edit_forms&view=settings&subview=gp-google-sheets&id=' . $feed['form_id'] . '&fid=' . $feed['id'] ),
							),
						),
						'legacyToken'          => true,
					);
				} elseif ( ! $feed_specific_token || rgar( $feed_specific_token, 'is_global_token' ) === true ) {
					$feeds_using_global_token[] = array(
						'feed_id'        => $feed['id'],
						'spreadsheet_id' => GP_Google_Sheets_Writer::spreadsheet_id( $feed ),
						'form_id'        => $feed['form_id'],
						'form_title'     => $form['title'],
						'feed_name'      => $feed['meta']['feed_name'],
						'feed_url'       => admin_url( 'admin.php?page=gf_edit_forms&view=settings&subview=gp-google-sheets&id=' . $feed['form_id'] . '&fid=' . $feed['id'] ),
					);
				}
			}

			$legacy_global_token = GP_Google_Sheets_Legacy_Tokens::get_global_token();
			if ( ! empty( $legacy_global_token ) ) {
				$legacy_global_token = GP_Google_Sheets_Legacy_Tokens::refresh_access_token( $legacy_global_token );

				$global_token_healthy = true;
				if ( $legacy_global_token === null ) {
					$global_token_healthy = false;
				}

				$token_data[] = array(
					'tokenIsHealthy'       => $global_token_healthy,
					'googleEmail'          => null,
					'userDisplayName'      => null,
					'userEditLink'         => null,
					'belongsToCurrentUser' => null,
					'connectedFeeds'       => $feeds_using_global_token,
					'legacyToken'          => true,
				);
			}

			wp_send_json_success( array(
				'token_data' => $token_data,
			) );
		}

		public function ajax_get_gppa_spreadsheets_markup() {
			if ( empty( $_GET['_ajax_nonce'] ) || ! wp_verify_nonce( $_GET['_ajax_nonce'], self::NONCE_AJAX ) ) {
				wp_send_json_error( array(
					'message' => 'You do not have permission to access this page.',
					'markup'  => null,
				) );
			}

			if ( ! GFCommon::current_user_can_any( 'gp-google-sheets_settings' ) ) {
				wp_send_json_error( array(
					'message' => 'You do not have permission to access this page.',
					'markup'  => null,
				) );
			}

			/**
			 * Filter whether to show the callout to the GP Populate Anything integration if Populate Anything is
			 * not installed.
			 *
			 * @param bool $show_gppa_integration Whether to show the callout. Default is `true`.
			 *
			 * @since 1.0-beta-2.0
			 */
			$show_gppa_integration = apply_filters( 'gpgs_show_gppa_integration', true );

			if ( ! class_exists( 'GPPA_Object_Type' ) && $show_gppa_integration ) {
				wp_send_json_success( array(
					'markup' => $this->get_gppa_upsell_markup(),
				) );
			}

			$spreadsheets = $this->get_available_spreadsheets();

			$output  = '<div class="gform-settings-description">';
			$output .= sprintf(
				// translators: placeholder is a link to the documentation.
				__( 'Populate Anything has access to the following spreadsheets. To populate data from these spreadsheets, select "Google Sheet" as the "Type" in the Populate Anything field settings. <a href="%s" target="_blank">Learn more</a>.', 'gp-google-sheets' ),
				'https://gravitywiz.com/documentation/gravity-forms-populate-anything/#google-sheets'
			);
			$output .= '</div>';

			$output .= '<ul>';

			foreach ( $spreadsheets as $file ) {
				// translators: placeholders are the name of a Spreadsheet and the ID of the spreadsheet.
				$output .= sprintf(
					'<li><a href="%s" target="_blank"><span class="dashicons dashicons-media-spreadsheet" style="text-decoration: none;margin-right:.25rem;"></span>%s</a></li>',
					esc_attr( $file->getWebViewLink() ),
					$file->getName(),
				);
			}

			$output .= '</ul>';

			// Button to authorize new spreadsheet using the picker. Only show it if the token is NOT a legacy token.
			if ( ! $this->has_legacy_token() ) {
				$output .= '<div style="margin-top: 1rem;display: flex;align-items: center;">';
				$output .= '<button class="button" id="gp-google-sheets-add-spreadsheet" style="margin-right: .5rem;">' . esc_html__( 'Add Spreadsheet', 'gp-google-sheets' ) . '</button>';
				$output .= $this->maybe_tooltip_html( 'google_sheet_only_user_who_connected', '', true );
				$output .= '</div>';
			}

			wp_send_json_success( array(
				'markup' => $output,
			) );
		}

		/**
		 * Generates the markup for a link to a Google Spreadsheet. If sheet_id and sheet_name are provided,
		 * the link will point to a specific sheet in the spreadsheet.
		 *
		 * @param string $spreadsheet_url The URL of the spreadsheet.
		 * @param string $spreadsheet_name The name of the spreadsheet.
		 * @param string $sheet_id The ID of the sheet.
		 * @param string $sheet_name The name of the sheet.
		 *
		 * @return string The markup for the link.
		 */
		public function generate_sheet_link_markup( $spreadsheet_url, $spreadsheet_name, $sheet_id = '0', $sheet_name = '' ) {
			$link_text = $spreadsheet_name;

			if ( $sheet_name ) {
				$link_text = "{$link_text} ({$sheet_name})";
			}

			return sprintf(
				'<p><a href="%s" target="_blank">%s</a></p>',
				$this->build_sheet_url( $spreadsheet_url, $sheet_id ),
				$link_text
			);
		}

		/**
		 * Build a link to a specific sheet in a spreadsheet.
		 *
		 * @param string $spreadsheet_url The URL of the spreadsheet.
		 * @param string $sheet_id The ID of the sheet.
		 *
		 * @return string The link to the sheet.
		 */
		public function build_sheet_url( $spreadsheet_url, $sheet_id = '0' ) {
			/**
			 * A spreadsheet url can contain a #gid= hash that specifies which sheet to open.
			 * Example: https://docs.google.com/spreadsheets/d/1ndppqF6hErAiLAtUZ-D72nhq3h4S8-j4N3dUBSp-400/edit#gid=0
			 * Adding or replacing this hash key/value will result in a url that points directly to a sheet rather than
			 * the first sheet in a spreadsheet.
			 */
			if ( strpos( $spreadsheet_url, '#gid=' ) === false ) {
				return $spreadsheet_url . '#gid=' . $sheet_id;
			}

			return preg_replace( '/#gid([^?])+/', "$1{$sheet_id}", $spreadsheet_url );
		}

		/**
		 * Returns the url of a feed's connected sheet.
		 *
		 * @param $feed array The feed to get the connected sheet url for.
		 */
		public function get_sheet_url_from_feed( $feed ) {
			if ( ! rgars( $feed, 'meta/google_sheet_url' ) ) {
				return null;
			}

			$sheet_id  = rgar( $feed['meta'], 'google_sheet_id', 0 );
			$sheet_url = rgar( $feed['meta'], 'google_sheet_url' );

			if ( ! $sheet_url ) {
				return null;
			}

			return $this->build_sheet_url( $sheet_url, $sheet_id );
		}

		/**
		 * Returns the name of a feed's connected sheet.
		 *
		 * @param $feed array The feed to get the connected sheet name for.
		 */
		public function get_spreadsheet_name_from_feed( $feed ) {
			// this can happen if the feed is not saved.
			if ( ! $feed ) {
				return null;
			}

			$spreadsheet_id = GP_Google_Sheets_Writer::spreadsheet_id( $feed );

			// this can happen if the spreadsheet has been disconnected from a feed
			if ( ! $spreadsheet_id ) {
				return null;
			}

			$service                = GP_Google_Sheets_Authenticator::create_service( $feed );
			$spreadsheet_name       = __( 'Open the sheet', 'gp-google-sheets' );
			$sheet_name_from_reader = GP_Google_Sheets_Reader::get_spreadsheet_name( $service, $spreadsheet_id );
			$spreadsheet_name       = $sheet_name_from_reader ? $sheet_name_from_reader : $spreadsheet_name;

			return $spreadsheet_name;
		}

		public function get_sheet_name_from_feed( $feed ) {
			// this can happen if the feed is not saved.
			if ( ! $feed ) {
				return null;
			}

			$spreadsheet_id = GP_Google_Sheets_Writer::spreadsheet_id( $feed );

			// this can happen if the spreadsheet has been disconnected from a feed
			if ( ! $spreadsheet_id ) {
				return null;
			}

			$service = GP_Google_Sheets_Authenticator::create_service( $feed );

			$tab = GP_Google_Sheets_Reader::find_tab( $service, $spreadsheet_id, $feed );

			if ( $tab === false || $tab instanceof \Exception ) {
				return null;
			}

			return $tab->tab_name;
		}

		/**
		 * Creates an array structure including column labels and at least one row
		 * of values that can be passed to GP_Google_Sheets_Writer class methods as the data
		 * payload to add to a Google Sheet. Without an $entry_id, a single sample
		 * row will be populated into the rows member of the output.
		 *
		 * @param array $feed A Gravity Forms feed instance
		 * @param int $form_id The form ID from which column data is extracted
		 * @param int|array $entries An entry ID, a GF_Entry object, or an array of GF_Entry objects from which to extract row data
		 * @param bool $for_append is row data being prepared for a append_rows() call? If so, we have to write empty strings in user columns
		 */
		private function create_row_data( $feed, $form_id, $entries = array(), $for_append = false ) {
			// Ensure necessary Gravity Forms classes are loaded
			require_once GFCommon::get_base_path() . '/form_display.php';
			require_once GFCommon::get_base_path() . '/export.php';

			$row_data = array(
				'columns' => array(),
				'rows'    => array(),
			);

			//Look at the sheet, make sure our field map is still accurate
			$feed = $this->maybe_update_field_map_setting( $feed['id'], $form_id );

			//Do we have a field map?
			if ( ! $this->field_map_is_empty( $feed['meta']['column_mapping'] ) ) {
				//We have a field map
				$form = GFAPI::get_form( $form_id );
				//Add fields to the form like user agent & ip
				if ( $form !== false && ! empty( $form['fields'] ) ) {
					/**
					 * Filters through the Form Export Page
					 *
					 * @see GFForms::select_export_form()
					 *
					 * @param int $form The Form Object of the form to export
					 */
					$form = gf_apply_filters( array( 'gform_form_export_page', $form_id ), $form );

					/**
					 * @see GFForms::select_export_form()
					 */
					$form = GFExport::add_default_export_fields( $form );
				}

				if ( is_integer( $entries ) ) {
					$entries = array( GFAPI::get_entry( $entries ) );
				}

				//If the $entry is null, use $fields to populate junk into it
				if ( empty( $entries ) && ! empty( $form['fields'] ) ) {
					$fields  = GP_Google_Sheets_Forms::extract_field_ids( $form );
					$entries = array( GP_Google_Sheets_Forms::create_test_entry( $form, $fields ) );
				}

				if ( ! is_array( $entries ) ) {
					$entries = array( $entries );
				}

				for ( $e = 0; $e < sizeof( $entries ); $e++ ) {
					$values       = array();
					$column_index = $e;
					foreach ( $feed['meta']['column_mapping'] as $column ) {
						$column_index = GP_Google_Sheets_Range_Parser::letters_to_index( $column['key'] );
						if ( $e == 0 ) {
							$row_data['columns'][ $column_index ] = array(
								'label' => $this->prepare_field_label( $form, $column['value'] ),
								'value' => $column['value'],
							);
						}
						$values[ $column_index ] = $this->prepare_entry_value( $entries[ $e ], $column, $form );
					}

					//Add entry ID, it gets written as developer metadata
					$row_data['entry_ids'][] = rgar( $entries[ $e ], 'id' );

					$fill_value = $for_append ? '' : null;

					$row_data['rows'][] = array_values( $this->fill_missing_array_keys( $values, 0, $fill_value ) );
				}
			}

			/**
			 * Filter the data that is used to populate a row.
			 *
			 * @param array $row_data The data to use for population.
			 * @param array $feed The current feed.
			 * @param int $form_id The current form ID.
			 * @param array $entries Entries to be inserted.
			 *
			 * @since 1.0-beta-1
			 */
			return apply_filters( 'gpgs_sheet_row_data', $row_data, $feed, $form_id, $entries );
		}

		private function has_leading_zero( $number ) {
			// If we're not working with a string/number, return false.
			// If the number is just zero, then we want to still treat it as a number so return false here, too.
			if ( ! is_scalar( $number ) || $number == '0' ) {
				return false;
			}

			return GFCommon::safe_substr( $number, 0, 1 ) === '0';
		}

		protected function prepare_entry_value( $entry, $column, $form ) {
			/*
			 * Run gform_pre_render on the form prior to getting the entry value that way plugins such as
			 * GP Populate Anything can manipulate the form and its choices beforehand.
			 */
			$form = gf_apply_filters( array( 'gform_pre_render', $form['id'] ), $form, false, $entry );

			$field_id = $column['value'];
			$value    = '';
			switch ( $field_id ) {
				case 'date_created':
				case 'payment_date':
				case 'entry_date':
					$value = $entry[ $field_id ];
					if ( $value ) {
						$lead_gmt_time   = mysql2date( 'G', $value );
						$lead_local_time = GFCommon::get_local_timestamp( $lead_gmt_time );
						$value           = date_i18n( 'Y-m-d H:i:s', $lead_local_time, true );
					}
					break;
				case 'entry_id':
				case 'user_ip':
				case 'created_by':
				case 'transaction_id':
				case 'payment_amount':
				case 'payment_status':
				case 'post_id':
				case 'user_agent':
					$value = $entry[ $field_id ];
					break;
				case 'form_title':
					$value = $form['title'];
					break;
				case 'gf_custom':
					$value = $column['custom_value'];
					$value = GFCommon::replace_variables( $value, $form, $entry, false, false, false, 'text' );
					break;
				default:
					$field = GFAPI::get_field( $form, $field_id );

					$value          = is_object( $field ) ? $field->get_value_export( $entry, $field_id, false, true ) : rgar( $entry, $field_id );
					$original_value = $value;

					/*
					 * Cast numbers to strings so Google inserts it as a number instead of string.
					 * This makes formulas work better and not require VALUE()
					 */
					if ( is_numeric( $value ) && ! $this->has_leading_zero( $value ) ) {
						$value = $value + 0;
					}

					/**
					 * Cast list values to a pipe-delimited string to better match Gravity Forms export behavior.
					 */
					if ( is_array( $value ) ) {
						$list_value = '';

						foreach ( $value as $row ) {
							if ( is_array( $row ) ) {
								$list_value .= implode( '|', $row ) . "\n";
							} else {
								$list_value .= $row . '|';
							}
						}

						$value = rtrim( trim( $list_value ), '|' );
					}

					$value = apply_filters( 'gform_export_field_value', $value, $form['id'], $field_id, $entry );

					/**
					 * Filter a value before it gets inserted into a row in Google Sheets.
					 *
					 * @param mixed $value The value to be inserted.
					 * @param int $form_id The current form ID.
					 * @param string $field_id The current field ID.
					 * @param array $entry The current entry.
					 * @param mixed $original_value The original value before any filters were applied.
					 *
					 * @since 1.0-beta-1.10
					 */
					$value = gf_apply_filters( array( 'gpgs_row_value', $form['id'], $field_id ), $value, $form['id'], $field_id, $entry, $original_value );
					break;
			}
			return $value;
		}

		protected function prepare_field_label( $form, $field_id ) {
			$field = RGFormsModel::get_field( $form, $field_id );
			if ( $field !== null ) {
				$field->set_context_property( 'use_admin_label', '1' );
				$value = GFCommon::get_label( $field, $field_id );
				$value = gf_apply_filters( array( 'gform_entries_field_header_pre_export', $form['id'], $field_id ), $value, $form, $field );
			} else {
				//form_title
				$value = $field_id;
			}
			$value = str_replace( '"', '""', $value );
			if ( strpos( $value, '=' ) === 0 ) {
				// Prevent Excel formulas
				$value = "'" . $value;
			}
			return $value;
		}

		/**
		 * create_sheet
		 *
		 * @param  string $sheet_name
		 * @return string|false The URL to the newly-created Google Sheet
		 */
		protected function create_sheet( $sheet_name ) {
			$feed    = array();
			$feed_id = $this->get_current_feed_id();

			if ( $feed_id ) {
				$feed = $this->get_feed( $feed_id );
				if ( is_wp_error( $feed ) ) {
					$this->log_debug( __METHOD__ . '(): ' . $feed->get_error_message() );
					return false;
				}
			}

			//Name the sheet the same as the feed
			$spreadsheet = new Google_Service_Sheets_Spreadsheet( array(
				'properties' => array( //more props https://developers.google.com/sheets/api/reference/rest/v4/spreadsheets/sheets#SheetProperties
					'title' => $sheet_name,
				),
				'sheets'     => array(
					$this->new_sheet_tab_with_metadata(),
				),
			) );

			$global_token = $this->tokens->get_global_token();

			$service      = GP_Google_Sheets_Authenticator::create_service( $feed, $global_token );
			$spreadsheets = GP_Google_Sheets_Authenticator::get_spreadsheets_resource( $service );

			if ( is_wp_error( $service ) ) {
				$this->log_debug( __METHOD__ . '(): ' . $service->get_error_message() );
				return false;
			}

			try {
				$spreadsheet = $spreadsheets->create( $spreadsheet, array(
					'fields' => 'spreadsheetId',
				) );
			} catch ( \Exception $e ) {
				$this->log_debug( __METHOD__ . '(): ' . $e->getMessage() );
				return false;
			}

			//Save the spreadsheet ID in the form settings by generating a URL
			$spreadsheet_url = sprintf(
				'https://docs.google.com/spreadsheets/d/%s/edit',
				$spreadsheet->spreadsheetId
			);

			//Sheet was created successfully
			$this->log_debug( __METHOD__ . '(): Sheet was created successfully at ' . $spreadsheet_url );
			return $spreadsheet_url;
		}

		function disconnect() {
			if ( empty( $_POST ) || empty( $_POST['form_id'] )
				|| empty( $_POST['_ajax_nonce'] )
				|| ! wp_verify_nonce( $_POST['_ajax_nonce'], self::NONCE_AJAX ) ) {
				wp_send_json_error();
			}

			$feed_id = intval( $_POST['feed_id'] );
			$feed    = $this->get_feed( $feed_id );
			if ( is_wp_error( $feed ) ) {
				wp_send_json_error();
			}

			/**
			 * When deleting a feed token, we need to also try and delete any token that may have been stored
			 * with legacy methods. Thus, the call to delete_feed_mapping() and delete_feed_token().
			 */
			$this->tokens->delete_feed_mapping( $feed );
			GP_Google_Sheets_Legacy_Tokens::delete_feed_token( $feed );

			// this particular feed setting is now deprecated, but we need to still clear it out to support feeds
			// connected before deprecation.
			$deprecated_feed_meta_fields = array(
				'token',
				'picked_token',
				'google_sheet_url_field',
				'google_sheet_url',
				'google_sheet_id',
				'sheet_was_picked',
			);

			foreach ( $deprecated_feed_meta_fields as $field_name ) {
				if ( rgar( $feed['meta'], $field_name ) ) {
					unset( $feed['meta'][ $field_name ] );
				}
			}

			$feed_addon = self::get_instance();
			$feed_addon->update_feed_meta( $feed['id'], $feed['meta'] );

			wp_send_json_success();
		}

		function disconnect_plugin() {
			if ( empty( $_POST ) || empty( $_POST['_ajax_nonce'] )
				|| ! wp_verify_nonce( $_POST['_ajax_nonce'], self::NONCE_AJAX ) ) {
				wp_send_json_error();
			}

			/**
			 * When deleting the global token, we need to also try and delete any token that may have been stored
			 * with legacy methods. Thus, the two delete_global_token() calls.
			 */
			$this->tokens->delete_global_token();
			GP_Google_Sheets_Legacy_Tokens::delete_global_token();

			wp_send_json_success();
		}

		/**
		 * Registers a notification event for Google Sheets feed errors.
		 *
		 * @param array $events Register notification events.
		 * @param array $form The current form.
		 *
		 * @return array
		 */
		public function add_feed_error_notification_event( $events, $form ) {
			if ( ! $this->get_active_feeds( $form['id'] ) ) {
				return $events;
			}

			$events['gpgs_feed_error'] = __( 'Google Sheets Feed Error' );

			return $events;
		}

		/**
		 * Extend add_feed_error to trigger a notification event.
		 *
		 * @todo Add a merge tag for the error message.
		 *
		 * @param $error_message
		 * @param $feed
		 * @param $entry
		 * @param $form
		 *
		 * @return void
		 */
		function add_feed_error( $error_message, $feed, $entry, $form ) {
			parent::add_feed_error( $error_message, $feed, $entry, $form );

			$notifications = GFCommon::get_notifications_to_send( 'gpgs_feed_error', $form, $entry );
			$ids           = array();

			foreach ( $notifications as $notification ) {
				$ids[] = $notification['id'];
			}

			GFCommon::send_notifications( $ids, $form, $entry, true, 'gpgs_feed_error' );
		}

		/**
		 * Delete and entry from a given feed and retry if the delete attempt fails
		 *
		 * @param $entry_id The ID of the entry that was deleted
		 * @param $feed_id The ID of the feed which connects to the given $spreadsheet_id
		 * @param $spreadsheet_id The ID of the spreadsheet that the entry should be deleted from
		 *
		 * @throws \Exception Expected to be handled by Action Scheduler.
		 */
		function delete_entry_from_sheet( $entry_id, $feed_id, $spreadsheet_id, $attempts_made = 0 ) {
			$feed          = $this->get_feed( $feed_id );
			$entry         = GFAPI::get_entry( $entry_id );
			$service       = GP_Google_Sheets_Authenticator::create_service( $feed );
			$attempts_made = $attempts_made + 1;

			try {
				// Filter hook purely for testing that retry logic works as intended.
				if ( apply_filters( 'gpgs_testing_should_fail_action', false, __METHOD__, $attempts_made, $feed, $entry ) ) {
					throw new Exception( 'Testing failure of action.' );
				}

				//Find this entry's row in the sheet so we can overwrite that single row
				$row_index = GP_Google_Sheets_Reader::find_row_by_metadata_value( $service, $spreadsheet_id, $feed['form_id'], $entry_id );

				if ( $row_index === false ) {
					//Something is wrong, we can't find the entry in the Sheet
					$message = sprintf(
						'Cannot find Entry %s in Sheet %s to delete.',
						$entry_id,
						$spreadsheet_id
					);

					$form = GFAPI::get_form( $entry['form_id'] );

					$this->log_debug( __METHOD__ . '(): ' . $message );
					$this->add_feed_error( $message, $feed, $entry, $form );
					return;
				}

				if ( is_wp_error( $service ) ) {
					//We can't delete a row because we can't connect to Google
					$message = 'Cannot connect to Google. ' . $service->get_error_message();
					$this->log_debug( __METHOD__ . '(): ' . $message );
					$form = GFAPI::get_form( $entry['form_id'] );
					return;
				}

				//Delete the row at $row_index
				GP_Google_Sheets_Writer::delete_row( $service, $spreadsheet_id, $row_index + 1, $feed );
			} catch ( \Exception $ex ) {
				if ( $attempts_made > GP_Google_Sheets_Retry::MAX_RETRY_ATTEMPTS ) {
					$message = 'Could not delete entry from Google Sheets and maximum retries have already been attempted. ' . $ex->getMessage();
					$this->log_debug( __METHOD__ . '(): ' . $message );
					$this->add_feed_error( $message, $feed, $entry, GFAPI::get_form( $entry['form_id'] ) );
					return;
				}

				GP_Google_Sheets_Retry::schedule_single_action(
					'gp_google_sheets_delete_entry_from_sheet',
					array(
						'entry_id'       => $entry_id,
						'feed_id'        => $feed_id,
						'spreadsheet_id' => $spreadsheet_id,
						'attempts_made'  => $attempts_made,
					),
					$entry_id,
					15,
					$attempts_made
				);

				// Re-throw the exception for Action Scheduler to catch, log, and mark the action as failed.
				throw $ex;
			}
		}

		/**
		 * Delete anentry from GravityView.
		 *
		 * @param $entry_id The ID of the entry that was deleted.
		 * @param $entry    The entry to be deleted.
		 *
		 * @throws \Exception Expected to be handled by Action Scheduler.
		 */
		function gpgs_gravityview_delete( $entry_id, $entry ) {

			$feeds        = $this->get_active_feeds( $entry['form_id'] );
			$current_feed = false;
			foreach ( $feeds as $feed ) {
				if ( rgar( $feed, 'form_id' ) === rgar( $entry, 'form_id' ) ) {
					$current_feed = $feed;
				}
			}

			if ( ! $current_feed ) {
				return null;
			}

			$spreadsheet_id = GP_Google_Sheets_Writer::spreadsheet_id( $current_feed );

			if ( ! $spreadsheet_id ) {
				return null;
			}

			$feed_id = rgar( $current_feed, 'id' );

			$this->delete_entry_from_sheet( $entry_id, $feed_id, $spreadsheet_id );
		}

		function delete_entry_from_connected_sheets( $entry_id ) {
			//Get form ID from $entry_id
			$entry = GFAPI::get_entry( $entry_id );
			if ( is_wp_error( $entry ) || empty( $entry['form_id'] ) ) {
				return;
			}

			$active_feeds = GP_Google_Sheets_Forms::find_our_feeds( $entry['form_id'] );
			if ( empty( $active_feeds ) ) {
				//There are no active feeds for this form
				return;
			}

			foreach ( $active_feeds as $feed ) {
				$spreadsheet_id = GP_Google_Sheets_Writer::spreadsheet_id( $feed );

				//Do we have a Sheet URL? Check the feed settings
				if ( empty( $spreadsheet_id ) ) {
					//No. Need a Sheet before we can make updates inside
					continue;
				}

				$edit_rows = false;
				if ( ! empty( $feed['meta']['edit_rows'] ) || $feed['meta']['edit_rows'] === '1' ) {
					$edit_rows = true;
				}

				$form = GFAPI::get_form( $entry['form_id'] );

				/**
				 * Disable Google Sheet row deletion when the "edit_rows" config option is enabled.
				 *
				 * @param bool $should_delete_sheet_row Whether or not the Google Sheet row should be deleted. Default is if the "edit_rows" config option value.
				 * @param array $form The current form.
				 * @param array $feed The current feed.
				 * @param array $entry The current entry.
				 *
				 * @since 1.0-beta-1.5
				 *
				 * @return bool
				 */
				$should_delete_sheet_row = gf_apply_filters( array( 'gpgs_should_delete_google_sheets_row', $form['id'] ), $edit_rows, $form, $feed, $entry );

				//Is the feature to edit & delete enabled on this form?
				if ( ! $should_delete_sheet_row ) {
					//No
					continue;
				}

				// queue up the delete action for immediately processing
				GP_Google_Sheets_Retry::enqueue_async_action(
					'gp_google_sheets_delete_entry_from_sheet',
					array(
						'entry_id'       => $entry_id,
						'feed_id'        => $feed['id'],
						'spreadsheet_id' => $spreadsheet_id,
					),
					$entry_id
				);
			}
		}

		/**
		 * @throws \Exception Expected to be handled by Action Scheduler.
		 */
		function edit_entry_in_sheet( $entry_id, $feed_id, $spreadsheet_id, $attempts_made = 0 ) {
			$feed          = $this->get_feed( $feed_id );
			$entry         = GFAPI::get_entry( $entry_id );
			$form          = GFAPI::get_form( $entry['form_id'] );
			$attempts_made = $attempts_made + 1;

			try {
				// Filter hook purely for testing that retry logic works as intended.
				if ( apply_filters( 'gpgs_testing_should_fail_action', false, __METHOD__, $attempts_made, $feed, $entry ) ) {
					throw new Exception( 'Testing failure of action.' );
				}

				$service = GP_Google_Sheets_Authenticator::create_service( $feed );

				if ( is_wp_error( $service ) ) {
					throw new \Exception( $service->get_error_message() );
				}

				//Look at the sheet, make sure our field map is still accurate
				$feed = $this->maybe_update_field_map_setting( $feed['id'], $feed['form_id'] );

				/**
				 * Create a row for the recently-edited entry. Convert $entry_id to an
				 * integer because Gravity Forms lies about this parameter being an
				 * integer.
				 */
				$row_data = $this->create_row_data( $feed, $feed['form_id'], (int) $entry_id );
				if ( empty( $row_data['rows'] ) ) {
					/**
					 * There's no data to write--this feed probably has an empty
					 * field map, so the entry exists but there's nothing to
					 * write.
					 */
					return;
				}

				//Find this entry's row in the sheet so we can overwrite that single row
				$row_index = GP_Google_Sheets_Reader::find_row_by_metadata_value( $service, $spreadsheet_id, $feed['form_id'], $entry_id );

				if ( $row_index === false ) {
					/**
					 * Can't find the entry in the Sheet
					 * This feed could have been inactive when this entry was
					 * submitted.
					 *
					 * Do not log an issue if there is a payment status associated as it's likely a delayed
					 * feed. Note: $this->maybe_delay_feed() isn't a reliable check here.
					 */
					if ( ! rgar( $entry, 'payment_status' ) ) {
						$message = sprintf(
							'Cannot find Entry %s in Sheet %s to edit.',
							$entry_id,
							$spreadsheet_id
						);

						$this->log_debug( __METHOD__ . '(): ' . $message );
						$this->add_feed_error( $message, $feed, $entry, $form );
					}

					return;
				}

				GP_Google_Sheets_Writer::edit_row( $service, $feed, $spreadsheet_id, $row_index, $row_data );
			} catch ( \Exception $ex ) {
				if ( $attempts_made > GP_Google_Sheets_Retry::MAX_RETRY_ATTEMPTS ) {
					$message = 'Could not edit entry in Google Sheets and maximum retries have already been attempted. ' . $ex->getMessage();
					$this->log_debug( __METHOD__ . '(): ' . $message );
					$this->add_feed_error( $message, $feed, $entry, GFAPI::get_form( $entry['form_id'] ) );
					return;
				}

				// queue up a retry for the edit action.
				GP_Google_Sheets_Retry::schedule_single_action(
					'gp_google_sheets_edit_entry_in_sheet',
					array(
						'entry_id'       => $entry_id,
						'feed_id'        => $feed_id,
						'spreadsheet_id' => $spreadsheet_id,
						'attempts_made'  => $attempts_made,
					),
					$entry_id,
					10,
					$attempts_made
				);

				// Re-throw the exception for Action Scheduler to catch, log, and mark the action as failed.
				throw $ex;
			}
		}

		/**
		 * gform_after_update_entry and gform_post_update_entry have different signatures. This methods passes everything
		 * to handle_after_update_entry().
		 *
		 * @param array $lead           The entry object after being updated.
		 * @param array $original_entry The entry object before being updated.
		 */
		function handle_post_update_entry( $lead, $original_entry ) {
			$this->handle_after_update_entry( GFAPI::get_form( $lead['form_id'] ), $lead );
		}

		/**
		 * @param array $form The Gravity Forms form array for the entry.
		 * @param int|array $entry_or_id The entry ID or entry object.
		 */
		function handle_after_update_entry( $form, $entry_or_id ) {
			if ( empty( $form['id'] ) ) {
				return;
			}

			if ( ! empty( rgar( $entry_or_id, 'id' ) ) ) {
				$entry_id = rgar( $entry_or_id, 'id' );
				$entry    = $entry_or_id;
			} elseif ( is_numeric( $entry_or_id ) ) {
				$entry_id = $entry_or_id;
				$entry    = GFAPI::get_entry( $entry_id );
			}

			if ( empty( $entry_id ) || empty( $entry ) || is_wp_error( $entry ) ) {
				return;
			}

			$active_feeds = GP_Google_Sheets_Forms::find_our_feeds( $form['id'] );
			if ( empty( $active_feeds ) ) {
				//There are no active feeds for this form
				return;
			}

			foreach ( $active_feeds as $feed ) {
				$spreadsheet_id = GP_Google_Sheets_Writer::spreadsheet_id( $feed );

				//Do we have a Sheet URL? Check the feed settings
				if ( empty( $spreadsheet_id ) ) {
					//No. Need a Sheet before we can make updates inside
					continue;
				}

				//Is the edit feature enabled for this form?
				if ( empty( $feed['meta']['edit_rows'] ) ) {
					//No
					continue;
				}

				$token = $this->tokens->get_feed_access_token( $feed );

				if ( empty( $token ) ) {
					$token = GP_Google_Sheets_Legacy_Tokens::get_feed_access_token( $feed );
				}

				if ( ! $token ) {
					$this->add_feed_error(
						__( 'Missing token. Ensure that the plugin is properly authenticated and the feed is properly configured.', 'gp-google-sheets' ),
						$feed,
						$entry,
						$form
					);

					continue;
				}

				GP_Google_Sheets_Retry::enqueue_async_action(
					'gp_google_sheets_edit_entry_in_sheet',
					array(
						'entry_id'       => $entry_id,
						'feed_id'        => $feed['id'],
						'spreadsheet_id' => $spreadsheet_id,
					),
					$entry_id,
				);
			}
		}

		/**
		 * entry_status_changed
		 *
		 * @param  integer $entry_id Current entry ID.
		 * @param  string $property_value New value of the entry’s property (ie “Active”, “Spam”, “Trash”).
		 * @param  string $previous_value Previous value of the entry’s property (ie “Active”, “Spam”, “Trash”).
		 * @return void
		 */
		function entry_status_changed( $entry_id, $property_value, $previous_value ) {
			switch ( strtolower( $property_value ) ) {
				case 'trash':
				case 'spam':
					//The entry was just trashed, delete the row from the Sheet
					$this->delete_entry_from_connected_sheets( $entry_id );
					return;
				case 'active':
					//The entry was just untrashed or unspammed
					$entry = GFAPI::get_entry( $entry_id );
					if ( is_wp_error( $entry ) ) {
						return;
					}

					//Do we have an active feed for this form?
					$active_feeds = GP_Google_Sheets_Forms::find_our_feeds( $entry['form_id'] );
					if ( empty( $active_feeds ) ) {
						//No
						return;
					}

					foreach ( $active_feeds as $feed ) {
						$spreadsheet_id = GP_Google_Sheets_Writer::spreadsheet_id( $feed );

						//Do we have a Sheet URL? Check the feed settings
						if ( empty( $spreadsheet_id ) ) {
							//No. Need a Sheet before we can make updates inside
							continue;
						}

						//process the feed
						$this->process_feed( $feed, $entry, GFAPI::get_form( $entry['form_id'] ) );
					}
					return;
			}

		}

		/**
		 * Handle payment status changes.
		 *
		 * To prevent adding rows if the feed is configured to be delayed until payment is captured, we can just
		 * ignore some statuses like pending for now.
		 *
		 * @param array $entry The Entry Object
		 * @param array $action {
		 *     The action performed.
		 *
		 *     @type string $type             The callback action type. Required.
		 *     @type string $transaction_id   The transaction ID to perform the action on. Required if the action is a payment.
		 *     @type string $subscription_id  The subscription ID. Required if this is related to a subscription.
		 *     @type string $amount           The transaction amount. Typically required.
		 *     @type int    $entry_id         The ID of the entry associated with the action. Typically required.
		 *     @type string $transaction_type The transaction type to process this action as. Optional.
		 *     @type string $payment_status   The payment status to set the payment to. Optional.
		 *     @type string $note             The note to associate with this payment action. Optional.
		 * }
		 */
		public function entry_payment_status_changed( $entry, $action ) {
			if ( rgar( $entry, 'payment_status' ) === 'Pending' ) {
				return;
			}

			$this->handle_after_update_entry( GFAPI::get_form( $entry['form_id'] ), $entry );
		}

		/**
		 * Get an instance of this class.
		 *
		 * @return GP_Google_Sheets
		 */
		public static function get_instance() {
			if ( self::$_instance == null ) {
				self::$_instance = new GP_Google_Sheets();
			}

			return self::$_instance;
		}

		/**
		 * Sets the minimum requirements for the Perk.
		 *
		 * @return array
		 */
		public function minimum_requirements() {
			return array(
				'gravityforms' => array(
					'version' => '2.5',
				),
				'plugins'      => array(
					'gravityperks/gravityperks.php' => array(
						'name'    => 'Gravity Perks',
						'version' => '2.0',
					),
				),
				'php'          => array(
					'version' => '7.3',
				),
			);
		}

		/**
		 * Prevent the class from being cloned
		 *
		 * @since 1.0
		 */
		private function __clone() {
		} /* do nothing */

		/**
		 * Ensure perk is authenticated before allowing feeds to be created.
		 *
		 * @since  1.0
		 *
		 * @return bool
		 */
		public function can_create_feed() {
			return $this->has_global_token();
		}

		/**
		 * Indicates if the feed can be duplicated.
		 *
		 * @since 1.0
		 * @since 1.3 Enabled feed duplication.
		 *
		 * @param int $id Feed ID requesting duplication.
		 *
		 * @return bool
		 */
		public function can_duplicate_feed( $id ) {
			return true;
		}

		public function has_global_token() {
			return ! empty( $this->tokens->get_global_token() ) || ! empty( GP_Google_Sheets_Legacy_Tokens::get_global_token() );
		}

		/**
		 * Setup columns for feed list table.
		 *
		 * @return array
		 * @since  1.0
		 *
		 */
		public function feed_list_columns() {
			return array(
				'feed_name' => esc_html__( 'Name', 'gp-google-sheets' ),
			);
		}

		/**
		 * Format the value to be displayed in the spreadsheet_link column.
		 *
		 * @param array $feed The feed being included in the feed list.
		 *
		 * @return string
		 */
		public function get_column_value_feed_name( $feed ) {
			$output = '';

			if ( rgars( $feed, 'meta/google_sheet_url' ) ) {
				$output .= sprintf(
					'<a href="%s" target="_blank" title="%s"><span class=""><span class="dashicons dashicons-media-spreadsheet"></span></a>',
					rgars( $feed, 'meta/google_sheet_url' ),
					esc_attr__( 'Open Spreadsheet', 'gp-google-sheets' )
				) . '&nbsp;';
			}

			$feed_name = rgars( $feed, 'meta/feed_name' ) ? rgars( $feed, 'meta/feed_name' ) : rgars( $feed, 'meta/feedName' );

			$output .= $feed_name;

			return $output;
		}

		public static function get_addon_slug() {
			return self::get_instance()->get_slug();
		}

		/**
		 * update_plugin_settings
		 *
		 * Does not allow values to be saved for connect button
		 *
		 * @param  mixed $settings
		 * @return void
		 */
		public function update_plugin_settings( $settings ) {
			unset( $settings['connect_button'] );
			parent::update_plugin_settings( $settings );
		}

		public function get_title() {
			return $this->_title;
		}

		/**
		 * save_sheet_url_after_create_sheet
		 *
		 * Gravity Forms doesn't make it easy for add-ons to change feed
		 * settings during the validation step, and that's when we create the
		 * Sheet. This callback on the gform_post_save_feed_settings hook takes
		 * the new Sheet URL and saves it in the feed settings that were just
		 * saved.
		 *
		 * @param string  $feed_id  The ID of the feed which was saved.
		 * @param int     $form_id  The current form ID associated with the feed.
		 * @param array   $settings An array containing the settings and mappings for the feed.
		 * @param GFAddOn $addon    The current instance of the GFAddOn object which extends GFFeedAddOn or GFPaymentAddOn (i.e. GFCoupons, GF_User_Registration, GFStripe).
		 * @return void
		 */
		public function save_sheet_url_after_create_sheet( $feed_id, $form_id, $settings, $addon ) {
			$spreadsheet_url = rgpost( '_gform_setting_google_sheet_url' );
			$spreadsheet_id  = GP_Google_Sheets_Writer::spreadsheet_id(
				array( 'meta' => array( 'google_sheet_url' => $spreadsheet_url ) )
			);

			if ( empty( $spreadsheet_url ) || ! empty( rgars( $settings, 'meta/google_sheet_url' ) ) ) {
				return;
			}

			$settings['google_sheet_url'] = $spreadsheet_url;
			if ( rgpost( '_gform_setting_google_sheet_url_field' ) === 'create' ) {
				// When creating a new spreadsheet, we only add one sheet to it which will have an id of "0"
				$settings['google_sheet_id'] = '0';
				$this->tokens->set_spreadsheet_with_global_token( $spreadsheet_id, $feed_id );
			} else {
				$settings['google_sheet_id'] = rgpost( '_gform_setting_google_sheet_id' );
				$this->tokens->set_spreadsheet_with_user_token( $spreadsheet_id, $feed_id, get_current_user_id() );
			}

			$this->update_feed_meta( $feed_id, $settings );
		}

		protected function generate_alert_markup( $message, $is_error = false ) {
			return sprintf(
				'<div class="alert gforms_note_%s gpgs_alert" role="alert">%s</div>',
				( ! $is_error ) ? 'success' : 'error',
				$message
			);
		}

		function insert_test_row() {
			$error_message = __( 'Test row insertion failed.', 'gp-google-sheets' );
			if ( empty( $_POST['_ajax_nonce'] )
				|| ! wp_verify_nonce( $_POST['_ajax_nonce'], self::NONCE_AJAX ) ) {
				wp_send_json_error( array(
					'message' => $this->generate_alert_markup( $error_message, true ),
				) );
			}

			$form_id = intval( $_POST['form_id'] );
			//This is how you get the settings of a feed type add-on
			$feed_id = intval( $_POST['feed_id'] );
			if ( empty( $feed_id ) ) {
				//can't get current feed ID
				wp_send_json_error( array(
					'message' => $this->generate_alert_markup( $error_message, true ),
				) );
			}
			$feed = $this->get_feed( $feed_id );

			//If there is no field map, there's nothing to test yet
			if ( $this->field_map_is_empty( $feed['meta']['column_mapping'] ) ) {
				$this->log_debug( __METHOD__ . '(): Cannot insert a test row. There is no field map.' );
				wp_send_json_error( array(
					'message' => $this->generate_alert_markup(
						$error_message . __( ' Choose at least one field to send in the Column Mapping section.', 'gp-google-sheets' ),
						true
					),
				) );
			}

			//Look at the sheet, make sure our field map is still accurate
			$feed = $this->maybe_update_field_map_setting( (string) $feed_id, $form_id );

			$row_data = $this->create_row_data( $feed, $form_id, array(), true );

			$this->log_debug( __METHOD__ . '(): Inserting a test row' );

			try {
				GP_Google_Sheets_Writer::append_rows( $feed, $row_data );

				/**
				 * TODO  this will now get executed in a thread that is not handling a UI request and we should probably
				 * move away from sending JSON as a result.
				 * */
				wp_send_json_success( array(
					'message' => $this->generate_alert_markup( __( 'Test row inserted successfully.', 'gp-google-sheets' ) ),
				) );
			} catch ( Exception $ex ) {
				$this->log_debug( __METHOD__ . '(): ' . $ex->getMessage() );
				wp_send_json_error( array(
					'message' => $this->generate_alert_markup(
						$error_message . ' ' . $ex->getMessage(),
						true
					),
				) );
			}
		}

		/**
		 * Provides more strings for JavaScript. Gravity Forms runs the scripts()
		 * method of add-ons like this one before Gravity Forms is loaded, it seems.
		 * gform_tooltip() isn't available by the time scripts() runs, so this
		 * method provides more strings variables to JavaScript from a place where
		 * gform_tooltip() is available.
		 *
		 * @todo find a way to merge this in with the other localization methods
		 */
		function more_strings_for_javascript() {
			if ( ! function_exists( 'gform_tooltip' ) ) {
				return;
			}

			wp_localize_script(
				self::SCRIPT_HANDLE,
				'gpgs_settings_strings_more',
				array(
					'tooltip_create_sheet'    => gform_tooltip( 'google_sheet_create_sheet', '', true ),
					'tooltip_disconnect'      => gform_tooltip( 'google_sheet_disconnect', '', true ),
					'tooltip_insert_test_row' => gform_tooltip( 'google_sheet_insert_test_row', '', true ),
				)
			);
		}

		private function new_sheet_tab_with_metadata() {
			$sheet      = new Google_Service_Sheets_Sheet();
			$properties = new Google_Service_Sheets_SheetProperties();
			$properties->setSheetId( '0' );
			$sheet->setProperties( $properties );
			return $sheet;
		}

		protected function create_feed_from_get() {
			$feed_id = intval( $_GET['feed_id'] );

			if ( $feed_id === 0 ) {
				$feed['meta']['token']            = (array) json_decode( stripslashes( sanitize_text_field( rgget( 'token' ) ) ) );
				$feed['meta']['sheet_was_picked'] = sanitize_text_field( rgget( 'sheet_was_picked' ) );
			} else {
				$feed = $this->get_feed( $feed_id );
				if ( is_wp_error( $feed ) ) {
					wp_send_json_error();
				}
			}

			$feed['meta']['picked_token']     = (array) json_decode( stripslashes( rgget( 'picked_token' ) ) );
			$feed['meta']['google_sheet_url'] = sanitize_text_field( rgget( 'sheet_url' ) );
			$feed['meta']['google_sheet_id']  = sanitize_text_field( rgget( 'sheet_id' ) );

			return $feed;
		}

		protected function force_field_markup_field_map( $feed, $service = null ) {
			$first_row_data = $this->get_first_row( $feed, $service );

			$fake_field = array(
				'type'      => 'generic_map',
				'name'      => 'column_mapping',
				'key_field' => array(
					'title'       => __( 'Sheet Column', 'gp-google-sheets' ),
					'placeholder' => __( 'Column heading', 'gp-google-sheets' ),
					'choices'     => $this->field_map_key_field_choices( $first_row_data ),
				),
			);
			$renderer   = new Gravity_Forms\Gravity_Forms\Settings\Settings(
				array(
					'capability'     => $this->_capabilities_form_settings,
					'fields'         => array(),
					'initial_values' => $feed['meta'],
				)
			);
			$this->set_settings_renderer( $renderer );
			$html = $this->settings_generic_map( $fake_field, false );

			//Remove Windows line breaks and whitespace
			$html = str_replace(
				"\n\t\t\t\t",
				'',
				$html
			);

			return $html;
		}

		// # SCRIPTS & STYLES -----------------------------------------------------------------------------------------------

		private function js_url( $file_name ) {
			return plugins_url( 'js/built/' . $file_name, dirname( __FILE__ ) . '/gp-google-sheets.php' );
		}

		private function assets_url( $file_name ) {
			return plugins_url( 'assets/' . $file_name, dirname( __FILE__ ) . '/gp-google-sheets.php' );
		}

		/**
		 * Return the scripts which should be enqueued.
		 *
		 * @return array
		 */
		public function scripts() {
			$form_id = ( ! empty( $_GET['id'] ) ? intval( $_GET['id'] ) : null );

			// only pass the license key if the user is an admin
			$license_info = $this->get_gp_license_info();

			//Do we have a Sheet URL? Check the feed settings
			$feed_id = $this->get_current_feed_id();
			$feed    = $this->get_feed( $feed_id );
			$user_id = get_current_user_id();

			if ( $this->tokens->get_global_token() ) {
				$global_token           = $this->tokens->get_global_token();
				$global_token_is_legacy = false;
			} elseif ( GP_Google_Sheets_Legacy_Tokens::get_global_token() ) {
				$global_token           = '1'; // Not actually used as this is for the picker and we'll hide the button if it's a legacy token.
				$global_token_is_legacy = true;
			} else {
				$global_token           = null;
				$global_token_is_legacy = false;
			}

			$error_message = null;
			$have_sheet    = $this->have_sheet();

			/** @var \Gravity_Forms\Gravity_Forms\Settings\Settings */
			$settings_renderer = $this->get_settings_renderer();

			if ( $have_sheet !== true && $settings_renderer !== false ) {
				$existing_field = $settings_renderer->get_field( 'google_sheet_url_field' );

				// not using rgar() as $existing_field is not of type array.
				if ( ! empty( $existing_field ) && ! empty( $existing_field['error'] ) ) {
					$error_message = $existing_field['error'];
				}

				if ( $this->get_setting( 'google_sheet_url' ) && ! $settings_renderer->is_save_postback() ) {
					$unable_to_load_spreadsheet = esc_html__( 'Unable to load spreadsheet from Google Sheets API.' );
					$how_to_fix_it              = esc_html__( 'How do I fix this error?' );
					$fix_instructions           = esc_html__( 'Disconnect this feed by clicking the “Disconnect” button below. Then, use the “Select Existing Sheet” option to reconnect your spreadsheet.' );

					$error_message = "<b>{$unable_to_load_spreadsheet}</b></br></br>";

					/*
					 * If we're unable to get the spreadsheet via the API but a URL to one is set, try to pass the error
					 * from Google into the UI if it's available.
					*/
					if ( is_wp_error( $have_sheet ) && self::is_json( $have_sheet->get_error_message() ) ) {
						$google_error = self::maybe_decode_json( $have_sheet->get_error_message() );
						$error_code   = rgars( $google_error, 'error/code' );
						$message      = rgars( $google_error, 'error/message' );

						if ( $error_code && $message ) {
							$error_message .= "<code class=\"gpgs_google_error_message\">Error {$error_code}: {$message}</code></br></br>";
						}
					}

					$error_message .= "<b class=\"gpgs_how_to_fix_header\">{$how_to_fix_it}</b>{$fix_instructions}";
				}
			}

			$scripts = array(
				// Plugin settings script
				array(
					'handle'  => self::SCRIPT_HANDLE . '_plugin',
					'src'     => $this->js_url( 'gp-google-sheets-settings-global.js' ),
					'version' => $this->_version,
					'deps'    => array( 'jquery' ),
					'strings' => array(
						'slug'                             => $this->_slug,
						'ajax_url'                         => admin_url( 'admin-ajax.php' ),
						'admin_url'                        => admin_url(),
						'nonce'                            => wp_create_nonce( self::NONCE_AJAX ),
						'oauth_validation_token'           => GFCommon::openssl_encrypt( (string) time() ),
						'gravity_perks_license_id'         => $license_info['id'],
						'gravity_perks_license_hash'       => $license_info['hash'],
						'gwiz_oauth_service_url'           => GP_Google_Sheets::GWIZ_OAUTH_SERVICE_URL,
						'site_url'                         => get_site_url(),
						'token'                            => $global_token,
						'token_is_legacy'                  => $global_token_is_legacy,
						'legacy_token'                     => __( 'Legacy Token', 'gp-google-sheets' ),
						'oauth_random_string'              => GP_Google_Sheets_Authenticator::random_string(),
						'connected_to_google'              => __( 'Connected to Google.', 'gp-google-sheets' ),
						'connect'                          => __( 'Connect', 'gp-google-sheets' ),
						'disconnect'                       => __( 'Disconnect', 'gp-google-sheets' ),
						'error_authenticating_with_google' => __( 'Error authenticating with Google:', 'gp-google-sheets' ),
						// translators: First placeholder is the plugin name, second is the error message
						'failed_to_connect_with_error_message' => __( '%1$s failed to connect to Google with error message: "%2$s". Check your Client ID and Client Secret then try again. If the issue persists, please <a href="https://gravitywiz.com/support/" target="_blank">contact support</a>.', 'gp-google-sheets' ),
						'connecting_requires_valid_license' => __( 'Connecting to Google Sheets requires a valid <a href="https://gravitywiz.com/pricing" target="_blank">Gravity Perks license</a>.', 'gp-google-sheets' ),
						'error_picking_sheet'              => __( 'Error picking spreadsheet.', 'gp-google-sheets' ),
						'gppa_section_error_fetching'      => __( 'There was an error fetching the spreadsheets that GPPA has access to. Double check that you have permissions to edit GP Google Sheets Settings and try again.' ),
						'token_health_error_fetching'      => __( 'There was an error fetching token health data. Double check that you have permissions to edit GP Google Sheets Settings and try again.' ),
						'no_connected_tokens'              => __( 'It looks like you don\'t have any connected tokens yet.' ),
						'gppa_section_no_global_token'     => __( 'It looks like you don\'t have a global token yet. Please connect to Google Sheets above to see which sheest GPPA has access to.' ),
						'user_id'                          => $user_id,
					),
					'enqueue' => array(
						//Forms → Settings → Google Sheets
						array(
							'query' => 'page=gf_settings&subview=' . $this->_slug,
						),
					),
				),

				// Feed settings edit script
				array(
					'handle'  => self::SCRIPT_HANDLE . '_feed_edit',
					'src'     => $this->js_url( 'gp-google-sheets-settings-feed-edit.js' ),
					'version' => $this->_version,
					'deps'    => array( 'jquery' ),
					'strings' => array(
						'feed_id'          => $this->get_current_feed_id(),
						'form_id'          => $form_id,
						'ajax_url'         => admin_url( 'admin-ajax.php' ),
						'nonce'            => wp_create_nonce( self::NONCE_AJAX ),
						'sheet_url'        => $this->get_sheet_url_from_feed( $feed ),
						'spreadsheet_name' => $this->get_spreadsheet_name_from_feed( $feed ),
						'sheet_name'       => $this->get_sheet_name_from_feed( $feed ),
						'disconnect'       => __( 'Disconnect', 'gp-google-sheets' ),
						'insert_test_row'  => __( 'Insert Test Row', 'gp-google-sheets' ),
						'error_message'    => $error_message,
					),
					'enqueue' => array(
						array(
							'query' => 'page=gf_edit_forms&view=settings&fid=_notempty_&id=_notempty_&subview=' . $this->_slug,
						),
					),
				),

				// Feed settings script
				array(
					'handle'  => self::SCRIPT_HANDLE,
					'src'     => $this->js_url( 'gp-google-sheets-settings-feed.js' ),
					'version' => $this->_version,
					'deps'    => array( 'jquery' ),
					'strings' => array(
						'slug'                             => $this->_slug,
						'feed_id'                          => $this->get_current_feed_id(),
						'form_id'                          => $form_id,
						'ajax_url'                         => admin_url( 'admin-ajax.php' ),
						'nonce'                            => wp_create_nonce( self::NONCE_AJAX ),
						'oauth_random_string'              => GP_Google_Sheets_Authenticator::random_string(),
						'oauth_validation_token'           => GFCommon::openssl_encrypt( (string) time() ),
						'credentials_empty'                => __( 'Obtain a Client ID and Client Secret from Google and save them in the corresponding boxes on this page before trying to use this feature. Alternatively, change Account to use the Default connection and try again.', 'gp-google-sheets' ),
						'gravity_perks_license_id'         => $license_info['id'],
						'gravity_perks_license_hash'       => $license_info['hash'],
						'gwiz_oauth_service_url'           => GP_Google_Sheets::GWIZ_OAUTH_SERVICE_URL,
						'add_new_column'                   => __( 'Add New Column', 'gp-google-sheets' ),
						'select_a_column'                  => __( 'Select a Column', 'gp-google-sheets' ),
						'site_url'                         => get_site_url(),
						'error_picking_spreadsheet'        => __( 'Error picking spreadsheet.', 'gp-google-sheets' ),
						'error_authenticating_with_google' => __( 'Error authenticating with Google:', 'gp-google-sheets' ),
						'token'                            => $this->tokens->get_token_by_user_id( get_current_user_id() ),
						'user_id'                          => $user_id,
					),
					'enqueue' => array(
						array(
							'query' => 'page=gf_edit_forms&view=settings&id=_notempty_&subview=' . $this->_slug,
						),
					),
				),
			);

			if (
				defined( 'GPPA_VERSION' )
				/** @phpstan-ignore-next-line (GPPA_VERSION can vary) */
				&& version_compare( GPPA_VERSION, '2.0.14', '>=' )
			) {
				$scripts[] = array(
					'handle'  => self::SCRIPT_HANDLE . '_form_editor',
					'src'     => $this->js_url( 'gp-google-sheets-settings-form-editor-gppa.js' ),
					'version' => $this->_version,
					'deps'    => array( 'jquery' ),
					'enqueue' => array(
						array( 'admin_page' => array( 'form_editor' ) ),
					),
				);
			}

			return array_merge( parent::scripts(), $scripts );
		}

		/**
		 * Return the stylesheets which should be enqueued.
		 *
		 * @return array
		 */
		public function styles() {
			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

			$styles = array(

				//Feed settings stylesheet
				array(
					'handle'  => self::SCRIPT_HANDLE,
					'src'     => $this->assets_url( "settings-feed{$min}.css" ),
					'version' => $this->_version,
					'enqueue' => array(
						array(
							'query' => 'page=gf_edit_forms&view=settings&id=_notempty_&subview=' . $this->_slug,
						),
					),
				),

				//Feed & plugin settings stylesheet
				array(
					'handle'  => self::SCRIPT_HANDLE . '_plugin',
					'src'     => $this->assets_url( "settings-global{$min}.css" ),
					'version' => $this->_version,
					'enqueue' => array(
						array(
							'query' => 'page=gf_edit_forms&view=settings&id=_notempty_&subview=' . $this->_slug,
						),
						array(
							'query' => 'page=gf_settings&subview=' . $this->_slug,
						),
					),
				),

				// Entry details
				array(
					'handle'  => self::SCRIPT_HANDLE . '_entry_details',
					'src'     => $this->assets_url( "entry-details{$min}.css" ),
					'version' => $this->_version,
					'enqueue' => array(
						array(
							'query' => 'page=gf_entries&view=entry',
						),
					),
				),
			);

			return array_merge( parent::styles(), $styles );
		}

		/**
		 * Helper method to get Gravity Perks license ID and hash for authenticating with the OAuth backend.
		 */
		public function get_gp_license_info() {
			$license_key = GWPerks::get_license_key();

			if ( ! $license_key ) {
				return array(
					'id'   => '',
					'hash' => '',
				);
			}

			$license = GravityPerks::get_license_data();

			return array(
				'id'   => isset( $license['ID'] ) ? $license['ID'] : '',
				'hash' => md5( $license_key ),
			);
		}

		// # ADMIN FUNCTIONS -----------------------------------------------------------------------------------------------

		/**
		 * Set the icon on our Form Settings tab
		 */
		public function form_settings_icon() {
			return '<i class="fa fa-table"></i>';
		}

		public function form_settings_title() {
			// translators: placeholder is form name
			return sprintf( esc_html__( '%s Feeds', 'gp-google-sheets' ), $this->get_title() );
		}

		public function get_menu_icon() {
			return file_get_contents( dirname( __FILE__ ) . '/assets/menu-icon.svg' );
		}

		/**
		 * This is a wrapper for Gravity Form's gform_tooltip() method that checks
		 * for the existence of that method before using it to avoid exceptions when
		 * gform_tooltip() may be called when Gravity Form is not loaded. Returns
		 * the tooltip name in brackets if the method is not defined.
		 *
		 * This is useful when gform_tooltip() may be called in an AJAX callback.
		 */
		function maybe_tooltip_html( $tooltip_name, $css_class = '', $return = false ) {
			if ( ! function_exists( 'gform_tooltip' ) ) {
				//Make shortcode like codes that we can replace in JavaScript
				//google_sheet_insert_test_row is the name of the tooltip
				//[tooltip_insert_test_row] is the code JS will look for
				return sprintf( '[%s] ', str_replace( 'google_sheet_', 'tooltip_', $tooltip_name ) );
			}
			return gform_tooltip( $tooltip_name, $css_class, $return ) . ' ';
		}

		/**
		 * plugin_settings_fields
		 *
		 * Provides the fields that appear on the Google Sheets tab of the
		 * Gravity Forms global settings (and not a specific form's settings).
		 *
		 * @return array
		 */
		public function plugin_settings_fields() {
			/**
			 * Filter whether to show the callout to the GP Populate Anything integration if Populate Anything is
			 * not installed.
			 *
			 * @param bool $show_gppa_integration Whether to show the callout. Default is `true`.
			 *
			 * @since 1.0-beta-2.0
			 */
			$show_gppa_integration = apply_filters( 'gpgs_show_gppa_integration', true );

			$sections = array(
				array(
					'fields' => array(
						//Connect button
						array(
							'label' => esc_html__( 'Google Account' ),
							'name'  => 'connect_button',
							'type'  => 'connect_button',
						),

						/**
						 * Token, put a hidden setting on the page so our OAuth
						 * token from Google doesn't get erased when the settings
						 * are saved.
						 */
						array(
							'label' => 'Token',
							'name'  => 'token',
							'type'  => 'hidden',
						),
					),
				),
				array(
					'title'  => __( 'Token Health', 'gp-google-sheets' ),
					'fields' => array(
						array(
							'name' => 'token_health_placeholder',
							'type' => 'token_health',
						),
					),
				),
				array(
					'title'  => __( 'Populate Anything Integration', 'gp-google-sheets' ),
					'fields' => array(
						array(
							'name' => 'gppa_spreadsheets',
							'type' => 'gppa_spreadsheets',
						),
					),
				),
			);

			if (
				apply_filters( 'gpgs_show_action_scheduler_purge', false )
				&& GFCommon::current_user_can_any( array( 'gp-google-sheets_uninstall' ) )
			) {
				$purge_url = add_query_arg( array(
					'gpgs_purge_action_scheduler' => wp_create_nonce( 'gpgs_purge_action_scheduler' ),
					'gpgs_purge_action_timestamp' => 'TIMESTAMP_PLACEHOLDER', // Used to prevent accidentally navigating and purging again
				) );

				$sections[] = array(
					'title'  => __( 'Action Scheduler', 'gp-google-sheets' ),
					'fields' => array(
						array(
							'name' => 'danger_zone',
							'type' => 'html',
							'html' => '<strong style="color: red;">' . __( 'Danger Zone! Make sure you know what you\'re doing here.', 'gp-google-sheets' ) . '</strong>',
						),
						array(
							'name' => 'action_scheduler_purge',
							'type' => 'html',
							'html' => '<a
								onclick="
									// Update gpgs_purge_action_timestamp in the URL with the current timestamp in seconds.
									this.href = this.href.replace( /TIMESTAMP_PLACEHOLDER/, Math.floor( Date.now() / 1000 ) );

									return confirm(\'' . esc_js( __( "Are you sure you want to purge all uncomplete GP Google Sheets actions?\n\nIf entries are not already in Google Sheets, you will need to reprocess the feeds to sync these entries.", 'gp-google-sheets' ) ) . '\');"
								href="' . $purge_url . '" class="button">' . __( 'Purge Action Scheduler', 'gp-google-sheets' ) . '</a>',
						),
					),
				);
			}

			return $sections;
		}

		/**
		 * Purges all pending, failed, in-progress, and past-due GP Google Sheets actions in Action Scheduler.
		 */
		public function maybe_purge_action_scheduler() {
			if (
				! apply_filters( 'gpgs_show_action_scheduler_purge', false )
				|| ! GFCommon::current_user_can_any( array( 'gp-google-sheets_uninstall' ) )
			) {
				return false;
			}

			$purge_nonce     = rgget( 'gpgs_purge_action_scheduler' );
			$purge_timestamp = rgget( 'gpgs_purge_action_timestamp' );

			if ( ! $purge_nonce || ! $purge_timestamp ) {
				return false;
			}

			if ( ! wp_verify_nonce( $purge_nonce, 'gpgs_purge_action_scheduler' ) ) {
				return false;
			}

			// Only allow the purge request timestamp to be processed within 10 seconds of the original request to
			// prevent accidental purging.
			if ( time() - (int) $purge_timestamp > 10 ) {
				wp_die(
					esc_html__( 'Action Scheduler purge link has expired. Please go back and click the "Purge Action Scheduler" button again.', 'gp-google-sheets' ),
					esc_html__( 'GP Google Sheets', 'gp-google-sheets' ),
					array(
						'response'  => 200,
						'back_link' => true,
					)
				);
			}

			/**
			 * Purge all uncomplete GP Google Sheets actions in Action Scheduler.
			 *
			 * We know they're GP Google Sheets actions because they have the "gp_google_sheets_" prefix in the hook.
			 */
			global $wpdb;

			$deleted_row_count = $wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->prefix}actionscheduler_actions WHERE hook LIKE %s AND status NOT IN (%s)",
					'gp_google_sheets_%',
					'complete'
				)
			);

			wp_die(
				esc_html__( 'GP Google Sheets actions have been successfully purged.', 'gp-google-sheets' )
				. '<br /><br />'
				// translators: %s is the number of rows deleted.
				. sprintf( _n( '%s action was deleted.', '%s actions were deleted.', $deleted_row_count, 'gp-google-sheets' ), number_format_i18n( $deleted_row_count ) ),
				esc_html__( 'GP Google Sheets', 'gp-google-sheets' ),
				array(
					'response'  => 200,
					'back_link' => true,
				)
			);
		}

		public function get_gppa_upsell_markup() {
			ob_start();
			$actions = array();
			if ( is_wp_error( validate_plugin( 'gp-populate-anything/gp-populate-anything.php' ) ) ) {
				if ( GWPerks::has_available_perks() ) {
					$actions[] = '<a class="button gpgs-manage-perk" style="margin-right: 0.5rem;" href="' . $this->get_plugin_action_url( 'install', 'gp-populate-anything/gp-populate-anything.php' ) . '">' . __( 'Install Populate Anything', 'gp-google-sheets' ) . '</a>';
				} else {
					$upgrade_url = add_query_arg( array(
						'utm_campaign' => 'gp-ui',
						'utm_medium'   => 'gpgs-settings',
						'utm_source'   => 'gpgs-gppa-upgrade',
					), GWPerks::get_license_upgrade_url() );

					$actions[] = '<a class="button gpgs-manage-perk" target="_blank" style="margin-right: 0.5rem;" href="' . $upgrade_url . '">' . __( 'Upgrade License', 'gp-google-sheets' ) . '</a>';
				}
			} else {
				$actions[] = '<a class="button gpgs-manage-perk" style="margin-right: 0.5rem;" href="' . $this->get_plugin_action_url( 'activate', 'gp-populate-anything/gp-populate-anything.php' ) . '">' . __( 'Activate Populate Anything', 'gp-google-sheets' ) . '</a>';
			}
			$actions[] = '<a class="button gpgs-manage-perk" style="margin-right: 0.5rem;" href="https://gravitywiz.com/documentation/gravity-forms-populate-anything/#google-sheets">' . __( 'Learn More', 'gp-google-sheets' ) . '</a>';
			?>
			<p style="margin-top: 0;"><strong><?php esc_html_e( 'Want to populate data from Google Sheets into your forms?', 'gp-google-sheets' ); ?></strong></p>
			<p>
				<?php
				esc_html_e( 'Populate Anything pulls data directly from Google Sheets into Gravity Forms field
				choices that users can select, or, as values — for calculations, conditional logic, or as defaults.
				Filter Google Sheets data live on your form based on conditions you set, or, dynamically with user input.
				Basically, your favorite Populate Anything features, but with data from Google Sheets.', 'gp-google-sheets' );
				?>
			</p>
			<div style="
				margin-top: 1rem;
				display: flex;
				align-items: center;
				">
				<?php echo implode( '', $actions ); ?>
			</div>
			<?php
			return ob_get_clean();
		}

		public function get_plugin_action_url( $action, $plugin_file ) {
			return esc_attr( add_query_arg( array(
				'gwp'  => false,
				'from' => $this->get_slug(),
			), htmlspecialchars_decode( $this->perk->get_link_for( $action, $plugin_file ) ) ) );
		}

		/**
		 * Add a link to the plugin installation page that will take the user back to the Google Sheets plugin settings
		 * page after installing Populate Anything from the Google Sheets settings page.
		 *
		 * @param $actions
		 * @param $api
		 * @param $plugin_file
		 *
		 * @return mixed
		 */
		public function add_back_to_plugin_settings_action( $actions, $api, $plugin_file ) {
			if ( rgget( 'from' ) !== $this->get_slug() || ! $plugin_file ) {
				return $actions;
			}

			unset( $actions['plugins_page'] );

			// translators: placeholder is the plugin short title
			$actions['manage_perks'] = '<a href="' . $this->get_plugin_settings_url() . '">' . sprintf( __( 'Back to %s Settings', 'gp-google-sheets' ), $this->get_short_title() ) . '</a>';

			if ( isset( $actions['activate_plugin'] ) ) {
				$actions['activate_plugin'] = sprintf(
					'<a class="button button-primary" href="%s" target="_parent">%s</a>',
					wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . urlencode( $plugin_file ) . '&amp;from=' . $this->get_slug(), 'activate-plugin_' . $plugin_file ),
					__( 'Activate Perk', 'gp-google-sheets' )
				);
			}

			return $actions;
		}

		/**
		 * Redirect back to the Google Sheets settings page after activating Populate Anything from the Google Sheets
		 * settings page.
		 *
		 * @param $location
		 *
		 * @return mixed|string
		 */
		public function redirect_to_plugin_settings( $location ) {
			if ( rgget( 'from' ) !== $this->get_slug() ) {
				return $location;
			}

			$parsed_url = parse_url( $location );
			parse_str( $parsed_url['query'], $query );
			if ( ! isset( $query['action'] ) ) {
				$location = $this->get_plugin_settings_url();
			}

			return $location;
		}

		/**
		 * Gets the sheets available to the global token.
		 *
		 * @return \GP_Google_Sheets\Dependencies\Google\Service\Drive\DriveFile[]
		 */
		public function get_available_spreadsheets() {
			$spreadsheets = array();

			try {
				$token = $this->tokens->get_global_token() ?? GP_Google_Sheets_Legacy_Tokens::get_global_token();

				$service = GP_Google_Sheets_Authenticator::create_drive_service( $token );

				/** @var \GP_Google_Sheets\Dependencies\Google\Service\Drive\Resource\Files $files_resource */
				$files_resource = $service->files;

				$files = $files_resource->listFiles(array(
					'q'        => 'trashed = false',
					'pageSize' => 500,
					'fields'   => 'files(contentHints/thumbnail,iconLink,id,name,size,thumbnailLink,webViewLink,mimeType,modifiedTime)',
				));
			} catch ( Exception $e ) {
				return $spreadsheets;
			}

			foreach ( $files as $file ) {
				$spreadsheets[ $file['id'] ] = $file;
			}

			return $spreadsheets;
		}

		public function settings_gppa_spreadsheets( $field, $echo = true ) {
			$output = '<span>Loading...</span>';

			if ( $echo ) {
				echo $output;
			}

			return $output;
		}

		/**
		 * Helper method to check if the user is using a legacy token.
		 */
		public function has_legacy_token() {
			return rgars( $this->get_plugin_settings(), 'token/access_token' ) && ! rgars( $this->get_plugin_settings(), 'token/gwiz_oauth' );
		}

		/**
		 * @since  1.0
		 *
		 * @param  array $feed  Feed object.
		 * @param  array $entry Entry object.
		 * @param  array $form  Form object.
		 */
		public function process_feed( $feed, $entry, $form ) {
			GP_Google_Sheets_Retry::enqueue_async_action(
				'gp_google_sheets_add_entry_to_sheet',
				array(
					'feed_id'  => $feed['id'],
					'entry_id' => $entry['id'],
					'form_id'  => $form['id'],
				),
				$entry['id']
			);

			return null;
		}

		/**
		 * @since  1.0
		 *
		 * @param  int $feed_id  Feed ID.
		 * @param  int $entry_id Entry ID.
		 * @param  int $form_id  Form ID.
		 *
		 * @throws \Exception Expected to be handled by Action Scheduler.
		 */
		public function add_entry_to_sheet( $feed_id, $entry_id, $form_id, $attempts_made = 0 ) {
			$feed          = $this->get_feed( $feed_id );
			$entry         = GFAPI::get_entry( $entry_id );
			$form          = GFAPI::get_form( $form_id );
			$attempts_made = $attempts_made + 1;

			$token = $this->tokens->get_feed_access_token( $feed );

			if ( ! $token ) {
				$token = GP_Google_Sheets_Legacy_Tokens::get_feed_access_token( $feed );
			}

			if ( ! $token ) {
				$this->add_feed_error(
					__( 'Missing token. Ensure that the plugin is properly authenticated and the feed is properly configured.', 'gp-google-sheets' ),
					$feed,
					$entry,
					$form
				);

				return;
			}

			//Has the entry been marked spam?
			$is_spam = $entry['status'] === 'spam';

			if ( $is_spam ) {
				//Yes, abort
				$this->log_debug( __METHOD__ . '(): ' . __( 'Entry marked as spam', 'gp-google-sheets' ) );
				return;
			}

			try {
				// Filter hook purely for testing that retry logic works as intended.
				if ( apply_filters( 'gpgs_testing_should_fail_action', false, __METHOD__, $attempts_made, $feed, $entry ) ) {
					throw new Exception( 'Testing failure of action.' );
				}

				/*
				 * Check if a row for the entry already exists in the Sheet. If so, bail out and rely on the user enabling
				 * "Update & Delete Rows" in the feed settings.
				 */
				if ( gform_get_meta( $entry['id'], "{$this->_slug}_{$feed['id']}_inserted" ) ) {
					$service        = GP_Google_Sheets_Authenticator::create_service( $feed );
					$spreadsheet_id = GP_Google_Sheets_Writer::spreadsheet_id( $feed );

					$row_index = GP_Google_Sheets_Reader::find_row_by_metadata_value( $service, $spreadsheet_id, $feed['form_id'], $entry['id'] );

					if ( $row_index !== false ) {
						/**
						 * We found an existing row for this entry in the Sheet. Write to the debug log and create a feed
						 * error note.
						 */
						$message = sprintf(
							'Row for Entry %s in Sheet %s already exists. Enable "Update & Delete Rows" in the feed settings to overwrite existing rows.',
							$entry['id'],
							$spreadsheet_id
						);

						$this->log_debug( __METHOD__ . '(): ' . $message );
						$this->add_feed_error( $message, $feed, $entry, $form );

						return;
					}
				}

				$row_data = $this->create_row_data( $feed, $form['id'], array( $entry ), true );
				// result will be, either, a string (range that rows were written to) or an exception.
				$result = GP_Google_Sheets_Writer::append_rows( $feed, $row_data );

				$inserted = gform_get_meta( $entry['id'], "{$this->_slug}_{$feed['id']}_inserted" );

				// Set inserted time only when feed is processed the first time. Set updated time when feed is processed again.
				if ( ! $inserted ) {
					gform_update_meta( $entry['id'], "{$this->_slug}_{$feed['id']}_inserted", time() );
				} else {
					gform_update_meta( $entry['id'], "{$this->_slug}_{$feed['id']}_updated", time() );
				}

				/**
				 * Adding the entry ID to the row metadata was previously handled with Action Scheduler. However, this
				 * caused a massive job pileup and was generally inefficient.
				 *
				 * The one tradeoff is this is one more failure point during the write job, but it's a tradeoff we're
				 * willing to make after issues with the previous approach.
				 */
				GP_Google_Sheets_Writer::add_entry_id_to_row_metadata( $feed, $row_data, $result );
			} catch ( \Exception $ex ) {
				/*
				 * If the error contains "createDeveloperMetadata", we can ignore the error as there isn't anything we
				 * can do in terms of retrying due to the quota of developer metadata being hit.
				 *
				 * The solutions here include the user needing to create a new spreadsheet from scratch or we need to
				 * look into moving away from developer metadata in favor of a hidden column.
				 *
				 * https://developers.google.com/sheets/api/guides/metadata#metadata_storage_limits
				 */
				if ( strpos( $ex->getMessage(), 'createDeveloperMetadata' ) !== false ) {
					$message = 'Developer metadata quota has been hit. This impacts the ability for entry edits/deletions to sync to Google Sheets if enabled. It is recommended that you connect this feed to a new spreadsheet.' . "\n\n" . $ex->getMessage();
					$this->log_debug( __METHOD__ . '(): ' . $message );
					$this->add_feed_error( $message, $feed, $entry, $form );
					return;
				}

				if ( $attempts_made > GP_Google_Sheets_Retry::MAX_RETRY_ATTEMPTS ) {
					$message = 'Could not add entry to Google Sheets and maximum retries have already been attempted.' . "\n\n" . $ex->getMessage();
					$this->log_debug( __METHOD__ . '(): ' . $message );
					$this->add_feed_error( $message, $feed, $entry, GFAPI::get_form( $entry['form_id'] ) );
					return;
				}

				$message = 'Cannot connect to Google.' . "\n\n" . $ex->getMessage();
				$this->log_debug( __METHOD__ . '(): ' . $message );
				$this->add_feed_error( $message, $feed, $entry, $form );

				GP_Google_Sheets_Retry::schedule_single_action(
					'gp_google_sheets_add_entry_to_sheet',
					array(
						'feed_id'       => $feed_id,
						'entry_id'      => $entry_id,
						'form_id'       => $form_id,
						'attempts_made' => $attempts_made,
					),
					$entry_id,
					5, // Run before other actions so things like edit/delete can happen in the same batch.
					$attempts_made
				);

				// Re-throw the exception for Action Scheduler to catch, log, and mark the action as failed.
				throw $ex;
			}
		}

		/**
		 * Setup fields for feed settings.
		 *
		 * @since 1.0
		 *
		 * @return array
		 */
		public function feed_settings_fields() {
			$have_sheet = $this->have_sheet();

			$fields = array(
				array(
					'fields' => array(

						//Name
						array(
							'name'          => 'feed_name',
							'label'         => esc_html__( 'Name', 'gp-google-sheets' ),
							'type'          => 'text',
							'class'         => 'medium',
							'required'      => true,
							'default_value' => $this->get_default_feed_name(),
							'tooltip'       => '<h6>' . esc_html__( 'Name', 'gp-google-sheets' ) . '</h6>' . esc_html__( 'Enter a feed name to uniquely identify this feed. If creating a new sheet, this will be used as the sheet\'s name.', 'gp-google-sheets' ),
						),
					),
				),
				array(
					'title'  => esc_html__( 'Google Sheets Settings', 'gp-google-sheets' ),
					'fields' => array(

						/**
						 * Google Spreadsheet URL, a hidden setting so this value
						 * doesn't get erased when settings are saved.
						 *
						 * NOTE: even though this is named "sheet_url", it's still
						 * just the url to the spreadsheet and not necessarily
						 * a direct link to the connected sheet itself.
						 */
						array(
							'label' => __( 'Google Sheet URL', 'gp-google-sheets' ),
							'name'  => 'google_sheet_url',
							'type'  => 'hidden',
						),

						/**
						 * Google Sheet ID, a hidden setting so this value
						 * doesn't get erased when settings are saved.
						 */
						array(
							'label' => __( 'Google Sheet ID', 'gp-google-sheets' ),
							'name'  => 'google_sheet_id',
							'type'  => 'hidden',
						),

						/**
						 * Sheet was picked, a hidden setting to let us remember
						 * if the sheet was picked. Because the other token must
						 * be used to read and edit the sheet when it's picked.
						 */
						array(
							'label' => __( 'Sheet Was Picked', 'gp-google-sheets' ),
							'name'  => 'sheet_was_picked',
							'type'  => 'hidden',
						),

						array(
							'label' => __( 'Google Picker Token', 'gp-google-sheets' ),
							'name'  => 'picked_token',
							'type'  => 'hidden',
						),
					),
				),

				// Column Mapping
				// @todo if the sheet gets disconnected, try to preserve values
				array(
					'title'       => esc_html__( 'Column Mapping', 'gp-google-sheets' ),
					'description' => esc_html__( 'Specify which entry data should populate which columns in your Google Sheet.' ),
					'fields'      => array(
						array(
							'type'      => 'generic_map',
							'name'      => 'column_mapping',
							'key_field' => array(
								'title'       => __( 'Sheet Column', 'gp-google-sheets' ),
								'placeholder' => __( 'Column heading', 'gp-google-sheets' ),
								'choices'     => $this->field_map_key_field_choices(),
							),
						),
					),
				),

				//Editing Settings
				array(
					'title'  => esc_html__( 'Additional Options', 'gp-google-sheets' ),
					'fields' => array(
						/**
						 * Shelved this feature after the field map was added.
						 * Field names are no longer used as default column
						 * headings, the user is always typing them into the
						 * field map. Form updates triggering column heading
						 * changes would be the first time the field names are
						 * used as headings at all.
						 */
						// array(
						// 	'label'   => esc_html__( 'Update Column Headings', 'gp-google-sheets' ),
						// 	'type'    => 'checkbox',
						// 	'name'    => 'edit_columns',
						// 	'tooltip' => esc_html__( 'When a field\'s admin label or name is changed, this plugin also edits the corresponding column heading in the Google Sheet.', 'gp-google-sheets' ),
						// 	'choices' => array(
						// 		array(
						// 			'label' => esc_html__( 'Update column headings when admin labels or field names are edited', 'gp-google-sheets' ),
						// 			'name'  => 'edit_columns'
						// 		),
						// 	),
						// 	'default_value' => '1',
						// ),
						array(
							'label'   => esc_html__( 'Update &amp; Delete Rows', 'gp-google-sheets' ),
							'type'    => 'checkbox',
							'name'    => 'edit_rows',
							'tooltip' => esc_html__( 'When entries are edited, also edit the corresponding row in the Google Sheet. Delete rows from the Sheet when entries are moved to trash or marked as spam. Entries are re-appended to the bottom of the Sheet after being restored from trash or spam.', 'gp-google-sheets' ),
							'choices' => array(
								array(
									'label' => esc_html__( 'Edit rows when entries are edited, and delete rows when entries are trashed or marked as spam', 'gp-google-sheets' ),
									'name'  => 'edit_rows',
								),
							),
						),
					),
				),

				//Conditional Logic
				array(
					'title'  => esc_html__( 'Conditional Logic', 'gp-google-sheets' ),
					'fields' => array(
						array(
							'label' => '',
							'name'  => 'conditional_logic',
							'type'  => 'feed_condition',
						),
					),
				),
			);

			//debug
			if ( defined( 'GP_GOOGLE_SHEETS_DEBUG' ) && GP_GOOGLE_SHEETS_DEBUG && ! is_wp_error( $have_sheet ) ) {
				$fields[] = array(
					'title'  => 'Developer Metadata (debug)',
					'fields' => array(
						array(
							'label' => 'Column Metadata',
							'type'  => 'metadata_output_column',
							'name'  => 'debug_metadata_column',
						),
						array(
							'label' => 'Row Metadata',
							'type'  => 'metadata_output_row',
							'name'  => 'debug_metadata_row',
						),
					),
				);
			}
			//debug

			/**
			 * Conditional fields in the Google Sheets Settings section
			 */

			/** @var \Gravity_Forms\Gravity_Forms\Settings\Settings */
			$settings_renderer = $this->get_settings_renderer();

			//Google Sheet URL
			if ( $have_sheet !== true ) {
				$select_existing_sheet_label = esc_html__( 'Select Existing Sheet', 'gp-google-sheets' );

				if ( ! GravityPerks::get_license_key() ) {
					$select_existing_sheet_label = esc_html__( 'Select Existing Sheet (enter Gravity Perks license under Forms &raquo; Perks)', 'gp-google-sheets' );
				}

				//Select / Create radio buttons
				$fields[1]['fields'] = array_merge( array(

					//Google Sheet
					array(
						'label'               => esc_html__( 'Spreadsheet', 'gp-google-sheets' ),
						'type'                => 'radio',
						'name'                => 'google_sheet_url_field',
						'tooltip'             => esc_html__( 'The Google Sheet to be populated with entries. You may create a new sheet or select an existing sheet.', 'gp-google-sheets' ),
						'horizontal'          => true,
						'choices'             => array(
							array(
								'label' => esc_html__( 'Create New Sheet', 'gp-google-sheets' ),
								'value' => 'create',
							),
							array(
								'label'    => $select_existing_sheet_label,
								'value'    => 'select',
								'disabled' => ! GravityPerks::get_license_key(),
							),
						),
						'validation_callback' => function( $field, $value ) {
							if ( empty( rgpost( '_gform_setting_google_sheet_url' ) ) ) {
								if ( empty( $value ) ) {
									$field->set_error( __( 'Please create a new sheet or select an existing sheet.', 'gp-google-sheets' ) );
								} elseif ( $value == 'create' ) {
									//create sheet
									try {
										$spreadsheet_url = $this->create_sheet( rgpost( '_gform_setting_feed_name' ) );
										if ( is_string( $spreadsheet_url ) ) {
											$_POST['_gform_setting_google_sheet_url'] = $spreadsheet_url;
										}
									} catch ( \Exception $e ) {
										// translators: %s is an error message.
										$field->set_error( sprintf( __( 'Unable to create sheet. Error: %s', 'gp-google-sheets' ), $e->getMessage() ) );
									}
								}
							}
						},
					),

					array(
						'label'   => esc_html__( 'Sheet', 'gp-google-sheets' ),
						'type'    => 'select',
						'name'    => 'google_sheet_id_selector',
						'tooltip' => esc_html__( 'Select the specific sub-sheet you would like to store Gravity Forms entry in.', 'gp-google-sheets' ),
						'choices' => array(
							// default choice
							array(
								'label' => '- Select a Sheet -',
								'value' => '',
							),
						),
					),

				), $fields[1]['fields'] );
			} else {
				//Sheet name link + Insert test row button
				$fields[1]['fields'] = array_merge( array(

					//Google Sheet
					array(
						'label'   => esc_html__( 'Spreadsheet', 'gp-google-sheets' ),
						'type'    => 'google_sheet_url_field',
						'name'    => 'google_sheet_url_field',
						'tooltip' => esc_html__( 'The full web address of the Google Sheet to be populated with entries. This URL will be populated for you when the Sheet is created.', 'gp-google-sheets' ),
					),
				), $fields[1]['fields'] );
			}

			return $fields;
		}

		/**
		 * field_map_is_empty
		 *
		 * @param array $field_map_meta The meta data array that defines a GF feed. ex $feed['meta']
		 *
		 * @return bool
		 */
		protected function field_map_is_empty( $field_map_meta ) {
			return empty( $field_map_meta )
				|| ( sizeof( $field_map_meta ) == 1
				&& ( ( empty( $field_map_meta[0]['value'] )
				&& empty( $field_map_meta[0]['custom_value'] ) ) ) );
		}

		protected function field_map_key_field_choices( $first_row_data = array() ) {
			if ( empty( $first_row_data ) ) {
				$first_row_data = $this->get_first_row();
			}

			if ( is_wp_error( $first_row_data ) || empty( $first_row_data[0] ) ) {
				return array();
			}

			$choices = array();
			for ( $c = 0; $c < sizeof( $first_row_data[0] ); $c++ ) {
				$letters   = GP_Google_Sheets_Writer::number_to_column_letters( $c + 1 );
				$choices[] = array(
					'label' => sprintf(
						'%s. %s',
						$letters,
						$first_row_data[0][ $c ]
					),
					'value' => $letters,
				);
			}

			return $choices;
		}

		/**
		 * Duplicated from GFFeedAddOn::get_feed_settings_field(), but without the check for $this->_feed_settings_fields
		 * as its private and cannot be unset().
		 *
		 * @return mixed
		 */
		public function get_feed_settings_fields_no_cache() {
			/**
			 * Filter the feed settings fields (typically before they are rendered on the Feed Settings edit view).
			 *
			 * @param array $feed_settings_fields An array of feed settings fields which will be displayed on the Feed Settings edit view.
			 * @param object $addon The current instance of the GFAddon object (i.e. GF_User_Registration, GFPayPal).
			 *
			 * @since 2.0
			 *
			 * @return array
			 */
			$feed_settings_fields = apply_filters( 'gform_addon_feed_settings_fields', $this->feed_settings_fields(), $this );
			$feed_settings_fields = apply_filters( "gform_{$this->_slug}_feed_settings_fields", $feed_settings_fields, $this );

			/** @phpstan-ignore-next-line */
			$this->_feed_settings_fields = $this->add_default_feed_settings_fields_props( $feed_settings_fields );

			return $this->_feed_settings_fields;
		}

		public function feed_settings_init() {
			//Look at the sheet, make sure our field map is still accurate, do not run this if saving.
			if ( rgempty( 'gform-settings-save' ) ) {
				$this->maybe_update_field_map_setting();
			}

			parent::feed_settings_init();

			/*
			 * Set fields again after saving has been processed as the save postback happens AFTER the
			 * settings are rendered by default, which causes have some values to not be ready when performing conditionals.
			 */
			/** @var \Gravity_Forms\Gravity_Forms\Settings\Settings */
			$settings_renderer = $this->get_settings_renderer();

			if ( $settings_renderer->is_save_postback() ) {
				// Get current form.
				$form = ( $this->get_current_form() ) ? $this->get_current_form() : array();
				$form = gf_apply_filters( array( 'gform_admin_pre_render', rgar( $form, 'id', 0 ) ), $form );

				// Get current feed ID, feed object.
				$feed_id      = $this->_multiple_feeds ? $this->get_current_feed_id() : $this->get_default_feed_id( rgar( $form, 'id', 0 ) );
				$current_feed = $feed_id ? $this->get_feed( $feed_id ) : array();

				// Refresh initial values in the settings otherwise custom columns in the field map may not properly change to a select.
				$settings_renderer->set_values( rgar( $current_feed, 'meta' ) );

				$sections = $this->get_feed_settings_fields_no_cache();
				$sections = $this->prepare_settings_sections( $sections, 'feed_settings' );
				$settings_renderer->set_fields( $sections );
			}
		}

		public function feed_list_message() {
			//Do we have global credentials and a token?
			if ( ! $this->has_global_token() ) {
				return sprintf(
					'%s <a href="%s">%s %s</a>',
					__( 'You must authenticate a Google Account before creating a feed.', 'gp-google-sheets' ),
					admin_url( '/admin.php?page=gf_settings&subview=' . $this->get_addon_slug() ),
					__( 'Visit Forms → Settings → ', 'gp-google-sheets' ),
					$this->_short_title
				);
			}

			return GFFeedAddOn::feed_list_message();
		}

		protected function fill_missing_array_keys( $arr, $starting_key = 0, $fill_value = '' ) {
			//Fill missing keys in the field map array, those are user columns
			for ( $c = $starting_key; $c <= max( array_keys( $arr ) ); $c++ ) {
				if ( ! isset( $arr[ $c ] ) ) {
					$arr[ $c ] = $fill_value;
				}
			}
			//Sort by key to put the values we just set in the correct order
			ksort( $arr );
			return $arr;
		}

		/**
		 * flatten_column_mapping
		 *
		 * Takes the setting value of $feed['meta']['column_mapping'] as GF
		 * stores it and returns a one-dimensional array of strings where keys
		 * are keys and values are values. Not useful before field map changes
		 * are saved because 'gf_custom' is the key and this method does not
		 * know where that column will be added in the sheet.
		 *
		 * @param  array $meta_column_mapping
		 * @return array
		 */
		protected function flatten_column_mapping( $meta_column_mapping ) {
			if ( empty( $meta_column_mapping ) || $this->field_map_is_empty( $meta_column_mapping ) ) {
				return array();
			}

			$letter_keyed_map     = array();
			$highest_column_index = 0;
			foreach ( $meta_column_mapping as $column ) {
				//Ack, this column isn't mapped yet
				if ( $column['key'] == 'gf_custom' ) {
					$column['key'] = '?';
					continue;
				}

				$letters                      = $column['key'];
				$letter_keyed_map[ $letters ] = $column['custom_value'] ? 'gf_custom:' . $column['custom_value'] : $column['value'];
				$column_index                 = GP_Google_Sheets_Range_Parser::letters_to_index( $letters );
				$highest_column_index         = max( $highest_column_index, $column_index );
			}

			$map = array();
			for ( $i = 0; $i <= $highest_column_index; $i++ ) {
				$letters   = GP_Google_Sheets_Writer::number_to_column_letters( $i + 1 );
				$map[ $i ] = empty( $letter_keyed_map[ $letters ] ) ? '' : $letter_keyed_map[ $letters ];
			}

			return $map;
		}

		/**
		 * get_first_row
		 *
		 * @return array|WP_Error
		 */
		protected function get_first_row( $feed = null, $service = null ) {
			//Do we have a Sheet URL? Check the feed settings
			if ( $feed === null ) {
				$feed = $this->get_feed( $this->get_current_feed_id() );
			}

			$spreadsheet_id = GP_Google_Sheets_Writer::spreadsheet_id( $feed );

			if ( empty( $spreadsheet_id ) ) {
				return array(); //??
			}

			//Get the column headers & field IDs from developer metadata
			if ( $service === null ) {
				$service = GP_Google_Sheets_Authenticator::create_service( $feed );
			}

			if ( is_wp_error( $service ) ) {
				//We can't connect to Google
				$this->log_debug( __METHOD__ . '(): Cannot connect to Google. ' . $service->get_error_message() );
				return $service;
			}

			$tab   = GP_Google_Sheets_Reader::find_tab( $service, $spreadsheet_id, $feed );
			$range = '1:1';

			if ( $tab !== false && ! $tab instanceof \Exception ) {
				$range = "{$tab->tab_name}!{$range}";
			}

			//How many columns are in the Sheet? Read the first row.
			$range = GP_Google_Sheets_Reader::prepare_range( $service, $spreadsheet_id, $range, $feed );
			return GP_Google_Sheets_Reader::read_range( $service, $spreadsheet_id, $range );
		}

		/**
		 * @return bool|WP_Error
		 */
		protected function have_sheet() {
			$feed_id = $this->get_current_feed_id();

			// Do not validate if creating a new feed
			if ( ! $feed_id && rgpost( '_gform_setting_google_sheet_url' ) ) {
				return true;
			}

			// Do not validate if saving feed settings and "Create new spreaedsheet" is selected
			if (
				rgpost( 'gform-settings-save' ) === 'save'
				&& ! empty( rgpost( '_gform_setting_google_sheet_url' ) )
			) {
				return true;
			}

			if ( $feed_id ) {
				//feed settings
				$feed = $this->get_feed( $feed_id );

				$spreadsheet_id = GP_Google_Sheets_Writer::spreadsheet_id( $feed );
				if ( ! $spreadsheet_id ) {
					return false;
				}

				$service      = GP_Google_Sheets_Authenticator::create_service( $feed );
				$spreadsheets = GP_Google_Sheets_Authenticator::get_spreadsheets_resource( $service );

				try {
					// Result is unused, we're listening for an exception.
					$spreadsheets->get( $spreadsheet_id );

					return true;
				} catch ( \Exception $e ) {
					return new \WP_Error( $e->getCode(), $e->getMessage() );
				}
			}

			return false;
		}

		protected function have_token_feed() {
			$feed_id = $this->get_current_feed_id();

			if ( $feed_id ) {
				//feed settings
				$feed = $this->get_feed( $feed_id );
				return ! empty( $feed['meta']['token'] );
			}

			return false;
		}

		/**
		 * maybe_update_field_map_setting
		 *
		 * Compare the Sheet field map with the feed settings field map. The
		 * user could have edited the sheet columns & our map needs updated.
		 *
		 * @param string  $feed_id  The ID of the feed which was saved.
		 * @param int     $form_id  The current form ID associated with the feed.
		 * @param array   $settings An array containing the settings and mappings for the feed.
		 *
		 * @return array Updated feed
		 */
		public function maybe_update_field_map_setting( $feed_id = null, $form_id = null, $settings = null ) {
			$feed           = $this->get_feed( isset( $feed_id ) ? $feed_id : $this->get_current_feed_id() );
			$spreadsheet_id = GP_Google_Sheets_Writer::spreadsheet_id( $feed );

			if ( ! $feed ) {
				return $feed;
			}

			if ( empty( $spreadsheet_id ) ) {
				// If the spreadsheet isn't found, revert any columns with a custom key back to gf_custom after disconnecting.
				if ( ! empty( $feed['meta']['column_mapping'] ) && is_array( $feed['meta']['column_mapping'] ) ) {
					foreach ( $feed['meta']['column_mapping'] as &$mapping ) {
						if ( ! rgblank( $mapping['custom_key'] ) ) {
							$mapping['key'] = 'gf_custom';
						}
					}
				}

				$this->update_feed_meta( $feed['id'], $feed['meta'] );

				return $feed;
			}
			//Get the column headers & field IDs from developer metadata
			$service = GP_Google_Sheets_Authenticator::create_service( $feed );
			if ( is_wp_error( $service ) ) {
				//We can't connect to Google
				$this->log_debug( __METHOD__ . '(): Cannot connect to Google. ' . $service->get_error_message() );
				return $feed;
			}

			try {
				$field_map_sheet = GP_Google_Sheets_Reader::metadata_field_map( $service, $spreadsheet_id, $feed['id'] );
			} catch ( \Exception $e ) {
				$field_map_sheet = false;
			}

			if ( empty( $field_map_sheet ) ) {
				return $feed;
			}

			if ( is_array( $field_map_sheet ) ) {
				$field_map_sheet = $this->fill_missing_array_keys( $field_map_sheet );
			}

			if ( ! is_array( rgars( $feed, 'meta/column_mapping' ) ) ) {
				return $feed;
			}

			$field_map_settings = $this->flatten_column_mapping( isset( $feed['meta']['column_mapping'] ) ? $feed['meta']['column_mapping'] : array() );
			if ( $field_map_sheet != $field_map_settings
				|| sizeof( isset( $feed['meta']['column_mapping'] ) ? $feed['meta']['column_mapping'] : array() ) != sizeof( array_filter( $field_map_sheet ) ) ) {
				//Something has changed, update column_mapping before displaying
				$column_mapping = array();
				foreach ( $field_map_sheet as $index => $field_id ) {
					if ( empty( $field_id ) ) {
						continue;
					}

					//Maybe this column was moved
					$settings_index = array_search( $field_id, $field_map_settings );

					if ( strpos( $field_id, 'gf_custom:' ) === 0 ) {
						$column_mapping[] = array(
							'key'          => GP_Google_Sheets_Writer::number_to_column_letters( $index + 1 ),
							'custom_key'   => '',
							'value'        => 'gf_custom',
							'custom_value' => str_replace( 'gf_custom:', '', $field_map_settings[ $settings_index ] ),
						);
					} else {
						$column_mapping[] = array(
							'key'          => GP_Google_Sheets_Writer::number_to_column_letters( $index + 1 ),
							'custom_key'   => '',
							'value'        => $field_id,
							'custom_value' => '',
						);
					}
				}

				if ( $feed['meta']['column_mapping'] != $column_mapping ) {
					$feed['meta']['column_mapping'] = $column_mapping;
					$this->update_feed_meta( $feed['id'], $feed['meta'] );
				}
			}

			return $feed;
		}

		public function plugin_settings_connect_button_markup() {
			// return an empty string as this is handled client side
			return '';
		}

		public function settings_connect_button( $field, $echo = true ) {
			// return an empty string as this is handled client side
			return '';
		}

		public function settings_token_health( $field, $echo = true ) {
			$output = '<span>Loading...</span>';

			if ( $echo ) {
				echo $output;
			}

			return $output;
		}

		public function settings_metadata_output_column( $field, $echo = true ) {
			$feed_id = 0;
			$html    = '';

			/**
			 * We use this method during an AJAX callback when Gravity Forms is not
			 * loaded and the gform_tooltip() method might not be defined.
			 */
			if ( ! empty( $_GET['fid'] ) ) { //building the settings page
				$feed_id = intval( $_GET['fid'] );
			} elseif ( ! empty( $field['feed_id'] ) ) { //AJAX callback when creating the sheet
				$feed_id = $field['feed_id'];
			}

			//Do we have a Sheet URL? Check the feed settings
			$feed = $this->get_feed( $feed_id );
			if ( ! empty( $feed['meta']['google_sheet_url'] ) ) {
				$service = GP_Google_Sheets_Authenticator::create_service( $feed );
				if ( ! is_wp_error( $service ) ) {
					$spreadsheet_id = GP_Google_Sheets_Writer::spreadsheet_id( $feed );

					try {
						$map = GP_Google_Sheets_Reader::metadata_field_map( $service, $spreadsheet_id, $feed_id );
					} catch ( \Exception $e ) {
						$map = array();
					}

					$html .= '<pre>' . print_r( $map, true ) . '</pre>';
				}
			}

			if ( $echo ) {
				echo $html;

				return;
			}

			return $html;
		}

		public function settings_metadata_output_row( $field, $echo = true ) {
			$feed_id = 0;
			$html    = '';

			/**
			 * We use this method during an AJAX callback when Gravity Forms is not
			 * loaded and the gform_tooltip() method might not be defined.
			 */
			if ( ! empty( $_GET['fid'] ) ) { //building the settings page
				$feed_id = intval( $_GET['fid'] );
			}

			//Do we have a Sheet URL? Check the feed settings
			$feed = $this->get_feed( $feed_id );
			if ( ! empty( $feed['meta']['google_sheet_url'] ) ) {
				$service = GP_Google_Sheets_Authenticator::create_service( $feed );
				if ( ! is_wp_error( $service ) ) {
					$spreadsheet_id = GP_Google_Sheets_Writer::spreadsheet_id( $feed );

					try {
						$map = GP_Google_Sheets_Reader::metadata_map_rows( $service, $spreadsheet_id, $feed['form_id'] );
					} catch ( \Exception $e ) {
						$map = array();
					}

					$html .= '<pre>' . print_r( $map, true ) . '</pre>';
				}
			}

			if ( $echo ) {
				echo $html;

				return;
			}

			return $html;
		}

		/**
		 * google_sheet_url_field
		 *
		 * Create the markup for the google_sheet_url_field type field, and return
		 * or output it.
		 *
		 * @param  array $field The field properties.
		 * @param  bool $echo Should the setting markup be output?
		 * @return string|void Returns a string if $echo is false. Returns void if $echo is true.
		 */
		public function settings_google_sheet_url_field( $field, $echo = true ) {
			// Return an empty string as this will be handled client side.
			$html = '';

			if ( $echo ) {
				echo $html;
				return;
			}

			return $html;
		}

		protected function map_contains_custom_key( $column_mapping ) {
			return is_array( $column_mapping ) && in_array( 'gf_custom', array_column( $column_mapping, 'key' ) );
		}

		/**
		 * update_sheet_after_field_map_change
		 *
		 * @param string  $feed_id  The ID of the feed which was saved.
		 * @param int     $form_id  The current form ID associated with the feed.
		 * @param array   $settings An array containing the settings and mappings for the feed.
		 * @param GFAddOn $addon    The current instance of the GFAddOn object which extends GFFeedAddOn or GFPaymentAddOn (i.e. GFCoupons, GF_User_Registration, GFStripe).
		 * @return void
		 */
		public function update_sheet_after_field_map_change( $feed_id, $form_id, $settings, $addon ) {
			if ( empty( $settings['google_sheet_url'] ) ) {
				// Persist the sheet_url and sheet_id if the user just created a new sheet
				$post_url = rgpost( '_gform_setting_google_sheet_url' );
				if ( ! empty( $post_url ) && rgpost( '_gform_setting_google_sheet_url_field' ) == 'create' ) {
					$settings['google_sheet_url'] = $post_url;
					$settings['google_sheet_id']  = '0';

					$spreadsheet_id = GP_Google_Sheets_Writer::spreadsheet_id(
						array( 'meta' => array( 'google_sheet_url' => $post_url ) )
					);

					$this->tokens->set_spreadsheet_with_global_token( $spreadsheet_id, $feed_id );
				} else {
					return;
				}
			}

			//Get the map out of the sheet
			$feed         = $this->get_feed( $feed_id );
			$service      = GP_Google_Sheets_Authenticator::create_service( $feed );
			$spreadsheets = GP_Google_Sheets_Authenticator::get_spreadsheets_resource( $service );

			if ( is_wp_error( $service ) ) {
				//We can't connect to Google
				$this->log_debug( __METHOD__ . '(): Cannot connect to Google. ' . $service->get_error_message() );
				return;
			}
			$spreadsheet_id = GP_Google_Sheets_Writer::spreadsheet_id( $feed );

			try {
				$metadata_field_map = GP_Google_Sheets_Reader::metadata_field_map( $service, $spreadsheet_id, $feed_id );
			} catch ( \Exception $e ) {
				$metadata_field_map = array();
			}

			$field_map_sheet = $metadata_field_map;

			if ( is_array( $field_map_sheet ) && ! empty( $field_map_sheet ) ) {
				$field_map_sheet = $this->fill_missing_array_keys( $field_map_sheet );
			}

			if ( ! is_array( rgars( $settings, 'column_mapping' ) ) ) {
				return;
			}

			//Has the field map changed?
			if ( self::flatten_column_mapping( $settings['column_mapping'] ) == $field_map_sheet
				&& ! $this->map_contains_custom_key( $settings['column_mapping'] ) ) {
				//No
				return;
			}

			//Build an array of requests to send in a batch
			$requests = array();

			$tab = GP_Google_Sheets_Reader::find_tab( $service, $spreadsheet_id, $feed );
			if ( $tab === false || $tab instanceof \Exception ) {
				//Cannot read the sheet
				return;

			}
			$claimed_columns_count = 0;

			for ( $c = 0; $c < sizeof( $settings['column_mapping'] ); $c++ ) {
				$column = $settings['column_mapping'][ $c ];

				//Is the map value stored in the field map or a field id?
				$column_metadata_value = ( $column['value'] == 'gf_custom' ? 'gf_custom:' . $column['custom_value'] : $column['value'] );

				//Is this a column the user just added? Or already mapped?
				$column_to_claim = '';
				if ( $column['key'] == 'gf_custom' ) {
					//User is adding this column
					//Are there empty columns in the sheet?
					$column_to_claim = GP_Google_Sheets_Reader::find_first_empty_column_index( $feed );
					if ( -1 == $column_to_claim ) {
						//Append a column to the right of the sheet
						$column_to_claim = GP_Google_Sheets_Writer::append_column( $feed );
					} else {
						/**
						 * $column_to_claim doesn't take into consideration the
						 * columns we are going to add in $requests array that
						 * we have not yet processed, so add our counter.
						 */
						$column_to_claim += $claimed_columns_count;
					}
				} else {
					//This column was moved maybe, make sure $column_metadata_value is somewhere
					$new_place = array_search( $column_metadata_value, $field_map_sheet );
					if ( $new_place === false
						|| $field_map_sheet[ $new_place ] != $column_metadata_value
						|| GP_Google_Sheets_Writer::number_to_column_letters( $new_place + 1 ) != $column['key'] ) {
						//This column isn't in the sheet yet or has moved
						$column_to_claim = GP_Google_Sheets_Range_Parser::letters_to_index( $column['key'] );
						unset( $field_map_sheet[ $column_to_claim ] );
					} else {
						//This column is where we expect it
						unset( $field_map_sheet[ $new_place ] );
						continue;
					}
				}

				//Claim column $column_to_claim for this field
				// - row 1 value $column['custom_key']
				// - column developer metadata of $column_metadata_value

				// Write metadata, first delete the old metadata then create.
				// This is easier/more reliable than an update request.
				if ( ! empty( $metadata_field_map ) ) {
					$requests[] = GP_Google_Sheets_Metadata_Writer::create_request_delete_column( $column_to_claim, (int) $feed_id, $tab->sheet_id );
				}

				$requests[] = GP_Google_Sheets_Metadata_Writer::create_request_write_column( $column_to_claim, $column_metadata_value, (int) $feed_id, $tab->sheet_id );
				$claimed_columns_count++;

				if ( ! empty( $column['custom_key'] ) ) {
					//Write contents of row 1
					$write_range = GP_Google_Sheets_Writer::number_to_column_letters( $column_to_claim + 1 ) . '1';
					$write_range = "{$tab->tab_name}!{$write_range}";

					$requests[] = GP_Google_Sheets_Writer::create_write_rows_request( $feed, $write_range, array( array( $column['custom_key'] ) ), array(), $tab->sheet_id );
				}

				//Save column letter in $column['key']
				$settings['column_mapping'][ $c ]['key'] = GP_Google_Sheets_Writer::number_to_column_letters( $column_to_claim + 1 );
				//$settings['column_mapping'][$c]['custom_key'] = '';
			}

			//Update the edited $settings
			$this->update_feed_meta( $feed_id, $settings );

			/**
			 * The items left in $field_map_sheet are mapped columns that are
			 * no longer in the feed's map. Stop updating them by removing the
			 * metadata value.
			 */
			foreach ( $field_map_sheet as $column_index => $metadata_value ) {
				if ( $metadata_value === '' ) {
					//User-added column
					continue;
				}
				//delete column metadata
				$requests[] = GP_Google_Sheets_Metadata_Writer::create_request_delete_column( $column_index, (int) $feed_id, $tab->sheet_id );
			}
			if ( ! empty( $requests ) ) {
				$body = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest();
				$body->setRequests( $requests );

				try {
					// TODO this should probably get moved into class-writer.php
					$spreadsheets->batchUpdate( $spreadsheet_id, $body );
				} catch ( \Exception $e ) {
					$this->log_error( $e->getMessage() );
				}
			}
		}

		public function handle_feed_deleted( $feed_id, $addon_instance ) {
			$this->tokens->delete_feed_mapping( GFAPI::get_feed( $feed_id ) );
		}

		public function register_entry_meta_box( $meta_boxes, $entry, $form ) {
			if ( $this->has_feed( $form['id'] ) ) {
				$meta_boxes['gp_google_sheets'] = array(
					'title'         => esc_html__( 'Google Sheets', 'gp-notification-scheduler' ),
					'callback'      => array( $this, 'entry_meta_box' ),
					'context'       => 'side',
					'callback_args' => array( $entry, $form ),
				);
			}

			return $meta_boxes;
		}

		/**
		 * Meta box for getting the status of each feed and the ability to process it if it failed.
		 *
		 * @see GFEntryDetail::meta_box_notifications()
		 */
		public function entry_meta_box( $args, $metabox ) {
			$form    = $args['form'];
			$form_id = $form['id'];
			$entry   = $args['entry'];

			$feeds = $this->get_active_feeds( $form_id );
			?>
			<div class="gpgs-entry-feeds">
				<?php
				foreach ( $feeds as $feed ) :
					$spreadsheet_id = GP_Google_Sheets_Writer::spreadsheet_id( $feed );

					//Do we have a Sheet URL? Check the feed settings
					if ( empty( $spreadsheet_id ) ) {
						//No. Need a Sheet before we can make updates inside
						continue;
					}

					$service      = GP_Google_Sheets_Authenticator::create_service( $feed );
					$sheet_name   = 'Open Sheet';
					$loaded_sheet = false;

					if ( ! is_wp_error( $service ) ) {
						try {
							$fetched_sheet_name = GP_Google_Sheets_Reader::get_spreadsheet_name( $service, $spreadsheet_id );
							if ( is_string( $fetched_sheet_name ) ) {
								$sheet_name = $fetched_sheet_name;
							}

							$loaded_sheet = true;
						} catch ( Exception $e ) {
							// Do nothing, we already have fallback for sheet name.
						}
					}

					$inserted_timestamp = gform_get_meta( $entry['id'], "{$this->_slug}_{$feed['id']}_inserted" );
					$updated_timestamp  = gform_get_meta( $entry['id'], "{$this->_slug}_{$feed['id']}_updated" );

					$inserted_time = wp_date( __( 'M j, Y \a\t g:ia', 'gp-google-sheets' ), $inserted_timestamp );
					$updated_time  = wp_date( __( 'M j, Y \a\t g:ia', 'gp-google-sheets' ), $updated_timestamp );

					$process_feed_url = wp_nonce_url( add_query_arg( array(
						'gpgs_process_feed' => $feed['id'],
					) ), 'gpgs_process_feed_' . $feed['id'] );
					?>
					<div class="alert">
						<h4><?php echo esc_html( rgars( $feed, 'meta/feed_name' ) ); ?></h4>
						<div><span>Sheet:</span><span><a href="<?php echo esc_html( rgars( $feed, 'meta/google_sheet_url' ) ); ?>" target="_blank"><?php echo esc_html( $sheet_name ); ?></a></span></div>
						<?php if ( $inserted_timestamp ) : ?>
							<div><span>Inserted:</span><span><?php echo esc_html( $inserted_time ); ?></span></div>
						<?php endif; ?>

						<?php if ( $updated_timestamp ) : ?>
							<div><span>Updated:</span><span><?php echo esc_html( $updated_time ); ?></span></div>
						<?php endif; ?>

						<?php if ( ! $inserted_timestamp && $loaded_sheet ) : ?>
							<a class="button" href="<?php echo $process_feed_url; ?>">Process Feed</a>
						<?php endif; ?>

						<?php if ( ! $inserted_timestamp && ! $loaded_sheet ) : ?>
							<p class="alert error"><?php esc_html_e( 'To reprocess this feed, please reauthenticate with Google Sheets.', 'gp-google-sheets' ); ?></p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<?php
		}

		public function entry_details_maybe_process_feed( $form, $entry ) {
			$feed_id = rgget( 'gpgs_process_feed' );

			// Don't try to process if we don't have an ID
			if ( ! $feed_id ) {
				return;
			}

			// Security
			if ( ! check_admin_referer( 'gpgs_process_feed_' . $feed_id ) ) {
				return;
			}

			// Don't reprocess if it is already inserted
			$inserted_timestamp = gform_get_meta( $entry['id'], "{$this->_slug}_{$feed_id}_inserted" );

			if ( $inserted_timestamp ) {
				return;
			}

			$feed = $this->get_feed( $feed_id );
			$this->process_feed( $feed, $entry, $form );

			$this->log_debug( __METHOD__ . '(): Processing feed via manual click from entry details.' );
		}

		public function maybe_display_disconnected_notice() {
			$notice_disabled = defined( 'GF_LOGGING_DISABLE_NOTICE' ) && GF_LOGGING_DISABLE_NOTICE;

			if (
				$this->has_global_token()
				|| rgget( 'subview' ) === 'gp-google-sheets'
				|| ! GFCommon::current_user_can_any( 'gp-google-sheets_settings' )
				|| $notice_disabled
				|| ! is_plugin_active( $this->_path )
			) {
				return;
			}

			$markup = '<div class="notice notice-error gf-notice" id="gpgs_google_not_connected_notice"><p><strong>GP Google Sheets is not connected to Google. <a href="%s/admin.php?page=gf_settings&subview=gp-google-sheets">Click here to reconnect.</a></strong></p></div>';

			printf( $markup, get_admin_url() );
		}

		public function maybe_display_http_warning() {
			// Don't display the warning if the site is using HTTPS.
			if ( wp_is_using_https() ) {
				return;
			}

			$is_plugin_settings_view = rgget( 'page' ) === 'gf_settings' && rgget( 'subview' ) === 'gp-google-sheets';
			$is_form_settings_view   =
				rgget( 'page' ) === 'gf_edit_forms'
				&& rgget( 'view' ) === 'settings'
				&& rgget( 'subview' ) === 'gp-google-sheets'
				&& rgget( 'fid' ) === '0';

			// only display the warning on pages where you are able to oauth with Google.
			if ( ! $is_plugin_settings_view && ! $is_form_settings_view ) {
				return;
			}

			$markup = '<div class="notice notice-warning gf-notice" id="gpgs_site_served_over_http_notice"><p><strong>This site is served over HTTP which may result in browser warnings when attempting to authenticate with Google.</strong></p></div>';

			printf( $markup );
		}
	}

	GFFeedAddOn::register( 'GP_Google_Sheets' );

	function gp_google_sheets() {
		return GP_Google_Sheets::get_instance();
	}
}
