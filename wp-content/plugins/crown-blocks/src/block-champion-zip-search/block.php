<?php

if(!class_exists('Crown_Block_Champion_Zip_Search')) {
	class Crown_Block_Champion_Zip_Search extends Crown_Block {


		public static $name = 'champion-zip-search';


		public static function init() {
			parent::init();

		}


		public static function get_attributes() {
			return array(
				'className' => array( 'type' => 'string', 'default' => '' )
			);
		}


		public static function render( $atts, $content ) {
			global $post;
			if ( ! class_exists( 'Crown_Champions' ) ) return '';
			$is_editor = defined( 'REST_REQUEST' ) && true === REST_REQUEST && 'edit' === filter_input( INPUT_GET, 'context', FILTER_SANITIZE_STRING );

			$block_class = array( 'wp-block-crown-blocks-champion-zip-search', $atts['className'] );

			ob_start();
			// print_r($atts);
			?>

				<div <?php post_class( $block_class ); ?>>
					<?php Crown_Champions::champion_zip_search( $is_editor ); ?>
				</div>

			<?php
			$output = ob_get_clean();
			wp_reset_postdata();

			return $output;
		}


	}
	Crown_Block_Champion_Zip_Search::init();
}