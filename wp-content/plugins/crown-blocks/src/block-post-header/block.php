<?php

if(!class_exists('Crown_Block_Post_Header')) {
	class Crown_Block_Post_Header extends Crown_Block {


		public static $name = 'post-header';


		public static function init() {
			parent::init();
		}


		public static function get_attributes() {
			return array(
				'className' => array( 'type' => 'string', 'default' => '' ),
				'backgroundGradientEnabled' => array( 'type' => 'boolean', 'default' => false ),
				'backgroundGradientAngle' => array( 'type' => 'number', 'default' => 180 ),
				'backgroundColor' => array( 'type' => 'string', 'default' => '#032040' ),
				'backgroundColorSecondary' => array( 'type' => 'string', 'default' => '#032040' ),
				'backgroundImageId' => array( 'type' => 'number', 'default' => 0 ),
				'backgroundImagePreviewSrc' => array( 'type' => 'string', 'default' => '' ),
				'backgroundImageFocalPoint' => array( 'type' => 'object', 'default' => array( 'x' => 0.5, 'y' => 0.5 ) ),
				'backgroundImageOpacity' => array( 'type' => 'number', 'default' => 100 ),
				'backgroundImageGrayscale' => array( 'type' => 'number', 'default' => 0 ),
				'backgroundImageBlendMode' => array( 'type' => 'string', 'default' => 'normal' ),
				'backgroundImageContain' => array( 'type' => 'boolean', 'default' => false ),
				'textColor' => array( 'type' => 'string', 'default' => 'auto' )
			);
		}


		public static function render( $atts, $content ) {
			// ob_start(); var_dump($atts); return ob_get_clean();

			$post_id = get_the_ID();
			if ( empty( $post_id ) ) return '';
			
			$block_class = array( 'wp-block-crown-blocks-post-header', $atts['className'] );

			$atts['backgroundColor'] = ! empty( $atts['backgroundColor'] ) ? $atts['backgroundColor'] : '#032040';
			if ( $atts['textColor'] == 'auto' && ! empty( $atts['backgroundColor'] ) ) {
				$block_class[] = 'text-color-' . ( self::is_dark_color( $atts['backgroundColor'] ) ? 'light' : 'dark' );
			} else if ( $atts['textColor'] != 'auto' ) {
				$block_class[] = 'text-color-' . $atts['textColor'];
			}

			$bg_style = array();
			if ( ! $atts['backgroundGradientEnabled'] ) {
				if ( ! empty( $atts['backgroundColor'] ) ) {
					$bg_style[] = 'background-color: ' . $atts['backgroundColor'] . ';';
				}
			} else {
				if ( ! empty( $atts['backgroundColor'] ) || ! empty( $atts['backgroundColorSecondary'] ) ) {
					$start_color = ! empty( $atts['backgroundColor'] ) ? $atts['backgroundColor'] : 'transparent';
					$end_color = ! empty( $atts['backgroundColorSecondary'] ) ? $atts['backgroundColorSecondary'] : 'transparent';
					$degrees = floatval( $atts['backgroundGradientAngle'] );
					$bg_style[] = 'background: linear-gradient(' . $degrees . 'deg, ' . $start_color . ', ' . $end_color . ');';
				}
			}

			$background_image_url = null;
			if ( $atts['backgroundImageId'] ) {
				$background_image_url = wp_get_attachment_image_url( $atts['backgroundImageId'], 'fullscreen' );
				$block_class[] = 'has-bg-image';
			}

			ob_start();
			?>

				<header class="<?php echo implode( ' ', $block_class); ?>">
					<div class="header-bg" style="<?php echo implode( ' ', $bg_style ); ?>">
						<?php if ( ! empty( $background_image_url ) ) { ?>
							<?php $bg_image_style = array(
								'background-image: url(' . $background_image_url . ');',
								'background-position: ' . ( floatval( $atts['backgroundImageFocalPoint']['x'] ) * 100 ) . '% ' . ( floatval( $atts['backgroundImageFocalPoint']['y'] ) * 100 ) . '%;',
								'opacity: ' . ( $atts['backgroundImageOpacity'] / 100 ) . ';',
								'filter: grayscale(' . ( $atts['backgroundImageGrayscale'] / 100 ) . ');',
								'mix-blend-mode: ' . $atts['backgroundImageBlendMode'] . ';',
								'background-size: ' . ( $atts['backgroundImageContain'] ? 'contain' : 'cover' ) . ';',
							); ?>
							<div class="bg-image" style="<?php echo implode( ' ', $bg_image_style ); ?>"></div>
						<?php } ?>
					</div>
					<div class="inner">
						<div class="header-contents">
							<div class="inner">

								<?php $categories = get_the_terms( $post_id, 'category' ); ?>
								<?php if ( ! empty( $categories ) ) { ?>
									<p class="entry-categories">
										<?php foreach ( $categories as $term ) { ?>
											<a class="category" href="<?php echo get_home_url(); ?>/news/?p_category=<?php echo $term->term_id; ?>"><?php echo $term->name; ?></a>
										<?php } ?>
									</p>
								<?php } ?>
								
								<h1 class="entry-title"><?php echo get_the_title( $post_id ); ?></h1>

								<div class="header-meta">

									<?php $relative_time = self::get_relative_time( get_the_time( 'Y-m-d H:i:s', $post_id ) ); ?>
									<?php if ( $relative_time && ! in_array( $relative_time->units, array( 'years', 'months' ) ) ) { ?>
										<p class="entry-date"><?php echo abs( $relative_time->value ) . ' ' . $relative_time->units . ( $relative_time->value <= 0 ? ' ago' : '' ); ?></p>
									<?php } else { ?>
										<p class="entry-date"><?php echo get_the_time( 'j F, Y', $post_id ); ?></p>
									<?php } ?>

									<?php if ( function_exists( 'ct_social_sharing_links' ) ) ct_social_sharing_links(); ?>

									<button type="button" class="btn btn-outline-dark-blue subscribe-button" data-toggle="modal" data-target="#subscribe-modal"><?php _e( 'Subscribe', 'crown_blocks' ); ?></a>

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


		public static function get_relative_time( $timestamp, $current_timestamp = null ) {

			$date = strtotime( $timestamp ) !== false ? new DateTime( $timestamp ) : false;
			if ( ! $date ) return false;

			$current_date = $current_timestamp !== null ? ( strtotime( $current_timestamp ) !== false ? new DateTime( $current_timestamp ) : false ) : new DateTime();
			if ( ! $current_date ) return false;

			$diff = $current_date->diff( $date );

			$time = (object) array(
				'value' => 0,
				'units' => '',
				'units_contextual' => ''
			);

			if ( intval( $diff->format( '%y' ) ) > 0 ) {
				$time->value = intval( $diff->format( '%r%y' ) );
				$time->units = 'years';
				$time->units_contextual = abs( $time->value ) == 1 ? 'year' : 'years';
			} else if ( intval( $diff->format( '%m' ) ) > 0 ) {
				$time->value = intval( $diff->format( '%r%m' ) );
				$time->units = 'months';
				$time->units_contextual = abs( $time->value ) == 1 ? 'month' : 'months';
			} else if ( intval( $diff->format( '%d' ) ) > 0 ) {
				$time->value = intval( $diff->format( '%r%d' ) );
				$time->units = 'days';
				$time->units_contextual = abs( $time->value ) == 1 ? 'day' : 'days';
			} else {
				$time->value = intval( $diff->format( '%r%i' ) );
				$time->units = 'minutes';
				$time->units_contextual = abs( $time->value ) == 1 ? 'minute' : 'minutes';
			}

			return $time;

		}


	}
	Crown_Block_Post_Header::init();
}