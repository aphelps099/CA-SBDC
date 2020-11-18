<?php


if ( ! function_exists( 'ct_get_svg' ) ) {
	function ct_get_svg( $file ) {
		$file_path = Crown_Theme::get_dir() . '/' . $file;
		if ( file_exists( $file_path ) ) {
			return file_get_contents( $file_path );
		}
		return '';
	}
}


if ( ! function_exists( 'ct_get_icon' ) ) {
	function ct_get_icon( $icon_name, $library = 'bootstrap-icons' ) {
		if ( $library == 'bootstrap-icons' ) {
			$dir = Crown_Theme::get_dir() . '/lib/bootstrap-icons/icons';
			$filename = $icon_name . '.svg';
			if ( file_exists( $dir . '/' . $filename ) ) {
				return file_get_contents( $dir . '/' . $filename );
			}
		} else if ( $library == 'font-awesome' ) {
			$dir = Crown_Theme::get_dir() . '/lib/font-awesome/svgs';
			$filename = $icon_name . '.svg';
			if ( file_exists( $dir . '/' . $filename ) ) {
				return file_get_contents( $dir . '/' . $filename );
			}
		}
		return '';
	}
}

if ( ! function_exists( 'ct_icon' ) ) {
	function ct_icon( $icon_name, $library = 'bootstrap-icons' ) {
		echo ct_get_icon( $icon_name, $library );
	}
}


if ( ! function_exists( 'ct_bg_image_css' ) ) {
	function ct_bg_image_css( $attachment_id, $selector, $breakpoint_sizes = array() ) {

		$image_full_url = ct_get_media_url( $attachment_id );
		if ( ! $image_full_url ) return;

		$css = array();

		$grid_breakpoints = Crown_Theme::get_grid_breakpoints();
		foreach ( $grid_breakpoints as $grid_breakpoint ) {
			if ( array_key_exists( $grid_breakpoint->name, $breakpoint_sizes ) ) {
				$image_source = ct_get_image_src( $attachment_id, $breakpoint_sizes[ $grid_breakpoint->name ] );
				if ( ! empty( $image_source ) ) {
					$css_line = $selector . ' { background-image: url(' . $image_source . '); }';
					if ( ! empty( $css ) ) {
						$css_line = '@media (min-width: ' . $grid_breakpoint->width . 'px) { ' . $css_line . ' }';
					}
					$css[] = $css_line;
				}
			}
		}

		if ( empty( $css ) ) {
			$css[] = $selector . ' { background-image: url(' . $image_full_url . '); }';
		}

		if ( ! empty( $css ) ) {
			echo '<style>' . implode( ' ', $css ) . '</style>';
		}

	}
}


if ( ! function_exists( 'ct_get_image_src' ) ) {
	function ct_get_image_src( $attachment_id, $size = 'full' ) {
		$src = wp_get_attachment_image_src( $attachment_id, $size );
		return $src ? $src[0] : '';
	}
}


if ( ! function_exists( 'ct_get_media_url' ) ) {
	function ct_get_media_url( $attachment_id ) {
		return wp_get_attachment_url( $attachment_id );
	}
}


if ( ! function_exists( 'ct_get_related_posts' ) ) {
	function ct_get_related_posts( $post_id, $tax_weights = array(), $custom_query_args = array() ) {
		
		$default_tax_weights = array(
			'post_tag' => 2,
			'category' => 1
		);
		$tax_weights = ! empty( $tax_weights ) ? (array) $tax_weights : $default_tax_weights;

		$default_query_args = array(
			'post_type' => 'post',
			'tax_query' => array()
		);
		$custom_query_args = (array) $custom_query_args;
		$query_args = array_merge( $default_query_args, $custom_query_args, array(
			'posts_per_page' => -1,
			'ignore_sticky_posts' => true,
			'post__not_in' => array( $post_id ),
			'fields' => 'all'
		) );

		$has_terms = false;
		$post_terms = array();
		$tax_query = array( 'relation' => 'OR' );
		foreach ( $tax_weights as $tax => $weight ) {
			$terms = wp_get_object_terms( $post_id, $tax, array( 'fields' => 'ids' ) );
			if ( ! empty( $terms ) ) $has_terms = true;
			$post_terms[ $tax ] = $terms;
			$tax_query[] = array( 'taxonomy' => $tax, 'terms' => $terms );
		}
		$query_args['tax_query'][] = $tax_query;
		if ( ! $has_terms ) return array();

		$similar_posts = get_posts( $query_args );
		if ( empty( $similar_posts ) ) return array();

		$ranked_posts = array_map( function( $n ) use ( $post_terms, $tax_weights ) {
			$n = (object) array( 'post' => $n, 'score' => 0 );
			foreach( $tax_weights as $tax => $weight ) {
				$terms = wp_get_object_terms( $n->post->ID, $tax, array( 'fields' => 'ids' ) );
				$n->score += $weight * count( array_intersect( $post_terms[ $tax ], $terms ) );
			}
			return $n;
		}, $similar_posts );

		usort( $ranked_posts, function( $a, $b ) {
			if ( $a->score == $b->score ) return strcmp( $b->post->post_date, $a->post->post_date );
			return $b->score - $a->score;
		});

		if ( isset( $custom_query_args['fields'] ) && $custom_query_args['fields'] == 'ids' ) {
			return array_map( function( $n ) { return $n->post->ID; }, $ranked_posts );
		}
		return array_map( function( $n ) { return $n->post; }, $ranked_posts);

	}
}


if ( ! function_exists( 'ct_get_post_gated_content_settings' ) ) {
	function ct_get_post_gated_content_settings( $post_id = 0 ) {
		$post_id = ! empty( $post_id ) ? $post_id : get_the_ID();
		$settings = (object) array(
			'active' => boolval( get_post_meta( get_the_ID(), 'post_gated_content_active', true ) ),
			'title' => get_post_meta( get_the_ID(), 'post_gated_content_title', true ),
			'embed_script' => get_post_meta( get_the_ID(), 'post_gated_content_embed_script', true ),
			'disclaimer' => get_post_meta( get_the_ID(), 'post_gated_content_disclaimer', true )
		);
		if ( empty( $settings->embed_script ) ) $settings->active = false;
		return $settings;
	}
}