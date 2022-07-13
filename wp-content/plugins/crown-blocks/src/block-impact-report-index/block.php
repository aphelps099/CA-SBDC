<?php

if(!class_exists('Crown_Block_Impact_Report_Index')) {
	class Crown_Block_Impact_Report_Index extends Crown_Block {


		public static $name = 'impact-report-index';


		public static function init() {
			parent::init();
		}


		public static function get_attributes() {
			return array(
				'className' => array( 'type' => 'string', 'default' => '' ),
				'postsPerPage' => array( 'type' => 'string', 'default' => '12' )
			);
		}


		public static function render( $atts, $content ) {
			global $post;

			$filters = (object) array(
				'region' => (object) array( 'key' => 'r_region', 'queried' => null, 'options' => array() ),
				'rep_type' => (object) array( 'key' => 'r_rep_type', 'queried' => null, 'options' => array() ),
				'district_no' => (object) array( 'key' => 'r_district_no', 'queried' => null, 'options' => array() )
			);

			// $atts['postsPerPage'] = 1;
			$query_args = array(
				'post_type' => array( 'impact_report' ),
				'posts_per_page' => $atts['postsPerPage'],
				'paged' => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
				'tax_query' => array(),
				'meta_query' => array()
			);

			$filters->region->queried = isset( $_GET[ $filters->region->key ] ) ? ( is_array( $_GET[ $filters->region->key ] ) ? $_GET[ $filters->region->key ] : array_filter( array_map( 'trim', explode( ',', $_GET[ $filters->region->key ] ) ), function( $n ) { return ! empty( $n ); } ) ) : array();
			if ( ! empty( $filters->region->queried ) ) $query_args['tax_query'][] = array( 'taxonomy' => 'ir_region', 'terms' => $filters->region->queried );

			$filters->rep_type->queried = isset( $_GET[ $filters->rep_type->key ] ) ? ( is_array( $_GET[ $filters->rep_type->key ] ) ? $_GET[ $filters->rep_type->key ] : array_filter( array_map( 'trim', explode( ',', $_GET[ $filters->rep_type->key ] ) ), function( $n ) { return ! empty( $n ); } ) ) : array();
			if ( ! empty( $filters->rep_type->queried ) ) $query_args['tax_query'][] = array( 'taxonomy' => 'ir_rep_type', 'terms' => $filters->rep_type->queried );

			$filters->district_no->queried = isset( $_GET[ $filters->district_no->key ] ) ? ( is_array( $_GET[ $filters->district_no->key ] ) ? $_GET[ $filters->district_no->key ] : array_filter( array_map( 'trim', explode( ',', $_GET[ $filters->district_no->key ] ) ), function( $n ) { return ! empty( $n ); } ) ) : array();
			if ( ! empty( $filters->district_no->queried ) ) $query_args['tax_query'][] = array( 'taxonomy' => 'ir_district_no', 'terms' => $filters->district_no->queried );

			$query = null;
			if ( function_exists( 'relevanssi_do_query' ) && isset( $query_args['s'] ) && ! empty( $query_args['s'] ) ) {
				$query = new WP_Query();
				$query->parse_query( $query_args );
				relevanssi_do_query( $query );
			} else {
				$query = new WP_Query( $query_args );
			}

			$filters_action = remove_query_arg( array(
				$filters->region->key,
				// $filters->rep_type->key,
				// $filters->district_no->key
			) );
			$filters_action = preg_replace( '/\/page\/\d+\/(\?.*)?$/', "/$1", $filters_action );

			$filters->region->options = array_map( function( $n ) use ( $filters ) {
				return (object) array( 'value' => $n->term_id, 'label' => $n->name, 'selected' => in_array( $n->term_id, $filters->region->queried ) );
			}, get_terms( array( 'taxonomy' => 'ir_region' ) ) );

			$filters->rep_type->options = array_map( function( $n ) use ( $filters ) {
				return (object) array( 'value' => $n->term_id, 'label' => $n->name, 'selected' => in_array( $n->term_id, $filters->rep_type->queried ) );
			}, get_terms( array( 'taxonomy' => 'ir_rep_type' ) ) );

			$filters->district_no->options = array_map( function( $n ) use ( $filters ) {
				return (object) array( 'value' => $n->term_id, 'label' => $n->name, 'selected' => in_array( $n->term_id, $filters->district_no->queried ) );
			}, get_terms( array( 'taxonomy' => 'ir_district_no' ) ) );

			$block_class = array( 'wp-block-crown-blocks-impact-report-index', 'post-feed-block', $atts['className'] );
			$block_id = 'post-feed-block-' . md5( json_encode( array( 'impact-report-index', $atts ) ) );

			ob_start();
			// print_r($filters);
			?>

				<div id="<?php echo $block_id; ?>" class="<?php echo implode( ' ', $block_class ); ?>">
					<div class="inner">

						<form action="<?php echo $filters_action; ?>" method="get" class="feed-filters">

							<header class="filters-header">

								<span class="filters-title"><span><?php _e( 'Filter', 'crown_blocks' ); ?></span></span>
								<button type="button" class="filters-clear"><span><?php _e( 'Clear', 'crown_blocks' ); ?></span></button>

								<?php if ( ! empty( $filters->region->options ) ) { ?>
										<ul class="options singular quick-filters">
											<?php foreach ( $filters->region->options as $option ) { ?>
												<li class="option">
													<label>
														<input type="checkbox" name="<?php echo $filters->region->key; ?>[]" value="<?php echo esc_attr( $option->value ); ?>" <?php echo $option->selected ? 'checked' : ''; ?>>
														<span class="label"><?php echo $option->label; ?></span>
													</label>
												</li>
											<?php } ?>
										</ul>
									<?php } ?>

								<nav class="filters-nav">
									<ul>
										<?php if ( ! empty( $filters->region->options ) ) { ?><li><button type="button" data-tab="region"><?php _e( 'Region', 'crown_blocks' ); ?></button></li><?php } ?>
									</ul>
								</nav>

							</header>

							<div class="filters-tabs">
								<div class="inner">

									<?php if ( ! empty( $filters->region->options ) ) { ?>
										<div class="filters-tab" data-tab="region">
											<ul class="options singular region">
												<?php foreach ( $filters->region->options as $option ) { ?>
													<li class="option">
														<label>
															<input type="checkbox" name="<?php echo $filters->region->key; ?>[]" value="<?php echo esc_attr( $option->value ); ?>" <?php echo $option->selected ? 'checked' : ''; ?>>
															<span class="label"><?php echo $option->label; ?></span>
														</label>
													</li>
												<?php } ?>
											</ul>
										</div>
									<?php } ?>

								</div>
							</div>

							<footer class="filters-footer">
								<button type="submit"><?php _e( 'Submit', 'crown_blocks' ); ?></button>
							</footer>

						</form>

						<div class="ajax-loader infinite">
							<div class="ajax-content">

								<div class="post-feed item-count-<?php echo $query->post_count; ?>" data-item-count="<?php echo $query->post_count; ?>">
									<div class="inner infinite-loader-container">
	
										<?php if ( ! $query->have_posts() ) { ?>
											<div class="alert-wrapper">
												<div class="alert alert-info no-results">
													<h4>No Resources Found</h4>
													<p>Please try adjusting your selected filters above.</p>
												</div>
											</div>
										<?php } ?>
										
										<?php for ( $i = 0; $i < 1; $i++ ) { ?>
										<?php while ( $query->have_posts() ) { ?>
											<?php $query->the_post(); ?>
											<article <?php post_class(); ?>>
												<a href="<?php the_permalink(); ?>">

													<h4 class="entry-title"><?php the_title(); ?></h4>

													<?php $rep_types = get_the_terms( get_the_ID(), 'ir_rep_type' ); ?>
													<?php if ( ! empty( $rep_types ) ) { ?>
														<p class="entry-rep-types">
															<?php foreach ( $rep_types as $term ) { ?>
																<span class="rep-type"><?php echo $term->name; ?></span>
															<?php } ?>
														</p>
													<?php } ?>

													<div class="entry-link">
														<?php $label = get_post_meta( get_the_ID(), 'impact_report_link_label', true ); ?>
														<span class="link-label"><?php echo ! empty( $label ) ? $label : 'Download'; ?></span>
													</div>

												</a>
											</article>
										<?php } ?>
										<?php } ?>
										<?php wp_reset_postdata(); ?>
	
									</div>
								</div>
	
								<?php //self::render_pagination( $query, 5 ); ?>
								<?php self::render_pagination_infinite( $query ); ?>

							</div>
						</div>

					</div>
				</div>

			<?php
			$output = ob_get_clean();

			return $output;
		}


	}
	Crown_Block_Impact_Report_Index::init();
}