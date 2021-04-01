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


if ( ! class_exists( 'Crown_Webinars' ) ) {
	class Crown_Webinars {

		public static $init = false;

		public static $webinar_post_type = null;
		public static $syndicated_webinar_post_type = null;


		public static function init() {
			if( self::$init ) return;
			self::$init = true;

			$plugin_file = preg_replace( '/\/classes$/', '', dirname( __FILE__ ) ) . '/plugin.php';
			register_activation_hook( $plugin_file, array( __CLASS__, 'activate' ));
			register_deactivation_hook( $plugin_file, array( __CLASS__, 'deactivate' ));

			add_action( 'init', array( __CLASS__, 'init_scheduled_events' ) );
			add_action( 'crown_sync_webinar_data', array( __CLASS__, 'sync_webinar_data' ) );
			// add_action( 'init', array( __CLASS__, 'sync_webinar_data' ), 100, 0 );

			add_action( 'after_setup_theme', array( __CLASS__, 'register_webinar_post_type' ) );

			add_action( 'save_post', array( __CLASS__, 'update_shared_post_reference' ), 100 );
			add_action( 'after_delete_post', array( __CLASS__, 'delete_syndicated_post' ), 100 );

			// add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'filter_use_block_editor_for_post_type' ), 10, 2 );

			add_action( 'admin_init', array( __CLASS__, 'process_action_publish_syndicated_webinar' ) );
			add_action( 'admin_init', array( __CLASS__, 'process_action_unpublish_syndicated_webinar' ) );
			add_action( 'admin_notices', array( __CLASS__, 'output_syndicated_webinar_admin_notices' ) );
			add_filter( 'bulk_actions-edit-webinar_s', array( __CLASS__, 'filter_bulk_actions_edit_webinar_s'), 100, 2 );
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
						$role->add_cap( $cap . '_webinars' );
					}
				}
			}

			flush_rewrite_rules();
		}


		public static function deactivate() {
			global $wp_roles;
			
			foreach ( $wp_roles->role_objects as $role ) {
				foreach ( array( 'publish', 'delete', 'delete_others', 'delete_private', 'delete_published', 'edit', 'edit_others', 'edit_private', 'edit_published', 'read_private' ) as $cap ) {
					$role->remove_cap( $cap . '_webinars' );
				}
			}
			
			flush_rewrite_rules();
		}


		public static function init_scheduled_events() {

			// wp_clear_scheduled_hook( 'crown_sync_webinar_data' );
			if ( ! wp_next_scheduled( 'crown_sync_webinar_data' ) ) {
				wp_schedule_single_event( time() - 60, 'crown_sync_webinar_data' );
			}

		}


		public static function sync_webinar_data( $sync_all = false ) {
			global $wpdb;

			$current_time = new DateTime();

			if ( ! is_main_site() ) {

				$query_modified_time = new DateTime( '@0' );
				if ( ! $sync_all ) {
					$query_modified_time = get_option( 'crown_webinar_data_last_synced' ) ? new DateTime( get_option( 'crown_webinar_data_last_synced', 0 ) ) : new DateTime();
					$query_modified_time->modify( '-1 hour' );
				}

				$syn_post_ids = $wpdb->get_col( $wpdb->prepare( "
					SELECT pm2.meta_value
					FROM $wpdb->posts p
					INNER JOIN $wpdb->postmeta pm1 ON (pm1.post_id = p.ID AND pm1.meta_key = '_original_site_id')
					INNER JOIN $wpdb->postmeta pm2 ON (pm2.post_id = p.ID AND pm2.meta_key = '_original_post_id')
					WHERE p.post_type = 'webinar_s'
						AND pm1.meta_value = %s
				", get_main_site_id() ) );

				$dest_site = get_current_blog_id();
				switch_to_blog( get_main_site_id() );

				$update_post_ids = get_posts( array(
					'post_type' => 'webinar',
					'posts_per_page' => -1,
					'fields' => 'ids',
					'date_query' => array(
						array( 'column' => 'post_modified_gmt', 'after' => $query_modified_time->format( 'Y-m-d H:i:s' ) )
					),
					'meta_query' => array(
						array( 'key' => '__webinar_options', 'value' => 'post-to-center-sites' )
					)
				) );

				foreach ( $update_post_ids as $post_id ) {
					self::syndicate_post( $post_id, $dest_site );
				}

				$post_ids = get_posts( array(
					'post_type' => 'webinar',
					'posts_per_page' => -1,
					'fields' => 'ids',
					'meta_query' => array(
						array( 'key' => '__webinar_options', 'value' => 'post-to-center-sites' )
					)
				) );
				
				$old_post_ids = array_diff( $syn_post_ids, $post_ids );
				foreach ( $old_post_ids as $post_id ) {
					self::delete_syndicated_post( $post_id, $dest_site );
				}

				restore_current_blog();

			}

			update_option( 'crown_webinar_data_last_synced', $current_time->format( 'Y-m-d H:i:s' ) );

			$sync_interval = 30; // minutes
			wp_clear_scheduled_hook( 'crown_sync_webinar_data' );
			wp_schedule_single_event( time() + ( 60 * $sync_interval ), 'crown_sync_webinar_data' );

		}


		public static function register_webinar_post_type() {

			$webinar_options = array();
			if ( is_main_site() ) {
				$webinar_options[] = array( 'value' => 'post-to-center-sites', 'label' => 'Display on Center Sites' );
			} else {
				$webinar_options[] = array( 'value' => 'do-not-post-to-regional-site', 'label' => 'Don\'t Display on Regional Site' );
			}

			self::$webinar_post_type = new PostType( array(
				'name' => 'webinar',
				'singularLabel' => 'Webinar',
				'pluralLabel' => 'Webinars',
				'settings' => array(
					'supports' => array( 'title', 'editor', 'excerpt', 'revisions', 'thumbnail' ),
					'rewrite' => array( 'slug' => 'webinar', 'with_front' => false ),
					'menu_icon' => 'dashicons-video-alt3',
					'has_archive' => false,
					'publicly_queryable' => true,
					'show_in_rest' => true,
					'show_in_nav_menus' => false,
					'capability_type' => array( 'webinar', 'webinars' ),
					'map_meta_cap' => true,
					'menu_position' => 32,
					'template' => array(
						array( 'crown-blocks/webinar-header', array() ),
						array( 'core/paragraph', array() )
					)
				),
				'metaBoxes' => array(
					new MetaBox( array(
						'id' => 'webinar-options',
						'title' => 'Webinar Options',
						'context' => 'side',
						'fields' => array(
							new Field( array(
								'input' => new CheckboxSet( array( 'name' => 'webinar_options', 'options' => $webinar_options ) )
							) )
						)
					) )
				)
			) );

			// $count = count( get_posts( array( 'post_type' => 'webinar_s', 'posts_per_page' => -1, 'fields' => 'ids', 'post_status' => 'pending' ) ) );
			$count = 0;
			self::$syndicated_webinar_post_type = new PostType( array(
				'name' => 'webinar_s',
				'singularLabel' => 'Syndicated Webinar',
				'pluralLabel' => 'Syndicated Webinars',
				'settings' => array(
					'public' => false,
					'show_in_menu' => 'edit.php?post_type=webinar',
					'show_ui' => true,
					'labels' => array(
						'all_items' => 'Syndicated' . ( $count ? ' <span class="awaiting-mod">' . $count . '</span>' : '' )
					)
				),
				'listTableColumns' => array(
					new ListTableColumn( array(
						'key' => 'webinar-site',
						'title' => 'SBDC',
						'position' => 2,
						'outputCb' => function( $post_id, $args ) {
							$site_id = get_post_meta( $post_id, '_original_site_id', true );
							$site_details = get_blog_details( array( 'blog_id' => $site_id ) );
							echo '<a href="' . $site_details->siteurl . '">' . $site_details->blogname . '</a>';
						}
					) )
				)
			) );

		}


		public static function update_shared_post_reference( $post_id ) {

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;
			// if ( defined( 'DOING_CRON' ) && DOING_CRON ) return $post_id;

			$post = get_post( $post_id );
			if ( ! $post || $post->post_type != 'webinar' ) return $post_id;
			if ( $post->post_title == 'Auto Draft' ) return $post_id;

			$options = get_post_meta( $post_id, '__webinar_options' );

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
				'post_topic' => array()
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

			$meta = array();
			foreach ( $meta as $k => $v ) {
				$meta[ $k ] = get_post_meta( $post_id, $k, true );
			}

			$src_site = get_current_blog_id();
			switch_to_blog( $dest_site );
			
			$syn_id = get_posts( array(
				'post_type' => 'webinar_s',
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
				// $syn_id = wp_insert_post( array( 'post_type' => 'webinar_s', 'post_status' => is_main_site() ? 'pending' : 'publish' ) );
				$syn_id = wp_insert_post( array( 'post_type' => 'webinar_s', 'post_status' => 'publish' ) );
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
				'post_type' => 'webinar_s',
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
			return in_array( $post_type, array( 'webinar' ) ) ? false : $use_block_editor;
		}


		public static function process_action_publish_syndicated_webinar() {
			
			$post_id = isset( $_GET['publish_webinar_s'] ) ? intval( $_GET['publish_webinar_s'] ) : 0;
			if ( ! $post_id ) return;
			if ( ! check_admin_referer( 'publish-webinar-s-' . $post_id ) ) return;

			wp_update_post( array( 'ID' => $post_id, 'post_status' => 'publish' ) );

			$sendback = admin_url( 'edit.php?post_type=webinar_s' );
			$sendback = add_query_arg( array( 'published_webinar_s' => $post_id ), $sendback );
			wp_redirect( $sendback );
			die;

		}


		public static function process_action_unpublish_syndicated_webinar() {
			
			$post_id = isset( $_GET['unpublish_webinar_s'] ) ? intval( $_GET['unpublish_webinar_s'] ) : 0;
			if ( ! $post_id ) return;
			if ( ! check_admin_referer( 'unpublish-webinar-s-' . $post_id ) ) return;

			wp_update_post( array( 'ID' => $post_id, 'post_status' => 'pending' ) );

			$sendback = admin_url( 'edit.php?post_type=webinar_s' );
			$sendback = add_query_arg( array( 'unpublished_webinar_s' => $post_id ), $sendback );
			wp_redirect( $sendback );
			die;

		}


		public static function output_syndicated_webinar_admin_notices() {
			$notices = array();

			$post_id = isset( $_GET['published_webinar_s'] ) ? intval( $_GET['published_webinar_s'] ) : 0;
			if ( $post_id ) {
				$notices[] = array( 'type' => 'success', 'message' => __( 'The syndicated webinar, "' . get_the_title( $post_id ) . '", has been published!', 'crown-webinars' ) );
			}

			$post_id = isset( $_GET['unpublished_webinar_s'] ) ? intval( $_GET['unpublished_webinar_s'] ) : 0;
			if ( $post_id ) {
				$notices[] = array( 'type' => 'success', 'message' => __( 'The syndicated webinar, "' . get_the_title( $post_id ) . '", has been unpublished and updated to "pending".', 'crown-webinars' ) );
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


		public static function filter_bulk_actions_edit_webinar_s( $actions ) {
			$actions = array();
			return $actions;
		}


		public static function filter_display_post_states( $post_states, $post ) {
			if ( $post->post_type == 'webinar' && is_main_site() && in_array( 'post-to-center-sites', get_post_meta( $post->ID, '__webinar_options' ) ) ) {
				$post_states['post-syndicated'] = 'Syndicated';
			} else if ( $post->post_type == 'webinar' && ! is_main_site() && ! in_array( 'do-not-post-to-regional-site', get_post_meta( $post->ID, '__webinar_options' ) ) ) {
				$post_states['post-syndicated'] = 'Syndicated';
			}
			return $post_states;
		}


		public static function filter_post_row_actions( $actions, $post ) {
			if ( $post->post_type == 'webinar_s' ) {
				$actions = array();
				if ( is_main_site() ) {
					if ( $post->post_status == 'publish' ) {
						$actions = array(
							'unpublish' => sprintf(
								'<a href="%s" class="submitunpublish" aria-label="%s">%s</a>',
								wp_nonce_url( add_query_arg( array( 'unpublish_webinar_s' => $post->ID ) ), 'unpublish-webinar-s-' . $post->ID ),
								esc_attr( sprintf( __( 'Unpublish &#8220;%s&#8221;' ), $post->post_title ) ),
								_x( 'Unpublish', 'verb' )
							)
						);
					} else {
						$actions = array(
							'publish' => sprintf(
								'<a href="%s" class="submitpublish" aria-label="%s">%s</a>',
								wp_nonce_url( add_query_arg( array( 'publish_webinar_s' => $post->ID ) ), 'publish-webinar-s-' . $post->ID ),
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
			if ( get_post_type( $post_id ) == 'webinar_s' ) {
				$original_post_id = get_post_meta( $post_id, '_original_post_id', true );
				switch_to_blog( get_post_meta( $post_id, '_original_site_id', true ) );
				$link = get_permalink( $original_post_id );
				restore_current_blog();
			}
			return $link;
		}


		public static function register_admin_styles( $hook ) {
			
			$screen = get_current_screen();
			if ( $screen->base == 'post' && $screen->post_type == 'webinar' ) {
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


		public static function get_webinar_primary_topic( $post_id ) {
			
			$term_ids = wp_get_post_terms( $post_id, 'post_topic', array( 'fields' => 'ids' ) );

			$primary_term_id = get_post_meta( $post_id, '_primary_term_post_topic', true );
			
			if ( ! empty( $primary_term_id ) && in_array( $primary_term_id, $term_ids ) ) array_unshift( $term_ids, $primary_term_id );
			$term_ids = array_values( array_unique( $term_ids ) );

			return ! empty( $term_ids ) ? get_term( $term_ids[0], 'post_topic' ) : null;
		}


		public static function get_webinar_primary_topic_color( $post_id ) {
			
			$term_ids = wp_get_post_terms( $post_id, 'post_topic', array( 'fields' => 'ids' ) );

			$primary_term_id = get_post_meta( $post_id, '_primary_term_post_topic', true );
			
			if ( ! empty( $primary_term_id ) && in_array( $primary_term_id, $term_ids ) ) array_unshift( $term_ids, $primary_term_id );
			$term_ids = array_values( array_unique( $term_ids ) );

			foreach( $term_ids as $term_id ) {
				$color = get_term_meta( $term_id, 'post_topic_color', true );
				if ( ! empty( $color ) ) return $color;
			}

			return '';
		}


		public static function webinar_teaser( $post_id = null ) {
			global $post;

			if ( ! empty( $post_id ) ) {
				$post = get_post( $post_id );
				if ( ! $post || ! in_array( $post->post_type, array( 'webinar', 'webinar_s' ) ) ) return;
				setup_postdata( $post );
			}

			$switched_site = false;
			$webinar_site_title = null;
			if ( get_post_type() == 'webinar_s' ) {
				$original_post_id = get_post_meta( get_the_ID(), '_original_post_id', true );
				switch_to_blog( get_post_meta( get_the_ID(), '_original_site_id', true ) );
				$post = get_post( $original_post_id );
				setup_postdata( $post );
				if ( ! is_main_site() ) $webinar_site_title = get_bloginfo( 'name' );
				$switched_site = true;
			}

			$color = self::get_webinar_primary_topic_color( get_the_ID() );

			?>
				<article <?php post_class( 'webinar-teaser' ); ?>>
					<a href="<?php the_permalink(); ?>">

						<div class="entry-featured-image">
							<div class="inner <?php echo ! empty( $color ) ? ( self::is_dark_color( $color ) ? 'dark' : 'light' ) : 'dark'; ?>" <?php echo ! empty( $color ) ? 'style="background-color: ' . $color . ';"' : ''; ?>>
								<?php $image_src = has_post_thumbnail() ? wp_get_attachment_image_url( get_post_thumbnail_id(), 'medium_large' ) : false; ?>
								<?php if ( ! empty( $image_src ) ) { ?>
									<div class="image" style="background-image: url(<?php echo $image_src; ?>);">
										<?php echo wp_get_attachment_image( get_post_thumbnail_id(), 'medium_large' ) ?>
									</div>
								<?php } ?>
							</div>
						</div>

						<div class="entry-header">
							
							<?php $topics = get_the_terms( get_the_ID(), 'post_topic' ); ?>
							<?php if ( ! empty( $topics ) ) { ?>
								<p class="entry-topic">
									<?php foreach ( $topics as $term ) { ?>
										<span class="topic"><?php echo $term->name; ?></span>
									<?php } ?>
								</p>
							<?php } ?>

							<h4 class="entry-title"><?php the_title(); ?></h4>

						</div>

						<div class="entry-excerpt">
							<?php if ( function_exists( 'ct_the_excerpt' ) ) { ?>
								<?php ct_the_excerpt( 16 ); ?>
							<?php } else { ?>
								<?php the_excerpt(); ?>
							<?php } ?>
						</div>

						<div class="bottom-border" <?php echo ! empty( $color ) ? 'style="border-color: ' . $color . ';"' : ''; ?>></div>

					</a>
				</article>
			<?php

			if ( $switched_site ) {
				restore_current_blog();
				wp_reset_postdata();
			}

			if ( ! empty( $post_id ) ) {
				wp_reset_postdata();
			}

		}


		public static function is_dark_color( $hex, $threshold = 0.607843137 ) {
			$luminosity = self::get_color_luminosity( $hex );
			return $luminosity <= $threshold;
		}

		protected static function get_color_luminosity( $hex = '' ) {
			$hex = preg_replace( '/[^0-9a-f]/i', '', $hex );
			if ( $hex == '' || strlen( $hex ) < 3 ) $hex = 'fff';
			if ( strlen( $hex ) < 6 ) $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
			$c = array();
			for ( $i = 0; $i < 3; $i++ ) $c[] = intval( substr( $hex, $i * 2, 2 ), 16) / 255;
			for ( $i = 0; $i < 3; $i++ ) {
				if ( $c[ $i ] <= 0.03928) {
					$c[ $i ] = $c[ $i ] / 12.92;
				} else {
					$c[ $i ] = pow( ( $c[ $i ] + 0.055 ) / 1.055, 2.4 );
				}
			}
			$luminosity = ( 0.2126 * $c[0] ) + ( 0.7152 * $c[1] ) + ( 0.0722 * $c[2] );
			return $luminosity;
		}


	}
}