<?php

if(!class_exists('Crown_Block_Client_Story_Header')) {
	class Crown_Block_Client_Story_Header extends Crown_Block {


		public static $name = 'client-story-header';


		public static function init() {
			parent::init();
		}


		public static function get_attributes() {
			return array(
				'className' => array( 'type' => 'string', 'default' => '' ),
				'introContent' => array( 'type' => 'string', 'default' => '' ),
				'featuredImageId' => array( 'type' => 'string', 'default' => '' ),
				'featuredImageFocalPoint' => array( 'type' => 'object', 'default' => array( 'x' => 0.5, 'y' => 0.5 ) )
			);
		}


		public static function render( $atts, $content ) {

			$post_id = get_the_ID();
			if ( empty( $post_id ) ) return '';
			
			$block_class = array( 'wp-block-crown-blocks-client-story-header', 'text-color-light', $atts['className'] );

			$featured_image_id = get_post_thumbnail_id( $post_id );
			$featured_image_url = '';
			if ( ! empty( $atts['featuredImageId'] ) ) $featured_image_id = $atts['featuredImageId'];
			$featured_image_url = ! empty( $featured_image_id ) ? wp_get_attachment_image_url( $featured_image_id, 'extra_large' ) : $featured_image_url;
			if ( ! empty( $featured_image_url ) ) $block_class[] = 'has-featured-image';

			$index_page_url = '';
			if ( ( $index_page_id = get_option( 'theme_config_index_page_client_story' ) ) ) $index_page_url = get_permalink( $index_page_id );

			ob_start();
			// print_r($atts);
			?>

				<header class="<?php echo implode( ' ', $block_class); ?>">
					<div class="header-bg"></div>
					<div class="inner">
						<div class="header-contents">
							<div class="inner">

								<div class="article-header">
									<div class="inner">

										<div class="push">

											<?php $centers = get_the_terms( $post_id, 'post_center' ); ?>
											<?php if ( ! empty( $centers ) ) { ?>
												<p class="entry-centers">
													<?php foreach ( $centers as $term ) { ?>
														<?php if ( ! empty( $index_page_url ) ) { ?>
															<a class="center" href="<?php echo add_query_arg( 'cs_center', $term->term_id, $index_page_url ); ?>"><?php echo $term->name; ?></a>
														<?php } else { ?>
															<span class="center"><?php echo $term->name; ?></span>
														<?php } ?>
													<?php } ?>
												</p>
											<?php } ?>
	
											<h1 class="entry-title"><?php echo get_the_title( $post_id ); ?></h1>

											<?php if ( ! empty( $atts['introContent'] ) ) { ?>
												<div class="entry-intro"><?php echo apply_filters( 'the_content', $atts['introContent'] ); ?></div>
											<?php } ?>

										</div>

										<?php if ( function_exists( 'ct_social_sharing_links' ) ) ct_social_sharing_links(); ?>

									</div>
								</div>

								<?php if ( ! empty( $featured_image_url ) ) { ?>
									<div class="entry-featured-image">
										<div class="image" style="background-image: url(<?php echo $featured_image_url; ?>); background-position: <?php echo floatval( $atts['featuredImageFocalPoint']['x'] ) * 100; ?>% <?php echo floatval( $atts['featuredImageFocalPoint']['y'] ) * 100; ?>%;">
											<?php echo wp_get_attachment_image( $featured_image_id, 'extra_large' ) ?>
										</div>
									</div>
								<?php } ?>

							</div>
						</div>
					</div>
				</header>

			<?php
			$output = ob_get_clean();

			return $output;
		}


	}
	Crown_Block_Client_Story_Header::init();
}