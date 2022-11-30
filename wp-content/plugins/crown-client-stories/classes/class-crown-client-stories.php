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


if ( ! class_exists( 'Crown_Client_Stories' ) ) {
	class Crown_Client_Stories {

		public static $init = false;

		public static $client_story_post_type = null;
		public static $syndicated_client_story_post_type = null;
		public static $client_story_industry_taxonomy = null;
		public static $client_story_region_taxonomy = null;


		public static function init() {
			if( self::$init ) return;
			self::$init = true;

			$plugin_file = preg_replace( '/\/classes$/', '', dirname( __FILE__ ) ) . '/plugin.php';
			register_activation_hook( $plugin_file, array( __CLASS__, 'activate' ));
			register_deactivation_hook( $plugin_file, array( __CLASS__, 'deactivate' ));

			add_action( 'init', array( __CLASS__, 'init_scheduled_events' ) );
			add_action( 'crown_sync_client_story_data', array( __CLASS__, 'sync_client_story_data' ) );
			if ( isset( $_GET['sync_client_stories'] ) && boolval( $_GET['sync_client_stories'] ) ) {
				add_action( 'init', function() { self::sync_client_story_data( true ); }, 100, 0 );
			}

			add_action( 'after_setup_theme', array( __CLASS__, 'register_client_story_post_type' ) );
			add_action( 'after_setup_theme', array( __CLASS__, 'register_client_story_industry_taxonomy' ) );
			add_action( 'after_setup_theme', array( __CLASS__, 'register_client_story_region_taxonomy' ) );

			add_action( 'save_post', array( __CLASS__, 'update_post_center_terms' ), 90 );

			add_action( 'save_post', array( __CLASS__, 'update_shared_post_reference' ), 100 );
			add_action( 'after_delete_post', array( __CLASS__, 'delete_syndicated_post' ), 100 );

			// add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'filter_use_block_editor_for_post_type' ), 10, 2 );
			
			add_action( 'admin_init', array( __CLASS__, 'process_action_publish_syndicated_client_story' ) );
			add_action( 'admin_init', array( __CLASS__, 'process_action_unpublish_syndicated_client_story' ) );
			add_action( 'admin_notices', array( __CLASS__, 'output_syndicated_client_story_admin_notices' ) );
			add_filter( 'bulk_actions-edit-client_story_s', array( __CLASS__, 'filter_bulk_actions_edit_client_story_s'), 100, 2 );
			add_filter( 'disable_months_dropdown', '__return_true' );
			add_filter( 'display_post_states', array( __CLASS__, 'filter_display_post_states'), 10, 2 );
			add_filter( 'post_row_actions', array( __CLASS__, 'filter_post_row_actions' ), 100, 2 );
			add_filter( 'get_edit_post_link', array( __CLASS__, 'filter_get_edit_post_link' ), 10, 3 );

			// add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_admin_styles' ) );

		}


		public static function activate() {
			global $wp_roles;
			
			foreach ( $wp_roles->role_objects as $role ) {
				foreach ( array( 'publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private' ) as $cap ) {
					if ( $role->has_cap( $cap . '_posts' ) ) {
						$role->add_cap( $cap . '_client_stories' );
					}
				}
				foreach ( array( 'manage', 'edit', 'delete' ) as $cap ) {
					if ( $role->has_cap( 'manage_categories' ) ) {
						$role->add_cap( $cap . '_client_story_industries' );
					}
				}
				if ( $role->has_cap( 'edit_posts' ) ) {
					$role->add_cap( 'assign_client_story_industries' );
				}
			}

			flush_rewrite_rules();
		}


		public static function deactivate() {
			global $wp_roles;
			
			foreach ( $wp_roles->role_objects as $role ) {
				foreach ( array( 'publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private' ) as $cap ) {
					$role->remove_cap( $cap . '_client_stories' );
				}
				foreach ( array( 'manage', 'edit', 'delete', 'assign' ) as $cap ) {
					$role->remove_cap ( $cap . '_client_story_industries' );
				}
			}
			
			flush_rewrite_rules();
		}


		public static function init_scheduled_events() {

			// wp_clear_scheduled_hook( 'crown_sync_client_story_data' );
			if ( ! wp_next_scheduled( 'crown_sync_client_story_data' ) ) {
				wp_schedule_single_event( time() - 60, 'crown_sync_client_story_data' );
			}

		}


		public static function sync_client_story_data( $sync_all = false ) {
			global $wpdb;

			$current_time = new DateTime();

			if ( ! is_main_site() ) {

				$syndication_enabled = apply_filters( 'crown_syndication_enabled', true, 'client_story' );

				$query_modified_time = new DateTime( '@0' );
				if ( ! $sync_all ) {
					$query_modified_time = get_option( 'crown_client_story_data_last_synced' ) ? new DateTime( get_option( 'crown_client_story_data_last_synced', 0 ) ) : new DateTime();
					$query_modified_time->modify( '-1 hour' );
				}

				$syn_post_ids = $wpdb->get_col( $wpdb->prepare( "
					SELECT pm2.meta_value
					FROM $wpdb->posts p
					INNER JOIN $wpdb->postmeta pm1 ON (pm1.post_id = p.ID AND pm1.meta_key = '_original_site_id')
					INNER JOIN $wpdb->postmeta pm2 ON (pm2.post_id = p.ID AND pm2.meta_key = '_original_post_id')
					WHERE p.post_type = 'client_story_s'
						AND pm1.meta_value = %s
				", get_main_site_id() ) );

				$dest_site = get_current_blog_id();
				switch_to_blog( get_main_site_id() );

				$post_ids = array();
				if ( $syndication_enabled && apply_filters( 'crown_syndication_enabled', true, 'client_story' ) ) {

					$update_post_ids = get_posts( array(
						'post_type' => 'client_story',
						'posts_per_page' => -1,
						'fields' => 'ids',
						'date_query' => array(
							array( 'column' => 'post_modified_gmt', 'after' => $query_modified_time->format( 'Y-m-d H:i:s' ) )
						),
						'meta_query' => array(
							array( 'key' => '__client_story_options', 'value' => 'post-to-center-sites' )
						)
					) );

					foreach ( $update_post_ids as $post_id ) {
						self::syndicate_post( $post_id, $dest_site );
					}

					$post_ids = get_posts( array(
						'post_type' => 'client_story',
						'posts_per_page' => -1,
						'fields' => 'ids',
						'meta_query' => array(
							array( 'key' => '__client_story_options', 'value' => 'post-to-center-sites' )
						)
					) );
				}
				
				$old_post_ids = array_diff( $syn_post_ids, $post_ids );
				foreach ( $old_post_ids as $post_id ) {
					self::delete_syndicated_post( $post_id, $dest_site );
				}

				restore_current_blog();

			}

			update_option( 'crown_client_story_data_last_synced', $current_time->format( 'Y-m-d H:i:s' ) );

			$sync_interval = 30; // minutes
			wp_clear_scheduled_hook( 'crown_sync_client_story_data' );
			wp_schedule_single_event( time() + ( 60 * $sync_interval ), 'crown_sync_client_story_data' );

		}


		public static function register_client_story_post_type() {

			$client_story_options = array(
				array( 'value' => 'featured-post', 'label' => 'Featured Client Story' )
			);
			if ( is_main_site() ) {
				$client_story_options[] = array( 'value' => 'post-to-center-sites', 'label' => 'Display on Center Sites' );
			} else {
				$client_story_options[] = array( 'value' => 'do-not-post-to-regional-site', 'label' => 'Don\'t Display on Regional Site' );
			}

			self::$client_story_post_type = new PostType( array(
				'name' => 'client_story',
				'singularLabel' => 'Client Story',
				'pluralLabel' => 'Client Stories',
				'settings' => array(
					'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
					'rewrite' => array( 'slug' => 'client-stories', 'with_front' => false ),
					'menu_icon' => 'dashicons-testimonial',
					'has_archive' => false,
					'publicly_queryable' => true,
					'show_in_rest' => true,
					'show_in_nav_menus' => false,
					'capability_type' => array( 'client_story', 'client_stories' ),
					'map_meta_cap' => true,
					'menu_position' => 36,
					'template' => array(
						array( 'crown-blocks/client-story-header', array() ),
						array( 'core/paragraph', array() )
					)
				),
				'fields' => array(
					new Field( array(
						'saveMetaCb' => function ( $field, $input, $type, $objectId, $value ) {
							$post = get_post( $objectId );
							$initial = substr( strtoupper( $post->post_title ), 0, 1 );
							if ( ! in_array( $initial, array( 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z' ) ) ) {
								$initial = '#';
							}
							update_post_meta( $post->ID, 'client_story_initial_lc', strtolower( $initial ) );
						}
					) )
				),
				'metaBoxes' => array(
					new MetaBox( array(
						'id' => 'client-story-options',
						'title' => 'Client Story Options',
						'context' => 'side',
						'fields' => array(
							new Field( array(
								'label' => 'Color',
								'input' => new ColorInput( array( 'name' => 'client_story_color', 'colorpickerOptions' => array( 'palettes' => apply_filters( 'crown_theme_colors', array(), 'client_story_color' ) ) ) )
							) ),
							new Field(array(
								'input' => new CheckboxSet( array( 'name' => 'client_story_options', 'options' => $client_story_options ) )
							) )
						)
					) ),
				),
				'listTableColumns' => array(
					
				)
			) );
			
			// $count = count( get_posts( array( 'post_type' => 'client_story_s', 'posts_per_page' => -1, 'fields' => 'ids', 'post_status' => 'pending' ) ) );
			$count = 0;
			self::$syndicated_client_story_post_type = new PostType( array(
				'name' => 'client_story_s',
				'singularLabel' => 'Syndicated Client Story',
				'pluralLabel' => 'Syndicated Client Stories',
				'settings' => array(
					'rewrite' => array( 'slug' => 'shared/client-stories', 'with_front' => false ),
					'show_in_menu' => apply_filters( 'crown_syndication_enabled', true, 'client_story' ) ? 'edit.php?post_type=client_story' : false,
					'has_archive' => false,
					'publicly_queryable' => true,
					'show_in_rest' => true,
					'show_in_nav_menus' => false,
					'labels' => array(
						'all_items' => 'Syndicated' . ( $count ? ' <span class="awaiting-mod">' . $count . '</span>' : '' )
					)
				),
				'listTableColumns' => array(
					// new ListTableColumn( array(
					// 	'key' => 'client-story-source-site',
					// 	'title' => 'Source Site',
					// 	'position' => 2,
					// 	'outputCb' => function( $post_id, $args ) {
					// 		$site_id = get_post_meta( $post_id, '_original_site_id', true );
					// 		$site_details = get_blog_details( array( 'blog_id' => $site_id ) );
					// 		echo '<a href="' . $site_details->siteurl . '">' . $site_details->blogname . '</a>';
					// 	}
					// ) )
				)
			) );

		}


		public static function register_client_story_industry_taxonomy() {

			self::$client_story_industry_taxonomy = new Taxonomy( array(
				'name' => 'client_story_industry',
				'singularLabel' => 'Client Story Industry',
				'pluralLabel' => 'Client Story Industries',
				'postTypes' => array( 'client_story', 'client_story_s' ),
				'settings' => array(
					'hierarchical' => true,
					'rewrite' => array( 'slug' => 'client-story-industries', 'with_front' => false ),
					'show_in_nav_menus' => false,
					'show_admin_column' => true,
					'publicly_queryable' => false,
					'show_in_rest' => true,
					'labels' => array(
						'menu_name' => 'Industries',
						'all_items' => 'All Industries'
					),
					'capabilities' => array(
						'manage_terms' => 'manage_client_story_industries',
						'edit_terms' => 'edit_client_story_industries',
						'delete_terms' => 'delete_client_story_industries',
						'assign_terms' => 'assign_client_story_industries'
					)
				)
			) );

		}


		public static function register_client_story_region_taxonomy() {

			if ( ! apply_filters( 'crown_client_story_region_taxonomy_enabled', false ) ) return;

			self::$client_story_region_taxonomy = new Taxonomy( array(
				'name' => 'client_story_region',
				'singularLabel' => 'Client Story Region',
				'pluralLabel' => 'Client Story Regions',
				'postTypes' => array( 'client_story', 'client_story_s' ),
				'settings' => array(
					'hierarchical' => true,
					'rewrite' => array( 'slug' => 'client-story-regions', 'with_front' => false ),
					'show_in_nav_menus' => false,
					'show_admin_column' => true,
					'publicly_queryable' => false,
					'show_in_rest' => true,
					'labels' => array(
						'menu_name' => 'Regions',
						'all_items' => 'All Regions'
					),
					'capabilities' => array(
						'manage_terms' => 'manage_client_story_industries',
						'edit_terms' => 'edit_client_story_industries',
						'delete_terms' => 'delete_client_story_industries',
						'assign_terms' => 'assign_client_story_industries'
					)
				)
			) );

		}


		public static function update_post_center_terms( $post_id ) {

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;
			// if ( defined( 'DOING_CRON' ) && DOING_CRON ) return $post_id;

			$post = get_post( $post_id );
			if ( ! $post || $post->post_type != 'client_story' ) return $post_id;
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
			if ( ! $post || $post->post_type != 'client_story' ) return $post_id;
			if ( $post->post_title == 'Auto Draft' ) return $post_id;

			if ( ! apply_filters( 'crown_syndication_enabled', true, $post->post_type ) ) {
				if ( ! is_main_site() ) {
					self::delete_syndicated_post( $post_id, get_main_site_id() );
				}
				return $post_id;
			}

			$options = get_post_meta( $post_id, '__client_story_options' );

			if ( ! in_array( 'do-not-post-to-regional-site', $options ) && $post->post_status == 'publish' && ! is_main_site() ) {
				self::syndicate_post( $post_id, get_main_site_id() );
			} else if ( ! is_main_site() ) {
				self::delete_syndicated_post( $post_id, get_main_site_id() );
			}

		}


		protected static function syndicate_post( $post_id, $dest_site ) {
			global $wpdb;

			$post_type = get_post_type( $post_id );

			$post = get_post( $post_id );
			$post_arr = array(
				'post_title' => $post->post_title,
				'post_date' => $post->post_date,
				'post_content' => $post->post_content,
				'post_excerpt' => $post->post_excerpt,
				'comment_status' => $post->comment_status,
				'ping_status' => $post->ping_status,
				'post_password' => $post->post_password,
				'post_name' => $post->post_name
			);

			$taxonomies = array(
				'client_story_industry' => array(),
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

			$meta = get_post_meta( $post_id );

			$src_site = get_current_blog_id();
			switch_to_blog( $dest_site );

			if ( ! apply_filters( 'crown_syndication_enabled', true, $post_type ) ) {
				restore_current_blog();
				return;
			}
			
			$syn_ids = get_posts( array(
				'post_type' => 'client_story_s',
				'posts_per_page' => -1,
				'fields' => 'ids',
				'post_status' => 'any',
				'meta_query' => array(
					array( 'key' => '_original_site_id', 'value' => $src_site ),
					array( 'key' => '_original_post_id', 'value' => $post_id )
				)
			) );
			$syn_id = ! empty( $syn_ids ) ? $syn_ids[0] : null;
			if ( count( $syn_ids ) > 1 ) {
				foreach ( $syn_ids as $i => $id ) {
					if ( $i == 0 ) continue;
					wp_delete_post( $id, true );
				}
			}

			if ( ! $syn_id ) {
				// $syn_id = wp_insert_post( array( 'post_type' => 'client_story_s', 'post_status' => is_main_site() ? 'pending' : 'publish' ) );
				$syn_id = wp_insert_post( array( 'post_type' => 'client_story_s', 'post_status' => 'publish' ) );
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

			$post_meta_ids = $wpdb->get_col( $wpdb->prepare( "SELECT meta_id FROM $wpdb->postmeta WHERE post_id = %d", $syn_id ) );
			foreach ( $post_meta_ids as $mid ) delete_metadata_by_mid( 'post', $mid );
			foreach ( $meta as $key => $values ) {
				foreach ( $values as $value ) {
					add_post_meta( $syn_id, $key, $value );
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
				'post_type' => 'client_story_s',
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
			return in_array( $post_type, array( 'client_story' ) ) ? false : $use_block_editor;
		}


		public static function process_action_publish_syndicated_client_story() {
			
			$post_id = isset( $_GET['publish_client_story_s'] ) ? intval( $_GET['publish_client_story_s'] ) : 0;
			if ( ! $post_id ) return;
			if ( ! check_admin_referer( 'publish-client-story-s-' . $post_id ) ) return;

			if ( is_main_site() ) {
				wp_update_post( array( 'ID' => $post_id, 'post_status' => 'publish' ) );
			} else {
				update_post_meta( $post_id, 'hide_from_index', false );
			}

			$sendback = admin_url( 'edit.php?post_type=client_story_s' );
			$sendback = add_query_arg( array( 'published_client_story_s' => $post_id ), $sendback );
			wp_redirect( $sendback );
			die;

		}


		public static function process_action_unpublish_syndicated_client_story() {
			
			$post_id = isset( $_GET['unpublish_client_story_s'] ) ? intval( $_GET['unpublish_client_story_s'] ) : 0;
			if ( ! $post_id ) return;
			if ( ! check_admin_referer( 'unpublish-client-story-s-' . $post_id ) ) return;

			if ( is_main_site() ) {
				wp_update_post( array( 'ID' => $post_id, 'post_status' => 'pending' ) );
			} else {
				update_post_meta( $post_id, 'hide_from_index', true );
			}

			$sendback = admin_url( 'edit.php?post_type=client_story_s' );
			$sendback = add_query_arg( array( 'unpublished_client_story_s' => $post_id ), $sendback );
			wp_redirect( $sendback );
			die;

		}


		public static function output_syndicated_client_story_admin_notices() {
			$notices = array();

			$post_id = isset( $_GET['published_client_story_s'] ) ? intval( $_GET['published_client_story_s'] ) : 0;
			if ( $post_id ) {
				if ( is_main_site() ) {
					$notices[] = array( 'type' => 'success', 'message' => __( 'The syndicated client story, "' . get_the_title( $post_id ) . '", has been published!', 'crown-client-stories' ) );
				} else {
					$notices[] = array( 'type' => 'success', 'message' => __( 'The syndicated client story, "' . get_the_title( $post_id ) . '", has been added to the client story index.', 'crown-client-stories' ) );
				}
			}

			$post_id = isset( $_GET['unpublished_client_story_s'] ) ? intval( $_GET['unpublished_client_story_s'] ) : 0;
			if ( $post_id ) {
				if ( is_main_site() ) {
					$notices[] = array( 'type' => 'success', 'message' => __( 'The syndicated client story, "' . get_the_title( $post_id ) . '", has been unpublished and updated to "pending".', 'crown-client-stories' ) );
				} else {
					$notices[] = array( 'type' => 'success', 'message' => __( 'The syndicated client story, "' . get_the_title( $post_id ) . '", has been hidden from the client story index.', 'crown-client-stories' ) );
				}
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


		public static function filter_bulk_actions_edit_client_story_s( $actions ) {
			if ( ! apply_filters( 'crown_syndication_enabled', true, 'client_story' ) ) return $actions;
			$actions = array();
			return $actions;
		}


		public static function filter_display_post_states( $post_states, $post ) {
			if ( ! apply_filters( 'crown_syndication_enabled', true, $post->post_type ) ) return $post_states;
			if( $post->post_type == 'client_story' && in_array( 'featured-post', get_post_meta( $post->ID, '__client_story_options' ) ) ) {
				$post_states['post-featured'] = 'Featured';
			}
			if ( $post->post_type == 'client_story' && is_main_site() && in_array( 'post-to-center-sites', get_post_meta( $post->ID, '__client_story_options' ) ) ) {
				$post_states['post-syndicated'] = 'Syndicated';
			} else if ( $post->post_type == 'client_story' && ! is_main_site() && ! in_array( 'do-not-post-to-regional-site', get_post_meta( $post->ID, '__client_story_options' ) ) ) {
				$post_states['post-syndicated'] = 'Syndicated';
			}
			if ( $post->post_type == 'client_story_s' && ! is_main_site() ) {
				$hide_from_index = get_post_meta( $post->ID, 'hide_from_index', true );
				if ( $hide_from_index ) {
					$post_states['post-syndicated-hidden'] = 'Hidden';
				}
			}
			return $post_states;
		}


		public static function filter_post_row_actions( $actions, $post ) {
			if ( ! apply_filters( 'crown_syndication_enabled', true, 'client_story' ) ) return $actions;
			if ( $post->post_type == 'client_story_s' ) {
				$actions = array();
				if ( is_main_site() ) {
					if ( $post->post_status == 'publish' ) {
						$actions = array(
							'unpublish' => sprintf(
								'<a href="%s" class="submitunpublish" aria-label="%s">%s</a>',
								wp_nonce_url( add_query_arg( array( 'unpublish_client_story_s' => $post->ID ) ), 'unpublish-client-story-s-' . $post->ID ),
								esc_attr( sprintf( __( 'Unpublish &#8220;%s&#8221;' ), $post->post_title ) ),
								_x( 'Unpublish', 'verb' )
							)
						);
					} else {
						$actions = array(
							'publish' => sprintf(
								'<a href="%s" class="submitpublish" aria-label="%s">%s</a>',
								wp_nonce_url( add_query_arg( array( 'publish_client_story_s' => $post->ID ) ), 'publish-client-story-s-' . $post->ID ),
								esc_attr( sprintf( __( 'Publish &#8220;%s&#8221;' ), $post->post_title ) ),
								_x( 'Publish', 'verb' )
							)
						);
					}
				} else {
					$hide_from_index = get_post_meta( $post->ID, 'hide_from_index', true );
					if ( empty( $hide_from_index ) || ! $hide_from_index ) {
						$actions = array(
							'unpublish' => sprintf(
								'<a href="%s" class="submitunpublish" aria-label="%s">%s</a>',
								wp_nonce_url( add_query_arg( array( 'unpublish_client_story_s' => $post->ID ) ), 'unpublish-client-story-s-' . $post->ID ),
								esc_attr( sprintf( __( 'Unpublish &#8220;%s&#8221;' ), $post->post_title ) ),
								_x( 'Unpublish', 'verb' )
							)
						);
					} else {
						$actions = array(
							'publish' => sprintf(
								'<a href="%s" class="submitpublish" aria-label="%s">%s</a>',
								wp_nonce_url( add_query_arg( array( 'publish_client_story_s' => $post->ID ) ), 'publish-client-story-s-' . $post->ID ),
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
			if ( get_post_type( $post_id ) == 'client_story_s' ) {
				$link = get_permalink( $post_id );
			}
			return $link;
		}


		public static function register_admin_styles( $hook ) {
			
			$screen = get_current_screen();
			if ( $screen->base == 'post' && $screen->post_type == 'client_story' ) {
				return;

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