<?php

namespace SB\SocialWall\Admin\Services;

use SB\SocialWall\SocialWall;
use SB\SocialWall\Admin\Localize;
use SB\SocialWall\Core\Abstracts\Service;

/**
 * Class Asset
 *
 * Handles all the admin assets.
 */
class Asset extends Service {

	/**
	 * Asset constructor.
	 */
	public function register_hooks() {
		add_action( 'admin_enqueue_scripts', [ $this, 'register_scripts' ] );
	}

	/**
	 * Enqueue scripts.
	 */
	public function register_scripts() {
		$screen = get_current_screen();
		if ( 'toplevel_page_sbsw' !== $screen->id ) {
			return;
		}

		$asset = require_once SocialWall::$plugin_directory . '/build/main.asset.php';

		if ( isset($_GET['feed_id']) ) {
			global $wp_locale;
			wp_enqueue_script(
				"sb-date_i18n",
				SBSW_PLUGIN_URL . 'assets/js/date_i18n.js',
				null,
				false,
				true
			);

			$monthNames = array_map(
				array(&$wp_locale, 'get_month'),
				range(1, 12)
			);
			$monthNamesShort = array_map(
				array(&$wp_locale, 'get_month_abbrev'),
				$monthNames
			);
			$dayNames = array_map(
				array(&$wp_locale, 'get_weekday'),
				range(0, 6)
			);
			$dayNamesShort = array_map(
				array(&$wp_locale, 'get_weekday_abbrev'),
				$dayNames
			);
			wp_localize_script("sb-date_i18n",
				"DATE_I18N", array(
					"month_names" => $monthNames,
					"month_names_short" => $monthNamesShort,
					"day_names" => $dayNames,
					"day_names_short" => $dayNamesShort
				)
			);
		}

		wp_register_script(
			'sbsw-main-script',
			SocialWall::$build_url . '/main.js',
			['react', 'react-dom', 'wp-element', 'wp-i18n'],
			$asset['version'],
			true
		);

		wp_localize_script(
			'sbsw-main-script',
			'sbsw_admin',
			Localize::get()
		);

		wp_register_style(
			'sbsw-main-style',
			SocialWall::$build_url . '/main.css',
			[],
			$asset['version'],
			false
		);
	}
}
