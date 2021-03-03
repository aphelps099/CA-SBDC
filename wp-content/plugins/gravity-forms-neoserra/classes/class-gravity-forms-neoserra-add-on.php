<?php

GFForms::include_addon_framework();

if ( ! class_exists( 'Gravity_Forms_Neoserra_Add_On' ) ) {
	class Gravity_Forms_Neoserra_Add_On extends GFAddOn {


		protected $_version = GRAVITY_FORMS_NEOSERRA_ADD_ON_VERSION;
		protected $_min_gravityforms_version = '1.9';
		protected $_slug = 'neoserra-add-on';
		protected $_path = GRAVITY_FORMS_NEOSERRA_ADD_ON_PATH_DIR . '/class-gravity-forms-neoserra-add-on.php';
		protected $_full_path = __FILE__;
		protected $_title = 'Gravity Forms Neoserra Add-On';
		protected $_short_title = 'Neoserra';
	
		private static $_instance = null;
	

		public static function get_instance() {
			if ( self::$_instance == null ) {
				self::$_instance = new self();
			}
	
			return self::$_instance;
		}


		public function form_settings_fields( $form ) {
			return array(
				array(
					'title'  => esc_html__( 'Neoserra Settings', 'gfneoserra' ),
					'fields' => array(
						array(
							'label'   => esc_html__( 'Options', 'gfneoserra' ),
							'type'    => 'checkbox',
							'name'    => 'options',
							// 'tooltip' => esc_html__( 'This is the tooltip', 'gfneoserra' ),
							'choices' => array(
								array(
									'label' => esc_html__( 'Enable for Neoserra data export', 'gfneoserra' ),
									'name'  => 'active'
								)
							)
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
					'name' => 'referral',
					'label' => esc_html__( 'Referral', 'gfneoserra' ),
					'required' => true,
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


	}
}