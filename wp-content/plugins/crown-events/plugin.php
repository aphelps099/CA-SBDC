<?php
/**
 * Plugin Name: Crown Events
 * Description: Adds support for managing event entries.
 * Version: 1.0.0
 * Author: Jordan Crown
 * Author URI: http://www.jordancrown.com
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */


// load required class files
$class_files_dir = dirname( __FILE__ ) . '/classes';
$ignore_class_files = array( 'class-crown-events-gravity-zwr.php' );
foreach ( scandir( $class_files_dir ) as $file) {
	if ( in_array( $file, $ignore_class_files ) ) continue;
	if ( preg_match( '/^[^\.]+\.php$/', $file ) ) {
		include_once( $class_files_dir . '/' . $file );
	}
}

Crown_Events::init();