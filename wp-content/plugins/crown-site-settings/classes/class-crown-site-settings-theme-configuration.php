<?php

use Crown\AdminPage;
use Crown\Form\Field;
use Crown\Form\FieldGroup;
use Crown\Form\FieldGroupSet;
use Crown\Form\Input\CheckboxSet;
use Crown\Form\Input\Media as MediaInput;
use Crown\Form\Input\RadioSet;
use Crown\Form\Input\Select;
use Crown\Form\Input\Text as TextInput;
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

		public static $form_input_options = null;


		public static function init() {
			if( self::$init ) return;
			self::$init = true;

			add_action( 'after_setup_theme', array( __CLASS__, 'register_admin_pages' ) );
			// add_action( 'admin_menu', array( __CLASS__, 'add_reusable_blocks_menu_page' ), 50 );
			add_filter( 'display_post_states', array( __CLASS__, 'filter_display_post_states' ), 10, 2 );

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
									new Field( array(
										'label' => 'Copyright Text',
										'input' => new TextInput( array( 'name' => 'theme_config_footer_copyright' ) )
									) )
								)
							) ),

							new FieldGroup( array(
								'label' => 'Social Media Links',
								'fields' => array(
									new Field( array(
										'label' => 'Facebook Page URL',
										'input' => new TextInput( array( 'name' => 'theme_config_facebook_profile_url', 'class' => 'input-medium', 'placeholder' => 'https://' ) )
									) ),
									new Field( array(
										'label' => 'Twitter Profile URL',
										'input' => new TextInput( array( 'name' => 'theme_config_twitter_profile_url', 'class' => 'input-medium', 'placeholder' => 'https://' ) )
									) ),
									new Field( array(
										'label' => 'Google+ Profile URL',
										'input' => new TextInput( array( 'name' => 'theme_config_google_plus_profile_url', 'class' => 'input-medium', 'placeholder' => 'https://' ) )
									) ),
									new Field( array(
										'label' => 'LinkedIn Business Page URL',
										'input' => new TextInput( array( 'name' => 'theme_config_linkedin_profile_url', 'class' => 'input-medium', 'placeholder' => 'https://' ) )
									) ),
									new Field( array(
										'label' => 'Instagram Profile URL',
										'input' => new TextInput( array( 'name' => 'theme_config_instagram_profile_url', 'class' => 'input-medium', 'placeholder' => 'https://' ) )
									) ),
									new Field( array(
										'label' => 'YouTube Channel URL',
										'input' => new TextInput( array( 'name' => 'theme_config_youtube_profile_url', 'class' => 'input-medium', 'placeholder' => 'https://' ) )
									) ),
									new Field( array(
										'label' => 'Pinterest Page URL',
										'input' => new TextInput( array( 'name' => 'theme_config_pinterest_profile_url', 'class' => 'input-medium', 'placeholder' => 'https://' ) )
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


		public static function set_form_select_input_options( $field, $args ) {
			$field->getInput()->setOptions( array_merge( array( array( 'label' => '&mdash;' ) ), self::getFormInputOptions() ) );
		}
		private static function getFormInputOptions() {
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


	}
}