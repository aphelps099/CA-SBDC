<?php
/**
 * @package gp-google-sheets
 * @copyright Copyright (c) 2022, Gravity Wiz, LLC
 * @author Gravity Wiz <support@gravitywiz.com>
 * @license GPLv2
 * @link https://gravitywiz.com/documentation/gravity-forms-google-sheets/
 */
defined( 'ABSPATH' ) or exit;

// Polyfill for https://www.php.net/manual/en/function.array-key-first.php
function gpgs_array_key_first( $array ) {
	if ( ! is_array( $array ) || empty( $array ) ) {
		return null;
	}

	foreach ( $array as $key => $unused ) {
		return $key;
	}
}

// Polyfill for https://www.php.net/manual/en/function.array-key-last.php
function gpgs_array_key_last( $array ) {
	if ( ! is_array( $array ) || empty( $array ) ) {
		return null;
	}

	return array_keys( $array )[ count( $array ) - 1 ];
}
