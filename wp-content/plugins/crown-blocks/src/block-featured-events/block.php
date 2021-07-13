<?php

if(!class_exists('Crown_Block_Featured_Events')) {
	class Crown_Block_Featured_Events extends Crown_Block {


		public static $name = 'featured-events';

		protected static $output_post_ids = array();


		public static function init() {
			parent::init();

			add_filter( 'crown_blocks_prev_post_ids', array( get_called_class(), 'filter_crown_blocks_prev_post_ids' ) );

		}


		public static function get_attributes() {
			return array(
				'className' => array( 'type' => 'string', 'default' => '' ),
				'configuration' => array( 'type' => 'string', 'default' => 'slider' ),
				'maxPostCount' => array( 'type' => 'string', 'default' => '3' ),
				'manuallySelectPosts' => array( 'type' => 'boolean', 'default' => false ),
				'filterCenters' => array( 'type' => 'array', 'default' => array(), 'items' => array( 'type' => 'object' ) ),
				'filterTopics' => array( 'type' => 'array', 'default' => array(), 'items' => array( 'type' => 'object' ) ),
				'filterSeries' => array( 'type' => 'array', 'default' => array(), 'items' => array( 'type' => 'object' ) ),
				'filterPostsExclude' => array( 'type' => 'array', 'default' => array(), 'items' => array( 'type' => 'object' ) ),
				'filterPostsInclude' => array( 'type' => 'array', 'default' => array(), 'items' => array( 'type' => 'object' ) )
			);
		}


		public static function render( $atts, $content ) {
			global $post;

			$event_args = array(
				'from' => date( 'Y-m-d H:i:s' ),
				'tax_query' => array(),
				'include_syndicated' => true
			);

			if ( ! empty( $atts['filterCenters'] ) ) {
				$event_args['tax_query'][] = array( 'taxonomy' => 'post_center', 'terms' => array_map( function( $n ) { return $n['id']; }, $atts['filterCenters'] ) );
			}

			if ( ! empty( $atts['filterTopics'] ) ) {
				$event_args['tax_query'][] = array( 'taxonomy' => 'post_topic', 'terms' => array_map( function( $n ) { return $n['id']; }, $atts['filterTopics'] ) );
			}

			if ( ! empty( $atts['filterSeries'] ) ) {
				$event_args['tax_query'][] = array( 'taxonomy' => 'event_series', 'terms' => array_map( function( $n ) { return $n['id']; }, $atts['filterSeries'] ) );
			}

			$query_args = Crown_Events::get_event_query_args( $event_args );

			$query_args = array_merge( $query_args, array(
				'posts_per_page' => $atts['maxPostCount']
			) );

			if ( ! empty( $atts['filterPostsExclude'] ) ) {
				$query_args['post__not_in'] = array_unique( array_merge( array(), array_map( function( $n ) { return $n['id']; }, $atts['filterPostsExclude'] ) ) );
			}

			if ( $atts['manuallySelectPosts'] ) {
				if ( empty( $atts['filterPostsInclude'] ) ) return '';
				$query_args['tax_query'] = array();
				$query_args['meta_query'] = array();
				$query_args['post__not_in'] = array();
				$query_args['post__in'] = array_unique( array_merge( array(), array_map( function( $n ) { return $n['id']; }, $atts['filterPostsInclude'] ) ) );
				$query_args['orderby'] = 'post__in';
				$query_args['order'] = 'ASC';
				// $query_args['posts_per_page'] = count( $query_args['post__in'] );
			}

			$query = new WP_Query( $query_args );
			if ( ! $query->have_posts() ) return '';

			$block_class = array( 'wp-block-crown-blocks-featured-events', $atts['className'], 'configuration-' . $atts['configuration'] );

			ob_start();
			// print_r($query_args);
			?>

				<div class="<?php echo implode( ' ', $block_class ); ?>">
					<div class="inner">

						<div class="post-feed item-count-<?php echo $query->post_count; ?>" data-item-count="<?php echo $query->post_count; ?>">
							<div class="inner">

								<?php while ( $query->have_posts() ) { ?>
									<?php $query->the_post(); ?>
									<?php
										$switched_site = false;
										$event_site_title = get_bloginfo( 'name' );
										if ( get_post_type() == 'event_s' ) {
											$original_post_id = get_post_meta( get_the_ID(), '_original_post_id', true );
											switch_to_blog( get_post_meta( get_the_ID(), '_original_site_id', true ) );
											$post = get_post( $original_post_id );
											setup_postdata( $post );
											$event_site_title = get_bloginfo( 'name' );
											$switched_site = true;
										}
									?>
									<article <?php post_class(); ?>>
										<a href="<?php the_permalink(); ?>" data-post-id="<?php echo get_the_ID(); ?>">
											<div class="inner">

												<header class="entry-header">

													<?php $centers = get_the_terms( get_the_ID(), 'post_center' ); ?>
													<?php if ( ! empty( $centers ) ) { ?>
														<p class="entry-centers">
															<?php foreach ( $centers as $term ) { ?>
																<span class="center"><?php echo $term->name; ?></span>
															<?php } ?>
														</p>
													<?php } else if ( $event_site_title ) { ?>
														<p class="entry-centers">
															<span class="center <?php echo ! $switched_site ? 'self' : ''; ?>"><?php echo $event_site_title; ?></span>
														</p>
													<?php } ?>

													<div class="entry-event-date-container">
														<?php if ( function_exists( 'ct_event_date' ) ) ct_event_date( get_the_ID() ); ?>
													</div>

													<h3 class="entry-title"><?php the_title(); ?></h3>

												</header>

											</div>
										</a>
									</article>
									<?php if ( $switched_site ) restore_current_blog(); ?>
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


		public static function get_output_post_ids() {
			return self::$output_post_ids;
		}


		public static function add_output_post_ids( $output_post_ids = array() ) {
			self::$output_post_ids = array_unique( array_merge( self::$output_post_ids, $output_post_ids ) );
		}


		public static function filter_crown_blocks_prev_post_ids( $prev_post_ids = array() ) {
			return array_unique( array_merge( $prev_post_ids, self::get_output_post_ids() ) );
		}


	}
	Crown_Block_Featured_Events::init();
}