<?php 

namespace SB\SocialWall\Admin;
use SB\SocialWall\Admin\Tabs\Customize_Tab;
use SB\SocialWall\Admin\Tabs\Settings_Tab;

class SW_Feed_Builder {

    public static function customizer_builder_data() {
        return array(
            array(
                'id' => 'sb-customize-tab',
                'name' => 'Customize',
                'sections' => Customize_Tab::get_sections()
            ),
            array(
                'id' => 'sb-settings-tab',
                'name' => 'Settings',
                'sections' => Settings_Tab::get_sections()
            ),
        );
    }

    public static function customizer_feed_data( $feed_id = null, $preview_settings = null ) {
        if ( is_null( $feed_id ) ) {
            $feed_id = $_GET['feed_id'];
        }
        $feed_saver = new Feed_Saver( $feed_id );
        $settings = $preview_settings ? $preview_settings : $feed_saver->get_feed_settings();
        $feed_plugins = $feed_saver->get_feed_plugins();
        $feed_db_data = $feed_saver->get_feed_db_data();

        $sw_shortcodes_content = self::build_sw_plugins_shortcode($feed_plugins);
        $feed_posts = sbsw_feed_init( [], $sw_shortcodes_content, 'return', $settings );
        
        return array(
            'feed_info' => array(
                'feed_name' => isset( $_POST['feedName'] ) ? sanitize_text_field( $_POST['feedName'] ) : $feed_db_data['feed_name'],
                'feed_style' => '',
                'id' => $feed_id,
            ),
            'settings' => $settings,
            'posts' => $feed_posts,
            'wall_plugins' => $feed_plugins
        );
    }

    /**
     * Build the shortcode content for the SW plugin out of the specific feed plugins
     * 
     * @since 2.0
     */
    public static function build_sw_plugins_shortcode( $plugins ) {
        $content = '';

        if ( isset( $plugins['facebook'] ) ) {
            $content .= sprintf('[custom-facebook-feed feed=%s]', $plugins['facebook']->id);
        }
        if ( isset( $plugins['instagram'] ) ) {
            $content .= sprintf('[instagram-feed feed=%s]', $plugins['instagram']->id);
        }
        if ( isset( $plugins['twitter'] ) ) {
            $content .= sprintf('[custom-twitter-feeds feed=%s]', $plugins['twitter']->id);
        }
        if ( isset( $plugins['youtube'] ) ) {
            $content .= sprintf('[youtube-feed feed=%s]', $plugins['youtube']->id);
        }
		if (isset($plugins['tiktok'])) {
			$content .= sprintf('[tiktok-feeds feed=%s]', $plugins['tiktok']->id);
		}

        return $content;
    }
}
