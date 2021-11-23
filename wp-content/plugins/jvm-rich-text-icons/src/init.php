<?php
class JVM_Richtext_icons {
    /**
     * This plugin's instance.
     *
     * @var JVM_Richttext_icons
     */
    private static $instance;

    /**
     * Registers the plugin.
     */
    public static function register() {
        if ( null === self::$instance ) {
            self::$instance = new JVM_Richtext_icons();
        }
    }


    /**
     * The Constructor.
     */
    private function __construct() {
        add_filter( 'block_editor_settings_all', array( $this, 'block_editor_settings' ), 10, 2 );
        add_action( 'init', array( $this, 'load_css') );
        add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_assets') );
    }

    /**
     * Filters the settings to pass to the block editor.
     *
     * @param array  $editor_settings The editor settings.
     * @param object $post The post being edited.
     *
     * @return array Returns updated editors settings.
     */
    public function block_editor_settings( $editor_settings, $post ) {
        if ( ! isset( $editor_settings['jvm_richtext_icons'] ) ) {

            $editor_settings['jvm_richtext_icons'] = [
                'formats'    => array(
                    'name'  => 'formats',
                    'label' => __( 'Formats', 'block-options' ),
                    'items' => array(
                        'icons'        => array(
                            'name'  => 'icon',
                            'label' => __( 'Insert icon', 'jvm-richtext-icons' ),
                            'value' => true,
                        )
                    )
                )
            ];

        }
        return $editor_settings;
    }


    /**
     * Enqueue Gutenberg block assets for both admin backend.
     */
    public function load_admin_assets($hook_suffix) {
        if( 'post.php' == $hook_suffix || 'post-new.php' == $hook_suffix ) {
            // Register block editor script for backend.
            wp_enqueue_script(
                'jvm-richhtext-icons-js', // Handle.
                plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ), // Block.build.js: We register the block here. Built with Webpack.
                array( 'wp-i18n', 'wp-element', 'wp-editor' ), // Dependencies, defined above.
                null, // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ), // Version: filemtime — Gets file modification time.
                true // Enqueue the script in the footer.
            );

            // Register block editor styles for backend.
            wp_enqueue_style(
                'jvm-richhtext-icons-editor-css', // Handle.
                plugins_url( 'dist/editor.css', dirname( __FILE__ ) ), // Block editor CSS.
                array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
                filemtime( plugin_dir_path( __DIR__ ) . 'dist/editor.css' ) // Version: File modification time.
            );

            $icons = JVM_Richtext_icons::get_icons();
            $base_class = JVM_Richtext_icons::get_class_prefix();
            wp_localize_script(
                'jvm-richhtext-icons-js',
                'jvm_richtext_icon_settings', // Array containing dynamic data for a JS Global.
                [
                    'iconset' => $icons,
                    'base_class' => $base_class
                ]
            );
        }
    }

    /**
     * Enqueue Gutenberg block assets for both frontend + backend.
     */
    public static function load_css() {
        // Icon set CSS (font awesome 4.7 is shipped by default).
        $fontCssFile = plugins_url( 'dist/fa-4.7/font-awesome.min.css', dirname( __FILE__ ) );
        $fontCssFile = apply_filters('jvm_richtext_icons_css_file', $fontCssFile);

        if (!empty($fontCssFile)) {
            wp_enqueue_style(
                'jvm-richhtext-icons-icon-font-css', // Handle.
                $fontCssFile
            );
        }
    }

    /**
     * Get the class prefix for the css
     * @return [string]
     */
    public static function get_class_prefix() {
        return apply_filters('jvm_richtext_icons_base_class', 'icon');
    }

    /**
     * Get the icon conifig
     * @return [array]
     */
    public static function get_icons() {
        // WP Localized globals. Use dynamic PHP stuff in JavaScript via `cgbGlobal` object.
        $iconFile = plugin_dir_path( __DIR__ ).'src/icons.json';
        $iconFile = apply_filters('jvm_richtext_icons_iconset_file', $iconFile);

        $icons = [];
        if (file_exists($iconFile)) {
            $iconData = file_get_contents($iconFile);
            $data = json_decode($iconData);
            $icons = [];
            // Check if data is fontello format
            if (isset($data->glyphs)) {
                foreach($data->glyphs as $g) {
                    $icons[] = $data->css_prefix_text.$g->css;
                }
            }else {
                $icons = $data;
            }

            $icons = apply_filters('jvm_richtext_icons_iconset', $icons);            
        }

        return $icons;
    }
}

JVM_Richtext_icons::register();