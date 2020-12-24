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


if ( ! class_exists( 'Crown_Team_Members' ) ) {
	class Crown_Team_Members {

		public static $init = false;

		public static $team_member_post_type = null;
		public static $team_member_category_taxonomy = null;
		public static $team_member_expertise_taxonomy = null;


		public static function init() {
			if( self::$init ) return;
			self::$init = true;

			$plugin_file = preg_replace( '/\/classes$/', '', dirname( __FILE__ ) ) . '/plugin.php';
			register_activation_hook( $plugin_file, array( __CLASS__, 'activate' ));
			register_deactivation_hook( $plugin_file, array( __CLASS__, 'deactivate' ));

			add_action( 'after_setup_theme', array( __CLASS__, 'register_team_member_post_type' ) );
			add_action( 'after_setup_theme', array( __CLASS__, 'register_team_member_category_taxonomy' ) );
			add_action( 'after_setup_theme', array( __CLASS__, 'register_team_member_expertise_taxonomy' ) );

			// add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'filter_use_block_editor_for_post_type' ), 10, 2 );

			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_admin_styles' ) );

		}


		public static function activate() {
			global $wp_roles;
			
			foreach ( $wp_roles->role_objects as $role ) {
				foreach ( array( 'publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private' ) as $cap ) {
					if ( $role->has_cap( $cap . '_posts' ) ) {
						$role->add_cap( $cap . '_team_members' );
					}
				}
				foreach ( array( 'manage', 'edit', 'delete' ) as $cap ) {
					if ( $role->has_cap( 'manage_categories' ) ) {
						$role->add_cap( $cap . '_team_member_categories' );
						$role->add_cap( $cap . '_team_member_expertise' );
					}
				}
				if ( $role->has_cap( 'edit_posts' ) ) {
					$role->add_cap( 'assign_team_member_categories' );
					$role->add_cap( 'assign_team_member_expertise' );
				}
			}

			flush_rewrite_rules();
		}


		public static function deactivate() {
			global $wp_roles;
			
			foreach ( $wp_roles->role_objects as $role ) {
				foreach ( array( 'publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private' ) as $cap ) {
					$role->remove_cap( $cap . '_team_members' );
				}
				foreach ( array( 'manage', 'edit', 'delete', 'assign' ) as $cap ) {
					$role->remove_cap ( $cap . '_team_member_categories' );
					$role->remove_cap ( $cap . '_team_member_expertise' );
				}
			}
			
			flush_rewrite_rules();
		}


		public static function register_team_member_post_type() {

			self::$team_member_post_type = new PostType( array(
				'name' => 'team_member',
				'singularLabel' => 'Team Member',
				'pluralLabel' => 'Team Members',
				'settings' => array(
					'supports' => array( 'revisions' ),
					'rewrite' => array( 'slug' => 'team-members', 'with_front' => false ),
					'menu_icon' => 'dashicons-groups',
					'has_archive' => false,
					'publicly_queryable' => true,
					'show_in_rest' => true,
					'show_in_nav_menus' => false,
					'capability_type' => array( 'team_member', 'team_members' ),
					'map_meta_cap' => true,
					'menu_position' => 34
				),
				'metaBoxes' => array(
					new MetaBox( array(
						'id' => 'team-member-details',
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
												'saveMetaCb' => array( __CLASS__, 'save_team_member_name' ),
												'fields' => array(
													new Field( array(
														'label' => 'Name',
														'input' => new TextInput( array( 'name' => 'team_member_first_name', 'label' => 'First Name', 'class' => 'input-large' ) )
													) ),
													new Field( array(
														'label' => '&nbsp;',
														'input' => new TextInput( array( 'name' => 'team_member_middle_name', 'label' => 'Middle Name (optional)', 'class' => 'input-large' ) )
													) ),
													new Field( array(
														'label' => '&nbsp;',
														'input' => new TextInput( array( 'name' => 'team_member_last_name', 'label' => 'Last Name', 'class' => 'input-large' ) )
													) )
												)
											) ),
											new Field( array(
												'label' => 'Job Title',
												'input' => new TextInput( array( 'name' => 'team_member_job_title', 'class' => 'input-medium' ) )
											) ),
											new Field( array(
												'label' => 'LinkedIn Profile URL',
												'input' => new TextInput( array( 'name' => 'team_member_linkedin', 'placeholder' => 'https://' ) )
											) ),
											new Field( array(
												'input' => new CheckboxSet( array( 'name' => 'team_member_options', 'options' => array(
													array( 'value' => 'multilingual', 'label' => 'Multilingual' )
												) ) )
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
														'input' => new MediaInput( array( 'name' => 'team_member_headshot_photo', 'buttonLabel' => 'Select Image', 'mimeType' => 'image' ) )
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
						'id' => 'team-member-bio',
						'title' => 'Bio',
						'priority' => 'high',
						'fields' => array(
							new Field( array(
								'input' => new RichTextarea( array( 'name' => 'team_member_bio', 'rows' => 16 ) )
							) )
						)
					) )
				),
				'listTableColumns' => array(
					new ListTableColumn( array(
						'key' => 'team-member-image',
						'title' => '',
						'position' => 1,
						'outputCb' => function( $post_id, $args ) {
							$image_id = get_post_meta( $post_id, 'team_member_headshot_photo', true );
							$image_url = wp_get_attachment_image_url( $image_id, 'thumbnail' );
							if ( $image_url ) echo '<a href="' . admin_url( 'post.php?post=' . $post_id . '&action=edit' ) . '"><img src="' . $image_url . '"></a>';
						}
					) ),
					new ListTableColumn( array(
						'key' => 'team-member-job-title',
						'title' => 'Job Title',
						'position' => 3,
						'outputCb' => function( $post_id, $args ) {
							$job_title = get_post_meta( $post_id, 'team_member_job_title', true );
							echo $job_title;
						}
					) )
				)
			) );

		}


		public static function save_team_member_name($field, $input, $type, $object_id, $value) {
			$post_data = array(
				'ID' => $object_id,
				'post_title' => array(),
				'post_name' => ''
			);
			$first = array();
			if ( isset( $input['team_member_first_name'] ) && ! empty( $input['team_member_first_name'] ) ) $first[] = $input['team_member_first_name'];
			if ( isset( $input['team_member_middle_name'] ) && ! empty( $input['team_member_middle_name'] ) ) $first[] = $input['team_member_middle_name'];
			if ( ! empty( $first ) ) $post_data['post_title'][] = implode( ' ', $first );
			if ( isset( $input['team_member_last_name'] ) && ! empty( $input['team_member_last_name'] ) ) $post_data['post_title'][] = $input['team_member_last_name'];
			update_post_meta( $object_id, 'team_member_name_first_comma_last', implode( ', ', $post_data['post_title'] ) );
			update_post_meta( $object_id, 'team_member_name_first_comma_last_lc', strtolower( implode( ', ', $post_data['post_title'] ) ) );
			update_post_meta( $object_id, 'team_member_name_last_comma_first', implode( ', ', array_reverse( $post_data['post_title'] ) ) );
			update_post_meta( $object_id, 'team_member_name_last_comma_first_lc', strtolower( implode( ', ', array_reverse( $post_data['post_title'] ) ) ) );
			update_post_meta( $object_id, 'team_member_name_last_initial_lc', substr( strtolower( implode( ', ', array_reverse( $post_data['post_title'] ) ) ), 0, 1 ) );
			update_post_meta( $object_id, 'team_member_name_first_initial_lc', substr( strtolower( implode( ', ', $post_data['post_title'] ) ), 0, 1 ) );
			$post_data['post_title'] = implode( ' ', $post_data['post_title'] );
			$post_data['post_name'] = sanitize_title( $post_data['post_title'] );
			wp_update_post( $post_data );
		}


		public static function register_team_member_category_taxonomy() {

			self::$team_member_category_taxonomy = new Taxonomy( array(
				'name' => 'team_member_category',
				'singularLabel' => 'Team Member Category',
				'pluralLabel' => 'Team Member Categories',
				'postTypes' => array( 'team_member' ),
				'settings' => array(
					'hierarchical' => true,
					'rewrite' => array( 'slug' => 'team-member-categories', 'with_front' => false ),
					'show_in_nav_menus' => false,
					'show_admin_column' => true,
					'publicly_queryable' => false,
					'show_in_rest' => true,
					'labels' => array(
						'menu_name' => 'Categories',
						'all_items' => 'All Categories'
					),
					'capabilities' => array(
						'manage_terms' => 'manage_team_member_categories',
						'edit_terms' => 'edit_team_member_categories',
						'delete_terms' => 'delete_team_member_categories',
						'assign_terms' => 'assign_team_member_categories'
					)
				)
			) );

		}


		public static function register_team_member_expertise_taxonomy() {

			self::$team_member_expertise_taxonomy = new Taxonomy( array(
				'name' => 'team_member_expertise',
				'singularLabel' => 'Team Member Expertise',
				'pluralLabel' => 'Team Member Expertise',
				'postTypes' => array( 'team_member' ),
				'settings' => array(
					'hierarchical' => true,
					'rewrite' => array( 'slug' => 'team-member-expertise', 'with_front' => false ),
					'show_in_nav_menus' => false,
					'show_admin_column' => true,
					'publicly_queryable' => false,
					'show_in_rest' => true,
					'labels' => array(
						'menu_name' => 'Expertise',
						'all_items' => 'All Expertise'
					),
					'capabilities' => array(
						'manage_terms' => 'manage_team_member_expertise',
						'edit_terms' => 'edit_team_member_expertise',
						'delete_terms' => 'delete_team_member_expertise',
						'assign_terms' => 'assign_team_member_expertise'
					)
				)
			) );

		}


		public static function filter_use_block_editor_for_post_type( $use_block_editor, $post_type ) {
			return in_array( $post_type, array( 'team_member' ) ) ? false : $use_block_editor;
		}


		public static function register_admin_styles( $hook ) {
			
			$screen = get_current_screen();
			if ( $screen->base == 'post' && $screen->post_type == 'team_member' ) {
				return;

				ob_start();
				?>
					<style>
						
					</style>
				<?php
				$css = trim( ob_get_clean() );
				$css = trim( preg_replace( array( '/^<style>/', '/<\/style>$/' ), '', $css ) );
				wp_add_inline_style( 'common', $css );

			} else if ( $screen->base == 'edit' && $screen->post_type == 'team_member' ) {

				$css = "
					table.wp-list-table	th.column-team-member-image,
					table.wp-list-table	td.column-team-member-image {
						width: 42px;
					}
					table.wp-list-table	td.column-team-member-image img {
						display: block;
						max-width: 100%;
						width: auto;
						height: auto;
					}
				";
				wp_add_inline_style( 'common', $css );

			}

		}


	}
}