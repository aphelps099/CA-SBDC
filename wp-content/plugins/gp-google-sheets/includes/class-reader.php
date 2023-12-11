<?php
/**
 * @package gp-google-sheets
 * @copyright Copyright (c) 2022, Gravity Wiz, LLC
 * @author Gravity Wiz <support@gravitywiz.com>
 * @license GPLv2
 * @link https://gravitywiz.com/documentation/gravity-forms-google-sheets/
 */
defined( 'ABSPATH' ) or exit;

use \GP_Google_Sheets\Dependencies\Google\Service\Sheets as Google_Service_Sheets;
use \GP_Google_Sheets\Dependencies\Google\Service\Sheets\DeveloperMetadataLookup as Google_Service_Sheets_DeveloperMetadataLookup;
use \GP_Google_Sheets\Dependencies\Google\Service\Sheets\DataFilter as Google_Service_Sheets_DataFilter;
use \GP_Google_Sheets\Dependencies\Google\Service\Sheets\SearchDeveloperMetadataRequest as Google_Service_Sheets_SearchDeveloperMetadataRequest;

/**
 * This class finds row and column indexes in a Google Sheet row using cell
 * values and developer metadata.
 */
class GP_Google_Sheets_Reader {

	/**
	 * find_first_empty_column_index
	 *
	 * @param  mixed $feed
	 * @return int|false Returns 0-based column index, -1 if there are no empty columns, or false if cannot connect
	 */
	public static function find_first_empty_column_index( $feed ) {
		$service = GP_Google_Sheets_Authenticator::create_service( $feed );
		if ( is_wp_error( $service ) ) {
			return false;
		}
		//We can use "Sheet1" as a range
		$spreadsheet_id = GP_Google_Sheets_Writer::spreadsheet_id( $feed );
		$tab            = self::find_tab( $service, $spreadsheet_id, $feed );
		if ( $tab !== false && ! $tab instanceof \Exception ) {
			//read_range() doesn't return empty cells
			$cells        = self::read_range( $service, $spreadsheet_id, $tab->tab_name );
			$column_count = self::column_count( $service, $spreadsheet_id, $tab->sheet_id );

			//Are there any columns that have no value in row 1?
			if ( is_array( $cells ) ) {
				if ( sizeof( $cells[0] ) == sizeof( array_filter( $cells[0] ) )
					&& sizeof( $cells[0] ) == $column_count ) {
					//No
					return -1;
				}

				for ( $c = 0; $c < sizeof( $cells[0] ); $c++ ) {
					$column_is_empty = true;
					if ( isset( $cells[0][ $c ] ) && $cells[0][ $c ] == '' ) {
						//$c has no value in row 1, check the rest of the rows
						for ( $r = 0; $r < sizeof( $cells ); $r++ ) {
							//Is this row empty in column $c?
							if ( isset( $cells[ $r ][ $c ] ) && $cells[ $r ][ $c ] != '' ) {
								//No, there's a value in row $r+1
								$column_is_empty = false;
								break;
							}
						}
						if ( $column_is_empty ) {
							return $c;
						}
					}
				}
				if ( sizeof( $cells[0] ) < $column_count ) {
					return sizeof( $cells[0] );
				}
			} elseif ( $column_count > 0 ) {
				//Blank sheet with empty columns
				//If sheet is brand new, $column_count is 26
				return 0;
			}
		}
		return -1;
	}

	/**
	 * find_row_by_metadata_value
	 *
	 * Finds the zero-based row index that contains the provided value.
	 *
	 * @param  Google_Service_Sheets $service
	 * @param  string $spreadsheet_id A Google Sheet unique ID, visible in the URL
	 * @param  string $value The metadata value to find
	 * @return int|false Zero-based row index
	 *
	 * @throws \Exception
	 */
	public static function find_row_by_metadata_value( $service, $spreadsheet_id, $form_id, $value ) {
		$metadata = self::metadata_map_rows( $service, $spreadsheet_id, $form_id );

		if ( ! $metadata ) {
			return false;
		}

		foreach ( $metadata as $index => $entry_id ) {
			if ( $value == $entry_id ) {
				return $index;
			}
		}

		return false;
	}

