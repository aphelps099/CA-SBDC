<?php
/**
 * Plugin Name:       Ghost Kit Pro
 * Description:       Professional addon, that extends the functionality of Ghost Kit plugin
 * Version:           1.7.3
 * Requires at least: 5.8
 * Requires PHP:      7.2
 * Author:            Ghost Kit Team
 * Author URI:        https://ghostkit.io/?utm_source=plugin_pro&utm_medium=readme&utm_campaign=byline
 * Text Domain:       ghostkit-pro
 *
 * @package ghostkit-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GhostKit_Pro Class
 */
class GhostKit_Pro {

    /**
     * The single class instance.
     *
     * @var $instance
     */
    private static $instance = null;

    /**
     * Plugin ID in EDD
     *
     * @var $plugin_id
     */
    public $plugin_id = '444754';

    /**
     * Path to the plugin main php file.
     *
     * @var $plugin_file_path
     */
    public $plugin_file_path;

    /**
     * Path to the plugin directory
     *
     * @var $plugin_path
     */
    public $plugin_path;

    /**
     * URL to the plugin directory
     *
     * @var $plugin_url
     */
    public $plugin_url;

    /**
     * Plugin name
     *
     * @var $plugin_name
     */
    public $plugin_name;

    /**
     * Plugin version
     *
     * @var $plugin_version
     */
    public $plugin_version;

    /**
     * Plugin slug
     *
     * @var $plugin_slug
     */
    public $plugin_slug;

    /**
     * Plugin name sanitized
     *
     * @var $plugin_name_sanitized
     */
    public $plugin_name_sanitized;

    /**
     * GhostKit constructor.
     */
    public function __construct() {
        /* We do nothing here! */
    }

