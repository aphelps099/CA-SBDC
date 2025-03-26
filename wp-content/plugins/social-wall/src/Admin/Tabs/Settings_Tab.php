<?php
/**
 * Customizer Tab
 *
 *
 * @since 2.0
 */
namespace SB\SocialWall\Admin\Tabs;
use SB\SocialWall\Admin\SW_Feed_Builder;

if(!defined('ABSPATH'))	exit;

class Settings_Tab {

    /**
	 * Get Customize Tab Sections
	 *
	 *
	 * @since 2.0
	 * @access public
	 *
	 * @return array
	*/
	static function get_sections(){
		return [
			'settings_feedtype_sources' => [
				'heading' 	=> __( 'Sources', 'social-wall' ),
				'icon' 		=> 'sources',
				'controls'	=> self::get_settings_sources_controls()
			],
			'settings_filters' => [
				'heading' 	=> __( 'Filters', 'social-wall' ),
				'description' 	=> __( 'Upgrade to Pro to show or hide tweets that meet a specific criteria, or are specified by an ID.', 'social-wall' ),
				'proLabel'		=> true,
				'checkExtensionPopup' => 'advancedFilters',
				'icon' 		=> 'filter',
				'separator'	=> 'none',
				'controls'	=> self::get_settings_filters_controls()
			],
			'settings_clear_cache' => [
				'heading' 	=> __( 'Clear Feed Cache', 'social-wall' ),
				'id'		=> 'clear_cache',
				'icon'		=> 'clearcache',
				'controls'	=> self::clear_cache_control()
			]
		];
	}



	/**
	 * Get Settings Tab Feed Type Sources
	 * @since 2.0
	 * @return array
	*/
	static function get_settings_sources_controls(){
		return [
			[
				'type' 				=> 'customview',
				'viewId'			=> 'sources'
			],
		];
	}

	/**
	 * Get Settings Tab Filters Section
	 * @since 2.0
	 * @return array
	*/
	static function get_settings_filters_controls(){
		return [
			[//Number of Columns
                'type'      => 'group',
                'id'        => 'filter_group',
                'heading'   => __('By Words', 'social-wall'),
                'controls'  => [
					[
						'type' 				=> 'textarea',
						'id' 				=> 'includewords',
						'checkExtensionDimmed'	=> 'advancedFilters',
						'checkExtensionPopup' => 'advancedFilters',
						'disabledInput'		=> true,
						'heading' 			=> __( 'Only show posts containing', 'social-wall' ),
						'placeholder' 			=> __( 'Add words here to only show posts containing these words', 'social-wall' ),
						'tooltip' 			=> __( 'Only show posts containing', 'social-wall' ),
						'labelStrong'		=> 'true',
						'stacked'			=> 'true',
						'ajaxAction'		=> 'feedFlyPreview'
					],
					[
						'type' 				=> 'textarea',
						'id' 				=> 'excludewords',
						'checkExtensionDimmed'	=> 'advancedFilters',
						'checkExtensionPopup' => 'advancedFilters',
						'disabledInput'		=> true,
						'heading' 			=> __( 'Do not show posts containing', 'social-wall' ),
						'placeholder' 			=> __( 'Add words here to hide any posts containing these words', 'social-wall' ),
						'tooltip' 			=> __( 'Do not show posts containing', 'social-wall' ),
						'labelStrong'		=> 'true',
						'stacked'			=> 'true',
						'ajaxAction'		=> 'feedFlyPreview'
					],
                ]
            ],
		];
	}



	/**
	 * Get Settings Tab Advanced Section
	 * @since 2.0
	 * @return array
	*/
	static function get_settings_advanced_controls(){
		return [
			[
				'type' 				=> 'separator',
				'top' 				=> 30,
				'bottom' 			=> 10,
			],
			[
				'type' 				=> 'select',
				'id' 				=> 'multiplier',
				'strongHeading'		=> 'true',
				'heading' 			=> __( 'Tweet Multiplier', 'social-wall' ),
				'tooltip' 			=> __( 'If your feed excludes reply tweets (this is automatic in hashtag/search feeds), the correct number of tweets may not show up. Increasing this number will increase the number of tweets retrieved but will also increase the load time for the feed as well.', 'social-wall' ),
				'options'			=> [
					'1.25' => '1.25',
					'2' => '2',
					'3' => '3',
				]
			],
			[
				'type' 				=> 'separator',
				'top' 				=> 20,
				'bottom' 			=> 10,
			],
			[
				'type' 				=> 'textarea',
				'heading' 			=> __( 'Add Custom CSS Class', 'social-wall' ),
				'id' 				=> 'class',
				'strongHeading'		=> 'true',
				'tooltip'		=> __( 'Add one or more CSS classes, example:  class1, class2', 'social-wall' ),
				'placeholder'		=> __( 'Add one or more CSS classes, example:  class1, class2', 'social-wall' ),
			],
		];
	}

	/**
	 * Get Settings Tab Feed Type Sources
	 * @since 2.0
	 * @return array
	*/
	static function clear_cache_control(){
		return [
			[
				'type' 				=> 'customview',
				'viewId'			=> 'clear_cache'
			],
		];
	}
}
