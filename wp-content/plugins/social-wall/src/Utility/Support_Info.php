<?php 

namespace SB\SocialWall\Utility;

class Support_Info {

    public static function get_system_info() {
        $output = '';
        $output .= self::get_site_n_server_info();
        $output .= self::get_active_plugins_info();
        $output .= self::get_cron_events();
        $output .= self::get_cron_cache();
        $output .= self::get_errors();

        return $output;
    }

    public static function get_site_n_server_info() {
        $allow_url_fopen = ini_get( 'allow_url_fopen' ) ? "Yes" : "No";
        $php_curl = is_callable('curl_init') ? "Yes" : "No";
        $php_json_decode = function_exists("json_decode") ? "Yes" : "No";
        $php_ssl = in_array('https', stream_get_wrappers()) ? "Yes" : "No";

        $output = '## SITE/SERVER INFO: ##' . "<br/>";
        $output .= 'Plugin Version:' . self::get_whitespace( 11 ) . SBSW_PLUGIN_EDD_NAME . ' ' . SWVER . "<br/>";
        $output .= 'Site URL:' . self::get_whitespace( 17 ) . site_url() . "<br/>";
        $output .= 'Home URL:' . self::get_whitespace( 17 ) . home_url() . "<br/>";
        $output .= 'WordPress Version:' . self::get_whitespace( 8 ) . get_bloginfo( 'version' ) . "<br/>";
        $output .= 'PHP Version:' . self::get_whitespace( 14 ) . PHP_VERSION . "<br/>";
        $output .= 'Web Server Info:' . self::get_whitespace( 10 ) . $_SERVER['SERVER_SOFTWARE'] . "<br/>";
        $output .= 'PHP allow_url_fopen:' . self::get_whitespace( 6 ) . $allow_url_fopen . "<br/>";
        $output .= 'PHP cURL:' . self::get_whitespace( 17 ) . $php_curl . "<br/>";
        $output .= 'JSON:' . self::get_whitespace( 21 ) . $php_json_decode . "<br/>";
        $output .= 'SSL Stream:' . self::get_whitespace( 15 ) . $php_ssl . "<br/>";

        return $output;
    }


    /**
     * Get Active Plugins
     *
     * @since 2.0
     *
     * @return string
     */
    public static function get_active_plugins_info() {
        $plugins = get_plugins();
        $active_plugins = get_option('active_plugins');
        $output = "<br/> ## ACTIVE PLUGINS: ## <br/>";

        foreach ( $plugins as $plugin_path => $plugin ) {
            if ( in_array( $plugin_path, $active_plugins ) ) {
                $output .= $plugin['Name'] . ': ' . $plugin['Version'] ."<br/>";
            }
        }

        $output .= "</br>";

        return $output;
    }

    /**
     * Get Cron Events
     *
     * @since 2.0
     *
     * @return string
     */
    public static function get_cron_events() {
        $output = "<br/> ## CRON EVENTS: ## <br/>";

        $cron = _get_cron_array();
        foreach ( $cron as $key => $data ) {
            $is_target = false;
            foreach ( $data as $key2 => $val ) {
                if ( strpos( $key2, 'sbsw' ) !== false ) {
                    $is_target = true;
                    $output .= $key2;
                    $output .= "\n";
                }
            }
            if ( $is_target) {
                $output .= date( "Y-m-d H:i:s", $key );
                $output .= "\n";
                $output .= 'Next Scheduled: ' . ((int)$key - time())/60 . ' minutes';
                $output .= "\n\n";
            }
        }

        $output .= "</br>";

        return $output;
    }

    /**
     * Get Cron Cache
     *
     * @since 2.0
     *
     * @return string
     */
    public static function get_cron_cache() {
        $output = "<br/> ## CRON CACHE: ## <br/>";
        $cron_report = get_option( 'sbsw_cron_report', array() );
        if ( ! empty( $cron_report ) ) {
            $output .= var_export( $cron_report, true );
        }
        echo "\n";

        $output .= "<br/>";

        return $output;
    }

    /**
     * Get Errors
     *
     * @since 2.0
     *
     * @return string
     */
    public static function get_errors() {
        $output = "<br/> ## ERRORS: ## <br/>";
        $errors = array();
        if ( ! empty( $errors ) ) :
            foreach ( $errors as $type => $error ) :
                $output .= $type . ': ' . str_replace( array( '<p>', '<b>', '</p>', '</b>' ), ' ', $error[1] ) . "\n";
            endforeach;
        endif;

        return $output;
    }

    /**
     * Get Whitespace
     *
     * @since 2.0
     *
     * @param int $times
     *
     * @return string
     */
    public static function get_whitespace( $times ) {
        return str_repeat('&nbsp;', $times );
    }
}