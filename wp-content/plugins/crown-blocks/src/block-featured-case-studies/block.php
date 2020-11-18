<?php

if(!class_exists('Crown_Block_Featured_Case_Studies')) {
	class Crown_Block_Featured_Case_Studies extends Crown_Block {


		public static $name = 'featured-case-studies';

		protected static $output_post_ids = array();


		public static function init() {
			parent::init();

			add_filter( 'crown_blocks_prev_post_ids', array( get_called_class(), 'filter_crown_blocks_prev_post_ids' ) );

		}


		public static function get_attributes() {
			return array(
				'className' => array( 'type' => 'string', 'default' => '' ),
				'maxPostCount' => array( 'type' => 'string', 'default' => '2' ),
				'manuallySelectPosts' => array( 'type' => 'boolean', 'default' => false ),
				'excludePrevPosts' => array( 'type' => 'boolean', 'default' => false ),
				'filterServices' => array( 'type' => 'array', 'default' => array(), 'items' => array( 'type' => 'object' ) ),
				'filterClientTypes' => array( 'type' => 'array', 'default' => array(), 'items' => array( 'type' => 'object' ) ),
				'filterPostsExclude' => array( 'type' => 'array', 'default' => array(), 'items' => array( 'type' => 'object' ) ),
				'filterPostsInclude' => array( 'type' => 'array', 'default' => array(), 'items' => array( 'type' => 'object' ) )
			);
		}


		public static function render( $atts, $content ) {
			global $post;

			$queryArgs = array(
				'post_type' => 'case_study',
				'tax_query' => array(),
				'post__not_in' => array(),
				'post__in' => array()
			);

			if ( boolval( $atts['excludePrevPosts'] ) ) {
				$prev_post_ids = apply_filters( 'crown_blocks_prev_post_ids', array() );
				$queryArgs['post__not_in'] = array_unique( array_merge( $queryArgs['post__not_in'], $prev_post_ids ) );
			}

			if ( ! empty( $atts['filterServices'] ) ) {
				$queryArgs['tax_query'][] = array( 'taxonomy' => 'case_study_service', 'terms' => array_map( function( $n ) { return $n['id']; }, $atts['filterServices'] ) );
			}

			if ( ! empty( $atts['filterClientTypes'] ) ) {
				$queryArgs['tax_query'][] = array( 'taxonomy' => 'case_study_client_type', 'terms' => array_map( function( $n ) { return $n['id']; }, $atts['filterClientTypes'] ) );
			}

			if ( ! empty( $atts['filterPostsExclude'] ) ) {
				$queryArgs['post__not_in'] = array_unique( array_merge( $queryArgs['post__not_in'], array_map( function( $n ) { return $n['id']; }, $atts['filterPostsExclude'] ) ) );
			}

			if ( $atts['manuallySelectPosts'] ) {
				if ( empty( $atts['filterPostsInclude'] ) ) return '';
				$queryArgs['tax_query'] = array();
				$queryArgs['post__not_in'] = array();
				$queryArgs['post__in'] = array_unique( array_merge( $queryArgs['post__not_in'], array_map( function( $n ) { return $n['id']; }, $atts['filterPostsInclude'] ) ) );
				$queryArgs['orderby'] = 'post__in';
				$queryArgs['order'] = 'ASC';
			}

			$queryArgs['posts_per_page'] = $atts['maxPostCount'];

			$query = new WP_Query( $queryArgs );
			if ( ! $query->have_posts() ) return '';

			$block_class = array( 'wp-block-crown-blocks-featured-case-studies', $atts['className'] );

			ob_start();
			// print_r($atts);
			?>

				<div class="<?php echo implode( ' ', $block_class ); ?>">
					<div class="inner">

						<div class="post-feed item-count-<?php echo $query->post_count; ?>" data-item-count="<?php echo $query->post_count; ?>">
							<div class="inner">

								<?php while ( $query->have_posts() ) { ?>
									<?php $query->the_post(); ?>
									<?php if ( class_exists( 'Crown_Case_Studies' ) ) Crown_Case_Studies::the_case_study(); ?>
								<?php } ?>
								<?php wp_reset_postdata(); ?>

							</div>
						</div>

					</div>
				</div>

			<?php
			$output = ob_get_clean();

			self::add_output_post_ids( array_map( function($n) { return $n->ID; }, $query->posts ) );

			return $output;
		}


		protected static function get_output_post_ids() {
			return self::$output_post_ids;
		}


		protected static function add_output_post_ids( $output_post_ids = array() ) {
			self::$output_post_ids = array_unique( array_merge( self::$output_post_ids, $output_post_ids ) );
		}


		public static function filter_crown_blocks_prev_post_ids( $prev_post_ids = array() ) {
			return array_unique( array_merge( $prev_post_ids, self::get_output_post_ids() ) );
		}


	}
	Crown_Block_Featured_Case_Studies::init();
}