<?php
/**
 * @package gp-populate-anything-google-sheets
 * @copyright Copyright (c) 2021-2022, Gravity Wiz, LLC
 * @author Gravity Wiz <support@gravitywiz.com>
 * @license GPLv2
 * @link https://github.com/gravitywiz/gp-populate-anything-google-sheets
 */
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

use GP_Google_Sheets\Dependencies\League\Csv\Reader;

class GP_Google_Sheets_GPPA_Object_Type_Google_Sheet extends GPPA_Object_Type {

	public $query_runtime_cache;

	const GPGS_EMPTY_HEADER_FOUND = 'GPGS_EMPTY_HEADER_FOUND';

	public function __construct( $id ) {
		parent::__construct( $id );

		add_action( 'gppa_pre_object_type_query_gpgs_sheet', array( $this, 'add_filter_hooks' ) );
		add_action( 'wp_ajax_gpgs_gppa_get_sheets', array( $this, 'ajax_get_sheets' ) );
	}

	public function add_filter_hooks() {
		add_filter( 'gppa_object_type_gpgs_sheet_filter', array( $this, 'process_filter_default' ), 10, 2 );
	}

	public function get_object_id( $object, $primary_property_value = null ) {
		return sha1( json_encode( $object ) );
	}

	public function get_object_prop_value( $object, $prop ) {
		return rgar( $object, $prop );
	}

	public function get_label() {
		return esc_html__( 'Google Sheet', 'gp-populate-anything' );
	}

	public function get_primary_property() {
		return array(
			'id'       => 'sheet',
			'label'    => esc_html__( 'Sheet', 'gp-populate-anything' ),
			'callable' => array( $this, 'get_primary_property_callback' ),
		);
	}

	public function get_primary_property_callback() {
		/** @phpstan-ignore-next-line (GPPA_VERSION can vary) */
		if ( ! version_compare( GPPA_VERSION, '2.0.14', '>=' ) ) {
			return $this->get_all_spreadsheet_sheets();
		}

		return $this->get_spreadsheets();
	}

	/**
	 * Legacy callback for GPPA <2.0.14 that gets all spreadsheets and sheets in a single dropdown.
	 *
	 * @return array
	 */
	public function get_all_spreadsheet_sheets() {
		$transient_key = 'gpgs_gppa_sheets';
		$cached        = get_transient( $transient_key );

		if ( ! empty( $cached ) ) {
			return $cached;
		}

		$spreadsheets_drive_files = gp_google_sheets()->get_available_spreadsheets();

		/**
		 * @var GP_Google_Sheets\Dependencies\Google\Service\Sheets\Sheet[][] $sheets
		 */
		$sheets = array();

		$service      = GP_Google_Sheets_Authenticator::create_service( array() );
		$spreadsheets = GP_Google_Sheets_Authenticator::get_spreadsheets_resource( $service );

		foreach ( $spreadsheets_drive_files as $spreadsheets_drive_file ) {
			try {
				// Ensure the file type is a Google spreadsheet.
				if ( $spreadsheets_drive_file->getMimeType() !== 'application/vnd.google-apps.spreadsheet' ) {
					continue;
				}

				$spreadsheet = $spreadsheets->get( $spreadsheets_drive_file->id );

				$sheets[ $spreadsheets_drive_file->id ] = $spreadsheet->getSheets();
			} catch ( Exception $e ) {
				// Do nothing
			}
		}

		$sheet_options = array();

		foreach ( $sheets as $spreadsheet_drive_file_id => $spreadsheet_sheets ) {
			foreach ( $spreadsheet_sheets as $sheet ) {
				/**
				 * @var GP_Google_Sheets\Dependencies\Google\Service\Drive\DriveFile|null $spreadsheet_drive_file
				 */
				$spreadsheet_drive_file = rgar( $spreadsheets_drive_files, $spreadsheet_drive_file_id );

				if ( ! $spreadsheet_drive_file ) {
					continue;
				}

				$option_value = $spreadsheet_drive_file_id . '|' . $sheet->getProperties()->getSheetId();
				$option_label = $spreadsheet_drive_file->getName() . ' - ' . $sheet->getProperties()->title;

				$sheet_options[ $option_value ] = $option_label;
			}
		}

		/**
		 * Filter the number of seconds that sheet data should be cached for. Defaults to `60` seconds.
		 * Sheet data is the list of "Sheets" contained in a connected Google Spreadsheet.
		 *
		 * @param int $expiration_time The expiration time in seconds.
		 */
		$expiration_time = apply_filters( 'gpgs_gppa_cache_sheets_expiration', 60 );
		set_transient( $transient_key, $sheet_options, $expiration_time );

		return $sheet_options;
	}

