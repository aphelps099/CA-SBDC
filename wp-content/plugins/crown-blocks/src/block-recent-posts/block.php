<?php

if(!class_exists('Crown_Block_Recent_Posts')) {
	class Crown_Block_Recent_Posts extends Crown_Block {


		public static $name = 'recent-posts';

		protected static $output_post_ids = array();


		public static function init() {
			parent::init();

			add_filter( 'crown_blocks_prev_post_ids', array( get_called_class(), 'filter_crown_blocks_prev_post_ids' ) );

		}


		public static function get_attributes() {
			return array(
				'className' => array( 'type' => 'string', 'default' => '' ),
				'align' => array( 'type' => 'string', 'default' => '' ),
				'displayAs' => array( 'type' => 'string', 'default' => 'thumbnails' ),
				'thumbnailsMaxPostCount' => array( 'type' => 'string', 'default' => '3' ),
				'displayAsSliderMobile' => array( 'type' => 'boolean', 'default' => false ),
				'listMaxPostCount' => array( 'type' => 'string', 'default' => '4' ),
				'manuallySelectPosts' => array( 'type' => 'boolean', 'default' => false ),
				'excludePrevPosts' => array( 'type' => 'boolean', 'default' => false ),
				'filterCategories' => array( 'type' => 'array', 'default' => array(), 'items' => array( 'type' => 'object' ) ),
				'filterTags' => array( 'type' => 'array', 'default' => array(), 'items' => array( 'type' => 'object' ) ),
				'filterPostsExclude' => array( 'type' => 'array', 'default' => array(), 'items' => array( 'type' => 'object' ) ),
				'filterPostsInclude' => array( 'type' => 'array', 'default' => array(), 'items' => array( 'type' => 'object' ) )
			);
		}


		public static function render( $atts, $content ) {
			global $post;

			$queryArgs = array(
				'post_type' => 'post',
				'tax_query' => array(),
				'post__not_in' => array(),
				'post__in' => array()
			);

			if ( boolval( $atts['excludePrevPosts'] ) ) {
				$prev_post_ids = apply_filters( 'crown_blocks_prev_post_ids', array() );
				$queryArgs['post__not_in'] = array_unique( array_merge( $queryArgs['post__not_in'], $prev_post_ids ) );
			}

			if ( ! empty( $atts['filterCategories'] ) ) {
				$queryArgs['tax_query'][] = array( 'taxonomy' => 'category', 'terms' => array_map( function( $n ) { return $n['id']; }, $atts['filterCategories'] ) );
			}

			if ( ! empty( $atts['filterTags'] ) ) {
				$queryArgs['tax_query'][] = array( 'taxonomy' => 'post_tag', 'terms' => array_map( function( $n ) { return $n['id']; }, $atts['filterTags'] ) );
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

			if ( $atts['displayAs'] == 'list' ) {
				$queryArgs['posts_per_page'] = $atts['listMaxPostCount'];
			} else {
				$queryArgs['posts_per_page'] = $atts['thumbnailsMaxPostCount'];
			}

			$query = new WP_Query( $queryArgs );
			if ( ! $query->have_posts() ) return '';

			$block_class = array( 'wp-block-crown-blocks-recent-posts', 'display-as-' . $atts['displayAs'], $atts['className'] );
			if ( ! empty( $atts['align'] ) ) $block_class[] = 'align' . $atts['align'];
			if ( boolval( $atts['displayAsSliderMobile'] ) ) $block_class[] = 'display-as-slider-mobile';

			ob_start();
			// print_r($atts);
			?>

				<div class="<?php echo implode( ' ', $block_class ); ?>">
					<div class="inner">

						<div class="post-feed item-count-<?php echo $query->post_count; ?> <?php echo $atts['displayAs']; ?>" data-item-count="<?php echo $query->post_count; ?>">
							<div class="inner">

								<?php if ( $atts['displayAs'] == 'list' ) { ?>
	
									<ul>
										<?php while ( $query->have_posts() ) { ?>
											<?php $query->the_post(); ?>
											<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
										<?php } ?>
									</ul>
	
								<?php } else { ?>
	
									<?php while ( $query->have_posts() ) { ?>
										<?php $query->the_post(); ?>
										<article <?php post_class(); ?>>

											<div class="entry-thumbnail">
												<div class="inner">
													<a href="<?php the_permalink(); ?>">
														<?php $image_src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'medium_large' ); ?>
														<div class="image" <?php echo $image_src ? 'style="background-image: url(' . $image_src[0] . ');"' : ''; ?>>
															<?php echo wp_get_attachment_image( get_post_thumbnail_id(), 'medium_large' ); ?>
														</div>
													</a>
												</div>
											</div>

											<div class="entry-header">
												<h4 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
											</div>

											<div class="entry-excerpt">
												<?php the_excerpt(); ?>
											</div>
											
										</article>
									<?php } ?>

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
	Crown_Block_Recent_Posts::init();
}