    /**
     * Main Instance
     * Ensures only one instance of this class exists in memory at any one time.
     */
    public static function instance() {
        if ( ! class_exists( 'GhostKit' ) ) {
            self::$instance = new self();
            self::$instance->init_options_no_free_plugin();

            return false;
        }

        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
            self::$instance->init_options();
            self::$instance->init_hooks();
        }
        return self::$instance;
    }

    /**
     * Init options when no free plugin installed.
     */
    public function init_options_no_free_plugin() {
        $this->plugin_file_path = __FILE__;
        $this->plugin_path      = plugin_dir_path( __FILE__ );
        $this->plugin_url       = plugin_dir_url( __FILE__ );

        // load textdomain.
        load_plugin_textdomain( 'ghostkit-pro', false, basename( dirname( __FILE__ ) ) . '/languages' );

        // notice about installed free plugin.
        add_action( 'admin_notices', array( $this, 'notice_free_plugin_installed' ) );
    }

    /**
     * Notice about installed free plugin.
     */
    public function notice_free_plugin_installed() {
        $screen = get_current_screen();

        if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
            return;
        }

        $plugin_name         = 'ghostkit/class-ghost-kit.php';
        $installed_plugins   = get_plugins();
        $is_plugin_installed = isset( $installed_plugins[ $plugin_name ] );
        $message             = '';

        if ( $is_plugin_installed ) {
            if ( ! current_user_can( 'activate_plugins' ) ) {
                return;
            }

            $activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin_name . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin_name );

            $message .= '<p>' . __( 'Ghost Kit Pro is not working because you need to activate the Ghost Kit plugin.', 'ghostkit-pro' ) . '</p>';
            $message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $activation_url, __( 'Activate Ghost Kit Now', 'ghostkit-pro' ) ) . '</p>';
        } else {
            if ( ! current_user_can( 'install_plugins' ) ) {
                return;
            }

            $install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=ghostkit' ), 'install-plugin_ghostkit' );

            $message .= '<p>' . __( 'Ghost Kit Pro is not working because you need to install the Ghost Kit plugin.', 'ghostkit-pro' ) . '</p>';
            $message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $install_url, __( 'Install Ghost Kit Now', 'ghostkit-pro' ) ) . '</p>';
        }

        echo '<div class="error">' . wp_kses_post( $message ) . '</div>';
    }

    /**
     * Init options
     */
    public function init_options() {
        $this->plugin_file_path = __FILE__;
        $this->plugin_path      = plugin_dir_path( __FILE__ );
        $this->plugin_url       = plugin_dir_url( __FILE__ );

        // load textdomain.
        load_plugin_textdomain( 'ghostkit-pro', false, basename( dirname( __FILE__ ) ) . '/languages' );

        // classes.
        require_once $this->plugin_path . 'classes/class-migration.php';
        require_once $this->plugin_path . 'classes/class-updater.php';
        require_once $this->plugin_path . 'classes/class-rest.php';
        require_once $this->plugin_path . 'classes/class-typekit-api.php';
        require_once $this->plugin_path . 'classes/class-adobe-fonts.php';
        require_once $this->plugin_path . 'classes/class-custom-fonts.php';
        require_once $this->plugin_path . 'classes/class-shapes.php';
        require_once $this->plugin_path . 'classes/class-breakpoints.php';

        // icons.
        require_once $this->plugin_path . 'icon-packs/feather.php';
        require_once $this->plugin_path . 'icon-packs/zwicon.php';
        require_once $this->plugin_path . 'icon-packs/octicons.php';
        require_once $this->plugin_path . 'icon-packs/unicons.php';
        require_once $this->plugin_path . 'icon-packs/7-stroke.php';
        require_once $this->plugin_path . 'icon-packs/heroicons-solid.php';
        require_once $this->plugin_path . 'icon-packs/heroicons-outline.php';
    }


    /**
     * Init hooks
     */
    public function init_hooks() {
        add_action( 'admin_init', array( $this, 'admin_init' ) );

        // work only if Gutenberg available.
        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        // we need to enqueue the main script earlier to let 3rd-party plugins add custom styles support.
        add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ), 9 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_block_assets' ) );

        // Load admin style sheet and JavaScript.
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

        // Remove go pro menu item.
        add_action( 'admin_menu', array( $this, 'admin_menu' ), 11 );
        add_filter( 'plugin_action_links_ghostkit/class-ghost-kit.php', array( $this, 'plugin_action_links' ), 11 );
    }

    /**
     * Register and enqueue admin-specific style sheet.
     */
    public function admin_enqueue_scripts() {

        $screen = get_current_screen();

        if ( 'toplevel_page_ghostkit' !== $screen->id ) {
            return;
        }

        $js_deps = array( 'ghostkit-helper', 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-edit-post', 'wp-compose', 'underscore', 'wp-components', 'lodash', 'jquery' );

        // Ghost Kit Settings.
        wp_enqueue_script(
            'ghostkit-pro-settings',
            $this->plugin_url . 'settings/index.min.js',
            $js_deps,
            '1.7.3',
            true
        );

        wp_localize_script(
            'ghostkit-pro-settings',
            'ghostkitProSettingsData',
            array(
                'api_nonce'        => wp_create_nonce( 'wp_rest' ),
                'api_url'          => rest_url( 'ghostkit/v1/' ),
                'activation_nonce' => wp_create_nonce( 'gkt_pro_activation' ),
                'license_key'      => trim( get_option( 'ghostkit_pro_license_key' ) ),
            )
        );

        // Ghost Kit Components.
        wp_enqueue_script(
            'ghostkit-pro-components',
            $this->plugin_url . 'components/index.min.js',
            $js_deps,
            '1.7.3',
            true
        );
    }

    /**
     * Enqueue editor assets
     */
    public function enqueue_block_editor_assets() {
        $js_deps = array( 'wp-blocks', 'wp-i18n', 'wp-components', 'ghostkit-helper', 'popper' );

        // Ghost Kit Pro.
        wp_enqueue_script(
            'popper',
            plugins_url( 'assets/vendor/@popperjs/core/dist/umd/popper.min.js', __FILE__ ),
            array(),
            '2.5.3',
            true
        );
        wp_enqueue_script(
            'ghostkit-pro-editor',
            plugins_url( 'blocks/index.min.js', __FILE__ ),
            $js_deps,
            '1.7.3',
            true
        );

        // Ghost Kit Pro Styles.
        wp_enqueue_style(
            'ghostkit-pro-editor',
            $this->plugin_url . 'blocks/editor.min.css',
            array(),
            '1.7.3'
        );
        wp_style_add_data( 'ghostkit-pro-editor', 'rtl', 'replace' );
        wp_style_add_data( 'ghostkit-pro-editor', 'suffix', '.min' );

        // Ghost Kit Components.
        wp_enqueue_script(
            'ghostkit-pro-components',
            $this->plugin_url . 'components/index.min.js',
            $js_deps,
            '1.7.3',
            true
        );
    }

    /**
     * Enqueue editor frontend assets
     */
    public function enqueue_block_assets() {
        $css_deps = array();
        $js_deps  = array( 'jquery', 'ghostkit', 'typed', 'popper' );

        // Ghost Kit Pro.
        wp_enqueue_style(
            'ghostkit-pro',
            plugins_url( 'blocks/style.min.css', __FILE__ ),
            $css_deps,
            '1.7.3'
        );
        wp_style_add_data( 'ghostkit-pro', 'rtl', 'replace' );
        wp_style_add_data( 'ghostkit-pro', 'suffix', '.min' );

        wp_enqueue_script(
            'typed',
            plugins_url( 'assets/vendor/typed.js/lib/typed.min.js', __FILE__ ),
            array( 'jquery' ),
            '2.0.11',
            true
        );
        wp_enqueue_script(
            'popper',
            plugins_url( 'assets/vendor/@popperjs/core/dist/umd/popper.min.js', __FILE__ ),
            array(),
            '2.5.3',
            true
        );
        wp_enqueue_script(
            'ghostkit-pro',
            plugins_url( 'assets/js/script.min.js', __FILE__ ),
            $js_deps,
            '1.7.3',
            true
        );
    }

    /**
     * Add admin menu.
     */
    public function admin_menu() {
        // Remove Go Pro link.
        remove_submenu_page( 'ghostkit', 'ghostkit_go_pro' );
    }

    /**
     * Plugin action links.
     *
     * @param Array $links - available links.
     *
     * @return array
     */
    public function plugin_action_links( $links ) {
        // Remove Go Pro link.
        $result = array();

        foreach ( $links as $link ) {
            if ( strpos( $link, 'admin.php?page=ghostkit_go_pro' ) === false ) {
                $result[] = $link;
            }
        }

        return $result;
    }

    /**
     * Init variables
     */
    public function admin_init() {
        // get current plugin data.
        $data                        = get_plugin_data( __FILE__ );
        $this->plugin_name           = $data['Name'];
        $this->plugin_version        = $data['Version'];
        $this->plugin_slug           = plugin_basename( __FILE__, '.php' );
        $this->plugin_name_sanitized = basename( __FILE__, '.php' );
    }
}

/**
 * Function works with the GhostKit_Pro class instance
 *
 * @return object GhostKit_Pro
 */
function ghostkit_pro() {
    return GhostKit_Pro::instance();
}
add_action( 'plugins_loaded', 'ghostkit_pro' );
