<?php

if(!class_exists('Crown_Block_Event_Header')) {
	class Crown_Block_Event_Header extends Crown_Block {


		public static $name = 'event-header';


		public static function init() {
			parent::init();
		}


		public static function get_attributes() {
			return array(
				'className' => array( 'type' => 'string', 'default' => '' ),
				'backgroundImageId' => array( 'type' => 'string', 'default' => '' ),
				'backgroundImageFocalPoint' => array( 'type' => 'object', 'default' => array( 'x' => 0.5, 'y' => 0.5 ) )
			);
		}


		public static function render( $atts, $content ) {

			$post_id = get_the_ID();
			if ( empty( $post_id ) ) return '';
			
			$block_class = array( 'wp-block-crown-blocks-event-header', $atts['className'] );

			$background_image_url = null;
			if ( $atts['backgroundImageId'] ) {
				$background_image_url = wp_get_attachment_image_url( $atts['backgroundImageId'], 'fullscreen' );
				$block_class[] = 'has-bg-image';
			}

			$index_page_url = '';
			if ( ( $index_page_id = get_option( 'theme_config_index_page_event' ) ) ) $index_page_url = get_permalink( $index_page_id );

			ob_start();
			// print_r($atts);
			?>

				<header class="<?php echo implode( ' ', $block_class); ?>">
					<div class="header-bg">
						<?php if ( ! empty( $background_image_url ) ) { ?>
							<div class="bg-image" style="background-image: url(<?php echo $background_image_url; ?>); background-position: <?php echo floatval( $atts['backgroundImageFocalPoint']['x'] ) * 100; ?>% <?php echo floatval( $atts['backgroundImageFocalPoint']['y'] ) * 100; ?>%;"></div>
						<?php } ?>
					</div>
					<div class="inner">
						<div class="header-contents">
							<div class="inner">

								<h2 class="index-title"><span><?php _e( 'SBDC', 'crown_blocks' ); ?></span><span><?php _e( 'Events', 'crown_blocks' ); ?></span></h2>

								<div class="article-header wp-block-crown-blocks-container bg-flush-right">
									<div class="container-bg"></div>
									<div class="inner">
										<div class="container-contents">
											<div class="inner">
												
												<?php $centers = get_the_terms( $post_id, 'post_center' ); ?>
												<?php if ( ! empty( $centers ) ) { ?>
													<p class="entry-centers">
														<?php foreach ( $centers as $term ) { ?>
															<?php if ( ! empty( $index_page_url ) ) { ?>
																<a class="center" href="<?php echo add_query_arg( 'e_center', $term->term_id, $index_page_url ); ?>"><?php echo $term->name; ?></a>
															<?php } else { ?>
																<span class="center"><?php echo $term->name; ?></span>
															<?php } ?>
														<?php } ?>
													</p>
												<?php } ?>

												<?php if ( function_exists( 'ct_event_date' ) ) ct_event_date( $post_id ); ?>

												<h1 class="entry-title"><?php echo get_the_title( $post_id ); ?></h1>
												
												<div class="header-meta">
													<div class="inner">

														<?php $topics = get_the_terms( $post_id, 'post_topic' ); ?>
														<?php if ( ! empty( $topics ) ) { ?>
															<p class="entry-topics">
																<?php foreach ( $topics as $term ) { ?>
																	<?php if ( ! empty( $index_page_url ) ) { ?>
																		<a class="topic" href="<?php echo add_query_arg( 'e_topic', $term->term_id, $index_page_url ); ?>"><?php echo $term->name; ?></a>
																	<?php } else { ?>
																		<span class="topic"><?php echo $term->name; ?></span>
																	<?php } ?>
																<?php } ?>
															</p>
														<?php } ?>
	
														<?php if ( function_exists( 'ct_social_sharing_links' ) ) ct_social_sharing_links(); ?>

													</div>
												</div>

											</div>
										</div>
									</div>
								</div>
								
							</div>
						</div>
					</div>
				</header>

			<?php
			$output = ob_get_clean();

			return $output;
		}


	}
	Crown_Block_Event_Header::init();
}