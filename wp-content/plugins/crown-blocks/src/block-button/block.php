<?php

if(!class_exists('Crown_Block_Button')) {
	class Crown_Block_Button extends Crown_Block {


		public static $name = 'button';

		protected static $modal_forms = array();


		public static function init() {
			parent::init();

			add_action( 'wp_enqueue_scripts', array( get_called_class(), 'register_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( get_called_class(), 'register_styles' ) );

			add_action( 'render_block', array( get_called_class(), 'filter_render_block' ), 10, 2 );

			add_filter( 'ct_footer_modal_forms', array( get_called_class(), 'filter_ct_footer_modal_forms' ) );

		}


		public static function register_scripts() {
			if ( ! wp_script_is( 'calendly-widget', 'registered' ) ) {
				wp_register_script( 'calendly-widget', 'https://assets.calendly.com/assets/external/widget.js', array(), false, true );
			}
		}


		public static function register_styles() {
			if ( ! wp_style_is( 'calendly-widget', 'registered' ) ) {
				wp_register_style( 'calendly-widget', 'https://assets.calendly.com/assets/external/widget.css' );
			}
		}


		public static function filter_render_block( $block_content, $block ) {
			if ( $block['blockName'] !== 'crown-blocks/button' ) return $block_content;
			
			if ( isset( $block['attrs']['openModal'] ) && boolval( $block['attrs']['openModal'] ) ) {
				$type = isset( $block['attrs']['linkModalType'] ) ? $block['attrs']['linkModalType'] : '';
				if ( $type == 'form' ) {
					$form_id = isset( $block['attrs']['linkModalFormId'] ) ? intval($block['attrs']['linkModalFormId']) : 0;
					if ( ! empty( $form_id ) ) self::$modal_forms[] = array( 'id' => $form_id );
				} else if ( $type == 'zoom_meeting_registration' ) {
					$form_id = get_option( 'theme_config_events_zoom_meeting_registration_form' );
					$meeting_id = isset( $block['attrs']['linkModalMeetingId'] ) ? intval($block['attrs']['linkModalMeetingId']) : 0;
					if ( ! empty( $form_id ) && ! empty( $meeting_id ) ) self::$modal_forms[] = array( 'id' => $form_id, 'type' => 'event-registration-zoom-meeting', 'field_values' => array( 'meeting_id' => $meeting_id ) );
				}
			}

			if ( isset( $block['attrs']['linkUrl'] ) ) {
				$link_url = $block['attrs']['linkUrl'];
				if ( preg_match( '/^https?:\/\/calendly\.com\/.+/', $link_url ) ) {
					wp_enqueue_script( 'calendly-widget' );
					wp_enqueue_style( 'calendly-widget' );
				}
			}

			if ( isset( $block['attrs']['openEventRegistration'] ) && boolval( $block['attrs']['openEventRegistration'] ) ) {

				$form_id = get_option( 'theme_config_events_zoom_meeting_registration_form' );
				if ( empty( $form_id ) ) return '';

				$field_values = array(
					'meeting_id' => isset( $block['attrs']['meetingId'] ) ? intval( $block['attrs']['meetingId'] ) : 0,
					'meeting_type' => isset( $block['attrs']['meetingType'] ) ? $block['attrs']['meetingType'] : 'meetings',
				);

				$block_content .= '<div class="collapse form-event-registration-zoom-meeting" id="form-event-registration-zoom-meeting-' . $field_values['meeting_id'] . '"><div class="inner">' . gravity_form( $form_id, false, false, false, $field_values, true, -1, false ) . '</div></div>';

			}

			return $block_content;
		}


		public static function filter_ct_footer_modal_forms( $forms ) {
			return array_merge( $forms, self::$modal_forms );
		}


	}
	Crown_Block_Button::init();
}