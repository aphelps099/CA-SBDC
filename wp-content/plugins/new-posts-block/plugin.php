<?php
/*
Plugin Name: New Posts Block
Plugin URI: https://xakuro.com/wordpress/
Description: Add a block to display a list of latest posts.
Version: 1.2.1
Author: Xakuro System
Author URI: https://xakuro.com/
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: new-posts-block
Domain Path: /languages

@package New_Posts_Block
*/

namespace New_Posts_Block;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'src/init.php';

class New_Posts_Block {
	const VERSION = '1.2.1';	// Plugin Version

	function __construct() {
		new New_Posts_Block_Initialize();
	}

	public static function plugin_dir_path() {
		return plugin_dir_path( __FILE__ );
	}
}

new New_Posts_Block();
