<?php


if ( ! class_exists( 'Crown_Site_Settings_Admin' ) ) {
	class Crown_Site_Settings_Admin {

		public static $init = false;


		public static function init() {
			if( self::$init ) return;
			self::$init = true;

			add_action( 'admin_menu', array( __CLASS__, 'cleanup_admin' ), 100 );
			add_action( 'admin_bar_menu', array( __CLASS__, 'cleanup_admin_bar' ), 999 );

			add_action( 'admin_menu', array( __CLASS__, 'add_blocks_menu_item' ), 10 );

		}


		public static function cleanup_admin() {
			global $menu, $submenu;

			// // find position of posts and pages menu items within menu
			// $blog_posts_menu_item_index = null;
			// $pages_menu_item_index = null;
			// foreach ( $menu as $index => $menu_item ) {
			// 	if ( in_array( 'edit.php', $menu_item ) ) {
			// 		$blog_posts_menu_item_index = $index;
			// 		$menu_item[0] = 'Blog Posts'; // rename menu item
			// 		$menu[$index] = $menu_item;
			// 	} else if ( in_array( 'edit.php?post_type=page', $menu_item ) ) {
			// 		$pages_menu_item_index = $index;
			// 	}
			// }
			
			// remove_menu_page( 'edit.php' );
			remove_menu_page( 'edit-comments.php' );
			// self::add_admin_menu_separator( 25 );

			// // reorder posts and pages menu items
			// if ( $blog_posts_menu_item_index !== null && array_key_exists($blog_posts_menu_item_index, $menu ) ) {
			// 	$menu[29] = $menu[ $blog_posts_menu_item_index ];
			// 	unset( $menu[ $blog_posts_menu_item_index ] );
			// }
			// if ( $pages_menu_item_index !== null && array_key_exists( $pages_menu_item_index, $menu ) ) {
			// 	$menu[7] = $menu[ $pages_menu_item_index ];
			// 	unset( $menu[ $pages_menu_item_index ] );
			// }
			
			// // remove customizer menu item
			// if ( isset( $submenu['themes.php'] ) ) {
			// 	foreach ( $submenu['themes.php'] as $index => $menu_item ) {
			// 		if ( ! empty( array_intersect( array( 'Customize', 'Customizer', 'customize' ), $menu_item ) ) ) {
			// 			unset( $submenu['themes.php'][ $index ] );
			// 		}
			// 	}
			// }

			if ( get_current_user_id() != 1 ) {
				remove_menu_page( 'ghostkit' );
				remove_menu_page( 'edit.php?post_type=ghostkit_template' );
				remove_menu_page( 'edit.php?post_type=wp_block' );
			}

		}

		protected static function add_admin_menu_separator( $position ) {
			global $menu;
			$index = 0;
			foreach ( $menu as $offset => $section ) {
				if ( substr( $section[2], 0, 9 ) == 'separator' ) $index++;
				if ( $offset >= $position ) {
					$menu[ $position ] = array( '', 'read', 'separator'.$index, '', 'wp-menu-separator' );
					break;
				}
			}
			ksort( $menu );
		}


		public static function cleanup_admin_bar( $adminBar ) {
			// $adminBar->remove_node( 'customize' );
			// $adminBar->remove_node( 'new-post' );
			$adminBar->remove_node( 'comments' );
		}


		public static function add_blocks_menu_item() {
			add_theme_page( 'Reusable Blocks', 'Reusable Blocks', 'read', 'edit.php?post_type=wp_block', '', null );
		}


	}
}