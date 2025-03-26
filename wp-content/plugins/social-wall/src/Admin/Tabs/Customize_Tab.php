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

class Customize_Tab{
	public static function get_sections() {
		$sections = array(
            'layout_section' => [
                'heading'     => __('Layout', 'social-wall'),
                'icon'        => 'feed-layout',
                'controls'    => self::get_layout_controls(),
                'separator' => false
            ],
            'color_palette_section' => [
                'heading'     => __('Color Palette', 'social-wall'),
                'icon'        => 'color-scheme',
                'controls'    => self::get_color_palette_controls(),
                'separator' => true
            ],
            'posts_section' => [
                'heading'     => __('Posts', 'social-wall'),
                'description' => __('Hide or Show individual elements of a post or edit their options', 'social-wall'),
                'icon'        => 'posts',
                'controls'    => self::get_posts_controls(),
                'separator' => false
            ],
            'loadbutton_section' => [
                'heading'     => __('Load More', 'social-wall'),
                'icon'        => 'load-more-button',
                'controls'    => self::get_loadbutton_controls(),
                'separator' => false
            ],
		);

		return $sections;
	}

	public static function get_layout_controls() {
		return array(
            [//Layout Type
                'type'      => 'toggleset',
                'id'        => 'layout',
                'options'   => [
                    [
                        'value' => 'masonry',
                        'icon'  => 'masonry',
                        'label' => __( 'Masonry', 'social-wall' )
                    ],
                    [
                        'value' => 'list',
                        'icon'  => 'list',
                        'label' => __( 'List', 'social-wall' )
                    ],
                    [
                        'value' => 'carousel',
                        'icon'  => 'carousel',
                        'label' => __( 'Carousel', 'social-wall' )
                    ]
                ]
            ],
            [//Spacing
                'type'      => 'group',
                'id'        => 'itemspacing',
                'heading'   => __('Spacing', 'social-wall'),
                'controls'  => [
                    [
                        'type'              => 'slider',
                        'id'                => 'itemspacingvertical',
                        'label'             => __('Verical', 'social-wall'),
                        'labelIcon'         => 'verticalspacing',
                        'unit'              => 'px',
                        'style'             => [ '.sb-customoizer-ctn .sb-customizer-preview .sbsw-list-layout .sbsw-post-item' => 'margin-bottom:{{value}}px;' ],
                        'condition'         => [
                            'layout' => [
                                'masonry',
                                'list'
                            ]
                        ],
                    ],
                    [
                        'type'              => 'slider',
                        'id'                => 'itemspacing',
                        'label'             => __('Horizontal', 'social-wall'),
                        'labelIcon'         => 'horizontalspacing',
                        'unit'              => 'px',
                        'condition'         => [
                            'layout' => [
                                'masonry',
                                'carousel'
                            ]
                        ],
                    ]
                ]
            ],
            [//Number of Columns
                'type'      => 'group',
                'id'        => 'number_columns',
                'heading'   => __('Columns', 'social-wall'),
                'controls'  => [
                    [
                        'type'      => 'list',
                        'controls'  => [
                            [
                                'type'          => 'number',
                                'id'            => 'masonrycols',
                                'min'           => 1,
                                'max'           => 10,
                                'ajaxAction'    => 'feedFlyPreview',
                                'leadingIcon'   => 'desktop'
                            ],
                            [
                                'type'          => 'number',
                                'id'            => 'masonrycolstablet',
                                'ajaxAction'    => 'feedFlyPreview',
                                'min'           => 1,
                                'max'           => 6,
                                'leadingIcon'   => 'tablet',
                            ],
                            [
                                'type'          => 'number',
                                'id'            => 'masonrycolsmobile',
                                'ajaxAction'    => 'feedFlyPreview',
                                'min'           => 1,
                                'max'           => 6,
                                'leadingIcon'   => 'mobile',
                            ],
                        ]
                    ],
                ],
                'condition'         => [
                    'layout' => [
                        'masonry',
                    ]
                ],
            ],
            [//Number of Posts
                'type'      => 'group',
                'id'        => 'num',
                'heading'   => __('Number of posts to display', 'social-wall'),
                'controls'  => [
                    [
                        'type'      => 'list',
                        'controls'  => [
                            [
                                'type'          => 'number',
                                'id'            => 'numdesktop',
                                'min'           => 1,
                                'ajaxAction'    => 'feedFlyPreview',
                                'leadingIcon'   => 'desktop'
                            ],
                            [
                                'type'          => 'number',
                                'id'            => 'numtablet',
                                'min'           => 1,
                                'ajaxAction'    => 'feedFlyPreview',
                                'leadingIcon'   => 'tablet',
                            ],
                            [
                                'type'          => 'number',
                                'id'            => 'nummobile',
                                'min'           => 1,
                                'ajaxAction'    => 'feedFlyPreview',
                                'leadingIcon'   => 'mobile',
                            ],
                        ]
                    ],
                ]
            ],
            [//Carousel Columns & Rows
                'type'      => 'group',
                'id'        => 'carousel_columns_rows',
                'heading'   => __('Columns and Rows', 'social-wall'),
                'condition'         => [
                    'layout' => [
                        'carousel'
                    ]
                ],
                'controls'  => [
                    [
                        'type'      => 'list',
                        'heading'   => __('Columns', 'social-wall'),
                        'controls'  => [
                            [
                                'type'          => 'number',
                                'id'            => 'carouselcols',
                                'min'           => 1,
                                'max'           => 6,
                                'leadingIcon'   => 'desktop'
                            ],
                            [
                                'type'          => 'number',
                                'id'            => 'carouselcolstablet',
                                'min'           => 1,
                                'max'           => 6,
                                'leadingIcon'   => 'tablet',
                            ],
                            [
                                'type'          => 'number',
                                'id'            => 'carouselcolsmobile',
                                'min'           => 1,
                                'max'           => 6,
                                'leadingIcon'   => 'mobile',
                            ],
                        ]
                    ],
                    [
                        'type'      => 'list',
                        'heading'   => __('Rows', 'social-wall'),
                        'controls'  => [
                            [
                                'type'          => 'number',
                                'id'            => 'carouselrows',
                                'leadingIcon'   => 'desktop',
                                'min'   => 1,
                                'max'   => 3
                            ],
                            [
                                'type'          => 'number',
                                'id'            => 'carouselrowstablet',
                                'leadingIcon'   => 'tablet',
                                'min'   => 1,
                                'max'   => 3
                            ],
                            [
                                'type'          => 'number',
                                'id'            => 'carouselrowsmobile',
                                'leadingIcon'   => 'mobile',
                                'min'   => 1,
                                'max'   => 3
                            ],
                        ]
                    ],
                ]
            ],
            [//Carousel Pagination
                'type'      => 'group',
                'id'        => 'carousel_pagination',
                'heading'   => __('Pagination', 'social-wall'),
                'condition'         => [
                    'layout' => [
                        'carousel'
                    ]
                ],
                'controls'  => [
                    [
                        'type'          => 'select',
                        'id'            => 'carouselloop',
                        'layout'        => 'half',
                        'strongheading' => false,
                        'stacked'       => true,
                        'heading'       => __( 'Loop Type', 'social-wall' ),
                        'options'       => [
                            'rewind' => __( 'Rewind', 'social-wall' ),
                            'infinity' => __( 'Infinity', 'social-wall' )
                        ]
                    ],
                    [
                        'type'          => 'number',
                        'id'            => 'carouseltime',
                        'layout'        => 'half',
                        'strongheading' => false,
                        'stacked'       => true,
                        'heading'       => __( 'Interval Time', 'social-wall' ),
                        'trailingText' => 'ms',
                    ],
                    [
                        'type'      => 'checkbox',
                        'id'        => 'carouselarrows',
                        'label'   => __('Show Navigation Arrows', 'social-wall'),
                        'stacked'       => true,
                        'options'   => [
                            'enabled' => true,
                            'disabled' => false
                        ]
                    ],
                    [
                        'type'      => 'checkbox',
                        'id'        => 'carouselpag',
                        'label'   => __('Show Pagination', 'social-wall'),
                        'stacked'       => true,
                        'options'   => [
                            'enabled' => true,
                            'disabled' => false
                        ]
                    ],
                    [
                        'type'      => 'checkbox',
                        'id'        => 'carouselautoplay',
                        'label'   => __('Enable Autoplay', 'social-wall'),
                        'stacked'       => true,
                        'options'   => [
                            'enabled' => true,
                            'disabled' => false
                        ]
                    ],
                ]
            ],
            [
                'type'      => 'switcher',
                'id'        => 'masonryshowfilter',
                'layout'    => 'third',
                'label'     => __('Social Media Feed Filter', 'social-wall'),
                'options'   => [
                    'enabled' => true,
                    'disabled' => false
                ],
                'condition'         => [
                    'layout' => [
                        'masonry'
                    ]
                ],
            ],
        );
	}

