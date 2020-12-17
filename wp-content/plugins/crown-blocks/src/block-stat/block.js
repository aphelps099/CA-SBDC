
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


registerBlockType('crown-blocks/stat', {
	title: 'Statistic',
	icon: 'chart-line',
	category: 'widgets',
	keywords: [ 'data', 'number', 'crown-blocks' ],

	supports: {},

	attributes: {
		value: { selector: '.stat-value', source: 'children' },
		label: { selector: '.stat-label', source: 'children' }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			label,
			value
		} = attributes;

		let blockClasses = [
			className
		];

		return [

			<div class="crown-block-editor-container">

				<div className={ blockClasses.join(' ') } key="stat">
					<div className="inner">

						<RichText
							tagName="p"
							className="stat-value"
							onChange={ (value) => setAttributes({ value: value }) } 
							value={ value }
							placeholder="#"
							allowedFormats={ [] }
						/>

						<RichText
							tagName="p"
							className="stat-label"
							onChange={ (value) => setAttributes({ label: value }) } 
							value={ label }
							placeholder="Provide a Label"
							allowedFormats={ [ 'core/bold' ] }
						/>

					</div>
				</div>

			</div>

		];
	},

	
	save: ({ attributes, className }) => {
		
		const {
			label,
			value
		} = attributes;

		let blockClasses = [
			className
		];

		return (

			<div className={ blockClasses.join(' ') } key="stat">
				<div className="inner odometer-statistic">

					{ (value != '') && <RichText.Content tagName="p" className="stat-value" value={ value } /> }

					{ (label != '') && <RichText.Content tagName="p" className="stat-label" value={ label } /> }

				</div>
			</div>

		);
	},


} );
