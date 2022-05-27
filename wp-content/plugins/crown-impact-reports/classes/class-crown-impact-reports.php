<?php

use Crown\AdminPage;
use Crown\Form\Field;
use Crown\Form\FieldGroup;
use Crown\Form\FieldGroupSet;
use Crown\Form\FieldRepeater;
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
use Crown\Shortcode;
use Crown\UIRule;


if ( ! class_exists( 'Crown_Impact_Reports' ) ) {
	class Crown_Impact_Reports {

		public static $init = false;

		public static $impact_report_post_type = null;
		public static $ir_rep_type_taxonomy = null;
		public static $ir_region_taxonomy = null;
		public static $ir_district_no_taxonomy = null;


		public static function init() {
			if( self::$init ) return;
			self::$init = true;

			$plugin_file = preg_replace( '/\/classes$/', '', dirname( __FILE__ ) ) . '/plugin.php';
			register_activation_hook( $plugin_file, array( __CLASS__, 'activate' ));
			register_deactivation_hook( $plugin_file, array( __CLASS__, 'deactivate' ));

			add_action( 'after_setup_theme', array( __CLASS__, 'register_impact_report_post_type' ) );
			add_action( 'after_setup_theme', array( __CLASS__, 'register_ir_rep_type_taxonomy' ) );
			add_action( 'after_setup_theme', array( __CLASS__, 'register_ir_region_taxonomy' ) );
			add_action( 'after_setup_theme', array( __CLASS__, 'register_ir_district_no_taxonomy' ) );

			add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'filter_use_block_editor_for_post_type' ), 10, 2 );

			add_action( 'template_redirect', array( __CLASS__, 'redirect_singular' ) );
			add_filter( 'post_type_link', array(  __CLASS__, 'filter_post_type_link' ), 10, 4 );

		}


		public static function activate() {
			global $wp_roles;
			
			foreach ( $wp_roles->role_objects as $role ) {
				foreach ( array( 'publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private' ) as $cap ) {
					if ( $role->has_cap( $cap . '_posts' ) ) {
						$role->add_cap( $cap . '_impact_reports' );
					}
				}
				foreach ( array( 'manage', 'edit', 'delete' ) as $cap ) {
					if ( $role->has_cap( 'manage_categories' ) ) {
						$role->add_cap( $cap . '_ir_rep_types' );
						$role->add_cap( $cap . '_ir_regions' );
						$role->add_cap( $cap . '_ir_district_nos' );
					}
				}
				if ( $role->has_cap( 'edit_posts' ) ) {
					$role->add_cap( 'assign_ir_rep_types' );
					$role->add_cap( 'assign_ir_regions' );
					$role->add_cap( 'assign_ir_district_nos' );
				}
			}

			flush_rewrite_rules();
		}


		public static function deactivate() {
			global $wp_roles;
			
			foreach ( $wp_roles->role_objects as $role ) {
				foreach ( array( 'publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private' ) as $cap ) {
					$role->remove_cap( $cap . '_impact_reports' );
				}
				foreach ( array( 'manage', 'edit', 'delete', 'assign' ) as $cap ) {
					$role->remove_cap ( $cap . '_ir_rep_types' );
					$role->remove_cap ( $cap . '_ir_regions' );
					$role->remove_cap ( $cap . '_ir_district_nos' );
				}
			}
			
			flush_rewrite_rules();
		}


		public static function register_impact_report_post_type() {

			self::$impact_report_post_type = new PostType( array(
				'name' => 'impact_report',
				'singularLabel' => 'Impact Report',
				'pluralLabel' => 'Impact Reports',
				'settings' => array(
					'supports' => array( 'title', 'revisions' ),
					'rewrite' => array( 'slug' => 'impact-reports', 'with_front' => false ),
					'menu_icon' => 'dashicons-media-document',
					'has_archive' => false,
					'publicly_queryable' => true,
					'show_in_rest' => true,
					'show_in_nav_menus' => true,
					'capability_type' => array( 'impact_report', 'impact_reports' ),
					'map_meta_cap' => true,
					'menu_position' => 36
				),
				'metaBoxes' => array(
					new MetaBox( array(
						'id' => 'report-settings',
						'title' => 'Report Settings',
						'priority' => 'high',
						'fields' => array(
							new FieldGroupSet( array(
								'fieldGroups' => array(
									new FieldGroup( array(
										'label' => 'General',
										'fields' => array(
											new Field( array(
												'label' => 'File',
												'input' => new MediaInput( array( 'name' => 'impact_report_file' ) )
											) ),
											new Field( array(
												'label' => 'Link Override',
												'input' => new TextInput( array( 'name' => 'impact_report_link', 'placeholder' => 'https://' ) )
											) ),
											new Field( array(
												'label' => 'Link Label',
												'input' => new TextInput( array( 'name' => 'impact_report_link_label', 'placeholder' => 'Download', 'class' => 'input-medium' ) )
											) )
										)
									) )
								)
							) )
						)
					) )
				)
			) );

		}


		public static function register_ir_rep_type_taxonomy() {

			self::$ir_rep_type_taxonomy = new Taxonomy( array(
				'name' => 'ir_rep_type',
				'singularLabel' => 'Impact Report Rep Type',
				'pluralLabel' => 'Impact Report Rep Types',
				'postTypes' => array( 'impact_report' ),
				'settings' => array(
					'hierarchical' => true,
					'rewrite' => array( 'slug' => 'ir-rep-types', 'with_front' => false ),
					'show_in_nav_menus' => false,
					'show_admin_column' => true,
					'publicly_queryable' => false,
					'show_in_rest' => true,
					'labels' => array(
						'menu_name' => 'Rep Types',
						'all_items' => 'All Rep Types'
					),
					'capabilities' => array(
						'manage_terms' => 'manage_ir_rep_types',
						'edit_terms' => 'edit_ir_rep_types',
						'delete_terms' => 'delete_ir_rep_types',
						'assign_terms' => 'assign_ir_rep_types'
					)
				)
			) );

		}


		public static function register_ir_region_taxonomy() {

			self::$ir_region_taxonomy = new Taxonomy( array(
				'name' => 'ir_region',
				'singularLabel' => 'Impact Report Region',
				'pluralLabel' => 'Impact Report Regions',
				'postTypes' => array( 'impact_report' ),
				'settings' => array(
					'hierarchical' => true,
					'rewrite' => array( 'slug' => 'ir-regions', 'with_front' => false ),
					'show_in_nav_menus' => false,
					'show_admin_column' => true,
					'publicly_queryable' => false,
					'show_in_rest' => true,
					'labels' => array(
						'menu_name' => 'Regions',
						'all_items' => 'All Regions'
					),
					'capabilities' => array(
						'manage_terms' => 'manage_ir_regions',
						'edit_terms' => 'edit_ir_regions',
						'delete_terms' => 'delete_ir_regions',
						'assign_terms' => 'assign_ir_regions'
					)
				)
			) );

		}


		public static function register_ir_district_no_taxonomy() {

			self::$ir_district_no_taxonomy = new Taxonomy( array(
				'name' => 'ir_district_no',
				'singularLabel' => 'Impact Report District Number',
				'pluralLabel' => 'Impact Report District Numbers',
				'postTypes' => array( 'impact_report' ),
				'settings' => array(
					'hierarchical' => true,
					'rewrite' => array( 'slug' => 'ir-district-numbers', 'with_front' => false ),
					'show_in_nav_menus' => false,
					'show_admin_column' => true,
					'publicly_queryable' => false,
					'show_in_rest' => true,
					'labels' => array(
						'menu_name' => 'Disctrict Numbers',
						'all_items' => 'All Disctrict Numbers'
					),
					'capabilities' => array(
						'manage_terms' => 'manage_ir_district_nos',
						'edit_terms' => 'edit_ir_district_nos',
						'delete_terms' => 'delete_ir_district_nos',
						'assign_terms' => 'assign_ir_district_nos'
					)
				)
			) );

		}


		public static function filter_use_block_editor_for_post_type( $use_block_editor, $post_type ) {
			return in_array( $post_type, array( 'impact_report' ) ) ? false : $use_block_editor;
		}


		public static function redirect_singular() {
			$redirect = null;
			if ( is_singular( 'impact_report' ) ) {
				$link = get_post_meta( get_the_ID(), 'impact_report_link', true );
				if ( ! empty( $link ) ) {
					$redirect = $link;
				} else {
					$link = wp_get_attachment_url( get_post_meta( get_the_ID(), 'impact_report_file', true ) );
					if ( ! empty( $link ) ) $redirect = $link;
				}
			}
			if ( ! empty( $redirect ) ) {
				wp_redirect( $redirect );
				exit;
			}
		}


		public static function filter_post_type_link( $permalink, $post, $leavename, $sample ) {
			if ( $post->post_type == 'impact_report' ) {
				$link = get_post_meta( $post->ID, 'impact_report_link', true );
				if ( ! empty( $link ) ) return $link;
				$link = wp_get_attachment_url( get_post_meta( $post->ID, 'impact_report_file', true ) );
				if ( ! empty( $link ) ) return $link;
			}
			return $permalink;
		}


	}
}