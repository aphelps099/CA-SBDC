<?php

if(!class_exists('Crown_Block_Resource_Header')) {
	class Crown_Block_Resource_Header extends Crown_Block {


		public static $name = 'resource-header';


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
			
			$block_class = array( 'wp-block-crown-blocks-resource-header', $atts['className'] );

			$color = '#108DBC';
			$is_dark_color = true;
			if ( class_exists( 'Crown_Resources' ) ) {
				$type_color = Crown_Resources::get_resource_primary_type_color( get_the_ID() );
				if ( ! empty( $type_color ) ) $color = $type_color;
				$is_dark_color = ! empty( $color ) ? Crown_Resources::is_dark_color( $color ) : $is_dark_color;
			}

			$type = null;
			if ( class_exists( 'Crown_Resources' ) ) {
				$type = Crown_Resources::get_resource_primary_type( get_the_ID() );
			}

			$index_page_url = '';
			if ( ( $index_page_id = get_option( 'theme_config_index_page_resource' ) ) ) $index_page_url = get_permalink( $index_page_id );

			ob_start();
			// print_r($atts);
			?>

				<header class="<?php echo implode( ' ', $block_class); ?>">
					<div class="header-bg"></div>
					<div class="inner">
						<div class="header-contents">
							<div class="inner">

								<h2 class="index-title"><?php _e( 'Resource Library', 'crown_blocks' ); ?></h2>

								<?php if ( ! empty( $index_page_url ) ) { ?>
									<div class="entry-breadcrumbs">
										<span class="crumb"><a href="<?php echo $index_page_url; ?>" class="return-to-index"><?php _e( 'All Resources', 'crown_blocks' ); ?></a></span>
										<?php if ( $type ) { ?>
											<span class="crumb"><a href="<?php echo add_query_arg( 'r_type', $type->term_id, $index_page_url ); ?>"><?php echo $type->name; ?></a></span>
										<?php } ?>
									</div>
								<?php } ?>

								<div class="article-header text-color-<?php echo $is_dark_color ? 'light' : 'dark'; ?>" style="background-color: <?php echo $color; ?>;">
									<div class="inner">

										<?php $types = get_the_terms( $post_id, 'resource_type' ); ?>
										<?php if ( ! empty( $types ) ) { ?>
											<p class="entry-type">
												<?php foreach ( $types as $term ) { ?>
													<?php if ( ! empty( $index_page_url ) ) { ?>
														<a class="type" href="<?php echo add_query_arg( 'r_type', $term->term_id, $index_page_url ); ?>"><?php echo $term->name; ?></a>
													<?php } else { ?>
														<span class="type"><?php echo $term->name; ?></span>
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
	Crown_Block_Resource_Header::init();
}