	public static function get_color_palette_controls() {
		return array(
            [//Layout Type
                'type'      => 'toggleset',
                'id'        => 'theme',
                'options'   => [
                    [
                        'value' => 'inherit',
                        'label' => __( 'Inherit from Theme', 'social-wall' )
                    ],
                    [
                        'value' => 'light',
                        'icon'  => 'color-scheme-light',
                        'label' => __( 'Light', 'social-wall' )
                    ],
                    [
                        'value' => 'dark',
                        'icon'  => 'color-scheme-dark',
                        'label' => __( 'Dark', 'social-wall' )
                    ],
                    [
                        'value' => 'custom',
                        'icon'  => 'color-scheme-custom',
                        'label' => __( 'Custom', 'social-wall' )
                    ]
                ]
            ],
            [//Author & Date Colors
                'type'      => 'group',
                'id'        => 'custom_palette',
                'heading'   => __('Custom Palette', 'social-wall'),
                'controls'  => [
                    [
                        'type'              => 'colorpicker',
                        'id'                => 'background',
                        'layout'            => 'half',
                        'strongheading'     => false,
                        'heading'           => __('Card BG', 'social-wall'),
                        'style'             => [ '.sb-customizer-preview .sb-feed-container[data-theme=custom] .sbsw-item-footer, .sb-customizer-preview .sb-feed-container[data-theme=custom] .sbsw-item-bottom-content, .sb-customizer-preview .sb-feed-container[data-theme=custom] .sbsw-item-media, .sb-customizer-preview .sb-feed-container[data-theme=custom] .sbsw-item-header' => 'background-color:{{value}};' ]
                    ],
                    [
                        'type'              => 'colorpicker',
                        'id'                => 'cardborder',
                        'layout'            => 'half',
                        'strongheading'     => false,
                        'heading'           => __('Card Border', 'social-wall'),
                        'style'             => [ '.sb-customizer-preview .sb-feed-container[data-theme=custom] .sbsw-post-item-inner' => 'box-shadow:0 0 0 1px {{value}};' ]
                    ],
                    [
                        'type'              => 'colorpicker',
                        'id'                => 'text1',
                        'layout'            => 'half',
                        'strongheading'     => false,
                        'heading'           => __('Text 1', 'social-wall'),
                        'style'             => [ '.sb-customizer-preview .sb-feed-container[data-theme=custom] .sbsw-item-bottom-content, .sb-customizer-preview .sb-feed-container[data-theme=custom] .sbsw-item-header .sbsw-author-name' => 'color:{{value}};' ]
                    ],
                    [
                        'type'              => 'colorpicker',
                        'id'                => 'text2',
                        'layout'            => 'half',
                        'strongheading'     => false,
                        'heading'           => __('Text 2', 'social-wall'),
                        'style'             => [ '.sb-customizer-preview .sb-feed-container[data-theme=custom] .sbsw-item-header .sbsw-date, .sb-customizer-preview .sb-feed-container[data-theme=custom] .sbsw-item-footer .sbsw-item-bottom .sbsw-item-stats .sbsw-summary-text' => 'color:{{value}};' ]
                    ],
                ],
                'condition'         => [
                    'theme' => [
                        'custom'
                    ]
                ],
            ],
        );
	}

