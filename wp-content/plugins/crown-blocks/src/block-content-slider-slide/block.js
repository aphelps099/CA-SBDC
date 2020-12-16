
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
		spacingProfile:    'Content Slide',
		paddingTopXl:      8,
		paddingBottomXl:   8,
		paddingXXl:        10,
		paddingTopLg:      6,
		paddingBottomLg:   6,
		paddingXLg:        8,
		paddingTopMd:      4,
		paddingBottomMd:   4,
		paddingXMd:        4,
		paddingTopSm:      3,
		paddingBottomSm:   3,
		paddingXSm:        2,
		paddingTopXs:      2,
		paddingBottomXs:   2,
		paddingXXs:        2,
		backgroundColor: '#108DBC',
		backgroundColorSlug: 'blue',
		backgroundColorSecondary: '#108DBC',
		backgroundColorSecondarySlug: 'blue',
	}, [
		[ 'crown-blocks/multi-column', {}, [
			[ 'crown-blocks/column', {}, [
				[ 'core/paragraph', { placeholder: 'Enter slide content...' } ]
			] ],
			[ 'crown-blocks/column', {}, [] ]
		] ]
	] ]
];


registerBlockType('crown-blocks/content-slider-slide', {
	title: 'Content Slider Slide',
	icon: 'slides',
	category: 'layout',
	keywords: [ 'container', 'crown-blocks' ],
	parent: [ 'crown-blocks/content-slider' ],

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

				<div className={ blockClasses.join(' ') } key="content-slider-slide">
					<InnerBlocks allowedBlocks={ ALLOWED_BLOCKS } template={ TEMPLATE } templateLock={ 'insert' } />
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
				<InnerBlocks.Content />
			</div>

		);
	},


} );
