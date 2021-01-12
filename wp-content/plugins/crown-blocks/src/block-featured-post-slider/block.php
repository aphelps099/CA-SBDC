<?php

if(!class_exists('Crown_Block_Featured_Post_Slider')) {
	class Crown_Block_Featured_Post_Slider extends Crown_Block {


		public static $name = 'featured-post-slider';

		protected static $output_post_ids = array();


		public static function init() {
			parent::init();

			add_filter( 'crown_blocks_prev_post_ids', array( get_called_class(), 'filter_crown_blocks_prev_post_ids' ) );

		}


		public static function get_attributes() {
			return array(
				'className' => array( 'type' => 'string', 'default' => '' ),
				'maxPostCount' => array( 'type' => 'string', 'default' => '9' ),
				'manuallySelectPosts' => array( 'type' => 'boolean', 'default' => false ),
				'excludePrevPosts' => array( 'type' => 'boolean', 'default' => false ),
				'filterCategories' => array( 'type' => 'array', 'default' => array(), 'items' => array( 'type' => 'object' ) ),
				'filterTags' => array( 'type' => 'array', 'default' => array(), 'items' => array( 'type' => 'object' ) ),
				'filterTopics' => array( 'type' => 'array', 'default' => array(), 'items' => array( 'type' => 'object' ) ),
				'filterPostsExclude' => array( 'type' => 'array', 'default' => array(), 'items' => array( 'type' => 'object' ) ),
				'filterPostsInclude' => array( 'type' => 'array', 'default' => array(), 'items' => array( 'type' => 'object' ) )
			);
		}


		public static function render( $atts, $content ) {
			global $post;

			$queryArgs = array(
				'post_type' => 'post',
				'posts_per_page' => $atts['maxPostCount'],
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

			if ( ! empty( $atts['filterTopics'] ) ) {
				$queryArgs['tax_query'][] = array( 'taxonomy' => 'post_topic', 'terms' => array_map( function( $n ) { return $n['id']; }, $atts['filterTopics'] ) );
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

			$block_class = array( 'wp-block-crown-blocks-featured-post-slider', $atts['className'] );

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

										<?php $color = class_exists( 'Crown_Site_Settings_Posts' ) && method_exists( 'Crown_Site_Settings_Posts', 'get_post_primary_category_color' ) ? Crown_Site_Settings_Posts::get_post_primary_category_color( get_the_ID() ) : false; ?>
										<div class="entry-date">
											<div class="inner">
												<span class="month"><?php the_time( 'M' ); ?></span>
												<span class="date"><?php the_time( 'j' ); ?></span>
												<span class="line" <?php echo ! empty( $color ) ? 'style="border-color: ' . $color . ';"' : ''; ?>></span>
											</div>
										</div>

										<?php $topics = get_the_terms( get_the_ID(), 'post_topic' ); ?>
										<?php if ( ! empty( $topics ) ) { ?>
											<p class="entry-topics">
												<?php foreach ( $topics as $term ) { ?>
													<a class="topic" href="<?php echo get_home_url(); ?>/news/?p_topic=<?php echo $term->term_id; ?>"><?php echo $term->name; ?></a>
												<?php } ?>
											</p>
										<?php } ?>

										<div class="entry-header">
											<h4 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
										</div>

										<p class="entry-link">
											<a href="<?php the_permalink(); ?>"><?php _e( 'Read Full Article', 'crown_blocks' ); ?></a>
										</p>
										
									</article>
								<?php } ?>
								<?php wp_reset_postdata(); ?>

							</div>
						</div>

						<p class="block-link">
							<a href="<?php echo get_post_type_archive_link( 'post' ); ?>" class="action-subject-link">
								<span class="action"><?php _e( 'View All' ); ?></span>
								<span class="subject"><?php _e( 'SBDC Updates' ); ?></span>
							</a>
						</p>

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
	Crown_Block_Featured_Post_Slider::init();
}