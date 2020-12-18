<?php

use Crown\Api\GoogleMaps;
use Crown\Shortcode;


if ( ! class_exists( 'Crown_Site_Settings_Shortcodes' ) ) {
	class Crown_Site_Settings_Shortcodes {

		public static $init = false;

		public static $shortcodes = null;


		public static function init() {
			if( self::$init ) return;
			self::$init = true;

			add_action( 'after_setup_theme', array( __CLASS__, 'register_shortcodes') );

		}


		public static function register_shortcodes() {

			self::$shortcodes['post_category'] = new Shortcode(array(
				'tag' => 'post_category',
				'getOutputCb' => array( __CLASS__, 'get_post_category_shortcode' ),
				'defaultAtts' => array(
					'post_id' => 0
				)
			));

			self::$shortcodes['social_profile_links'] = new Shortcode(array(
				'tag' => 'social_profile_links',
				'getOutputCb' => array( __CLASS__, 'get_social_profile_links_shortcode' ),
				'defaultAtts' => array()
			));

			self::$shortcodes['contact_info'] = new Shortcode(array(
				'tag' => 'contact_info',
				'getOutputCb' => array( __CLASS__, 'get_contact_info_shortcode' ),
				'defaultAtts' => array(
					'context' => ''
				)
			));

			self::$shortcodes['branch_map'] = new Shortcode(array(
				'tag' => 'branch_map',
				'getOutputCb' => array( __CLASS__, 'get_branch_map_shortcode' ),
				'defaultAtts' => array(
					'class' => '',
					'zoom' => 11
				)
			));

		}


		public static function get_post_category_shortcode( $atts, $content ) {
			$post_id = $atts['post_id'];
			if ( empty( $post_id ) ) $post_id = is_singular() ? get_the_ID() : $post_id;
			if ( empty( $post_id ) ) return '';
			$primary_term_id = get_post_meta( $post_id, '_primary_term_category', true );
			if ( ! empty( $primary_term_id ) ) {
				$term = get_term( $primary_term_id, 'category' );
				if ( $term ) return $term->name;
			}
			$terms = get_the_terms( $post_id, 'category' );
			if ( empty( $terms ) ) return '';
			$term_names = array_map( function( $n ) { return $n->name; }, $terms );
			return implode( ', ', $term_names );
		}


		public static function get_social_profile_links_shortcode( $atts, $content ) {
			return apply_filters( 'crown_social_profile_links_shortcode', '', $atts, $content );
		}


		public static function get_contact_info_shortcode( $atts, $content ) {
			$branches = get_repeater_entries( 'blog', 'theme_config_contact_branches' );
			if ( empty( $branches ) ) return '';
			$classes = array( 'contact-info' );
			if ( ! empty( $atts['context'] ) ) $classes[] = $atts['context'];
			ob_start();
			?>
				<div class="<?php echo implode( ' ', $classes ); ?>">
					<?php foreach ( $branches as $branch ) { ?>
						<div class="branch">

							<h6 class="name"><?php echo ! empty( $branch['title'] ) ? $branch['title'] : get_bloginfo( 'name' ); ?></h6>

							<?php if ( $atts['context'] == 'simple' ) { ?>

								<?php if ( ! empty( $branch['address'] ) ) { ?>
									<p class="address"><?php echo nl2br( $branch['address'] ); ?></p>
									<p class="directions"><a href="https://www.google.com/maps/dir//<?php echo esc_attr( preg_replace( '/\n/', ', ', $branch['address'] ) ); ?>/" target="_blank"><?php _e( 'Get Directions', 'crown_site_settings' ); ?></a></p>
								<?php } ?>

								<?php if ( ! empty( $branch['phone'] ) ) { ?>
									<p class="phone"><a href="<?php echo self::get_tel_link( $branch['phone'] ); ?>"><?php echo $branch['phone']; ?></a></p>
								<?php } ?>

							<?php } else { ?>

								<div class="columns">

									<div class="column">

										<?php if ( ! empty( $branch['address'] ) ) { ?>
											<p class="address"><?php echo nl2br( $branch['address'] ); ?></p>
											<p class="directions"><a href="https://www.google.com/maps/dir//<?php echo esc_attr( preg_replace( '/\n/', ', ', $branch['address'] ) ); ?>/" target="_blank"><?php _e( 'Get Directions', 'crown_site_settings' ); ?></a></p>
										<?php } ?>

									</div>

									<div class="column">

										<?php if ( ! empty( $branch['phone'] ) ) { ?>
											<p class="phone"><a href="<?php echo self::get_tel_link( $branch['phone'] ); ?>"><?php echo $branch['phone']; ?></a></p>
										<?php } ?>

										<?php if ( ! empty( $branch['hours'] ) ) { ?>
											<p class="hours"><?php echo nl2br( $branch['hours'] ); ?></p>
										<?php } ?>

									</div>

								</div>

							<?php } ?>

						</div>
					<?php } ?>
				</div>
			<?php
			return ob_get_clean();
		}


		protected static function get_tel_link( $phone_number ) {

			$phone_number = preg_replace( '/[^0-9A-Z]/', '', strtoupper( $phone_number ) );

			$t9_map = array(
				'2' => array( 'A', 'B', 'C' ),
				'3' => array( 'D', 'E', 'F' ),
				'4' => array( 'G', 'H', 'I' ),
				'5' => array( 'J', 'K', 'L' ),
				'6' => array( 'M', 'N', 'O' ),
				'7' => array( 'P', 'Q', 'R', 'S' ),
				'8' => array( 'T', 'U', 'V' ),
				'9' => array( 'W', 'X', 'Y', 'Z' )
			);
			$s = array_map( function( $n ) { return '/[' . implode( '', $n ) . ']/'; }, $t9_map );
			$r = array_keys( $t9_map );
			$phone_number = preg_replace( $s, $r, $phone_number );

			return 'tel:' . $phone_number;
		}


		public static function get_branch_map_shortcode( $atts, $content ) {

			$classes = array_filter( array_map( 'trim', explode( ' ', $atts['class'] ) ), function( $n ) { return ! empty( $n ); } );
			$wrapper_classes = array_merge( array( 'branch-map' ), array_map( function( $n ) { return $n . '-wrapper'; }, $classes ) );

			$map_args = array(
				'points' => array(),
				'class' => implode( ' ', $classes ),
				'autoAddMarkers' => false,
				'options' => array(
					'styles' => apply_filters( 'crown_google_map_styles', null ),
					'scrollwheel' => false,
					'mapTypeControl' => false,
					'streetViewControl' => false,
					'zoom' => $atts['zoom']
				)
			);

			$branches = get_repeater_entries( 'blog', 'theme_config_contact_branches' );
			if ( empty( $branches ) ) return '';

			foreach ( $branches as $branch ) {
				if ( ! isset( $branch['coordinates'] ) || empty( $branch['coordinates']['lat'] ) || empty( $branch['coordinates']['lng'] ) ) continue;
				$map_args['points'][] = $branch['coordinates'];
			}
			if ( empty( $map_args['points'] ) ) return '';

			return '<div class="' . implode( ' ', $wrapper_classes ) . '">' . GoogleMaps::getMap( $map_args ) . '</div>';

		}


	}
}