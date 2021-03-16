
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


registerBlockType('crown-blocks/dropdown-nav-menu-item', {
	title: 'Dropdown Nav Menu Item',
	icon: 'list-view',
	category: 'widgets',
	keywords: [ 'crown-blocks' ],
	parent: [ 'crown-blocks/dropdown-nav-menu' ],

	supports: {
		// inserter: false
	},

	attributes: {
		label: { type: 'string', default: '', selector: '.menu-item-label', source: 'html' },
		linkUrl: { type: 'string', default: '' },
		linkPost: { type: 'object' },
		openNewWindow: { type: 'boolean', default: false }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			label,
			linkUrl,
			linkPost,
			openNewWindow
		} = attributes;

		let blockClasses = [ className ];

		return [

			<InspectorControls key="inspector-controls">

				<PanelBody title={ 'Link Settings' } initialOpen={ true }>

					<ToggleControl
						label={ 'Open link in new window' }
						checked={ openNewWindow }
						onChange={ (value) => { setAttributes({ openNewWindow: value }); } }
					/>

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<BlockControls>
					<ToolbarGroup class="components-toolbar components-toolbar-group crown-block-button-toolbar">
						<URLInputButton
							url={ linkUrl }
							onChange={ ( url, post ) => setAttributes({ linkUrl: url, linkPost: post }) }
						/>
					</ToolbarGroup>
				</BlockControls>

				<div className={ blockClasses.join(' ') } key="dropdown-nav-menu-item">
					
					<span class="menu-item-link">

						<RichText
							tagName="div"
							className="menu-item-label"
							onChange={ (value) => setAttributes({ label: value }) } 
							value={ label }
							placeholder="Menu Item Label"
							allowedFormats={ [] }
						/>

					</span>

				</div>

			</div>

		];
	},

	
	save: ({ attributes, className }) => {

		const {
			label,
			linkUrl,
			linkPost,
			openNewWindow
		} = attributes;

		let blockClasses = [ className ];

		return (
			<div className={ blockClasses.join(' ') }>
				<a href={ linkUrl } class="menu-item-link" target={ openNewWindow && '_blank' } rel={ openNewWindow && 'noopener noreferrer' }>
					<span class="menu-item-label">{ label }</span>
				</a>
			</div>
		);
	}


} );
