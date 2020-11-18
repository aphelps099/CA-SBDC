
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
		label: { selector: '.stat-label', source: 'children' },
		value: { selector: '.stat-value', source: 'children' }
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
							className="stat-label"
							onChange={ (value) => setAttributes({ label: value }) } 
							value={ label }
							placeholder="Provide a Label"
							allowedFormats={ [] }
						/>

						<RichText
							tagName="p"
							className="stat-value"
							onChange={ (value) => setAttributes({ value: value }) } 
							value={ value }
							placeholder="Statistic Value"
							allowedFormats={ [] }
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

					{ (label != '') && <RichText.Content tagName="p" className="stat-label" value={ label } /> }

					{ (value != '') && <RichText.Content tagName="p" className="stat-value" value={ value } /> }

				</div>
			</div>

		);
	},


} );
