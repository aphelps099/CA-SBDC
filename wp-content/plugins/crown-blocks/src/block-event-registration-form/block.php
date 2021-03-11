<?php

if(!class_exists('Crown_Block_Event_Registration_Form')) {
	class Crown_Block_Event_Registration_Form extends Crown_Block {


		public static $name = 'event-registration-form';


		public static function init() {
			parent::init();

		}


		public static function get_attributes() {
			return array(
				'className' => array( 'type' => 'string', 'default' => '' ),
				'meetingId' => array( 'type' => 'string', 'default' => '' )
			);
		}


		public static function render( $atts, $content ) {

			$form_id = get_option( 'theme_config_events_zoom_meeting_registration_form' );
			if ( empty( $form_id ) ) return '';

			$field_values = array(
				'meeting_id' => $atts['meetingId']
			);

			return gravity_form( $form_id, false, false, false, $field_values, true, -1, false );
		}


	}
	Crown_Block_Event_Registration_Form::init();
}