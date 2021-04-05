<?php
/**
 * Plugin Name: JVM rich text icons
 * Description: Add Font Awesome icons, or icons from a custom icon set to rich text fields in the Gutenberg block editor.
 * Version: 1.0
 * Author: Joris van Montfort
 * Author URI: https://jorisvm.nl
 * Text Domain: jvm-richtext-icons
 * Domain Path: languages
 *
 * @category Gutenberg
 * @author Joris van Montfort
 * @version 1.0
 * @package JVM rich text icons
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Initializer.
 */
require_once plugin_dir_path( __FILE__ ) . 'src/init.php';