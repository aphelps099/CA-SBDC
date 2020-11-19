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
use Crown\UIRule;


if ( ! class_exists( 'Crown_Events' ) ) {
	class Crown_Events {

		public static $init = false;

		public static $event_post_type = null;
		public static $event_topic_taxonomy = null;


		public static function init() {
			if( self::$init ) return;
			self::$init = true;

			$plugin_file = preg_replace( '/\/classes$/', '', dirname( __FILE__ ) ) . '/plugin.php';
			register_activation_hook( $plugin_file, array( __CLASS__, 'activate' ));
			register_deactivation_hook( $plugin_file, array( __CLASS__, 'detactivate' ));

			add_action( 'after_setup_theme', array( __CLASS__, 'register_event_post_type' ) );
			add_action( 'after_setup_theme', array( __CLASS__, 'register_event_topic_taxonomy' ) );

			// add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'filter_use_block_editor_for_post_type' ), 10, 2 );

			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_admin_styles' ) );

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
						$role->add_cap( $cap . '_event_topics' );
					}
				}
				if ( $role->has_cap( 'edit_posts' ) ) {
					$role->add_cap( 'assign_event_topics' );
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
					$role->remove_cap ( $cap . '_event_topics' );
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
					'supports' => array( 'title', 'editor', 'excerpt', 'revisions' ),
					'rewrite' => array( 'slug' => 'events', 'with_front' => false ),
					'menu_icon' => 'dashicons-calendar',
					'has_archive' => false,
					'publicly_queryable' => true,
					'show_in_rest' => true,
					'show_in_nav_menus' => false,
					'capability_type' => array( 'event', 'events' ),
					'map_meta_cap' => true,
					'menu_position' => 31
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
						'outputCb' => function( $postId, $args ) {
							$output = array();
							$start = strtotime( get_post_meta( $postId, 'event_start_timestamp', true ) );
							$end = strtotime( get_post_meta( $postId, 'event_end_timestamp', true ) );
							$tz = get_post_meta( $postId, 'event_timezone', true );
							if ( $start === false || $end === false ) return;
							$start = new DateTime( get_post_meta( $postId, 'event_start_timestamp', true ), ! empty( $tz ) ? new DateTimeZone( $tz ) : null );
							$end = new DateTime( get_post_meta( $postId, 'event_end_timestamp', true ), ! empty( $tz ) ? new DateTimeZone( $tz ) : null );
							$output[] = '<strong>' . $start->format( 'D, M j, Y' ) . '</strong>';
							$output[] = $start->format( 'g:ia' ) . ' - ' . $end->format( 'g:ia' ) . ' (' . $start->format( 'T' ) . ')';
							echo implode('<br>', $output);
						},
						'sortCb' => function($queryVars) {
							$queryVars['meta_key'] = 'event_start_timestamp_utc';
							$queryVars['orderby'] = 'meta_key';
							return $queryVars;
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


		public static function register_event_topic_taxonomy() {

			self::$event_topic_taxonomy = new Taxonomy( array(
				'name' => 'event_topic',
				'singularLabel' => 'Event Topic',
				'pluralLabel' => 'Event Topics',
				'postTypes' => array( 'event' ),
				'settings' => array(
					'hierarchical' => true,
					'rewrite' => array( 'slug' => 'event-topics', 'with_front' => false ),
					'show_in_nav_menus' => false,
					'show_admin_column' => true,
					'show_in_rest' => true,
					'labels' => array(
						'menu_name' => 'Topics',
						'all_items' => 'All Topics'
					),
					'capabilities' => array(
						'manage_terms' => 'manage_event_topics',
						'edit_terms' => 'edit_event_topics',
						'delete_terms' => 'delete_event_topics',
						'assign_terms' => 'assign_event_topics'
					)
				)
			) );

		}


		public static function filter_use_block_editor_for_post_type( $use_block_editor, $post_type ) {
			return in_array( $post_type, array( 'event' ) ) ? false : $use_block_editor;
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


	}
}