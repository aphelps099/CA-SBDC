
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

const ALLOWED_BLOCKS = [ 'crown-blocks/tabbed-content-tab' ];

const TEMPLATE = [
	[ 'crown-blocks/tabbed-content-tab', {}, [] ]
];


registerBlockType('crown-blocks/tabbed-content', {
	title: 'Tabbed Content',
	icon: 'index-card',
	category: 'layout',
	keywords: [ 'tab', 'slider', 'crown-blocks' ],

	supports: {},

	attributes: {
		type: { type: 'string', default: '' },
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			type
		} = attributes;

		let blockClasses = [ className ];
		let tabsClasses = [ 'tabbed-content-tabs '];

		if(type != '') blockClasses.push('type-' + type);
		// if(type == 'grid') {
		// 	tabsClasses.push('text-color-dark');
		// }

		return [

			<InspectorControls key="inspector-controls">

				<PanelBody title={ 'Appearance' } initialOpen={ true }>

					<SelectControl
						label="Display Style"
						value={ type }
						onChange={ (value) => setAttributes({ type: value }) }
						options={ [
							{ label: 'Default', value: '' },
							{ label: 'Grid', value: 'grid' },
							{ label: 'Accordion', value: 'accordion' }
						] }
					/>

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<div className={ blockClasses.join(' ') } key="tabbed-content">
					<div className="inner">

						<div className={ tabsClasses.join(' ') }>
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
			type
		} = attributes;

		let blockClasses = [ className ];
		let tabsClasses = [ 'tabbed-content-tabs '];

		if(type != '') blockClasses.push('type-' + type);
		// if(type == 'grid') {
		// 	tabsClasses.push('text-color-dark');
		// }

		return (

			<div className={ blockClasses.join(' ') } key="tabbed-content">
				<div className="inner">

				<div className={ tabsClasses.join(' ') }>
						<div className="inner">

							<InnerBlocks.Content />

						</div>
					</div>

				</div>
			</div>

		);
	},


} );
