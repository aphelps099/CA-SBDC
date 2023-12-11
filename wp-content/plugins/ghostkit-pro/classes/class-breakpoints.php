<?php
/**
 * Breakpoints.
 *
 * @package ghostkit-pro
 */

if ( ! class_exists( 'GhostKit_Breakpoints' ) && function_exists( 'ghostkit' ) ) {
    require_once ghostkit()->plugin_path . 'classes/class-breakpoints.php';
}

if ( ! class_exists( 'GhostKit_Scss_Compiler' ) && function_exists( 'ghostkit' ) ) {
    require_once ghostkit()->plugin_path . 'classes/class-scss-compiler.php';
}

if ( class_exists( 'GhostKit_Breakpoints' ) ) {
    /**
     * GhostKit_PRO_Breakpoints class
     */
    class GhostKit_PRO_Breakpoints extends GhostKit_Breakpoints {
        /**
         * Database saved hash option Name.
         *
         * @var string
         */
        protected $database_saved_hash_option_name = 'ghostkit_pro_saved_breakpoints_hash';

        /**
         * Plugin Name.
         *
         * @var string
         */
        protected $plugin_name = 'ghostkit-pro';

        /**
         * Scss Configurations.
         *
         * @var array
         */
        protected $scss_configs = array(
            'blocks/style.scss',
            'blocks/editor.scss',
            'blocks/*/style.scss',
        );

        /**
         * Saved Breakpoints from Plugin Settings page.
         *
         * @var array
         */
        private $saved_breakpoints;

        /**
         * Plugin version.
         *
         * @var string
         */
        protected $plugin_version = '1.7.3';

        /**
         * GhostKit_PRO_Breakpoints constructor.
         */
        public function __construct() {
            $this->saved_breakpoints = get_option( 'ghostkit_pro_breakpoints', array() );

            parent::__construct();

            add_filter( 'gkt_breakpoint_xs', array( $this, 'set_breakpoint_xs' ), 99 );
            add_filter( 'gkt_breakpoint_sm', array( $this, 'set_breakpoint_sm' ), 99 );
            add_filter( 'gkt_breakpoint_md', array( $this, 'set_breakpoint_md' ), 99 );
            add_filter( 'gkt_breakpoint_lg', array( $this, 'set_breakpoint_lg' ), 99 );
        }

        /**
         * Get plugin path.
         *
         * @return string
         */
        public function get_plugin_path() {
            return ghostkit_pro()->plugin_path;
        }

        /**
         * Get plugin url.
         *
         * @return string
         */
        public function get_plugin_url() {
            return ghostkit_pro()->plugin_url;
        }

        /**
         * Set value of Breakpoint xs option.
         *
         * @param int $default_xs - Default xs breakpoint.
         * @return int
         */
        public function set_breakpoint_xs( $default_xs ) {
            $xs = isset( $this->saved_breakpoints['xs'] ) ? $this->saved_breakpoints['xs'] : $default_xs;
            return (int) $xs;
        }

        /**
         * Set value of Breakpoint sm option.
         *
         * @param int $default_sm - Default sm breakpoint.
         * @return int
         */
        public function set_breakpoint_sm( $default_sm ) {
            $sm = isset( $this->saved_breakpoints['sm'] ) ? $this->saved_breakpoints['sm'] : $default_sm;
            return (int) $sm;
        }

        /**
         * Set value of Breakpoint md option.
         *
         * @param int $default_md - Default md breakpoint.
         * @return int
         */
        public function set_breakpoint_md( $default_md ) {
            $md = isset( $this->saved_breakpoints['md'] ) ? $this->saved_breakpoints['md'] : $default_md;
            return (int) $md;
        }

        /**
         * Set value of Breakpoint lg option.
         *
         * @param int $default_lg - Default lg breakpoint.
         * @return int
         */
        public function set_breakpoint_lg( $default_lg ) {
            $lg = isset( $this->saved_breakpoints['lg'] ) ? $this->saved_breakpoints['lg'] : $default_lg;
            return (int) $lg;
        }
    }
    new GhostKit_PRO_Breakpoints();
}
