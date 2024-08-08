<?php
/**
 * Plugin Name: GF No Duplicates
 * Description: Prevents duplicate Gravity Forms submissions caused by the same POST request sent more than once.
 * Version: 1.2
 * Author: Samuel Aguilera
 * Author URI: https://www.samuelaguilera.com
 * License: GPL-3.0+
 * Text Domain: gf-no-duplicates
 *
 * @package GF No Duplicates
 */

/*
------------------------------------------------------------------------
Copyright 2021 Samuel Aguilera

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see http://www.gnu.org/licenses.
*/

defined( 'ABSPATH' ) || die();

// Defines the current version of the GF No Duplicates.
define( 'GF_NO_DUPLICATES_VERSION', '1.2' );

// Defines the minimum version of Gravity Forms required to run GF No Duplicates.
define( 'GF_NO_DUPLICATES_MIN_GF_VERSION', '2.5' );

// After Gravity Forms is loaded, load the Add-On.
add_action( 'gform_loaded', array( 'GF_No_Duplicates_Bootstrap', 'load_addon' ), 5 );

/**
 * Loads the GF No Duplicates Add-On.
 *
 * Includes the main class and registers it with GFAddOn.
 *
 * @since 1.0
 */
class GF_No_Duplicates_Bootstrap {

	/**
	 * Loads the required files.
	 *
	 * @since  1.0
	 */
	public static function load_addon() {

		// Requires the class file.
		require_once plugin_dir_path( __FILE__ ) . '/class-gf-no-duplicates.php';

		// Registers the class name with GFAddOn.
		GFAddOn::register( 'GF_No_Duplicates' );
	}

}

/**
 * Returns an instance of the GF_No_Duplicates class
 *
 * @since  1.0
 *
 * @return GF_No_Duplicates|bool An instance of the GF_No_Duplicates class
 */
function gf_no_duplicates() {
	return class_exists( 'GF_No_Duplicates' ) ? GF_No_Duplicates::get_instance() : false;
}
