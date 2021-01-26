<?php

if(!class_exists('Crown_Block_Featured_Client_Story')) {
	class Crown_Block_Featured_Client_Story extends Crown_Block {


		public static $name = 'featured-client-story';


		public static function init() {
			parent::init();

		}


		public static function get_attributes() {
			return array(
				'className' => array( 'type' => 'string', 'default' => '' ),
				'label' => array( 'type' => 'string', 'default' => 'Featured Client' ),
				'backgroundColor' => array( 'type' => 'string', 'default' => '#108DBC' ),
				'borderColor' => array( 'type' => 'string', 'default' => '#E60045' ),
				'textColor' => array( 'type' => 'string', 'default' => 'auto' )
			);
		}


		public static function render( $atts, $content ) {
			global $post;

			$queryArgs = array(
				'post_type' => 'client_story',
				'posts_per_page' => 1,
				'meta_query' => array(
					array( 'key' => '__client_story_options', 'value' => 'featured-post' )
				)
			);
			$query = new WP_Query( $queryArgs );
			if ( ! $query->have_posts() ) return '';
			$query->the_post();

			$block_class = array( 'wp-block-crown-blocks-featured-client-story', $atts['className'] );

			if ( $atts['textColor'] == 'auto' && ! empty( $atts['backgroundColor'] ) ) {
				$block_class[] = 'text-color-' . ( self::is_dark_color( $atts['backgroundColor'] ) ? 'light' : 'dark' );
			} else if ( $atts['textColor'] != 'auto' ) {
				$block_class[] = 'text-color-' . $atts['textColor'];
			}

			ob_start();
			// print_r($atts);
			?>

				<article class="<?php echo implode( ' ', $block_class ); ?>">
					<a href="<?php the_permalink(); ?>">
						<div class="bg" style="background-color: <?php echo $atts['backgroundColor']; ?>;">
							<?php if ( has_post_thumbnail() && ( $image_src = wp_get_attachment_image_url( get_post_thumbnail_id(), 'large' ) ) ) { ?>
								<div class="image" style="background-image: url(<?php echo $image_src; ?>);"></div>
							<?php } ?>
						</div>
						<div class="inner">

							<?php if ( ! empty( $atts['label'] ) ) { ?>
								<h6><?php echo $atts['label']; ?></h6>
							<?php } ?>

							<h3 style="border-color: <?php echo $atts['borderColor']; ?>;"><?php the_title(); ?></h3>

						</div>
					</a>
				</article>

			<?php
			$output = ob_get_clean();
			wp_reset_postdata();

			return $output;
		}


	}
	Crown_Block_Featured_Client_Story::init();
}