<?php

/**
 * Social Wall Tooltip Wizard
 *
 *
 * @since 2.0
 */

namespace SB\SocialWall\Admin\Services;

use SB\SocialWall\Core\Abstracts\Service;

class Tooltip extends Service {

	/**
	 * Register hooks.
	 *
	 * @since 2.0
	 */
	public function register_hooks() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueues' ] );
		add_action( 'admin_footer', [ $this, 'output' ] );
	}


	/**
	 * Enqueue assets.
	 *
	 * @since 2.0
	 */
	public function enqueues() {
		wp_enqueue_style(
			'sw-tooltipster-css',
			SBSW_PLUGIN_URL . 'css/tooltipster.css',
			null,
			SWVER
		);
		wp_enqueue_script(
			'sw-tooltipster-js',
			SBSW_PLUGIN_URL . 'js/jquery.tooltipster.min.js',
			[ 'jquery' ],
			SWVER,
			true
		);
		wp_enqueue_script(
			'sw-admin-tooltip-wizard',
			SBSW_PLUGIN_URL . 'js/tooltip-wizard.js',
			[ 'sw-tooltipster-js' ],
			SWVER
		);
		$wp_localize_data = [];
		if( $this->check_gutenberg_wizard() ){
			$wp_localize_data['sw_wizard_gutenberg'] = true;
		}

		wp_localize_script(
			'sw-admin-tooltip-wizard',
			'sw_admin_tooltip_wizard',
			$wp_localize_data
		);
	}

	/**
	 * Output HTML.
	 *
	 * @since 2.0
	 */
	public function output() {
		if( $this->check_gutenberg_wizard() ){
			$this->gutenberg_tooltip_output();
		}

	}

	/**
	 * Gutenberg Tooltip Output HTML.
	 *
	 * @since 2.0
	 */
	public function check_gutenberg_wizard() {
		global $pagenow;
		return ( ( $pagenow == 'post.php' ) || (get_post_type() == 'page') )
				&& ! empty( $_GET['sw_wizard'] );
	}


	/**
	 * Gutenberg Tooltip Output HTML.
	 *
	 * @since 2.0
	 */
	public function gutenberg_tooltip_output() {
		?>
		<div id="sw-gutenberg-tooltip-content">
			<div class="sw-tlp-wizard-cls sw-tlp-wizard-close"></div>
			<div class="sw-tlp-wizard-content">
				<strong class="sw-tooltip-wizard-head"><?php echo __('Add a Block','social-wall') ?></strong>
				<p class="sw-tooltip-wizard-txt"><?php echo __('Click the plus button, search for Social Wall','social-wall'); ?>
                    <br/><?php echo __('Feed, and click the block to embed it.','social-wall') ?> <a href="https://smashballoon.com/doc/wordpress-5-block-page-editor-gutenberg/?social-wall" rel="noopener" target="_blank"><?php echo __('Learn More','social-wall') ?></a></p>
				<div class="sw-tooltip-wizard-actions">
					<button class="sw-tlp-wizard-close"><?php echo __('Done','social-wall') ?></button>
				</div>
			</div>
		</div>
		<?php
	}


}