	/**
	 * Gets an array of the spreadsheets in an associative array for the primary property if GPPA >= 2.0.14.
	 *
	 * @return array
	 */
	public function get_spreadsheets() {
		$spreadsheets_drive_files = gp_google_sheets()->get_available_spreadsheets();
		$spreadsheets             = array();

		foreach ( $spreadsheets_drive_files as $spreadsheets_drive_file ) {
			try {
				// Ensure the file type is a Google spreadsheet.
				if ( $spreadsheets_drive_file->getMimeType() !== 'application/vnd.google-apps.spreadsheet' ) {
					continue;
				}

				$spreadsheets[ $spreadsheets_drive_file->id ] = $spreadsheets_drive_file->getName();
			} catch ( Exception $e ) {
				// Do nothing
			}
		}

		return $spreadsheets;
	}

	/**
	 * Gets an array of the sheets in a spreadsheet in an associative array for the primary property.
	 *
	 * We utilize a custom Vue component to show both the spreadsheet and sheet dropdowns. They are then
	 * concatenated into a single value for the primary property.
	 *
	 * Used by GPPA >2.0.14.
	 */
	public function get_sheets( $spreadsheet_id ) {
		$service      = GP_Google_Sheets_Authenticator::create_service( array() );
		$spreadsheets = GP_Google_Sheets_Authenticator::get_spreadsheets_resource( $service );
		$spreadsheet  = $spreadsheets->get( $spreadsheet_id );

		$sheets = array();

		foreach ( $spreadsheet->getSheets() as $sheet ) {
			$sheets[ $sheet->getProperties()->getSheetId() ] = $sheet->getProperties()->title;
		}

		return $sheets;
	}

	/**
	 * AJAX callback to get the sheets for a spreadsheet.
	 *
	 * Used by GPPA >2.0.14.
	 */
	public function ajax_get_sheets() {
		if ( ! GFCommon::current_user_can_any( array( 'gravityforms_edit_forms' ) ) ) {
			wp_die( '-1' );
		}

		check_ajax_referer( 'gppa', 'security' );

		$spreadsheet_id = rgpost( 'spreadsheet_id' );

		$sheets        = $this->get_sheets( $spreadsheet_id );
		$sheet_options = array();

		foreach ( $sheets as $sheet_id => $title ) {
			$sheet_options[] = array(
				'label' => $title,
				'value' => $sheet_id,
			);
		}

		return wp_send_json( $sheet_options );
	}

	/**
	 * We store the primary property in the format of `spreadsheet_id|sheet_id` so we need a way to easily extract them.
	 *
	 * @param $primary_property
	 *
	 * @return array
	 */
	public function get_ids_from_primary_property( $primary_property ) {
		$sheet_parts = explode( '|', $primary_property );

		return array(
			'spreadsheet_id' => rgar( $sheet_parts, 0 ),
			'sheet_id'       => (int) rgar( $sheet_parts, 1 ),
		);
	}

	public function get_groups() {
		return array();
	}

