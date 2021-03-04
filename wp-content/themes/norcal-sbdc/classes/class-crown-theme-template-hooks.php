<?php

if ( ! class_exists( 'Crown_Theme_Template_Hooks' ) ) {
	class Crown_Theme_Template_Hooks {


		public static function init() {

			add_filter( 'body_class', array( __CLASS__, 'filter_body_class' ) );
			add_filter( 'excerpt_length', array( __CLASS__, 'filter_excerpt_length' ) );
			add_filter( 'excerpt_more', array( __CLASS__, 'filter_excerpt_more' ) );

			add_filter( 'gform_submit_button', array( __CLASS__, 'filter_gravity_form_submit_button' ), 10, 2 );
			add_filter( 'gform_next_button', array( __CLASS__, 'filter_gravity_form_next_button' ), 10, 2 );
			add_filter( 'gform_previous_button', array( __CLASS__, 'filter_gravity_form_previous_button' ), 10, 2 );
			add_filter( 'gform_field_validation', array( __CLASS__, 'filter_gravity_form_field_validation' ), 10, 4 );

			add_filter( 'get_previous_post_join', array( __CLASS__, 'filter_get_adjacent_post_join' ), 10, 5 );
			add_filter( 'get_next_post_join', array( __CLASS__, 'filter_get_adjacent_post_join' ), 10, 5 );
			add_filter( 'get_previous_post_where', array( __CLASS__, 'filter_get_adjacent_post_where' ), 10, 5 );
			add_filter( 'get_next_post_where', array( __CLASS__, 'filter_get_adjacent_post_where' ), 10, 5 );

		}


		public static function filter_body_class( $classes ) {

			if ( is_singular() ) {
				$page_header_options = get_post_meta( get_the_ID(), 'page_header_options', true );
				if ( ! empty( $page_header_options ) ) {
					$classes = array_merge( $classes, array_map( function ( $n ) { return 'page-header-' . $n; }, $page_header_options ) );
				}
			}

			return $classes;
		}


		public static function filter_excerpt_length( $length ) {
			$length = 30;
			return $length;
		}


		public static function filter_excerpt_more( $excerpt_more ) {
			$excerpt_more = '&hellip;';
			return $excerpt_more;
		}


		public static function filter_gravity_form_submit_button( $button, $form ) {
			if ( preg_match( '/^\s*<input\s.*value=\'([^\']*)\'/', $button, $matches ) ) {
				$button = preg_replace( array( '/^<input/', '/\/?>$/' ), array(' <button', '>' . $matches[1] . '</button>'), $button );
			}
			return $button;
		}


		public static function filter_gravity_form_next_button( $button, $form ) {
			if ( preg_match( '/^\s*<input\s.*value=\'([^\']*)\'/', $button, $matches ) ) {
				$button = preg_replace( array( '/^<input/', '/\/?>$/' ), array(' <button', '>' . $matches[1] . '</button>'), $button );
			}
			return $button;
		}


		public static function filter_gravity_form_previous_button( $button, $form ) {
			if ( preg_match( '/^\s*<input\s.*value=\'([^\']*)\'/', $button, $matches ) ) {
				$button = preg_replace( array( '/^<input/', '/\/?>$/' ), array(' <button', '>' . $matches[1] . '</button>'), $button );
			}
			return $button;
		}


		public static function filter_gravity_form_field_validation( $result, $value, $form, $field ) {

			if ( $result['is_valid'] && $field->type == 'name' ) {
		 
				$prefix = rgar( $value, $field->id . '.2' );
				$first  = rgar( $value, $field->id . '.3' );
				$middle = rgar( $value, $field->id . '.4' );
				$last   = rgar( $value, $field->id . '.6' );
				$suffix = rgar( $value, $field->id . '.8' );
		 
				if ( ( ! empty( $first ) && ! $field->get_input_property( '3', 'isHidden' ) && preg_match( '/[0-9]/', $first ) )
					 || ( ! empty( $last ) && ! $field->get_input_property( '6', 'isHidden' ) && preg_match( '/[0-9]/', $last ) )
				) {
					$result['is_valid'] = false;
					$result['message'] = empty( $field->errorMessage ) ? __( 'Please enter a valid name (no numbers).', 'crown_theme' ) : $field->errorMessage;
				}

			}
		 
			return $result;

		}


		public static function filter_get_adjacent_post_join( $join, $in_same_term, $excluded_terms, $taxonomy, $post ) {
			global $wpdb;
			if ( $post->post_type == 'post' ) {
				$join .= " LEFT JOIN $wpdb->postmeta AS pm_pf ON (p.ID = pm_pf.post_id AND pm_pf.meta_key = 'crown_post_format')";
			}
			return $join;
		}

		public static function filter_get_adjacent_post_where( $where, $in_same_term, $excluded_terms, $taxonomy, $post ) {
			global $wpdb;
			if ( $post->post_type == 'post' ) {
				$where .= " AND (pm_pf.meta_value IS NULL OR pm_pf.meta_value NOT IN ('tweet', 'facebook-update'))";
			}
			return $where;
		}


	}
}