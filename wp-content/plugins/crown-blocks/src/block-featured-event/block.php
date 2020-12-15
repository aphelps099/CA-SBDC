<?php

if(!class_exists('Crown_Block_Featured_Event')) {
	class Crown_Block_Featured_Event extends Crown_Block {


		public static $name = 'featured-event';


		public static function init() {
			parent::init();

		}


		public static function get_attributes() {
			return array(
				'className' => array( 'type' => 'string', 'default' => '' ),
				'label' => array( 'type' => 'string', 'default' => 'Featured Event' ),
				'backgroundColor' => array( 'type' => 'string', 'default' => '#D44457' ),
				'backgroundColorSecondary' => array( 'type' => 'string', 'default' => '#108DBC' ),
				'textColor' => array( 'type' => 'string', 'default' => 'auto' )
			);
		}


		public static function render( $atts, $content ) {
			global $post;

			if ( ! class_exists( 'Crown_Events' ) ) return '';
			$upcoming_events = Crown_Events::get_upcoming_events( 1, array(), true );
			if ( empty( $upcoming_events ) ) return '';
			$post = $upcoming_events[0];
			setup_postdata( $post );

			$block_class = array( 'wp-block-crown-blocks-featured-event', $atts['className'] );

			if ( $atts['textColor'] == 'auto' && ! empty( $atts['backgroundColor'] ) ) {
				$block_class[] = 'text-color-' . ( self::isDarkColor( $atts['backgroundColor'] ) ? 'light' : 'dark' );
			} else if ( $atts['textColor'] != 'auto' ) {
				$block_class[] = 'text-color-' . $atts['textColor'];
			}

			ob_start();
			// print_r($atts);
			?>

				<article <?php post_class( $block_class ); ?>>
					<a href="<?php the_permalink(); ?>">
						<div class="bg" style="background: linear-gradient(45deg, <?php echo $atts['backgroundColor']; ?>, <?php echo $atts['backgroundColorSecondary']; ?>);">
							<?php if ( has_post_thumbnail() && ( $image_src = wp_get_attachment_image_url( get_post_thumbnail_id(), 'large' ) ) ) { ?>
								<div class="image" style="background-image: url(<?php echo $image_src; ?>);"></div>
							<?php } ?>
						</div>
						<div class="inner">

							<?php if ( ! empty( $atts['label'] ) ) { ?>
								<h6 class="entry-label"><?php echo $atts['label']; ?></h6>
							<?php } ?>

							<?php $event_start_timestamp = strtotime( get_post_meta( get_the_ID(), 'event_start_timestamp', true ) ); ?>
							<?php $event_end_timestamp = strtotime( get_post_meta( get_the_ID(), 'event_end_timestamp', true ) ); ?>
							<?php if ( $event_start_timestamp !== false ) { ?>
								<div class="entry-event-date">
									<div class="inner">
										<span class="month"><?php echo date( 'M', $event_start_timestamp ); ?></span>
										<span class="date"><?php echo date( 'j', $event_start_timestamp ); ?></span>
										<span class="time"><?php echo date( 'g:i a', $event_start_timestamp ); ?><?php if ( $event_end_timestamp !== false) { ?> &mdash; <?php echo date( 'g:i a', $event_end_timestamp ); ?></span><?php } ?>
									</div>
								</div>
							<?php } ?>

							<div class="entry-contents">
								<h3 class="entry-title"><?php the_title(); ?></h3>
								<div class="entry-excerpt"><?php the_excerpt(); ?></div>
							</div>

						</div>
					</a>
				</article>

			<?php
			$output = ob_get_clean();
			wp_reset_postdata();

			return $output;
		}


	}
	Crown_Block_Featured_Event::init();
}