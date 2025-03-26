<?php
/**
 * Class CtfAdmin
 *
 * Uses the Settings API to create easily customizable settings pages and tabs
 */

namespace TwitterFeed;
use TwitterFeed\Admin\CTF_Notifications;

// Don't load directly
if (!defined('ABSPATH')) {
    die('-1');
}

class CtfAdmin {
    public function __construct() {
        add_action('admin_menu', array(
            $this,
            'add_menu'
        ));


    }

    public function add_menu() {
        $cap = current_user_can( 'manage_twitter_feed_options' ) ? 'manage_twitter_feed_options' : 'manage_options';

        $cap = apply_filters( 'ctf_settings_pages_capability', $cap );

        $data_x_icon = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAiIGhlaWdodD0iMzAiIHZpZXdCb3g9IjAgMCAzMCAzMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJNMjEuMTE2MSA2LjI3MzQ0SDI0LjIyODlMMTcuNDI4NCAxNC4wNDU5TDI1LjQyODYgMjQuNjIyNUgxOS4xNjQ1TDE0LjI1ODMgMTguMjA3OUw4LjY0NDQgMjQuNjIyNUg1LjUyOTc2TDEyLjgwMzUgMTYuMzA5TDUuMTI4OTEgNi4yNzM0NEgxMS41NTJMMTUuOTg2OCAxMi4xMzY3TDIxLjExNjEgNi4yNzM0NFpNMjAuMDIzNiAyMi43NTk0SDIxLjc0ODRMMTAuNjE0OCA4LjAzODcxSDguNzYzOUwyMC4wMjM2IDIyLjc1OTRaIiBmaWxsPSJibGFjayIvPjwvc3ZnPg==';

        $ctf_notifications = new CTF_Notifications();
        $notifications = $ctf_notifications->get();

        $notice_bubble = '';
        if ( empty( $notice ) && ! empty( $notifications ) && is_array( $notifications ) ) {
            $notice_bubble = ' <span class="ctf-notice-alert"><span>'.count( $notifications ).'</span></span>';
        }

        add_menu_page(
            __( 'Twitter Feed', 'custom-twitter-feed' ),
            __( 'Twitter Feed', 'custom-twitter-feed' ). $notice_bubble,
            $cap,
            'custom-twitter-feeds',
            array( $this, 'sb_twitter_settings_page' ),
            ctf_should_rebrand_to_x() ? $data_x_icon : 'dashicons-twitter'
        );
    }
    function sb_twitter_settings_page() {
        $link = admin_url( 'admin.php?page=ctf-settings' );
        ?>
        <div id="ctf-admin">
            <div class="ctf_notice">
                <strong><?php esc_html_e( 'The Twitter Feed Settings page has moved!', 'custom-twitter-feeds' ); ?></strong>
                <a href="<?php echo esc_url( $link ); ?>"><?php esc_html_e( 'Click here to go to the new page.', 'custom-twitter-feeds' ); ?></a>
            </div>
        </div>
    <?php
    }
}

