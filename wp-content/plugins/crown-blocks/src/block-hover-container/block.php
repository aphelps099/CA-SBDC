<?php

if(!class_exists('Crown_Block_Hover_Container')) {
	class Crown_Block_Hover_Container extends Crown_Block {


		public static $name = 'hover-container';

		protected static $modal_forms = array();


		public static function init() {
			parent::init();

			add_action( 'render_block', array( get_called_class(), 'filter_render_block' ), 10, 2 );

			add_filter( 'ct_footer_modal_forms', array( get_called_class(), 'filter_ct_footer_modal_forms' ) );

		}


		public static function filter_render_block( $block_content, $block ) {
			if ( $block['blockName'] !== 'crown-blocks/hover-container' ) return $block_content;
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
			return $block_content;
		}


		public static function filter_ct_footer_modal_forms( $forms ) {
			return array_merge( $forms, self::$modal_forms );
		}


	}
	Crown_Block_Hover_Container::init();
}