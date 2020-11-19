<?php

use Crown\AdminPage;
use Crown\Api\GoogleMaps;
use Crown\Form\Field;
use Crown\Form\FieldGroup;
use Crown\Form\FieldGroupSet;
use Crown\Form\FieldRepeater;
use Crown\Form\FieldRepeaterFlex;
use Crown\Form\Input\CheckboxSet;
use Crown\Form\Input\Media as MediaInput;
use Crown\Form\Input\RadioSet;
use Crown\Form\Input\Select;
use Crown\Form\Input\GeoCoordinates as GeoCoordinatesInput;
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


if ( ! class_exists( 'Crown_Centers' ) ) {
	class Crown_Centers {

		public static $init = false;

		public static $center_post_type = null;
		public static $post_center_taxonomy = null;


		public static function init() {
			if( self::$init ) return;
			self::$init = true;

			GoogleMaps::init( array( 'apiKey' => defined( 'CROWN_GOOGLE_API_KEY' ) ? CROWN_GOOGLE_API_KEY : '' ) );

			$plugin_file = preg_replace( '/\/classes$/', '', dirname( __FILE__ ) ) . '/plugin.php';
			register_activation_hook( $plugin_file, array( __CLASS__, 'activate' ));
			register_deactivation_hook( $plugin_file, array( __CLASS__, 'deactivate' ));

			add_action( 'after_setup_theme', array( __CLASS__, 'register_center_post_type' ) );
			add_action( 'after_setup_theme', array( __CLASS__, 'register_post_center_taxonomy' ) );

			add_action( 'save_post', array( __CLASS__, 'update_post_center_term' ) );
			add_action( 'after_delete_post', array( __CLASS__, 'delete_post_center_term' ) );

			// add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'filter_use_block_editor_for_post_type' ), 10, 2 );

			// add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_admin_styles' ) );

		}


		public static function activate() {
			global $wp_roles;
			
			foreach ( $wp_roles->role_objects as $role ) {
				foreach ( array( 'publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private' ) as $cap ) {
					if ( $role->has_cap( $cap . '_posts' ) ) {
						$role->add_cap( $cap . '_centers' );
					}
				}
				// foreach ( array( 'manage', 'edit', 'delete' ) as $cap ) {
				// 	if ( $role->has_cap( 'manage_categories' ) ) {
				// 		$role->add_cap( $cap . '_post_centers' );
				// 	}
				// }
				if ( $role->has_cap( 'edit_posts' ) ) {
					$role->add_cap( 'assign_post_centers' );
				}
			}

			flush_rewrite_rules();
		}


		public static function deactivate() {
			global $wp_roles;
			
			foreach ( $wp_roles->role_objects as $role ) {
				foreach ( array( 'publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private' ) as $cap ) {
					$role->remove_cap( $cap . '_centers' );
				}
				foreach ( array( 'manage', 'edit', 'delete', 'assign' ) as $cap ) {
					$role->remove_cap ( $cap . '_post_centers' );
				}
			}
			
			flush_rewrite_rules();
		}


		public static function register_center_post_type() {

			self::$center_post_type = new PostType( array(
				'name' => 'center',
				'singularLabel' => 'SBDC',
				'pluralLabel' => 'SBDCs',
				'settings' => array(
					'supports' => array( 'title', 'revisions' ),
					'rewrite' => array( 'slug' => 'centers', 'with_front' => false ),
					'menu_icon' => 'dashicons-location',
					'has_archive' => false,
					'publicly_queryable' => true,
					'show_in_rest' => true,
					'show_in_nav_menus' => false,
					'capability_type' => array( 'center', 'centers' ),
					'map_meta_cap' => true,
					'menu_position' => 40
				),
				'metaBoxes' => array(
					new MetaBox( array(
						'id' => 'center-general-info',
						'title' => 'General Info',
						'priority' => 'high',
						'fields' => array(
							new Field(array(
								'label' => 'Website',
								'input' => new TextInput( array( 'name' => 'center_website', 'placeholder' => 'https://' ) )
							) ),
							new Field(array(
								'label' => 'Phone Number',
								'input' => new TextInput( array( 'name' => 'center_phone', 'class' => 'input-medium' ) )
							) ),
							new Field(array(
								'label' => 'Email Address',
								'input' => new TextInput( array( 'name' => 'center_email', 'class' => 'input-medium' ) )
							) )
						)
					) ),
					new MetaBox( array(
						'id' => 'center-office-locations',
						'title' => 'Office Locations',
						'priority' => 'high',
						'fields' => array(
							new FieldRepeater( array(
								'name' => 'center_office_locations',
								'addNewLabel' => 'Add Office Location',
								'fields' => array(
									new FieldGroup( array(
										'class' => 'no-border two-column',
										'fields' => array(
											new FieldGroup( array(
												'class' => 'no-border',
												'fields' => array(
													new Field(array(
														'input' => new MediaInput( array( 'name' => 'featured_image', 'buttonLabel' => 'Select Featured Image', 'mimeType' => 'image' ) )
													) ),
													new Field( array(
														'label' => 'Local Phone Number',
														'input' => new TextInput( array( 'name' => 'phone') )
													) ),
													new Field( array(
														'label' => 'Address',
														'input' => new Textarea( array( 'name' => 'address', 'rows' => 6) )
													) ),
													new Field( array(
														'getOutputCb' => array( __CLASS__, 'get_address_components_field_output' )
													) )
												)
											) ),
											new FieldGroup( array(
												'class' => 'no-border',
												'fields' => array(
													new Field( array(
														'input' => new GeoCoordinatesInput( array( 'name' => 'coordinates', 'readonly' => true, 'showGoogleMap' => true ) ),
														'saveMetaCb' => array( __CLASS__, 'save_coordinates_field_meta' )
													) )
												)
											) )
										)
									) )
								)
							) )
							
						)
					) )
				),
				'listTableColumns' => array(
					new ListTableColumn( array(
						'key' => 'center-website',
						'title' => 'Website',
						'position' => 2,
						'outputCb' => function( $post_id, $args ) {
							$website = get_post_meta( $post_id, 'center_website', true );
							if ( ! empty( $website ) ) {
								$clean_website = preg_replace( array( '/^https?:\/\/(?:www\.)?/', '/\.(com|net|org)\/$/' ), array( "", ".$1" ), $website );
								echo '<a href="' . $website . '" target="_blank">' . $clean_website . '</a>';
							}
						}
					) ),
					new ListTableColumn( array(
						'key' => 'center-location',
						'title' => 'Location',
						'position' => 3,
						'outputCb' => function( $post_id, $args ) {
							$output = array();
							$locations = get_repeater_entries( 'post', 'center_office_locations', $post_id );
							foreach ( $locations as $location ) {
								$line = array();
								if ( isset( $location['_ac_locality_l'] ) && ! empty( $location['_ac_locality_l'] ) ) $line[] = $location['_ac_locality_l'];
								if ( isset( $location['_ac_administrative_area_level_1_s'] ) && ! empty( $location['_ac_administrative_area_level_1_s'] ) ) $line[] = $location['_ac_administrative_area_level_1_s'];
								if ( ! empty( $line ) ) $output[] = implode( ', ', $line );
							}
							echo implode( '<br>', $output );
						}
					) )
				)
			) );

		}


		public static function get_address_components_field_output( $field, $args ) {
			$object_id = $args['entryId'];

			$address_component_meta_keys = array(
				'street_number' => 'Street Number',
				'route' => 'Route',
				'locality' => 'City',
				'administrative_area_level_2' => 'County',
				'administrative_area_level_1' => 'State',
				'country' => 'Country',
				'postal_code' => 'Postal Code',
				'postal_code_suffix' => 'Postal Code Suffix',
			);

			$output = '';
			$output .= '<div class="crown-field address-components"><div class="input-wrap"><ul style="margin: 0; font-size: 12px; line-height: 1.2; color: #888;">';
			foreach ( $address_component_meta_keys as $key => $label ) {
				$output .= '<li style="margin: 0;"><strong class="label" style="color: #444;">' . $label . ':</strong> <span class="value">' . get_post_meta( $object_id, '_ac_' . $key . '_l', true ) . '</span></li>';
			}
			$output .= '</ul></div></div>';

			return $output;
		}


		public static function save_coordinates_field_meta($field, $input, $type, $objectId, $value) {

			$address = isset( $input['address'] ) ? $input['address'] : '';
			$address = preg_replace( '/\r\n/', ', ', $address );
			if ( empty( $address ) ) return;

			$geocode_data = GoogleMaps::getGeocodeData( $address );

			$address_component_meta_keys = array(
				'street_number' => 'Street Number',
				'route' => 'Route',
				'locality' => 'City',
				'administrative_area_level_2' => 'County',
				'administrative_area_level_1' => 'State',
				'country' => 'Country',
				'postal_code' => 'Postal Code',
				'postal_code_suffix' => 'Postal Code Suffix',
			);
			$address_component_meta = array();
			foreach ( $address_component_meta_keys as $key => $label ) {
				$address_component_meta[ $key ] = (object) array( 'long' => '', 'short' => '' );
			}

			if ( $geocode_data ) {

				if ( ! empty( $geocode_data->geometry->location ) ) {
					$field->setValue( (array) $geocode_data->geometry->location, $type, $objectId );
				} else {
					$field->setValue( array( 'lat' => '', 'lng' => '' ), $type, $objectId );
				}

				foreach ( $geocode_data->address_components as $address_component ) {
					if ( ! is_array( $address_component->types ) || ! isset( $address_component->types[0] ) ) continue;
					if ( ! array_key_exists( $address_component->types[0], $address_component_meta ) ) continue;
					$meta = $address_component_meta[ $address_component->types[0] ];
					
					$meta->long = $address_component->long_name;
					$meta->short = $address_component->short_name;
				}

			}

			foreach ( $address_component_meta as $key => $meta ) {
				update_post_meta( $objectId, '_ac_' . $key . '_l', $meta->long );
				update_post_meta( $objectId, '_ac_' . $key . '_s', $meta->short );
			}

		}


		public static function register_post_center_taxonomy() {

			self::$post_center_taxonomy = new Taxonomy( array(
				'name' => 'post_center',
				'singularLabel' => 'SBDC',
				'pluralLabel' => 'SBDCs',
				'postTypes' => array( 'event', 'job' ),
				'settings' => array(
					'hierarchical' => true,
					'rewrite' => array( 'slug' => 'post-centers', 'with_front' => false ),
					'show_in_nav_menus' => false,
					'show_admin_column' => true,
					'publicly_queryable' => false,
					'show_in_rest' => true,
					'labels' => array(
						'menu_name' => 'SBDCs',
						'all_items' => 'All SBDCs'
					),
					'capabilities' => array(
						'manage_terms' => 'manage_post_centers',
						'edit_terms' => 'edit_post_centers',
						'delete_terms' => 'delete_post_centers',
						'assign_terms' => 'assign_post_centers'
					)
				)
			) );

		}


		public static function update_post_center_term( $post_id ) {

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;
			// if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return $post_id;
			if ( defined( 'DOING_CRON' ) && DOING_CRON ) return $post_id;

			$post = get_post( $post_id );
			if ( ! $post || $post->post_type != 'center' ) return $post_id;
			if ( $post->post_title == 'Auto Draft' ) return $post_id;

			$term_id = get_post_meta( $post_id, 'center_term_id', true );
			$term = get_term( $term_id, 'post_center' );
			if ( is_wp_error( $term ) || empty( $term ) ) {
				$term = wp_insert_term( $post->post_title, 'post_center' );
				$term = get_term( $term['term_id'], 'post_center' );
			}

			update_post_meta( $post_id, 'center_term_id', $term->term_id );

			wp_update_term( $term->term_id, 'post_center', array( 'name' => $post->post_title, 'slug' => sanitize_title( $post->post_title ) ) );
			update_term_meta( $term->term_id, 'center_post_id', $post_id );

		}


		public static function delete_post_center_term( $post_id ) {

			$center_terms = get_terms( array(
				'taxonomy' => 'post_center',
				'hide_empty' => false,
				'meta_query' => array(
					array( 'key' => 'center_post_id', 'value' => $post_id )
				)
			) );
			foreach ( $center_terms as $term ) {
				wp_delete_term( $term->term_id, 'post_center' );
			}

		}


		public static function filter_use_block_editor_for_post_type( $use_block_editor, $post_type ) {
			return in_array( $post_type, array( 'job' ) ) ? false : $use_block_editor;
		}


		public static function register_admin_styles( $hook ) {
			
			$screen = get_current_screen();
			if ( $screen->base == 'post' && $screen->post_type == 'job' ) {

				ob_start();
				?>
					<style>
						
					</style>
				<?php
				$css = trim( ob_get_clean() );
				$css = trim( preg_replace( array( '/^<style>/', '/<\/style>$/' ), '', $css ) );
				wp_add_inline_style( 'common', $css );

			}

		}


	}
}