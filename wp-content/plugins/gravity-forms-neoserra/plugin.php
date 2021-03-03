<?php
/**
 * Plugin Name: Gravity Forms Neoserra
 * Description: Adds support for exporing Gravity Forms entries to a spreadsheet to be imported into Neoserra.
 * Version: 1.0.0
 * Author: Jordan Crown
 * Author URI: http://www.jordancrown.com
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

 
include_once( dirname( __FILE__ ) . '/classes/class-gravity-forms-neoserra.php' );
Gravity_Forms_Neoserra::init();