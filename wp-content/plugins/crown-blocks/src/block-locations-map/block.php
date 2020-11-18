<?php

if(!class_exists('Crown_Block_Locations_Map')) {
	class Crown_Block_Locations_Map extends Crown_Block {


		public static $name = 'locations-map';


		public static function init() {
			parent::init();
		}


		public static function get_attributes() {
			return array(
				'className' => array( 'type' => 'string', 'default' => '' )
			);
		}


		public static function render( $atts, $content ) {
			global $post;

			$svg = file_exists( dirname( __FILE__ ) . '/map.svg' ) ? file_get_contents( dirname( __FILE__ ) . '/map.svg' ) : '';

			$queryArgs = array(
				'post_type' => 'location',
				'posts_per_page' => -1,
				'orderby' => 'menu_order',
				'order' => 'ASC',
				'meta_query' => array(
					array( 'key' => 'location_map_pin', 'compare' => '!=', 'value' => '' )
				)
			);
			$query = new WP_Query( $queryArgs );

			$block_class = array( 'wp-block-crown-blocks-locations-map', $atts['className'] );

			ob_start();
			// print_r($atts);
			?>

				<div class="<?php echo implode( ' ', $block_class ); ?>">
					<div class="inner">

						<div class="map-wrapper">
							<div class="map">
								<?php echo $svg; ?>
							</div>
						</div>

						<div class="locations-wrapper">
							<div class="locations">
								<?php while ( $query->have_posts() ) { ?>
									<?php $query->the_post(); ?>
									<?php
										$post_classes = array();
										$map_pin = get_post_meta( get_the_ID(), 'location_map_pin', true );
										if ( ! empty( $map_pin ) ) $post_classes[] = 'map-' . $map_pin;
									?>
									<article <?php post_class( $post_classes ); ?> <?php echo ! empty( $map_pin ) ? 'data-map-pin="' . $map_pin . '"' : ''; ?>>
									
										<?php $image_src = wp_get_attachment_image_url( get_post_thumbnail_id(), 'medium_large' ); ?>
										<?php if ( ! empty( $image_src ) ) { ?>
											<div class="entry-photo">
												<div class="image" style="background-image: url(<?php echo $image_src; ?>);">
													<img src="<?php echo $image_src; ?>">
												</div>
											</div>
										<?php } ?>
	
										<div class="entry-contents">

											<h3 class="entry-title"><?php the_title(); ?></h3>

											<?php $address = get_post_meta( get_the_ID(), 'location_address', true ); ?>
											<?php if ( ! empty( $address ) ) { ?>
												<p class="entry-address"><?php echo nl2br( $address ); ?></p>
											<?php } ?>

											<?php $phone = get_post_meta( get_the_ID(), 'location_phone', true ); ?>
											<?php if ( ! empty( $phone ) ) { ?>
												<p class="entry-phone"><a href="tel:<?php echo preg_replace( '/[^\d]/', '', $phone ); ?>"><?php echo $phone; ?></a></p>
											<?php } ?>

											<?php $email = get_post_meta( get_the_ID(), 'location_email', true ); ?>
											<?php if ( ! empty( $email ) ) { ?>
												<p class="entry-email"><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo $email; ?></a></p>
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
	Crown_Block_Locations_Map::init();
}