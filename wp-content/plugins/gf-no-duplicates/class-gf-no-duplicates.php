<?php
/**
 * GF No Duplicates Add-On.
 *
 * @since     1.0
 * @package GF No Duplicates
 * @author    Samuel Aguilera
 * @copyright Copyright (c) 2022 Samuel Aguilera
 */

defined( 'ABSPATH' ) || die();

// Include the Gravity Forms Add-On Framework.
GFForms::include_addon_framework();

/**
 * Class GF_No_Duplicates
 *
 * Primary class to manage the GF No Duplicates Add-On.
 *
 * @since 1.0
 *
 * @uses GFAddOn
 */
class GF_No_Duplicates extends GFAddOn {

	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since  1.0
	 * @var    GF_No_Duplicates $_instance If available, contains an instance of this class
	 */
	private static $_instance = null;

	/**
	 * Defines the version of the GF No Duplicates Add-On.
	 *
	 * @since  1.0
	 * @var    string $_version Contains the version.
	 */
	protected $_version = GF_NO_DUPLICATES_VERSION;

	/**
	 * Defines the minimum Gravity Forms version required.
	 *
	 * @since  1.0
	 * @var    string $_min_gravityforms_version The minimum version required.
	 */
	protected $_min_gravityforms_version = GF_NO_DUPLICATES_MIN_GF_VERSION;

	/**
	 * Defines the plugin slug.
	 *
	 * @since  1.0
	 * @var    string $_slug The slug used for this plugin.
	 */
	protected $_slug = 'gf-no-duplicates';

	/**
	 * Defines the main plugin file.
	 *
	 * @since  1.0
	 * @var    string $_path The path to the main plugin file, relative to the plugins folder.
	 */
	protected $_path = 'gf-no-duplicates/gf-no-duplicates.php';

	/**
	 * Defines the full path to this class file.
	 *
	 * @since  1.0
	 * @var    string $_full_path The full path.
	 */
	protected $_full_path = __FILE__;

	/**
	 * Defines the URL where this add-on can be found.
	 *
	 * @since  1.0
	 * @var    string The URL of the Add-On.
	 */
	protected $_url = 'https://www.samuelaguilera.com';

	/**
	 * Defines the title of this add-on.
	 *
	 * @since  1.0
	 * @var    string $_title The title of the add-on.
	 */
	protected $_title = 'GF No Duplicates Add-On';

	/**
	 * Defines the short title of the add-on.
	 *
	 * @since  1.0
	 * @var    string $_short_title The short title.
	 */
	protected $_short_title = 'GF No Duplicates';

	/**
	 * Defines the capabilities needed for the GF No Duplicates Add-On
	 *
	 * @since  1.0
	 * @access protected
	 * @var    array $_capabilities The capabilities needed for the Add-On
	 */
	protected $_capabilities = array( 'gf-no-duplicates', 'gf-no-duplicates_uninstall' );

	/**
	 * Defines the capability needed to uninstall the Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_uninstall The capability needed to uninstall the Add-On.
	 */
	protected $_capabilities_uninstall = 'gf-no-duplicates_uninstall';

	/**
	 * Returns an instance of this class, and stores it in the $_instance property.
	 *
	 * @since  1.0
	 *
	 * @return GF_No_Duplicates $_instance An instance of the GF_No_Duplicates class
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new GF_No_Duplicates();
		}

		return self::$_instance;
	}

	/**
	 * Register initialization hooks.
	 *
	 * @since  1.0
	 */
	public function init() {

		parent::init();
		add_filter( 'gform_form_tag', array( $this, 'add_token_input' ), 99999, 2 ); // Try to run later than any other snippet using the same filter.
		add_filter( 'gform_validation', array( $this, 'validate_token' ), 99999, 1 ); // Low priority required to avoid User Registration validation overriding our validation result.
		add_filter( 'gform_pre_render', array( $this, 'replace_form_string_handler' ), 99999, 1 ); // Try to run later than any other snippet using the same filter.
	}



	/**
	 * Return the plugin's icon for the plugin/form settings menu.
	 *
	 * @return string
	 */
	public function get_menu_icon() {
		return file_get_contents( $this->get_base_path() . '/images/menu-icon.svg' ); // phpcs:ignore
	}

