<?php
/**
 * New Posts Block Initialize.
 *
 * @package New_Posts_Block
 */

namespace New_Posts_Block;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class New_Posts_Block_Initialize {
	function __construct() {
		add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ] );
		add_action( 'init', [ $this, 'init' ] );
		add_shortcode( 'new_posts', [ $this, 'new_posts_shortcode' ] );
		add_shortcode( 'new_rss', [ $this, 'new_rss_shortcode' ] );
	}

	function plugins_loaded() {
		load_plugin_textdomain( 'new-posts-block', false, basename( New_Posts_Block::plugin_dir_path() ) . '/languages' );
	}

	function init() {
		wp_register_style( 'new-posts-block-style', plugins_url( 'dist/style.css', dirname( __FILE__ ) ), [ 'wp-editor' ], New_Posts_Block::VERSION );
		//wp_register_style( 'new-posts-block-editor', plugins_url( 'dist/editor.css', dirname( __FILE__ ) ), [ 'wp-edit-blocks' ], New_Posts_Block::VERSION );
		wp_register_script( 'new-posts-block', plugins_url( 'dist/blocks.js', dirname( __FILE__ ) ), [ 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor' ], New_Posts_Block::VERSION, true );
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'new-posts-block', 'new-posts-block', New_Posts_Block::plugin_dir_path() . 'languages' );
		} elseif ( function_exists( 'gutenberg_get_jed_locale_data' ) ) {
			$locale  = gutenberg_get_jed_locale_data( $_settings_plugin->text_domain );
			wp_add_inline_script( 'new-posts-block', 'data', 'wp.i18n.setLocaleData( ' . json_encode( $locale ) . ', "new-posts-block" );' );
		}

		if ( function_exists( 'register_block_type' ) ) {
			register_block_type( 'new-posts-block/new-posts', [
				'style' => 'new-posts-block-style',
				//'editor_style' => 'new-posts-block-editor',
				'editor_script' => 'new-posts-block',
				'attributes' => [
					'align' => [
						'type' => 'string'
					],
					'className' => [
						'type' => 'string',
						'default' => '',
					],
					'postTypeSlug' => [
						'type'    => 'string',
						'default' => 'post',
					],
					'orderBy' => [
						'type'    => 'string',
						'default' => 'date',
					],
					'postsToShow' => [
						'type'    => 'number',
						'default' => 5,
					],
					'newMarkDays' => [
						'type'    => 'number',
						'default' => 30,
					],
					'newMarkText' => [
						'type'    => 'string',
						'default' => 'NEW!',
					],
					'displayNewMark' => [
						'type'    => 'boolean',
						'default' =>  true,
					],
					'excludePosts' => [
						'type'    => 'string',
						'default' =>  '',
					],
				],
				'render_callback' => [ $this, 'render_posts_block' ],
			] );

			register_block_type( 'new-posts-block/new-rss', [
				'style' => 'new-posts-block-style',
				//'editor_style' => 'new-posts-block-editor',
				'editor_script' => 'new-posts-block',
				'attributes' => [
					'align' => [
						'type' => 'string'
					],
					'className' => [
						'type' => 'string',
						'default' => '',
					],
					'feedURL' => [
						'type'    => 'string',
						'default' => '',
					],
					'postsToShow' => [
						'type'    => 'number',
						'default' => 5,
					],
					'newMarkDays' => [
						'type'    => 'number',
						'default' => 30,
					],
					'newMarkText' => [
						'type'    => 'string',
						'default' => 'NEW!',
					],
					'displayNewMark' => [
						'type'    => 'boolean',
						'default' =>  true,
					],
				],
				'render_callback' => [ $this, 'render_rss_block' ],
			] );
		}
	}

	function render_posts_block( $attributes ) {
		$class = 'new-posts';
		if ( ! empty( $attributes['className'] ) ) {
			$class .= ' ' . $attributes['className'];
		}
		if ( isset( $attributes['align'] ) ) {
			$class .= ' align' . $attributes['align'];
		}
		$html = $this->new_posts_shortcode( [
			'class'            => $class,
			'post_type'        => $attributes['postTypeSlug'],
			'orderby'          => $attributes['orderBy'],
			'count'            => $attributes['postsToShow'],
			'new_mark_days'    => $attributes['newMarkDays'],
			'new_mark_text'    => $attributes['newMarkText'],
			'display_new_mark' => $attributes['displayNewMark'],
			'exclude'          => $attributes['excludePosts'],
		]);
		return $html;
	}

	function new_posts_shortcode( $atts = null ) {
		global $post;

		$atts = shortcode_atts( [
			'class'            => 'new-posts',
			'count'            => 10,
			'post_type'        => 'post',
			'category'         => '',
			'exclude'          => '',
			'orderby'          => 'date',
			'date_format'      => '',
			'new_mark_days'    => 30,
			'new_mark_text'    => 'NEW!',
			'display_new_mark' => true,
		], $atts, 'new_posts' );

		$myposts = get_posts( [
			'showposts'     => $atts['count'],
			'post_status'   => 'publish',
			'post_type'     => $atts['post_type'],
			'orderby'       => $atts['orderby'],
			'order'         => 'DESC',
			'category_name' => $atts['category'],
			'exclude'       => $atts['exclude'],
		] );

		$retour = '<ul' . ( $atts['class'] != '' ? " class=\"{$atts['class']}\"" : '' ) . ">\n";
		$now_unix = date_i18n( 'U' );
		foreach ( $myposts as $post ) {
			setup_postdata( $post );
			$retour .= '<li>';

			$date_unix = $atts['orderby'] == 'date' ? get_the_time( 'U' ) : get_the_modified_time( 'U' );
			$diff_day = ( $now_unix - $date_unix ) / 86400;
			$new_html =  ( $atts['display_new_mark'] && $atts['new_mark_days'] > $diff_day ) ? '<span class="new-mark">' . wp_kses_post( $atts['new_mark_text'] ) . '</span>' : '';

			$date_html = $atts['orderby'] == 'date' ? get_the_date( $atts['date_format'] ) : get_the_modified_date( $atts['date_format'] );

			$retour .= '<span class="post-date">' . $date_html . '</span>';
			$retour .= '<div class="post-data">' . $new_html . '<span class="title"><a href="' . get_permalink() . '">' . the_title( '', '', false ) . "</a></span></div>\n";

			$retour .= "</li>\n";
		}
		wp_reset_postdata();
		$retour .= '</ul>' . "\n";

		return $retour;
	}

	function render_rss_block( $attributes ) {
		$class = 'new-rss';
		if ( ! empty( $attributes['className'] ) ) {
			$class .= ' ' . $attributes['className'];
		}
		if ( isset( $attributes['align'] ) ) {
			$class .= ' align' . $attributes['align'];
		}
		$html = $this->new_rss_shortcode( [
			'class'            => $class,
			'feed_url'         => $attributes['feedURL'],
			'count'            => $attributes['postsToShow'],
			'new_mark_days'    => $attributes['newMarkDays'],
			'new_mark_text'    => $attributes['newMarkText'],
			'display_new_mark' => $attributes['displayNewMark'],
		]);
		return $html;
	}

	function new_rss_shortcode( $atts = null ) {
		global $post;

		$atts = shortcode_atts( [
			'class'            => 'new-rss',
			'count'            => 10,
			'feed_url'         => '',
			'date_format'      => '',
			'new_mark_days'    => 30,
			'new_mark_text'    => 'NEW!',
			'display_new_mark' => true,
		], $atts, 'new_rss' );

		$date_format = $atts['date_format'];
		if ( ! $date_format ) $date_format = get_option( 'date_format' );

		$rss = fetch_feed( $atts['feed_url'] );

		if ( is_wp_error( $rss ) ) {
			return '<div class="components-placeholder"><div class="notice notice-error"><strong>' . __( 'RSS Error:', 'new-posts-block' ) . '</strong> ' . $rss->get_error_message() . '</div></div>';
		}
	
		if ( ! $rss->get_item_quantity() ) {
			$rss->__destruct();
			unset( $rss );
			return '<div class="components-placeholder"><div class="notice notice-error">' . __( 'Could not access feed. Please wait.', 'new-posts-block' ) . '</div></div>';
		}
	
		$rss_items = $rss->get_items( 0, $atts['count'] );
		$retour = '<ul' . ( $atts['class'] != '' ? " class=\"{$atts['class']}\"" : '' ) . ">\n";
		$now_unix = date_i18n( 'U' );
		foreach ( $rss_items as $item ) {
			$title = esc_html( trim( strip_tags( $item->get_title() ) ) );
			if ( empty( $title ) ) {
				$title = __( '(Untitled)', 'new-posts-block' );
			}
			$link = $item->get_link();
			$link = esc_url( $link );
			if ( $link ) {
				$title = "<a href='{$link}'>{$title}</a>";
			}

			$retour .= '<li>';

			$date_unix = $item->get_date( 'U' );
			$diff_day = ( $now_unix - $date_unix ) / 86400;
			$new_html =  ( $atts['display_new_mark'] && $atts['new_mark_days'] > $diff_day ) ? '<span class="new-mark">' . wp_kses_post( $atts['new_mark_text'] ) . '</span>' : '';

			$retour .= '<span class="post-date">' . date_i18n( $date_format, $date_unix ) . '</span>';
			$retour .= '<div class="post-data">' . $new_html . '<span class="title">' . $title . '</span></div>' . "\n";

			$retour .= "</li>\n";
		}
		$retour .= '</ul>' . "\n";

		return $retour;
	}
}
