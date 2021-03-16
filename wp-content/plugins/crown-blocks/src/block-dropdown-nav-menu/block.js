
// import helper functions
import CrownBlocks from '../common.js';

// import CSS
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InnerBlocks, RichText, Editable, MediaUpload, BlockControls, AlignmentToolbar, InspectorControls, PanelColorSettings, URLInput } = wp.blockEditor;
const { PanelBody, RadioControl, ColorPicker, Button, ButtonGroup, RangeControl, FocalPointPicker, ToggleControl, TextControl, TextareaControl, SelectControl } = wp.components;
const { getColorObjectByColorValue } = wp.blockEditor;

const ALLOWED_BLOCKS = [ 'crown-blocks/dropdown-nav-menu-item' ];

const TEMPLATE = [
	[ 'crown-blocks/dropdown-nav-menu-item', {}, [] ]
];


registerBlockType('crown-blocks/dropdown-nav-menu', {
	title: 'Dropdown Navigation Menu',
	icon: 'list-view',
	category: 'widgets',
	keywords: [ 'links', 'button', 'crown-blocks' ],

	supports: {},

	attributes: {
		toggleLabel: { type: 'string', default: 'Explore', selector: '.toggle-label', source: 'html' },
		toggleColor: { type: 'string', default: '#D11141' },
		toggleColorSlug: { type: 'string', default: 'red' }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			toggleLabel,
			toggleColor,
			toggleColorSlug
		} = attributes;

		let blockClasses = [
			className
		];

		let buttonClasses = [ 'menu-toggle', 'btn' ];
		buttonClasses.push('btn-' + toggleColorSlug);

		return [

			<InspectorControls key="inspector-controls">

				<PanelColorSettings
					title={ 'Color' }
					initialOpen={ true }
					colorSettings={ [
						{
							label: 'Button Color',
							value: toggleColor,
							onChange: (value) => {
								let colors = CrownBlocks.getThemeColorPalette();
								let colorSlug = '';
								if(colors) {
									let colorObject = getColorObjectByColorValue(colors, value);
									if(colorObject) colorSlug = colorObject.slug;
								}
								setAttributes({ toggleColor: value, toggleColorSlug: colorSlug });
							},
							disableCustomColors: true
						}
					] }
				/>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<div className={ blockClasses.join(' ') } key="dropdown-nav-menu">
					<div className="inner">

						<div className={ buttonClasses.join(' ') }>
							<RichText
								tagName="div"
								className="toggle-label"
								onChange={ (value) => setAttributes({ toggleLabel: value }) } 
								value={ toggleLabel }
								allowedFormats={ [] }
							/>
						</div>

						<div class="menu">
							<div class="inner">
								<InnerBlocks allowedBlocks={ ALLOWED_BLOCKS } template={ TEMPLATE } />
							</div>
						</div>

					</div>
				</div>

			</div>

		];
	},

	
	save: ({ attributes, className }) => {
		
		const {
			toggleLabel,
			toggleColor,
			toggleColorSlug
		} = attributes;

		let blockClasses = [
			className
		];

		let buttonClasses = [ 'menu-toggle', 'btn' ];
		buttonClasses.push('btn-' + toggleColorSlug);

		return (

			<div className={ blockClasses.join(' ') }>
				<div className="inner">

					<button className={ buttonClasses.join(' ') }>
						<span class="toggle-label">{ toggleLabel }</span>
					</button>

					<div class="menu">
						<div class="inner">
							<InnerBlocks.Content />
						</div>
					</div>

				</div>
			</div>

		);
	},


} );