	/**
	 * Configures the settings which should be rendered on the add-on settings tab.
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {

		return array(
			array(
				'title'       => esc_html__( 'GF No Duplicate Settings', 'gf-no-duplicates' ),
				'description' => '<p style="text-align: left;">' . esc_html__( 'The following optional setting allows you to customize the message shown to visitors when a duplicate submission is stopped. Leave it empty to use the default message.', 'gf-no-duplicates' ) . '</p>',
				'fields'      => array(
					array(
						'type'          => 'textarea',
						'name'          => 'duplicate_submission_message',
						'label'         => esc_html__( 'Duplicate Submission Message', 'gf-no-duplicates' ),
						'default_value' => '',
						'use_editor'    => true,
						'class'         => 'large',
					),
				),
			),
		);
	}

	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Generates and return the toke value.
	 *
	 * @param array $form The form data.
	 */
	public function generate_random_token( $form ) {
		// Generate random value for the field.
		if ( function_exists( 'random_bytes' ) ) {
			$random_token = wp_date( 'YmdHis' ) . '-' . bin2hex( random_bytes( 20 ) );
		} else {
			$random_token = wp_date( 'YmdHis' ) . '-' . wp_rand( 100000, PHP_INT_MAX );
		}

		/**
		 * Allow replacing the generated random token value with one generated by user custom code.
		 * Generating an unique value for this is crucial for the add-on to work as expected. Use at your own risk.
		 */
		$random_token = apply_filters( 'gnd_token_value', $random_token, $form );

		return $random_token;
	}

	/**
	 * Adds the input to the form HTML markup.
	 *
	 * @param string $form_tag The form tag.
	 * @param array  $form     The form array.
	 */
	public function add_token_input( $form_tag, $form ) {
		// Prevent issues with add-ons that may remove the form tag in some cases. e.g. Gravity Flow editable fields step.
		if ( empty( $form_tag ) ) {
			return $form_tag;
		}

		// Get the token value.
		$random_token = $this->generate_random_token( $form );
		$token_input  = "<input type='hidden' class='gform_hidden' name='gnd_token' id='gnd_token' autocomplete='off' value='$random_token' />";

		return $form_tag . $token_input;
	}

	/**
	 * Gets entry meta for displaying.
	 *
	 * @param array $entry_meta Entry meta array.
	 * @param int   $form_id    Form id number.
	 */
	public function get_entry_meta( $entry_meta, $form_id ) {
		$entry_meta['gnd_token'] = array(
			'label'                      => 'GND Token',
			'is_numeric'                 => false,
			'is_default_column'          => false,
			'update_entry_meta_callback' => array( $this, 'update_entry_meta' ),
			'filter'                     => array(
				'operators' => array( 'is', 'isnot' ),
			),
		);
		return $entry_meta;
	}

	/**
	 * Saves entry meta to the entry.
	 *
	 * @param string $key   The entry meta key.
	 * @param array  $entry The entry object.
	 * @param array  $form  The form object.
	 */
	public function update_entry_meta( $key, $entry, $form ) {

		// Skip Partial Entries add-on requests.
		$is_heartbeat = rgpost( 'action' ) === 'heartbeat';
		if ( true === $is_heartbeat ) {
			$this->log_debug( __METHOD__ . '(): Skipping Partial Entries request...' );
			return;
		}

		$gnd_token = rgpost( 'gnd_token' ); // rgpost already does isset/returns empty.
		if ( ! empty( $gnd_token ) ) {
			$entry_id  = rgar( $entry, 'id' );
			$gnd_token = sanitize_text_field( $gnd_token );
			$this->log_debug( __METHOD__ . "(): GND Token {$gnd_token} added to entry ID #{$entry_id}" );
		}

		return $gnd_token;
	}

