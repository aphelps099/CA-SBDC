<?php

if(!class_exists('Crown_Block_Featured_Case_Study_Logos')) {
	class Crown_Block_Featured_Case_Study_Logos extends Crown_Block {


		public static $name = 'featured-case-study-logos';

		protected static $output_post_ids = array();


		public static function init() {
			parent::init();

		}


		public static function get_attributes() {
			return array(
				'className' => array( 'type' => 'string', 'default' => '' ),
				'maxPostCount' => array( 'type' => 'number', 'default' => 12 ),
				'manuallySelectPosts' => array( 'type' => 'boolean', 'default' => false ),
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
				'meta_query' => array(
					array( 'key' => 'case_study_client_logo', 'compare' => '!=', 'value' => '' )
				),
				'post__not_in' => array(),
				'post__in' => array()
			);

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

			$block_class = array( 'wp-block-crown-blocks-featured-case-study-logos', $atts['className'] );

			ob_start();
			// print_r($atts);
			?>

				<div class="<?php echo implode( ' ', $block_class ); ?>">
					<div class="inner">

						<div class="post-feed item-count-<?php echo $query->post_count; ?>" data-item-count="<?php echo $query->post_count; ?>">
							<div class="inner">

								<?php while ( $query->have_posts() ) { ?>
									<?php $query->the_post(); ?>
									<?php $post_classes = array(); ?>
									<?php $logo_src = wp_get_attachment_image_url( get_post_meta( get_the_ID(), 'case_study_client_logo', true ) , 'medium'); ?>
									<?php $logo_active_src = wp_get_attachment_image_url( get_post_meta( get_the_ID(), 'case_study_client_logo_active', true ), 'medium' ); ?>
									<?php if ( empty( $logo_src ) ) continue; ?>
									<article <?php post_class( $post_classes ); ?>>

										<a href="#case-study-modal-<?php echo get_the_ID(); ?>">
											<div class="entry-logo-container">
	
												<div class="entry-logo">
													<div class="image" <?php echo 'style="background-image: url(' . $logo_src . ');"'; ?>>
														<?php echo wp_get_attachment_image( get_post_meta( get_the_ID(), 'case_study_client_logo', true ), 'medium' ); ?>
													</div>
												</div>
	
												<?php if ( ! empty( $logo_active_src ) ) { ?>
													<div class="entry-logo active">
														<div class="image" <?php echo 'style="background-image: url(' . $logo_active_src . ');"'; ?>>
															<?php echo wp_get_attachment_image( get_post_meta( get_the_ID(), 'case_study_client_logo_active', true ), 'medium' ); ?>
														</div>
													</div>
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

			return $output;
		}


	}
	Crown_Block_Featured_Case_Study_Logos::init();
}