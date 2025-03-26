<?php

/**
 * Builder Customizer Tab
 *
 *
 * @since 2.0
 */
namespace SB\SocialWall\Admin\Tabs;

if(!defined('ABSPATH'))	exit;

class SW_Builder_Customizer_Tab {

	/**
	 * Get Tabs Data
	 *
	 *
	 * @since 2.0
	 * @access public
	 *
	 * @return array
	*/
	public static function get_customizer_tabs(){
		return [
			'customize' => [
				'id' 		=> 'customize',
				'heading' 	=> __( 'Customize', 'social-wall' ),
				'sections'	=> Customize_Tab::get_sections()
			],
			'settings' => [
				'id' 		=> 'settings',
				'heading' 	=> __( 'Settings', 'social-wall' ),
				'sections'	=> Settings_Tab::get_sections()
			]
		];
	}
}