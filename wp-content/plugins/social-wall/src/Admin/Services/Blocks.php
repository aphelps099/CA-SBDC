<?php
/**
 * Custom Facebook Feed block with live preview.
 *
 * @since 2.0
 */
namespace SB\SocialWall\Admin\Services;

use SB\SocialWall\Database;


class Blocks {

	/**
	 * Indicates if current integration is allowed to load.
	 *
	 * @since 1.8
	 *
	 * @return bool
	 */
	public function allow_load() {
		return function_exists( 'register_block_type' );
	}

	/**
	 * Loads an integration.
	 *
	 * @since 1.7.1
	 */
	public function load() {
		$this->register_hooks();
	}

	/**
	 * Integration hooks.
	 *
	 * @since 2.0
	 */
	public function register_hooks() {
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
	}

	/**
	 * Register Custom Facebook Feed Gutenberg block on the backend.
	 *
	 * @since 2.0
	 */
	public function register_block() {
		wp_register_style(
			'sw-blocks-styles',
			trailingslashit( SBSW_PLUGIN_URL ) . 'css/sw-blocks.css',
			array( 'wp-edit-blocks' ),
			SWVER
		);

		$attributes = array(
			'shortcodeSettings' => array(
				'type' => 'string',
			),
			'noNewChanges' => array(
				'type' => 'boolean',
			),
			'executed' => array(
				'type' => 'boolean',
			)
		);

		register_block_type(
			'sb/sw-feed-block',
			array(
				'attributes'      => $attributes,
				'render_callback' => array( $this, 'get_feed_html' ),
			)
		);

	}

	/**
	 * Load Custom Facebook Feed Gutenberg block scripts.
	 *
	 * @since 2.0
	 */
	public function enqueue_block_editor_assets() {
		sbsw_scripts_enqueue(true);

		wp_enqueue_style( 'sw-blocks-styles' );
		wp_enqueue_script(
			'sw-feed-block',
			trailingslashit( SBSW_PLUGIN_URL ) . 'js/sw-blocks.js',
			array( 'wp-blocks', 'wp-i18n', 'wp-element' ),
			SWVER,
			true
		);

		$shortcodeSettings = '';

		$i18n = array(
			'addSettings'         => esc_html__( 'Add Settings', 'custom-facebook-feed' ),
			'shortcodeSettings'   => esc_html__( 'Shortcode Settings', 'custom-facebook-feed' ),
			'example'             => esc_html__( 'Example', 'custom-facebook-feed' ),
			'preview'             => esc_html__( 'Apply Changes', 'custom-facebook-feed' ),

		);

		if ( ! empty( $_GET['sw_wizard'] ) ) {
			$shortcodeSettings = 'feed="' . (int)sanitize_text_field( wp_unslash( $_GET['sw_wizard'] ) ) . '"';
		}

		wp_localize_script(
			'sw-feed-block',
			'sw_block_editor',
			array(
				'wpnonce'  => wp_create_nonce( 'facebook-blocks' ),
				'canShowFeed' => ! empty( $access_token ),
				'configureLink' => get_admin_url() . '?page=sw-top',
				'shortcodeSettings'    => $shortcodeSettings,
				'i18n'     => $i18n,
			)
		);

	}

	/**
	 * Get form HTML to display in a Custom Facebook Feed Gutenberg block.
	 *
	 * @param array $attr Attributes passed by Custom Facebook Feed Gutenberg block.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function get_feed_html( $attr ) {
		$shortcode_settings = isset( $attr['shortcodeSettings'] ) ? $attr['shortcodeSettings'] : '';
		$feeds_count = Database::feeds_count();
		if ( $feeds_count <= 0 ) {
			return $this->plain_block_design();
		}

		if ( empty( $shortcode_settings ) || strpos( $shortcode_settings, 'feed=' ) === false ) {
			$feeds = Database::feeds_query();
			if ( ! empty( $feeds[0]['id'] ) ) {
				$shortcode_settings = 'feed="' . (int) $feeds[0]['id'] . '"';
			}
		}

		$shortcode_settings = str_replace(array( '[social-wall', ']' ), '', $shortcode_settings);

		$return = do_shortcode( '[social-wall '.$shortcode_settings.']' );

		return $return;
	}

	/**
	 * Plain block design when theres no feeds.
	 * 
	 * @since 4.4.0
	 */
	public function plain_block_design( $license_state = 'inactive' ) {
		if ( !is_admin() && !defined( 'REST_REQUEST' ) ) {
			return;
		}
		$sw_logo = '<svg width="30" height="38" viewBox="0 0 24 32" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path fill-rule="evenodd" clip-rule="evenodd" d="M23.184 15.0983C23.184 7.16669 18.0279 0.736816 11.6653 0.736816C5.30271 0.736816 0.144287 7.16669 0.144287 15.0983C0.144287 22.702 4.86911 28.9061 10.8523 29.4265L10.2161 31.4581L14.2056 31.1159L12.8059 29.3908C18.6312 28.678 23.184 22.5523 23.184 15.0983Z" fill="#FE544F"/>
		<path fill-rule="evenodd" clip-rule="evenodd" d="M14.412 5.78583L14.9664 11.5534L20.7103 11.7198L16.5552 15.6153L19.837 20.4015L14.3084 19.3536L12.6323 24.9284L10.0874 19.9361L4.94895 22.3103L6.92564 16.9585L1.91415 14.4097L7.27881 12.67L5.79849 7.34117L10.8738 10.2588L14.412 5.78583Z" fill="white"/>
		</svg>';

		$output = '<div class="sw-license-expired-plain-block-wrapper '. $license_state .'">';
			$output .= '<div class="sw-lepb-body">
				'. $sw_logo .'
				<p class="sw-block-body-title">Get started with your first feed from <br/> your Social Wall</p>';
		$output .= sprintf(
					'<a href="%s" class="sw-btn sw-btn-blue">%s </a>', 
					esc_url( admin_url('admin.php?page=sbsw') ), 
					__('Create a Social Wall Feed', 'social-wall')
				);
		$output .= '</div>
		</div>';

		return $output;
	}
}