	/**
	 * Validates token during submission.
	 *
	 * @param array $validation_result Validation result object containing the form.
	 */
	public function validate_token( $validation_result ) {

		// Skip Partial Entries add-on requests.
		$is_heartbeat = rgpost( 'action' ) === 'heartbeat';
		if ( true === $is_heartbeat ) {
			$this->log_debug( __METHOD__ . '(): Skipping Partial Entries request...' );
			return $validation_result;
		}

		$form                    = $validation_result['form'];
		$gnd_token               = rgpost( 'gnd_token' ); // rgpost already does isset/returns empty.
		$token_validation_failed = false;
		$current_page_url        = RGFormsModel::get_current_page_url();

		/**
		 * Providing a token value is required, so POST requests without a gnd_token or with empty value will fail.
		 * This prevents repeated POST requests created before enabling the add-on from creating duplicates.
		 * Also stops bots from trying to submit the form without providing the gnd_token value.
		 */
		if ( empty( $gnd_token ) ) {
			$token_validation_failed = true;
			$this->log_debug( __METHOD__ . "(): POST request from {$current_page_url} - gnd_token parameter missing or empty!" );
		} else {
			$search_criteria['field_filters'][] = array(
				'key'   => 'gnd_token',
				'value' => sanitize_text_field( $gnd_token ),
			);
			$gnd_token_exists                   = GFAPI::count_entries( $form['id'], $search_criteria );
			$this->log_debug( __METHOD__ . "(): POST request from {$current_page_url} using GND Token {$gnd_token}\nExisting entries using this token: {$gnd_token_exists}" );
			if ( $gnd_token_exists > 0 ) {
				$token_validation_failed = true;
				$this->log_debug( __METHOD__ . "(): GND Token {$gnd_token} was saved to a previous entry!" );
			} else {
				$this->log_debug( __METHOD__ . "(): No existing entries using GND Token {$gnd_token} were found. Submission is valid." );
			}
		}

		// Trigger validation error if token failed.
		if ( true === $token_validation_failed ) {
			$validation_result['is_valid'] = false;
			// Add the token to the $form object for later usage.
			$form['failed_gnd_token'] = $gnd_token;
			$this->log_debug( __METHOD__ . "(): Form validation set as failed and {$gnd_token} added to the form object as value for failed_gnd_token" );
		}

		// Assign modified $form object back to the validation result.
		$validation_result['form'] = $form;
		return $validation_result;
	}

	/**
	 * Replace the form with an error message to prevent visitors from submitting the duplicate manually.
	 *
	 * @param array $form_string The form markup.
	 * @param int   $form        The form currently being processed.
	 */
	public function replace_form_string( $form_string, $form ) {

		// Replace $form_string only when $form['failed_gnd_token'] exists and it's populated.
		if ( is_array( $form ) && isset( $form['failed_gnd_token'] ) && ! empty( $form['failed_gnd_token'] ) ) {
			$current_page_url = RGFormsModel::get_current_page_url();
			// translators: Placeholders are HTML tags for a link. Just leave them on the same position.
			$default_message = sprintf( wp_filter_nohtml_kses( __( 'A duplicate submission has been aborted! If you really want to submit the form again, please %1$sclick here%2$s.', 'gf-no-duplicates' ) ), "<a href='$current_page_url' rel='noopener noreferrer'>", '</a>' );
			// Get custom message from settings.
			$custom_message = $this->get_plugin_setting( 'duplicate_submission_message' );

			// Use custom message if there's any saved.
			$message = empty( $custom_message ) ? $default_message : $custom_message;

			// Filter to allow customization of the warning message displayed.
			$message = apply_filters( 'gnd_duplicate_submission_message', $message, $form );

			// Sanitizes content for allowed HTML tags (the ones allowed by WordPress for post content).
			$message = wp_kses_post( $message );

			$form_string = "<p class='gnd_duplicate_message'>$message</p>";
			$this->log_debug( __METHOD__ . "(): Form replaced with error message to prevent re-submission due to failed_gnd_token found with the following value: {$form['failed_gnd_token']}" );
		}

		return $form_string;
	}


	/**
	 * Check for a failed token to replace the form string.
	 *
	 * Calling this from gform_pre_render is required to avoid User Registration overriding our form string with the logged in required message for Update User feeds.
	 *
	 * This doesn't help with core 'Require user to be logged in' setting:
	 * It's because there's no submission process when a not logged in user sends a POST request for a form with this setting enabled. So no entry is created anyway.
	 *
	 * @param int $form The form currently being processed.
	 */
	public function replace_form_string_handler( $form ) {
		if ( ! is_array( $form ) || empty( $form['id'] ) ) {
			return $form;
		}

		// Replace $form_string only when $form['failed_gnd_token'] exists and it's populated.
		if ( isset( $form['failed_gnd_token'] ) && ! empty( $form['failed_gnd_token'] ) ) {
			add_filter( 'gform_get_form_filter_' . $form['id'], array( $this, 'replace_form_string' ), 99999, 2 );
		}

		return $form;
	}
}
