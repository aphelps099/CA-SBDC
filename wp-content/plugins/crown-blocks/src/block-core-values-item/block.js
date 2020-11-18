
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


registerBlockType('crown-blocks/core-values-item', {
	title: 'Core Values Item',
	icon: 'heart',
	category: 'widgets',
	keywords: [ 'crown-blocks' ],
	parent: [ 'crown-blocks/core-values' ],

	supports: {
		// inserter: false
	},

	attributes: {
		title: { selector: '.value-title', source: 'children' },
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

				<div className={ blockClasses.join(' ') } key="core-values-item">
					<div className="inner">

						<RichText
							tagName="h3"
							className="value-title"
							onChange={ (value) => setAttributes({ title: value }) } 
							value={ title }
							placeholder="Value Title"
							allowedFormats={ [] }
						/>

						<div className="value-description">
							<div className="inner">

								<InnerBlocks />

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

			<div className={ blockClasses.join(' ') } key="core-values-item">
				<div className="inner">

					{ (title != '') && <RichText.Content tagName="h3" className="value-title" value={ title } /> }

					<div className="value-description">
						<div className="inner">

							<InnerBlocks.Content />

						</div>
					</div>

				</div>
			</div>

		);
	},


} );
