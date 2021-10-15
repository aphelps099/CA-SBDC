
// import helper functions
import CrownBlocks from '../common.js';

// import CSS
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InnerBlocks, RichText, Editable, MediaUpload, BlockControls, AlignmentToolbar, InspectorControls, PanelColorSettings, URLInputButton, URLInput } = wp.blockEditor;
const { PanelBody, Popover, RadioControl, ColorPicker, ColorPalette, ToolbarButton, ToolbarGroup, Button, ButtonGroup, Icon, RangeControl, FocalPointPicker, ToggleControl, TextControl, SelectControl } = wp.components;
const { getColorObjectByColorValue, __experimentalPanelColorGradientSettings } = wp.blockEditor;


const { createHigherOrderComponent } = wp.compose;
const crownAddComponentBlockButtonClasses = createHigherOrderComponent( ( BlockListBlock ) => {
	return ( props ) => {
		let classNames = [];
		if(props.attributes.displayAsBlock) {
			if(props.attributes.disabledDisplayAsBlockBreakpoint == 'none') {
				classNames.push('btn-block-container');
			} else {
				classNames.push('btn-block-to-' + props.attributes.disabledDisplayAsBlockBreakpoint + '-container');
			}
		}
		if(classNames.length) {
			return <BlockListBlock { ...props } className={ classNames.join(' ') } />;
		}
		return <BlockListBlock {...props} />;
	};
}, 'withClientIdClassName' );
wp.hooks.addFilter('editor.BlockListBlock', 'crown-blocks/add-component-block-button-classes', crownAddComponentBlockButtonClasses);


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
		alignment: { type: 'string', default: 'none' },
		type: { type: 'string', default: 'default' },
		color: { type: 'string', default: '#D11141' },
		colorSlug: { type: 'string', default: 'red' },
		gradient: { type: 'string' },
		size: { type: 'string', default: 'md' },
		displayWithArrowIcon: { type: 'boolean', default: false },
		displayAsBlock: { type: 'boolean', default: false },
		disabledDisplayAsBlockBreakpoint: { type: 'string', default: 'none' },
		openNewWindow: { type: 'boolean', default: false },
		openModal: { type: 'boolean', default: false },
		linkModalType: { type: 'string', default: '' },
		linkModalFormId: { type: 'string', default: '' },
		linkModalVideoEmbed: { type: 'string', default: '' },
		linkModalMeetingId: { type: 'string', default: '' },
		openEventRegistration: { type: 'boolean', default: false },
		meetingType: { type: 'string', default: 'meetings' },
		meetingId: { type: 'string', default: '' }
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
			gradient,
			size,
			displayWithArrowIcon,
			displayAsBlock,
			disabledDisplayAsBlockBreakpoint,
			openNewWindow,
			openModal,
			linkModalType,
			linkModalFormId,
			linkModalVideoEmbed,
			linkModalMeetingId,
			openEventRegistration,
			meetingId,
			meetingType
		} = attributes;

		let blockClasses = [ className ];
		if(typeof alignment != 'undefined') blockClasses.push('text-alignment-' + alignment);
		blockClasses.push('button-type-' + type + '-container');

		let buttonClasses = [ 'btn' ];

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
				blockClasses.push('btn-block-container');
				buttonClasses.push('btn-block');
			} else {
				blockClasses.push('btn-block-to-' + disabledDisplayAsBlockBreakpoint + '-container');
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
								let colors = CrownBlocks.getThemeColorPalette();
								let colorSlug = '';
								if(colors) {
									let colorObject = getColorObjectByColorValue(colors, value);
									if(colorObject) colorSlug = colorObject.slug;
								}
								setAttributes({ color: value, colorSlug: colorSlug });
							},
							disableCustomColors: true
						}
					] }
				/>

				<__experimentalPanelColorGradientSettings
					title={ 'Button Color' }
					initialOpen={ true }
					settings={ [ {
						colorValue: color,
						gradientValue: gradient,
						label: 'Button Color',
						disableCustomColors: true,
						disableCustomGradients: true,
						onColorChange: (value) => {
							let colors = CrownBlocks.getThemeColorPalette();
							let colorSlug = '';
							if(colors) {
								let colorObject = getColorObjectByColorValue(colors, value);
								console.log(colorObject);
								if(colorObject) colorSlug = colorObject.slug;
							}
							setAttributes({ color: value, colorSlug: colorSlug });
						},
						onGradientChange: (value) => {
							console.log(value);
							setAttributes({ gradient: value });
						}
					} ] }
				/>

				<PanelBody title={ 'Link Settings' } initialOpen={ true }>

					<ToggleControl
						label={ 'Open link in new window' }
						checked={ openNewWindow }
						onChange={ (value) => { setAttributes({ openNewWindow: value }); } }
					/>

					<ToggleControl
						label={ 'Link opens modal window' }
						checked={ openModal }
						onChange={ (value) => { setAttributes({ openModal: value }); } }
					/>

					{ !! openModal && <SelectControl
						label="Modal Type"
						value={ linkModalType }
						onChange={ (value) => setAttributes({ linkModalType: value }) }
						options={ [
							{ label: 'Select Option...', value: '' },
							{ label: 'Form', value: 'form' },
							{ label: 'Subscribe', value: 'subscribe' },
							{ label: 'Video', value: 'video' },
							{ label: 'Zoom Meeting Registration', value: 'zoom_meeting_registration' }
						] }
					/> }

					{ !! (openModal && linkModalType == 'form') && <TextControl
						label="Form ID"
						value={ linkModalFormId }
						onChange={ (value) => setAttributes({ linkModalFormId: value }) }
					/> }

					{ !! (openModal && linkModalType == 'video') && <TextControl
						label="Video Embed URL"
						value={ linkModalVideoEmbed }
						onChange={ (value) => setAttributes({ linkModalVideoEmbed: value }) }
					/> }

					{ !! (openModal && linkModalType == 'zoom_meeting_registration') && <TextControl
						label="Meeting ID"
						value={ linkModalMeetingId }
						onChange={ (value) => setAttributes({ linkModalMeetingId: value }) }
					/> }

					<ToggleControl
						label={ 'Link opens Zoom registration' }
						checked={ openEventRegistration }
						onChange={ (value) => { setAttributes({ openEventRegistration: value }); } }
					/>

					{ !! openEventRegistration && <SelectControl
						label="Meeting Type"
						value={ meetingType }
						onChange={ (value) => setAttributes({ meetingType: value }) }
						options={ [
							{ value: 'meetings', label: 'Meeting' },
							{ value: 'webinars', label: 'Webinar' }
						] }
					/> }

					{ !! openEventRegistration && <TextControl
						label="Meeting ID"
						value={ meetingId }
						onChange={ (value) => setAttributes({ meetingId: value }) }
					/> }

				</PanelBody>

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

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<BlockControls>
					<ToolbarGroup class="components-toolbar components-toolbar-group crown-block-button-toolbar">
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
			openNewWindow,
			openModal,
			linkModalType,
			linkModalFormId,
			linkModalVideoEmbed,
			linkModalMeetingId,
			openEventRegistration,
			meetingId,
			meetingType
		} = attributes;

		let blockClasses = [ className ];
		if(typeof alignment != 'undefined') blockClasses.push('text-alignment-' + alignment);
		blockClasses.push('button-type-' + type + '-container');

		let buttonClasses = [ 'btn' ];

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
				blockClasses.push('btn-block-container');
				buttonClasses.push('btn-block');
			} else {
				blockClasses.push('btn-block-to-' + disabledDisplayAsBlockBreakpoint + '-container');
				buttonClasses.push('btn-block-to-' + disabledDisplayAsBlockBreakpoint);
			}
		}

		if(openModal) {

			if(linkModalType == 'subscribe') {
				return (
					<p className={ blockClasses.join(' ') }>
						<button className={ buttonClasses.join(' ') } data-toggle="modal" data-target="#subscribe-modal">
							<span class="btn-label">{ label }</span>
						</button>
					</p>
				);
			}

			if(linkModalType == 'form' && linkModalFormId != '') {
				return (
					<p className={ blockClasses.join(' ') }>
						<button className={ buttonClasses.join(' ') } data-toggle="modal" data-target={ '#form-' + parseInt(linkModalFormId) + '-modal' }>
							<span class="btn-label">{ label }</span>
						</button>
					</p>
				);
			}

			if(linkModalType == 'video' && linkModalVideoEmbed != '') {
				return (
					<p className={ blockClasses.join(' ') }>
						<a href={ linkModalVideoEmbed } className={ buttonClasses.join(' ') } target="_blank" rel="noopener noreferrer" data-toggle="modal" data-target={ '#video-modal' }>
							<span class="btn-label">{ label }</span>
						</a>
					</p>
				);
			}

			if(linkModalType == 'zoom_meeting_registration' && linkModalMeetingId != '') {
				return (
					<p className={ blockClasses.join(' ') }>
						<button className={ buttonClasses.join(' ') } data-toggle="modal" data-target={ '#form-event-registration-zoom-meeting-' + parseInt(linkModalMeetingId) + '-modal' }>
							<span class="btn-label">{ label }</span>
						</button>
					</p>
				);
			}

		} else if(openEventRegistration) {

			return (
				<p className={ blockClasses.join(' ') }>
					<button className={ buttonClasses.join(' ') } data-toggle="collapse" data-target={ '#form-event-registration-zoom-meeting-' + parseInt(meetingId) }>
						<span class="btn-label">{ label }</span>
					</button>
				</p>
			);

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
