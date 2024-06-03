<?php

GFForms::include_feed_addon_framework();

if ( ! class_exists( 'Gravity_Forms_Neoserra_Update_Client_Records_Feed_Add_On' ) ) {
	class Gravity_Forms_Neoserra_Update_Client_Records_Feed_Add_On extends GFFeedAddOn {


		protected $_version = GRAVITY_FORMS_NEOSERRA_UPDATE_CLIENT_RECORDS_ADD_ON_VERSION;
		protected $_min_gravityforms_version = '1.9';
		protected $_slug = 'neoserra-udpate-client-records-add-on';
		protected $_path = GRAVITY_FORMS_NEOSERRA_UPDATE_CLIENT_RECORDS_ADD_ON_PATH_DIR . '/class-gravity-forms-neoserra-udpate-client-records-add-on.php';
		protected $_full_path = __FILE__;
		protected $_title = 'Gravity Forms Neoserra Update Client Records Add-On';
		protected $_short_title = 'Neoserra Update Client Records';

		protected static $neoserra_dashboard_uri = 'https://norcal.neoserra.com/';
		protected static $session_neoserra_clients = array();


		private static $_instance = null;

		// X TODO: add email notification to center director when business start form is submitted with link to record(s) created
		// X TODO: add auto company status for business start
		// X TODO: multiple business handling, make radio selection stand out
		// X TODO: action fields to buttons
		// X TODO: provide link upon to completion to submit again


		public function init() {
			parent::init();
	
			add_filter( 'gform_notification_events', array( &$this, 'add_notification_events' ) );

			add_filter( 'gform_custom_merge_tags', array( &$this, 'add_custom_merge_tags' ), 10, 4);
			add_filter( 'gform_replace_merge_tags', array( &$this, 'replace_custom_merge_tags' ), 10, 7 );


			add_action( 'gform_pre_submission', function( $form ) {

				$email = null;
				$field_ids = array(
					'neoserra_contact_id' => null,
					'neoserra_client_id' => null
				);
				foreach ( $form['fields'] as $field ) {
					if ( ! $email && $field->type == 'email' ) {
						$email = rgpost( 'input_' . $field->id );
					}
					if ( array_key_exists( $field->inputName, $field_ids ) ) {
						$field_ids[ $field->inputName ] = $field->id;
					}
				}
				if ( empty( $email ) ) return;

				$contact_id = null;
				if ( $field_ids['neoserra_contact_id'] ) {
					$contact_search_response = Crown_Neoserra_Records_Api::get_contacts( array( 'email' => $email ) );
					$contact_id = is_object( $contact_search_response ) && property_exists( $contact_search_response, 'rows' ) && is_array( $contact_search_response->rows ) && ! empty( $contact_search_response->rows ) ? $contact_search_response->rows[0]->indivId : null;
					if ( $contact_id ) {
						$_POST[ 'input_' . $field_ids['neoserra_contact_id'] ] = $contact_id;
					}
				}

				$client_ids = array();
				if ( $field_ids['neoserra_client_id'] ) {
					if ( $contact_id ) {
						$client_search_response = Crown_Neoserra_Records_Api::get_clients( array( 'indiv_id' => $contact_id, 'columns' => implode( ',', array( 'clientId' ) ) ) );
						$client_ids = is_object( $client_search_response ) && property_exists( $client_search_response, 'rows' ) && is_array( $client_search_response->rows ) && ! empty( $client_search_response->rows ) ? array_map( function( $n ) { return $n->clientId; }, $client_search_response->rows ) : array();
					} else {
						$client_search_response = Crown_Neoserra_Records_Api::get_clients( array( 'email' => $email ) );
						$client_ids = is_object( $client_search_response ) && property_exists( $client_search_response, 'rows' ) && is_array( $client_search_response->rows ) && ! empty( $client_search_response->rows ) ? array_map( function( $n ) { return $n->clientId; }, $client_search_response->rows ) : array();
					}
					if ( $client_ids ) {
						$_POST[ 'input_' . $field_ids['neoserra_client_id'] ] = implode( ',', $client_ids );
					}
				}

			} );


			add_filter( 'gform_pre_render', function( $form, $ajax, $field_values ) {
				if ( empty( $form['id'] ) ) return $form;

				$field_indices = array(
					'neoserra_client_id' => null,
					'neoserra_client_ftEmps' => null,
					'neoserra_milestone_ns_amount' => null,
					'neoserra_client_ptEmps' => null,
					'neoserra_milestone_nspt_amount' => null,
					'neoserra_client_grossSales' => null,
					'neoserra_milestone_is_amount' => null,
				);
				foreach ( $form['fields'] as $i => $field ) {
					if ( array_key_exists( $field->inputName, $field_indices ) ) {
						$field_indices[ $field->inputName ] = $i;
					}
				}
				if ( ! $field_indices[ 'neoserra_client_id' ] ) return $form;
				if ( ! in_array( $form['fields'][ $field_indices[ 'neoserra_client_id' ] ]->type, array( 'radio', 'select' ) ) ) return $form;

				$contact_id = isset( $_GET['contact'] ) ? intval( $_GET['contact'] ) : null;
				if ( ! $contact_id ) return $form;

				$client_search_response = Crown_Neoserra_Records_Api::get_clients( array( 'indiv_id' => $contact_id, 'columns' => implode( ',', array(
					'clientId',
					'company',
					'ftEmps',
					'ptEmps',
					'grossSales'
				) ) ) );
				$clients = is_object( $client_search_response ) && property_exists( $client_search_response, 'rows' ) && is_array( $client_search_response->rows ) && ! empty( $client_search_response->rows ) ? $client_search_response->rows : array();
				if ( empty( $clients ) ) return $form;

				self::$session_neoserra_clients = $clients;

				$default_client = $clients[0];
				if ( isset( $_GET['client'] ) ) {
					foreach ( $clients as $client ) {
						if ( $_GET['client'] == $client->clientId ) {
							$default_client = $client;
						}
					}
				}

				// build list of client options
				$client_choices = array_map( function( $client ) {
					return array(
						'text' => $client->company,
						'value' => $client->clientId,
						'isSelected' => isset( $_GET['client'] ) && $_GET['client'] == $client->clientId
					);
				}, $clients );
				if ( count( $client_choices ) == 1 ) $client_choices[0]['isSelected'] = true;
				$form['fields'][ $field_indices[ 'neoserra_client_id' ] ]->choices = $client_choices;

				// assign default values from default client
				if ( $field_indices[ 'neoserra_client_ftEmps' ] ) $form['fields'][ $field_indices[ 'neoserra_client_ftEmps' ] ]->defaultValue = $default_client->ftEmps;
				if ( $field_indices[ 'neoserra_milestone_ns_amount' ] ) $form['fields'][ $field_indices[ 'neoserra_milestone_ns_amount' ] ]->defaultValue = $default_client->ftEmps;
				if ( $field_indices[ 'neoserra_client_ptEmps' ] ) $form['fields'][ $field_indices[ 'neoserra_client_ptEmps' ] ]->defaultValue = $default_client->ptEmps;
				if ( $field_indices[ 'neoserra_milestone_nspt_amount' ] ) $form['fields'][ $field_indices[ 'neoserra_milestone_nspt_amount' ] ]->defaultValue = $default_client->ptEmps;
				if ( $field_indices[ 'neoserra_client_grossSales' ] ) $form['fields'][ $field_indices[ 'neoserra_client_grossSales' ] ]->defaultValue = $default_client->grossSales;
				if ( $field_indices[ 'neoserra_milestone_is_amount' ] ) $form['fields'][ $field_indices[ 'neoserra_milestone_is_amount' ] ]->defaultValue = $default_client->grossSales;

				// assign css classes to fields
				foreach ( $field_indices as $k => $field_index ) {
					if ( $field_index ) {
						$form['fields'][ $field_index ]->cssClass .= ' ' . 'field-' . $k;
					}
				}

				return $form;
			}, 10, 3 );


			add_action( 'wp_footer', function() {
				if ( empty( self::$session_neoserra_clients ) ) return;
				?>
					<script>
						var sessionNeoserraClients = <?php echo json_encode( self::$session_neoserra_clients ); ?>;
						(function($) {
							$(document).on('change', '.field-neoserra_client_id input[type=radio], .field-neoserra_client_id select', function(e) {
								var form = $(this).closest('form');
								var clientId = $(this).val();
								if($(this).is('input[type=radio]')) {
									clientId = form.find('input[name=' + $(this).attr('name') + ']:checked').val();
								}
								for(var i in sessionNeoserraClients) {
									var client = sessionNeoserraClients[i];
									if(client.clientId == clientId) {

										if((matches = $('.field-neoserra_client_ftEmps input', form).val(client.ftEmps).trigger('change').attr('id').match(/^input_(\d+)_(\d+)/))) {
											if(gf_form_conditional_logic[matches[1]] && gf_form_conditional_logic[matches[1]].defaults[matches[2]]) gf_form_conditional_logic[matches[1]].defaults[matches[2]] = client.ftEmps;
										}

										if((matches = $('.field-neoserra_milestone_ns_amount input', form).val(client.ftEmps).trigger('change').attr('id').match(/^input_(\d+)_(\d+)/))) {
											if(gf_form_conditional_logic[matches[1]] && gf_form_conditional_logic[matches[1]].defaults[matches[2]]) gf_form_conditional_logic[matches[1]].defaults[matches[2]] = client.ftEmps;
										}

										if((matches = $('.field-neoserra_client_ptEmps input', form).val(client.ptEmps).trigger('change').attr('id').match(/^input_(\d+)_(\d+)/))) {
											if(gf_form_conditional_logic[matches[1]] && gf_form_conditional_logic[matches[1]].defaults[matches[2]]) gf_form_conditional_logic[matches[1]].defaults[matches[2]] = client.ptEmps;
										}

										if((matches = $('.field-neoserra_milestone_nspt_amount input', form).val(client.ptEmps).trigger('change').attr('id').match(/^input_(\d+)_(\d+)/))) {
											if(gf_form_conditional_logic[matches[1]] && gf_form_conditional_logic[matches[1]].defaults[matches[2]]) gf_form_conditional_logic[matches[1]].defaults[matches[2]] = client.ptEmps;
										}

										if((matches = $('.field-neoserra_client_grossSales input', form).val(client.grossSales).trigger('change').attr('id').match(/^input_(\d+)_(\d+)/))) {
											if(gf_form_conditional_logic[matches[1]] && gf_form_conditional_logic[matches[1]].defaults[matches[2]]) gf_form_conditional_logic[matches[1]].defaults[matches[2]] = client.grossSales;
										}

										if((matches = $('.field-neoserra_milestone_is_amount input', form).val(client.grossSales).trigger('change').attr('id').match(/^input_(\d+)_(\d+)/))) {
											if(gf_form_conditional_logic[matches[1]] && gf_form_conditional_logic[matches[1]].defaults[matches[2]]) gf_form_conditional_logic[matches[1]].defaults[matches[2]] = client.grossSales;
										}

										break;
									}
								}
							});
							$('.field-employee_type').closest('form').find('.field-neoserra_milestone_ns_amount input, .field-neoserra_milestone_nspt_amount input').prop('readonly', true);
							$(document).on('change', '.field-employee_type input[type=checkbox]', function(e) {
								var form = $(this).closest('form');
								var selectedOptions = [];
								$(this).closest('.gfield').find('input:checked').each(function(i, el2) {
									selectedOptions.push($(el2).val());
								});
								console.log(selectedOptions);
							});
						})(jQuery);
					</script>
				<?php
			} );


			add_filter( 'gform_pre_validation', function( $form ) {
				$contact_id = isset( $_GET['contact'] ) ? intval( $_GET['contact'] ) : null;
				$client_id = isset( $_GET['client'] ) ? intval( $_GET['client'] ) : null;
				$form = self::set_contact_client_field_options( $form, $contact_id, $client_id );
				return $form;
			} );

			add_filter( 'gform_pre_submission_filter', function( $form ) {
				$contact_id = isset( $_GET['contact'] ) ? intval( $_GET['contact'] ) : null;
				$client_id = isset( $_GET['client'] ) ? intval( $_GET['client'] ) : null;
				$form = self::set_contact_client_field_options( $form, $contact_id, $client_id );
				return $form;
			} );


			// add_filter( 'gform_pre_render', array( __CLASS__, 'populate_client_field_options' ) );
			// add_filter( 'gform_pre_validation', array( __CLASS__, 'populate_client_field_options' ) );
			// add_filter( 'gform_pre_submission_filter', array( __CLASS__, 'populate_client_field_options' ) );
			// // add_filter( 'gform_admin_pre_render', array( __CLASS__, 'populate_client_field_options' ) );

		}


		public static function populate_client_field_options( $form ) {
			// print_r($form['fields']); die;
			$client_choices = array_map( function( $client ) {
				return array(
					'text' => $client->company,
					'value' => $client->id,
					'isSelected' => isset( $_GET['client'] ) && $_GET['client'] == $client->id
				);
			}, self::$session_neoserra_clients );
			if ( count( $client_choices ) == 1 ) {
				$client_choices[0]['isSelected'] = true;
			}
			foreach ( $form['fields'] as $field ) {
				if ( $field->inputName == 'client' && in_array( $field->type, array( 'radio', 'select' ) ) ) {
					$field->choices = $client_choices;
				}
			}
			return $form;
		}


		protected static function set_contact_client_field_options( $form, $contact_id, $default = null ) {
			
			$client_search_response = Crown_Neoserra_Records_Api::get_clients( array( 'indiv_id' => $contact_id, 'columns' => implode( ',', array(
				'clientId',
				'company'
			) ) ) );
			$clients = is_object( $client_search_response ) && property_exists( $client_search_response, 'rows' ) && is_array( $client_search_response->rows ) && ! empty( $client_search_response->rows ) ? $client_search_response->rows : array();

			$client_choices = array_map( function( $client ) {
				return array(
					'text' => $client->company,
					'value' => $client->clientId,
					'isSelected' => ! empty( $default ) && $default == $client->clientId
				);
			}, $clients );
			if ( count( $client_choices ) == 1 ) {
				$client_choices[0]['isSelected'] = true;
			}

			foreach ( $form['fields'] as $field ) {
				if ( $field->inputName == 'client' && in_array( $field->type, array( 'radio', 'select' ) ) ) {
					$field->choices = $client_choices;
				}
			}

			return $form;
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
							'name' => 'feedName',
							'label' => esc_html__( 'Name', 'gfneoserra' ),
							'type' => 'text',
							'required' => true,
							'class' => 'medium',
							'tooltip' => sprintf(
								'<h6>%s</h6>%s',
								esc_html__( 'Name', 'gfneoserra' ),
								esc_html__( 'Enter a feed name to uniquely identify this setup.', 'gfneoserra' )
							),
						),
						array(
							'name'     => 'client_id_field',
							'label'    => esc_html__( 'Client ID', 'gfneoserra' ),
							'type'     => 'field_select',
							'tooltip' => esc_html__( 'Select the client ID field to use for updating the corresponding client record in Neoserra.', 'gfneoserra' ),
							'args'     => array(
								// 'input_types' => array( 'email' )
							)
						),
						array(
							'type'  => 'feed_condition',
							'name'  => 'update_record_condition',
							'label' => 'Feed Condition',
						)
					)
				),
				// array(
				// 	'title'  => esc_html__( 'Update Contact Record', 'gfneoserra' ),
				// 	'fields' => array(
				// 		array(
				// 			'label' => esc_html__( 'Contact Record Properties', 'gfneoserra' ),
				// 			'name' => 'contact_props',
				// 			'type' => 'generic_map',
				// 			'key_field' => array(
				// 				'title' => 'Property Name',
				// 				'allow_custom'  => true,
				// 				'choices' => self::get_contact_record_property_choices()
				// 			),
				// 			'value_field' => array(
				// 				'title' => 'Form Field'
				// 			)
				// 		),
				// 	)
				// ),
				array(
					'title'  => esc_html__( 'Update Client Record', 'gfneoserra' ),
					'fields' => array(
						array(
							// 'label' => esc_html__( 'Client Record Properties', 'gfneoserra' ),
							'name' => 'client_props',
							'type' => 'generic_map',
							'key_field' => array(
								'title' => 'Property Name',
								'allow_custom'  => true,
								'allow_duplicates' => true,
								'choices' => self::get_client_record_property_choices()
							),
							'value_field' => array(
								'title' => 'Form Field'
							)
						),
					)
				),
				array(
					'title'  => esc_html__( 'Add New Milestone Record', 'gfneoserra' ),
					'fields' => array(
						array(
							'type'    => 'checkbox',
							'name'    => 'milestone_record_options',
							// 'label'   => esc_html__( 'Milestone Record Options', 'gfneoserra' ),
							'choices' => array(
								array(
									'label' => esc_html__( 'Enable Milestone Record Creation', 'gfneoserra' ),
									'name' => 'milestone_record_create_enabled',
									'default_value' => 0
								)
							)
						),
						array(
							// 'label' => esc_html__( 'Milestone Record Properties', 'gfneoserra' ),
							'name' => 'milestone_props',
							'type' => 'generic_map',
							'key_field' => array(
								'title' => 'Property Name',
								'allow_custom'  => true,
								'allow_duplicates' => true,
								'choices' => self::get_milestone_record_property_choices()
							),
							'value_field' => array(
								'title' => 'Form Field'
							)
						),
					)
				),
				array(
					'title'  => esc_html__( 'Add New Capital Funding Record', 'gfneoserra' ),
					'fields' => array(
						array(
							'type'    => 'checkbox',
							'name'    => 'capital_funding_record_options',
							// 'label'   => esc_html__( 'Capital Funding Record Options', 'gfneoserra' ),
							'choices' => array(
								array(
									'label' => esc_html__( 'Enable Capital Funding Record Creation', 'gfneoserra' ),
									'name' => 'capital_funding_record_create_enabled',
									'default_value' => 0
								)
							)
						),
						array(
							// 'label' => esc_html__( 'Capital Funding Record Properties', 'gfneoserra' ),
							'name' => 'capital_funding_props',
							'type' => 'generic_map',
							'key_field' => array(
								'title' => 'Property Name',
								'allow_custom'  => true,
								'allow_duplicates' => true,
								'choices' => self::get_capital_funding_record_property_choices()
							),
							'value_field' => array(
								'title' => 'Form Field'
							)
						),
					)
				)
			);
		}


		protected static function get_contact_record_property_choices() {
			$properties = array(
				'Salutation' => 'salutation',
				'First Name' => 'first',
				'Last Name' => 'last',
				'Pronouns' => 'pronouns',
				'Email Address' => 'email',
				'Secondary Email Address' => 'emailAlt',
				'Subscribe to emails?' => 'emailingLists',
				'Preferred Language' => 'language',
				'Position' => 'position',
				'Business Owner?' => 'owner',
				'Phone Number' => 'phone',
				'Work Phone Number' => 'phone2',
				'Cell Phone' => 'mobileph',
				'Fax Number' => 'fax',
				// 'Center ID (override)' => 'centerId',
				'Address' => 'mailaddr',
				'City' => 'mailcity',
				'State' => 'mailst',
				'County' => 'county',
				'Zip Code' => 'mailzip',
				'Federal Congressional District' => 'congDistFed',
				'State Representative District' => 'houseDistState',
				'State Senate/Assembly District' => 'senateDistState',
				'Country' => 'Country',
				'Special Program' => 'specprog',
				'Family Income' => 'famincome',
				'Annual Family Income' => 'afamincome',
				'Family Size' => 'famsize',
				'Single-Parent Family' => 'singparent',
				'CDBG Income Certification Date' => 'date',
				'Alternate Email Address' => 'altemail',
				'Age' => 'age',
				'Date of Birth' => 'dob',
				'Gender' => 'gender',
				'LGBTQ+' => 'orientation',
				'Identification Type' => 'identType',
				'Identification Number' => 'ident',
				'Race' => 'ethnic',
				'Additional Race(s)' => 'raceAdd',
				'Hispanic Origin' => 'hispanic',
				'Indian Tribal Entity' => 'tribe',
				'Veteran Status' => 'veteran',
				'Highest Military Rank' => 'militaryRank',
				'Military Status' => 'reservist',
				'Military Branch' => 'militaryBranch',
				'Disabled/Handicapped?' => 'handicapped',
				'Education Level Completed' => 'education',
				'Notes' => 'notes'
			);
			return self::convert_properties_to_choices( $properties );
		}

		protected static function get_client_record_property_choices() {
			$properties = array(
				'Business Idea' => 'product_alt',
				'Client/Pre-client Type' => 'type2',
				'Services Sought' => 'serviceSeeking',
				'Company Name' => 'company',
				'DBA' => 'dba',
				'Phone Number' => 'busphone',
				'Secondary Phone Number' => 'busphone2',
				'Fax Number' => 'busfax',
				'Email Address' => 'busemail',
				'Subscribe to emails?' => 'emailingLists',
				'Website' => 'url',
				'Client Start Date' => 'started',
				'Expiration Date' => 'expires',
				'Physical Address Street' => 'physaddr',
				'Physical Address City' => 'physcity',
				'Physical Address State' => 'physst',
				'Physical Address Zip Code' => 'physzip',
				'Physical Address County' => 'county',
				'Country' => 'country',
				'Federal Congressional District' => 'congDistFed',
				'State Representative District' => 'houseDistState',
				'State Senate District' => 'senateDistState',
				'Mailing address is the same as physical address?' => 'mailUsePhys',
				'Mailing Address' => 'mailaddr',
				'Mailing City' => 'mailcity',
				'Mailing State' => 'mailst',
				'Mailing ZIP Code' => 'mailzip',
				'Mailing Address County' => 'mailCounty',
				'Ethnicity-Expanded' => 'ethnic',
				'Language' => 'user5',
				'Age' => 'age',
				'Qualifies for YEP (14-27 yrs old) ?' => 'yep',
				'Primary contact employed at intake?' => 'employed',
				'Have you attended the Business Basics workshop' => 'busbasic',
				'Referral From Bank' => 'refbank',
				'SBDC Regional Special Programs' => 'sbdcsp',
				'Special Program' => 'specialpgm',
				'Client Funding Program' => 'fundpgm',
				'Other' => 'other',
				'CDBG Income Certification Date' => 'cdbgicd',
				'Family Income' => 'famincome',
				'Annual Family Income' => 'familyinc',
				'Family Size' => 'famsize',
				'Single-Parent Family?' => 'sglparent',
				'Dream Fund Client?' => 'dfc',
				'Client is grant eligible?' => 'eligible',
				'Business Concept' => 'bcon',
				'Business Model' => 'bmod',
				'Business Type (Dream Fund)' => 'btyp',
				'Business Industry' => 'bind',
				'Intended Use(s) of Award' => 'intaw',
				'Intended Operation Location (Zip Code)' => 'intzip',
				'Has Business Bank Account?' => 'bnkacc',
				'Applicable Business Formation Documents Complete' => 'bizform',
				'Physical Location Established' => 'location',
				'Hired at least one employee' => 'job',
				'Revenue Generated' => 'revenue',
				'Spent money on business expenses' => 'expenses',
				'Business Investment Received' => 'invest',
				'East Bay Program Code' => 'ebpc',
				'Would you be interested in in-person advising in Oakland, Concord or Richmond?' => 'ebtraining',
				'Centro Latino West' => 'clw',
				'What is your SJSU Affiliation?' => 'sjsuaff',
				'Santa Cruz SBDC' => 'scsbdc',
				'Santa Cruz Special Programs' => 'spsc',
				'Are you a student of Cabrillo College?' => 'cabrillo',
				'Are you applying for services to retain current jobs in the business, including your own?' => 'retjob',
				'What is your preferred method of contact?' => 'contact',
				'San Francisco Legacy Business Status' => 'sflbs',
				'Sonoma Program Codes' => 'sonprco',
				'Date of Birth' => 'dob',
				'Gender Expanded' => 'gender',
				'City of Chico Client' => 'user20',
				'Income Level' => 'user25',
				'# In Household' => 'user26',
				'Has your business been affected by the 2018 wildfires in Shasta and Trinity Counties?' => 'wildfires',
				'MEHKO Client?' => 'mehko',
				'Date of Initial MEHKO Consultation' => 'initcons',
				'Race or Ethnicity of MEHKO Client' => 'ethnmehko',
				'Gender of MEHKO Client' => 'gendmehko',
				'Veteran Status of MEHKO Client' => 'vetmehko',
				'APEX Client?' => 'apex',
				'APEX Counselor Assignment' => 'apexcouns',
				'Is this client registered with any local (cities, counties, etc.) agencies to do business?' => 'localcont',
				// 'Is your company registered with Cal eProcure?' => '',
				// 'California Certifications' => '',
				'CA Certification ID' => 'caci',
				'CA Certification Renewal Date' => 'caexpir',
				'SAM.gov Registration Renewal Date' => 'samrenew',
				// 'Is your business registered in the SBA\'s Dynamic Small Business Search (DSBS)?' => '',
				// 'Is your company currently a government vendor listed on one or more GSA schedules, CMAS, or other similar contract vehicles?' => '',
				'Compliant with CMMC 2.0 Level 1?' => 'cmmc1',
				'Compliant with CMMC 2.0 Level 2 or above?' => 'cmmc2',
				// 'Compliant with FAR 52.204-21 (although not necessarily compliant with CMMC 2.0)' => '',
				// 'Is client working with Project Spectrum?' => '',
				// 'Has this client been connected with a federal laboratory?' => '',
				'Do you have a business plan?' => 'bplan',
				'Do you have money to finance your business venture?' => 'finance',
				'How much funding are you seeking? $' => 'funding2',
				'Step One: How may we help you?' => 'step1',
				'Step Two: How may we assist your business?' => 'step2',
				'If Other' => 'other',
				'Initial Business Status' => 'statusInit',
				'Company Status' => 'status',
				'Date Company Established' => 'estab',
				'Verified to be in business?' => 'verifiedInBusiness',
				'Reportable Business Start Impact' => 'impactDate',
				'Full-Time Employees' => 'ftEmps',
				'Part-Time Employees' => 'ptEmps',
				'Export-Related Employees' => 'empsExport',
				'Ownership Gender' => 'ownerGender',
				'Ownership Gender Certification Date' => 'ownerGenderDate',
				'Percentage of Woman Ownership' => 'womanOwnedPct',
				'Company Veteran Status' => 'ownerVeteran',
				'Veteran Verification/Certification Date' => 'ownerVeteranDate',
				'Business Size' => 'bussize',
				'Disadvantage Status' => 'disadvStatus',
				'Disadvantage Certification Date' => 'disadvCertdate',
				'Business Type' => 'bustype',
				'Organization Type' => 'orgtype',
				'State of Incorporation' => 'incstate',
				'HUBZone' => 'hubzone',
				'HUBZone Certification Date' => 'hubzoneDate',
				'Distressed Area?' => 'distress',
				'Located on Tribal Lands?' => 'locationTribalLands',
				'Indian Tribal Entity' => 'tribe',
				'Member of an underserved community?' => 'underserved',
				'International Trade' => 'intlTrade',
				'Export Countries' => 'exportCountries',
				'Business Online?' => 'busonline',
				'Home-based?' => 'homebased',
				'Conducts business in a language other than English?' => 'otEnglish',
				'Business Languages' => 'oteLangs',
				'Federal ID' => 'fedid',
				'SBA Client Type' => 'sbaClientType',
				'SBA Relationship' => 'sbarel',
				'Gross Revenue' => 'grossSales',
				'Gross Revenue Date' => 'grossSalesDate',
				'Gross Exported-Related Revenue' => 'grossSalesExport',
				'Profits/Losses' => 'grossProfits',
				'Profits/Losses Date' => 'grossProfitsDate',
				'Current Bonding Amount' => 'bondingAmount',
				'Government Industrial Base (GIB) ready?' => 'hasPotential',
				'GIB Ready Date' => 'hasPotentialDate',
				'Defense Industrial Base (DIB) ready?' => 'hasDodPotential',
				'DIB Ready Date' => 'hasDodPotentialDate',
				'Key Technology Areas' => 'kta',
				'Key Technology Sub-areas' => 'subkta',
				'SAM Registration' => 'samReg',
				'Capabilities Statement?' => 'capStmt',
				'IP Ownership' => 'ipOwnership',
				'IP Owner Name' => 'ipOwner',
				'Products Commercially Available' => 'commerciallyAvailable',
				'Annual Production Capacity' => 'prodCap',
				'Technology Readiness Level' => 'techReadyLevel',
				'DoD Covered Small Business?' => 'coveredSb',
				'Unique Entity Identifier' => 'uei',
				'DUNS Number' => 'duns',
				'CAGE Code' => 'cage',
				'Primary Counselor' => 'counselId',
				'Default Funding Source' => 'defaultfundarea',
				'Default Sub-funding Source' => 'defaultfemaEnergy',
				'Referral From' => 'reffrom',
				'Referral From Description' => 'reffromDesc',
				'Referral To' => 'refto',
				'Referral To Description' => 'reftoDesc',
				'PSCs' => 'pscs',
				'Primary PSC' => 'primaryPsc',
				'SICs' => 'sics',
				'Primary SIC' => 'primarySic',
				'NAICs' => 'naics',
				'Primary NAICS' => 'primaryNaics',
				'ISICs' => 'isics',
				'Primary ISIC' => 'primaryIsic',
				'HS Code(s)' => 'hs',
				'Primary Harmonized Code' => 'primaryHs',
				'Product or Service Description' => 'product',
				'Notes' => 'notes',
				'Case End Type' => 'caseend',
				'Agreement on file?' => 'agreement',
				'Permit SBA surveys?' => 'allowSbaContact',
				'Signature' => 'signature',
				'Signature Date' => 'ecenterEntry',
			);
			return self::convert_properties_to_choices( $properties );
		}

		protected static function get_milestone_record_property_choices() {
			$properties = array(
				'Milestone Date' => 'date',
				'Milestone Type' => 'type',
				'Milestone Amount' => 'amount',
				'Initial Date' => 'initialDate',
				'Initial Amount' => 'initialAmount',
				'Counterpart' => 'remark',
				'COVID-19 related?' => 'covid19',
				'Subject' => 'text',
				// 'Center ID' => 'centerId',
				'Funding Source' => 'fundarea',
				'Sub-funding Source' => 'femaEnergy',
				'Notes' => 'memo',
				'Survey Response' => 'surveyId',
				'Reportable?' => 'isReportable',
				'Attribution Statement' => 'attribStatement',
				'Attribution Source' => 'attribution',
				'Attribution Signature' => 'attribSignature',
				'Attribution Date' => 'attribDate',
				'Verified' => 'verify'
			);
			return self::convert_properties_to_choices( $properties );
		}

		protected static function get_capital_funding_record_property_choices() {
			$properties = array(
				'Reporting Date' => 'date',
				'Application Date' => 'appdate',
				'Completion Status' => 'status',
				'Date Completed' => 'dateCompleted',
				'Funding Type' => 'type',
				'Institution' => 'institution',
				'Amount Requested' => 'amountReq',
				'Amount Approved' => 'amountApproved',
				'Subject' => 'text',
				// 'Center ID' => 'centerId',
				'Funding Source' => 'fundarea',
				'Notes' => 'memo',
				'Attribution Statement' => 'attribStatement',
				'Attribution Source' => 'attribution',
				'Attribution Signature' => 'attribSignature',
				'Attribution Date' => 'attribDate',
				'Required Attribution' => 'reqattrib'
			);
			return self::convert_properties_to_choices( $properties );
		}


		protected static function convert_properties_to_choices( $properties ) {
			$choices = array();
			foreach ( $properties as $label => $value ) {
				$choice = array(
					'label' => $label
				);
				if ( is_array( $value ) ) {
					$choice['choices'] = array();
					foreach ( $value as $inner_label => $inner_value ) {
						$choice['choices'][] = array(
							'label' => $inner_label,
							'value' => $inner_value
						);
					}
				} else {
					$choice['value'] = $value;
				}
				$choices[] = $choice;
			}
			return $choices;
		}


		public function feed_list_columns() {
			return array(
				'feedName' => __( 'Name', 'gfneoserra' ),
				// 'center_id'   => __( 'Center Id', 'gfneoserra' )
			);
		}


		public function process_feed( $feed, $entry, $form ) {

			$error_messages = array();

			// $contact_props = $this->get_generic_map_fields( $feed, 'contact_props' );
			$client_props = $this->get_generic_map_fields( $feed, 'client_props' );
			$milestone_props = $this->get_generic_map_fields( $feed, 'milestone_props' );
			$capital_funding_props = $this->get_generic_map_fields( $feed, 'capital_funding_props' );

			$notes_field_ids = array();
			foreach ( $feed['meta']['client_props'] as $mapping ) {
				if ( $mapping['key'] == 'notes' && ! empty( $mapping['value'] ) ) {
					$notes_field_ids[] = $mapping['value'];
				}
			}
			if ( count( $notes_field_ids ) > 1 ) {
				$client_props['notes'] = $notes_field_ids;
			}

			$client_id_field_id = $feed['meta']['client_id_field'];
			$client_id = ! empty( $client_id_field_id ) ? $this->get_field_value( $form, $entry, $client_id_field_id ) : '';

			// $contact = null;
			$client = null;

			if ( ! empty( $client_id ) ) {
				$client_response = Crown_Neoserra_Records_Api::get_client( $client_id );
				$client = is_object( $client_response ) && property_exists( $client_response, 'id' ) ? $client_response : null;
			}

			if ( $client ) {
				gform_update_meta( $entry['id'], 'neoserra_client_id', $client->id );
			} else {
				$error_messages[] = '[update_client] ' . 'Client not found.';
			}

			$center = null;
			if ( $client ) {
				$center_response = Crown_Neoserra_Records_Api::get_center( $client->centerId );
				$center = is_object( $center_response ) && property_exists( $center_response, 'id' ) ? $center_response : null;
			}

			$center_director_notification_links = array();

			// update client record
			if ( $client ) {
				$client_args = array();
				foreach ( $client_props as $prop => $field_id ) {
					$value = null;
					if ( is_array( $field_id ) ) {
						$values = array();
						foreach ( $field_id as $fid ) {
							$v = $this->get_field_value( $form, $entry, $fid );
							if ( empty( $v ) ) continue;
							$label = GFCommon::get_label( RGFormsModel::get_field( $form, $fid ) );
							$values[] = $label . ': ' . $v;
						}
						$value = implode( "\n", $values );
					} else {
						if ( array_key_exists( $field_id, $entry ) ) {
							$value = $this->get_field_value( $form, $entry, $field_id );
						} else {
							$value = GFCommon::replace_variables( $field_id, $form, $entry );
						}
					}
					if ( ! empty( $value ) ) {
						$client_args[ $prop ] = $value;
					}
				}
				if ( array_key_exists( 'estab', $client_args ) && ! array_key_exists( 'status', $client_args ) ) {
					$date_estab = new DateTime( $client_args['estab'] );
					$now = new DateTime();
					$diff = $now->diff( $date_estab );
					$client_args['status'] = intval( $diff->format('%y') ) > 0 ? 'B' : 'S';
				}
				if ( ! empty( $client_args ) ) {
					$client_response = Crown_Neoserra_Records_Api::update_client( $client_id, $client_args );
					$error_messages = array_merge( $error_messages, self::get_error_messages( $client_response, 'update_client' ) );
				}
			}

			// add new milestone record
			if ( $client && boolval( $feed['meta']['milestone_record_create_enabled'] ) ) {
				$milestone_args = array(
					'clientId' => $client->id,
					'centerId' => $center->id,
					'fundarea' => ! empty( $client->defaultfundarea ) && ! in_array( $client->defaultfundarea, array( '?' ) ) ? $client->defaultfundarea : 'S',
					'counselors' => $center->counselId
				);
				foreach ( $milestone_props as $prop => $field_id ) {
					$value = null;
					if ( is_array( $field_id ) ) {
						$values = array();
						foreach ( $field_id as $fid ) {
							$v = $this->get_field_value( $form, $entry, $fid );
							if ( empty( $v ) ) continue;
							$label = GFCommon::get_label( RGFormsModel::get_field( $form, $fid ) );
							$values[] = $label . ': ' . $v;
						}
						$value = implode( "\n", $values );
					} else {
						if ( array_key_exists( $field_id, $entry ) ) {
							$value = $this->get_field_value( $form, $entry, $field_id );
						} else {
							$value = GFCommon::replace_variables( $field_id, $form, $entry );
						}
					}
					if ( ! empty( $value ) ) {
						if ( in_array( $prop, array( 'amount', 'initialAmount' ) ) ) {
							$value = floatval( preg_replace( '/[^\d\.\-]/', '', $value ) );
						} else if ( in_array( $prop, array( 'date', 'initialDate', 'attribDate' ) ) ) {
							$value = preg_replace( '/^(\d{4}-\d{2}-\d{2}).*/', '$1', $value );
						}
						$milestone_args[ $prop ] = $value;
					}
				}
				$milestone_response = Crown_Neoserra_Records_Api::create_milestone( $milestone_args );
				$error_messages = array_merge( $error_messages, self::get_error_messages( $milestone_response, 'create_milestone' ) );
				$milestone_id = is_object( $milestone_response ) && property_exists( $milestone_response, 'id' ) ? $milestone_response->id : null;
				if ( $milestone_id ) {
					$center_director_notification_links['milestone'] = self::$neoserra_dashboard_uri . 'activity/view?formid=7&eid=' . $milestone_id . '&url=/clients/' . $client->id;
				}
			}

			// add new capital funding record
			if ( $client && boolval( $feed['meta']['capital_funding_record_create_enabled'] ) ) {
				$capital_funding_args = array(
					'clientId' => $client->id,
					'centerId' => $center->id,
					'fundarea' => ! empty( $client->defaultfundarea ) && ! in_array( $client->defaultfundarea, array( '?' ) ) ? $client->defaultfundarea : 'S',
					'counselors' => $center->counselId
				);
				foreach ( $capital_funding_props as $prop => $field_id ) {
					$value = null;
					if ( is_array( $field_id ) ) {
						$values = array();
						foreach ( $field_id as $fid ) {
							$v = $this->get_field_value( $form, $entry, $fid );
							if ( empty( $v ) ) continue;
							$label = GFCommon::get_label( RGFormsModel::get_field( $form, $fid ) );
							$values[] = $label . ': ' . $v;
						}
						$value = implode( "\n", $values );
					} else {
						if ( array_key_exists( $field_id, $entry ) ) {
							$value = $this->get_field_value( $form, $entry, $field_id );
						} else {
							$value = GFCommon::replace_variables( $field_id, $form, $entry );
						}
					}
					if ( ! empty( $value ) ) {
						if ( in_array( $prop, array( 'amountReq', 'amountApproved' ) ) ) {
							$value = floatval( preg_replace( '/[^\d\.\-]/', '', $value ) );
						} else if ( in_array( $prop, array( 'date', 'appdate', 'dateCompleted', 'attribDate' ) ) ) {
							$value = preg_replace( '/^(\d{4}-\d{2}-\d{2}).*/', '$1', $value );
						}
						$capital_funding_args[ $prop ] = $value;
					}
				}
				$capital_funding_response = Crown_Neoserra_Records_Api::create_capital_funding( $capital_funding_args );
				$error_messages = array_merge( $error_messages, self::get_error_messages( $capital_funding_response, 'create_capital_funding' ) );
				$capital_funding_id = is_object( $capital_funding_response ) && property_exists( $capital_funding_response, 'id' ) ? $capital_funding_response->id : null;
				if ( $capital_funding_id ) {
					$center_director_notification_links['capital_funding'] = self::$neoserra_dashboard_uri . 'activity/view?formid=20&eid=' . $capital_funding_id . '&url=/clients/' . $client->id;
				}
			}

			// handle error messages
			foreach ( $error_messages as $error_message ) {
				$this->add_feed_error( $error_message, $feed, $entry, $form );
			}
			if ( ! empty( $error_messages ) ) {
				gform_update_meta( $entry['id'], 'neoserra_api_errors', $error_messages );
				GFAPI::send_notifications( $form, $entry, 'neoserra_api_error' );
			}

			if ( ! empty( $center_director_notification_links ) && ! empty( $center->diremail ) ) {
				gform_update_meta( $entry['id'], 'neoserra_center_diremail', $center->diremail );
				gform_update_meta( $entry['id'], 'neoserra_record_links', $center_director_notification_links );
				GFAPI::send_notifications( $form, $entry, 'neoserra_center_director_notification' );
			}

			return;
		}

		protected static function get_error_messages( $response, $context ) {
			$error_messages = array();
			if ( ! is_object( $response ) ) {
				$error_messages[] = '[' . $context . '] ' . 'No API response.';
			} else if ( property_exists( $response, 'errors' ) ) {
				foreach ( $response->errors as $error ) {
					$error = array_merge( array(
						'field' => '',
						'error' => ''
					), (array) $error );
					$error_messages[] = '[' . $context . ']' . ( ! empty( $error['field'] ) ? '[' . $error['field'] . ']' : '' ) . ' ' . $error['error'];
				}
			} else if ( property_exists( $response, 'exception' ) ) {
				$error_messages[] = '[' . $context . '] ' . $response->exception;
			} else {
				if ( preg_match( '/^get_/', $context ) ) {
					// no error logged for getter methods
				} else {
					if ( ! property_exists( $response, 'status' ) || $response->status != 'okay' ) {
						$error_messages[] = '[' . $context . '] ' . 'An unknown error occurred: ' . json_encode( $response );
					}
				}
			}
			return $error_messages;
		}


		public function add_notification_events( $notification_events ) {
			$notification_events['neoserra_api_error'] = __( 'Neoserra API Error', 'gfneoserra' );
			$notification_events['neoserra_center_director_notification'] = __( 'Neoserra Center Director Email', 'gfneoserra' );
			return $notification_events;
		}

		
		public function add_custom_merge_tags( $merge_tags, $form_id, $fields, $element_id ) {
			$merge_tags[] = array( 'label' => 'Neoserra API Errors', 'tag' => '{neoserra_api_errors}' );
			$merge_tags[] = array( 'label' => 'Neoserra Center Director Email', 'tag' => '{neoserra_center_diremail}' );
			$merge_tags[] = array( 'label' => 'Neoserra Record Links', 'tag' => '{neoserra_record_links}' );
			return $merge_tags;
		}


		public function replace_custom_merge_tags( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {

			$merge_tag = '{neoserra_api_errors}';
			if ( strpos( $text, $merge_tag ) !== false ) {
				$errors = gform_get_meta( $entry['id'], 'neoserra_api_errors' );
				if ( ! empty( $errors ) ) {
					$text = str_replace( $merge_tag, implode( "\n", $errors ), $text );
				} else {
					$text = str_replace( $merge_tag, '', $text );
				}
			}

			$merge_tag = '{neoserra_center_diremail}';
			if ( strpos( $text, $merge_tag ) !== false ) {
				$email = gform_get_meta( $entry['id'], 'neoserra_center_diremail' );
				if ( ! empty( $email ) ) {
					$text = str_replace( $merge_tag, $email, $text );
				} else {
					$text = str_replace( $merge_tag, '', $text );
				}
			}

			$merge_tag = '{neoserra_record_links}';
			if ( strpos( $text, $merge_tag ) !== false ) {
				$links = gform_get_meta( $entry['id'], 'neoserra_record_links' );
				if ( ! empty( $links ) ) {
					$html_links = array();
					foreach ( $links as $k => $link ) {
						$label = 'Record';
						if ( $k == 'milestone' ) $label = 'Milestone Record';
						if ( $k == 'capital_funding' ) $label = 'Capital Funding Record';
						$html_links[] = '<strong>' . $label . ':</strong> <a href="' . $link . '" target="_blank">' . $link . '</a>';
					}
					$text = str_replace( $merge_tag, implode( "\n", $html_links ), $text );
				} else {
					$text = str_replace( $merge_tag, '', $text );
				}
			}

			return $text;
		}


	}
}