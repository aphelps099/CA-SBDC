<?php

namespace SB\SocialWall;

/**
 * Text domain class.
 *
 * @package PhpKnight\WpFireUser
 */
class TextDomain {

	/**
	 * Loads plugin text domain.
	 *
	 * @return void
	 */
	public static function load_text_domain() {
		load_plugin_textdomain( 'wp-fire-user', false, SocialWall::$text_domain_directory );
	}
}

