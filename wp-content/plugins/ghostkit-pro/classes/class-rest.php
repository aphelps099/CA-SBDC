<?php
/**
 * Rest API functions
 *
 * @package ghostkit-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class GhostKit_Rest_Pro
 */
class GhostKit_Rest_Pro extends WP_REST_Controller {
    /**
     * Namespace.
     *
     * @var string
     */
    protected $namespace = 'ghostkit/v';

    /**
     * Version.
     *
     * @var string
     */
    protected $version = '1';

    /**
     * GhostKit_Rest constructor.
     */
    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Register rest routes.
     */
    public function register_routes() {
        $namespace = $this->namespace . $this->version;

        // Get Custom Fonts.
        register_rest_route(
            $namespace,
            '/get_custom_fonts/',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_custom_fonts' ),
                'permission_callback' => array( $this, 'get_custom_fonts_permission' ),
            )
        );

        // Get Typekit.
        register_rest_route(
            $namespace,
            '/get_typekit/',
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array( $this, 'get_typekit' ),
                'permission_callback' => array( $this, 'get_typekit_permission' ),
            )
        );

        // Update Custom Fonts.
        register_rest_route(
            $namespace,
            '/update_custom_fonts/',
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array( $this, 'update_custom_fonts' ),
                'permission_callback' => array( $this, 'update_custom_fonts_permission' ),
            )
        );

        // Get Breakpoints.
        register_rest_route(
            $namespace,
            '/get_breakpoints/',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_breakpoints' ),
                'permission_callback' => array( $this, 'get_breakpoints_permission' ),
            )
        );

        // Update Breakpoints.
        register_rest_route(
            $namespace,
            '/update_breakpoints/',
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array( $this, 'update_breakpoints' ),
                'permission_callback' => array( $this, 'update_breakpoints_permission' ),
            )
        );
    }

    /**
     * Get read fonts permissions.
     *
     * @return bool
     */
    public function get_custom_fonts_permission() {
        if ( ! current_user_can( 'edit_theme_options' ) ) {
            return $this->error( 'user_dont_have_permission', __( 'User don\'t have permissions to change options.', 'ghostkit-pro' ), true );
        }

        return true;
    }

    /**
     * Get read typekit permissions.
     *
     * @return bool
     */
    public function get_typekit_permission() {
        if ( ! current_user_can( 'edit_theme_options' ) ) {
            return $this->error( 'user_dont_have_permission', __( 'User don\'t have permissions to change options.', 'ghostkit-pro' ), true );
        }

        return true;
    }

    /**
     * Get edit fonts permissions.
     *
     * @return bool
     */
    public function update_custom_fonts_permission() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return $this->error( 'user_dont_have_permission', __( 'User don\'t have permissions to change options.', 'ghostkit-pro' ), true );
        }

        return true;
    }

    /**
     * Get default breakpoints permissions.
     *
     * @return bool
     */
    public function get_breakpoints_permission() {
        if ( ! current_user_can( 'edit_theme_options' ) ) {
            return $this->error( 'user_dont_have_permission', __( 'User don\'t have permissions to change options.', 'ghostkit-pro' ), true );
        }

        return true;
    }

    /**
     * Get default breakpoints permissions.
     *
     * @return bool
     */
    public function update_breakpoints_permission() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return $this->error( 'user_dont_have_permission', __( 'User don\'t have permissions to change options.', 'ghostkit-pro' ), true );
        }

        return true;
    }

    /**
     * Get custom fonts.
     *
     * @return mixed
     */
    public function get_custom_fonts() {
        $fonts = get_option( 'ghostkit_fonts_settings', array() );

        if ( ! isset( $fonts['adobe'] ) || empty( $fonts['adobe'] ) ) {
            $fonts['adobe'] = '';
        }

        if ( is_array( $fonts ) ) {
            return $this->success( $fonts );
        } else {
            return $this->error( 'no_fonts', __( 'Custom fonts not found.', 'ghostkit-pro' ) );
        }
    }

    /**
     * Update fonts.
     *
     * @param WP_REST_Request $request  request object.
     *
     * @return mixed
     */
    public function update_custom_fonts( WP_REST_Request $request ) {
        $new_fonts      = $request->get_param( 'data' );
        $updated_option = array();

        if ( isset( $new_fonts['adobe'] ) && is_array( $new_fonts['adobe'] ) ) {
            if ( isset( $new_fonts['adobe']['token'] ) && ! empty( $new_fonts['adobe']['token'] ) ) {

                $typekit = new GhostKit_Typekit_Api();

                $new_fonts['adobe']['errors'] = false;
                $new_fonts['adobe']['fonts']  = false;
                $new_fonts['adobe']['kits']   = array();

                $typekit_data = $typekit->get( null, $new_fonts['adobe']['token'], false );

                if ( isset( $typekit_data['errors'] ) ) {
                    $new_fonts['adobe']['errors'] = $typekit_data['errors'];
                    $new_fonts['adobe']['kits']   = false;
                    $new_fonts['adobe']['kit']    = false;
                }

                if ( $typekit_data['kits'] ) {
                    foreach ( $typekit_data['kits'] as $current_kit ) {
                        $kit_data = $typekit->get( $current_kit['id'], $new_fonts['adobe']['token'] );

                        $new_fonts['adobe']['kits'][] = array(
                            'value' => $current_kit['id'],
                            'label' => $kit_data['kit']['name'],
                        );
                    }
                }

                if ( isset( $new_fonts['adobe']['kit'] ) && ! empty( $new_fonts['adobe']['kit'] ) ) {
                    $typekit_data = $typekit->get( $new_fonts['adobe']['kit'], $new_fonts['adobe']['token'], false );

                    if ( isset( $typekit_data['kit'] ) ) {
                        $new_fonts['adobe']['fonts'] = $typekit_data['kit'];
                    }
                }
            } else {
                $new_fonts['adobe']['token'] = '';
                $new_fonts['adobe']['fonts'] = false;
                $new_fonts['adobe']['kit']   = false;
                $new_fonts['adobe']['kits']  = false;
            }
        }

        if ( is_array( $new_fonts ) ) {
            $current_fonts = get_option( 'ghostkit_fonts_settings', array() );

            $updated_option = array_merge( is_array( $current_fonts ) ? $current_fonts : array(), $new_fonts );

            update_option( 'ghostkit_fonts_settings', $updated_option );
        }

        return $this->success( $updated_option );
    }

    /**
     * Get Typekit fonts.
     *
     * @param WP_REST_Request $request  request object.
     * @return mixed
     */
    public function get_typekit( WP_REST_Request $request ) {
        $data = $request->get_param( 'data' );

        if ( isset( $data ) && ! empty( $data ) && is_array( $data ) ) {
            if ( isset( $data['token'] ) && ! empty( $data['token'] ) ) {

                $typekit = new GhostKit_Typekit_Api();

                $data['errors'] = false;
                $data['fonts']  = false;
                $data['kits']   = array();

                $typekit_data = $typekit->get( null, $data['token'], false );

                if ( isset( $typekit_data['errors'] ) ) {
                    $data['errors'] = $typekit_data['errors'];
                    $data['kits']   = false;
                    $data['kit']    = false;
                }

                if ( isset( $typekit_data['kits'] ) ) {
                    foreach ( $typekit_data['kits'] as $current_kit ) {
                        $kit_data       = $typekit->get( $current_kit['id'], $data['token'] );
                        $data['kits'][] = array(
                            'value' => $current_kit['id'],
                            'label' => $kit_data['kit']['name'],
                        );
                    }
                }

                if ( isset( $data['kit'] ) && ! empty( $data['kit'] ) ) {
                    $typekit_data = $typekit->get( $data['kit'], $data['token'] );

                    if ( isset( $typekit_data['kit'] ) ) {
                        $data['fonts'] = $typekit_data['kit'];
                    }
                }
            } else {
                $data['token'] = '';
                $data['fonts'] = false;
                $data['kit']   = false;
                $data['kits']  = false;
            }
        }

        return $this->success( $data );
    }

    /**
     * Get Breakpoints.
     *
     * @return mixed
     */
    public function get_breakpoints() {
        if ( ! class_exists( 'GhostKit_Breakpoints' ) && function_exists( 'ghostkit' ) ) {
            require_once ghostkit()->plugin_path . 'classes/class-breakpoints.php';
        }

        $default_breakpoints = GhostKit_Breakpoints::get_default_breakpoints();
        $saved_breakpoints   = get_option( 'ghostkit_pro_breakpoints', $default_breakpoints );

        $breakpoints = array(
            'default_breakpoints' => $default_breakpoints,
            'saved_breakpoints'   => $saved_breakpoints,
        );

        if ( is_array( $breakpoints ) ) {
            return $this->success( wp_json_encode( $breakpoints ) );
        } else {
            return $this->error( 'no_breakpoints', __( 'Breakpoints not found.', 'ghostkit-pro' ) );
        }
    }

    /**
     * Update breakpoints.
     *
     * @param WP_REST_Request $request  request object.
     *
     * @return mixed
     */
    public function update_breakpoints( WP_REST_Request $request ) {
        $data = $request->get_param( 'data' );

        if (
            isset( $data ) &&
            ! empty( $data )
        ) {
            $breakpoints = json_decode( $data, true );
            $errors      = array();

            if ( $breakpoints['xs'] >= $breakpoints['sm'] ) {
                $errors[] = $this->error( 'xs', __( 'The value of breakpoint Extra Small cannot be more than Small.', 'ghostkit-pro' ) );
            }
            if ( $breakpoints['xs'] < 1 ) {
                $errors[] = $this->error( 'xs', __( 'The value of breakpoint Extra Small cannot be less than 1.', 'ghostkit-pro' ) );
            }

            if ( $breakpoints['sm'] >= $breakpoints['md'] ) {
                $errors[] = $this->error( 'sm', __( 'The value of breakpoint Small cannot be more than Medium.', 'ghostkit-pro' ) );
            }
            if ( $breakpoints['sm'] <= $breakpoints['xs'] ) {
                $errors[] = $this->error( 'sm', __( 'The value of breakpoint Small cannot be less than Extra Small.', 'ghostkit-pro' ) );
            }

            if ( $breakpoints['md'] >= $breakpoints['lg'] ) {
                $errors[] = $this->error( 'md', __( 'The value of breakpoint Medium cannot be more than Large.', 'ghostkit-pro' ) );
            }
            if ( $breakpoints['md'] <= $breakpoints['sm'] ) {
                $errors[] = $this->error( 'md', __( 'The value of breakpoint Medium cannot be less than Small.', 'ghostkit-pro' ) );
            }

            if ( $breakpoints['lg'] >= 3840 ) {
                $errors[] = $this->error( 'lg', __( 'The value of breakpoint Large cannot be more than 3840.', 'ghostkit-pro' ) );
            }
            if ( $breakpoints['lg'] <= $breakpoints['md'] ) {
                $errors[] = $this->error( 'lg', __( 'The value of breakpoint Large cannot be less than Medium.', 'ghostkit-pro' ) );
            }

            if ( empty( $errors ) ) {
                update_option( 'ghostkit_pro_breakpoints', $breakpoints );
            }
        }

        if ( empty( $errors ) ) {
            return $this->success( $data );
        } else {
            return $errors;
        }
    }

    /**
     * Success rest.
     *
     * @param mixed $response response data.
     * @return mixed
     */
    public function success( $response ) {
        return new WP_REST_Response(
            array(
                'success'  => true,
                'response' => $response,
            ),
            200
        );
    }

    /**
     * Error rest.
     *
     * @param mixed   $code       error code.
     * @param mixed   $response   response data.
     * @param boolean $true_error use true error response to stop the code processing.
     * @return mixed
     */
    public function error( $code, $response, $true_error = false ) {
        if ( $true_error ) {
            return new WP_Error( $code, $response, array( 'status' => 401 ) );
        }

        return new WP_REST_Response(
            array(
                'error'      => true,
                'success'    => false,
                'error_code' => $code,
                'response'   => $response,
            ),
            401
        );
    }
}
new GhostKit_Rest_Pro();