	public static function get_posts_controls() {
		return array(
            [
                'type'          => 'checkboxsection',
                'id'            => 'individual_elements_sections',
                'settingId'     => 'postElements',
                'topLabel'      => __('Name', 'social-wall'),
                'includeTop'    => true,
                'enableSorting' => true,
                'controls'   => [
                    [//Post Avatar
                        'heading'   => __('Avatar', 'social-wall'),
                        'id'        => 'avatar',
                    ],
                    [//Post Username
                        'heading'   => __('Username', 'social-wall'),
                        'id'        => 'username',
                    ],
                    [//Post Date
                        'heading'   => __('Date and Time', 'social-wall'),
                        'id'        => 'date',
                        'controls'  => [
                            [//Date Font
                                'type'      => 'group',
                                'id'        => 'date_font',
                                'heading'   => __('Text', 'sb-customizer'),
                                'controls'  => [
                                    [
                                        'type'              => 'font',
                                        'id'                => 'dateFont',
                                        'style'             => [ '.sb-customizer-preview .sb-preview-wrapper .sbsw-identity .sbsw-date' => '{{value}}' ]
                                    ]
                                ]
                            ],
                            [//Date Font
                                'type'      => 'group',
                                'id'        => 'date_format',
                                'heading'   => __('Format', 'sb-customizer'),
                                'controls'  => [
                                    [
                                        'type'          => 'select',
                                        'id'            => 'dateformat',
                                        'stacked'       => true,
                                        'options'       => self::get_date_format_options()
                                    ],
                                    [
                                        'type'          => 'text',
                                        'id'            => 'customdate',
                                        'stacked'       => true,
                                        'condition'     => [
                                            'dateformat' => [ 'custom' ]
                                        ]
                                    ],
                                    [
                                        'type'          => 'text',
                                        'id'            => 'dateBeforeText',
                                        'heading'       => __('Add text before date', 'sb-customizer'),
                                        'layout'        => 'half',
                                        'stacked'       => true,
                                        'strongheading'  => false
                                    ],
                                    [
                                        'type'          => 'text',
                                        'id'            => 'dateAfterText',
                                        'heading'   => __('Add text after date', 'sb-customizer'),
                                        'layout'        => 'half',
                                        'stacked'       => true,
                                        'strongheading'  => false
                                    ],
                                ]
                            ]
                        ]
                    ],
                    [//Post Text
                        'heading'   => __('Post Text', 'social-wall'),
                        'id'        => 'text',
                        'highlight' => 'post-username',
                    ],
                    [//Post Media
                        'heading'   => __('Post Media', 'social-wall'),
                        'id'        => 'media',
                        'highlight' => 'post-username',
                    ],
                    [//Post Media
                        'heading'   => __('Summary (Like, Comment etc.)', 'social-wall'),
                        'id'        => 'summary',
                        'highlight' => 'post-summary',
                    ],
                ]
            ],
        );
	}

