
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

const ALLOWED_BLOCKS = [ 'crown-blocks/testimonial' ];

const TEMPLATE = [
	[ 'crown-blocks/testimonial', {}, [] ]
];


registerBlockType('crown-blocks/testimonial-slider', {
	title: 'Testimonial Slider',
	icon: 'format-quote',
	category: 'widgets',
	keywords: [ 'quote', 'pullquote', 'crown-blocks' ],

	supports: {},

	attributes: {
		title: { selector: '.testimonial-slider-title', source: 'children' },
		linkUrl: { type: 'string', default: '' },
		linkLabel: { type: 'string', default: '' },
		linkOpenNewWindow: { type: 'boolean', default: false }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			title,
			linkUrl,
			linkLabel,
			linkOpenNewWindow
		} = attributes;

		let blockClasses = [
			className
		];

		return [

			<InspectorControls key="inspector-controls">

				<PanelBody title={ 'Slider Link' } initialOpen={ true }>

					<TextControl
						label="Link URL"
						value={ linkUrl }
						onChange={ (value) => setAttributes({ linkUrl: value }) }
						placeholder="https://"
					/>

					<TextControl
						label="Label"
						value={ linkLabel }
						onChange={ (value) => setAttributes({ linkLabel: value }) }
					/>

					<ToggleControl
						label={ 'Open link in new window' }
						checked={ linkOpenNewWindow }
						onChange={ (value) => { setAttributes({ linkOpenNewWindow: value }); } }
					/>

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<div className={ blockClasses.join(' ') } key="testimonial-slider">
					<div className="inner">

						<RichText
							tagName="h2"
							className="testimonial-slider-title"
							onChange={ (value) => setAttributes({ title: value }) } 
							value={ title }
							placeholder="Optional Title"
							allowedFormats={ [] }
						/>

						<div className="testimonial-slider-testimonials">
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
			title,
			linkUrl,
			linkLabel,
			linkOpenNewWindow
		} = attributes;

		let blockClasses = [
			className
		];

		if(linkUrl && linkLabel) blockClasses.push('has-link');

		return (

			<div className={ blockClasses.join(' ') } key="testimonial-slider">
				<div className="inner">

					{ title != '' && <RichText.Content tagName="h2" className="testimonial-slider-title" value={ title } /> }

					<div className="testimonial-slider-testimonials">
						<div className="inner">

							<InnerBlocks.Content />

						</div>
					</div>

					{ (linkUrl && linkLabel) && <p class="testimonial-slider-link">
						<a href={ linkUrl } target={ linkOpenNewWindow && '_blank' } rel={ linkOpenNewWindow && 'noopener noreferrer' }>{ linkLabel }</a>
					</p> }

				</div>
			</div>

		);
	},


} );
