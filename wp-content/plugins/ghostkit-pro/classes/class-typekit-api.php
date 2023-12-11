<?php
/**
 * Typekit Client
 *
 * @package ghostkit-pro
 */

/**
 * Typekit Client Class
 */
class GhostKit_Typekit_Api {

    /**
     * Timeout.
     *
     * @var int $timeout Timeout.
     */
    private $timeout = 30;

    /**
     * Api link.
     *
     * @var string $api link.
     */
    private $api = 'https://typekit.com/api/v1/json/kits/';

    /**
     * Create a new instance of the client
     *
     * @param number $timeout Connection timeout in seconds (default is 30 seconds).
     */
    public function __construct( $timeout = 30 ) {
        $this->timeout = $timeout;
    }

    /**
     * Make a request and read the response. If succesful,
     * a tuple of HTTP status code and response data is
     * returned. If an error occurs NULL is returned.
     *
     * @param number $path Path.
     * @param string $token Token.
     * @return (number, string)|null
     */
    private function make_request( $path, $token ) {

        $remote_get = wp_remote_get(
            $path,
            array(
                'timeout'     => $this->timeout,
                'httpversion' => '1.1',
                'headers'     => array(
                    'Accept'          => 'application/json',
                    'Host'            => 'typekit.com',
                    'X-Typekit-Token' => $token,
                ),
            )
        );

        if ( ! is_wp_error( $remote_get ) || wp_remote_retrieve_response_code( $remote_get ) === 200 ) {
            return array( '200', $remote_get['body'] );
        } else {
            return null;
        }
    }

    /**
     * Get one or more kits. If kit identifier is not given
     * all kits are returned.
     *
     * @param string $id     The kit identifier (optional).
     * @param string $token  Your Typekit API token (optional).
     * @param bool   $cached Cache result.
     * @return string|null NULL if retrieving the kit(s) failed, otherwise it return the data.
     */
    public function get( $id = null, $token = null, $cached = true ) {

        $transient = sprintf( 'ghostkit_typekit_%s_s', $id, $token );

        $data = $cached ? get_option( $transient ) : null;

        if ( null === $data || false === $data ) {

            if ( ! is_null( $id ) ) {
                if ( ! is_null( $token ) ) {
                    $result = $this->make_request( $this->api . $id . '/', $token );
                } else {
                    $result = $this->make_request( $this->api . $id . '/published', $token );
                }
            } else {
                $result = $this->make_request( $this->api, $token );
            }

            if ( ! is_null( $result ) ) {
                list($status, $data) = $result;

                if ( '200' === $status ) {
                    $data = json_decode( $data, true );
                }
            }

            update_option( $transient, $data, false );
        }

        return $data;
    }
}