	public static function get_loadbutton_controls() {
		return [
            [
                'type'      => 'switcher',
                'id'        => 'showbutton',
                'layout' => 'third',
                'label'     => __('Enable', 'sb-customizer'),
                'options'   => [
                    'enabled' => true,
                    'disabled' => false
                ]
            ],
            [//Load More Text
                'type'      => 'group',
                'id'        => 'loadmorebutton_text',
                'heading'   => __('Text', 'sb-customizer'),
                'condition' => [
                    'showbutton' => [ true ]
                ],
                'controls'  => [
                    [
                        'type'              => 'text',
                        'id'                => 'buttontext',
                        'condition'         => [
                            'showbutton' => [ true ]
                        ],
                        'heading'           => __('Text', 'sb-customizer'),
                        'headingstrong'     => false,
                        'stacked'           => true,
                        'layout'            => 'third'
                    ],
                    // [
                    //     'type'              => 'font',
                    //     'id'                => 'loadButtonFont',
                    //     'condition'         => [
                    //         'showbutton' => [ true ]
                    //     ],
                    //     'style'             => [ '.sb-load-button' => '{{value}}' ]
                    // ],
                ]
            ],
            [//Load More Color
                'type'      => 'group',
                'id'        => 'loadmorebutton_color',
                'heading'   => __('Color', 'sb-customizer'),
                'condition' => [
                    'showbutton' => [ true ]
                ],
                'controls'  => [
                    [
                        'type'              => 'colorpicker',
                        'id'                => 'loadButtonColor',
                        'condition'         => [
                                'showbutton' => [ true ]
                        ],
                         'heading'          => __('Text', 'sb-customizer'),
                         'layout'           => 'third',
                         'stacked'          => true,
                         'headingstrong'    => false,
                         'style'            => [ '.sb-customoizer-ctn .sb-customizer-preview .sb-load-button' => 'color:{{value}};' ]
                    ],
                    [
                        'type'              => 'colorpicker',
                        'id'                => 'loadButtonBg',
                        'condition'         => [
                                'showbutton' => [ true ]
                        ],
                         'heading'          => __('Background', 'sb-customizer'),
                         'layout'           => 'third',
                         'stacked'          => true,
                         'headingstrong'    => false,
                         'style'            => [ '.sb-customoizer-ctn .sb-customizer-preview .sb-load-button' => 'background-color:{{value}};' ]
                    ],
                    [
                        'type'              => 'colorpicker',
                        'id'                => 'loadButtonHoverColor',
                        'condition'         => [
                                'showbutton' => [ true ]
                        ],
                         'heading'          => __('Text / Hover', 'sb-customizer'),
                         'layout'           => 'third',
                         'stacked'          => true,
                         'headingstrong'    => false,
                         'style'            => [ '.sb-customoizer-ctn .sb-customizer-preview .sb-load-button:hover' => 'color:{{value}};' ]
                    ],
                    [
                        'type'              => 'colorpicker',
                        'id'                => 'loadButtonHoverBg',
                        'condition'         => [
                                'showbutton' => [ true ]
                        ],
                         'heading'          => __('Bg / Hover', 'sb-customizer'),
                         'layout'           => 'third',
                         'stacked'          => true,
                         'headingstrong'    => false,
                         'style'            => [ '.sb-customoizer-ctn .sb-customizer-preview .sb-load-button:hover' => 'background-color:{{value}};' ]
                    ],
                ]
            ],
        ];
	}

