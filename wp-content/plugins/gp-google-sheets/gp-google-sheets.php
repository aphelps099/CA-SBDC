<?php
/**
 * Plugin Name: GP Google Sheets
 * Plugin URI: https://gravitywiz.com/documentation/gravity-forms-google-sheets/
 * Description: Automatically send and sync Gravity Forms data with Google Sheets - and unlock new possibilities for your data.
 * Version: 1.0.4
 * Author: Gravity Wiz
 * Author URI: http://gravitywiz.com/
 * Text Domain: gp-google-sheets
 * Domain Path: /languages
 * License: GPL2
 * Perk: True
 *
 * @package gp-google-sheets
 * @copyright Copyright (c) 2022, Gravity Wiz, LLC
 * @author Gravity Wiz <support@gravitywiz.com>
 * @license GPLv2
 * @link https://gravitywiz.com/documentation/gravity-forms-google-sheets/
 */

defined( 'ABSPATH' ) or exit;

define( 'GP_GOOGLE_SHEETS_VERSION', '1.0.4' );

//Initialize this Perk
require plugin_dir_path( __FILE__ ) . 'includes/class-gp-bootstrap.php';
require plugin_dir_path( __FILE__ ) . 'third-party/woocommerce/action-scheduler/action-scheduler.php';
require plugin_dir_path( __FILE__ ) . 'includes/functions.php';

$GP_Google_Sheets_Bootstrap = new \GP_Google_Sheets\GP_Bootstrap( 'class-gp-google-sheets.php', __FILE__ );

/*
* Gravity Flow compatibility. We need to load Steps sooner than GF Add-ons are typically initialized.
*/
add_action( 'gravityflow_loaded', function() {
	require_once( plugin_dir_path( __FILE__ ) . '/includes/class-gravity-flow-step-feed-googlesheets.php' );
	Gravity_Flow_Steps::register( new Gravity_Flow_Step_Feed_GP_Google_Sheets() );
} );
