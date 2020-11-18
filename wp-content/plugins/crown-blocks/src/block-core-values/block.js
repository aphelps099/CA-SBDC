
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

const ALLOWED_BLOCKS = [ 'crown-blocks/core-values-item' ];

const TEMPLATE = [
	[ 'crown-blocks/core-values-item', {}, [] ]
];


registerBlockType('crown-blocks/core-values', {
	title: 'Core Values',
	icon: 'heart',
	category: 'widgets',
	keywords: [ 'values', 'words', 'crown-blocks' ],

	supports: {},

	attributes: {
		overview: { selector: '.values-overview', source: 'children' },
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			overview
		} = attributes;

		let blockClasses = [
			className
		];

		return [

			<div class="crown-block-editor-container">

				<div className={ blockClasses.join(' ') } key="core-values">
					<div className="inner">

						<div class="core-values-overview">
							<RichText
								tagName="p"
								className="values-overview"
								onChange={ (value) => setAttributes({ overview: value }) } 
								value={ overview }
								placeholder="Provide an overview description..."
							/>
						</div>

						<div className="core-values-items">
							<div className="inner">

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
			overview
		} = attributes;

		let blockClasses = [
			className
		];

		return (

			<div className={ blockClasses.join(' ') } key="core-values">
				<div className="inner">

					{ (overview != '') && <div class="core-values-overview">
						<RichText.Content tagName="p" className="values-overview" value={ overview } />
					</div> }

					<div className="core-values-items">
						<div className="inner">

							<InnerBlocks.Content />

						</div>
					</div>

				</div>
			</div>

		);
	},


} );
