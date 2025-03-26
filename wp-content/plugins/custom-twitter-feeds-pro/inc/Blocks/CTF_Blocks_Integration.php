<?php

namespace TwitterFeed\Blocks;

use TwitterFeed\Builder\CTF_Db;
use TwitterFeed\Builder\CTF_Feed_Builder;

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

/**
 * CTF_Blocks_Integration Class
 * Common funcions for Elementor/Divi/Gutenberg
 *
 * @since 2.5
 *
 * @return array
*/

class CTF_Blocks_Integration {
	 /**
	 * Get Widget/Module/Block Info
	 *
	 * @since 2.5
	 *
	 * @return array
	 */
	public static function get_widget_info()
    {
        return [
            'plugin'                            => 'twitter',
            'cta_header'                        => esc_html__('Get started with your first feed from br/>Twitter', 'custom-twitter-feeds'),
            'cta_header2'                       => esc_html__('Select a Twitter feed to embed', 'custom-twitter-feeds'),
            'cta_description_free'              => esc_html__('You can display feeds for your Twitter user timeline, hashtag, search and more using the Pro version', 'custom-twitter-feeds'),
            'cta_description_pro'               => esc_html__('You can also add Instagram, Facebook, and YouTube posts into your feed using our Social Wall plugin', 'custom-twitter-feeds'),
            'plugins'                           => (new CTF_Feed_Builder)->install_plugins_popup()
        ];
    }

    /**
	 * Widget CTA
	 *
	 * @since 2.5
	 *
	 * @return HTML
	*/
    public static function get_widget_cta($type = 'dropdown')
    {
        $widget_cta_html = '';
        $feeds_list = CTF_Db::elementor_feeds_query();
        ob_start();
        self::get_widget_cta_html($feeds_list, $type);
        $widget_cta_html .= ob_get_contents();
        ob_get_clean();
        return $widget_cta_html;
    }

    public static function get_widget_cta_html($feeds_list, $type)
    {
        $info = self::get_widget_info();
        $feeds_exist = is_array($feeds_list) && !empty($feeds_list);
        ?>
        <div class="sb-elementor-cta">
            <div class="sb-elementor-cta-img-ctn">
                <div class="sb-elementor-cta-img">
                    <span><?php echo $info['plugins'][$info['plugin']]['svgIcon']; ?></span>
                    <svg class="sb-elementor-cta-logo" width="31" height="39" viewBox="0 0 31 39" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.62525 18.4447C1.62525 26.7883 6.60827 33.9305 13.3915 35.171L12.9954 36.4252L12.5937 37.6973L13.923 37.5843L18.4105 37.2026L20.0997 37.0589L19.0261 35.7468L18.4015 34.9834C24.7525 33.3286 29.3269 26.4321 29.3269 18.4447C29.3269 9.29016 23.2952 1.53113 15.4774 1.53113C7.65975 1.53113 1.62525 9.2899 1.62525 18.4447Z" fill="#FE544F" stroke="white" stroke-width="1.78661"/><path fill-rule="evenodd" clip-rule="evenodd" d="M18.5669 8.05676L19.1904 14.4905L25.6512 14.6761L20.9776 19.0216L24.6689 22.3606L18.4503 23.1916L16.5651 29.4104L13.7026 23.8415L7.92284 26.4899L10.1462 20.5199L4.50931 17.6767L10.5435 15.7361L8.8784 9.79176L14.5871 13.0464L18.5669 8.05676Z" fill="white"/></svg>
                </div>
            </div>
            <h3 class="sb-elementor-cta-heading"><?php echo $feeds_exist ? $info['cta_header2'] : $info['cta_header'] ?></h3>

            <?php if($feeds_exist): ?>
                <div class="sb-elementor-cta-selector">
                    <?php if( $type == 'dropdown' ): ?>
                        <select class="sb-elementor-cta-feedselector">
                            <option><?php echo __('Select', 'custom-twitter-feeds') . ' ' . ucfirst($info['plugin']) . ' '. __('Feed', 'custom-twitter-feeds')?> </option>
                            <?php foreach ($feeds_list as $feed_id => $feed_name): ?>
                                <option value="<?php echo $feed_id ?>"><?php echo $feed_name ?></option>
                            <?php endforeach ?>
                        </select>
                    <?php elseif( $type == 'button' ): ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=ctf-feed-builder' ) ) ?>" rel="noopener noreferrer" class="sb-elementor-cta-btn">
                            <?php echo esc_html__('Create Twitter Feed', 'custom-twitter-feeds'); ?>
                        </a>
                    <?php endif; ?>

                    <span>
                        <?php
                            echo esc_html__('Or create a Feed for', 'custom-twitter-feeds');
                            unset( $info['plugins'][$info['plugin']] );
                            foreach ($info['plugins'] as $name => $plugin):
                                if (isset($plugin['link'])) :
                        ?>
                            <a href="<?php echo esc_url($plugin['link']) ?>" target="_blank"><?php echo $name ?></a>
                        <?php   
                                endif; 
                            endforeach 
                        ?>
                    </span>
                </div>
            <?php else: ?>
                <a href="" class="sb-elementor-cta-btn"><?php echo esc_html__('Create', 'custom-twitter-feeds') . ' ' . ucfirst($info['plugin']) . ' ' . esc_html__('Feed', 'custom-twitter-feeds') ?></a>
            <?php endif; ?>

            <div class="sb-elementor-cta-desc">
                <strong><?php echo esc_html__('Did you Know?', 'custom-twitter-feeds') ?></strong>
                <span>
                    <?php echo $info['cta_description_pro']; ?>
                </span>
            </div>
        </div>
        <?php
    }
}