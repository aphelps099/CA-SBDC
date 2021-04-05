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

			add_action( 'save_post', array( __CLASS__, 'update_post_center_term' ), 100 );
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
				foreach ( array( 'manage', 'edit', 'delete' ) as $cap ) {
					if ( $role->has_cap( 'manage_categories' ) ) {
						$role->add_cap( $cap . '_post_centers' );
					}
				}
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
					'show_ui' => is_main_site(),
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
							) ),
							new Field(array(
								'label' => 'Network Site',
								'input' => new Select( array( 'name' => 'center_site_id', 'class' => 'input-medium' ) ),
								'getOutputCb' => array( __CLASS__, 'set_network_site_id_select_field_options' )
							) ),
							new Field(array(
								'input' => new CheckboxSet( array( 'name' => 'center_options', 'options' => array(
									array( 'value' => 'hide-from-index', 'label' => 'Hide from index' )
								) ) )
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


		public static function set_network_site_id_select_field_options( $field, $args ) {
			$sites = get_sites( array( 'site__not_in' => array( get_current_blog_id() ) ) );
			$site_options = array_map( function ( $n ) {
				$site_details = get_blog_details( array( 'blog_id' => $n->blog_id ) );
				return array(
					'value' => $n->blog_id,
					'label' => $site_details->blogname
				);
			}, $sites );
			usort( $site_options, function ( $a, $b ) { return strcmp( $a['label'], $b['label'] ); } );
			$options = array_merge( array(
				array( 'value' => '', 'label' => '&mdash;' )
			), $site_options );
			$field->getInput()->setOptions( $options );
		}


		public static function get_address_components_field_output( $field, $args ) {
			$object_id = isset( $args['entryId'] ) ? $args['entryId'] : 0;

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
				'postTypes' => array( 'event', 'job', 'team_member', 'team_member_s', 'client_story' ),
				'settings' => array(
					'hierarchical' => true,
					'rewrite' => array( 'slug' => 'post-centers', 'with_front' => false ),
					'show_in_nav_menus' => false,
					'show_in_menu' => false,
					'show_admin_column' => is_main_site(),
					'show_ui' => is_main_site(),
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
				$new_term = wp_insert_term( $post->post_title, 'post_center' );
				$term = ! is_wp_error( $new_term ) ? get_term( $new_term['term_id'], 'post_center' ) : null;
			}

			if ( ! is_wp_error( $term ) && ! empty( $term ) ) {

				update_post_meta( $post_id, 'center_term_id', $term->term_id );

				wp_update_term( $term->term_id, 'post_center', array( 'name' => $post->post_title, 'slug' => sanitize_title( $post->post_title ) ) );
				update_term_meta( $term->term_id, 'center_post_id', $post_id );
				update_term_meta( $term->term_id, 'center_site_id', get_post_meta( $post_id, 'center_site_id', true ) );

			}

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


		public static function center_finder( $is_editor = false ) {

			$queried_zip = isset( $_GET['zip'] ) ? trim( $_GET['zip'] ) : '';

			$excluded_center_ids = get_posts( array(
				'post_type' => 'center',
				'posts_per_page' => -1,
				'fields' => 'ids',
				'meta_query' => array(
					array( 'key' => '__center_options', 'value' => 'hide-from-index' )
				)
			) );
			// $excluded_center_ids = array();
			
			$included_center_ids = get_posts( array(
				'post_type' => 'center',
				'posts_per_page' => -1,
				'fields' => 'ids',
				'post__not_in' => $excluded_center_ids
			) );

			$office_query_args = array(
				'post_type' => 'crown_repeater_entry',
				'posts_per_page' => -1,
				'post_status' => 'publish',
				'post_parent__in' => $included_center_ids,
				'fields' => 'ids',
				'meta_query' => array(
					array( 'key' => 'crown_repeater_entry_name', 'value' => 'center_office_locations' )
				)
			);

			$nearby_offices = array();
			if ( ! empty( $queried_zip ) ) {
				$nearby_locations = self::get_nearby_center_locations( $queried_zip, 100 );
				if ( ! empty( $nearby_locations ) ) {
					$office_query_args['post__in'] = array_map( function( $n ) { return $n->office_id; }, $nearby_locations );
					$office_query_args['orderby'] = 'post__in';
					$office_query_args['order'] = 'ASC';
					foreach( $nearby_locations as $n ) {
						$nearby_offices[ 'id:' . $n->office_id ] = $n;
					}
				} else {
					$office_query_args['post__in'] = array( 0 ); // no results
				}
			}

			$office_ids = get_posts( $office_query_args );
			$locations = array_map( function( $office_id ) use ( $nearby_offices ) {
				$center_id = wp_get_post_parent_id( $office_id );
				return (object) array(
					'center' => (object) array(
						'id' => $center_id,
						'title' => get_the_title( $center_id ),
						'website' => get_post_meta( $center_id, 'center_website', true ),
						'phone' => get_post_meta( $center_id, 'center_phone', true ),
						'email' => get_post_meta( $center_id, 'center_email', true )
					),
					'office' => (object) array(
						'id' => $office_id,
						'featured_image' => get_post_meta( $office_id, 'featured_image', true ),
						'phone' => get_post_meta( $office_id, 'phone', true ),
						'address' => get_post_meta( $office_id, 'address', true ),
						'coordinates' => get_post_meta( $office_id, 'coordinates', true )
					),
					'distance' => array_key_exists( 'id:' . $office_id, $nearby_offices ) ? $nearby_offices[ 'id:' . $office_id ]->distance : null
				);
			}, $office_ids );

			if ( empty( $nearby_locations ) ) {
				usort( $locations, function( $a, $b ) {
					if ( $a->center->title == $b->center->title ) {
						return strcmp( $a->office->address, $b->office->address );
					}
					return strcmp( $a->center->title, $b->center->title );
				} );
			}

			$map_args = array(
				'points' => array(),
				'autoAddMarkers' => false,
				'options' => array(
					'styles' => apply_filters( 'crown_google_map_styles', null ),
					'scrollwheel' => false,
					'mapTypeControl' => false,
					'streetViewControl' => false,
					'zoom' => 11
				)
			);
			foreach ( $locations as $location ) {
				if ( ! is_array( $location->office->coordinates ) || empty( $location->office->coordinates['lat'] ) || empty( $location->office->coordinates['lng'] ) ) continue;
				$point = $location->office->coordinates;
				$point['locationId'] = $location->office->id;
				$map_args['points'][] = $point;
			}

			$search_action = remove_query_arg( array( 'zip' ) );

			?>
				<div class="center-finder">

					<form class="location-search-form" method="get" action="<?php echo esc_attr( $search_action ); ?>">
						<div class="field">
							<input type="text" name="zip" value="<?php echo esc_attr( $queried_zip ); ?>" placeholder="<?php echo esc_attr( __( 'Enter Your Zipcode' ), 'crown_centers' ); ?>">
						</div>
						<footer class="form-footer">
							<button type="submit" class="btn btn-primary">Search</button>
						</footer>
					</form>

					<?php if ( empty( $locations ) ) { ?>

						<div class="alert alert-warning no-results">
							<h4><?php _e( 'No Results Found' ); ?></h4>
							<p><?php _e( 'Please try searching another zip code or'); ?> <a href="<?php echo esc_attr( $search_action ); ?>"><?php _e( 'view all' ); ?></a> <?php _e( 'our SBDC\'s' ); ?>.</p>
						</div>

					<?php } else { ?>

						<div class="results">

							<div class="locations">
								<div class="inner">
									<?php foreach( $locations as $location ) { ?>
										<?php self::center_finder_location( $location ); ?>
									<?php } ?>
								</div>
							</div>

							<div class="map">
								<div class="inner">
									<?php if ( $is_editor ) { ?>
										<div class="placeholder"></div>
									<?php } else { ?>
										<?php echo GoogleMaps::getMap( $map_args ); ?>
									<?php } ?>
								</div>
							</div>

							<div class="preview">
								<?php self::center_finder_location( $locations[0] ); ?>
							</div>

						</div>

					<?php } ?>

				</div>
			<?php
		}


		protected static function center_finder_location( $location ) {
			?>
				<article class="location" data-location-id="<?php echo $location->office->id; ?>">
					
					<?php $image_src = $location->office->featured_image ? wp_get_attachment_image_url( $location->office->featured_image, 'large' ) : false; ?>
					<?php if ( ! empty( $image_src ) ) { ?>
						<div class="entry-featured-image">
							<div class="image" style="background-image: url(<?php echo $image_src; ?>);">
								<?php echo wp_get_attachment_image( $location->office->featured_image, 'large' ) ?>
							</div>
						</div>
					<?php } ?>

					<header class="entry-header">
						<h3 class="entry-title"><?php echo $location->center->title; ?></h3>
						<?php if ( $location->distance != null ) { ?>
							<p class="entry-distance">
								<span class="value"><?php echo number_format( $location->distance, 2 ); ?></span>
								<span class="units"><?php _e( 'mi', 'crown_centers' ); ?></span>
							</p>
						<?php } ?>
					</header>

					<div class="entry-contents">

						<p class="entry-address"><?php echo nl2br( $location->office->address ); ?></p>

						<?php $phone = ! empty( $location->office->phone ) ? $location->office->phone : $location->center->phone; ?>
						<?php if ( ! empty( $phone ) ) { ?>
							<p class="entry-phone">
								<span class="label"><?php _e( 'Phone', 'crown_centers' ); ?>:</span>
								<span class="value"><a href="<?php echo self::get_tel_link( $phone ); ?>"><?php echo $phone; ?></a></span>
							</p>
						<?php } ?>

						<?php if ( ! empty( $location->center->email ) ) { ?>
							<p class="entry-email">
								<span class="label"><?php _e( 'Email', 'crown_centers' ); ?>:</span>
								<span class="value"><a href="mailto:<?php echo esc_attr( $location->center->email ); ?>"><?php echo $location->center->email; ?></a></span>
							</p>
						<?php } ?>

						<?php if ( ! empty( $location->center->website ) ) { ?>
							<p class="entry-website">
								<span class="label"><?php _e( 'Web', 'crown_centers' ); ?>:</span>
								<span class="value"><a href="<?php echo esc_attr( $location->center->website ); ?>" target="_blank"><?php echo self::get_url_domain_name( $location->center->website ); ?></a></span>
							</p>
						<?php } ?>

						<?php if ( ! empty( $location->center->website ) ) { ?>
							<p class="entry-link">
								<a href="<?php echo esc_attr( $location->center->website ); ?>" target="_blank" class="btn btn-red btn-lg btn-has-arrow-icon"><?php _e( 'Visit Website', 'crown_centers' ); ?></a>
							</p>
						<?php } ?>

					</div>

				</article>
			<?php
		}


		protected static function get_nearby_center_locations( $address, $radius = 0 ) {
			global $wpdb;

			$search_coords = GoogleMaps::geocode( $address );
			if ( empty( $search_coords ) ) return array();

			$query = "
				SELECT
					center.ID center_id,
					center.post_title center_title,
					p.ID office_id,
					lat.meta_value lat,
					lng.meta_value lng,
					(3959 * acos(cos(radians(" . $search_coords->lat . ")) * cos(radians(lat.meta_value)) * cos(radians(lng.meta_value) - radians(" . $search_coords->lng . ")) + sin(radians(" . $search_coords->lat . ")) * sin(radians(lat.meta_value)))) AS distance
				FROM $wpdb->posts p
				INNER JOIN $wpdb->posts center ON (p.post_parent = center.ID AND center.post_type = 'center' AND center.post_status = 'publish')
				INNER JOIN $wpdb->postmeta name ON (p.ID = name.post_id AND name.meta_key = 'crown_repeater_entry_name' AND name.meta_value = 'center_office_locations')
				INNER JOIN $wpdb->postmeta lat ON (p.ID = lat.post_id AND lat.meta_key = 'coordinates_lat')
				INNER JOIN $wpdb->postmeta lng ON (p.ID = lng.post_id AND lng.meta_key = 'coordinates_lng')
				WHERE p.post_type = 'crown_repeater_entry'
				AND p.post_status = 'publish'
				" . ( $radius > 0 ? "HAVING distance <= " . $radius : "" ) . "
				ORDER BY distance ASC
			";
			return $wpdb->get_results( $query );

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


		protected static function get_url_domain_name( $url ) {
			$domain = $url;
			if ( preg_match( '/^(?:https?:\/\/)?([\w.-]+(?:\.[\w\.-]+)+).*/i', $url, $matches ) ) {
				$domain = $matches[1];
			}
			$domain = preg_replace( '/^www\./', '', $domain );
			return $domain;
		}


	}
}