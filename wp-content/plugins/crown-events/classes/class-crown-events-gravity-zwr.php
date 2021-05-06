<?php

if ( ! class_exists( 'Crown_Events_Gravity_ZWR' ) ) {
	class Crown_Events_Gravity_ZWR extends GravityZWR {


		private static $_instance;


		public static function get_instance() {
			if ( null === self::$_instance ) {
				self::$_instance = new Crown_Events_Gravity_ZWR();
			}
	
			return self::$_instance;
		}


		public function feed_settings_fields() {
			$fields = parent::feed_settings_fields();

			$additional_fields = array(
				array(
					'title'  => esc_html__( 'Custom Registration Fields', 'gravity-zwr' ),
					'fields' => array(
						array(
							'name'      => 'customFields',
							'label'     => esc_html__( 'Custom fields', 'gravity-zwr' ),
							'type'      => 'dynamic_field_map',
							'tooltip'   => sprintf(
								'<h6>%s</h6>%s',
								esc_html__( 'Custom Fields', 'gravity-zwr' ),
								esc_html__( 'Setup your custom Zoom Webinar Registration fields by defining field\'s title and selecting the appropriate form field from the list.', 'gravity-zwr' )
							),
						),
					),
				),
			);

			array_splice( $fields, -1, 0, $additional_fields );
	
			return $fields;
	
		}


		public function process_feed( $feed, $entry, $form ) {

			// Log that we are processing feed.
			$this->log_debug( __METHOD__ . '(): Processing feed.' );
	
			$webinar = preg_replace( '/[^0-9]/', '', $feed['meta']['zoomWebinarID'] );
	
			if ( empty( $webinar ) ) {
				$this->add_feed_error( esc_html__( 'Aborted: Empty Webinar ID', 'gravity-zwr' ), $feed, $entry, $form );
				return $entry;
			}
	
			$meetingtype = in_array( $feed['meta']['meetingtype'], [ 'webinars', 'meetings' ], true ) ? $feed['meta']['meetingtype'] : 'webinars';
	
			$settings = $this->get_plugin_settings();
	
			if ( empty( $settings ) ) {
				$this->add_feed_error( esc_html__( 'Aborted: Empty Plugin Settings', 'gravity-zwr' ), $feed, $entry, $form );
				return $entry;
			}
	
			// Retrieve the name => value pairs for all fields mapped in the 'mappedFields' field map.
			$field_map = $this->get_field_map_fields( $feed, 'mappedFields' );
	
			// Get mapped email address.
			$email = $this->get_field_value( $form, $entry, $field_map['email'] );
	
			// If email address is invalid, log error and return.
			if ( GFCommon::is_invalid_or_empty_email( $email ) ) {
				$this->add_feed_error( esc_html__( 'A valid Email address must be provided.', 'gravity-zwr' ), $feed, $entry, $form );
				return $entry;
			}
	
			// Loop through the fields from the field map setting building an array of values to be passed to the third-party service.
			$merge_vars = array();
			foreach ( $field_map as $name => $field_id ) {
	
				// If no field is mapped, skip it.
				if ( rgblank( $field_id ) ) {
					continue;
				}
	
				// Get field value.
				$field_value = $this->get_field_value( $form, $entry, $field_id );
	
				// If field value is empty, skip it.
				if ( empty( $field_value ) ) {
					continue;
				}
	
				// Get the field value for the specified field id
				$merge_vars[ $name ] = $field_value;
	
			}
	
			if ( empty( $merge_vars ) ) {
				$this->add_feed_error( esc_html__( 'Aborted: Empty merge fields', 'gravity-zwr' ), $feed, $entry, $form );
				return $entry;
			}





			$custom_fields = $this->get_dynamic_field_map_fields( $feed, 'customFields' );
			if ( ! empty( $custom_fields ) ) {
				$merge_vars['custom_questions'] = array();
				foreach ( $custom_fields as $title => $field_id ) {
					$merge_vars['custom_questions'][] = array(
						'title' => $title,
						'value' => $this->get_field_value( $form, $entry, $field_id )
					);
				}
			}


	


			$remote_request = new GravityZWR_ZOOMAPI( GRAVITYZWR_ZOOMAPIURL . '/' . $meetingtype . '/' . $webinar . '/registrants', array( 'body' => wp_json_encode( $merge_vars ) ), 'post' );
			$remote_request->run();
	
			if ( ! $remote_request->is_success() ) {
				// Log that registration failed.
				$this->add_feed_error( esc_html__( 'Zoom API error when attempting registration: ' . print_r( $remote_request->get_response(), true ), 'gravity-zwr' ), $feed, $entry, $form ); // phpcs:ignore
				return false;
			} else {
				// Log that the registrant was added.
				$this->log_debug( __METHOD__ . '(): Registrant successfull: ' . print_r( $remote_request->get_body(), true ) ); // phpcs:ignore
			}
	
			return $entry;
	
		}


	}
}