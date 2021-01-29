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
		public static $event_series_taxonomy = null;

		public static $weekly_event_count_shortcode = null;


		public static function init() {
			if( self::$init ) return;
			self::$init = true;

			$plugin_file = preg_replace( '/\/classes$/', '', dirname( __FILE__ ) ) . '/plugin.php';
			register_activation_hook( $plugin_file, array( __CLASS__, 'activate' ));
			register_deactivation_hook( $plugin_file, array( __CLASS__, 'deactivate' ));

			add_action( 'after_setup_theme', array( __CLASS__, 'register_event_post_type' ) );
			add_action( 'after_setup_theme', array( __CLASS__, 'register_event_series_taxonomy' ) );
			add_action( 'after_setup_theme', array( __CLASS__, 'register_weekly_event_count_shortcode' ) );

			// add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'filter_use_block_editor_for_post_type' ), 10, 2 );

			add_filter( 'display_post_states', array( __CLASS__, 'filter_display_post_states'), 10, 2 );

			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_admin_styles' ) );

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
										'backgroundColor' => '#E0E0E0',
										'backgroundColorSlug' => 'ghost',
										'className' => 'bg-flush-right position-lg-sticky'
									), array(
										array( 'core/heading', array(
											'level' => 3,
											'placeholder' => 'Register for this Event'
										) )
									) )
								) )
							) )
						) )
					)
				),
				'metaBoxes' => array(
					new MetaBox( array(
						'id' => 'event-date',
						'title' => 'Event Date',
						'context' => 'side',
						'priority' => 'high',
						'fields' => array(
							new Field( array(
								'label' => 'Date',
								'input' => new DateInput( array( 'name' => 'event_date' ) )
							) ),
							new Field( array(
								'label' => 'Start Time',
								'input' => new TimeInput( array( 'name' => 'event_start_time', 'defaultValue' => '12:00:00' ) )
							) ),
							new Field( array(
								'label' => 'End Time',
								'input' => new TimeInput( array( 'name' => 'event_end_time', 'defaultValue' => '13:00:00' ) )
							) ),
							new Field( array(
								'label' => 'Timezone',
								'input' => new Select( array( 'name' => 'event_timezone', 'defaultValue' => 'America/Los_Angeles', 'options' => $timezone_options ) )
							) )
						),
						'saveMetaCb' => array( __CLASS__, 'save_event_date_meta_box' )
					) ),
					new MetaBox( array(
						'id' => 'event-options',
						'title' => 'Event Options',
						'context' => 'side',
						'fields' => array(
							new Field( array(
								'input' => new CheckboxSet( array( 'name' => 'event_options', 'options' => array(
									array( 'value' => 'featured-post', 'label' => 'Featured Event' )
								) ) )
							) )
						)
					) )
				),
				'listTableColumns' => array(
					new ListTableColumn( array(
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
							$query_vars['orderby'] = 'meta_key';
							return $query_vars;
						}
					) )
				)
			) );

		}


		public static function save_event_date_meta_box($post, $input, $args, $fields) {

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
				'postTypes' => array( 'event' ),
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
					$events = self::get_events( array( 'from' => $from, 'to' => $to, 'fields' => 'ids' ) );
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


		public static function filter_use_block_editor_for_post_type( $use_block_editor, $post_type ) {
			return in_array( $post_type, array( 'event' ) ) ? false : $use_block_editor;
		}


		public static function filter_display_post_states( $post_states, $post ) {
			if( $post->post_type == 'event' && in_array( 'featured-post', get_post_meta( $post->ID, '__event_options' ) ) ) {
				$post_states['post-featured'] = 'Featured';
			}
			return $post_states;
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
				'meta_query' => array()
			), $args );

			$query_args = array(
				'post_type' => 'event',
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

			$query_args['meta_query'] = $meta_query;

			return $query_args;

		}


		public static function get_events( $args = array() ) {
			return get_posts( self::get_event_query_args( $args ) );
		}


		public static function get_upcoming_events( $count = -1, $tax_query = array(), $featured = false ) {
			$args = array(
				'from' => date( 'Y-m-d H:i:s' ),
				'count' => $count,
				'tax_query' => $tax_query
			);
			if ( $featured ) $args['meta_query'] = array( array( 'key' => '__event_options', 'value' => 'featured-post' ) );
			return self::get_events( $args );
		}


		public static function get_past_events( $count = -1, $tax_query = array(), $featured = false ) {
			$args = array(
				'to' => date( 'Y-m-d H:i:s' ),
				'count' => $count,
				'order' => 'DESC',
				'tax_query' => $tax_query
			);
			return self::get_events( $args );
		}


		public static function filter_gform_addon_feed_settings_fields( $fields, $feed ) {
			
			if ( $feed->get_slug() == 'gravity-forms-zoom-webinar-registration' ) {
				if ( isset( $fields[1]['fields'][0]['field_map'] ) ) {
					$fields[1]['fields'][0]['field_map'] = array_merge( array(
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
					$meeting_id = $feed['meta']['zoomWebinarID'];
					$meeting_id_override_field_id = isset( $feed['meta']['mappedFields_meeting_id'] ) ? $feed['meta']['mappedFields_meeting_id'] : '';
					if ( ! empty( $meeting_id_override_field_id ) && isset( $entry[ $meeting_id_override_field_id ] ) ) {
						$meeting_id = $entry[ $meeting_id_override_field_id ];
					}
					$feeds[ $feed_index ]['meta']['zoomWebinarID'] = $meeting_id;
				}
			}

			return $feeds;
		}


	}
}