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


		private static $_instance = null;

		// TODO: add email notification to center director when business start form is submitted with link to record(s) created
		// TODO: add auto company status for business start
		// TODO: multiple business handling, make radio selection stand out
		// TODO: action fields to buttons
		// TODO: provide link upon to completion to submit again


		public function init() {
			parent::init();
	
			// add_filter( 'gform_notification_events', array( &$this, 'add_neoserra_api_error_event' ) );

			// add_filter( 'gform_custom_merge_tags', array( &$this, 'add_custom_merge_tags' ), 10, 4);
			// add_filter( 'gform_replace_merge_tags', array( &$this, 'replace_custom_merge_tags' ), 10, 7 );

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

				$contact_id = isset( $_GET['contact'] ) ? intval( $_GET['contact'] ) : null;
				if ( ! $contact_id ) return $form;

				// fetch contact record
				$contact_response = Crown_Neoserra_Records_Api::get_contact( $contact_id );
				$contact = is_object( $contact_response ) && property_exists( $contact_response, 'id' ) ? $contact_response : null;
				if ( ! $contact ) return $form;

				// search for clients associated with contact email
				$client_search_response = Crown_Neoserra_Records_Api::get_clients( array( 'email' => $contact->email ) );
				$client_results = is_object( $client_search_response ) && property_exists( $client_search_response, 'rows' ) && is_array( $client_search_response->rows ) && ! empty( $client_search_response->rows ) ? $client_search_response->rows : null;
				if ( ! $client_results ) return $form;

				// print_r($client_results);

				// fetch client records
				$clients = array();
				foreach ( $client_results as $client_result ) {
					$client_response = Crown_Neoserra_Records_Api::get_client( $client_result->clientId );
					$client = is_object( $client_response ) && property_exists( $client_response, 'id' ) ? $client_response : null;
					if ( $client ) $clients[] = $client;
				}
				if ( empty( $clients ) ) return $form;

				$client = $clients[0];

				if ( $field_indices[ 'neoserra_client_id' ] ) $form['fields'][ $field_indices[ 'neoserra_client_id' ] ]->defaultValue = $client->id;
				if ( $field_indices[ 'neoserra_client_ftEmps' ] ) $form['fields'][ $field_indices[ 'neoserra_client_ftEmps' ] ]->defaultValue = $client->ftEmps;
				if ( $field_indices[ 'neoserra_milestone_ns_amount' ] ) $form['fields'][ $field_indices[ 'neoserra_milestone_ns_amount' ] ]->defaultValue = $client->ftEmps;
				if ( $field_indices[ 'neoserra_client_ptEmps' ] ) $form['fields'][ $field_indices[ 'neoserra_client_ptEmps' ] ]->defaultValue = $client->ptEmps;
				if ( $field_indices[ 'neoserra_milestone_nspt_amount' ] ) $form['fields'][ $field_indices[ 'neoserra_milestone_nspt_amount' ] ]->defaultValue = $client->ptEmps;
				if ( $field_indices[ 'neoserra_client_grossSales' ] ) $form['fields'][ $field_indices[ 'neoserra_client_grossSales' ] ]->defaultValue = $client->grossSales;
				if ( $field_indices[ 'neoserra_milestone_is_amount' ] ) $form['fields'][ $field_indices[ 'neoserra_milestone_is_amount' ] ]->defaultValue = $client->grossSales;

				return $form;
			}, 10, 3 );

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
						$value = $this->get_field_value( $form, $entry, $field_id );
					}
					if ( ! empty( $value ) ) $client_args[ $prop ] = $value;
				}
				$client_response = Crown_Neoserra_Records_Api::update_client( $client_id, $client_args );
				$error_messages = array_merge( $error_messages, self::get_error_messages( $client_response, 'update_client' ) );
			}

			// add new milestone record
			if ( $client && boolval( $feed['meta']['milestone_record_create_enabled'] ) ) {
				$milestone_args = array(
					'clientId' => $client->id,
					'centerId' => $client->centerId,
					'fundarea' => $client->defaultfundarea
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
						$value = $this->get_field_value( $form, $entry, $field_id );
					}
					if ( ! empty( $value ) ) $milestone_args[ $prop ] = $value;
				}
				$milestone_response = Crown_Neoserra_Records_Api::create_milestone( $milestone_args );
				$error_messages = array_merge( $error_messages, self::get_error_messages( $milestone_response, 'create_milestone' ) );
			}

			// add new capital funding record
			if ( $client && boolval( $feed['meta']['capital_funding_record_create_enabled'] ) ) {
				$capital_funding_args = array(
					'clientId' => $client->id,
					'centerId' => $client->centerId,
					'fundarea' => $client->defaultfundarea
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
						$value = $this->get_field_value( $form, $entry, $field_id );
					}
					if ( ! empty( $value ) ) $capital_funding_args[ $prop ] = $value;
				}
				$capital_funding_response = Crown_Neoserra_Records_Api::create_capital_funding( $capital_funding_args );
				$error_messages = array_merge( $error_messages, self::get_error_messages( $capital_funding_response, 'create_capital_funding' ) );
			}

			// handle error messages
			foreach ( $error_messages as $error_message ) {
				$this->add_feed_error( $error_message, $feed, $entry, $form );
			}
			if ( ! empty( $error_messages ) ) {
				gform_update_meta( $entry['id'], 'neoserra_api_errors', $error_messages );
				GFAPI::send_notifications( $form, $entry, 'neoserra_api_error' );
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


		// public function add_neoserra_api_error_event( $notification_events ) {
		// 	$notification_events['neoserra_api_error'] = __( 'Neoserra API Error', 'gfneoserra' );
		// 	return $notification_events;
		// }

		
		// public function add_custom_merge_tags( $merge_tags, $form_id, $fields, $element_id ) {
		// 	$merge_tags[] = array( 'label' => 'Neoserra API Errors', 'tag' => '{neoserra_api_errors}' );
		// 	return $merge_tags;
		// }


		// public function replace_custom_merge_tags( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {
		// 	$merge_tag = '{neoserra_api_errors}';
		// 	if ( strpos( $text, $merge_tag ) === false ) {
		// 		return $text;
		// 	} else {
		// 		$errors = gform_get_meta( $entry['id'], 'neoserra_api_errors' );
		// 		if ( ! empty( $errors ) ) {
		// 			$text = str_replace( $merge_tag, implode( "\n", $errors ), $text );
		// 		} else {
		// 			$text = str_replace( $merge_tag, '', $text );
		// 		}
		// 	}
		// 	return $text;
		// }


	}
}