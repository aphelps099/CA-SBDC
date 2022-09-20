
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
		borderColor: { type: 'string', default: '#D11141' },
		alignment: { type: 'string', default: 'none' }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const TEMPLATE = [
			[ 'core/heading', {} ]
		];

		const {
			borderColor,
			alignment
		} = attributes;

		let blockClasses = [ className ];
		if(typeof alignment != 'undefined' && alignment != 'none') blockClasses.push('text-alignment-' + alignment);

		let hrStyle = {};
		if(borderColor) {
			let borderColorRGB = CrownBlocks.hexToRgb(borderColor);
			hrStyle.backgroundColor = borderColor;
			hrStyle.background = 'linear-gradient(to right, rgba(' + borderColorRGB.r + ', ' + borderColorRGB.g + ', ' + borderColorRGB.b + ', 0), ' + borderColor + ')';
		}

		let hrSolidStyle = {};
		if(borderColor) {
			hrSolidStyle.backgroundColor = borderColor;
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

				<BlockControls>
					<AlignmentToolbar
						value={ alignment }
						onChange={ (value) => { setAttributes({ alignment: value }); } }
					/>
				</BlockControls>

				<div className={ blockClasses.join(' ') } key="header">
					<div className="header-container">
						<div className="inner">

							<InnerBlocks allowedBlocks={ ALLOWED_BLOCKS } template={ TEMPLATE } renderAppender={ false } />

						</div>
						<hr style={ hrStyle } />
						<hr style={ hrSolidStyle } class="solid" />
					</div>
				</div>

			</div>

		];
	},

	
	save: ({ attributes, className }) => {
		
		const {
			borderColor,
			alignment
		} = attributes;

		let blockClasses = [ className ];
		if(typeof alignment != 'undefined' && alignment != 'none') blockClasses.push('text-alignment-' + alignment);

		let hrStyle = {};
		if(borderColor) {
			let borderColorRGB = CrownBlocks.hexToRgb(borderColor);
			hrStyle.backgroundColor = borderColor;
			hrStyle.background = 'linear-gradient(to right, rgba(' + borderColorRGB.r + ', ' + borderColorRGB.g + ', ' + borderColorRGB.b + ', 0), ' + borderColor + ')';
		}

		let hrSolidStyle = {};
		if(borderColor) {
			hrSolidStyle.backgroundColor = borderColor;
		}

		return (

			<div className={ blockClasses.join(' ') } key="header">
				<div className="header-container">
					<div className="inner">

						<InnerBlocks.Content />

					</div>
					<div class="hr-container"><hr style={ hrStyle } /><hr style={ hrSolidStyle } class="solid" /></div>
				</div>
			</div>

		);
	},


	deprecated: [

		{
			attributes: {
				borderColor: { type: 'string', default: '#D11141' }
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
			}
		},
		{
			attributes: {
				borderColor: { type: 'string', default: '#D11141' },
				alignment: { type: 'string', default: 'none' }
			},
			save: ({ attributes, className }) => {
		
				const {
					borderColor,
					alignment
				} = attributes;
		
				let blockClasses = [ className ];
				if(typeof alignment != 'undefined' && alignment != 'none') blockClasses.push('text-alignment-' + alignment);
		
				let hrStyle = {};
				if(borderColor) {
					let borderColorRGB = CrownBlocks.hexToRgb(borderColor);
					hrStyle.backgroundColor = borderColor;
					hrStyle.background = 'linear-gradient(to right, rgba(' + borderColorRGB.r + ', ' + borderColorRGB.g + ', ' + borderColorRGB.b + ', 0), ' + borderColor + ')';
				}
		
				return (
		
					<div className={ blockClasses.join(' ') } key="header">
						<div className="inner">
		
							<InnerBlocks.Content />
		
						</div>
						<div class="hr-container"><hr style={ hrStyle } /></div>
					</div>
		
				);
			},
		}

	]


} );
