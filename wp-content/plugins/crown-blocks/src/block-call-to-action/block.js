
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


registerBlockType('crown-blocks/call-to-action', {
	title: 'Call to Action',
	icon: 'megaphone',
	category: 'widgets',
	keywords: [ 'cta', 'form', 'crown-blocks' ],

	supports: {
		align: [ 'left', 'right', 'wide', 'full' ]
	},

	attributes: {
		align: { type: 'string', default: '' },
		backgroundColor: { type: 'string', default: '#F7F7F7' },
		backgroundImageId: { type: 'number' },
		backgroundImageData: { type: 'object' },
		backgroundImagePosition: { type: 'object', default: { x: 0.5, y: 0.5 } },
		backgroundImageSize: { type: 'number', default: 100 },
		textColor: { type: 'string', default: 'auto' },
		title: { selector: '.cta-title', source: 'children' },
		description: { selector: '.cta-description', source: 'children' },
		linkLabel: { type: 'string', defalt: 'Learn More' },
		linkType: { type: 'string', default: 'url' },
		linkUrl: { type: 'string', default: '' },
		linkPost: { type: 'object' },
		linkOpenInNewWindow: { type: 'boolean', default: false },
		contactFormTitle: { type: 'string', default: '' },
		contactFormEmbed: { type: 'string', default: '' },
		contactFormDisclaimer: { type: 'string', default: '' },
		contactFormOpenInModal: { type: 'boolean', default: false },
		additionalPaddingTop: { type: 'number', default: 0 },
		additionalPaddingBottom: { type: 'number', default: 0 }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			backgroundColor,
			backgroundImageId,
			backgroundImageData,
			backgroundImagePosition,
			backgroundImageSize,
			textColor,
			title,
			description,
			linkLabel,
			linkType,
			linkUrl,
			linkPost,
			linkOpenInNewWindow,
			contactFormTitle,
			contactFormEmbed,
			contactFormDisclaimer,
			contactFormOpenInModal,
			additionalPaddingTop,
			additionalPaddingBottom
		} = attributes;

		let blockClasses = [
			className
		];

		if(textColor == 'auto') {
			blockClasses.push('text-color-' + (CrownBlocks.isDarkColor(backgroundColor) ? 'light' : 'dark'));
		} else {
			blockClasses.push('text-color-' + textColor);
		}

		let blockStyle = {
			backgroundColor: backgroundColor,
		};

		let backgroundImageUrl = null;
		if(backgroundImageId) {
			backgroundImageUrl = backgroundImageData.sizes.medium_large ? backgroundImageData.sizes.medium_large.url : backgroundImageData.url;
			blockClasses.push('has-background-image');
		}

		let bgOverlayStyle = { backgroundImage: `radial-gradient( closest-side, ${ backgroundColor }, ${ backgroundColor } 60%, transparent )` };
		let bgColorRGB = CrownBlocks.hexToRgb(backgroundColor);
		if(bgColorRGB) {
			bgOverlayStyle.backgroundImage = `radial-gradient( closest-side, ${ backgroundColor }, ${ backgroundColor } 60%, rgba(${ bgColorRGB.r }, ${ bgColorRGB.g }, ${ bgColorRGB.b }, 0) )`;
		}

		return [

			<InspectorControls key="inspector-controls">

				<PanelBody title={ 'Call to Action' } initialOpen={ true }>

					<TextControl
						label="Link Label"
						value={ linkLabel }
						onChange={ (value) => setAttributes({ linkLabel: value }) }
					/>

					<SelectControl
						label="Link Type"
						value={ linkType }
						onChange={ (value) => setAttributes({ linkType: value }) }
						options={ [
							{ label: 'URL Link', value: 'url' },
							{ label: 'Contact Form', value: 'contact-form' }
						] }
					/>

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

					{ !! (linkType == 'contact-form') && <TextControl
						label="Form Title"
						value={ contactFormTitle }
						onChange={ (value) => setAttributes({ contactFormTitle: value }) }
					/> }

					{ !! (linkType == 'contact-form') && <TextareaControl
						label="Embed Script"
						value={ contactFormEmbed }
						onChange={ (value) => setAttributes({ contactFormEmbed: value }) }
						rows={ 4 }
					/> }

					{ !! (linkType == 'contact-form') && <TextareaControl
						label="Disclaimer"
						value={ contactFormDisclaimer }
						onChange={ (value) => setAttributes({ contactFormDisclaimer: value }) }
						rows={ 6 }
					/> }

					{/* { !! (linkType == 'contact-form') && <ToggleControl
						label={ 'Open form in modal window' }
						checked={ contactFormOpenInModal }
						onChange={ (value) => { setAttributes({ contactFormOpenInModal: value }); } }
					/> } */}

				</PanelBody>

				<PanelBody title={ 'Background Image' } className={ 'crown-blocks-background-image' } initialOpen={ false }>

					{ !! backgroundImageId && <FocalPointPicker 
						label="Image Position"
						className="crown-blocks-background-position-picker"
						url={ backgroundImageData.sizes.medium ? backgroundImageData.sizes.medium.url : backgroundImageData.sizes.thumbnail.url }
						dimensions={ { width: 400, height: 100 } }
						value={ backgroundImagePosition }
						onChange={ (value) => setAttributes({ backgroundImagePosition: value }) } 
					/> }

					<MediaUpload
						onSelect={ (media) => { setAttributes({ backgroundImageId: media.id, backgroundImageData: media, backgroundImagePosition: { x: 0.5, y: 0.5 }, backgroundImageSize: media.width } ); } }
						type="image"
						value={ backgroundImageId }
						render={ ({ open }) => (
							<div className={ 'crown-blocks-media-upload' }>
								{/* { backgroundImageId && <Button className={ 'image-preview' } onClick={ open }><img src={ backgroundImageData.sizes.medium ? backgroundImageData.sizes.medium.url : backgroundImageData.sizes.thumbnail.url } /></Button> } */}
								<Button className={ 'button' } onClick={ open }>Select Image</Button>
								{ backgroundImageId && <Button className={ 'button is-link is-destructive' } onClick={ (e) => { setAttributes({ backgroundImageId: null, backgroundImageData: null, backgroundImageSize: 100 }); } }>Remove Image</Button> }
							</div>
						) }
					/>

					{ !! backgroundImageId && <RangeControl
						label="Image Size"
						value={ backgroundImageSize }
						onChange={ (value) => setAttributes({ backgroundImageSize: value }) }
						min={ 0 }
						max={ backgroundImageData.width }
					/> }

				</PanelBody>

				<PanelBody title={ 'Spacing' } initialOpen={ false }>

					<RangeControl
						label="Additional Top Padding"
						value={ additionalPaddingTop }
						onChange={ (value) => setAttributes({ additionalPaddingTop: value }) }
						min={ 0 }
						max={ 600 }
						step={ 10 }
					/>

					<RangeControl
						label="Additional Bottom Padding"
						value={ additionalPaddingBottom }
						onChange={ (value) => setAttributes({ additionalPaddingBottom: value }) }
						min={ 0 }
						max={ 600 }
						step={ 10 }
					/>

				</PanelBody>

				<PanelColorSettings
					title={ 'Background Color' }
					initialOpen={ false }
					colorSettings={ [
						{
							label: 'Background Color',
							value: backgroundColor,
							onChange: (value) => setAttributes({ backgroundColor: value })
						}
					] }
				/>

				<PanelBody title={ 'Text Color' } initialOpen={ false }>

					<ButtonGroup>
						<Button isPrimary={ textColor == 'auto' } isSecondary={ textColor != 'auto' } onClick={ (e) => setAttributes({ textColor: 'auto' }) }>Auto</Button>
						<Button isPrimary={ textColor == 'dark' } isSecondary={ textColor != 'dark' } onClick={ (e) => setAttributes({ textColor: 'dark' }) }>Dark</Button>
						<Button isPrimary={ textColor == 'light' } isSecondary={ textColor != 'light' } onClick={ (e) => setAttributes({ textColor: 'light' }) }>Light</Button>
					</ButtonGroup>

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<div className={ blockClasses.join(' ') } style={ blockStyle } key="cta">
					<div className="inner" style={ { paddingTop: additionalPaddingTop, paddingBottom: additionalPaddingBottom } }>

						{ backgroundImageUrl && <div className="cta-bg-image">
							<div class="inner">
								<div className={ 'image' } style={ {
									backgroundImage: 'url(' + backgroundImageUrl + ')',
									backgroundPosition: `${ backgroundImagePosition.x * 100 }% ${ backgroundImagePosition.y * 100 }%`,
									backgroundSize: `${ backgroundImageSize }px ${ (backgroundImageSize / backgroundImageData.width) * backgroundImageData.height }px `
								} }></div>
							</div>
						</div> }

						<div className="cta-contents">
							<div class="overlay" style={ bgOverlayStyle }></div>
							<div className="inner">

								<RichText
									tagName="h3"
									className="cta-title"
									onChange={ (value) => setAttributes({ title: value }) } 
									value={ title }
									placeholder="Enter a title"
									allowedFormats={ [] }
								/>

								<RichText
									tagName="p"
									className="cta-description"
									onChange={ (value) => setAttributes({ description: value }) } 
									value={ description }
									placeholder="Provide an optional description..."
									allowedFormats={ [ 'core/bold', 'core/italic' ] }
								/>

								<p class="cta-link">
									<span class="btn btn-primary">{ linkLabel ? linkLabel : 'Learn More' }</span>
								</p>

							</div>
						</div>

					</div>
				</div>

			</div>

		];
	},

	
	save: ({ attributes, className }) => {
		
		const {
			backgroundColor,
			backgroundImageId,
			backgroundImageData,
			backgroundImagePosition,
			backgroundImageSize,
			textColor,
			title,
			description,
			linkLabel,
			linkType,
			linkUrl,
			linkPost,
			linkOpenInNewWindow,
			contactFormTitle,
			contactFormEmbed,
			contactFormDisclaimer,
			contactFormOpenInModal,
			additionalPaddingTop,
			additionalPaddingBottom
		} = attributes;

		let blockClasses = [
			className
		];

		if(textColor == 'auto') {
			blockClasses.push('text-color-' + (CrownBlocks.isDarkColor(backgroundColor) ? 'light' : 'dark'));
		} else {
			blockClasses.push('text-color-' + textColor);
		}

		let blockStyle = {
			backgroundColor: attributes.backgroundColor,
		};

		let backgroundImageUrl = null;
		if(backgroundImageId) {
			backgroundImageUrl = backgroundImageData.sizes.medium_large ? backgroundImageData.sizes.medium_large.url : backgroundImageData.url;
			blockClasses.push('has-background-image');
		}

		let bgOverlayStyle = { backgroundImage: `radial-gradient( closest-side, ${ backgroundColor }, ${ backgroundColor } 60%, transparent )` };
		let bgColorRGB = CrownBlocks.hexToRgb(backgroundColor);
		if(bgColorRGB) {
			bgOverlayStyle.backgroundImage = `radial-gradient( closest-side, ${ backgroundColor }, ${ backgroundColor } 60%, rgba(${ bgColorRGB.r }, ${ bgColorRGB.g }, ${ bgColorRGB.b }, 0) )`;
		}

		return (

			<div className={ blockClasses.join(' ') } style={ blockStyle }>

				<div className="inner" style={ { paddingTop: additionalPaddingTop, paddingBottom: additionalPaddingBottom } }>

					{ backgroundImageUrl && <div className="cta-bg-image">
						<div class="inner">
							<div className={ 'image' } style={ {
								backgroundImage: 'url(' + backgroundImageUrl + ')',
								backgroundPosition: `${ backgroundImagePosition.x * 100 }% ${ backgroundImagePosition.y * 100 }%`,
								backgroundSize: `${ backgroundImageSize }px ${ (backgroundImageSize / backgroundImageData.width) * backgroundImageData.height }px `
							} }></div>
						</div>
					</div> }

					<div className="cta-contents">
					<div class="overlay" style={ bgOverlayStyle }></div>
						<div className="inner">
							
							<RichText.Content tagName="h3" className="cta-title" value={ title } />
							<RichText.Content tagName="p" className="cta-description" value={ description } />

							{ (linkType == 'url' && linkUrl) && <p class="cta-link">
								<a class="btn btn-primary" href={ linkUrl } target={ linkOpenInNewWindow && '_blank' } rel={ linkOpenInNewWindow && 'noopener noreferrer' }>{ linkLabel ? linkLabel : 'Learn More' }</a>
							</p> }

							{ (linkType == 'contact-form' && contactFormEmbed) && <p class="cta-link">
								<button type="button" class="btn btn-primary contact-form-toggle" data-toggle="modal">{ linkLabel ? linkLabel : 'Learn More' }</button>
							</p> }

						</div>
					</div>

				</div>

				{ (linkType == 'contact-form' && contactFormEmbed) && <div class="modal fade contact-form" tabindex="-1" role="dialog">
					<div class="modal-dialog modal-dialog-centered">
						<div class="modal-content">
							<div class="modal-header">
								{ contactFormTitle && <h5 class="modal-title">{ contactFormTitle }</h5> }
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">
										<svg class="bi bi-x" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
											<path fill-rule="evenodd" d="M11.854 4.146a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708-.708l7-7a.5.5 0 0 1 .708 0z"/>
											<path fill-rule="evenodd" d="M4.146 4.146a.5.5 0 0 0 0 .708l7 7a.5.5 0 0 0 .708-.708l-7-7a.5.5 0 0 0-.708 0z"/>
										</svg>
									</span>
								</button>
							</div>
							<div class="modal-body">
								<div class="contact-form-container" dangerouslySetInnerHTML={ { __html: contactFormEmbed } }></div>
								{ contactFormDisclaimer && <p class="disclaimer" dangerouslySetInnerHTML={ { __html: contactFormDisclaimer } }></p> }
							</div>
						</div>
					</div>
				</div> }

			</div>

		);
	},


} );
