<?php

use Crown\AdminPage;
use Crown\Form\Field;
use Crown\Form\FieldGroup;
use Crown\Form\FieldGroupSet;
use Crown\Form\FieldRepeaterFlex;
use Crown\Form\Input\CheckboxSet;
use Crown\Form\Input\Media as MediaInput;
use Crown\Form\Input\RadioSet;
use Crown\Form\Input\Select;
use Crown\Form\Input\Date as DateInput;
use Crown\Form\Input\Time as TimeInput;
use Crown\Form\Input\Text as TextInput;
use Crown\Form\Input\Checkbox as CheckboxInput;
use Crown\Form\Input\Color as ColorInput;
use Crown\Form\Input\RichTextarea;
use Crown\Form\Input\Textarea;
use Crown\Form\Input\Gallery as GalleryInput;
use Crown\ListTableColumn;
use Crown\Post\MetaBox;
use Crown\Post\Type as PostType;
use Crown\Post\Taxonomy;
use Crown\UIRule;


if ( ! class_exists( 'Crown_Resources' ) ) {
	class Crown_Resources {

		public static $init = false;

		public static $resource_post_type = null;
		public static $resource_type_taxonomy = null;


		public static function init() {
			if( self::$init ) return;
			self::$init = true;

			$plugin_file = preg_replace( '/\/classes$/', '', dirname( __FILE__ ) ) . '/plugin.php';
			register_activation_hook( $plugin_file, array( __CLASS__, 'activate' ));
			register_deactivation_hook( $plugin_file, array( __CLASS__, 'deactivate' ));

			add_action( 'after_setup_theme', array( __CLASS__, 'register_resource_post_type' ) );
			add_action( 'after_setup_theme', array( __CLASS__, 'register_resource_type_taxonomy' ) );

			// add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'filter_use_block_editor_for_post_type' ), 10, 2 );

			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_admin_styles' ) );

		}


		public static function activate() {
			global $wp_roles;
			
			foreach ( $wp_roles->role_objects as $role ) {
				foreach ( array( 'publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private' ) as $cap ) {
					if ( $role->has_cap( $cap . '_posts' ) ) {
						$role->add_cap( $cap . '_resources' );
					}
				}
				foreach ( array( 'manage', 'edit', 'delete' ) as $cap ) {
					if ( $role->has_cap( 'manage_categories' ) ) {
						$role->add_cap( $cap . '_resource_types' );
					}
				}
				if ( $role->has_cap( 'edit_posts' ) ) {
					$role->add_cap( 'assign_resource_types' );
				}
			}

			flush_rewrite_rules();
		}


		public static function deactivate() {
			global $wp_roles;
			
			foreach ( $wp_roles->role_objects as $role ) {
				foreach ( array( 'publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private' ) as $cap ) {
					$role->remove_cap( $cap . '_resources' );
				}
				foreach ( array( 'manage', 'edit', 'delete', 'assign' ) as $cap ) {
					$role->remove_cap ( $cap . '_resource_types' );
				}
			}
			
			flush_rewrite_rules();
		}


		public static function register_resource_post_type() {

			self::$resource_post_type = new PostType( array(
				'name' => 'resource',
				'singularLabel' => 'Resource',
				'pluralLabel' => 'Resources',
				'settings' => array(
					'supports' => array( 'title', 'editor', 'excerpt', 'revisions', 'thumbnail' ),
					'rewrite' => array( 'slug' => 'resources', 'with_front' => false ),
					'menu_icon' => 'dashicons-text-page',
					'has_archive' => false,
					'publicly_queryable' => true,
					'show_in_rest' => true,
					'show_in_nav_menus' => false,
					'capability_type' => array( 'resource', 'resources' ),
					'map_meta_cap' => true,
					'menu_position' => 32,
					'template' => array(
						array( 'crown-blocks/resource-header', array() ),
						array( 'core/paragraph', array() )
					)
				),
				'metaBoxes' => array(
					
				),
				'listTableColumns' => array(
					
				)
			) );

		}


		public static function register_resource_type_taxonomy() {

			self::$resource_type_taxonomy = new Taxonomy( array(
				'name' => 'resource_type',
				'singularLabel' => 'Resource Type',
				'pluralLabel' => 'Resource Types',
				'postTypes' => array( 'resource' ),
				'settings' => array(
					'hierarchical' => true,
					'rewrite' => array( 'slug' => 'resource-types', 'with_front' => false ),
					'show_in_nav_menus' => false,
					'show_admin_column' => true,
					'publicly_queryable' => true,
					'show_in_rest' => true,
					'labels' => array(
						'menu_name' => 'Types',
						'all_items' => 'All Types'
					),
					'capabilities' => array(
						'manage_terms' => 'manage_resource_types',
						'edit_terms' => 'edit_resource_types',
						'delete_terms' => 'delete_resource_types',
						'assign_terms' => 'assign_resource_types'
					)
				),
				'fields' => array(
					new Field( array(
						'label' => 'Color',
						'input' => new ColorInput( array( 'name' => 'resource_type_color', 'colorpickerOptions' => array( 'palettes' => apply_filters( 'crown_theme_colors', array(), 'resource_type_color' ) ) ) )
					) )
				),
				'listTableColumns' => array(
					new ListTableColumn(array(
						'key' => 'resource-type-color',
						'title' => '',
						'position' => 1,
						'outputCb' => function( $term_id, $args ) {
							$color = get_term_meta( $term_id, 'resource_type_color', true );
							if ( ! empty( $color ) ) {
								$screen = get_current_screen();
								echo '<a class="color" style="background-color: ' . $color . ';" href="' . get_edit_term_link( $term_id, $screen->taxonomy, $screen->post_type ) . '">' . $color . '</a>';
							}
						}
					))
				)
			) );

		}


		public static function filter_use_block_editor_for_post_type( $use_block_editor, $post_type ) {
			return in_array( $post_type, array( 'resource' ) ) ? false : $use_block_editor;
		}


		public static function register_admin_styles( $hook ) {
			
			$screen = get_current_screen();
			if ( $screen->base == 'post' && $screen->post_type == 'resource' ) {
				return;

				ob_start();
				?>
					<style>
						
					</style>
				<?php
				$css = trim( ob_get_clean() );
				$css = trim( preg_replace( array( '/^<style>/', '/<\/style>$/' ), '', $css ) );
				wp_add_inline_style( 'common', $css );

			} else if ( $screen->base == 'edit-tags' && $screen->taxonomy == 'resource_type' ) {

				ob_start();
				?>
					<style>
						.wp-list-table .column-resource-type-color { width: 30px; }
						.wp-list-table .column-resource-type-color .color { display: block; overflow: hidden; text-indent: -100000px; width: 30px; height: 30px; border-radius: 3px; }
					</style>
				<?php
				$css = trim( ob_get_clean() );
				$css = trim( preg_replace( array( '/^<style>/', '/<\/style>$/' ), '', $css ) );
				wp_add_inline_style( 'common', $css );

			}

		}


		public static function get_resource_primary_type( $post_id ) {
			
			$term_ids = wp_get_post_terms( $post_id, 'resource_type', array( 'fields' => 'ids' ) );

			$primary_term_id = get_post_meta( $post_id, '_primary_term_resource_type', true );
			
			if ( ! empty( $primary_term_id ) && in_array( $primary_term_id, $term_ids ) ) array_unshift( $term_ids, $primary_term_id );
			$term_ids = array_values( array_unique( $term_ids ) );

			return ! empty( $term_ids ) ? get_term( $term_ids[0], 'resource_type' ) : null;
		}


		public static function get_resource_primary_type_color( $post_id ) {
			
			$term_ids = wp_get_post_terms( $post_id, 'resource_type', array( 'fields' => 'ids' ) );

			$primary_term_id = get_post_meta( $post_id, '_primary_term_resource_type', true );
			
			if ( ! empty( $primary_term_id ) && in_array( $primary_term_id, $term_ids ) ) array_unshift( $term_ids, $primary_term_id );
			$term_ids = array_values( array_unique( $term_ids ) );

			foreach( $term_ids as $term_id ) {
				$color = get_term_meta( $term_id, 'resource_type_color', true );
				if ( ! empty( $color ) ) return $color;
			}

			return '';
		}


		public static function resource_teaser( $post_id = null ) {
			global $post;

			if ( ! empty( $post_id ) ) {
				$post = get_post( $post_id );
				if ( ! $post || ! in_array( $post->post_type, array( 'resource' ) ) ) return;
				setup_postdata( $post );
			}

			$color = self::get_resource_primary_type_color( get_the_ID() );

			?>
				<article <?php post_class( 'resource-teaser' ); ?>>
					<a href="<?php the_permalink(); ?>">

						<div class="entry-featured-image">
							<div class="inner <?php echo ! empty( $color ) ? ( self::is_dark_color( $color ) ? 'dark' : 'light' ) : 'dark'; ?>" <?php echo ! empty( $color ) ? 'style="background-color: ' . $color . ';"' : ''; ?>>
								<?php $image_src = has_post_thumbnail() ? wp_get_attachment_image_url( get_post_thumbnail_id(), 'medium_large' ) : false; ?>
								<?php if ( ! empty( $image_src ) ) { ?>
									<div class="image" style="background-image: url(<?php echo $image_src; ?>);">
										<?php echo wp_get_attachment_image( get_post_thumbnail_id(), 'medium_large' ) ?>
									</div>
								<?php } ?>
							</div>
						</div>

						<div class="entry-header">
							
							<?php $types = get_the_terms( get_the_ID(), 'resource_type' ); ?>
							<?php if ( ! empty( $types ) ) { ?>
								<p class="entry-type">
									<?php foreach ( $types as $term ) { ?>
										<span class="type"><?php echo $term->name; ?></span>
									<?php } ?>
								</p>
							<?php } ?>

							<h4 class="entry-title"><?php the_title(); ?></h4>

						</div>

						<div class="entry-excerpt">
							<?php if ( function_exists( 'ct_the_excerpt' ) ) { ?>
								<?php ct_the_excerpt( 16 ); ?>
							<?php } else { ?>
								<?php the_excerpt(); ?>
							<?php } ?>
						</div>

						<div class="bottom-border" <?php echo ! empty( $color ) ? 'style="border-color: ' . $color . ';"' : ''; ?>></div>

					</a>
				</article>
			<?php

			if ( ! empty( $post_id ) ) {
				wp_reset_postdata();
			}

		}


		public static function is_dark_color( $hex, $threshold = 0.607843137 ) {
			$luminosity = self::get_color_luminosity( $hex );
			return $luminosity <= $threshold;
		}

		protected static function get_color_luminosity( $hex = '' ) {
			$hex = preg_replace( '/[^0-9a-f]/i', '', $hex );
			if ( $hex == '' || strlen( $hex ) < 3 ) $hex = 'fff';
			if ( strlen( $hex ) < 6 ) $hex = $hex[0] + $hex[0] + $hex[1] + $hex[1] + $hex[2] + $hex[2];
			$c = array();
			for ( $i = 0; $i < 3; $i++ ) $c[] = intval( substr( $hex, $i * 2, 2 ), 16) / 255;
			for ( $i = 0; $i < 3; $i++ ) {
				if ( $c[ $i ] <= 0.03928) {
					$c[ $i ] = $c[ $i ] / 12.92;
				} else {
					$c[ $i ] = pow( ( $c[ $i ] + 0.055 ) / 1.055, 2.4 );
				}
			}
			$luminosity = ( 0.2126 * $c[0] ) + ( 0.7152 * $c[1] ) + ( 0.0722 * $c[2] );
			return $luminosity;
		}


	}
}