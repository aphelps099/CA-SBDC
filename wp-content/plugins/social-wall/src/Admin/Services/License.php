<?php

namespace SB\SocialWall\Admin\Services;

class License {

    /**
     * Activate license
     * 
     * @since 2.0
     */
    public static function activate_license() {
        check_ajax_referer( 'sbsw_admin_settings' , 'nonce');
        $license_key = isset( $_POST['license_key'] ) ? sanitize_text_field($_POST['license_key']) : '';

        $response = self::process_license_activation( $license_key );

		if ( $response === true ) {
			wp_send_json_success( [
				'licenseStatus' => self::get_license_status(),
				'licenseData'   => self::get_license_data()
			] );
		}

		wp_send_json_error();
    }


    /**
     * Activate license
     * 
     * @since 2.0
     */
    public static function deactivate_license() {
        check_ajax_referer( 'sbsw_admin_settings' , 'nonce');
        $license_key = isset( $_POST['license_key'] ) ? sanitize_text_field($_POST['license_key']) : '';

        $response = self::process_license_deactivation( $license_key );

		if ( $response === true ) {
			wp_send_json_success( [
				'licenseStatus' => self::get_license_status(),
				'licenseData'   => self::get_license_data()
			] );
		}

		wp_send_json_error();
    }

    /**
     * Process activating license 
     * 
     * @since 2.0
     */
    public static function process_license_activation( $license_key ) {
		$license_key = trim( $license_key );

		// data to send in our API request
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license_key,
			'item_name'  => urlencode( SBSW_PLUGIN_EDD_NAME ), // the name of our product in EDD
			'url'        => home_url()
		);

		// Call the remote API.
		$response = wp_remote_get( 
            add_query_arg( $api_params, SBSW_STORE_URL ),
			array( 'timeout' => 15, 'sslverify' => false ) 
        );

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// decode the license data
		$sbsw_license_data = (array) json_decode( wp_remote_retrieve_body( $response ) );
        
        if ( 
            isset($sbsw_license_data['success']) && $sbsw_license_data['success'] == false || 
            isset($sbsw_license_data['error']) && $sbsw_license_data['error'] == 'missing' || 
            isset($sbsw_license_data['license']) && ( 
                $sbsw_license_data['license'] == 'invalid_item_id' || 
                $sbsw_license_data['license'] == 'invalid' || 
                $sbsw_license_data['license'] == 'expired'
            ) 
        ) {
            return false;
        }

		// only store the license key
		update_option( 'sbsw_license_key', $license_key );
		//store the license data in an option
		update_option( 'sbsw_license_data', $sbsw_license_data );
		// $license_data->license will be either "valid" or "invalid"
		update_option( 'sbsw_license_status', $sbsw_license_data['license'] );

		return true;
    }

    public static function process_license_deactivation( $license_key ) {
		$license_key = trim( $license_key );
        
		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'deactivate_license',
			'license'    => $license_key,
			'item_name'  => urlencode( SBSW_PLUGIN_EDD_NAME ), // the name of our product in EDD
			'url'        => home_url()
		);

		// Call the custom API.
		$response = wp_remote_get( add_query_arg( $api_params, SBSW_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// decode the license data
		$license_data = (array) json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if( isset( $license_data['license'] ) && ( 
            $license_data['license'] == 'deactivated' || $license_data['license'] == 'failed'
        ) ) {
			delete_option( 'sbsw_license_data' );
			delete_option( 'sbsw_license_status' );
		}

		return true;
    }

    /**
     * Get license key
     * 
     * @since 2.0
     */
    public static function get_license_key() {
		return get_option('sbsw_license_key', '');
	}

    /**
     * Get license status
     * 
     * @since 2.0
     */
    public static function get_license_status() {
		return get_option('sbsw_license_status', 'inactive');
	}

    /**
     * Get license data
     * 
     * @since 2.0
     */
    public static function get_license_data() {
		return get_option('sbsw_license_data', []);
	}
}