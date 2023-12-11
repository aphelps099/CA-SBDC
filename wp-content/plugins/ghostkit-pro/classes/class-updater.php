<?php
/**
 * Updater class
 *
 * @package ghostkit-pro
 */

/**
 * GhostKit_Pro_Updater Class
 */
class GhostKit_Pro_Updater {
    /**
     * GhostKit_Pro_Updater construct
     */
    public function __construct() {
        // phpcs:disable
        // uncomment this line for testing
        // set_site_transient( 'update_plugins', null );
        // phpcs:enable

        // admin_init.
        add_action( 'init', array( $this, 'init_updater' ), 11 );

        // Add the Updates settings to Ghost Kit admin menu.
        add_action( 'admin_menu', array( $this, 'admin_menu' ), 11 );

        // Activate or Deactivate plugin license.
        add_action( 'wp_ajax_gkt_pro_activation_action', array( $this, 'ajax_activation_action' ) );
    }

    /**
     * License license activation message when plugin update is available.
     */
    public function modify_plugin_update_message() {
        // translators: %1$s - settings updates page url.
        // translators: %2$s - purchase page.
        echo '<br />' . wp_kses_post( sprintf( __( 'To enable updates, please enter your license key on the <a href="%1$s">Updates</a> page. If you don\'t have a license key, please see <a href="%2$s" target="_blank">details & pricing</a>.', 'ghostkit-pro' ), admin_url( 'admin.php?page=ghostkit&sub_page=updates' ), 'https://ghostkit.io/pricing/' ) );
    }

    /**
     * Init updater on `init` action.
     */
    public function init_updater() {
        // retrieve license key from the DB.
        $license_key = trim( get_option( 'ghostkit_pro_license_key' ) );

        // License activation message when plugin update is available.
        if ( empty( $license_key ) || ! $license_key ) {
            add_action( 'in_plugin_update_message-' . plugin_basename( ghostkit_pro()->plugin_file_path ), array( $this, 'modify_plugin_update_message' ) );
        }

        // load updater class.
        if ( ! class_exists( 'EDD_GKT_Plugin_Updater' ) ) {
            include ghostkit_pro()->plugin_path . '/vendor/EDD_GKT_Plugin_Updater.php';
        }

        // setup the updater.
        new EDD_GKT_Plugin_Updater(
            'https://nkdev.info',
            ghostkit_pro()->plugin_file_path,
            array(
                'version' => '1.7.3',
                'license' => $license_key,
                'item_id' => ghostkit_pro()->plugin_id,
                'author'  => 'nK',
                'url'     => home_url(),
            )
        );
    }

    /**
     * Add admin menu.
     */
    public function admin_menu() {
        add_submenu_page(
            'ghostkit',
            '',
            esc_html__( 'Updates', 'ghostkit-pro' ),
            'manage_options',
            'admin.php?page=ghostkit&sub_page=updates'
        );
    }

    /**
     * Activate or Deactivate plugin license.
     *
     * @return void
     */
    public function ajax_activation_action() {
        check_ajax_referer( 'gkt_pro_activation', 'ajax_nonce' );

        $type        = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : null;                // input var okay.
        $license_key = isset( $_POST['license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['license_key'] ) ) : null;  // input var okay.
        $response    = false;
        $message     = false;

        if ( 'deactivate' === $type ) {
            // data to send in our API request.
            $api_params = array(
                'edd_action' => 'deactivate_license',
                'license'    => $license_key,
                'item_id'    => ghostkit_pro()->plugin_id,
                'url'        => home_url(),
            );

            // Call the custom API.
            $response = wp_remote_post(
                'https://nkdev.info',
                array(
                    'timeout'   => 15,
                    'sslverify' => false,
                    'body'      => $api_params,
                )
            );

            // make sure the response came back okay.
            if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
                $message = ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __( 'An error occurred, please try again.', 'ghostkit-pro' );
            } else {
                $license_data = json_decode( wp_remote_retrieve_body( $response ) );

                if ( false === $license_data->success && isset( $response->error ) ) {
                    $message = esc_html( $response->error );
                }
            }

            // Check if anything passed on a message constituting a failure.
            if ( $message && ! empty( $message ) ) {
                // phpcs:ignore
                echo 'Error: ' . $message;
                die();
            }

            // Remove license.
            delete_option( 'ghostkit_pro_license_key' );
        } else {
            // verify purchase code.
            if ( null === $license_key || ! $license_key ) {
                $message = __( 'Purchase code was not specified.', 'ghostkit-pro' );
            } else {
                // data to send in our API request.
                $api_params = array(
                    'edd_action' => 'activate_license',
                    'license'    => $license_key,
                    'item_id'    => ghostkit_pro()->plugin_id,
                    'url'        => home_url(),
                );

                // Call the custom API.
                $response = wp_remote_post(
                    'https://nkdev.info',
                    array(
                        'timeout'   => 15,
                        'sslverify' => false,
                        'body'      => $api_params,
                    )
                );

                // make sure the response came back okay.
                if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
                    $message = ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __( 'An error occurred, please try again.', 'ghostkit-pro' );
                } else {
                    $license_data = json_decode( wp_remote_retrieve_body( $response ) );

                    if ( false === $license_data->success ) {
                        if ( isset( $response->error ) ) {
                            $message = esc_html( $response->error );
                        } else {
                            $message = __( 'An error occurred, please try again.', 'ghostkit-pro' );
                        }
                    }
                }
            }

            // Check if anything passed on a message constituting a failure.
            if ( $message && ! empty( $message ) ) {
                // phpcs:ignore
                echo 'Error: ' . $message;
                die();
            }

            // Save license.
            update_option( 'ghostkit_pro_license_key', $license_key );
        }

        die();
    }
}

new GhostKit_Pro_Updater();
