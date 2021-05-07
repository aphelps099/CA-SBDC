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


if ( ! class_exists( 'Crown_Champions' ) ) {
	class Crown_Champions {

		public static $init = false;

		public static $champion_post_type = null;


		public static function init() {
			if( self::$init ) return;
			self::$init = true;

			GoogleMaps::init( array( 'apiKey' => defined( 'CROWN_GOOGLE_API_KEY' ) ? CROWN_GOOGLE_API_KEY : '' ) );

			$plugin_file = preg_replace( '/\/classes$/', '', dirname( __FILE__ ) ) . '/plugin.php';
			register_activation_hook( $plugin_file, array( __CLASS__, 'activate' ));
			register_deactivation_hook( $plugin_file, array( __CLASS__, 'deactivate' ));

			add_action( 'after_setup_theme', array( __CLASS__, 'register_champion_post_type' ) );

			add_action( 'wp_ajax_get_champion_state_from_zip', array( __CLASS__, 'get_ajax_champion_state_from_zip' ) );
			add_action( 'wp_ajax_nopriv_get_champion_state_from_zip', array( __CLASS__, 'get_ajax_champion_state_from_zip' ) );

		}


		public static function activate() {
			global $wp_roles;
			
			foreach ( $wp_roles->role_objects as $role ) {
				foreach ( array( 'publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private' ) as $cap ) {
					if ( $role->has_cap( $cap . '_posts' ) ) {
						$role->add_cap( $cap . '_champions' );
					}
				}
			}

			flush_rewrite_rules();
		}


		public static function deactivate() {
			global $wp_roles;
			
			foreach ( $wp_roles->role_objects as $role ) {
				foreach ( array( 'publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private' ) as $cap ) {
					$role->remove_cap( $cap . '_champions' );
				}
			}
			
			flush_rewrite_rules();
		}


		public static function get_states() {
			$config = json_decode( file_get_contents( dirname( __FILE__ ) . '/../config.json' ) );
			return $config->states;
		}


		public static function get_state_by( $prop, $value ) {
			if ( ! in_array( $prop, array( 'name', 'abbreviation' ) ) ) return null;
			$states = self::get_states();
			foreach ( $states as $state ) {
				if ( strtolower( $state->$prop ) == strtolower( $value ) ) {
					return $state;
				}
			}
			return null;
		}


		public static function register_champion_post_type() {

			$state_options = array_map( function( $n ) { return array( 'value' => $n->abbreviation, 'label' => $n->name ); }, self::get_states() );
			array_unshift( $state_options, array( 'value' => '', 'label' => '&mdash;' ) );

			self::$champion_post_type = new PostType( array(
				'name' => 'champion',
				'singularLabel' => 'Champion',
				'pluralLabel' => 'Champions',
				'settings' => array(
					'supports' => array( 'revisions' ),
					'rewrite' => array( 'slug' => 'centers', 'with_front' => false ),
					'menu_icon' => 'dashicons-businessman',
					'has_archive' => false,
					'publicly_queryable' => false,
					'show_in_rest' => true,
					'show_in_nav_menus' => false,
					'capability_type' => array( 'champion', 'champions' ),
					'map_meta_cap' => true,
					'menu_position' => 35
				),
				'metaBoxes' => array(
					new MetaBox( array(
						'id' => 'champion-details',
						'title' => 'Details',
						'priority' => 'high',
						'fields' => array(
							new FieldGroup( array(
								'class' => 'no-border two-column large-left',
								'fields' => array(
									new FieldGroup( array(
										'class' => 'no-border',
										'fields' => array(
											new FieldGroup( array(
												'class' => 'no-border three-column',
												'saveMetaCb' => array( __CLASS__, 'save_champion_name' ),
												'fields' => array(
													new Field( array(
														'label' => 'Name',
														'input' => new TextInput( array( 'name' => 'champion_first_name', 'label' => 'First Name', 'class' => 'input-large' ) )
													) ),
													new Field( array(
														'label' => '&nbsp;',
														'input' => new TextInput( array( 'name' => 'champion_middle_name', 'label' => 'Middle Name (optional)', 'class' => 'input-large' ) )
													) ),
													new Field( array(
														'label' => '&nbsp;',
														'input' => new TextInput( array( 'name' => 'champion_last_name', 'label' => 'Last Name', 'class' => 'input-large' ) )
													) )
												)
											) ),
											new Field( array(
												'label' => 'Job Title',
												'input' => new TextInput( array( 'name' => 'champion_job_title', 'class' => 'input-medium' ) )
											) ),
											new Field( array(
												'label' => 'Organization (SBDC Center)',
												'input' => new TextInput( array( 'name' => 'champion_organization', 'class' => 'input-medium' ) )
											) ),
											new Field( array(
												'label' => 'Email Address',
												'input' => new TextInput( array( 'name' => 'champion_email', 'class' => 'input-medium' ) )
											) )
										)
									) ),
									new FieldGroup( array(
										'class' => 'no-border',
										'fields' => array(
											new FieldGroup( array(
												'label' => 'Primary Headshot Photo',
												'fields' => array(
													new Field( array(
														'input' => new MediaInput( array( 'name' => 'champion_headshot_photo', 'buttonLabel' => 'Select Image', 'mimeType' => 'image' ) )
													) )
												)
											) )
										)
									) )
								)
							) )
						)
					) ),
					new MetaBox( array(
						'id' => 'champion-location',
						'title' => 'Office Location',
						'priority' => 'high',
						'fields' => array(
							new FieldGroup( array(
								'class' => 'no-border two-column',
								'fields' => array(
									new FieldGroup( array(
										'class' => 'no-border',
										'fields' => array(
											new Field( array(
												'label' => 'Street Address 1',
												'input' => new TextInput( array( 'name' => 'champion_street_address_1' ) )
											) ),
											new Field( array(
												'label' => 'Street Address 2',
												'input' => new TextInput( array( 'name' => 'champion_street_address_2' ) )
											) ),
											new FieldGroup( array(
												'class' => 'no-border two-column',
												'fields' => array(
													new Field( array(
														'label' => 'City',
														'input' => new TextInput( array( 'name' => 'champion_city' ) )
													) ),
													new FieldGroup( array(
														'class' => 'no-border two-column',
														'fields' => array(
															new Field( array(
																'label' => 'State',
																'input' => new Select( array( 'name' => 'champion_state', 'options' => $state_options ) )
															) ),
															new Field( array(
																'label' => 'Zip',
																'input' => new TextInput( array( 'name' => 'champion_zip' ) )
															) )
														)
													) )
												)
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
				),
				'listTableColumns' => array(
					new ListTableColumn( array(
						'key' => 'champion-details',
						'title' => 'Details',
						'position' => 2,
						'outputCb' => function( $post_id, $args ) {
							$output = array();
							if ( ( $value = get_post_meta( $post_id, 'champion_job_title', true ) ) ) {
								$output[] = $value;
							}
							if ( ( $value = get_post_meta( $post_id, 'champion_organization', true ) ) ) {
								$output[] = $value;
							}
							echo implode( '<br>', $output );
						}
					) ),
					new ListTableColumn( array(
						'key' => 'champion-state',
						'title' => 'State',
						'position' => 3,
						'outputCb' => function( $post_id, $args ) {
							$state_abbreviation = get_post_meta( $post_id, 'champion_state', true );
							$state = self::get_state_by( 'abbreviation', $state_abbreviation );
							if ( $state ) echo $state->name;
						}
					) )
				)
			) );

		}


		public static function save_champion_name( $field, $input, $type, $object_id, $value ) {
			$post_data = array(
				'ID' => $object_id,
				'post_title' => array(),
				'post_name' => ''
			);
			$first = array();
			if ( isset( $input['champion_first_name'] ) && ! empty( $input['champion_first_name'] ) ) $first[] = $input['champion_first_name'];
			if ( isset( $input['champion_middle_name'] ) && ! empty( $input['champion_middle_name'] ) ) $first[] = $input['champion_middle_name'];
			if ( ! empty( $first ) ) $post_data['post_title'][] = implode( ' ', $first );
			if ( isset( $input['champion_last_name'] ) && ! empty( $input['champion_last_name'] ) ) $post_data['post_title'][] = $input['champion_last_name'];
			update_post_meta( $object_id, 'champion_name_first_comma_last', implode( ', ', $post_data['post_title'] ) );
			update_post_meta( $object_id, 'champion_name_first_comma_last_lc', strtolower( implode( ', ', $post_data['post_title'] ) ) );
			update_post_meta( $object_id, 'champion_name_last_comma_first', implode( ', ', array_reverse( $post_data['post_title'] ) ) );
			update_post_meta( $object_id, 'champion_name_last_comma_first_lc', strtolower( implode( ', ', array_reverse( $post_data['post_title'] ) ) ) );
			update_post_meta( $object_id, 'champion_name_last_initial_lc', substr( strtolower( implode( ', ', array_reverse( $post_data['post_title'] ) ) ), 0, 1 ) );
			update_post_meta( $object_id, 'champion_name_first_initial_lc', substr( strtolower( implode( ', ', $post_data['post_title'] ) ), 0, 1 ) );
			$post_data['post_title'] = implode( ' ', $post_data['post_title'] );
			$post_data['post_name'] = sanitize_title( $post_data['post_title'] );
			wp_update_post( $post_data );
		}


		public static function get_address_components_field_output( $field, $args ) {
			$object_id = isset( $args['objectId'] ) ? $args['objectId'] : 0;

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


		public static function save_coordinates_field_meta( $field, $input, $type, $object_id, $value ) {

			$street_address_1 = isset( $input['champion_street_address_1'] ) ? $input['champion_street_address_1'] : '';
			$street_address_2 = isset( $input['champion_street_address_2'] ) ? $input['champion_street_address_2'] : '';
			$city = isset( $input['champion_city'] ) ? $input['champion_city'] : '';
			$state = isset( $input['champion_state'] ) ? $input['champion_state'] : '';
			$zip = isset( $input['champion_zip'] ) ? $input['champion_zip'] : '';

			$address = array();
			if ( ! empty( $street_address_1 ) ) $address[] = $street_address_1;
			if ( ! empty( $street_address_2 ) ) $address[] = $street_address_2;
			$address_component = array();
			if ( ! empty( $city ) ) $address_component[] = $city;
			if ( ( ! empty( $state ) || ! empty( $zip ) ) && ! empty( $address_component ) ) $address_component[0] .= ',';
			if ( ! empty( $state ) ) $address_component[] = $state;
			if ( ! empty( $zip ) ) $address_component[] = $zip;
			if ( ! empty( $address_component ) ) $address[] = implode( ' ', $address_component );

			update_post_meta( $object_id, 'champion_address', implode( "\r\n", $address ) );
			if ( empty( $address ) ) return;

			$geocode_data = GoogleMaps::getGeocodeData( implode( ', ', $address ) );

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
					$field->setValue( (array) $geocode_data->geometry->location, $type, $object_id );
				} else {
					$field->setValue( array( 'lat' => '', 'lng' => '' ), $type, $object_id );
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
				update_post_meta( $object_id, '_ac_' . $key . '_l', $meta->long );
				update_post_meta( $object_id, '_ac_' . $key . '_s', $meta->short );
			}

		}


		public static function get_state_champions( $state_abbreviation, $args = array() ) {
			$args = array_merge( array(
				'post_type' => 'champion',
				'posts_per_page' => -1,
				'orderby' => 'title',
				'order' => 'ASC',
				'meta_query' => array(
					array( 'key' => 'champion_state', 'value' => $state_abbreviation )
				)
			), $args );
			return get_posts( $args );
		}


		public static function champion_finder( $is_editor = false ) {

			$queried_zip = isset( $_GET['zip'] ) ? trim( $_GET['zip'] ) : '';

			$champion_ids = get_posts( array(
				'post_type' => 'champion',
				'posts_per_page' => -1,
				'fields' => 'ids'
			) );

			$states = self::get_states();
			foreach ( $states as $i => $state ) {
				$champion_ids = self::get_state_champions( $state->abbreviation, array( 'fields' => 'ids' ) );
				$state->champions = array_map( function( $n ) {
					return (object) array(
						'name' => get_the_title( $n ),
						'job_title' => get_post_meta( $n, 'champion_job_title', true ),
						'organization' => get_post_meta( $n, 'champion_organization', true ),
						'email' => get_post_meta( $n, 'champion_email', true ),
						'headshot_photo' => get_post_meta( $n, 'champion_headshot_photo', true )
					);
				}, $champion_ids );
				$states[ $i ] = $state;
			}
			
			$map_svg = file_get_contents( dirname( __FILE__ ) . '/../assets/img/us.svg' );

			$search_action = remove_query_arg( array( 'zip' ) );

			?>
				<div class="champion-finder">

					<div class="search-results">

						<div class="wp-block-crown-blocks-header">
							<div class="inner">
								<h2>Find your State Champion</h2>
							</div>
							<div class="hr-container reveal-right">
								<hr style="background: linear-gradient(to right, rgba(0, 129, 183, 0), rgb(0, 129, 183));">
							</div>
						</div>

						<form class="location-search-form" method="get" action="<?php echo esc_attr( $search_action ); ?>">
							<div class="field">
								<input type="text" name="zip" value="<?php echo esc_attr( $queried_zip ); ?>" placeholder="<?php echo esc_attr( __( 'Enter Zipcode' ), 'crown_centers' ); ?>">
							</div>
							<footer class="form-footer">
								<button type="submit" class="btn btn-primary">Search</button>
							</footer>
						</form>

						<div class="results">
							<div class="inner">

								<?php foreach ( $states as $state ) { ?>
									<?php if ( empty( $state->champions ) ) continue; ?>
									<div class="state state-<?php echo sanitize_title( $state->abbreviation ); ?>" data-state="<?php echo esc_attr( $state->abbreviation ); ?>">
										<?php foreach ( $state->champions as $champion ) { ?>
											<div class="champion">
												<div class="inner">
													<h4 class="state-name"><?php echo $state->name; ?></h4>
													<h3 class="name"><?php echo $champion->name; ?></h3>
													<?php if ( ! empty( $champion->job_title ) ) { ?>
														<p class="job-title"><?php echo $champion->job_title; ?></p>
													<?php } ?>
													<?php if ( ! empty( $champion->organization ) ) { ?>
														<p class="organization"><?php echo $champion->organization; ?></p>
													<?php } ?>
													<?php if ( ! empty( $champion->email ) ) { ?>
														<p class="email"><a href="mailto:<?php echo esc_attr( $champion->email ); ?>" target="_blank">Email me</a></p>
													<?php } ?>
													<div class="headshot-container">
														<div class="headshot">
															<?php if ( ! empty( $champion->headshot_photo ) ) { ?>
																<?php echo wp_get_attachment_image( $champion->headshot_photo, 'medium' ); ?>
															<?php } ?>
														</div>
													</div>
												</div>
											</div>
										<?php } ?>
									</div>
								<?php } ?>

							</div>
						</div>

					</div>

					<div class="map-container">
						<div class="inner">
							<div class="map">
								<?php echo $map_svg; ?>
							</div>
						</div>
					</div>

				</div>
			<?php
		}


		public static function get_ajax_champion_state_from_zip() {
			$response = (object) array(
				'success' => false,
				'state' => null
			);
			$zip = isset( $_GET['zip'] ) ? $_GET['zip'] : '';
			$geocode_data = GoogleMaps::getGeocodeData( $zip );
			if ( $geocode_data ) {
				foreach ( $geocode_data->address_components as $address_component ) {
					if ( is_array( $address_component->types ) && isset( $address_component->types[0] ) && $address_component->types[0] == 'administrative_area_level_1' ) {
						$state = self::get_state_by( 'abbreviation', $address_component->short_name );
						if ( $state ) {
							$response->state = $state;
							$response->success = true;
						}
					}
				}
			}
			wp_send_json( $response );
		}


	}
}