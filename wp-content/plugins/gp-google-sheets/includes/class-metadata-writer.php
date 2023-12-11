<?php
/**
 * @package gp-google-sheets
 * @copyright Copyright (c) 2022, Gravity Wiz, LLC
 * @author Gravity Wiz <support@gravitywiz.com>
 * @license GPLv2
 * @link https://gravitywiz.com/documentation/gravity-forms-google-sheets/
 */
defined( 'ABSPATH' ) or exit;

use \GP_Google_Sheets\Dependencies\Google\Service\Sheets\DeveloperMetadata as Google_Service_Sheets_DeveloperMetadata;
use \GP_Google_Sheets\Dependencies\Google\Service\Sheets\DeveloperMetadataLocation as Google_Service_Sheets_DeveloperMetadataLocation;
use \GP_Google_Sheets\Dependencies\Google\Service\Sheets\CreateDeveloperMetadataRequest as Google_Service_Sheets_CreateDeveloperMetadataRequest;
use \GP_Google_Sheets\Dependencies\Google\Service\Sheets\DimensionRange as Google_Service_Sheets_DimensionRange;
use \GP_Google_Sheets\Dependencies\Google\Service\Sheets\Request as Google_Service_Sheets_Request;
use \GP_Google_Sheets\Dependencies\Google\Service\Sheets\DeveloperMetadataLookup as Google_Service_Sheets_DeveloperMetadataLookup;
use \GP_Google_Sheets\Dependencies\Google\Service\Sheets\DataFilter as Google_Service_Sheets_DataFilter;
use \GP_Google_Sheets\Dependencies\Google\Service\Sheets\DeleteDeveloperMetadataRequest as Google_Service_Sheets_DeleteDeveloperMetadataRequest;

/**
 * This class reads and writes metadata to a Google Sheet
 */
class GP_Google_Sheets_Metadata_Writer {

	/**
	 * @var string SHEET_KEY The metadata key and value written to the sheet tab we use in the Sheet.
	 * @deprecated We no longer rely on this and instead, we save the sheet ID in the feed meta.
	 */
	const SHEET_KEY = 'entries_to_google_sheet';

	/**
	 * Developer metadata key for linking columns to feeds.
	 *
	 * @deprecated Use column_key() instead which reduces the amount of characters used.
	 */
	public static function column_key_legacy( $feed_id ) {
		return "feed_{$feed_id}";
	}

	/**
	 * Developer metadata key for linking rows to entry IDs.
	 *
	 * @deprecated Use row_key() instead which reduces the amount of characters used.
	 */
	public static function row_key_legacy( $form_id ) {
		return "form_{$form_id}_entry_id";
	}

	/**
	 * Developer metadata key for linking columns to feeds.
	 *
	 * "fe" here means "feed"
	 */
	public static function column_key( $feed_id ) {
		return "fe{$feed_id}";
	}

	/**
	 * Developer metadata key for linking rows to entry IDs.
	 *
	 * "f" here means "form"
	 */
	public static function row_key( $form_id ) {
		return "f{$form_id}";
	}

	/**
	 * create_location
	 *
	 * Creates a Google_Service_Sheets_DeveloperMetadataLocation object that
	 * identifies a single column or row.
	 *
	 * @param  string $dimension "COLUMNS" or "ROWS
	 * @param  int $column_index
	 * @param  int $sheet_id Zero-based index that identifies a tab in the Sheet
	 * @return Google_Service_Sheets_DeveloperMetadataLocation
	 */
	protected static function create_location( $dimension, $column_index, $sheet_id = 0 ) {
		if ( ! in_array( $dimension, array( 'COLUMNS', 'ROWS' ) ) ) {
			$dimension = 'COLUMNS';
		}

		//Create a range that means "this column"
		$range = new Google_Service_Sheets_DimensionRange();
		$range->setSheetId( $sheet_id ); //0 = first tab
		$range->setDimension( $dimension );
		$range->setStartIndex( $column_index );
		$range->setEndIndex( $column_index + 1 );

		//And use it to create a location
		$location = new Google_Service_Sheets_DeveloperMetadataLocation();
		$location->setDimensionRange( $range );

		return $location;
	}

	/**
	 * create_request_delete_column
	 *
	 * Creates a Google_Service_Sheets_Request that deletes our column developer
	 * metadata value.
	 *
	 * @param  int $column_index
	 * @param  int $feed_id
	 * @param  int $sheet_id
	 *
	 * @return Google_Service_Sheets_Request
	 */
	public static function create_request_delete_column( $column_index, $feed_id, $sheet_id = 0 ) {
		$lookup = new Google_Service_Sheets_DeveloperMetadataLookup();
		$lookup->setMetadataKey( self::column_key( $feed_id ) );
		$lookup->setMetadataLocation( self::create_location( 'COLUMNS', $column_index, $sheet_id ) );

		$dataFilter = new Google_Service_Sheets_DataFilter();
		$dataFilter->setDeveloperMetadataLookup( $lookup );

		$delete = new Google_Service_Sheets_DeleteDeveloperMetadataRequest();
		$delete->setDataFilter( $dataFilter );

		$request = new Google_Service_Sheets_Request();
		$request->setDeleteDeveloperMetadata( $delete );

		return $request;
	}

	/**
	 * create_request_write_column
	 *
	 * Creates a Google_Service_Sheets_Request that helps write a metadata key
	 * value pair on a column.
	 *
	 * @param  int $column_index zero-based
	 * @param  string $value
	 * @param  int $feed_id
	 * @param  int $sheet_id
	 *
	 * @return Google_Service_Sheets_Request
	 */
	public static function create_request_write_column( $column_index, $value, $feed_id, $sheet_id = 0 ) {
		$metadata = new Google_Service_Sheets_DeveloperMetadata();
		$metadata->setLocation( self::create_location( 'COLUMNS', $column_index, $sheet_id ) );
		$metadata->setMetadataKey( self::column_key( $feed_id ) );
		$metadata->setMetadataValue( (string) $value );
		$metadata->setVisibility( 'DOCUMENT' ); //Let any app see this metadata

		$create = new Google_Service_Sheets_CreateDeveloperMetadataRequest();
		$create->setDeveloperMetadata( $metadata );

		$request = new Google_Service_Sheets_Request();
		$request->setCreateDeveloperMetadata( $create );

		return $request;
	}

	/**
	 * create_write_row_single_request
	 *
	 * Creates a Google_Service_Sheets_Request that helps write a metadata key
	 * value pair on a row.
	 *
	 * @param  int $row_index zero-based
	 * @param  string $value
	 * @param  int $form_id
	 * @param  int $sheet_id
	 *
	 * @return Google_Service_Sheets_Request
	 */
	public static function create_request_write_row( $row_index, $value, $form_id, $sheet_id = 0 ) {
		$metadata = new Google_Service_Sheets_DeveloperMetadata();
		$metadata->setLocation( self::create_location( 'ROWS', $row_index, $sheet_id ) );
		$metadata->setMetadataKey( self::row_key( $form_id ) );
		$metadata->setMetadataValue( (string) $value );
		$metadata->setVisibility( 'DOCUMENT' ); //Let any app see this metadata

		$create = new Google_Service_Sheets_CreateDeveloperMetadataRequest();
		$create->setDeveloperMetadata( $metadata );

		$request = new Google_Service_Sheets_Request();
		$request->setCreateDeveloperMetadata( $create );

		return $request;
	}
}
