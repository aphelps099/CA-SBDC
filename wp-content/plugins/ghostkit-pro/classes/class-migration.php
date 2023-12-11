<?php
/**
 * Migrations
 *
 * @package ghostkit-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class GhostKit_Pro_Migration
 */
class GhostKit_Pro_Migration {
    /**
     * The version.
     *
     * @var string
     */
    protected $version = '1.7.3';

    /**
     * Initial version.
     *
     * @var string
     */
    protected $initial_version = '1.5.0';

    /**
     * LazyBlocks_Extend constructor.
     */
    public function __construct() {
        if ( is_admin() ) {
            add_action( 'admin_init', array( $this, 'init' ), 3 );
        } else {
            add_action( 'wp', array( $this, 'init' ), 3 );
        }
    }

    /**
     * Init.
     */
    public function init() {
        // Migration code added after `$this->initial_version` plugin version.
        $saved_version   = get_option( 'gkt_pro_db_version', $this->initial_version );
        $current_version = $this->version;

        foreach ( $this->get_migrations() as $migration ) {
            if ( version_compare( $saved_version, $migration['version'], '<' ) ) {
                call_user_func( $migration['cb'] );
            }
        }

        if ( version_compare( $saved_version, $current_version, '<' ) ) {
            update_option( 'gkt_pro_db_version', $current_version );
        }
    }

    /**
     * Get all available migrations.
     *
     * @return array
     */
    public function get_migrations() {
        return array(
            array(
                'version' => '1.5.1',
                'cb'      => array( $this, 'v_1_5_1' ),
            ),
        );
    }

    /**
     * Rename Adobe Fonts options.
     */
    public function v_1_5_1() {
        $adobe_fonts = get_option( 'ghostkit_fonts_settings', array() );

        if ( is_array( $adobe_fonts ) && ! empty( $adobe_fonts ) && isset( $adobe_fonts['ghostkit_fonts_settings'] ) ) {
            if ( is_string( $adobe_fonts['ghostkit_fonts_settings'] ) ) {
                $adobe_fonts['adobe'] = json_decode( $adobe_fonts['ghostkit_fonts_settings'], true );
            }

            unset( $adobe_fonts['ghostkit_fonts_settings'] );

            update_option( 'ghostkit_fonts_settings', $adobe_fonts );
        }
    }
}

new GhostKit_Pro_Migration();
