
// import helper functions
import CrownBlocks from '../common.js';

// import CSS
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InnerBlocks, RichText, Editable, MediaUpload, BlockControls, AlignmentToolbar, InspectorControls, PanelColorSettings, URLInput } = wp.blockEditor;
const { PanelBody, RadioControl, ColorPicker, Button, ButtonGroup, RangeControl, FocalPointPicker, ToggleControl, TextControl, TextareaControl, SelectControl, BaseControl } = wp.components;
const { PlainText } = wp.editor;


registerBlockType('crown-blocks/portal-link', {
	title: 'Portal Link',
	icon: 'screenoptions',
	category: 'layout',
	// parent: [ 'crown-blocks/portal-link-grid' ],
	keywords: [ 'cta', 'tile', 'crown-blocks' ],

	supports: {},

	attributes: {
		title: { selector: '.link-title', source: 'children' },
		linkType: { type: 'string', default: 'url' },
		linkFileId: { type: 'number' },
		linkFileData: { type: 'object' },
		linkUrl: { type: 'string', default: '' },
		linkPost: { type: 'object' },
		linkOpenInNewWindow: { type: 'boolean', default: false },
		isStyleOutline: { type: 'boolean', default: false },
		backgroundColor: { type: 'string', default: '#032040' },
		backgroundImageId: { type: 'number' },
		backgroundImageData: { type: 'object' },
		backgroundImageFocalPoint: { type: 'object', default: { x: 0.5, y: 0.5 } },
		backgroundImageOpacity: { type: 'number', default: 50 },
		textColor: { type: 'string', default: 'auto' }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			title,
			linkType,
			linkFileId,
			linkFileData,
			linkUrl,
			linkPost,
			linkOpenInNewWindow,
			isStyleOutline,
			backgroundColor,
			backgroundImageId,
			backgroundImageData,
			backgroundImageFocalPoint,
			backgroundImageOpacity,
			textColor
		} = attributes;

		let blockClasses = [ className ];

		if(textColor == 'auto' && backgroundColor) {
			blockClasses.push('text-color-' + (CrownBlocks.isDarkColor(backgroundColor) ? 'light' : 'dark'));
		} else if(textColor != 'auto') {
			blockClasses.push('text-color-' + textColor);
		}

		let blockStyle = {};
		let bgStyle = {};
		let outlineStyle = {};
		if(isStyleOutline) {
			blockClasses.push('is-style-outline');
			if(backgroundColor) {
				blockStyle.color = backgroundColor;
				outlineStyle.borderColor = backgroundColor;
			}
		}
		if(backgroundColor) {
			bgStyle.backgroundColor = backgroundColor;
		}

		let backgroundImageUrl = null;
		if(backgroundImageId) {
			backgroundImageUrl = backgroundImageData.sizes.fullscreen ? backgroundImageData.sizes.fullscreen.url : backgroundImageData.url;
			blockClasses.push('has-bg-image');
		}

		let blockLink = linkUrl;
		let blockLinkOpenInNewWindow = linkOpenInNewWindow;
		if(linkType == 'file') {
			blockLink = linkFileData ? linkFileData.url : '';
			blockLinkOpenInNewWindow = true;
		}

		let colorSettings = [];
		colorSettings.push({
			label: 'Background Color',
			value: backgroundColor,
			onChange: (value) => setAttributes({ backgroundColor: value })
		});

		return [

			<InspectorControls key="inspector-controls">

				<PanelBody title={ 'Call to Action' } initialOpen={ true }>

					<BaseControl label="Link Type">
						<div>
							<ButtonGroup>
								<Button isPrimary={ linkType == 'url' } isSecondary={ linkType != 'url' } onClick={ (e) => setAttributes({ linkType: 'url' }) }>Link URL</Button>
								<Button isPrimary={ linkType == 'file' } isSecondary={ linkType != 'file' } onClick={ (e) => setAttributes({ linkType: 'file' }) }>File Download</Button>
							</ButtonGroup>
						</div>
					</BaseControl>

					{ !! (linkType == 'file') && <MediaUpload
						onSelect={ (media) => { setAttributes({ linkFileId: media.id, linkFileData: media }); } }
						value={ linkFileId }
						render={ ({ open }) => (
							<div className={ 'crown-blocks-media-upload' }>
								{ linkFileId && <div><Button className={ 'image-preview' } onClick={ open }><img src={ linkFileData.sizes && linkFileData.sizes.medium ? linkFileData.sizes.medium.url : (linkFileData.sizes ? linkFileData.sizes.thumbnail.url : linkFileData.icon) } /></Button></div> }
								{ linkFileId && <div class="media-title">{ linkFileData.title }</div> }
								<Button className={ 'components-button is-secondary' } onClick={ open }>Select File</Button>
								{ linkFileId && <Button className={ 'components-button is-link is-destructive' } onClick={ (e) => { setAttributes({ linkFileId: null, linkFileData: null }); } }>Remove File</Button> }
							</div>
						) }
					/> }

					{ !! (linkType == 'url') && <TextControl
						label="Link URL"
						value={ linkUrl }
						placeholder="https://"
						onChange={ (url, post) => setAttributes({ linkUrl: url, linkPost: post }) }
						autoFocus={ false }
					/> }

					{ !! (linkType == 'url') && <ToggleControl
						label={ 'Open link in new window' }
						checked={ linkOpenInNewWindow }
						onChange={ (value) => { setAttributes({ linkOpenInNewWindow: value }); } }
					/> }

				</PanelBody>

				<PanelBody title={ 'Style' } initialOpen={ true }>

					<ToggleControl
						label={ 'Display as outlined block' }
						checked={ isStyleOutline }
						onChange={ (value) => { setAttributes({ isStyleOutline: value }); } }
					/>

				</PanelBody>

				<PanelColorSettings
					title={ 'Colors' }
					initialOpen={ true }
					colorSettings={ colorSettings }
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

				</PanelBody>

				<PanelBody title={ 'Text Color' } initialOpen={ true }>

					<ButtonGroup>
						<Button isPrimary={ textColor == 'auto' } isSecondary={ textColor != 'auto' } onClick={ (e) => setAttributes({ textColor: 'auto' }) }>Auto</Button>
						<Button isPrimary={ textColor == 'dark' } isSecondary={ textColor != 'dark' } onClick={ (e) => setAttributes({ textColor: 'dark' }) }>Dark</Button>
						<Button isPrimary={ textColor == 'light' } isSecondary={ textColor != 'light' } onClick={ (e) => setAttributes({ textColor: 'light' }) }>Light</Button>
					</ButtonGroup>

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<div className={ blockClasses.join(' ') } style={ blockStyle } key="link">
					<div class="link">
						<div className="link-bg" style={ bgStyle }>
							{ backgroundImageUrl && <div className={ 'bg-image' } style={ {
								backgroundImage: 'url(' + backgroundImageUrl + ')',
								opacity: (backgroundImageOpacity / 100),
								backgroundPosition: `${ backgroundImageFocalPoint.x * 100 }% ${ backgroundImageFocalPoint.y * 100 }%`
							} }></div> }
						</div>
						{ isStyleOutline && <div class="link-outline" style={ outlineStyle }></div> }
						<div className="inner">

							<div className="link-contents">

								<header class="link-header">

									<RichText
										tagName="h3"
										className="link-title"
										onChange={ (value) => setAttributes({ title: value }) } 
										value={ title }
										placeholder="Link Title"
										allowedFormats={ [] }
									/>

								</header>

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
			linkType,
			linkFileId,
			linkFileData,
			linkUrl,
			linkPost,
			linkOpenInNewWindow,
			isStyleOutline,
			backgroundColor,
			backgroundImageId,
			backgroundImageData,
			backgroundImageFocalPoint,
			backgroundImageOpacity,
			textColor
		} = attributes;

		let blockClasses = [ className ];

		if(textColor == 'auto' && backgroundColor) {
			blockClasses.push('text-color-' + (CrownBlocks.isDarkColor(backgroundColor) ? 'light' : 'dark'));
		} else if(textColor != 'auto') {
			blockClasses.push('text-color-' + textColor);
		}

		let blockStyle = {};
		let bgStyle = {};
		let outlineStyle = {};
		if(isStyleOutline) {
			blockClasses.push('is-style-outline');
			if(backgroundColor) {
				blockStyle.color = backgroundColor;
				outlineStyle.borderColor = backgroundColor;
			}
		}
		if(backgroundColor) {
			bgStyle.backgroundColor = backgroundColor;
		}

		let backgroundImageUrl = null;
		if(backgroundImageId) {
			backgroundImageUrl = backgroundImageData.sizes.fullscreen ? backgroundImageData.sizes.fullscreen.url : backgroundImageData.url;
			blockClasses.push('has-bg-image');
		}

		let blockLink = linkUrl;
		let blockLinkOpenInNewWindow = linkOpenInNewWindow;
		if(linkType == 'file') {
			blockLink = linkFileData ? linkFileData.url : '';
			blockLinkOpenInNewWindow = true;
		}

		return (

			<div className={ blockClasses.join(' ') } style={ blockStyle }>
				<a class="link" href={ blockLink } target={ blockLinkOpenInNewWindow && '_blank' } rel={ blockLinkOpenInNewWindow && 'noopener noreferrer' }>
					<div className="link-bg" style={ bgStyle }>
						{ backgroundImageUrl && <div className={ 'bg-image' } style={ {
							backgroundImage: 'url(' + backgroundImageUrl + ')',
							opacity: (backgroundImageOpacity / 100),
							backgroundPosition: `${ backgroundImageFocalPoint.x * 100 }% ${ backgroundImageFocalPoint.y * 100 }%`
						} }></div> }
					</div>
					{ isStyleOutline && <div class="link-outline" style={ outlineStyle }></div> }
					<div class="inner">
						<div className="link-contents">

							<header class="link-header">
								{ !! title && <RichText.Content tagName="h3" className="link-title" value={ title } /> }
							</header>

						</div>
					</div>
				</a>
			</div>

		);
	},


} );
