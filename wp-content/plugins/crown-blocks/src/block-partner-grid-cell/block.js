
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


registerBlockType('crown-blocks/partner-grid-cell', {
	title: 'Partner Grid Cell',
	icon: 'screenoptions',
	category: 'layout',
	// parent: [ 'crown-blocks/partner-grid' ],
	keywords: [ 'cta', 'tile', 'crown-blocks' ],

	supports: {},

	attributes: {
		title: { selector: '.link-title', source: 'children' },
		linkUrl: { type: 'string', default: '' },
		linkOpenInNewWindow: { type: 'boolean', default: true },
		logoImageId: { type: 'number' },
		logoImageData: { type: 'object' }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			title,
			linkUrl,
			linkOpenInNewWindow,
			logoImageId,
			logoImageData
		} = attributes;

		let blockClasses = [ className ];

		let blockStyle = {};
		let bgStyle = {};
		let outlineStyle = {};

		let logoImageUrl = null;
		if(logoImageId) {
			logoImageUrl = logoImageData.sizes.large ? logoImageData.sizes.large.url : logoImageData.url;
		}

		let blockLink = linkUrl;
		let blockLinkOpenInNewWindow = linkOpenInNewWindow;

		return [

			<InspectorControls key="inspector-controls">

				<PanelBody title={ 'Link Settings' } initialOpen={ true }>

					<TextControl
						label="Link URL"
						value={ linkUrl }
						placeholder="https://"
						onChange={ (url, post) => setAttributes({ linkUrl: url, linkPost: post }) }
						autoFocus={ false }
					/>

					<ToggleControl
						label={ 'Open link in new window' }
						checked={ linkOpenInNewWindow }
						onChange={ (value) => { setAttributes({ linkOpenInNewWindow: value }); } }
					/>

				</PanelBody>

				<PanelBody title={ 'Logo' } initialOpen={ true }>

					<MediaUpload
						onSelect={ (media) => { setAttributes({ logoImageId: media.id, logoImageData: media }) } }
						type="image"
						value={ logoImageId }
						render={ ({ open }) => (
							<div className={ 'crown-blocks-media-upload' }>
								{ logoImageId && <Button className={ 'image-preview' } onClick={ open }><img src={ logoImageData.sizes.medium ? logoImageData.sizes.medium.url : logoImageData.sizes.thumbnail.url } /></Button> }
								<Button className={ 'button' } onClick={ open }>Select Image</Button>
								{ logoImageId && <Button className={ 'button is-link is-destructive' } onClick={ (e) => { setAttributes({ logoImageId: null, logoImageData: null }); } }>Remove Image</Button> }
							</div>
						) }
					/>

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<div className={ blockClasses.join(' ') } style={ blockStyle } key="link">
					<div class="link">
						<div className="link-logo" style={ bgStyle }>
							{ logoImageUrl && <div className={ 'logo-image' } style={ {
								backgroundImage: 'url(' + logoImageUrl + ')'
							} }></div> }
						</div>
					</div>
				</div>

			</div>

		];
	},

	
	save: ({ attributes, className }) => {
		
		const {
			title,
			linkUrl,
			linkOpenInNewWindow,
			logoImageId,
			logoImageData
		} = attributes;

		let blockClasses = [ className ];

		let blockStyle = {};
		let bgStyle = {};
		let outlineStyle = {};

		let logoImageUrl = null;
		if(logoImageId) {
			logoImageUrl = logoImageData.sizes.large ? logoImageData.sizes.large.url : logoImageData.url;
		}

		let blockLink = linkUrl;
		let blockLinkOpenInNewWindow = linkOpenInNewWindow;

		return (

			<div className={ blockClasses.join(' ') } style={ blockStyle }>
				<a class="link" href={ blockLink } target={ blockLinkOpenInNewWindow && '_blank' } rel={ blockLinkOpenInNewWindow && 'noopener noreferrer' }>
					<div className="link-logo" style={ bgStyle }>
						{ logoImageUrl && <div className={ 'logo-image' } style={ {
							backgroundImage: 'url(' + logoImageUrl + ')'
						} }></div> }
					</div>
				</a>
			</div>

		);
	},


} );
