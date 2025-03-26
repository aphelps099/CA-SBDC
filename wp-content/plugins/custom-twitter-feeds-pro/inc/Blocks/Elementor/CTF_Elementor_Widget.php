<?php

namespace TwitterFeed\Blocks\Elementor;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use TwitterFeed\Builder\CTF_Db;
use TwitterFeed\Blocks\CTF_Blocks_Integration;

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

class CTF_Elementor_Widget extends Widget_Base {

	public function get_name() {
        return 'ctf-widget';
    }
    public function get_title() {
        return esc_html__('Twitter Feed', 'custom-twitter-feeds');
    }
    public function get_icon() {
        return 'sb-elem-icon sb-elem-twitter';
    }
    public function get_categories() {
        return array('smash-balloon');
    }
    public function get_script_depends() {
        return [
            'ctfscripts',
            'ctf-elementor-preview'
        ];
    }

    protected function register_controls() {
    	/********************************************
                    CONTENT SECTION
        ********************************************/
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__('Twitter Feed Settings', 'custom-twitter-feeds'),
            ]
        );
        $this->add_control(
            'feed_id',
            [
                'label' => esc_html__('Select a Feed', 'custom-twitter-feeds'),
                'type' => 'ctf_feed_control',
                'label_block' => true,
                'dynamic' => ['active' => true],
                'options' => CTF_Db::elementor_feeds_query(),
            ]
        );
        $this->end_controls_section();
    }

    protected function render() {
    	$settings = $this->get_settings_for_display();
    	if (isset($settings['feed_id']) && !empty($settings['feed_id'])) {
            $feed_id = (int) $settings['feed_id'];
    		$output = do_shortcode( shortcode_unautop( '[custom-twitter-feeds feed='. $feed_id .']' ) );
    	} else {
            $output = is_admin() ? CTF_Blocks_Integration::get_widget_cta() : '';
    	}
        echo apply_filters('sby_output', $output, $settings);
    }
}