	/**
	 *
	 * Find the current name of the tab to which we write data. Starts as the
	 * default "Sheet1" when we create the Sheet, but the user could rename or
	 * move it.
	 *
	 * @param  Google_Service_Sheets $service
	 * @param  string $spreadsheet_id A Google Sheet unique ID, visible in the URL
	 * @return GP_Google_Sheets_Tab|\Exception|false
	 */
	public static function find_tab( $service, $spreadsheet_id, $feed ) {
		// Check the transient for the tab. We cache it for 1 minute as the likelihood of the tab changing is quite low.
		$transient_key = 'gspc_tab_' . $feed['id'];
		$cached        = get_transient( $transient_key );

		if ( $cached ) {
			return $cached;
		}

		// Get the API client and construct the service object.
		if ( empty( $service->spreadsheets ) ) {
			return false;
		}

		$tab = new GP_Google_Sheets_Tab();

		$spreadsheets = GP_Google_Sheets_Authenticator::get_spreadsheets_resource( $service );

		try {
			$response = $spreadsheets->get( $spreadsheet_id );
		} catch ( \Exception $ex ) {
			GP_Google_Sheets::get_instance()->log_error( __METHOD__ . '(): Could not find Sheet. ' . $ex->getMessage() );
			/**
			 * Hit this error by getting a good token, sabotaging the client ID
			 * & secret, and editing a form
			 *
			 * PHP Fatal error:  Uncaught Google\Service\Exception: {
			 *  "error": "invalid_client",
			 *  "error_description": "The OAuth client was not found."
			 * }
			 *
			 */
			return $ex;
		}

		$sheets         = $response->getSheets();
		$saved_sheet_id = rgar( $feed['meta'], 'google_sheet_id' );

		// If the sheet was created with the "legacy authentication" system,
		// we will not have a sheet id to match against and should thus skip this
		// next matching process.
		if ( $saved_sheet_id !== '' && $saved_sheet_id !== null ) {
			// if a specific sheet has been chosen by the user, then its id will be in the feed meta table
			// and we should use that to find the sheet.
			foreach ( $sheets as $sheet ) {
				$properties = $sheet->getProperties();
				$sheet_id   = $properties->getSheetId();

				if ( $sheet_id != $saved_sheet_id ) {
					continue;
				}

				$tab->sheet_id = $sheet_id;
				$tab->tab_name = $properties->getTitle();

				//Total row count is in the grid properties
				$grid_properties = $properties->getGridProperties();
				$tab->row_count  = $grid_properties->getRowCount();

				set_transient( $transient_key, $tab, MINUTE_IN_SECONDS );

				return $tab;
			}
		}

		/**
		 * Try to find the sheet that has the metadata key linked to the feed.
		 *
		 * NOTE: this is now considered legacy and considered "deprecated" as we store the sheet ID in the feed meta.
		 */
		foreach ( $sheets as $sheet ) {
			if ( empty( $sheet->getDeveloperMetadata() ) ) {
				continue;
			}

			foreach ( $sheet->getDeveloperMetadata() as $metadata ) {
				if ( GP_Google_Sheets_Metadata_Writer::SHEET_KEY != $metadata->getMetadataKey() ) {
					continue;
				}

				//This is our tab, what's the sheet ID?
				$location      = $metadata->getLocation();
				$tab->sheet_id = $location->getSheetId();

				//Tab name is saved in the properties
				$properties    = $sheet->getProperties();
				$tab->tab_name = $properties->getTitle();

				//Total row count is in the grid properties
				$grid_properties = $properties->getGridProperties();
				$tab->row_count  = $grid_properties->getRowCount();

				set_transient( $transient_key, $tab, MINUTE_IN_SECONDS );

				return $tab;
			}
		}

		/**
		 * If we still don't have a tab, search for Sheet1
		 *
		 * NOTE: this is now considered legacy and considered "deprecated" as we store the sheet ID in the feed meta.
		 */
		foreach ( $sheets as $sheet ) {
			$properties    = $sheet->getProperties();
			$tab->tab_name = $properties->getTitle();

			if ( $tab->tab_name === 'Sheet1' ) {
				$tab->sheet_id = $properties->getSheetId();

				//Total row count is in the grid properties
				$grid_properties = $properties->getGridProperties();
				$tab->row_count  = $grid_properties->getRowCount();

				set_transient( $transient_key, $tab, MINUTE_IN_SECONDS );

				return $tab;
			}
		}

		// If that STILL doesn't work, pick the first tab.
		foreach ( $sheets as $sheet ) {
			$properties    = $sheet->getProperties();
			$tab->tab_name = $properties->getTitle();
			$tab->sheet_id = $properties->getSheetId();

			//Total row count is in the grid properties
			$grid_properties = $properties->getGridProperties();
			$tab->row_count  = $grid_properties->getRowCount();

			set_transient( $transient_key, $tab, MINUTE_IN_SECONDS );

			return $tab;
		}

		set_transient( $transient_key, false, MINUTE_IN_SECONDS );

		return false;
	}

