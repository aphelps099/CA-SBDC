
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


registerBlockType('crown-blocks/nav-menu', {
	title: 'Navigation Menu',
	icon: 'list-view',
	category: 'widgets',
	keywords: [ 'links', 'button', 'crown-blocks' ],

	supports: {},

	attributes: {
		title: { type: 'string', default: 'Explore', selector: '.menu-title', source: 'html' }
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

				<div className={ blockClasses.join(' ') } key="nav-menu">
					<div className="inner">

						<RichText
							tagName="h3"
							className="menu-title"
							onChange={ (value) => setAttributes({ title: value }) } 
							value={ title }
							allowedFormats={ [] }
						/>

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
			title
		} = attributes;

		let blockClasses = [
			className
		];

		return (

			<div className={ blockClasses.join(' ') }>
				<div className="inner">

					{ (title != '') && <RichText.Content tagName="h3" className="menu-title" value={ title } /> }

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
