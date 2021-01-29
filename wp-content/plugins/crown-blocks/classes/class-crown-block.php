<?php

if ( ! class_exists( 'Crown_Block' ) ) {
	class Crown_Block {


		private static $namespace = 'crown-blocks';
		protected static $name = 'block-name'; // overridden by extended class

		private static $blocks_style_handle = 'crown-blocks-style-css';
		private static $blocks_editor_script_handle = 'crown-blocks-editor-js';
		private static $blocks_editor_style_handle = 'crown-blocks-editor-css';


		public static function get_namespace() { return self::$namespace; }
		public static function get_name() { return static::$name; }
		public static function get_block_name() { return self::get_namespace().'/'.self::get_name(); }

		public static function get_blocks_style_handle() { return self::$blocks_style_handle; }
		public static function get_blocks_editor_script_handle() { return self::$blocks_editor_script_handle; }
		public static function get_blocks_editor_style_handle() { return self::$blocks_editor_style_handle; }


		public static function init() {

			add_action( 'init', array( get_called_class(), 'register_block_scripts' ) );
			add_action( 'init', array( get_called_class(), 'register_block_styles' ) );
			add_action( 'init', array( get_called_class(), 'register' ) );

		}


		public static function register_block_scripts() {

			if ( ! wp_script_is( self::$blocks_editor_script_handle, 'registered' ) ) {

				// register block editor script
				wp_register_script(
					self::$blocks_editor_script_handle,
					plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ),
					array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
					filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ),
					true
				);

				// register localized globals for block editor script
				wp_localize_script(
					self::$blocks_editor_script_handle,
					'cbGlobal',
					array(
						'pluginDirPath' => plugin_dir_path( __DIR__ ),
						'pluginDirUrl' => plugin_dir_url( __DIR__ )
					)
				);

			}

		}


		public static function register_block_styles() {

			if ( ! wp_style_is( self::$blocks_style_handle, 'registered' ) ) {

				// register blocks style
				wp_register_style(
					self::$blocks_style_handle,
					plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ),
					is_admin() ? array( 'wp-editor' ) : array(),
					filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.style.build.css' )
				);

			}

			if ( ! wp_style_is( self::$blocks_editor_style_handle, 'registered' ) ) {

				// register blocks editor style
				wp_register_style(
					self::$blocks_editor_style_handle,
					plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ),
					array( 'wp-edit-blocks' ),
					filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.editor.build.css' )
				);

			}

		}


		public static function register() {

			$blockArgs = array(
				'style' => self::get_blocks_style_handle(),
				'editor_script' => self::get_blocks_editor_script_handle(),
				'editor_style' => self::get_blocks_editor_style_handle()
			);

			if ( is_callable( array( get_called_class(), 'get_attributes' ) ) ) {
				$attributes = call_user_func( array( get_called_class(), 'get_attributes' ) );
				if ( is_array( $attributes ) ) {
					$blockArgs['attributes'] = $attributes;
				}
			}

			if ( is_callable( array( get_called_class(), 'render' ) ) ) {
				$blockArgs['render_callback'] = array( get_called_class(), 'render' );
			}

			// register block
			register_block_type( self::get_block_name(), $blockArgs );

		}


		protected static function is_dark_color( $hex, $threshold = 0.607843137 ) {
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


		protected static function render_pagination_infinite( $query ) {

			$pagination = self::get_pagination( $query );
			if ( ! $pagination ) return;
			if ( empty( $pagination->next ) ) return;

			?>
				<div class="pagination-wrapper infinite">
					<nav class="navigation pagination infinite">
						<a class="btn btn-lg btn-blue" href="<?php echo esc_attr( $pagination->next ); ?>"><span><?php _e( 'Load More', 'crown_blocks' ); ?></span></a>
					</nav>
				</div>
			<?php
		}


		protected static function render_pagination( $query, $max_page_links = 5, $scroll_anchor = '' ) {

			$pagination = self::get_pagination( $query, $scroll_anchor );
			if ( ! $pagination ) return;

			$index_min = max( 1, $pagination->current_page - ceil( ( $max_page_links - 2 ) / 2 ) );
			$index_max = min( count( $pagination->pages ) - 2, $pagination->current_page - 1 + floor( ( $max_page_links - 2 ) / 2 ) );

			$index_min = $index_min == 1 ? 0 : $index_min;
			$index_max = $index_max == count( $pagination->pages ) - 2 ? count( $pagination->pages ) - 1 : $index_max;

			if ( $index_max - $index_min + 2 < $max_page_links ) {
				if ( $index_min == 0 ) {
					$index_max = $index_max + ( $max_page_links - ( $index_max - $index_min + 2 ) );
					$index_max = $index_max == count( $pagination->pages ) - 2 ? count( $pagination->pages ) - 1 : $index_max;
				} else if ( $index_max == count( $pagination->pages ) - 1 ) {
					$index_min = $index_min - ( $max_page_links - ( $index_max - $index_min + 2 ) );
					$index_min = $index_min == 1 ? 0 : $index_min;
				}
			}

			?>
				<div class="pagination-wrapper">
					<nav class="navigation pagination">
						<h3 class="screen-reader-text">Page Navigation</h3>
						<div class="nav-links">
	
							<?php if ( ! empty( $pagination->prev ) ) { ?>
								<a class="page-numbers prev" href="<?php echo esc_attr( $pagination->prev ); ?>"><span>Previous</span></a>
							<?php } else { ?>
								<span class="page-numbers prev"><span>Previous</span></span>
							<?php } ?>
	
							<?php if ( $index_min > 0 ) { ?>
								<a class="page-numbers <?php echo $pagination->current_page == 1 ? 'current' : ''; ?>" href="<?php echo esc_attr( $pagination->pages[0] ); ?>"><span>1</span></a>
								<span class="page-numbers dots"><span>&hellip;</span></span>
							<?php } ?>
							
							<?php $page_links = array_slice( $pagination->pages, $index_min, $index_max - $index_min + 1 ); ?>
							<?php foreach ( $page_links as $i => $page_link ) { ?>
								<a class="page-numbers <?php echo $pagination->current_page == $i + $index_min + 1 ? 'current' : ''; ?>" href="<?php echo esc_attr( $page_link ); ?>"><span><?php echo $i + $index_min + 1; ?></span></a>
							<?php } ?>
	
							<?php if ( $index_max < count( $pagination->pages ) - 1 ) { ?>
								<span class="page-numbers dots"><span>&hellip;</span></span>
								<a class="page-numbers" href="<?php echo esc_attr( $pagination->pages[ count( $pagination->pages ) - 1 ] ); ?>"><span><?php echo count( $pagination->pages ); ?></span></a>
							<?php } ?>
	
							<?php if ( ! empty( $pagination->next ) ) { ?>
								<a class="page-numbers next" href="<?php echo esc_attr( $pagination->next ); ?>"><span>Next</span></a>
							<?php } else { ?>
								<span class="page-numbers next"><span>Next</span></span>
							<?php } ?>
	
						</div>
					</nav>
				</div>
			<?php
		}


		protected static function get_pagination( $query, $scroll_anchor = '' ) {

			$page = $query->get( 'paged' );
			if ( ! $page ) $page = 1;
			$maxPage = $query->max_num_pages;
			if ( $maxPage < 2 ) return false;

			$pagination = (object) array(
				'current_page' => $page,
				'pages' => array(),
				'next' => null,
				'prev' => null
			);

			for ( $i = 1; $i <= $maxPage; $i++ ) {
				$pagination->pages[] = get_pagenum_link( $i ) . ( ! empty( $scroll_anchor ) ? '#' . $scroll_anchor : '' );
			}

			$nextPage = intval( $page ) + 1;
			if ( ! is_single() && ( $nextPage <= $maxPage ) ) {
				$pagination->next = next_posts( $maxPage, false ) . ( ! empty( $scroll_anchor ) ? '#' . $scroll_anchor : '' );
			}

			if ( ! is_single() && $page > 1 ) {
				$pagination->prev = previous_posts( false ) . ( ! empty( $scroll_anchor ) ? '#' . $scroll_anchor : '' );
			}

			return $pagination;
		}


	}
}