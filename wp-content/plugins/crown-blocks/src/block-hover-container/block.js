
// import helper functions
import CrownBlocks from '../common.js';

// import CSS
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InnerBlocks, RichText, Editable, MediaUpload, BlockControls, AlignmentToolbar, InspectorControls, PanelColorSettings, URLInputButton, URLInput } = wp.blockEditor;
const { PanelBody, Popover, RadioControl, ColorPicker, ColorPalette, ToolbarButton, ToolbarGroup, Button, ButtonGroup, Icon, RangeControl, FocalPointPicker, ToggleControl, TextControl, SelectControl, BaseControl } = wp.components;
const { getColorObjectByColorValue } = wp.blockEditor;


registerBlockType('crown-blocks/hover-container', {
	title: 'Hover Container',
	description: 'Add a container that links to another page.',
	icon: <svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg"><g fill-rule="evenodd"><g><path d="M15,22 L15,24 L9,24 L9,22 L15,22 Z M2,16.999 L2,20 C2,21.1045695 2.8954305,22 4,22 L4,22 L7,22 L7,24 L3,24 C1.34314575,24 -1.27867033e-14,22.6568542 -1.29896094e-14,21 L1.22124533e-15,17 L2,16.999 Z M24,17 L24,21 C24,22.6568542 22.6568542,24 21,24 L17,24 L17,22 L20,22 C21.0543618,22 21.9181651,21.1841222 21.9945143,20.1492623 L22,20 L22,16.999 L24,17 Z M24,9 L24,15 L22,14.999 L22,8.999 L24,9 Z M2,8.999 L2,14.999 L1.22124533e-15,15 L1.22124533e-15,9 L2,8.999 Z M21,4.4408921e-15 C22.6568542,4.13653291e-15 24,1.34314575 24,3 L24,7 L22,6.999 L22,4 C22,2.9456382 21.1841222,2.08183488 20.1492623,2.00548574 L20,2 L17,2 L17,4.4408921e-15 L21,4.4408921e-15 Z M7,4.4408921e-15 L7,2 L4,2 C2.9456382,2 2.08183488,2.81587779 2.00548574,3.85073766 L2,4 L2,6.999 L1.22124533e-15,7 L-1.29896094e-14,3 C-1.31925155e-14,1.34314575 1.34314575,4.74525129e-15 3,4.4408921e-15 L7,4.4408921e-15 Z M15,2 L9,2 L9,4.4408921e-15 L15,4.4408921e-15 L15,2 Z" id="container"></path></g></g></svg>,
	category: 'layout',
	keywords: [ 'section', 'link', 'crown-blocks' ],

	supports: {},

	attributes: {
		title: { type: 'string', selector: '.title', source: 'html' },
		content: { type: 'string', selector: '.content', source: 'html' },
		ctaLabel: { type: 'string', default: 'view', selector: '.cta-label', source: 'html' },
		linkUrl: { type: 'string', default: '' },
		linkPost: { type: 'object' },
		hoverType: { type: 'string', default: 'default' },
		backgroundColor: { type: 'string', default: '#012D61' },
		backgroundColorHover: { type: 'string', default: '#0381C3' },
		backgroundImageId: { type: 'number' },
		backgroundImageData: { type: 'object' },
		backgroundImageFocalPoint: { type: 'object', default: { x: 0.5, y: 0.5 } },
		backgroundImageOpacity: { type: 'number', default: 100 },
		backgroundImageGrayscale: { type: 'number', default: 0 },
		backgroundImageBlendMode: { type: 'string', default: 'normal' },
		openNewWindow: { type: 'boolean', default: false },
		openModal: { type: 'boolean', default: false },
		linkModalType: { type: 'string', default: '' },
		linkModalFormId: { type: 'string', default: '' },
		linkModalVideoEmbed: { type: 'string', default: '' },
		linkModalMeetingId: { type: 'string', default: '' },
		textColor: { type: 'string', default: 'auto' },
		textColorHover: { type: 'string', default: 'auto' }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			title,
			content,
			ctaLabel,
			linkUrl,
			linkPost,
			hoverType,
			backgroundColor,
			backgroundColorHover,
			backgroundImageId,
			backgroundImageData,
			backgroundImageFocalPoint,
			backgroundImageOpacity,
			backgroundImageGrayscale,
			backgroundImageBlendMode,
			openNewWindow,
			openModal,
			linkModalType,
			linkModalFormId,
			linkModalVideoEmbed,
			linkModalMeetingId,
			textColor,
			textColorHover
		} = attributes;

		let blockClasses = [ className ];
		blockClasses.push('hover-type-' + hoverType);

		if(textColor == 'auto' && backgroundColor) {
			blockClasses.push('text-color-' + (CrownBlocks.isDarkColor(backgroundColor) ? 'light' : 'dark'));
		} else if(textColor != 'auto') {
			blockClasses.push('text-color-' + textColor);
		}

		if(textColorHover == 'auto' && backgroundColorHover) {
			blockClasses.push('text-color-hover-' + (CrownBlocks.isDarkColor(backgroundColorHover) ? 'light' : 'dark'));
		} else if(textColorHover != 'auto') {
			blockClasses.push('text-color-hover-' + textColorHover);
		}

		let bgStyle = {};
		if(backgroundColor) {
			bgStyle.backgroundColor = backgroundColor;
		}

		let bgHoverStyle = {};
		if(backgroundColorHover) {
			bgHoverStyle.backgroundColor = backgroundColorHover;
		}

		let backgroundImageUrl = null;
		if(backgroundImageId) {
			backgroundImageUrl = backgroundImageData.sizes.fullscreen ? backgroundImageData.sizes.fullscreen.url : backgroundImageData.url;
			blockClasses.push('has-bg-image');
		}

		return [

			<InspectorControls key="inspector-controls">

				<PanelColorSettings
					title={ 'Color' }
					initialOpen={ true }
					colorSettings={ [
						{
							label: 'Background Color',
							value: backgroundColor,
							onChange: (value) => setAttributes({ backgroundColor: value })
						},
						{
							label: 'Background Hover Color',
							value: backgroundColorHover,
							onChange: (value) => setAttributes({ backgroundColorHover: value })
						}
					] }
				/>

				<PanelBody title={ 'Background Image' } className={ 'crown-blocks-background-image' } initialOpen={ true }>

					{ !! backgroundImageId && <FocalPointPicker 
						label="Focal Point"
						url={ backgroundImageData.sizes.medium ? backgroundImageData.sizes.medium.url : backgroundImageData.sizes.thumbnail.url }
						dimensions={ { width: 400, height: 100 } }
						value={ backgroundImageFocalPoint }
						onChange={ (value) => setAttributes({ backgroundImageFocalPoint: value }) } 
					/> }

					<MediaUpload
						onSelect={ (media) => { setAttributes({ backgroundImageId: media.id, backgroundImageData: media, backgroundImageFocalPoint: { x: 0.5, y: 0.5 } }); } }
						type="image"
						value={ backgroundImageId }
						render={ ({ open }) => (
							<div className={ 'crown-blocks-media-upload' }>
								{/* { backgroundImageId && <Button className={ 'image-preview' } onClick={ open }><img src={ backgroundImageData.sizes.medium ? backgroundImageData.sizes.medium.url : backgroundImageData.sizes.thumbnail.url } /></Button> } */}
								<Button className={ 'button' } onClick={ open }>Select Image</Button>
								{ backgroundImageId && <Button className={ 'button is-link is-destructive' } onClick={ (e) => { setAttributes({ backgroundImageId: null, backgroundImageData: null }); } }>Remove Image</Button> }
							</div>
						) }
					/>

					{ !! backgroundImageId && <RangeControl
						label="Opacity"
						value={ backgroundImageOpacity }
						onChange={ (value) => setAttributes({ backgroundImageOpacity: value }) }
						min={ 0 }
						max={ 100 }
					/> }

					{ !! backgroundImageId && <RangeControl
						label="Grayscale"
						value={ backgroundImageGrayscale }
						onChange={ (value) => setAttributes({ backgroundImageGrayscale: value }) }
						min={ 0 }
						max={ 100 }
					/> }

					{ !! backgroundImageId && <SelectControl
						label="Blend Mode"
						value={ backgroundImageBlendMode }
						onChange={ (value) => setAttributes({ backgroundImageBlendMode: value }) }
						options={ [
							{ label: 'Normal', value: 'normal' },
							{ label: 'Multiply', value: 'multiply' },
							{ label: 'Screen', value: 'screen' },
							{ label: 'Overlay', value: 'overlay' },
							{ label: 'Soft Light', value: 'soft-light' },
							{ label: 'Hard Light', value: 'hard-light' },
							{ label: 'Darken', value: 'darken' },
							{ label: 'Lighten', value: 'lighten' }
						] }
					/> }

				</PanelBody>

				<PanelBody title={ 'Text Color' } initialOpen={ true }>
					
					<BaseControl label="Default Color">
						<div>
							<ButtonGroup>
								<Button isPrimary={ textColor == 'auto' } isSecondary={ textColor != 'auto' } onClick={ (e) => setAttributes({ textColor: 'auto' }) }>Auto</Button>
								<Button isPrimary={ textColor == 'dark' } isSecondary={ textColor != 'dark' } onClick={ (e) => setAttributes({ textColor: 'dark' }) }>Dark</Button>
								<Button isPrimary={ textColor == 'light' } isSecondary={ textColor != 'light' } onClick={ (e) => setAttributes({ textColor: 'light' }) }>Light</Button>
							</ButtonGroup>
						</div>
					</BaseControl>

					<BaseControl label="Hover Color">
						<div>
							<ButtonGroup>
								<Button isPrimary={ textColorHover == 'auto' } isSecondary={ textColorHover != 'auto' } onClick={ (e) => setAttributes({ textColorHover: 'auto' }) }>Auto</Button>
								<Button isPrimary={ textColorHover == 'dark' } isSecondary={ textColorHover != 'dark' } onClick={ (e) => setAttributes({ textColorHover: 'dark' }) }>Dark</Button>
								<Button isPrimary={ textColorHover == 'light' } isSecondary={ textColorHover != 'light' } onClick={ (e) => setAttributes({ textColorHover: 'light' }) }>Light</Button>
							</ButtonGroup>
						</div>
					</BaseControl>

				</PanelBody>

				<PanelBody title={ 'Appearance' } initialOpen={ true }>

					<SelectControl
						label="Hover Type"
						value={ hoverType }
						onChange={ (value) => setAttributes({ hoverType: value }) }
						options={ [
							{ label: 'Default', value: 'default' },
							{ label: 'Left-Aligned', value: 'left-aligned' }
						] }
					/>

				</PanelBody>

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

				<div className={ blockClasses.join(' ') } key="container">
					<div className="container-bg" style={ bgStyle }>
						{ backgroundImageUrl && <div className={ 'bg-image' } style={ {
							backgroundImage: 'url(' + backgroundImageUrl + ')',
							backgroundPosition: `${ backgroundImageFocalPoint.x * 100 }% ${ backgroundImageFocalPoint.y * 100 }%`,
							opacity: (backgroundImageOpacity / 100),
							filter: `grayscale(${ backgroundImageGrayscale / 100 })`,
							mixBlendMode: backgroundImageBlendMode
						} }></div> }
					</div>
					<div className="container-bg hover" style={ bgHoverStyle }></div>
					<div className="inner">
						<div className="container-contents">
							<div className="inner">
								
								<RichText
									tagName="h3"
									className="title"
									placeholder="Title"
									onChange={ (value) => setAttributes({ title: value }) } 
									value={ title }
									allowedFormats={ [] }
								/>

								<div class="details">
									<div class="inner">

										<RichText
											tagName="p"
											className="content"
											placeholder="Description"
											onChange={ (value) => setAttributes({ content: value }) } 
											value={ content }
											allowedFormats={ [ 'core/bold', 'core/italic' ] }
										/>

										<div class="cta-label-container">
											<RichText
												tagName="p"
												className="cta-label"
												onChange={ (value) => setAttributes({ ctaLabel: value }) } 
												value={ ctaLabel }
												allowedFormats={ [] }
											/>
										</div>

									</div>
								</div>

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
			content,
			ctaLabel,
			linkUrl,
			linkPost,
			hoverType,
			backgroundColor,
			backgroundColorHover,
			backgroundImageId,
			backgroundImageData,
			backgroundImageFocalPoint,
			backgroundImageOpacity,
			backgroundImageGrayscale,
			backgroundImageBlendMode,
			openNewWindow,
			openModal,
			linkModalType,
			linkModalFormId,
			linkModalVideoEmbed,
			linkModalMeetingId,
			textColor,
			textColorHover
		} = attributes;

		let blockClasses = [ className ];
		blockClasses.push('hover-type-' + hoverType);

		if(textColor == 'auto' && backgroundColor) {
			blockClasses.push('text-color-' + (CrownBlocks.isDarkColor(backgroundColor) ? 'light' : 'dark'));
		} else if(textColor != 'auto') {
			blockClasses.push('text-color-' + textColor);
		}

		if(textColorHover == 'auto' && backgroundColorHover) {
			blockClasses.push('text-color-hover-' + (CrownBlocks.isDarkColor(backgroundColorHover) ? 'light' : 'dark'));
		} else if(textColorHover != 'auto') {
			blockClasses.push('text-color-hover-' + textColorHover);
		}

		let bgStyle = {};
		if(backgroundColor) {
			bgStyle.backgroundColor = backgroundColor;
		}

		let bgHoverStyle = {};
		if(backgroundColorHover) {
			bgHoverStyle.backgroundColor = backgroundColorHover;
		}

		let backgroundImageUrl = null;
		if(backgroundImageId) {
			backgroundImageUrl = backgroundImageData.sizes.fullscreen ? backgroundImageData.sizes.fullscreen.url : backgroundImageData.url;
			blockClasses.push('has-bg-image');
		}

		let linkHref = linkUrl;
		let linkOpenNewWindow = openNewWindow;
		let linkDataToggle = null;
		let linkDataTarget = null;
		if(openModal) {
			if(linkModalType == 'subscribe') {
				linkHref = '#';
				linkOpenNewWindow = false;
				linkDataToggle = 'modal';
				linkDataTarget = '#subscribe-modal';
			}
			if(linkModalType == 'form' && linkModalFormId != '') {
				linkHref = '#';
				linkOpenNewWindow = false;
				linkDataToggle = 'modal';
				linkDataTarget = '#form-' + parseInt(linkModalFormId) + '-modal';
			}
			if(linkModalType == 'video' && linkModalVideoEmbed != '') {
				linkHref = linkModalVideoEmbed;
				linkOpenNewWindow = true;
				linkDataToggle = 'modal';
				linkDataTarget = '#video-modal';
			}
			if(linkModalType == 'zoom_meeting_registration' && linkModalMeetingId != '') {
				linkHref = '#';
				linkOpenNewWindow = false;
				linkDataToggle = 'modal';
				linkDataTarget = '#form-event-registration-zoom-meeting-' + parseInt(linkModalMeetingId) + '-modal';
			}
		}

		return (
			<div className={ blockClasses.join(' ') } key="container">
				<div className="container-bg" style={ bgStyle }>
					{ backgroundImageUrl && <div className={ 'bg-image' } style={ {
						backgroundImage: 'url(' + backgroundImageUrl + ')',
						backgroundPosition: `${ backgroundImageFocalPoint.x * 100 }% ${ backgroundImageFocalPoint.y * 100 }%`,
						opacity: (backgroundImageOpacity / 100),
						filter: `grayscale(${ backgroundImageGrayscale / 100 })`,
						mixBlendMode: backgroundImageBlendMode
					} }></div> }
				</div>
				<div className="container-bg hover" style={ bgHoverStyle }></div>
				<div className="inner">
					<div className="container-contents">
						<div className="inner">
							<RichText.Content tagName="h3" className="title" value={ title } />
							<div class="details">
								<div class="inner">
									<RichText.Content tagName="p" className="content" value={ content } />
									<div class="cta-label-container">
										<RichText.Content tagName="p" className="cta-label" value={ ctaLabel } />
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<a
					class="container-link"
					href={ linkHref }
					target={ linkOpenNewWindow && '_blank' }
					rel={ linkOpenNewWindow && 'noopener noreferrer' }
					data-toggle={ linkDataToggle && linkDataToggle }
					data-target={ linkDataTarget && linkDataTarget }
				></a>
			</div>
		);
	},


	deprecated: [

		{
			attributes: {
				title: { type: 'string', selector: '.title', source: 'html' },
				content: { type: 'string', selector: '.content', source: 'html' },
				ctaLabel: { type: 'string', default: 'view', selector: '.cta-label', source: 'html' },
				linkUrl: { type: 'string', default: '' },
				linkPost: { type: 'object' },
				hoverType: { type: 'string', default: 'default' },
				backgroundColor: { type: 'string', default: '#012D61' },
				backgroundColorHover: { type: 'string', default: '#0381C3' },
				backgroundImageId: { type: 'number' },
				backgroundImageData: { type: 'object' },
				backgroundImageFocalPoint: { type: 'object', default: { x: 0.5, y: 0.5 } },
				openNewWindow: { type: 'boolean', default: false },
				openModal: { type: 'boolean', default: false },
				linkModalType: { type: 'string', default: '' },
				linkModalFormId: { type: 'string', default: '' },
				linkModalVideoEmbed: { type: 'string', default: '' },
				linkModalMeetingId: { type: 'string', default: '' },
				textColor: { type: 'string', default: 'auto' },
				textColorHover: { type: 'string', default: 'auto' }
			},
			save: ({ attributes, className }) => {

				const {
					title,
					content,
					ctaLabel,
					linkUrl,
					linkPost,
					hoverType,
					backgroundColor,
					backgroundColorHover,
					backgroundImageId,
					backgroundImageData,
					backgroundImageFocalPoint,
					openNewWindow,
					openModal,
					linkModalType,
					linkModalFormId,
					linkModalVideoEmbed,
					linkModalMeetingId,
					textColor,
					textColorHover
				} = attributes;
		
				let blockClasses = [ className ];
				blockClasses.push('hover-type-' + hoverType);
		
				if(textColor == 'auto' && backgroundColor) {
					blockClasses.push('text-color-' + (CrownBlocks.isDarkColor(backgroundColor) ? 'light' : 'dark'));
				} else if(textColor != 'auto') {
					blockClasses.push('text-color-' + textColor);
				}
		
				if(textColorHover == 'auto' && backgroundColorHover) {
					blockClasses.push('text-color-hover-' + (CrownBlocks.isDarkColor(backgroundColorHover) ? 'light' : 'dark'));
				} else if(textColorHover != 'auto') {
					blockClasses.push('text-color-hover-' + textColorHover);
				}
		
				let bgStyle = {};
				if(backgroundColor) {
					bgStyle.backgroundColor = backgroundColor;
				}
		
				let bgHoverStyle = {};
				if(backgroundColorHover) {
					bgHoverStyle.backgroundColor = backgroundColorHover;
				}
		
				let backgroundImageUrl = null;
				if(backgroundImageId) {
					backgroundImageUrl = backgroundImageData.sizes.fullscreen ? backgroundImageData.sizes.fullscreen.url : backgroundImageData.url;
					blockClasses.push('has-bg-image');
				}
		
				let linkHref = linkUrl;
				let linkOpenNewWindow = openNewWindow;
				let linkDataToggle = null;
				let linkDataTarget = null;
				if(openModal) {
					if(linkModalType == 'subscribe') {
						linkHref = '#';
						linkOpenNewWindow = false;
						linkDataToggle = 'modal';
						linkDataTarget = '#subscribe-modal';
					}
					if(linkModalType == 'form' && linkModalFormId != '') {
						linkHref = '#';
						linkOpenNewWindow = false;
						linkDataToggle = 'modal';
						linkDataTarget = '#form-' + parseInt(linkModalFormId) + '-modal';
					}
					if(linkModalType == 'video' && linkModalVideoEmbed != '') {
						linkHref = linkModalVideoEmbed;
						linkOpenNewWindow = true;
						linkDataToggle = 'modal';
						linkDataTarget = '#video-modal';
					}
					if(linkModalType == 'zoom_meeting_registration' && linkModalMeetingId != '') {
						linkHref = '#';
						linkOpenNewWindow = false;
						linkDataToggle = 'modal';
						linkDataTarget = '#form-event-registration-zoom-meeting-' + parseInt(linkModalMeetingId) + '-modal';
					}
				}
		
				return (
					<div className={ blockClasses.join(' ') } key="container">
						<div className="container-bg" style={ bgStyle }>
							{ backgroundImageUrl && <div className={ 'bg-image' } style={ {
								backgroundImage: 'url(' + backgroundImageUrl + ')',
								backgroundPosition: `${ backgroundImageFocalPoint.x * 100 }% ${ backgroundImageFocalPoint.y * 100 }%`
							} }></div> }
						</div>
						<div className="container-bg hover" style={ bgHoverStyle }></div>
						<div className="inner">
							<div className="container-contents">
								<div className="inner">
									<RichText.Content tagName="h3" className="title" value={ title } />
									<div class="details">
										<div class="inner">
											<RichText.Content tagName="p" className="content" value={ content } />
											<div class="cta-label-container">
												<RichText.Content tagName="p" className="cta-label" value={ ctaLabel } />
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<a
							class="container-link"
							href={ linkHref }
							target={ linkOpenNewWindow && '_blank' }
							rel={ linkOpenNewWindow && 'noopener noreferrer' }
							data-toggle={ linkDataToggle && linkDataToggle }
							data-target={ linkDataTarget && linkDataTarget }
						></a>
					</div>
				);
			}
		}

	]


} );
