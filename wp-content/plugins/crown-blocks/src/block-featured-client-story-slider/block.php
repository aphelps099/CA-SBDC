<?php

if(!class_exists('Crown_Block_Featured_Client_Story_Slider')) {
	class Crown_Block_Featured_Client_Story_Slider extends Crown_Block {


		public static $name = 'featured-client-story-slider';

		protected static $output_post_ids = array();


		public static function init() {
			parent::init();

			add_filter( 'crown_blocks_prev_post_ids', array( get_called_class(), 'filter_crown_blocks_prev_post_ids' ) );

		}


		public static function get_attributes() {
			return array(
				'className' => array( 'type' => 'string', 'default' => '' ),
				'maxPostCount' => array( 'type' => 'string', 'default' => '3' ),
				'manuallySelectPosts' => array( 'type' => 'boolean', 'default' => false ),
				'excludePrevPosts' => array( 'type' => 'boolean', 'default' => false ),
				'filterIndustries' => array( 'type' => 'array', 'default' => array(), 'items' => array( 'type' => 'object' ) ),
				'filterPostsExclude' => array( 'type' => 'array', 'default' => array(), 'items' => array( 'type' => 'object' ) ),
				'filterPostsInclude' => array( 'type' => 'array', 'default' => array(), 'items' => array( 'type' => 'object' ) )
			);
		}


		public static function render( $atts, $content ) {
			global $post;

			$queryArgs = array(
				'post_type' => 'client_story',
				'posts_per_page' => $atts['maxPostCount'],
				'tax_query' => array(),
				'post__not_in' => array(),
				'post__in' => array()
			);

			if ( boolval( $atts['excludePrevPosts'] ) ) {
				$prev_post_ids = apply_filters( 'crown_blocks_prev_post_ids', array() );
				$queryArgs['post__not_in'] = array_unique( array_merge( $queryArgs['post__not_in'], $prev_post_ids ) );
			}

			if ( ! empty( $atts['filterIndustries'] ) ) {
				$queryArgs['tax_query'][] = array( 'taxonomy' => 'client_story_industry', 'terms' => array_map( function( $n ) { return $n['id']; }, $atts['filterIndustries'] ) );
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

			$query = new WP_Query( $queryArgs );
			if ( ! $query->have_posts() ) return '';

			$block_class = array( 'wp-block-crown-blocks-featured-client-story-slider', $atts['className'] );

			ob_start();
			// print_r($atts);
			?>

				<div class="<?php echo implode( ' ', $block_class ); ?>">
					<div class="inner">

						<div class="post-feed item-count-<?php echo $query->post_count; ?>" data-item-count="<?php echo $query->post_count; ?>">
							<div class="inner">

								<?php while ( $query->have_posts() ) { ?>
									<?php $query->the_post(); ?>
									<article <?php post_class(); ?>>
										<a href="<?php the_permalink(); ?>">
											<?php $color = get_post_meta( get_the_ID(), 'client_story_color', true ); ?>
											<div class="bg" <?php echo ! empty( $color ) ? 'style="background-color: ' . $color . ';"' : '' ?>>
												<?php if ( has_post_thumbnail() && ( $image_src = wp_get_attachment_image_url( get_post_thumbnail_id(), 'large' ) ) ) { ?>
													<div class="bg-image" style="background-image: url(<?php echo $image_src; ?>);"></div>
												<?php } ?>
											</div>
											<div class="inner">

												<h3 class="entry-title"><?php the_title(); ?></h3>

												<?php $industries = get_the_terms( get_the_ID(), 'client_story_industry' ); ?>
												<?php if ( ! empty( $industries ) ) { ?>
													<p class="entry-industries" style="display: none;">
														<?php foreach ( $industries as $term ) { ?>
															<span class="industry"><?php echo $term->name; ?></span>
														<?php } ?>
													</p>
												<?php } ?>

											</div>
										</a>
									</article>
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
	Crown_Block_Featured_Client_Story_Slider::init();
}