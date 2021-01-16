<?php

use Crown\AdminPage;
use Crown\Api\GoogleMaps;
use Crown\Form\Field;
use Crown\Form\FieldGroup;
use Crown\Form\FieldGroupSet;
use Crown\Form\FieldRepeater;
use Crown\Form\Input\CheckboxSet;
use Crown\Form\Input\Media as MediaInput;
use Crown\Form\Input\RadioSet;
use Crown\Form\Input\Select;
use Crown\Form\Input\Text as TextInput;
use Crown\Form\Input\Gallery as GalleryInput;
use Crown\Form\Input\GeoCoordinates as GeoCoordinatesInput;
use Crown\Form\Input\Textarea;
use Crown\Form\Input\RichTextarea;
use Crown\Post\MetaBox;
use Crown\Post\Type as PostType;
use Crown\UIRule;


if ( ! class_exists( 'Crown_Site_Settings_Theme_Configuration' ) ) {
	class Crown_Site_Settings_Theme_Configuration {

		public static $init = false;

		public static $theme_config_admin_page = null;
		public static $post_types = array();

		public static $post_type_input_options = array();
		public static $form_input_options = null;


		public static function init() {
			if( self::$init ) return;
			self::$init = true;

			add_action( 'after_setup_theme', array( __CLASS__, 'register_admin_pages' ) );
			// add_action( 'admin_menu', array( __CLASS__, 'add_reusable_blocks_menu_page' ), 50 );
			add_filter( 'display_post_states', array( __CLASS__, 'filter_display_post_states' ), 10, 2 );

			add_filter( 'crown_site_footer_copyright', array( __CLASS__, 'filter_crown_site_footer_copyright' ));
			add_filter( 'crown_site_footer_description', array( __CLASS__, 'filter_crown_site_footer_description' ));

			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_admin_styles' ) );

		}


		public static function register_admin_pages() {

			self::$theme_config_admin_page = new AdminPage( array(
				'key' => 'theme-config',
				'parent' => 'theme',
				'title' => 'Theme Configuration',
				'menuTitle' => 'Configuration',
				'fields' => array(
					new FieldGroupSet( array(
						'fieldGroups' => array(

							new FieldGroup( array(
								'label' => 'Site Footer',
								'fields' => array(
									new FieldGroup( array(
										'label' => 'Host Logos',
										'fields' => array(
											new FieldRepeater( array(
												'name' => 'theme_config_footer_host_logos',
												'addNewLabel' => 'Add Host Logo',
												'fields' => array(
													new FieldGroup( array(
														'class' => 'no-border two-column small-left',
														'fields' => array(
															new Field( array(
																'input' => new MediaInput( array( 'name' => 'image', 'mimeType' => 'image', 'buttonLabel' => 'Select Image', 'class' => 'logo' ) )
															) ),
															new Field( array(
																'input' => new TextInput( array( 'name' => 'link_url', 'placeholder' => 'https://', 'label' => 'Link URL' ) )
															) )
														)
													) )
												)
											) )
										)
									) ),
									new Field( array(
										'label' => 'Subscribe Form',
										'input' => new Select( array( 'name' => 'theme_config_footer_subscribe_form' ) ),
										'getOutputCb' => array( __CLASS__, 'set_form_select_input_options' )
									) ),
									new Field( array(
										'label' => 'Copyright Text',
										'description' => 'Use the <code>%%year%%</code> placeholder to display the current year.',
										'input' => new TextInput( array( 'name' => 'theme_config_footer_copyright' ) )
									) ),
									new Field( array(
										'label' => 'Footer Description',
										'input' => new Textarea( array( 'name' => 'theme_config_footer_description', 'rows' => 4 ) )
									) )
								)
							) ),

							new FieldGroup( array(
								'label' => 'Modals',
								'fields' => array(
									new FieldGroup( array(
										'label' => 'Subscribe',
										'fields' => array(
											new Field( array(
												'label' => 'Title',
												'input' => new TextInput( array( 'name' => 'theme_config_modal_subscribe_title', 'class' => 'input-large' ) )
											) ),
											new Field( array(
												'label' => 'Description',
												'input' => new TextInput( array( 'name' => 'theme_config_modal_subscribe_description' ) )
											) ),
											new Field( array(
												'label' => 'Form',
												'input' => new Select( array( 'name' => 'theme_config_modal_subscribe_form' ) ),
												'getOutputCb' => array( __CLASS__, 'set_form_select_input_options' )
											) )
										)
									) )
								)
							) ),

							new FieldGroup( array(
								'label' => 'Events',
								'fields' => array(
									new Field( array(
										'label' => 'Form',
										'input' => new Select( array( 'name' => 'theme_config_events_zoom_meeting_registration_form' ) ),
										'getOutputCb' => array( __CLASS__, 'set_form_select_input_options' )
									) )
								)
							) ),

							new FieldGroup( array(
								'label' => 'Index Pages',
								'fields' => array(
									new Field( array(
										'label' => 'Posts',
										'input' => new Select( array( 'name' => 'theme_config_index_page_post' ) ),
										'getOutputCb' => array( __CLASS__, 'set_page_select_input_options' )
									) ),
									new Field( array(
										'label' => 'Events',
										'input' => new Select( array( 'name' => 'theme_config_index_page_event' ) ),
										'getOutputCb' => array( __CLASS__, 'set_page_select_input_options' )
									) ),
									new Field( array(
										'label' => 'Resources',
										'input' => new Select( array( 'name' => 'theme_config_index_page_resource' ) ),
										'getOutputCb' => array( __CLASS__, 'set_page_select_input_options' )
									) ),
									new Field( array(
										'label' => 'Jobs',
										'input' => new Select( array( 'name' => 'theme_config_index_page_job' ) ),
										'getOutputCb' => array( __CLASS__, 'set_page_select_input_options' )
									) ),
									new Field( array(
										'label' => 'Team Members',
										'input' => new Select( array( 'name' => 'theme_config_index_page_team_member' ) ),
										'getOutputCb' => array( __CLASS__, 'set_page_select_input_options' )
									) ),
									new Field( array(
										'label' => 'Client Stories',
										'input' => new Select( array( 'name' => 'theme_config_index_page_client_story' ) ),
										'getOutputCb' => array( __CLASS__, 'set_page_select_input_options' )
									) ),
									new Field( array(
										'label' => 'FAQs',
										'input' => new Select( array( 'name' => 'theme_config_index_page_faq' ) ),
										'getOutputCb' => array( __CLASS__, 'set_page_select_input_options' )
									) ),
									new Field( array(
										'label' => 'SBDCs',
										'input' => new Select( array( 'name' => 'theme_config_index_page_center' ) ),
										'getOutputCb' => array( __CLASS__, 'set_page_select_input_options' )
									) )
								)
							) )

						)
					) )
				)
			) );

		}


		public static function set_block_id_select_field_options( $field, $args ) {
			$options = array( array( 'value' => '', 'label' => '&mdash;' ) );
			$posts = get_posts( array(
				'post_type' => 'wp_block',
				'posts_per_page' => -1,
				'orderby' => 'title',
				'order' => 'ASC',
				'post_status' => 'any'
			) );
			if ( ! empty( $posts ) ) {
				$options = array_merge( $options, array_map( function( $n ) {
					return array( 'value' => $n->ID, 'label' => $n->post_title );
				}, $posts ) );
				$field->getInput()->setOptions( $options );
			}
		}


		public static function set_page_select_input_options( $field, $args ) {
			$field->getInput()->setOptions( array_merge( array( array( 'label' => '&mdash;' ) ), self::get_post_input_options( 'page' ) ) );
		}
		private static function get_post_input_options( $post_type = 'post', $order_by = 'title', $order = 'ASC', $parent = 0, $level = 0 ) {
			if ( $parent == 0 && array_key_exists( $post_type, self::$post_type_input_options ) ) return self::$post_type_input_options[ $post_type ];
			$options = array();
			$posts = get_posts( array( 'post_type' => $post_type, 'posts_per_page' => -1, 'orderby' => $order_by, 'order' => $order, 'post_parent' => $parent ) );
			foreach ( $posts as $post ) {
				$options[] = array( 'value' => $post->ID, 'label' => $post->post_title, 'depth' => $level );
				$options = array_merge( $options, self::get_post_input_options( $post_type, $order_by, $order, $post->ID, $level + 1 ) );
			}
			if ( $parent == 0 ) self::$post_type_input_options[ $post_type ] = $options;
			return $options;
		}


		public static function set_form_select_input_options( $field, $args ) {
			$field->getInput()->setOptions( array_merge( array( array( 'label' => '&mdash;' ) ), self::get_form_input_options() ) );
		}
		private static function get_form_input_options() {
			if ( empty( self::$form_input_options ) ) {
				self::$form_input_options = array();
				if ( class_exists('RGFormsModel' ) ) {
					$forms = RGFormsModel::get_forms();
					foreach ( $forms as $form ) {
						self::$form_input_options[] = array('value' => $form->id, 'label' => $form->title);
					}
				}
			}
			return self::$form_input_options;
		}


		public static function add_reusable_blocks_menu_page() {
			add_menu_page( 'Reusable Blocks', 'Reusable Blocks', 'edit_posts', 'edit.php?post_type=wp_block', '', 'dashicons-editor-table', 22 );
		}


		public static function filter_display_post_states( $post_states, $post ) {
			// if ( $post->post_type == 'wp_block' && $post->ID == get_option( 'theme_config_category_tpl_cta_block_id' ) ) {
			// 	$post_states['category-tpl-cta'] = 'Category Template CTA';
			// }
			return $post_states;
		}


		public static function filter_crown_site_footer_copyright( $copyright = '' ) {
			$copyright = get_option( 'theme_config_footer_copyright' );
			$copyright = str_replace( '%%year%%', current_time( 'Y' ), $copyright );
			return $copyright;
		}


		public static function filter_crown_site_footer_description( $description = '' ) {
			$description = get_option( 'theme_config_footer_description' );
			return $description;
		}


		public static function register_admin_styles( $hook ) {
			
			$screen = get_current_screen();
			if ( $screen->id == 'appearance_page_theme-config' ) {

				ob_start();
				?>
					<style>
						.crown-media-input.logo .media-input-preview img { width: 100%; height: 100px; object-fit: contain; object-position: left center; }
					</style>
				<?php
				$css = trim( ob_get_clean() );
				$css = trim( preg_replace( array( '/^<style>/', '/<\/style>$/' ), '', $css ) );
				wp_add_inline_style( 'common', $css );

			}

		}


	}
}