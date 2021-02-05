<?php

if(!class_exists('Crown_Block_Button')) {
	class Crown_Block_Button extends Crown_Block {


		public static $name = 'button';

		protected static $modal_form_ids = array();


		public static function init() {
			parent::init();

			add_action( 'render_block', array( get_called_class(), 'filter_render_block' ), 10, 2 );

			add_filter( 'ct_footer_modal_form_ids', array( get_called_class(), 'filter_ct_footer_modal_form_ids' ) );

		}


		public static function filter_render_block( $block_content, $block ) {
			if ( $block['blockName'] !== 'crown-blocks/button' ) return $block_content;
			if ( isset( $block['attrs']['openModal'] ) && boolval( $block['attrs']['openModal'] ) ) {
				$type = isset( $block['attrs']['linkModalType'] ) ? $block['attrs']['linkModalType'] : '';
				if ( $type == 'form' ) {
					$form_id = isset( $block['attrs']['linkModalFormId'] ) ? intval($block['attrs']['linkModalFormId']) : 0;
					if ( ! empty( $form_id ) ) self::$modal_form_ids[] = $form_id;
				}
			}
			return $block_content;
		}


		public static function filter_ct_footer_modal_form_ids( $form_ids ) {
			return array_merge( $form_ids, self::$modal_form_ids );
		}


	}
	Crown_Block_Button::init();
}