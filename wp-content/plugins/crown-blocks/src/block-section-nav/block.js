
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

const ALLOWED_BLOCKS = [ 'crown-blocks/section-nav-content' ];

const TEMPLATE = [
	[ 'crown-blocks/section-nav-content', {}, [] ]
];


registerBlockType('crown-blocks/section-nav', {
	title: 'Section Navigation',
	icon: 'editor-kitchensink',
	category: 'layout',
	keywords: [ 'scroll', 'crown-blocks' ],

	supports: {
		align: [ 'full' ],
		anchor: true
	},

	attributes: {
		align: { type: 'string', default: 'full' },
		title: { selector: '.section-nav-title', source: 'children' },
		layout: { type: 'string', default: '' }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			title,
			layout
		} = attributes;

		let blockClasses = [
			className
		];

		if(layout != '') blockClasses.push('layout-' + layout);

		return [

			<InspectorControls key="inspector-controls">

				<PanelBody title={ 'Appearance' } initialOpen={ true }>

					<SelectControl
						label="Layout"
						value={ layout }
						onChange={ (value) => setAttributes({ layout: value }) }
						options={ [
							{ label: 'Header', value: '' },
							{ label: 'Sidebar', value: 'sidebar' }
						] }
					/>

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<div className={ blockClasses.join(' ') } key="section-nav">
					<div className="inner">

						<RichText
							tagName="h2"
							className="section-nav-title"
							onChange={ (value) => setAttributes({ title: value }) } 
							value={ title }
							placeholder="Navigation Title"
							allowedFormats={ [] }
						/>

						<div className="section-nav-contents">
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
			layout
		} = attributes;

		let blockClasses = [
			className
		];

		if(layout != '') blockClasses.push('layout-' + layout);

		return (

			<div className={ blockClasses.join(' ') }>
				<div className="inner">

					{ (title != '') && <RichText.Content tagName="h2" className="section-nav-title" value={ title } /> }

					<div className="section-nav-contents">
						<div className="inner">

							<InnerBlocks.Content />

						</div>
					</div>

				</div>
			</div>

		);
	},


} );
