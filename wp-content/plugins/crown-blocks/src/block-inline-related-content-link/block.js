
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


registerBlockType('crown-blocks/inline-related-content-link', {
	title: 'Inline Related Content Link',
	icon: 'admin-links',
	category: 'widgets',
	keywords: [ 'related', 'link', 'crown-blocks' ],

	supports: {
		align: [ 'left', 'right', 'wide' ]
	},

	attributes: {
		label: { type: 'string', default: 'Related', selector: '.cta-label', source: 'html' },
		title: { type: 'string', default: '', selector: '.cta-title', source: 'html' },
		linkUrl: { type: 'string', default: '' },
		linkPost: { type: 'object' },
		color: { type: 'string', default: '#108DBC' },
		colorSlug: { type: 'string', default: 'blue' },
		openNewWindow: { type: 'boolean', default: false }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			label,
			title,
			linkUrl,
			linkPost,
			color,
			colorSlug,
			openNewWindow
		} = attributes;

		let blockClasses = [ 'inline-related-content-link', className ];

		if(colorSlug) blockClasses.push('inline-related-content-link-' + colorSlug);

		return [

			<InspectorControls key="inspector-controls">

				<PanelColorSettings
					title={ 'Color' }
					initialOpen={ true }
					colorSettings={ [
						{
							label: 'Link Color',
							value: color,
							onChange: (value) => {
								let settings = wp.data.select('core/editor').getEditorSettings();
								let colorSlug = '';
								if(settings.colors) {
									let colorObject = getColorObjectByColorValue(settings.colors, value);
									if(colorObject) colorSlug = colorObject.slug;
								}
								setAttributes({ color: value, colorSlug: colorSlug });
							},
							disableCustomColors: true
						}
					] }
				/>

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
					<ToolbarGroup class="components-toolbar-group crown-block-button-toolbar">
						<URLInputButton
							url={ linkUrl }
							onChange={ ( url, post ) => setAttributes({ linkUrl: url, linkPost: post }) }
						/>
					</ToolbarGroup>
				</BlockControls>

				<aside className={ blockClasses.join(' ') } key="inline-related-content-link">
					<div class="inner">

						<RichText
							tagName="div"
							className="cta-label"
							onChange={ (value) => setAttributes({ label: value }) } 
							value={ label }
							allowedFormats={ [] }
						/>

						<RichText
							tagName="div"
							className="cta-title"
							onChange={ (value) => setAttributes({ title: value }) } 
							value={ title }
							placeholder="Provide a title for the related content..."
							allowedFormats={ [] }
						/>

					</div>
				</aside>

			</div>

		];
	},

	
	save: ({ attributes, className }) => {

		const {
			label,
			title,
			linkUrl,
			linkPost,
			color,
			colorSlug,
			openNewWindow
		} = attributes;

		let blockClasses = [ 'inline-related-content-link', className ];

		if(colorSlug) blockClasses.push('inline-related-content-link-' + colorSlug);

		return (

			<aside className={ blockClasses.join(' ') }>
				<a href={ linkUrl } target={ openNewWindow && '_blank' } rel={ openNewWindow && 'noopener noreferrer' }>
					<div class="inner">

						{ (label != '') && <RichText.Content tagName="h6" className="cta-label" value={ label } /> }
						{ (title != '') && <RichText.Content tagName="h3" className="cta-title" value={ title } /> }

					</div>
				</a>
			</aside>

		);
	}


} );