    /**
	 * Date Format Options
	 *
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @return array
	*/
	public static function get_date_format_options(){
		$original = strtotime('2016-07-25T17:30:00+0000');
		return [
			'1'			=> __('2 days ago','sb-customizer'),
			'2'			=> date('F jS, g:i a', $original),
			'3'			=> date('F jS', $original),
			'4'			=> date('D F jS', $original),
			'5'			=> date('l F jS', $original),
			'6'			=> date('D M jS, Y', $original),
			'7'			=> date('l F jS, Y', $original),
			'8'			=> date('l F jS, Y - g:i a', $original),
			'9'			=> date("l M jS, 'y", $original),
			'10'		=> date('m.d.y', $original),
			'18'		=> date('m.d.y - G:i', $original),
			'11'		=> date('m/d/y', $original),
			'12'		=> date('d.m.y', $original),
			'19'		=> date('d.m.y - G:i', $original),
			'13'		=> date('d/m/y', $original),
			'14'		=> date('d-m-Y, G:i', $original),
			'15'		=> date('jS F Y, G:i', $original),
			'16'		=> date('d M Y, G:i', $original),
			'17'		=> date('l jS F Y, G:i', $original),
			'18'		=> date('Y-m-d', $original),
			'custom'	=> __('Custom','sb-customizer')
		];
	}
}
