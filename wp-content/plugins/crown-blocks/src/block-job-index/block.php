<?php

if(!class_exists('Crown_Block_Job_Index')) {
	class Crown_Block_Job_Index extends Crown_Block {


		public static $name = 'job-index';


		public static function init() {
			parent::init();
		}


		public static function get_attributes() {
			return array(
				'className' => array( 'type' => 'string', 'default' => '' ),
				'postsPerPage' => array( 'type' => 'string', 'default' => '10' )
			);
		}


		public static function render( $atts, $content ) {
			global $post;

			if ( is_singular( 'job' ) ) {
				return self::render_single_job( $atts, $content );
			}

			$filters = (object) array(
				'center' => (object) array( 'key' => 'j_center', 'queried' => null, 'options' => array() ),
				'search' => (object) array( 'key' => 'r_search', 'queried' => null, 'options' => array() )
			);

			// $atts['postsPerPage'] = 1;
			$query_args = array(
				'post_type' => 'job',
				'posts_per_page' => $atts['postsPerPage'],
				'paged' => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
				'tax_query' => array(),
				'meta_query' => array()
			);

			$filters->center->queried = isset( $_GET[ $filters->center->key ] ) ? ( is_array( $_GET[ $filters->center->key ] ) ? $_GET[ $filters->center->key ] : array_filter( array_map( 'trim', explode( ',', $_GET[ $filters->center->key ] ) ), function( $n ) { return ! empty( $n ); } ) ) : array();
			if ( ! empty( $filters->center->queried ) ) $event_args['tax_query'][] = array( 'taxonomy' => 'post_center', 'terms' => $filters->center->queried );

			$filters->search->queried = isset( $_GET[ $filters->search->key ] ) ? trim( $_GET[ $filters->search->key ] ) : '';
			if ( ! empty( $filters->search->queried ) ) $query_args['s'] = $filters->search->queried;

			$query = null;
			if ( function_exists( 'relevanssi_do_query' ) && isset( $query_args['s'] ) && ! empty( $query_args['s'] ) ) {
				$query = new WP_Query();
				$query->parse_query( $query_args );
				relevanssi_do_query( $query );
			} else {
				$query = new WP_Query( $query_args );
			}

			$filters_action = remove_query_arg( array(
				$filters->center->key,
				$filters->search->key
			) );
			$filters_action = preg_replace( '/\/page\/\d+\/(\?.*)?$/', "/$1", $filters_action );

			$block_class = array( 'wp-block-crown-blocks-job-index', 'post-feed-block', $atts['className'] );
			$block_id = 'post-feed-block-' . md5( json_encode( array( 'job-index', $atts ) ) );

			ob_start();
			// print_r($query);
			?>

				<div id="<?php echo $block_id; ?>" class="<?php echo implode( ' ', $block_class ); ?>">
					<div class="inner">

						<div class="ajax-loader infinite">
							<div class="ajax-content">

								<div class="post-feed item-count-<?php echo $query->post_count; ?>" data-item-count="<?php echo $query->post_count; ?>">
									<div class="inner infinite-loader-container">
	
										<?php if ( ! $query->have_posts() ) { ?>
											<div class="alert-wrapper">
												<div class="alert alert-info no-results">
													<h4>No Jobs Found</h4>
													<p>Please try adjusting your selected filters above.</p>
												</div>
											</div>
										<?php } ?>
										
										<?php while ( $query->have_posts() ) { ?>
											<?php $query->the_post(); ?>
											<article <?php post_class(); ?>>
												<a href="<?php the_permalink(); ?>" data-post-id="<?php echo get_the_ID(); ?>">
													<div class="inner">

														<?php $centers = get_the_terms( get_the_ID(), 'post_center' ); ?>
														<?php if ( ! empty( $centers ) ) { ?>
															<p class="entry-centers">
																<?php foreach ( $centers as $term ) { ?>
																	<span class="center"><?php echo $term->name; ?></span>
																<?php } ?>
															</p>
														<?php } ?>

														<h3 class="entry-title"><?php the_title(); ?></h3>
		
														<div class="entry-excerpt">
															<?php the_excerpt(); ?>
														</div>
		
													</div>
												</a>
											</article>

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


		protected static function render_single_job( $atts, $content ) {
			global $post;

			$index_page_url = '';
			if ( ( $index_page_id = get_option( 'theme_config_index_page_job' ) ) && ( $index_page = get_post( $index_page_id ) ) ) {
				$index_page_url = apply_filters( 'crown_localized_index_url', get_permalink( $index_page_id ), 'theme_config_index_page_job' );
			}

			ob_start();
			?>

				<div class="wp-block-crown-blocks-job-index-single">

					<div class="entry-contents">
	
						<header class="entry-header">

							<?php $centers = get_the_terms( get_the_ID(), 'post_center' ); ?>
							<?php if ( ! empty( $centers ) ) { ?>
								<p class="entry-centers">
									<?php foreach ( $centers as $term ) { ?>
										<span class="center"><?php echo $term->name; ?></span>
									<?php } ?>
								</p>
							<?php } ?>
	
							<h2 class="entry-title"><?php the_title(); ?></h2>
	
						</header>
	
						<div class="entry-content">
							<?php the_content(); ?>
						</div>
	
					</div>
	
					<div class="entry-footer">

						<?php if ( ! empty( $index_page_url ) ) { ?>
							<a href="<?php echo $index_page_url; ?>" class="return-to-index"><?php _e( 'All Jobs', 'crown_blocks' ); ?></a>
						<?php } ?>

					</div>
					
				</div>

			<?php
			return ob_get_clean();
		}


	}
	Crown_Block_Job_Index::init();
}