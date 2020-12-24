<?php

if ( ! class_exists( 'Crown_Theme_Ajax_Content' ) ) {
	class Crown_Theme_Ajax_Content {


		public static function init() {

			add_action( 'wp_ajax_get_ajax_content', array( __CLASS__, 'get_ajax_content' ) );
			add_action( 'wp_ajax_nopriv_get_ajax_content', array( __CLASS__, 'get_ajax_content' ) );

		}


		public static function get_ajax_content() {
			$response = (object) array(
				'url' => '',
				'id' => '',
				'content' => ''
			);

			$url = isset( $_GET['url'] ) ? $_GET['url'] : '';
			$id = isset( $_GET['id'] ) ? $_GET['id'] : '';

			if ( preg_match( '/^\//', $url ) ) $url = get_site_url() . $url;

			$response->url = $url;
			$response->id = $id;

			if ( empty( $url ) || empty( $id ) ) wp_send_json( $response );

			$content = wp_remote_get( $url, array( 'sslverify' => false ) );
			if ( ! is_array( $content ) || is_wp_error( $content ) ) wp_send_json( $response );
			
			$response->content = $content['body'];

			wp_send_json( $response );
		}


	}
}