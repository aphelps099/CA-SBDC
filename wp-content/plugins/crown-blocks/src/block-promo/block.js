
// import helper functions
import CrownBlocks from '../common.js';

// import CSS
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InnerBlocks, RichText, Editable, MediaUpload, BlockControls, AlignmentToolbar, InspectorControls, PanelColorSettings, URLInput } = wp.blockEditor;
const { PanelBody, RadioControl, ColorPicker, Button, ButtonGroup, RangeControl, FocalPointPicker, ToggleControl, TextControl, SelectControl } = wp.components;


registerBlockType('crown-blocks/promo', {
	title: 'Promo',
	icon: 'star-filled',
	category: 'layout',
	keywords: [ 'hero', 'header', 'crown-blocks' ],

	supports: {
		align: [ 'wide', 'full' ],
		anchor: true
	},

	attributes: {
		align: {
			type: 'string',
			default: ''
		},
		displayJumbo: {
			type: 'boolean',
			default: false
		},
		backgroundColor: {
			type: 'string',
			default: '#24282F'
		},
		accentColor: {
			type: 'string',
			default: '#65A5F4'
		},
		linkColor: {
			type: 'string',
			default: '#65A5F4'
		},
		featuredImageId: {
			type: 'number'
		},
		featuredImageData: {
			type: 'object'
		},
		featuredImageFocalPoint: {
			type: 'object',
			default: { x: 0.5, y: 0.5 }
		},
		featuredImageLeft: {
			type: 'boolean',
			default: false
		},
		reverseAngle: {
			type: 'boolean',
			default: false
		},
		textColor: {
			type: 'string',
			default: 'auto'
		},
		overline: {
			selector: '.promo-overline',
			source: 'children'
		},
		title: {
			selector: '.promo-title',
			source: 'children'
		},
		description: {
			selector: '.promo-description',
			source: 'children'
		},
		linkUrl: {
			type: 'string',
			default: ''
		},
		linkPost: {
			type: 'object'
		},
		linkLabel: {
			type: 'string',
			default: ''
		}
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		let disallowedBlocks = [
			'crown-blocks/promo'
		];
		const ALLOWED_BLOCKS = wp.blocks.getBlockTypes().map(block => block.name).filter(blockName => !disallowedBlocks.includes(blockName));

		const {
			displayJumbo,
			backgroundColor,
			accentColor,
			linkColor,
			featuredImageId,
			featuredImageData,
			featuredImageFocalPoint,
			featuredImageLeft,
			reverseAngle,
			textColor,
			overline,
			title,
			description,
			linkUrl,
			linkPost,
			linkLabel
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

		let featuredImageUrl = null;
		if(featuredImageId) {
			featuredImageUrl = featuredImageData.sizes.medium_large ? featuredImageData.sizes.medium_large.url : featuredImageData.url;
			blockClasses.push('has-featured-image');
			if(featuredImageLeft) blockClasses.push('featured-image-left');
			if(reverseAngle) blockClasses.push('reverse-angle');
		}

		if(displayJumbo) {
			blockClasses.push('display-jumbo');
		}

		return [

			<InspectorControls key="inspector-controls">

				<PanelBody title={ 'Featured Image' } className={ 'crown-blocks-featured-image' } initialOpen={ true }>

					{ !! featuredImageId && <FocalPointPicker 
						label="Focal Point"
						url={ featuredImageData.sizes.medium ? featuredImageData.sizes.medium.url : featuredImageData.sizes.thumbnail.url }
						dimensions={ { width: 400, height: 100 } }
						value={ featuredImageFocalPoint }
						onChange={ (value) => setAttributes({ featuredImageFocalPoint: value }) } 
					/> }

					<MediaUpload
						onSelect={ (media) => { setAttributes({ featuredImageId: media.id, featuredImageData: media, featuredImageFocalPoint: { x: 0.5, y: 0.5 } }); } }
						type="image"
						value={ featuredImageId }
						render={ ({ open }) => (
							<div className={ 'crown-blocks-media-upload' }>
								{/* { featuredImageId && <Button className={ 'image-preview' } onClick={ open }><img src={ featuredImageData.sizes.medium ? featuredImageData.sizes.medium.url : featuredImageData.sizes.thumbnail.url } /></Button> } */}
								<Button className={ 'button' } onClick={ open }>Select Image</Button>
								{ featuredImageId && <Button className={ 'button is-link is-destructive' } onClick={ (e) => { setAttributes({ featuredImageId: null, featuredImageData: null }); } }>Remove Image</Button> }
							</div>
						) }
					/>

					{ !! featuredImageId && <ToggleControl
						label={ 'Display image on left' }
						checked={ featuredImageLeft }
						onChange={ (value) => { setAttributes({ featuredImageLeft: value }); } }
					/> }

					{ !! featuredImageId && <ToggleControl
						label={ 'Reverse angle' }
						checked={ reverseAngle }
						onChange={ (value) => { setAttributes({ reverseAngle: value }); } }
					/> }

				</PanelBody>

				<PanelBody title={ 'Link' } initialOpen={ true }>

					<TextControl
						label="Link URL"
						value={ linkUrl }
						placeholder="https://"
						onChange={ (url, post) => setAttributes({ linkUrl: url, linkPost: post }) }
						autoFocus={ false }
					/>

					<TextControl
						label="Link CTA Label"
						value={ linkLabel }
						onChange={ (value) => setAttributes({ linkLabel: value }) }
					/>

				</PanelBody>

				<PanelBody title={ 'Appearance' } initialOpen={ true }>

					<ToggleControl
						label={ 'Jumbo display' }
						checked={ displayJumbo }
						onChange={ (value) => { setAttributes({ displayJumbo: value }); } }
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

				<PanelColorSettings
					title={ 'Accent Color' }
					initialOpen={ false }
					colorSettings={ [
						{
							label: 'Accent Color',
							value: accentColor,
							onChange: (value) => setAttributes({ accentColor: value })
						}
					] }
				/>

				{ (linkUrl != '' && linkLabel != '') && <PanelColorSettings
					title={ 'Link Color' }
					initialOpen={ false }
					colorSettings={ [
						{
							label: 'Link Color',
							value: linkColor,
							onChange: (value) => setAttributes({ linkColor: value }),
							disableCustomColors: true
						}
					] }
				/> }

				<PanelBody title={ 'Text Color' } initialOpen={ false }>

					<ButtonGroup>
						<Button isPrimary={ textColor == 'auto' } isSecondary={ textColor != 'auto' } onClick={ (e) => setAttributes({ textColor: 'auto' }) }>Auto</Button>
						<Button isPrimary={ textColor == 'dark' } isSecondary={ textColor != 'dark' } onClick={ (e) => setAttributes({ textColor: 'dark' }) }>Dark</Button>
						<Button isPrimary={ textColor == 'light' } isSecondary={ textColor != 'light' } onClick={ (e) => setAttributes({ textColor: 'light' }) }>Light</Button>
					</ButtonGroup>

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<div className={ blockClasses.join(' ') } style={ blockStyle } key="promo">
					<div className="inner">

						<div className="promo-contents">
							<div className="inner">

								<RichText
									tagName="h6"
									className="promo-overline"
									onChange={ (value) => setAttributes({ overline: value }) } 
									value={ overline }
									placeholder="Optional Overline"
									allowedFormats={ [] }
									style={ { color: accentColor } }
								/>

								<RichText
									tagName="h3"
									className="promo-title"
									onChange={ (value) => setAttributes({ title: value }) } 
									value={ title }
									placeholder="Enter a title"
									allowedFormats={ [] }
								/>

								<RichText
									tagName="p"
									className="promo-description"
									onChange={ (value) => setAttributes({ description: value }) } 
									value={ description }
									placeholder="Provide an optional description..."
									allowedFormats={ [ 'core/bold', 'core/italic' ] }
								/>

								{ (linkUrl && linkLabel) && <p class="cta-link">
									<span class="icon-link" style={ { color: linkColor } }>
										{ linkLabel }
										<svg class="bi bi-arrow-right-short" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
											<path fill-rule="evenodd" d="M8.146 4.646a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.793 8 8.146 5.354a.5.5 0 0 1 0-.708z"/>
											<path fill-rule="evenodd" d="M4 8a.5.5 0 0 1 .5-.5H11a.5.5 0 0 1 0 1H4.5A.5.5 0 0 1 4 8z"/>
										</svg>
									</span>
								</p> }

							</div>
						</div>

						{ featuredImageUrl && <div className="promo-featured-image">
							<div class="inner" style={ { borderColor: accentColor } }>
								<div className={ 'image' } style={ {
									backgroundImage: 'url(' + featuredImageUrl + ')',
									backgroundPosition: `${ featuredImageFocalPoint.x * 100 }% ${ featuredImageFocalPoint.y * 100 }%`,
								} }>
									<div class="overlay" style={ { backgroundColor: accentColor } }></div>
									<img src={ featuredImageUrl } />
								</div>
							</div>
						</div> }

					</div>
				</div>

			</div>

		];
	},

	
	save: ({ attributes, className }) => {
		
		const {
			displayJumbo,
			backgroundColor,
			accentColor,
			linkColor,
			featuredImageId,
			featuredImageData,
			featuredImageFocalPoint,
			featuredImageLeft,
			reverseAngle,
			textColor,
			overline,
			title,
			description,
			linkUrl,
			linkPost,
			linkLabel
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

		let featuredImageUrl = null;
		if(featuredImageId) {
			featuredImageUrl = featuredImageData.sizes.medium_large.url ? featuredImageData.sizes.medium_large.url : featuredImageData.url;
			blockClasses.push('has-featured-image');
			if(featuredImageLeft) blockClasses.push('featured-image-left');
			if(reverseAngle) blockClasses.push('reverse-angle');
		}

		if(displayJumbo) {
			blockClasses.push('display-jumbo');
		}

		let ConditionalWrapper = ({ condition, wrapper, children }) => condition ? wrapper(children) : children;

		return (

			<div className={ blockClasses.join(' ') } style={ blockStyle }>
				<ConditionalWrapper condition={ linkUrl } wrapper={ children => <a href={ linkUrl }>{ children }</a> }>
					<div className="inner">

						<div className="promo-contents">
							<div className="inner">
								
								<RichText.Content tagName="h6" className="promo-overline" value={ overline } style={ { color: accentColor } } />
								<RichText.Content tagName="h3" className="promo-title" value={ title } />
								<RichText.Content tagName="p" className="promo-description" value={ description } />

								{ (linkUrl && linkLabel) && <p class="cta-link">
									<span class="icon-link" style={ { color: linkColor } }>
										{ linkLabel }
										<svg class="bi bi-arrow-right-short" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
											<path fill-rule="evenodd" d="M8.146 4.646a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.793 8 8.146 5.354a.5.5 0 0 1 0-.708z"/>
											<path fill-rule="evenodd" d="M4 8a.5.5 0 0 1 .5-.5H11a.5.5 0 0 1 0 1H4.5A.5.5 0 0 1 4 8z"/>
										</svg>
									</span>
								</p> }

							</div>
						</div>

						{ featuredImageUrl && <div className="promo-featured-image">
							<div class="inner" style={ { borderColor: accentColor } }>
								<div className={ 'image' } style={ {
									backgroundImage: 'url(' + featuredImageUrl + ')',
									backgroundPosition: `${ featuredImageFocalPoint.x * 100 }% ${ featuredImageFocalPoint.y * 100 }%`,
								} }>
									<div class="overlay" style={ { backgroundColor: accentColor } }></div>
									<img src={ featuredImageUrl } />
								</div>
							</div>
						</div> }

					</div>
				</ConditionalWrapper>
			</div>

		);
	},


} );
