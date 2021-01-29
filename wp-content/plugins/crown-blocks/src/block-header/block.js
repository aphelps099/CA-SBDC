
// import helper functions
import CrownBlocks from '../common.js';

// import CSS
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InnerBlocks, RichText, Editable, MediaUpload, BlockControls, AlignmentToolbar, InspectorControls, PanelColorSettings, URLInputButton, URLInput } = wp.blockEditor;
const { PanelBody, Popover, RadioControl, ColorPicker, ColorPalette, ToolbarButton, ToolbarGroup, Button, ButtonGroup, Icon, RangeControl, FocalPointPicker, ToggleControl, TextControl, SelectControl } = wp.components;
const { getColorObjectByColorValue } = wp.blockEditor;

const ALLOWED_BLOCKS = [
	'core/heading',
	'core/paragraph'
];


registerBlockType('crown-blocks/header', {
	title: 'Header',
	icon: <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M6.2 5.2v13.4l5.8-4.8 5.8 4.8V5.2z"></path></svg>,
	category: 'text',
	keywords: [ 'heading', 'lead', 'crown-blocks' ],

	supports: {},

	attributes: {
		borderColor: { type: 'string', default: '#F7024D' }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const TEMPLATE = [
			[ 'core/heading', {} ]
		];

		const {
			borderColor,
		} = attributes;

		let blockClasses = [ className ];

		let hrStyle = {};
		if(borderColor) {
			hrStyle.backgroundColor = borderColor;
		}

		return [

			<InspectorControls key="inspector-controls">

				<PanelColorSettings
					title={ 'Colors' }
					initialOpen={ true }
					colorSettings={ [
						{
							label: 'Border Color',
							value: borderColor,
							onChange: (value) => { setAttributes({ borderColor: value }); }
						}
					] }
				/>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<div className={ blockClasses.join(' ') } key="header">
					<div className="inner">

						<InnerBlocks allowedBlocks={ ALLOWED_BLOCKS } template={ TEMPLATE } renderAppender={ false } />

					</div>
					<hr style={ hrStyle } />
				</div>

			</div>

		];
	},

	
	save: ({ attributes, className }) => {
		
		const {
			borderColor
		} = attributes;

		let blockClasses = [ className ];

		let hrStyle = {};
		if(borderColor) {
			hrStyle.backgroundColor = borderColor;
		}

		return (

			<div className={ blockClasses.join(' ') } key="header">
				<div className="inner">

					<InnerBlocks.Content />

				</div>
				<hr style={ hrStyle } />
			</div>

		);
	},


} );