	/**
	 * metadata_field_map
	 *
	 * Creates an associative array where the keys are column indices and the
	 * values are Gravity Forms field IDs. Used to rearrange arrays of row data
	 * before writes in case the position of columns has been changed in the
	 * Sheet.
	 *
	 * @param  Google_Service_Sheets $service
	 * @param  string $spreadsheet_id A Google Sheet unique ID, visible in the URL
	 * @return array|false Associative array with column index keys and Gravity Forms field ID values
	 *
	 * @throws \Exception
	 */
	public static function metadata_field_map( $service, $spreadsheet_id, $feed_id ) {
		if ( empty( $service->spreadsheets_developerMetadata ) ) {
			return false;
		}

		static $metadata_field_map_cache = array();

		if ( ! empty( $feed_id ) && rgar( $metadata_field_map_cache, $feed_id ) ) {
			return rgar( $metadata_field_map_cache, $feed_id );
		}

		$lookup_legacy = new Google_Service_Sheets_DeveloperMetadataLookup();
		$lookup_legacy->setMetadataKey( GP_Google_Sheets_Metadata_Writer::column_key_legacy( $feed_id ) );

		$dataFilter_legacy = new Google_Service_Sheets_DataFilter();
		$dataFilter_legacy->setDeveloperMetadataLookup( $lookup_legacy );

		$lookup = new Google_Service_Sheets_DeveloperMetadataLookup();
		$lookup->setMetadataKey( GP_Google_Sheets_Metadata_Writer::column_key( $feed_id ) );

		$dataFilter = new Google_Service_Sheets_DataFilter();
		$dataFilter->setDeveloperMetadataLookup( $lookup );

		$search = new Google_Service_Sheets_SearchDeveloperMetadataRequest();
		$search->setDataFilters( array( $dataFilter_legacy, $dataFilter ) );

		$developer_metadata = GP_Google_Sheets_Authenticator::get_spreadsheets_developer_metadata_resource( $service );

		/** @throws \Exception */
		$response = $developer_metadata->search( $spreadsheet_id, $search );

		$found_metadata = $response->getMatchedDeveloperMetadata();

		if ( empty( $found_metadata ) ) {
			$metadata_field_map_cache[ $feed_id ] = array();
			return array();
		}

		$metadata = array();
		foreach ( $found_metadata as $metadata_obj ) {
			$location     = $metadata_obj->getDeveloperMetadata()->getLocation();
			$range        = $location->getDimensionRange();
			$column_index = (int) $range->getStartIndex();
			$value        = $metadata_obj->getDeveloperMetadata()->getMetadataValue();

			if ( rgblank( $value ) ) {
				continue;
			}

			$metadata[ $column_index ] = strval( $metadata_obj->getDeveloperMetadata()->getMetadataValue() );
		}

		//debug
		//sort by array keys to make debugging easy
		ksort( $metadata );

		$metadata_field_map_cache[ $feed_id ] = $metadata;

		return $metadata;
	}

	/**
	 * metadata_map_rows
	 *
	 * Creates an associative array where the keys are row indices and the
	 * values are Gravity Forms entry IDs. Used to find entries in the sheet
	 * when editing or deleting.
	 *
	 * @param  Google_Service_Sheets $service
	 * @param  string $spreadsheet_id A Google Sheet unique ID, visible in the URL
	 * @return array|false Associative array with row index keys and Gravity Forms entry ID values
	 *
	 * @throws \Exception
	 */
	public static function metadata_map_rows( $service, $spreadsheet_id, $form_id ) {
		if ( empty( $service->spreadsheets_developerMetadata ) ) {
			return false;
		}

		$lookup_legacy = new Google_Service_Sheets_DeveloperMetadataLookup();
		$lookup_legacy->setMetadataKey( GP_Google_Sheets_Metadata_Writer::row_key_legacy( $form_id ) );

		$dataFilter_legacy = new Google_Service_Sheets_DataFilter();
		$dataFilter_legacy->setDeveloperMetadataLookup( $lookup_legacy );

		$lookup = new Google_Service_Sheets_DeveloperMetadataLookup();
		$lookup->setMetadataKey( GP_Google_Sheets_Metadata_Writer::row_key( $form_id ) );

		$dataFilter = new Google_Service_Sheets_DataFilter();
		$dataFilter->setDeveloperMetadataLookup( $lookup );

		$search = new Google_Service_Sheets_SearchDeveloperMetadataRequest();
		$search->setDataFilters( array( $dataFilter_legacy, $dataFilter ) );

		$developer_metadata = GP_Google_Sheets_Authenticator::get_spreadsheets_developer_metadata_resource( $service );

		/** @throws \Exception */
		$response = $developer_metadata->search( $spreadsheet_id, $search );

		$found_metadata = $response->getMatchedDeveloperMetadata();

		if ( empty( $found_metadata ) ) {
			return array();
		}

		$metadata = array();
		foreach ( $found_metadata as $metadata_obj ) {
			$location                                  = $metadata_obj->getDeveloperMetadata()->getLocation();
			$range                                     = $location->getDimensionRange();
			$metadata[ (int) $range->getStartIndex() ] = strval( $metadata_obj->getDeveloperMetadata()->getMetadataValue() );
		}

		//debug
		//sort by array keys to make debugging easy
		ksort( $metadata );

		return $metadata;
	}

