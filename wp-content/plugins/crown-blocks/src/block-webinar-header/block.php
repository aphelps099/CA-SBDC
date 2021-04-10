<?php

if(!class_exists('Crown_Block_Webinar_Header')) {
	class Crown_Block_Webinar_Header extends Crown_Block {


		public static $name = 'webinar-header';


		public static function init() {
			parent::init();
		}


		public static function get_attributes() {
			return array(
				'className' => array( 'type' => 'string', 'default' => '' )
			);
		}


		public static function render( $atts, $content ) {

			$post_id = get_the_ID();
			if ( empty( $post_id ) ) return '';
			
			$block_class = array( 'wp-block-crown-blocks-webinar-header', $atts['className'] );

			$color = '#0381C3';
			$is_dark_color = true;
			if ( class_exists( 'Crown_Webinars' ) ) {
				$topic_color = Crown_Webinars::get_webinar_primary_topic_color( get_the_ID() );
				if ( ! empty( $topic_color ) ) $color = $topic_color;
				$is_dark_color = ! empty( $color ) ? Crown_Webinars::is_dark_color( $color ) : $is_dark_color;
			}

			$topic = null;
			if ( class_exists( 'Crown_Webinars' ) ) {
				$topic = Crown_Webinars::get_webinar_primary_topic( get_the_ID() );
			}

			$bg_color = '#eee';
			$index_page_url = '';
			if ( ( $index_page_id = get_option( 'theme_config_index_page_webinar' ) ) && ( $index_page = get_post( $index_page_id ) ) ) {
				$index_page_url = apply_filters( 'crown_localized_index_url', get_permalink( $index_page_id ), 'theme_config_index_page_webinar' );
				$index_page_blocks = apply_filters( 'crown_localized_index_blocks', parse_blocks( $index_page->post_content ), 'theme_config_index_page_webinar' );
				if ( ! empty( $index_page_blocks ) ) {
					if ( $index_page_blocks[0]['blockName'] == 'crown-blocks/container' && isset( $index_page_blocks[0]['attrs']['backgroundColor'] ) && ! empty( $index_page_blocks[0]['attrs']['backgroundColor'] ) ) {
						$bg_color = $index_page_blocks[0]['attrs']['backgroundColor'];
					}
				}
			}

			if ( class_exists( 'Crown_Webinars' ) ) {
				$block_class[] = ! empty( $bg_color ) && Crown_Webinars::is_dark_color( $bg_color ) ? 'text-color-light' : 'text-color-dark';
			}

			ob_start();
			// print_r($atts);
			?>

				<header class="<?php echo implode( ' ', $block_class); ?>">
					<div class="header-bg" style="background-color: <?php echo $bg_color; ?>;"></div>
					<div class="inner">
						<div class="header-contents">
							<div class="inner">

								<h2 class="index-title"><?php _e( 'Webinar Library', 'crown_blocks' ); ?></h2>

								<?php if ( ! empty( $index_page_url ) ) { ?>
									<div class="entry-breadcrumbs">
										<span class="crumb"><a href="<?php echo $index_page_url; ?>" class="return-to-index"><?php _e( 'All Webinars', 'crown_blocks' ); ?></a></span>
										<?php if ( $topic ) { ?>
											<span class="crumb"><a href="<?php echo add_query_arg( 'w_topic', $topic->term_id, $index_page_url ); ?>"><?php echo $topic->name; ?></a></span>
										<?php } ?>
									</div>
								<?php } ?>

								<div class="article-header text-color-<?php echo $is_dark_color ? 'light' : 'dark'; ?>" style="background-color: <?php echo $color; ?>;">
									<div class="inner">

										<?php $topics = get_the_terms( $post_id, 'post_topic' ); ?>
										<?php if ( ! empty( $topics ) ) { ?>
											<p class="entry-topic">
												<?php foreach ( $topics as $term ) { ?>
													<?php if ( ! empty( $index_page_url ) ) { ?>
														<a class="topic" href="<?php echo add_query_arg( 'w_topic', $term->term_id, $index_page_url ); ?>"><?php echo $term->name; ?></a>
													<?php } else { ?>
														<span class="topic"><?php echo $term->name; ?></span>
													<?php } ?>
												<?php } ?>
											</p>
										<?php } ?>

										<h1 class="entry-title"><?php echo get_the_title( $post_id ); ?></h1>

										<?php if ( function_exists( 'ct_social_sharing_links' ) ) ct_social_sharing_links(); ?>

									</div>
								</div>

							</div>
						</div>
					</div>
					<?php if ( function_exists( 'ct_social_sharing_links' ) ) { ?>
						<div class="sticky-share-links">
							<?php ct_social_sharing_links(); ?>
						</div>
					<?php } ?>
				</header>

			<?php
			$output = ob_get_clean();

			return $output;
		}


	}
	Crown_Block_Webinar_Header::init();
}