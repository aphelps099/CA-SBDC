
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


registerBlockType('crown-blocks/button', {
	title: 'Button',
	description: 'Add a button-styled link to your content.',
	icon: <svg width="24px" height="16px" viewBox="0 0 24 16" version="1.1" xmlns="http://www.w3.org/2000/svg"><g fill-rule="evenodd"><g transform="translate(0.000000, -4.000000)"><path d="M21,4 C22.6568542,4 24,5.4326888 24,7.2 L24,16.8 C24,18.5673112 22.6568542,20 21,20 L3,20 C1.34314575,20 0,18.5673112 0,16.8 L0,7.2 C0,5.4326888 1.34314575,4 3,4 L21,4 Z M21,6 L3,6 C2.48716416,6 2.06449284,6.38604019 2.00672773,6.88337887 L2,7 L2,14 C2,14.5128358 2.38604019,14.9355072 2.88337887,14.9932723 L3,15 L21,15 C21.5128358,15 21.9355072,14.6139598 21.9932723,14.1166211 L22,14 L22,7 C22,6.48716416 21.6139598,6.06449284 21.1166211,6.00672773 L21,6 Z" id="button"></path></g></g></svg>,
	category: 'layout',
	keywords: [ 'btn', 'link', 'crown-blocks' ],

	supports: {},

	attributes: {
		label: { type: 'string', default: 'Learn More', selector: '.btn-label', source: 'html' },
		linkUrl: { type: 'string', default: '' },
		linkPost: { type: 'object' },
		alignment: { type: 'alignment', default: 'none' },
		type: { type: 'string', default: 'default' },
		color: { type: 'string', default: '#0099D8' },
		colorSlug: { type: 'string', default: 'sky-blue' },
		size: { type: 'string', default: 'md' },
		displayWithArrowIcon: { type: 'boolean', default: false },
		displayAsBlock: { type: 'boolean', default: false },
		disabledDisplayAsBlockBreakpoint: { type: 'string', default: 'none' },
		openNewWindow: { type: 'boolean', default: false }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			label,
			linkUrl,
			linkPost,
			alignment,
			type,
			color,
			colorSlug,
			size,
			displayWithArrowIcon,
			displayAsBlock,
			disabledDisplayAsBlockBreakpoint,
			openNewWindow
		} = attributes;

		let blockClasses = [ className ];
		if(typeof alignment != 'undefined') blockClasses.push('text-alignment-' + alignment);

		let buttonClasses = [ 'btn' ]

		if(type == 'outline') {
			buttonClasses.push('btn-outline-' + colorSlug);
		} else if(type == 'link') {
			buttonClasses.push('btn-link');
			buttonClasses.push('btn-link-' + colorSlug);
		} else {
			buttonClasses.push('btn-' + colorSlug);
		}

		buttonClasses.push('btn-' + size);

		if(displayWithArrowIcon) buttonClasses.push('btn-has-arrow-icon');

		if(displayAsBlock) {
			if(disabledDisplayAsBlockBreakpoint == 'none') {
				buttonClasses.push('btn-block');
			} else {
				buttonClasses.push('btn-block-to-' + disabledDisplayAsBlockBreakpoint);
			}
		}

		return [

			<InspectorControls key="inspector-controls">

				<PanelColorSettings
					title={ 'Color' }
					initialOpen={ true }
					colorSettings={ [
						{
							label: 'Button Color',
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

				<PanelBody title={ 'Appearance' } initialOpen={ true }>

					<SelectControl
						label="Button Type"
						value={ type }
						onChange={ (value) => setAttributes({ type: value }) }
						options={ [
							{ label: 'Default', value: 'default' },
							{ label: 'Outline', value: 'outline' },
							{ label: 'Link', value: 'link' }
						] }
					/>

					<SelectControl
						label="Size"
						value={ size }
						onChange={ (value) => setAttributes({ size: value }) }
						options={ [
							{ label: 'Small', value: 'sm' },
							{ label: 'Medium', value: 'md' },
							{ label: 'Large', value: 'lg' }
						] }
					/>

					<ToggleControl
						label={ 'Display with arrow icon' }
						checked={ displayWithArrowIcon }
						onChange={ (value) => { setAttributes({ displayWithArrowIcon: value }); } }
					/>

					<ToggleControl
						label={ 'Display as block' }
						checked={ displayAsBlock }
						onChange={ (value) => { setAttributes({ displayAsBlock: value }); } }
					/>

					{ !! displayAsBlock && <SelectControl
						label="Disable block appearance at specified screensize:"
						value={ disabledDisplayAsBlockBreakpoint }
						onChange={ (value) => setAttributes({ disabledDisplayAsBlockBreakpoint: value }) }
						options={ [
							{ label: 'Never', value: 'none' },
							{ label: 'Mobile - Landscape (576px)', value: 'sm' },
							{ label: 'Tablet - Portrait (768px)', value: 'md' },
							{ label: 'Tablet - Landscape (992px)', value: 'lg' },
							{ label: 'Desktop - Widescreen (1200px)', value: 'xl' }
						] }
					/> }

				</PanelBody>

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
					<AlignmentToolbar
						value={ alignment }
						onChange={ (value) => { setAttributes({ alignment: value }); } }
					/>
				</BlockControls>

				<div className={ blockClasses.join(' ') } key="button">
					
					<span className={ buttonClasses.join(' ') }>

						<RichText
							tagName="div"
							className="btn-label"
							onChange={ (value) => setAttributes({ label: value }) } 
							value={ label }
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
			alignment,
			type,
			color,
			colorSlug,
			size,
			displayWithArrowIcon,
			displayAsBlock,
			disabledDisplayAsBlockBreakpoint,
			openNewWindow
		} = attributes;

		let blockClasses = [ className ];
		if(typeof alignment != 'undefined') blockClasses.push('text-alignment-' + alignment);

		let buttonClasses = [ 'btn' ]

		if(type == 'outline') {
			buttonClasses.push('btn-outline-' + colorSlug);
		} else if(type == 'link') {
			buttonClasses.push('btn-link');
			buttonClasses.push('btn-link-' + colorSlug);
		} else {
			buttonClasses.push('btn-' + colorSlug);
		}

		buttonClasses.push('btn-' + size);

		if(displayWithArrowIcon) buttonClasses.push('btn-has-arrow-icon');

		if(displayAsBlock) {
			if(disabledDisplayAsBlockBreakpoint == 'none') {
				buttonClasses.push('btn-block');
			} else {
				buttonClasses.push('btn-block-to-' + disabledDisplayAsBlockBreakpoint);
			}
		}

		return (

			<p className={ blockClasses.join(' ') }>
				<a href={ linkUrl } className={ buttonClasses.join(' ') } target={ openNewWindow && '_blank' } rel={ openNewWindow && 'noopener noreferrer' }>
					<span class="btn-label">{ label }</span>
				</a>
			</p>

		);
	}


} );
