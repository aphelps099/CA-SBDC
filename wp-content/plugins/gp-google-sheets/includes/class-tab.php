<?php
/**
 * @package gp-google-sheets
 * @copyright Copyright (c) 2022, Gravity Wiz, LLC
 * @author Gravity Wiz <support@gravitywiz.com>
 * @license GPLv2
 * @link https://gravitywiz.com/documentation/gravity-forms-google-sheets/
 */
defined( 'ABSPATH' ) or exit;

/**
 * GP_Google_Sheets_Tab
 *
 * This class helps transport a few properties of a tab in a Google Sheet that
 * are needed when creating ranges that target the Sheet.
 */
class GP_Google_Sheets_Tab {
	public $tab_name;
	public $sheet_id;
	public $row_count;
}
