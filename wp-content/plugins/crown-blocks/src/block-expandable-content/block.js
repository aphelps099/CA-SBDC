
// import helper functions
import CrownBlocks from '../common.js';

// import CSS
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InnerBlocks, RichText, Editable, MediaUpload, BlockControls, AlignmentToolbar, InspectorControls, PanelColorSettings, URLInput } = wp.blockEditor;
const { PanelBody, RadioControl, BaseControl, ColorPicker, Button, ButtonGroup, RangeControl, FocalPointPicker, ToggleControl, TextControl, TextareaControl, SelectControl } = wp.components;
const { PlainText } = wp.editor;
const { getColorObjectByColorValue } = wp.blockEditor;

const ALLOWED_BLOCKS = [];


registerBlockType('crown-blocks/expandable-content', {
	title: 'Expandable Content',
	icon: 'image-flip-vertical',
	category: 'layout',
	keywords: [ 'collapse', 'accordion', 'crown-blocks' ],

	supports: {},

	attributes: {
		align: { type: 'string', default: 'center' },
		label: { type: 'string', default: 'More', selector: '.btn-label', source: 'html' },
		type: { type: 'string', default: 'default' },
		color: { type: 'string', default: '#E12C2C' },
		colorSlug: { type: 'string', default: 'red' },
		size: { type: 'string', default: 'md' },
		displayAsBlock: { type: 'boolean', default: false },
		disabledDisplayAsBlockBreakpoint: { type: 'string', default: 'none' },
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			align,
			label,
			type,
			color,
			colorSlug,
			size,
			displayAsBlock,
			disabledDisplayAsBlockBreakpoint
		} = attributes;

		let blockClasses = [ className ];
		blockClasses.push( 'toggle-align-' + align );

		let buttonClasses = [ 'btn' ]

		if(type == 'outline') {
			buttonClasses.push('btn-outline-' + colorSlug);
		} else if(type == 'link') {
			buttonClasses.push('btn-link');
		} else {
			buttonClasses.push('btn-' + colorSlug);
		}

		buttonClasses.push('btn-' + size);

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

					<BaseControl label="Button Alignment">
						<div>
							<ButtonGroup>
								<Button isPrimary={ align == 'left' } isSecondary={ align != 'left' } onClick={ (e) => setAttributes({ align: 'left' }) }>Left</Button>
								<Button isPrimary={ align == 'center' } isSecondary={ align != 'center' } onClick={ (e) => setAttributes({ align: 'center' }) }>Center</Button>
								<Button isPrimary={ align == 'right' } isSecondary={ align != 'right' } onClick={ (e) => setAttributes({ align: 'right' }) }>Right</Button>
							</ButtonGroup>
						</div>
					</BaseControl>

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

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<div className={ blockClasses.join(' ') } key="expandable-content">
					<div className="inner">

						<div class="expandable-content-toggle">
							
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

						<div class="expandable-content-contents">
							<div class="inner">

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
			align,
			label,
			type,
			color,
			colorSlug,
			size,
			displayAsBlock,
			disabledDisplayAsBlockBreakpoint
		} = attributes;

		let blockClasses = [ className ];
		blockClasses.push( 'toggle-align-' + align );

		let buttonClasses = [ 'btn' ]

		if(type == 'outline') {
			buttonClasses.push('btn-outline-' + colorSlug);
		} else if(type == 'link') {
			buttonClasses.push('btn-link');
		} else {
			buttonClasses.push('btn-' + colorSlug);
		}

		buttonClasses.push('btn-' + size);

		if(displayAsBlock) {
			if(disabledDisplayAsBlockBreakpoint == 'none') {
				buttonClasses.push('btn-block');
			} else {
				buttonClasses.push('btn-block-to-' + disabledDisplayAsBlockBreakpoint);
			}
		}

		return (

			<div className={ blockClasses.join(' ') } key="expandable-content">
				<div className="inner">

					<div class="expandable-content-toggle">

						<button type="button" className={ buttonClasses.join(' ') }>
							<span class="btn-label">{ label }</span>
						</button>
						
					</div>

					<div class="expandable-content-contents">
						<div class="inner">

							<InnerBlocks.Content />

						</div>
					</div>
				
				</div>
			</div>

		);
	},


} );