	/**
	 * prepare_range
	 *
	 * Turns a range like "1:1" into "Sheet1!1:1" after connecting to the Sheet and
	 * finding the tab where our data is stored.
	 *
	 * @param  mixed $service
	 * @param  mixed $spreadsheet_id
	 * @param  mixed $range
	 * @return void|string
	 */
	public static function prepare_range( $service, $spreadsheet_id, $range, $feed ) {
		$tab = self::find_tab( $service, $spreadsheet_id, $feed );
		if ( ! $tab instanceof \Exception && ! empty( $tab->tab_name ) ) {
			$preamble = $tab->tab_name . '!';
			if ( $preamble != substr( $range, 0, strlen( $preamble ) ) ) {
				$range = $preamble . $range;
			}
		}
		return $range;
	}

	public static function column_count( $service, $spreadsheet_id, $sheet_id = 0 ) {
		if ( empty( $service->spreadsheets ) ) {
			return false;
		}

		$spreadsheets = GP_Google_Sheets_Authenticator::get_spreadsheets_resource( $service );

		try {
			$response = $spreadsheets->get( $spreadsheet_id );
		} catch ( \Exception $ex ) {
			return false;
		}

		if ( is_callable( array( $response, 'getSheets' ) ) ) {
			foreach ( $response->getSheets() as $sheet ) {
				$props = $sheet->getProperties();
				if ( $props->getSheetId() != $sheet_id ) {
					continue;
				}

				$grid_props = $props->getGridProperties();
				return $grid_props->getColumnCount();
			}
		}
		return false;
	}

	/**
	 * read_range
	 *
	 * Connects to a Sheet, reads a cell range, and returns a two-dimensional
	 * array of the values.
	 *
	 * @param  Google_Service_Sheets $service
	 * @param  string $spreadsheet_id A Google Sheet unique ID, visible in the URL
	 * @param  string $range A Google Sheet cell range like A1 or A1:B1 that describes where to write $rows
	 *
	 * @return array[]|false A two-dimensional array of values.
	 */
	public static function read_range( $service, $spreadsheet_id, $range ) {
		if ( empty( $service->spreadsheets_values ) ) {
			return false;
		}

		$spreadsheets_values = GP_Google_Sheets_Authenticator::get_spreadsheets_values_resource( $service );

		try {
			$result = $spreadsheets_values->get( $spreadsheet_id, $range );
		} catch ( \Exception $ex ) {
			return false;
		}

		return $result->getValues();
	}

	public static function get_spreadsheet_name( $service, $spreadsheet_id ) {
		// Get the API client and construct the service object.
		if ( empty( $service->spreadsheets ) ) {
			return false;
		}

		// Cache spreadsheet name for 5 minutes using a transient.
		$transient_key = 'gpgs_spreadsheet_name_' . $spreadsheet_id;
		$cached_name   = get_transient( $transient_key );

		if ( $cached_name ) {
			return $cached_name;
		}

		$spreadsheets = GP_Google_Sheets_Authenticator::get_spreadsheets_resource( $service );

		try {
			$response = $spreadsheets->get( $spreadsheet_id );
			$name     = $response->getProperties()->getTitle();
		} catch ( \Exception $ex ) {
			$name = false;
		}

		set_transient( $transient_key, $name, 5 * MINUTE_IN_SECONDS );
		return $name;
	}

	public static function get_spreadsheet_sheets( $service, $spreadsheet_id ) {
		$spreadsheets = GP_Google_Sheets_Authenticator::get_spreadsheets_resource( $service );

		try {
			$spreadsheet = $spreadsheets->get(
				$spreadsheet_id,
				array( 'fields' => '*' )
			);

			$sheets = array();
			foreach ( $spreadsheet->getSheets() as $sheet ) {
				$sheets[] = array(
					'title' => $sheet->getProperties()->getTitle(),
					'id'    => $sheet->getProperties()->getSheetId(),
				);
			}

			return $sheets;
		} catch ( \Exception $e ) {
			return array();
		}
	}
}
