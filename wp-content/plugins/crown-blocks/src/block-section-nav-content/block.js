
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
		backgroundColor: '#108DBC',
		backgroundColorSlug: 'blue',
		backgroundColorSecondary: '#108DBC',
		backgroundColorSecondarySlug: 'blue',
	}, [] ]
];


registerBlockType('crown-blocks/section-nav-content', {
	title: 'Section Navigation Content',
	icon: 'editor-kitchensink',
	category: 'layout',
	keywords: [ 'scroll', 'crown-blocks' ],
	parent: [ 'crown-blocks/section-nav' ],

	supports: {
		// inserter: false
	},

	attributes: {
		title: { selector: '.section-nav-content-title', source: 'children' }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			title
		} = attributes;

		let blockClasses = [
			className
		];

		return [

			<div class="crown-block-editor-container">

				<div className={ blockClasses.join(' ') } key="section-nav-content">
					<div className="inner">

						<RichText
							tagName="h3"
							className="section-nav-content-title"
							onChange={ (value) => setAttributes({ title: value }) } 
							value={ title }
							placeholder="Section Title"
							allowedFormats={ [] }
						/>

						<div className="section-nav-content-contents">
							<div className="inner">

								<InnerBlocks allowedBlocks={ ALLOWED_BLOCKS } template={ TEMPLATE } templateLock={ 'insert' } />

							</div>
						</div>

					</div>
				</div>

			</div>

		];
	},

	
	save: ({ attributes, className }) => {
		
		const {
			title
		} = attributes;

		let blockClasses = [
			className
		];

		return (

			<div className={ blockClasses.join(' ') }>
				<div className="inner">

					{ (title != '') && <RichText.Content tagName="h3" className="section-nav-content-title" value={ title } /> }

					<div className="section-nav-content-contents">
						<div className="inner">

							<InnerBlocks.Content />

						</div>
					</div>

				</div>
			</div>

		);
	},


} );
