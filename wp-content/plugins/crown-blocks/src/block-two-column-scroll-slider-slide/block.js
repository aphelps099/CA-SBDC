
// import helper functions
import CrownBlocks from '../common.js';

// import CSS
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InnerBlocks, RichText, Editable, MediaUpload, BlockControls, AlignmentToolbar, InspectorControls, PanelColorSettings, URLInput } = wp.blockEditor;
const { PanelBody, RadioControl, ColorPicker, Button, ButtonGroup, RangeControl, FocalPointPicker, ToggleControl, TextControl, TextareaControl, SelectControl } = wp.components;
const { PlainText } = wp.editor;

const ALLOWED_BLOCKS = [ 'crown-blocks/container' ];

const TEMPLATE = [
	[ 'crown-blocks/container', {
		spacingProfile:    'Standard Page Section',
		paddingTopXl:      10,
		paddingBottomXl:   10,
		paddingXXl:        5,
		paddingTopLg:      10,
		paddingBottomLg:   10,
		paddingXLg:        4,
		paddingTopMd:      7,
		paddingBottomMd:   7,
		paddingXMd:        3,
		paddingTopSm:      5,
		paddingBottomSm:   5,
		paddingXSm:        2,
		paddingTopXs:      5,
		paddingBottomXs:   5,
		paddingXXs:        2,
		backgroundColor: '#0381C3',
		backgroundColorSlug: 'blue',
		backgroundColorSecondary: '#0381C3',
		backgroundColorSecondarySlug: 'blue',
	}, [] ],
	[ 'crown-blocks/container', {
		spacingProfile:    'Standard Page Section',
		paddingTopXl:      10,
		paddingBottomXl:   10,
		paddingXXl:        5,
		paddingTopLg:      10,
		paddingBottomLg:   10,
		paddingXLg:        4,
		paddingTopMd:      7,
		paddingBottomMd:   7,
		paddingXMd:        3,
		paddingTopSm:      5,
		paddingBottomSm:   5,
		paddingXSm:        2,
		paddingTopXs:      5,
		paddingBottomXs:   5,
		paddingXXs:        2,
		backgroundColor: '#032040',
		backgroundColorSlug: 'dark-blue',
		backgroundColorSecondary: '#032040',
		backgroundColorSecondarySlug: 'dark-blue',
	}, [] ]
];


registerBlockType('crown-blocks/two-column-scroll-slider-slide', {
	title: 'Two-Column Scroll Slider Slide',
	icon: 'columns',
	category: 'layout',
	keywords: [ 'container', 'crown-blocks' ],
	parent: [ 'crown-blocks/two-column-scroll-slider' ],

	supports: {
		// inserter: false
	},

	attributes: {},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {} = attributes;

		let blockClasses = [
			className
		];

		return [

			<div class="crown-block-editor-container">

				<div className={ blockClasses.join(' ') } key="two-column-scroll-slider-slide">
					<div class="inner">
						<InnerBlocks allowedBlocks={ ALLOWED_BLOCKS } template={ TEMPLATE } orientation="horizontal" templateLock={ 'insert' } />
					</div>
				</div>

			</div>

		];
	},

	
	save: ({ attributes, className }) => {
		
		const {} = attributes;

		let blockClasses = [
			className
		];

		return (

			<div className={ blockClasses.join(' ') }>
				<div className="inner">
					<InnerBlocks.Content />
				</div>
			</div>

		);
	},


} );
