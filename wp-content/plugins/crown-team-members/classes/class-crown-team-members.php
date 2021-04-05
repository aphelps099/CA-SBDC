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
		public static $syndicated_team_member_post_type = null;
		public static $team_member_category_taxonomy = null;
		public static $team_member_expertise_taxonomy = null;


		public static function init() {
			if( self::$init ) return;
			self::$init = true;

			$plugin_file = preg_replace( '/\/classes$/', '', dirname( __FILE__ ) ) . '/plugin.php';
			register_activation_hook( $plugin_file, array( __CLASS__, 'activate' ));
			register_deactivation_hook( $plugin_file, array( __CLASS__, 'deactivate' ));

			add_action( 'init', array( __CLASS__, 'init_scheduled_events' ) );
			add_action( 'crown_sync_team_member_data', array( __CLASS__, 'sync_team_member_data' ) );
			// add_action( 'init', array( __CLASS__, 'sync_team_member_data' ), 100, 0 );

			add_action( 'after_setup_theme', array( __CLASS__, 'register_team_member_post_type' ) );
			add_action( 'after_setup_theme', array( __CLASS__, 'register_team_member_category_taxonomy' ) );
			add_action( 'after_setup_theme', array( __CLASS__, 'register_team_member_expertise_taxonomy' ) );

			add_action( 'save_post', array( __CLASS__, 'update_shared_post_reference' ), 100 );
			add_action( 'after_delete_post', array( __CLASS__, 'delete_syndicated_post' ), 100 );

			// add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'filter_use_block_editor_for_post_type' ), 10, 2 );

			add_action( 'admin_init', array( __CLASS__, 'process_action_publish_syndicated_team_member' ) );
			add_action( 'admin_init', array( __CLASS__, 'process_action_unpublish_syndicated_team_member' ) );
			add_action( 'admin_notices', array( __CLASS__, 'output_syndicated_team_member_admin_notices' ) );
			add_filter( 'bulk_actions-edit-team_member_s', array( __CLASS__, 'filter_bulk_actions_edit_team_member_s'), 100, 2 );
			add_filter( 'disable_months_dropdown', '__return_true' );
			add_filter( 'display_post_states', array( __CLASS__, 'filter_display_post_states'), 10, 2 );
			add_filter( 'post_row_actions', array( __CLASS__, 'filter_post_row_actions' ), 100, 2 );
			add_filter( 'get_edit_post_link', array( __CLASS__, 'filter_get_edit_post_link' ), 10, 3 );

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


		public static function init_scheduled_events() {

			// wp_clear_scheduled_hook( 'crown_sync_team_member_data' );
			if ( ! wp_next_scheduled( 'crown_sync_team_member_data' ) ) {
				wp_schedule_single_event( time() - 60, 'crown_sync_team_member_data' );
			}

		}


		public static function sync_team_member_data( $sync_all = false ) {
			global $wpdb;

			$current_time = new DateTime();

			if ( ! is_main_site() ) {

				$query_modified_time = new DateTime( '@0' );
				if ( ! $sync_all ) {
					$query_modified_time = get_option( 'crown_team_member_data_last_synced' ) ? new DateTime( get_option( 'crown_team_member_data_last_synced', 0 ) ) : new DateTime();
					$query_modified_time->modify( '-1 hour' );
				}

				$syn_post_ids = $wpdb->get_col( $wpdb->prepare( "
					SELECT pm2.meta_value
					FROM $wpdb->posts p
					INNER JOIN $wpdb->postmeta pm1 ON (pm1.post_id = p.ID AND pm1.meta_key = '_original_site_id')
					INNER JOIN $wpdb->postmeta pm2 ON (pm2.post_id = p.ID AND pm2.meta_key = '_original_post_id')
					WHERE p.post_type = 'team_member_s'
						AND pm1.meta_value = %s
				", get_main_site_id() ) );

				$dest_site = get_current_blog_id();
				switch_to_blog( get_main_site_id() );

				$update_post_ids = get_posts( array(
					'post_type' => 'team_member',
					'posts_per_page' => -1,
					'fields' => 'ids',
					'date_query' => array(
						array( 'column' => 'post_modified_gmt', 'after' => $query_modified_time->format( 'Y-m-d H:i:s' ) )
					),
					'meta_query' => array(
						array( 'key' => '__team_member_options', 'value' => 'post-to-center-sites' )
					)
				) );

				foreach ( $update_post_ids as $post_id ) {
					self::syndicate_post( $post_id, $dest_site );
				}

				$post_ids = get_posts( array(
					'post_type' => 'team_member',
					'posts_per_page' => -1,
					'fields' => 'ids',
					'meta_query' => array(
						array( 'key' => '__team_member_options', 'value' => 'post-to-center-sites' )
					)
				) );
				
				$old_post_ids = array_diff( $syn_post_ids, $post_ids );
				foreach ( $old_post_ids as $post_id ) {
					self::delete_syndicated_post( $post_id, $dest_site );
				}

				restore_current_blog();

			} else {

				// $post_ids = get_posts( array(
				// 	'post_type' => 'team_member_s',
				// 	'posts_per_page' => -1,
				// 	'fields' => 'ids'
				// ) );

				// foreach ( $post_ids as $post_id ) {
				// 	$site_id = get_post_meta( $post_id, '_original_site_id', true );
				// 	$center_term_ids = get_terms( array(
				// 		'taxonomy' => 'post_center',
				// 		'fields' => 'ids',
				// 		'hide_empty' => false,
				// 		'meta_query' => array(
				// 			array( 'key' => 'center_site_id', 'value' => $site_id )
				// 		)
				// 	) );
				// 	wp_set_object_terms( $post_id, $center_term_ids, 'post_center', false );
				// }

			}

			update_option( 'crown_team_member_data_last_synced', $current_time->format( 'Y-m-d H:i:s' ) );

			$sync_interval = 30; // minutes
			wp_clear_scheduled_hook( 'crown_sync_team_member_data' );
			wp_schedule_single_event( time() + ( 60 * $sync_interval ), 'crown_sync_team_member_data' );

		}


		public static function register_team_member_post_type() {

			$team_member_options = array(
				array( 'value' => 'multilingual', 'label' => 'Multilingual' )
			);
			if ( is_main_site() ) {
				$team_member_options[] = array( 'value' => 'post-to-center-sites', 'label' => 'Display on Center Sites' );
			} else {
				$team_member_options[] = array( 'value' => 'do-not-post-to-regional-site', 'label' => 'Don\'t Display on Regional Site' );
			}

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
												'input' => new CheckboxSet( array( 'name' => 'team_member_options', 'options' => $team_member_options ) )
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

			// $count = count( get_posts( array( 'post_type' => 'team_member_s', 'posts_per_page' => -1, 'fields' => 'ids', 'post_status' => 'pending' ) ) );
			$count = 0;
			self::$syndicated_team_member_post_type = new PostType( array(
				'name' => 'team_member_s',
				'singularLabel' => 'Syndicated Team Member',
				'pluralLabel' => 'Syndicated Team Members',
				'settings' => array(
					'public' => false,
					'show_in_menu' => 'edit.php?post_type=team_member',
					// 'show_ui' => ! is_main_site(),
					'show_ui' => true,
					'labels' => array(
						'all_items' => 'Syndicated' . ( $count ? ' <span class="awaiting-mod">' . $count . '</span>' : '' )
					)
				),
				'listTableColumns' => array(
					new ListTableColumn( array(
						'key' => 'team-member-image',
						'title' => '',
						'position' => 1,
						'outputCb' => function( $post_id, $args ) {
							$image_url = get_post_meta( $post_id, 'team_member_headshot_photo_thumbnail_url', true );
							if ( $image_url ) echo '<a href="' . get_edit_post_link($post_id) . '"><img src="' . $image_url . '"></a>';
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
					) ),
					// new ListTableColumn( array(
					// 	'key' => 'team-member-site',
					// 	'title' => 'SBDC',
					// 	'position' => 4,
					// 	'outputCb' => function( $post_id, $args ) {
					// 		$site_id = get_post_meta( $post_id, '_original_site_id', true );
					// 		$site_details = get_blog_details( array( 'blog_id' => $site_id ) );
					// 		echo '<a href="' . $site_details->siteurl . '">' . $site_details->blogname . '</a>';
					// 	}
					// ) )
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
				'postTypes' => array( 'team_member', 'team_member_s' ),
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
				'postTypes' => array( 'team_member', 'team_member_s' ),
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


		public static function update_shared_post_reference( $post_id ) {

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;
			// if ( defined( 'DOING_CRON' ) && DOING_CRON ) return $post_id;

			$post = get_post( $post_id );
			if ( ! $post || $post->post_type != 'team_member' ) return $post_id;
			if ( $post->post_title == 'Auto Draft' ) return $post_id;

			$options = get_post_meta( $post_id, '__team_member_options' );

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
				'team_member_category' => array(),
				'team_member_expertise' => array()
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
				'team_member_first_name' => '',
				'team_member_middle_name' => '',
				'team_member_last_name' => '',
				'team_member_name_first_comma_last' => '',
				'team_member_name_first_comma_last_lc' => '',
				'team_member_name_last_comma_first' => '',
				'team_member_name_last_comma_first_lc' => '',
				'team_member_name_last_initial_lc' => '',
				'team_member_name_first_initial_lc' => '',
				'team_member_job_title' => '',
				'team_member_options' => ''
			);
			foreach ( $meta as $k => $v ) {
				$meta[ $k ] = get_post_meta( $post_id, $k, true );
			}

			$meta[ 'team_member_headshot_photo_thumbnail_url' ] = wp_get_attachment_image_url( get_post_meta( $post_id, 'team_member_headshot_photo', true ), 'thumbnail' );

			$src_site = get_current_blog_id();
			switch_to_blog( $dest_site );
			
			$syn_id = get_posts( array(
				'post_type' => 'team_member_s',
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
				// $syn_id = wp_insert_post( array( 'post_type' => 'team_member_s', 'post_status' => is_main_site() ? 'pending' : 'publish' ) );
				$syn_id = wp_insert_post( array( 'post_type' => 'team_member_s', 'post_status' => 'publish' ) );
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
			if ( is_array( $meta['team_member_options'] ) ) {
				foreach( $meta['team_member_options'] as $v ) {
					if ( in_array( $v, array( 'post-to-center-sites', 'do-not-post-to-center-sites', 'post-to-regional-site', 'do-not-post-to-regional-site' ) ) ) continue;
					add_post_meta( $syn_id, '__team_member_options', $v );
				}
			}

			update_post_meta( $syn_id, '_original_site_id', $src_site );
			update_post_meta( $syn_id, '_original_post_id', $post_id );

			if ( is_main_site() ) {
				$center_term_ids = get_terms( array(
					'taxonomy' => 'post_center',
					'fields' => 'ids',
					'hide_empty' => false,
					'meta_query' => array(
						array( 'key' => 'center_site_id', 'value' => $src_site )
					)
				) );
				wp_set_object_terms( $syn_id, $center_term_ids, 'post_center', false );
			}

			restore_current_blog();

		}


		public static function delete_syndicated_post( $post_id, $dest_site = 0 ) {
			if ( empty( $dest_site ) ) $dest_site = get_main_site_id();

			$src_site = get_current_blog_id();
			switch_to_blog( $dest_site );
				
			$syn_id = get_posts( array(
				'post_type' => 'team_member_s',
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
			return in_array( $post_type, array( 'team_member' ) ) ? false : $use_block_editor;
		}


		public static function process_action_publish_syndicated_team_member() {
			
			$post_id = isset( $_GET['publish_team_member_s'] ) ? intval( $_GET['publish_team_member_s'] ) : 0;
			if ( ! $post_id ) return;
			if ( ! check_admin_referer( 'publish-team-member-s-' . $post_id ) ) return;

			wp_update_post( array( 'ID' => $post_id, 'post_status' => 'publish' ) );

			$sendback = admin_url( 'edit.php?post_type=team_member_s' );
			$sendback = add_query_arg( array( 'published_team_member_s' => $post_id ), $sendback );
			wp_redirect( $sendback );
			die;

		}


		public static function process_action_unpublish_syndicated_team_member() {
			
			$post_id = isset( $_GET['unpublish_team_member_s'] ) ? intval( $_GET['unpublish_team_member_s'] ) : 0;
			if ( ! $post_id ) return;
			if ( ! check_admin_referer( 'unpublish-team-member-s-' . $post_id ) ) return;

			wp_update_post( array( 'ID' => $post_id, 'post_status' => 'pending' ) );

			$sendback = admin_url( 'edit.php?post_type=team_member_s' );
			$sendback = add_query_arg( array( 'unpublished_team_member_s' => $post_id ), $sendback );
			wp_redirect( $sendback );
			die;

		}


		public static function output_syndicated_team_member_admin_notices() {
			$notices = array();

			$post_id = isset( $_GET['published_team_member_s'] ) ? intval( $_GET['published_team_member_s'] ) : 0;
			if ( $post_id ) {
				$notices[] = array( 'type' => 'success', 'message' => __( 'The syndicated team member, "' . get_the_title( $post_id ) . '", has been published!', 'crown-team-members' ) );
			}

			$post_id = isset( $_GET['unpublished_team_member_s'] ) ? intval( $_GET['unpublished_team_member_s'] ) : 0;
			if ( $post_id ) {
				$notices[] = array( 'type' => 'success', 'message' => __( 'The syndicated team member, "' . get_the_title( $post_id ) . '", has been unpublished and updated to "pending".', 'crown-team-members' ) );
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


		public static function filter_bulk_actions_edit_team_member_s( $actions ) {
			$actions = array();
			return $actions;
		}


		public static function filter_display_post_states( $post_states, $post ) {
			if ( $post->post_type == 'team_member' && is_main_site() && in_array( 'post-to-center-sites', get_post_meta( $post->ID, '__team_member_options' ) ) ) {
				$post_states['post-syndicated'] = 'Syndicated';
			} else if ( $post->post_type == 'team_member' && ! is_main_site() && ! in_array( 'do-not-post-to-regional-site', get_post_meta( $post->ID, '__team_member_options' ) ) ) {
				$post_states['post-syndicated'] = 'Syndicated';
			}
			return $post_states;
		}


		public static function filter_post_row_actions( $actions, $post ) {
			if ( $post->post_type == 'team_member_s' ) {
				$actions = array();
				if ( is_main_site() ) {
					if ( $post->post_status == 'publish' ) {
						$actions = array(
							'unpublish' => sprintf(
								'<a href="%s" class="submitunpublish" aria-label="%s">%s</a>',
								wp_nonce_url( add_query_arg( array( 'unpublish_team_member_s' => $post->ID ) ), 'unpublish-team-member-s-' . $post->ID ),
								esc_attr( sprintf( __( 'Unpublish &#8220;%s&#8221;' ), $post->post_title ) ),
								_x( 'Unpublish', 'verb' )
							)
						);
					} else {
						$actions = array(
							'publish' => sprintf(
								'<a href="%s" class="submitpublish" aria-label="%s">%s</a>',
								wp_nonce_url( add_query_arg( array( 'publish_team_member_s' => $post->ID ) ), 'publish-team-member-s-' . $post->ID ),
								esc_attr( sprintf( __( 'Publish &#8220;%s&#8221;' ), $post->post_title ) ),
								_x( 'Publish', 'verb' )
							)
						);
					}
				}
			}
			return $actions;
		}
		

		public static function filter_get_edit_post_link( $link, $post_id, $context ) {
			if ( get_post_type( $post_id ) == 'team_member_s' ) {
				$original_post_id = get_post_meta( $post_id, '_original_post_id', true );
				switch_to_blog( get_post_meta( $post_id, '_original_site_id', true ) );
				$link = get_permalink( $original_post_id );
				restore_current_blog();
			}
			return $link;
		}


		public static function register_admin_styles( $hook ) {
			
			$screen = get_current_screen();
			if ( $screen->base == 'post' && in_array( $screen->post_type, array( 'team_member', 'team_member_s' ) ) ) {
				return;

				ob_start();
				?>
					<style>
						
					</style>
				<?php
				$css = trim( ob_get_clean() );
				$css = trim( preg_replace( array( '/^<style>/', '/<\/style>$/' ), '', $css ) );
				wp_add_inline_style( 'common', $css );

			} else if ( $screen->base == 'edit' && in_array( $screen->post_type, array( 'team_member', 'team_member_s' ) ) ) {

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