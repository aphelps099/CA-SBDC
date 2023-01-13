<?php

GFForms::include_feed_addon_framework();

if ( ! class_exists( 'Gravity_Forms_Neoserra_Feed_Add_On' ) ) {
	class Gravity_Forms_Neoserra_Feed_Add_On extends GFFeedAddOn {


		protected $_version = GRAVITY_FORMS_NEOSERRA_ADD_ON_VERSION;
		protected $_min_gravityforms_version = '1.9';
		protected $_slug = 'neoserra-add-on';
		protected $_path = GRAVITY_FORMS_NEOSERRA_ADD_ON_PATH_DIR . '/class-gravity-forms-neoserra-add-on.php';
		protected $_full_path = __FILE__;
		protected $_title = 'Gravity Forms Neoserra Add-On';
		protected $_short_title = 'Neoserra';


		protected static $export_column_map = array(
			'center_id'                       => 'Center ID',
			'date'                            => 'Date',
			'pc_field_map_first_name'         => 'Primary Contact First Name',
			'pc_field_map_last_name'          => 'Primary Contact Last Name',
			'pc_field_map_email'              => 'PC Email',
			'pc_field_map_phone'              => 'PC Phone number',
			'pc_field_map_street_address'     => 'Physical Street Address',
			'pc_field_map_city'               => 'PC Address City',
			'pc_field_map_state'              => 'PC Address State',
			'pc_field_map_zip'                => 'PC Address ZIP Code',
			'bd_field_map_initial_status'     => 'Company Initial Status',
			'bd_field_map_name'               => 'Company Name',
			'bd_field_map_date_established'   => 'Date Company Established',
			'bd_field_map_street_address'     => 'CO Physical Street Address',
			'bd_field_map_city'               => 'CO Physical Address City',
			'bd_field_map_state'              => 'CO Physical Address State',
			'bd_field_map_zip'                => 'CO Physical Address ZIP Code',
			'bd_field_map_pc_position'        => 'PC Position',
			'bd_field_map_description'        => 'Product or Service Description',
			'bd_field_map_idea'               => 'Product or Service Description',
			'other_field_map_referral'        => 'Referral From',
			'other_field_map_privacy_release' => 'Permit SBA Survey',
			'other_field_map_consent'         => '641 Agreement (Consent)',
			'other_field_map_referral_desc'   => 'Referral From Description'
		);

	
		private static $_instance = null;


		public function init() {
			parent::init();
	
			add_filter( 'gform_notification_events', array( &$this, 'add_neoserra_api_error_event' ) );

			add_filter( 'gform_custom_merge_tags', array( &$this, 'add_custom_merge_tags' ), 10, 4);
			add_filter( 'gform_replace_merge_tags', array( &$this, 'replace_custom_merge_tags' ), 10, 7 );

		}
	

		public static function get_instance() {
			if ( self::$_instance == null ) {
				self::$_instance = new self();
			}
	
			return self::$_instance;
		}


		public function feed_settings_fields() {
			return array(
				array(
					'title'  => esc_html__( 'Neoserra Settings', 'gfneoserra' ),
					'fields' => array(
						array(
							'name'     => 'feedName',
							'label'    => esc_html__( 'Name', 'gfneoserra' ),
							'type'     => 'text',
							'required' => true,
							'class'    => 'medium',
							'tooltip'  => sprintf(
								'<h6>%s</h6>%s',
								esc_html__( 'Name', 'gfneoserra' ),
								esc_html__( 'Enter a feed name to uniquely identify this setup.', 'gfneoserra' )
							),
						),
						array(
							'label'   => esc_html__( 'Center ID', 'gfneoserra' ),
							'type'    => 'text',
							'name'    => 'center_id',
							'tooltip' => esc_html__( 'Provide the center ID to send this Neoserra data to.', 'gfneoserra' ),
							'required' => true
						),
						array(
							'label' => esc_html__( 'Primary Contact Fields', 'gfneoserra' ),
							'type' => 'field_map',
							'name' => 'pc_field_map',
							'field_map' => $this->get_primary_contact_field_map(),
							'tooltip'   => '<h6>' . esc_html__( 'Map Fields', 'gfneoserra' ) . '</h6>' . esc_html__( 'Select which Gravity Form fields pair with their respective Neoserra fields.', 'gfneoserra' )
						),
						array(
							'label' => esc_html__( 'Business Details Fields', 'gfneoserra' ),
							'type' => 'field_map',
							'name' => 'bd_field_map',
							'field_map' => $this->get_business_details_field_map(),
							'tooltip'   => '<h6>' . esc_html__( 'Map Fields', 'gfneoserra' ) . '</h6>' . esc_html__( 'Select which Gravity Form fields pair with their respective Neoserra fields.', 'gfneoserra' )
						),
						array(
							'label' => esc_html__( 'Other Fields', 'gfneoserra' ),
							'type' => 'field_map',
							'name' => 'other_field_map',
							'field_map' => $this->get_other_field_map(),
							'tooltip'   => '<h6>' . esc_html__( 'Map Fields', 'gfneoserra' ) . '</h6>' . esc_html__( 'Select which Gravity Form fields pair with their respective Neoserra fields.', 'gfneoserra' )
						)
					)
				)
			);
		}


		public function get_primary_contact_field_map() {
			return array(
				array(
					'name' => 'first_name',
					'label' => esc_html__( 'First Name', 'gfneoserra' ),
					'required' => true,
					'field_type' => array( 'name', 'text', 'hidden' ),
					'default_value' => $this->get_first_field_by_type( 'name', 3 )
				),
				array(
					'name' => 'last_name',
					'label' => esc_html__( 'Last Name', 'gfneoserra' ),
					'required' => true,
					'field_type' => array( 'name', 'text', 'hidden' ),
					'default_value' => $this->get_first_field_by_type( 'name', 5 )
				),
				array(
					'name' => 'email',
					'label' => esc_html__( 'Email', 'gfneoserra' ),
					'required' => true,
					'field_type' => array( 'email', 'hidden' ),
					'default_value' => $this->get_first_field_by_type( 'email' )
				),
				array(
					'name' => 'phone',
					'label' => esc_html__( 'Phone Number', 'gfneoserra' ),
					'required' => true,
					'field_type' => array( 'phone', 'hidden' ),
					'default_value' => $this->get_first_field_by_type( 'phone' )
				),
				array(
					'name' => 'street_address',
					'label' => esc_html__( 'Street Address', 'gfneoserra' ),
					'required' => true,
					'field_type' => array( 'address', 'hidden' ),
					'default_value' => $this->get_first_field_by_type( 'address', 1 )
				),
				array(
					'name' => 'city',
					'label' => esc_html__( 'City', 'gfneoserra' ),
					'required' => true,
					'field_type' => array( 'address', 'hidden' ),
					'default_value' => $this->get_first_field_by_type( 'address', 3 )
				),
				array(
					'name' => 'state',
					'label' => esc_html__( 'State', 'gfneoserra' ),
					'required' => true,
					'field_type' => array( 'address', 'hidden' ),
					'default_value' => $this->get_first_field_by_type( 'address', 4 )
				),
				array(
					'name' => 'zip',
					'label' => esc_html__( 'Zip', 'gfneoserra' ),
					'required' => true,
					'field_type' => array( 'address', 'hidden' ),
					'default_value' => $this->get_first_field_by_type( 'address', 5 )
				)
			);
		}


		public static function get_business_details_field_map() {
			return array(
				array(
					'name' => 'initial_status',
					'label' => esc_html__( 'Initial Status', 'gfneoserra' ),
					'required' => true,
					'field_type' => array( 'radio', 'select', 'hidden' )
				),
				array(
					'name' => 'name',
					'label' => esc_html__( 'Company Name', 'gfneoserra' ),
					'required' => true,
					'field_type' => array( 'text', 'hidden' )
				),
				array(
					'name' => 'date_established',
					'label' => esc_html__( 'Date Established', 'gfneoserra' ),
					'required' => true,
					'field_type' => array( 'date', 'text', 'hidden' )
				),
				array(
					'name' => 'street_address',
					'label' => esc_html__( 'Street Address', 'gfneoserra' ),
					'required' => true,
					'field_type' => array( 'address', 'hidden' )
				),
				array(
					'name' => 'city',
					'label' => esc_html__( 'City', 'gfneoserra' ),
					'required' => true,
					'field_type' => array( 'address', 'hidden' )
				),
				array(
					'name' => 'state',
					'label' => esc_html__( 'State', 'gfneoserra' ),
					'required' => true,
					'field_type' => array( 'address', 'hidden' )
				),
				array(
					'name' => 'zip',
					'label' => esc_html__( 'Zip', 'gfneoserra' ),
					'required' => true,
					'field_type' => array( 'address', 'hidden' )
				),
				array(
					'name' => 'pc_position',
					'label' => esc_html__( 'Primary Contact Position', 'gfneoserra' ),
					'required' => true,
					'field_type' => array( 'text', 'select', 'hidden' )
				),
				array(
					'name' => 'description',
					'label' => esc_html__( 'Business Description', 'gfneoserra' ),
					'required' => true,
					'field_type' => array( 'text', 'textarea', 'hidden' )
				),
				array(
					'name' => 'idea',
					'label' => esc_html__( 'Business Idea', 'gfneoserra' ),
					'required' => true,
					'field_type' => array( 'text', 'textarea', 'hidden' )
				),
			);
		}


		public static function get_other_field_map() {
			return array(
				array(
					'name' => 'program',
					'label' => esc_html__( 'Program', 'gfneoserra' ),
					'required' => true,
					'field_type' => array( 'text', 'select', 'hidden' ),
					'tooltip' => esc_html__( 'Use this field to map to a Neoserra center ID to override the default one provided above.', 'gfneoserra' ),
				),
				array(
					'name' => 'referral',
					'label' => esc_html__( 'Referral', 'gfneoserra' ),
					'required' => true,
					'field_type' => array( 'text', 'select', 'hidden' )
				),
				array(
					'name' => 'referral_desc',
					'label' => esc_html__( 'Referral Description (other)', 'gfneoserra' ),
					'required' => false,
					'field_type' => array( 'text', 'select', 'hidden' )
				),
				array(
					'name' => 'privacy_release',
					'label' => esc_html__( 'Privacy Release', 'gfneoserra' ),
					'required' => true,
					'field_type' => array( 'radio', 'hidden' )
				),
				array(
					'name' => 'consent',
					'label' => esc_html__( 'Consent', 'gfneoserra' ),
					'required' => true,
					'field_type' => array( 'text', 'hidden' )
				)
			);
		}


		public function feed_list_columns() {
			return array(
				'feedName' => __( 'Name', 'gfneoserra' ),
				'center_id'   => __( 'Center Id', 'gfneoserra' )
			);
		}


		public function process_feed( $feed, $entry, $form ) {

			$export_id = $entry['id'] . '-' . date( 'Ymd-His' ) . '-'. random_int( 100000, 999999 );

			// generate csv lines
			$csv_rows = array();

			$headers = self::$export_column_map;
			unset( $headers['bd_field_map_idea'] );
			$csv_rows[] = array_values( $headers );

			$center_id = $feed['meta']['center_id'];
			$queried_program = $this->get_field_value( $form, $entry, $feed['meta']['other_field_map_program'] );
			if ( ! empty( $queried_program ) && class_exists( 'Crown_Site_Settings_Signup' ) ) {
				$program = Crown_Site_Settings_Signup::get_program( $queried_program );
				if ( $program && !empty( trim( $program->neoserra_center_id ) ) ) {
					$center_id = trim( $program->neoserra_center_id );
				}
			}

			$row = array();
			foreach ( self::$export_column_map as $key => $label ) {
				$k = $key;
				$v = '';
				if ( in_array( $k, array( 'bd_field_map_idea' ) ) ) {
					continue;
				} else if ( $k == 'center_id' ) {
					$v = $center_id;
				} else if ( $k == 'date' ) {
					$v = $entry['date_created'];
				} else if ( array_key_exists( $k, $feed['meta'] ) ) {
					$v = $this->get_field_value( $form, $entry, $feed['meta'][ $k ] );
					if ( $k == 'bd_field_map_description' && empty( $v ) ) {
						$k = 'bd_field_map_idea';
						if ( array_key_exists( $k, $feed['meta'] ) ) {
							$v = $this->get_field_value( $form, $entry, $feed['meta'][ $k ] );
						}
					}
				}
				if ( $k == 'other_field_map_referral_desc' ) {
					$v = substr( $v, 0, 80 );
				}
				$row[] = $v;
			}
			$csv_rows[] = $row;

			$lines = array();
			foreach ( $csv_rows as $row ) {
				$lines[] = $this->str_putcsv( $row );
			}
			$lines = implode( "\n", $lines );
			// print_r($lines);

			// export to csv file
			require_once( GFCommon::get_base_path() . '/export.php' );
			GFExport::write_file( $lines, $export_id );

			$export_folder = RGFormsModel::get_upload_root() . 'export/';
			$file = $export_folder . sanitize_file_name( 'export-' . $export_id . '.csv' );

			// echo ' '.$file;
			// die;

			// send to neoserra
			$statuses = $this->export_to_neoserra( $file );

			// handle errors
			$error_messages = array();
			if ( empty( $statuses ) ) {
				$name = $this->get_field_value( $form, $entry, $feed['meta']['pc_field_map_first_name'] ) . ' ' . $this->get_field_value( $form, $entry, $feed['meta']['pc_field_map_last_name'] );
				$message = 'Unable to add contact: ' . $name;
				$message .= ' (API: No response.)';
				$error_messages[] = $message;
			}
			foreach ( $statuses as $status ) {
				if ( ! isset( $status['Status'] ) || $status['Status'] != 'P' ) {
					$name = $this->get_field_value( $form, $entry, $feed['meta']['pc_field_map_first_name'] ) . ' ' . $this->get_field_value( $form, $entry, $feed['meta']['pc_field_map_last_name'] );
					$message = 'Unable to add contact: ' . $name;
					if ( isset( $status['Message'] ) ) $message .= ' (' . $status['Message'] . ')';
					$error_messages[] = $message;
				}
			}
			foreach ( $error_messages as $error_message ) {
				$this->add_feed_error( $error_message, $feed, $entry, $form );
			}
			if ( ! empty( $error_messages ) ) {
				gform_update_meta( $entry['id'], 'neoserra_api_errors', $error_messages );
				GFAPI::send_notifications( $form, $entry, 'neoserra_api_error' );
			}

			unlink( $file );
			return;
		}


		protected function str_putcsv( $input, $delimiter = ',', $enclosure = '"' ) {
			$fp = fopen('php://temp', 'r+');
			fputcsv($fp, $input, $delimiter, $enclosure);
			rewind($fp);
			$data = fread($fp, 1048576);
			fclose($fp);
			return rtrim($data, "\n");
		}


		protected function export_to_neoserra( $file ) {

			$api_url = 'https://norcal.neoserra.com/api/';
			$endpoint = 'import';

			$params = array_merge( array(
				'userid' => NEOSERRA_API_USER_ID,
				'appid' => NEOSERRA_API_APP_ID,
				'appkey' => NEOSERRA_API_APP_KEY
			), array() );

			foreach($params as $k => $v) {
				if(is_bool($v)) $v = $v ? 'true' : 'false';
				$params[$k] = $v;
			}

			$url = $api_url . $endpoint . '?' . http_build_query( $params );

			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch, CURLOPT_POST, 1 );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, file_get_contents( $file ) );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 
				'User-Agent: WordPress Library for Neoserra API',
				'Content-Type: multipart/form-data'
			) );
			$response = curl_exec( $ch );
			$code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
			curl_close( $ch );

			$statuses = array();
			if ( $code == 200 ) {

				$response_csv = array_map( 'str_getcsv', str_getcsv( $response, "\n" ) );
				array_walk( $response_csv, function( &$a ) use ( $response_csv ) {
					$a = array_combine( $response_csv[0], $a );
				});
				array_shift( $response_csv );

				$statuses = array_values( $response_csv );

			}
			return $statuses;

		}


		public function add_neoserra_api_error_event( $notification_events ) {
			$notification_events['neoserra_api_error'] = __( 'Neoserra API Error', 'gfneoserra' );
			return $notification_events;
		}

		
		public function add_custom_merge_tags( $merge_tags, $form_id, $fields, $element_id ) {
			$merge_tags[] = array( 'label' => 'Neoserra API Errors', 'tag' => '{neoserra_api_errors}' );
			return $merge_tags;
		}


		public function replace_custom_merge_tags( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {
			$merge_tag = '{neoserra_api_errors}';
			if ( strpos( $text, $merge_tag ) === false ) {
				return $text;
			} else {
				$errors = gform_get_meta( $entry['id'], 'neoserra_api_errors' );
				if ( ! empty( $errors ) ) {
					$text = str_replace( $merge_tag, implode( "\n", $errors ), $text );
				}
			}
			return $text;
		}


	}
}