	/**
	 * Queries the Google Sheet using the Google Visualization API Query Language
	 * https://developers.google.com/chart/interactive/docs/querylanguage
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function query_sheet( $args ) {
		$cache_key = sha1( json_encode( $args ) );

		if ( isset( $this->query_runtime_cache[ $cache_key ] ) ) {
			return $this->query_runtime_cache[ $cache_key ];
		}

		$transient_key = 'gpgs_gppa_query_' . $cache_key;
		$cached        = get_transient( $transient_key );

		if ( $cached !== false ) {
			return $cached;
		}

		try {
			$client       = GP_Google_Sheets_Authenticator::get_google_client( array() );
			$access_token = $client->getAccessToken();

			if ( is_array( $access_token ) ) {
				$access_token = $access_token['access_token'];
			}

			$url = add_query_arg(array(
				// `tq` must be encoded with rawurlencode, encodeURIComponent, etc. as that is what the gviz API expects.
				'tq'      => rawurlencode( $args['query'] ),
				'tqx'     => 'out:csv',
				'gid'     => $args['sheet_id'],
				/**
				 * We need to send the "headers" query param and set its value to either "0" or "1". Without it, the Google gviz API
				 * will "guess" what the header contents of a sheet should be instead of just simply using the first row as the headers.
				 * Sometimes guessing works correctly, but other times, it results in multiple rows getting concatenated together to form
				 * the header.
				 *
				 * See this issue for further reference:
				 * https://support.google.com/docs/thread/100365600/issue-with-google-visualization-api-query-language-and-non-numeric-data?hl=en
				 */
				'headers' => '1',
			), 'https://docs.google.com/spreadsheets/d/' . $args['spreadsheet_id'] . '/gviz/tq');

			$request = wp_remote_request(
				$url,
				array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $access_token,
					),
					/**
					 * Filter the request timeout in seconds for queries to the Google Gviz API. Defaults to `20` seconds.
					 *
					 * @param int $seconds The number of seconds to wait before timing out.
					 * @param array $args The arguments that were used to build the request.
					 * */
					'timeout' => apply_filters( 'gpgs_gppa_http_request_timeout', 20, $args ),
				)
			);

			if ( is_wp_error( $request ) ) {
				throw new Exception( $request->get_error_message() );
			}

			// Check the mime
			$mime = wp_remote_retrieve_header( $request, 'content-type' );

			if ( stripos( $mime, 'text/csv' ) === false ) {
				throw new Exception( 'Invalid mime type. This usually means the token is not valid for the selected spreadsheet.' );
			}

			$response = wp_remote_retrieve_body( $request );

			if ( empty( $response ) ) {
				throw new Exception( 'Empty response.' );
			}

			$reader = Reader::createFromString( $response );
			$reader->setHeaderOffset( 0 );

			/**
			 * Get header names and deduplicate them as duplicate header names
			 * will cause an exception to get thrown when calling getRecords().
			 * Then pass those deduplicated headers to getRecords() to avoid
			 * the exception.
			 */
			$header = $this->deduplicate_header_columns( $reader->getHeader() );
			$values = iterator_to_array( $reader->getRecords( $header ) );
			/**
			 * Remove values for any columns that don't have a name as we
			 * currently do not support unnamed columns.
			 */
			$values = array_map( function( $row ) {
				foreach ( $row as $header_name => $val ) {
					if ( str_contains( $header_name, self::GPGS_EMPTY_HEADER_FOUND ) ) {
						unset( $row[ $header_name ] );
					}
				}

				return $row;
			}, $values );

			/*
			 * Loop over all of the values and add in a "Row Number" column that is a unique number based on the
			 * JSON-encoded contents of the row.
			 */
			foreach ( $values as $index => $value ) {
				$values[ $index ]['Row Number'] = hexdec( sprintf( '%u', crc32( json_encode( $value ) ) ) );
			}

			$this->query_runtime_cache[ json_encode( $args ) ] = $values;

			/**
			 * Filter the number of seconds that data from a sheet should be cached for. Defaults to `60` seconds.
			 * This is the row/column data returned from a sheet.
			 *
			 * @param int $expiration_time The expiration time in seconds.
			 */
			$expiration_time = apply_filters( 'gpgs_gppa_cache_query_expiration', 60 );
			set_transient( $transient_key, $values, $expiration_time );

			return $values;
		} catch ( Exception $e ) {
			error_log( 'Unable to fetch from Google Sheets: ' . $e->getMessage() );
			gp_google_sheets()->log_error( __METHOD__ . '(): Unable to fetch from Google Sheets: ' . $e->getMessage() );

			return array();
		}
	}

	public function is_empty_string( $str ) {
		return preg_match( '/^\s*$/', $str ) === 1;
	}

	/**
	 * Given an array of header names, deduplicate any duplicate column names.
	 * For example, if two headers are named "Name", the second one will be renamed to "Name (1)",
	 * the third to "Name (2)", etc.
	 *
	 * @param array $header List of headers strings to deduplicate.
	 */
	public function deduplicate_header_columns( $header ) {
		$existing_column_names = array();
		$deduped_header        = array();

		foreach ( $header as $column_name ) {
			if ( $this->is_empty_string( $column_name ) ) {
				$column_name = self::GPGS_EMPTY_HEADER_FOUND;
			}

			$column_name = trim( $column_name );

			if ( ! isset( $existing_column_names[ $column_name ] ) ) {
				$existing_column_names[ $column_name ] = 0;
			}

			$existing_column_names[ $column_name ] = $existing_column_names[ $column_name ] + 1;

			if ( $existing_column_names[ $column_name ] > 1 ) {
				$dedup_count  = $existing_column_names[ $column_name ] - 1;
				$column_name .= " ($dedup_count)";
			}

			$deduped_header[] = $column_name;
		}

		return $deduped_header;
	}

	/**
	 * Gets a sheet from the primary property for the Google API to be used with methods like ->get().
	 */
	public function get_sheet( $primary_property ) {
		$service      = GP_Google_Sheets_Authenticator::create_service( array() );
		$spreadsheets = GP_Google_Sheets_Authenticator::get_spreadsheets_resource( $service );

		/** @var string */
		$spreadsheet_id = null;

		/** @var int */
		$sheet_id = null;

		$ids = $this->get_ids_from_primary_property( $primary_property );

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( $ids );

		$spreadsheet_sheets = $spreadsheets->get( $spreadsheet_id )->getSheets();

		// Find the sheet with the matching ID using $sheet->getProperties()->getSheetId().
		foreach ( $spreadsheet_sheets as $spreadsheet_sheet ) {
			if ( $spreadsheet_sheet->getProperties()->getSheetId() === $sheet_id ) {
				return $spreadsheet_sheet;
			}
		}

		return null;
	}

	/**
	 * Gets the columns for a sheet.
	 */
	public function get_columns( $primary_property ) {
		$transient_key = 'gpgs_gppa_columns_' . sanitize_key( $primary_property );
		$cached        = get_transient( $transient_key );

		if ( ! empty( $cached ) ) {
			return $cached;
		}

		$service             = GP_Google_Sheets_Authenticator::create_service( array() );
		$spreadsheets_values = GP_Google_Sheets_Authenticator::get_spreadsheets_values_resource( $service );

		/** @var string */
		$spreadsheet_id = null;

		/** @var int */
		$sheet_id = null;

		$ids = $this->get_ids_from_primary_property( $primary_property );

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( $ids );

		$sheet = $this->get_sheet( $primary_property );

		if ( ! $sheet ) {
			return array();
		}

		$sheet_name = $sheet->getProperties()->title;
		$response   = $spreadsheets_values->get( $spreadsheet_id, $sheet_name . '!1:1' );
		$values     = $response->getValues();

		if ( empty( $values ) ) {
			return array();
		}

		$columns = $values[0];
		$columns = $this->deduplicate_header_columns( $columns );
		/**
		 * Remove values for any columns that don't have a name as we
		 * currently do not support unnamed columns.
		 */
		$columns = array_filter( $columns, function( $str ) {
			return ! str_contains( $str, self::GPGS_EMPTY_HEADER_FOUND );
		} );

		$this->cache_columns( $primary_property, $columns );

		return $columns;
	}

	/**
	 * Caches the columns for a sheet.
	 *
	 * @param string $primary_property
	 * @param array  $columns
	 */
	public function cache_columns( $primary_property, $columns ) {
		$transient_key = 'gpgs_gppa_columns_' . sanitize_key( $primary_property );

		/**
		 * Filter the number of seconds that column data from a sheet should be cached for. Defaults to `1800` seconds (30 minutes).
		 *
		 * @param int $expiration_time The expiration time in seconds.
		 */
		$expiration_time = apply_filters( 'gpgs_gppa_cache_columns_expiration', MINUTE_IN_SECONDS * 30 );
		set_transient( $transient_key, $columns, $expiration_time );
	}

	/**
	 * @param string $primary_property
	 *
	 * @return array
	 */
	public function get_properties( $primary_property = null ) {
		$properties = array();

		if ( ! $primary_property ) {
			return array( $properties );
		}

		$columns = $this->get_columns( $primary_property );

		/**
		 * Extract column names from the first row.
		 */
		foreach ( $columns as $column ) {
			$properties[ $column ] = array(
				'label'     => $column,
				'value'     => $column,
				'orderby'   => true,
				'callable'  => '__return_empty_array',
				'operators' => array(
					'is',
					'isnot',
					'>',
					'>=',
					'<',
					'<=',
					'contains',
					'does_not_contain',
					'starts_with',
					'ends_with',
				),
			);
		}

		return $properties;
	}

	public function process_filter_default( $query_builder_args, $args ) {

		/** @var string|string[] */
		$filter_value = null;

		/** @var array */
		$filter = null;

		/** @var int */
		$filter_group_index = null;

		/** @var string */
		$property_id = null;

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( $args );

		$column_letter = $this->get_column_letter( $args['primary_property_value'], $property_id );

		$query_builder_args['where'][ $filter_group_index ][] = $this->build_gviz_where_clause( $column_letter, $filter['operator'], $filter_value );

		return $query_builder_args;
	}

	public function get_column_letter( $primary_property_id, $property_id ) {
		$columns = $this->get_columns( $primary_property_id );

		$column_index = array_search( $property_id, $columns );

		return GP_Google_Sheets_Writer::number_to_column_letters( $column_index + 1 );
	}

	public function build_gviz_where_clause( $column_letter, $operator, $value ) {
		global $wpdb;

		$clauses = array();

		/**
		 * We have to build our where clause based on a few factors and limitations of the Google gviz API.
		 *
		 * 1. if the value is numeric, we want to try and match the equivalent number and string value as
		 *    it could be stored as either.
		 * 2. If the value contains a single quote, we need to wrap it in double quotes.
		 * 3. If the value does not contain a single quote, default to single quotes.
		 *
		 *    Important Note:
		 *
		 *    The caveat with these last two parts is that the gviz API currenlty does not support querying
		 *    for values which contain both a single (') and double (") quote. This is a limitation of the
		 *    gviz API and not this plugin but does effect the end user as this prevents the plugin from also
		 *    supporting values with single and double quotes.
		 */
		if ( is_numeric( $value ) ) {
			// Multiple clauses for numeric values because they can be stored as a number or a string.
			$clauses[] = sprintf( '%s %s %s', $column_letter, $this->get_gviz_operator( $operator ), $wpdb->prepare( '%d', $value ) );
			$clauses[] = sprintf( '%s %s %s', $column_letter, $this->get_gviz_operator( $operator ), $wpdb->prepare( '%s', $value ) );
		} elseif ( str_contains( $value, "'" ) ) {
			$prepared_value = "\"{$value}\"";
			$clauses[]      = sprintf( '%s %s %s', $column_letter, $this->get_gviz_operator( $operator ), $prepared_value );
		} else {
			$prepared_value = "'{$value}'";
			$clauses[]      = sprintf( '%s %s %s', $column_letter, $this->get_gviz_operator( $operator ), $prepared_value );
		}

		if ( $operator === 'does_not_contain' ) {
			foreach ( $clauses as &$clause ) {
				$clause = "NOT {$clause}";
			}
		}

		foreach ( $clauses as &$clause ) {
			$clause = $wpdb->remove_placeholder_escape( $clause );
		}

		$clause = implode( ' OR ', $clauses );

		if ( count( $clauses ) > 1 ) {
			$clause = '(' . $clause . ')';
		}

		return $clause;
	}

	public function get_gviz_operator( $operator ) {

		switch ( $operator ) {
			case 'starts_with':
				return 'starts with';

			case 'ends_with':
				return 'ends with';

			case 'contains':
			case 'does_not_contain':
				return 'contains';

			case 'is':
				return '=';

			case 'isnot':
				return '!=';

			default:
				return $operator;
		}

	}

	public function build_gviz_query( $query_args, $primary_property_value ) {
		global $wpdb;

		$query = array();

		$select = implode( ', ', $query_args['select'] );

		$query[] = "SELECT {$select}";

		if ( ! empty( $query_args['where'] ) ) {
			$where_clauses = array();

			foreach ( $query_args['where'] as $where_or_grouping => $where_or_grouping_clauses ) {
				$where_clauses[] = '(' . implode( ' AND ', $where_or_grouping_clauses ) . ')';
			}

			$query[] = "WHERE \n" . implode( "\n OR ", $where_clauses );
		}

		if ( ! empty( $query_args['order_by'] ) && ! empty( $query_args['order'] ) ) {
			$order_by = $this->get_column_letter( $primary_property_value, $query_args['order_by'] );
			$order    = $query_args['order'];

			// @todo make sure rand doesn't show
			$query[] = "ORDER BY {$order_by} {$order}";
		}

		if ( ! isset( $query_args['limit'] ) ) {
			$query_args['limit'] = gp_populate_anything()->get_query_limit( $this );
		}

		if ( $query_args['limit'] ) {
			$query[] = "LIMIT {$query_args['limit']}";
		}

		return implode( "\n", $query );
	}

	public function query( $args ) {
		// We intentionally use process_filter_groups() here to maintain backwards compatibility with GPPA <2.0.
		$query_builder_args = $this->process_filter_groups( $args, array(
			'select'   => array( '*' ),
			'where'    => array(),
			'order_by' => rgars( $args, 'ordering/orderby' ),
			'order'    => rgars( $args, 'ordering/order', 'ASC' ),
		) );

		$query = $this->build_gviz_query( $query_builder_args, $args['primary_property_value'] );

		$ids = $this->get_ids_from_primary_property( $args['primary_property_value'] );

		$results = $this->query_sheet( array(
			'query'          => $query,
			'spreadsheet_id' => $ids['spreadsheet_id'],
			'sheet_id'       => $ids['sheet_id'],
		) );

		// If there are results, cache the columns for the sheet.
		if ( ! empty( $results ) ) {
			$first_row = current( $results );

			if ( ! empty( $first_row ) ) {
				$this->cache_columns( $args['primary_property_value'], array_keys( $first_row ) );
			}
		}

		return $results;

	}

}
