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
use Crown\Form\Input\RichTextarea;
use Crown\Form\Input\Textarea;
use Crown\Form\Input\Gallery as GalleryInput;
use Crown\ListTableColumn;
use Crown\Post\MetaBox;
use Crown\Post\Type as PostType;
use Crown\Post\Taxonomy;
use Crown\Shortcode;
use Crown\UIRule;


if ( ! class_exists( 'Crown_Events' ) ) {
	class Crown_Events {

		public static $init = false;

		public static $event_post_type = null;
		public static $syndicated_event_post_type = null;
		public static $event_series_taxonomy = null;

		public static $weekly_event_count_shortcode = null;


		public static function init() {
			if( self::$init ) return;
			self::$init = true;

			$plugin_file = preg_replace( '/\/classes$/', '', dirname( __FILE__ ) ) . '/plugin.php';
			register_activation_hook( $plugin_file, array( __CLASS__, 'activate' ));
			register_deactivation_hook( $plugin_file, array( __CLASS__, 'deactivate' ));

			add_action( 'init', array( __CLASS__, 'init_scheduled_events' ) );
			add_action( 'crown_sync_event_data', array( __CLASS__, 'sync_event_data' ) );
			// add_action( 'init', array( __CLASS__, 'sync_event_data' ), 100, 0 );

			add_action( 'after_setup_theme', array( __CLASS__, 'register_event_post_type' ) );
			add_action( 'after_setup_theme', array( __CLASS__, 'register_event_series_taxonomy' ) );
			add_action( 'after_setup_theme', array( __CLASS__, 'register_weekly_event_count_shortcode' ) );

			add_action( 'save_post', array( __CLASS__, 'update_post_center_terms' ), 90 );

			add_action( 'save_post', array( __CLASS__, 'update_shared_post_reference' ), 100 );
			add_action( 'after_delete_post', array( __CLASS__, 'delete_syndicated_post' ), 100 );

			// add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'filter_use_block_editor_for_post_type' ), 10, 2 );

			add_action( 'admin_init', array( __CLASS__, 'process_action_publish_syndicated_event' ) );
			add_action( 'admin_init', array( __CLASS__, 'process_action_unpublish_syndicated_event' ) );
			add_action( 'admin_notices', array( __CLASS__, 'output_syndicated_event_admin_notices' ) );
			add_filter( 'bulk_actions-edit-event_s', array( __CLASS__, 'filter_bulk_actions_edit_event_s'), 100, 2 );
			add_filter( 'disable_months_dropdown', '__return_true' );
			add_filter( 'display_post_states', array( __CLASS__, 'filter_display_post_states'), 10, 2 );
			add_filter( 'post_row_actions', array( __CLASS__, 'filter_post_row_actions' ), 100, 2 );
			add_filter( 'get_edit_post_link', array( __CLASS__, 'filter_get_edit_post_link' ), 10, 3 );

			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_admin_styles' ) );

			add_action( 'gform_loaded', array( __CLASS__, 'register_gf_addons' ) );
			add_filter( 'gform_addon_feed_settings_fields', array( __CLASS__, 'filter_gform_addon_feed_settings_fields' ), 10, 2 );
			add_filter( 'gform_addon_pre_process_feeds', array( __CLASS__, 'filter_gform_addon_pre_process_feeds' ), 10, 3 );

		}


		public static function activate() {
			global $wp_roles;
			
			foreach ( $wp_roles->role_objects as $role ) {
				foreach ( array( 'publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private' ) as $cap ) {
					if ( $role->has_cap( $cap . '_posts' ) ) {
						$role->add_cap( $cap . '_events' );
					}
				}
				foreach ( array( 'manage', 'edit', 'delete' ) as $cap ) {
					if ( $role->has_cap( 'manage_categories' ) ) {
						$role->add_cap( $cap . '_event_series' );
					}
				}
				if ( $role->has_cap( 'edit_posts' ) ) {
					$role->add_cap( 'assign_event_series' );
				}
			}

			flush_rewrite_rules();
		}


		public static function deactivate() {
			global $wp_roles;
			
			foreach ( $wp_roles->role_objects as $role ) {
				foreach ( array( 'publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private' ) as $cap ) {
					$role->remove_cap( $cap . '_events' );
				}
				foreach ( array( 'manage', 'edit', 'delete', 'assign' ) as $cap ) {
					$role->remove_cap ( $cap . '_event_series' );
				}
			}
			
			flush_rewrite_rules();
		}


		public static function init_scheduled_events() {

			// wp_clear_scheduled_hook( 'crown_sync_event_data' );
			if ( ! wp_next_scheduled( 'crown_sync_event_data' ) ) {
				wp_schedule_single_event( time() - 60, 'crown_sync_event_data' );
			}

		}


		public static function sync_event_data( $sync_all = false ) {
			global $wpdb;

			$current_time = new DateTime();

			if ( ! is_main_site() ) {

				$query_modified_time = new DateTime( '@0' );
				if ( ! $sync_all ) {
					$query_modified_time = get_option( 'crown_event_data_last_synced' ) ? new DateTime( get_option( 'crown_event_data_last_synced', 0 ) ) : new DateTime();
					$query_modified_time->modify( '-1 hour' );
				}

				$syn_post_ids = $wpdb->get_col( $wpdb->prepare( "
					SELECT pm2.meta_value
					FROM $wpdb->posts p
					INNER JOIN $wpdb->postmeta pm1 ON (pm1.post_id = p.ID AND pm1.meta_key = '_original_site_id')
					INNER JOIN $wpdb->postmeta pm2 ON (pm2.post_id = p.ID AND pm2.meta_key = '_original_post_id')
					WHERE p.post_type = 'event_s'
						AND pm1.meta_value = %s
				", get_main_site_id() ) );

				$dest_site = get_current_blog_id();
				switch_to_blog( get_main_site_id() );

				$update_post_ids = get_posts( array(
					'post_type' => 'event',
					'posts_per_page' => -1,
					'fields' => 'ids',
					'date_query' => array(
						array( 'column' => 'post_modified_gmt', 'after' => $query_modified_time->format( 'Y-m-d H:i:s' ) )
					),
					'meta_query' => array(
						array( 'key' => '__event_options', 'value' => 'post-to-center-sites' )
					)
				) );

				foreach ( $update_post_ids as $post_id ) {
					self::syndicate_post( $post_id, $dest_site );
				}

				$post_ids = get_posts( array(
					'post_type' => 'event',
					'posts_per_page' => -1,
					'fields' => 'ids',
					'meta_query' => array(
						array( 'key' => '__event_options', 'value' => 'post-to-center-sites' )
					)
				) );
				
				$old_post_ids = array_diff( $syn_post_ids, $post_ids );
				foreach ( $old_post_ids as $post_id ) {
					self::delete_syndicated_post( $post_id, $dest_site );
				}

				restore_current_blog();

			}

			update_option( 'crown_event_data_last_synced', $current_time->format( 'Y-m-d H:i:s' ) );

			$sync_interval = 30; // minutes
			wp_clear_scheduled_hook( 'crown_sync_event_data' );
			wp_schedule_single_event( time() + ( 60 * $sync_interval ), 'crown_sync_event_data' );

		}


		public static function register_event_post_type() {

			$timezone_options = array();
			$option_group = null;
			$other_options = array();
			foreach ( DateTimeZone::listIdentifiers(DateTimeZone::ALL) as $timezone ) {
				$option = array( 'value' => $timezone, 'label' => str_replace( array( '_', '/' ), array( ' ', ' - ' ), $timezone ) );
				if ( preg_match( '/^([^\/]+)\/(.+)$/', $timezone, $matches ) ) {
					$option['label'] = str_replace( array( '_', '/' ), array( ' ', ' - ' ), $matches[2] );
					if ( ! $option_group ) {
						$option_group = array( 'label' => $matches[1], 'options' => array() );
					} else if ( $option_group['label'] != $matches[1] ) {
						$timezone_options[] = $option_group;
						$option_group = array( 'label' => $matches[1], 'options' => array() );
					}
					$option_group['options'][] = $option;
				} else {
					$other_options[] = $option;
				}
			}
			if ( $option_group ) $timezone_options[] = $option_group;
			$timezone_options = array_merge( $timezone_options, $other_options );

			$event_options = array(
				array( 'value' => 'featured-post', 'label' => 'Featured Event' )
			);
			if ( is_main_site() ) {
				$event_options[] = array( 'value' => 'post-to-center-sites', 'label' => 'Display on Center Sites' );
			} else {
				$event_options[] = array( 'value' => 'do-not-post-to-regional-site', 'label' => 'Don\'t Display on Regional Site' );
			}

			$event_date_list_table_column = new ListTableColumn( array(
				'key' => 'event-date',
				'title' => 'Event Date',
				'position' => 2,
				'outputCb' => function( $post_id, $args ) {
					$output = array();
					$start = strtotime( get_post_meta( $post_id, 'event_start_timestamp', true ) );
					$end = strtotime( get_post_meta( $post_id, 'event_end_timestamp', true ) );
					$tz = get_post_meta( $post_id, 'event_timezone', true );
					if ( $start === false || $end === false ) return;
					$start = new DateTime( get_post_meta( $post_id, 'event_start_timestamp', true ), ! empty( $tz ) ? new DateTimeZone( $tz ) : null );
					$end = new DateTime( get_post_meta( $post_id, 'event_end_timestamp', true ), ! empty( $tz ) ? new DateTimeZone( $tz ) : null );
					$output[] = '<strong>' . $start->format( 'D, M j, Y' ) . '</strong>';
					$output[] = $start->format( 'g:ia' ) . ' - ' . $end->format( 'g:ia' ) . ' (' . $start->format( 'T' ) . ')';
					echo implode( '<br>', $output );
				},
				'sortCb' => function( $query_vars ) {
					$query_vars['meta_key'] = 'event_start_timestamp_utc';
					$query_vars['orderby'] = 'meta_value';
					return $query_vars;
				}
			) );

			self::$event_post_type = new PostType( array(
				'name' => 'event',
				'singularLabel' => 'Event',
				'pluralLabel' => 'Events',
				'settings' => array(
					'supports' => array( 'title', 'editor', 'excerpt', 'revisions', 'thumbnail' ),
					'rewrite' => array( 'slug' => 'event', 'with_front' => false ),
					'menu_icon' => 'dashicons-calendar',
					'has_archive' => false,
					'publicly_queryable' => true,
					'show_in_rest' => true,
					'show_in_nav_menus' => false,
					'capability_type' => array( 'event', 'events' ),
					'map_meta_cap' => true,
					'menu_position' => 31,
					'template' => array(
						array( 'crown-blocks/event-header', array() ),
						array( 'crown-blocks/container', array(
							'align' => 'full',
							'spacingProfile' => '',
							'paddingTopXl' => 0,
							'paddingBottomXl' => 10,
							'paddingXXl' => 5,
							'paddingTopLg' => 0,
							'paddingBottomLg' => 10,
							'paddingXLg' => 4,
							'paddingTopMd' => 0,
							'paddingBottomMd' => 7,
							'paddingXMd' => 3,
							'paddingTopSm' => 0,
							'paddingBottomSm' => 5,
							'paddingXSm' => 2,
							'paddingTopXs' => 0,
							'paddingBottomXs' => 5,
							'paddingXXs' => 2
						), array(
							array( 'crown-blocks/multi-column', array(
								'columnBreakpoint' => 'lg',
								'columnSpacingXl' => 0
							), array(
								array( 'crown-blocks/column', array(
									'paddingTopXs' => 2,
									'paddingTopSm' => 2,
									'paddingTopMd' => 2,
									'paddingTopLg' => 2,
									'paddingTopXl' => 3,
									'paddingRightLg' => 2,
									'paddingRightXl' => 3,
									'paddingBottomXs' => 2,
									'paddingBottomSm' => 2,
									'paddingBottomMd' => 2,
									'paddingBottomLg' => 2,
									'paddingBottomXl' => 3
								), array(
									array( 'core/heading', array(
										'content' => 'Event Details'
									) ),
									array( 'core/paragraph', array() )
								) ),
								array( 'crown-blocks/column', array(), array(
									array( 'crown-blocks/container', array(
										'spacingProfile' => '',
										'paddingTopXl' => 6,
										'paddingBottomXl' => 6,
										'paddingXXl' => 6,
										'paddingTopLg' => 4,
										'paddingBottomLg' => 4,
										'paddingXLg' => 4,
										'paddingTopMd' => 4,
										'paddingBottomMd' => 4,
										'paddingXMd' => 4,
										'paddingTopSm' => 4,
										'paddingBottomSm' => 4,
										'paddingXSm' => 4,
										'paddingTopXs' => 4,
										'paddingBottomXs' => 4,
										'paddingXXs' => 4,
										'backgroundColor' => '#FFFFFF',
										'backgroundColorSlug' => '',
										'className' => 'bg-flush-right position-lg-sticky'
									), array(
										array( 'crown-blocks/button', array(
											'label' => 'Register for Event',
											'size' => 'lg',
											'displayAsBlock' => true
										) )
									) )
								) )
							) )
						) )
					)
				),
				'metaBoxes' => array(
					new MetaBox( array(
						'id' => 'event-details',
						'title' => 'Event Details',
						'priority' => 'high',
						'fields' => array(
							new FieldGroupSet(array(
								'fieldGroups' => array(
									new FieldGroup( array(
										'label' => 'Date & Time',
										'fields' => array(
											new Field( array(
												'label' => 'Date',
												'input' => new DateInput( array( 'name' => 'event_date', 'class' => 'input-small' ) )
											) ),
											new Field( array(
												'label' => 'Start Time',
												'input' => new TimeInput( array( 'name' => 'event_start_time', 'defaultValue' => '12:00:00', 'class' => 'input-small' ) )
											) ),
											new Field( array(
												'label' => 'End Time',
												'input' => new TimeInput( array( 'name' => 'event_end_time', 'defaultValue' => '13:00:00', 'class' => 'input-small' ) )
											) ),
											new Field( array(
												'label' => 'Timezone',
												'input' => new Select( array( 'name' => 'event_timezone', 'defaultValue' => 'America/Los_Angeles', 'class' => 'input-small', 'options' => $timezone_options ) )
											) )
										)
									) ),
									new FieldGroup( array(
										'label' => 'Options',
										'fields' => array(
											new Field( array(
												'input' => new CheckboxSet( array( 'name' => 'event_options', 'options' => $event_options ) )
											) )
										)
									) )
								)
							))
						),
						'saveMetaCb' => array( __CLASS__, 'save_event_details_meta_box' )
					) )
				),
				'listTableColumns' => array( $event_date_list_table_column )
			) );
			
			// $count = count( get_posts( array( 'post_type' => 'event_s', 'posts_per_page' => -1, 'fields' => 'ids', 'post_status' => 'pending' ) ) );
			$count = 0;
			self::$syndicated_event_post_type = new PostType( array(
				'name' => 'event_s',
				'singularLabel' => 'Syndicated Event',
				'pluralLabel' => 'Syndicated Events',
				'settings' => array(
					'public' => false,
					'show_in_menu' => 'edit.php?post_type=event',
					'show_ui' => true,
					'show_in_rest' => true,
					'labels' => array(
						'all_items' => 'Syndicated' . ( $count ? ' <span class="awaiting-mod">' . $count . '</span>' : '' )
					)
				),
				'listTableColumns' => array(
					$event_date_list_table_column,
					// new ListTableColumn( array(
					// 	'key' => 'event-site',
					// 	'title' => 'SBDC',
					// 	'position' => 3,
					// 	'outputCb' => function( $post_id, $args ) {
					// 		$site_id = get_post_meta( $post_id, '_original_site_id', true );
					// 		$site_details = get_blog_details( array( 'blog_id' => $site_id ) );
					// 		echo '<a href="' . $site_details->siteurl . '">' . $site_details->blogname . '</a>';
					// 	}
					// ) )
				)
			) );

		}


		public static function save_event_details_meta_box($post, $input, $args, $fields) {

			$date = isset( $input['event_date'])  ? $input['event_date'] : '';
			$start_time = isset( $input['event_start_time'] ) ? $input['event_start_time'] : '';
			$end_time = isset( $input['event_end_time'] ) ? $input['event_end_time'] : '';
			$timezone = isset( $input['event_timezone'] ) ? $input['event_timezone'] : '';

			$start_timestamp = strtotime( $date . ' ' . $start_time );
			$end_timestamp = strtotime( $date . ' ' . $end_time );

			if ( $start_timestamp === false && $end_timestamp !== false ) { // auto-set start date/time
				$start_timestamp = $end_timestamp - 3600;
				update_post_meta( $post->ID, 'event_date', date( 'Y-m-d', $start_timestamp ) );
				update_post_meta( $post->ID, 'event_start_date', date( 'Y-m-d', $start_timestamp ) );
				update_post_meta( $post->ID, 'event_start_time', date( 'H:i:s', $start_timestamp ) );
			}
			if ( $start_timestamp !== false && $end_timestamp === false ) { // auto-set end date/time
				$end_timestamp = $start_timestamp + 3600;
				update_post_meta( $post->ID, 'event_end_date', date( 'Y-m-d', $end_timestamp ) );
				update_post_meta( $post->ID, 'event_end_time', date( 'H:i:s', $end_timestamp ) );
			}

			update_post_meta( $post->ID, 'event_start_timestamp', $start_timestamp !== false ? date( 'Y-m-d H:i:s', $start_timestamp ) : '' );
			update_post_meta( $post->ID, 'event_end_timestamp', $end_timestamp !== false ? date( 'Y-m-d H:i:s', $end_timestamp ) : '' );

			$utc_start_timestamp = '';
			$utc_end_timestamp = '';
			if ( $start_timestamp !== false ) {
				$utc_start_datetime = ( new DateTime( date( 'Y-m-d H:i:s', $start_timestamp ), ! empty ( $timezone ) ? new DateTimeZone( $timezone ) : null ) )->setTimezone( new DateTimeZone( 'UTC' ) );
				$utc_start_timestamp = $utc_start_datetime->format( 'Y-m-d H:i:s' );
			}
			if ( $end_timestamp !== false ) {
				$utc_end_datetime = ( new DateTime( date( 'Y-m-d H:i:s', $end_timestamp ), ! empty ( $timezone ) ? new DateTimeZone( $timezone ) : null ) )->setTimezone( new DateTimeZone( 'UTC' ) );
				$utc_end_timestamp = $utc_end_datetime->format( 'Y-m-d H:i:s' );
			}
			update_post_meta( $post->ID, 'event_start_timestamp_utc', $utc_start_timestamp );
			update_post_meta( $post->ID, 'event_end_timestamp_utc', $utc_end_timestamp );

		}


		public static function register_event_series_taxonomy() {

			self::$event_series_taxonomy = new Taxonomy( array(
				'name' => 'event_series',
				'singularLabel' => 'Event Series',
				'pluralLabel' => 'Event Series',
				'postTypes' => array( 'event', 'event_s' ),
				'settings' => array(
					'hierarchical' => true,
					'rewrite' => array( 'slug' => 'event-series', 'with_front' => false ),
					'show_in_nav_menus' => false,
					'show_admin_column' => true,
					'publicly_queryable' => true,
					'show_in_rest' => true,
					'labels' => array(
						'menu_name' => 'Series',
						'all_items' => 'All Series'
					),
					'capabilities' => array(
						'manage_terms' => 'manage_event_series',
						'edit_terms' => 'edit_event_series',
						'delete_terms' => 'delete_event_series',
						'assign_terms' => 'assign_event_series'
					)
				)
			) );
		}


		public static function register_weekly_event_count_shortcode() {

			self::$weekly_event_count_shortcode = new Shortcode( array(
				'tag' => 'weekly_event_count',
				'defaultAtts' => array(
					'pre_p' => '',
					'pre_s' => '',
					'post_p' => 'events',
					'post_s' => 'event',
					'class' => ''
				),
				'getOutputCb' => function( $atts, $content ) {
					$classes = array( 'weekly-event-count-shortcode', $atts['class'] );
					$from = date( 'Y-m-d H:i:s', strtotime( 'Monday this week' ) );
					$to = date( 'Y-m-d H:i:s', strtotime( 'Monday next week' ) );
					$events = self::get_events( array( 'from' => $from, 'to' => $to, 'fields' => 'ids', 'include_syndicated' => true ) );
					$output = array( '<span class="count"><span class="value">' . count( $events ) . '</span></span>' );
					if ( count( $events ) == 1 ) {
						if ( ! empty( $atts['pre_s'] ) ) array_unshift( $output, $atts['pre_s'] );
						if ( ! empty( $atts['post_s'] ) ) array_push( $output, $atts['post_s'] );
					} else {
						if ( ! empty( $atts['pre_p'] ) ) array_unshift( $output, $atts['pre_p'] );
						if ( ! empty( $atts['post_p'] ) ) array_push( $output, $atts['post_p'] );
					}
					return '<span class="' . implode( ' ', $classes ) . '">' . implode( ' ', $output ) . '</span>';
				}
			) );

		}


		public static function update_post_center_terms( $post_id ) {

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;
			// if ( defined( 'DOING_CRON' ) && DOING_CRON ) return $post_id;

			$post = get_post( $post_id );
			if ( ! $post || $post->post_type != 'event' ) return $post_id;
			if ( $post->post_title == 'Auto Draft' ) return $post_id;

			if ( ! is_main_site() ) {

				$src_site = get_current_blog_id();
				switch_to_blog( get_main_site_id() );

				$center_terms = get_terms( array(
					'taxonomy' => 'post_center',
					'hide_empty' => false,
					'meta_query' => array(
						array( 'key' => 'center_site_id', 'value' => $src_site )
					)
				) );

				restore_current_blog();
				
				if ( ! empty( $center_terms ) ) {
					$term_ids = array();
					foreach ( $center_terms as $t ) {
						$term_id = 0;
						if ( ( $result = term_exists( $t->name, 'post_center' ) ) ) {
							$term_id = intval( $result['term_id'] );
						} else if ( ( $result = @wp_insert_term( $t->name, 'post_center', array() ) ) ) {
							if ( ! is_wp_error( $result ) ) {
								$term_id = intval( $result['term_id'] );
							}
						}
						if ( $term_id ) $term_ids[] = $term_id;
					}
					wp_set_object_terms( $post_id, $term_ids, 'post_center', false );
				}

			}

		}


		public static function update_shared_post_reference( $post_id ) {

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;
			// if ( defined( 'DOING_CRON' ) && DOING_CRON ) return $post_id;

			$post = get_post( $post_id );
			if ( ! $post || $post->post_type != 'event' ) return $post_id;
			if ( $post->post_title == 'Auto Draft' ) return $post_id;

			$options = get_post_meta( $post_id, '__event_options' );

			if ( ! in_array( 'do-not-post-to-regional-site', $options ) && $post->post_status == 'publish' && ! is_main_site() ) {
				self::syndicate_post( $post_id, get_main_site_id() );
			} else if ( ! is_main_site() ) {
				self::delete_syndicated_post( $post_id, get_main_site_id() );
			}

		}


		protected static function syndicate_post( $post_id, $dest_site ) {

			$post_arr = array(
				'post_title' => get_the_title( $post_id ),
				'post_date' => get_the_time( 'Y-m-d H:i:s', $post_id )
			);

			$taxonomies = array(
				'post_topic' => array(),
				'event_series' => array(),
				'post_center' => array()
			);
			foreach ( $taxonomies as $tax => $terms ) {
				$taxonomies[ $tax ] = wp_get_object_terms( $post_id, $tax );
				if ( is_wp_error( $taxonomies[ $tax ] ) ) {
					$taxonomies[ $tax ] = array();
				} else {
					$taxonomies[ $tax ] = array_map( function( $n ) {
						$branch = array( clone $n );
						while( $branch[0]->parent != 0 ) {
							array_unshift( $branch, get_term( $branch[0]->parent, $n->taxonomy ) );
						}
						$n->branch = $branch;
						return $n;
					}, $taxonomies[ $tax ] );
				}
			}

			$meta = array(
				'event_start_timestamp' => '',
				'event_end_timestamp' => '',
				'event_start_timestamp_utc' => '',
				'event_end_timestamp_utc' => ''
			);
			foreach ( $meta as $k => $v ) {
				$meta[ $k ] = get_post_meta( $post_id, $k, true );
			}

			$src_site = get_current_blog_id();
			switch_to_blog( $dest_site );
			
			$syn_id = get_posts( array(
				'post_type' => 'event_s',
				'posts_per_page' => 1,
				'fields' => 'ids',
				'post_status' => 'any',
				'meta_query' => array(
					array( 'key' => '_original_site_id', 'value' => $src_site ),
					array( 'key' => '_original_post_id', 'value' => $post_id )
				)
			) );
			$syn_id = ! empty( $syn_id ) ? $syn_id[0] : null;

			if ( ! $syn_id ) {
				// $syn_id = wp_insert_post( array( 'post_type' => 'event_s', 'post_status' => is_main_site() ? 'pending' : 'publish' ) );
				$syn_id = wp_insert_post( array( 'post_type' => 'event_s', 'post_status' => 'publish' ) );
			}

			wp_update_post( array_merge( $post_arr, array( 'ID' => $syn_id ) ) );

			foreach ( $taxonomies as $tax => $terms ) {
				$syn_term_ids = array();
				foreach ( $terms as $term ) {
					$syn_term_id = 0;
					foreach ( $term->branch as $t ) {
						$term_args = array( 'parent' => $syn_term_id );
						if ( ( $result = term_exists( $t->name, $tax, $syn_term_id ) ) ) {
							$syn_term_id = intval( $result['term_id'] );
						} else if ( ( $result = @wp_insert_term( $t->name, $tax, $term_args ) ) ) {
							if ( is_wp_error( $result ) ) break;
							$syn_term_id = intval( $result['term_id'] );
						}
					}
					if ( $syn_term_id ) $syn_term_ids[] = $syn_term_id;
				}
				wp_set_object_terms( $syn_id, $syn_term_ids, $tax, false );
			}

			foreach ( $meta as $k => $v ) {
				update_post_meta( $syn_id, $k, $v );
			}

			update_post_meta( $syn_id, '_original_site_id', $src_site );
			update_post_meta( $syn_id, '_original_post_id', $post_id );

			restore_current_blog();

		}


		public static function delete_syndicated_post( $post_id, $dest_site = 0 ) {
			if ( empty( $dest_site ) ) $dest_site = get_main_site_id();

			$src_site = get_current_blog_id();
			switch_to_blog( $dest_site );
				
			$syn_id = get_posts( array(
				'post_type' => 'event_s',
				'posts_per_page' => 1,
				'fields' => 'ids',
				'post_status' => 'any',
				'meta_query' => array(
					array( 'key' => '_original_site_id', 'value' => $src_site ),
					array( 'key' => '_original_post_id', 'value' => $post_id )
				)
			) );
			$syn_id = ! empty( $syn_id ) ? $syn_id[0] : null;

			if ( $syn_id ) {
				wp_delete_post( $syn_id, true );
			}
			
			restore_current_blog();

		}


		public static function filter_use_block_editor_for_post_type( $use_block_editor, $post_type ) {
			return in_array( $post_type, array( 'event' ) ) ? false : $use_block_editor;
		}


		public static function process_action_publish_syndicated_event() {
			
			$post_id = isset( $_GET['publish_event_s'] ) ? intval( $_GET['publish_event_s'] ) : 0;
			if ( ! $post_id ) return;
			if ( ! check_admin_referer( 'publish-event-s-' . $post_id ) ) return;

			wp_update_post( array( 'ID' => $post_id, 'post_status' => 'publish' ) );

			$sendback = admin_url( 'edit.php?post_type=event_s' );
			$sendback = add_query_arg( array( 'published_event_s' => $post_id ), $sendback );
			wp_redirect( $sendback );
			die;

		}


		public static function process_action_unpublish_syndicated_event() {
			
			$post_id = isset( $_GET['unpublish_event_s'] ) ? intval( $_GET['unpublish_event_s'] ) : 0;
			if ( ! $post_id ) return;
			if ( ! check_admin_referer( 'unpublish-event-s-' . $post_id ) ) return;

			wp_update_post( array( 'ID' => $post_id, 'post_status' => 'pending' ) );

			$sendback = admin_url( 'edit.php?post_type=event_s' );
			$sendback = add_query_arg( array( 'unpublished_event_s' => $post_id ), $sendback );
			wp_redirect( $sendback );
			die;

		}


		public static function output_syndicated_event_admin_notices() {
			$notices = array();

			$post_id = isset( $_GET['published_event_s'] ) ? intval( $_GET['published_event_s'] ) : 0;
			if ( $post_id ) {
				$notices[] = array( 'type' => 'success', 'message' => __( 'The syndicated event, "' . get_the_title( $post_id ) . '", has been published!', 'crown-events' ) );
			}

			$post_id = isset( $_GET['unpublished_event_s'] ) ? intval( $_GET['unpublished_event_s'] ) : 0;
			if ( $post_id ) {
				$notices[] = array( 'type' => 'success', 'message' => __( 'The syndicated event, "' . get_the_title( $post_id ) . '", has been unpublished and updated to "pending".', 'crown-events' ) );
			}

			foreach ( $notices as $notice ) {
				$notice = array_merge( array(
					'type' => 'info',
					'dismissible' => true,
					'message' => ''
				), $notice );
				?>
					<div class="notice notice-<?php echo $notice['type']; ?> <?php echo $notice['dismissable'] ? 'is-dismissible' : ''; ?>">
						<p><?php echo $notice['message']; ?></p>
					</div>
				<?php
			}

		}


		public static function filter_bulk_actions_edit_event_s( $actions ) {
			$actions = array();
			return $actions;
		}


		public static function filter_display_post_states( $post_states, $post ) {
			if ( $post->post_type == 'event' && in_array( 'featured-post', get_post_meta( $post->ID, '__event_options' ) ) ) {
				$post_states['post-featured'] = 'Featured';
			}
			if ( $post->post_type == 'event' && is_main_site() && in_array( 'post-to-center-sites', get_post_meta( $post->ID, '__event_options' ) ) ) {
				$post_states['post-syndicated'] = 'Syndicated';
			} else if ( $post->post_type == 'event' && ! is_main_site() && ! in_array( 'do-not-post-to-regional-site', get_post_meta( $post->ID, '__event_options' ) ) ) {
				$post_states['post-syndicated'] = 'Syndicated';
			}
			return $post_states;
		}


		public static function filter_post_row_actions( $actions, $post ) {
			if ( $post->post_type == 'event_s' ) {
				$actions = array();
				// if ( is_main_site() ) {
					if ( $post->post_status == 'publish' ) {
						$actions = array(
							'unpublish' => sprintf(
								'<a href="%s" class="submitunpublish" aria-label="%s">%s</a>',
								wp_nonce_url( add_query_arg( array( 'unpublish_event_s' => $post->ID ) ), 'unpublish-event-s-' . $post->ID ),
								esc_attr( sprintf( __( 'Unpublish &#8220;%s&#8221;' ), $post->post_title ) ),
								_x( 'Unpublish', 'verb' )
							)
						);
					} else {
						$actions = array(
							'publish' => sprintf(
								'<a href="%s" class="submitpublish" aria-label="%s">%s</a>',
								wp_nonce_url( add_query_arg( array( 'publish_event_s' => $post->ID ) ), 'publish-event-s-' . $post->ID ),
								esc_attr( sprintf( __( 'Publish &#8220;%s&#8221;' ), $post->post_title ) ),
								_x( 'Publish', 'verb' )
							)
						);
					}
				// }
			}
			return $actions;
		}
		

		public static function filter_get_edit_post_link( $link, $post_id, $context ) {
			if ( get_post_type( $post_id ) == 'event_s' ) {
				$original_post_id = get_post_meta( $post_id, '_original_post_id', true );
				switch_to_blog( get_post_meta( $post_id, '_original_site_id', true ) );
				$link = get_permalink( $original_post_id );
				restore_current_blog();
			}
			return $link;
		}


		public static function register_admin_styles( $hook ) {
			return;
			
			$screen = get_current_screen();
			if ( $screen->base == 'post' && $screen->post_type == 'event' ) {

				ob_start();
				?>
					<style>
						
					</style>
				<?php
				$css = trim( ob_get_clean() );
				$css = trim( preg_replace( array( '/^<style>/', '/<\/style>$/' ), '', $css ) );
				wp_add_inline_style( 'common', $css );

			} else if ( $screen->base == 'edit' && $screen->post_type == 'event' ) {

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


		public static function get_event_query_args( $args = array() ) {

			$args = array_merge( array(
				'from' => null,
				'to' => null,
				'count' => -1,
				'order' => 'ASC',
				'fields' => 'all',
				'tax_query' => array(),
				'meta_query' => array(),
				'include_syndicated' => false
			), $args );

			$query_args = array(
				'post_type' => array( 'event' ),
				'posts_per_page' => $args['count'],
				'orderby' => 'meta_value',
				'order' => $args['order'],
				'meta_key' => 'event_start_timestamp_utc',
				'fields' => $args['fields'],
				'tax_query' => $args['tax_query'],
			);
			$meta_query = $args['meta_query'];

			if ( ! empty( $args['from'] ) ) {
				$meta_query[] = array( 'key' => 'event_end_timestamp_utc', 'compare' => '>=', 'value' => $args['from'] );
			}
			if ( ! empty( $args['to'] ) ) {
				$meta_query[] = array( 'key' => 'event_start_timestamp_utc', 'compare' => '<=', 'value' => $args['to'] );
			}
			if ( $args['include_syndicated'] ) {
				$query_args['post_type'][] = 'event_s';
			}

			$query_args['meta_query'] = $meta_query;

			return $query_args;

		}


		public static function get_events( $args = array() ) {
			$query_args = self::get_event_query_args( $args );
			return get_posts( $query_args );
		}


		public static function get_upcoming_events( $count = -1, $tax_query = array(), $featured = false, $include_syndicated = false ) {
			$args = array(
				'from' => date( 'Y-m-d H:i:s' ),
				'count' => $count,
				'tax_query' => $tax_query,
				'include_syndicated' => $include_syndicated
			);
			if ( $featured ) $args['meta_query'] = array( array( 'key' => '__event_options', 'value' => 'featured-post' ) );
			return self::get_events( $args );
		}


		public static function get_past_events( $count = -1, $tax_query = array(), $featured = false, $include_syndicated = false ) {
			$args = array(
				'to' => date( 'Y-m-d H:i:s' ),
				'count' => $count,
				'order' => 'DESC',
				'tax_query' => $tax_query,
				'include_syndicated' => $include_syndicated
			);
			return self::get_events( $args );
		}


		public static function register_gf_addons() {

			if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
				return;
			}

			include_once( dirname( __FILE__ ) . '/class-crown-events-gravity-zwr.php' );
			GFAddOn::register( 'Crown_Events_Gravity_ZWR', 'GravityZWR' );

			// print_r(GFAddOn::get_registered_addons()); die;

		}


		public static function filter_gform_addon_feed_settings_fields( $fields, $feed ) {
			
			if ( $feed->get_slug() == 'gravity-forms-zoom-webinar-registration' ) {
				if ( isset( $fields[1]['fields'][0]['field_map'] ) ) {
					$fields[1]['fields'][0]['field_map'] = array_merge( array(
						'meeting_type' => array(
							'name' => 'meeting_type',
							'label' => 'Meeting Type',
							'required' => false,
							'field_type' => array( 'hidden', 'text', 'select', 'radio' ),
							'tooltip' => 'Dynamically override Zoom meeting type by passing in a field value.'
						),
						'meeting_id' => array(
							'name' => 'meeting_id',
							'label' => 'Meeting ID',
							'required' => false,
							'field_type' => array( 'hidden', 'text' ),
							'tooltip' => 'Dynamically override Zoom meeting ID by passing in a field value.'
						)
					), $fields[1]['fields'][0]['field_map'] );
				}
			}

			return $fields;
		}


		public static function filter_gform_addon_pre_process_feeds( $feeds, $entry, $form ) {

			foreach ( $feeds as $feed_index => $feed ) {
				if ( $feed['addon_slug'] == 'gravity-forms-zoom-webinar-registration' && isset( $feed['meta']['zoomWebinarID'] ) ) {

					$meeting_type = $feed['meta']['meetingtype'];
					$meeting_type_override_field_id = isset( $feed['meta']['mappedFields_meeting_type'] ) ? $feed['meta']['mappedFields_meeting_type'] : '';
					if ( ! empty( $meeting_type_override_field_id ) && isset( $entry[ $meeting_type_override_field_id ] ) && ! empty( $entry[ $meeting_type_override_field_id ] ) ) {
						$meeting_type = $entry[ $meeting_type_override_field_id ];
					}
					$feeds[ $feed_index ]['meta']['meetingtype'] = $meeting_type;

					$meeting_id = $feed['meta']['zoomWebinarID'];
					$meeting_id_override_field_id = isset( $feed['meta']['mappedFields_meeting_id'] ) ? $feed['meta']['mappedFields_meeting_id'] : '';
					if ( ! empty( $meeting_id_override_field_id ) && isset( $entry[ $meeting_id_override_field_id ] ) && ! empty( intval( $entry[ $meeting_id_override_field_id ] ) ) ) {
						$meeting_id = $entry[ $meeting_id_override_field_id ];
					}
					$feeds[ $feed_index ]['meta']['zoomWebinarID'] = $meeting_id;

				}
			}

			return $feeds;
		}


	}
}