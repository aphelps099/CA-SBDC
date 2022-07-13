<?php

if(!class_exists('Crown_Block_Impact_Report_Search')) {
	class Crown_Block_Impact_Report_Search extends Crown_Block {


		public static $name = 'impact-report-search';


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

			$filters = (object) array(
				'rep_type' => (object) array( 'key' => 'r_rep_type', 'queried' => null, 'options' => array() ),
				'district_no' => (object) array( 'key' => 'r_district_no', 'queried' => null, 'options' => array() )
			);

			$filters->rep_type->queried = isset( $_GET[ $filters->rep_type->key ] ) ? ( is_array( $_GET[ $filters->rep_type->key ] ) ? $_GET[ $filters->rep_type->key ] : array_filter( array_map( 'trim', explode( ',', $_GET[ $filters->rep_type->key ] ) ), function( $n ) { return ! empty( $n ); } ) ) : array();
			$filters->district_no->queried = isset( $_GET[ $filters->district_no->key ] ) ? ( is_array( $_GET[ $filters->district_no->key ] ) ? $_GET[ $filters->district_no->key ] : array_filter( array_map( 'trim', explode( ',', $_GET[ $filters->district_no->key ] ) ), function( $n ) { return ! empty( $n ); } ) ) : array();

			$filters_action = remove_query_arg( array(
				$filters->rep_type->key,
				$filters->district_no->key
			) );
			$filters_action = preg_replace( '/\/page\/\d+\/(\?.*)?$/', "/$1", $filters_action );

			$filters->rep_type->options = array_map( function( $n ) use ( $filters ) {
				return (object) array( 'value' => $n->term_id, 'label' => $n->name, 'selected' => in_array( $n->term_id, $filters->rep_type->queried ) );
			}, get_terms( array( 'taxonomy' => 'ir_rep_type' ) ) );

			$filters->district_no->options = array_map( function( $n ) use ( $filters ) {
				return (object) array( 'value' => $n->term_id, 'label' => $n->name, 'selected' => in_array( $n->term_id, $filters->district_no->queried ) );
			}, get_terms( array( 'taxonomy' => 'ir_district_no' ) ) );

			$block_class = array( 'wp-block-crown-blocks-impact-report-search', $atts['className'] );
			$block_id = 'post-feed-block-' . md5( json_encode( array( 'impact-report-search', $atts ) ) );

			ob_start();
			// print_r($filters);
			?>

				<div id="<?php echo $block_id; ?>" class="<?php echo implode( ' ', $block_class ); ?>">
					<div class="inner">

						<form action="<?php echo $filters_action; ?>" method="get">

							<div class="fields">

								<div class="field rep-type">
									<label>I am a</label>
									<select name="<?php echo $filters->rep_type->key; ?>">
										<option value=""></option>
										<?php foreach ( $filters->rep_type->options as $option ) { ?>
											<option value="<?php echo esc_attr( $option->value ); ?>" <?php echo $option->selected ? 'selected' : ''; ?>><?php echo $option->label; ?></option>
										<?php } ?>
									</select>
								</div>

								<div class="field district-no">
									<label>My District # is:</label>
									<select name="<?php echo $filters->district_no->key; ?>">
										<option value=""></option>
										<?php foreach ( $filters->district_no->options as $option ) { ?>
											<option value="<?php echo esc_attr( $option->value ); ?>" <?php echo $option->selected ? 'selected' : ''; ?>><?php echo $option->label; ?></option>
										<?php } ?>
									</select>
								</div>

							</div>

							<footer class="filters-footer">
								<button type="submit"><?php _e( 'Submit', 'crown_blocks' ); ?></button>
							</footer>

						</form>

					</div>
				</div>

			<?php
			$output = ob_get_clean();

			return $output;
		}


	}
	Crown_Block_Impact_Report_Search::init();
}