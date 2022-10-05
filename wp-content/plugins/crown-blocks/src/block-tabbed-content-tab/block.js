
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


registerBlockType('crown-blocks/tabbed-content-tab', {
	title: 'Tab',
	icon: 'index-card',
	category: 'layout',
	keywords: [ 'tab', 'slider', 'crown-blocks' ],
	parent: [ 'crown-blocks/tabbed-content' ],

	supports: {
		// inserter: false
	},

	attributes: {
		title: { selector: '.tab-title', source: 'children' },
		imageId: { type: 'number' },
		imageData: { type: 'object' }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			title,
			imageId,
			imageData
		} = attributes;

		let blockClasses = [
			className
		];

		let imageUrl = null;
		if(imageId) {
			imageUrl = imageData.sizes.large ? imageData.sizes.large.url : imageData.url;
		}

		return [

			<InspectorControls key="inspector-controls">

				<PanelBody title={ 'Image' } initialOpen={ true }>

					<MediaUpload
						onSelect={ (media) => { setAttributes({ imageId: media.id, imageData: media }) } }
						type="image"
						value={ imageId }
						render={ ({ open }) => (
							<div className={ 'crown-blocks-media-upload' }>
								{ imageId && <Button className={ 'image-preview' } onClick={ open }><img src={ imageData.sizes.medium ? imageData.sizes.medium.url : imageData.sizes.thumbnail.url } /></Button> }
								<Button className={ 'button' } onClick={ open }>Select Image</Button>
								{ imageId && <Button className={ 'button is-link is-destructive' } onClick={ (e) => { setAttributes({ imageId: null, imageData: null }); } }>Remove Image</Button> }
							</div>
						) }
					/>

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<div className={ blockClasses.join(' ') } key="tabbed-content-tab">
					<div className="inner">

						<RichText
							tagName="h3"
							className="tab-title"
							onChange={ (value) => setAttributes({ title: value }) } 
							value={ title }
							placeholder="Tab Title"
							allowedFormats={ [] }
						/>

						<div className="tab-contents">
							<div className="inner">

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
			title,
			imageId,
			imageData
		} = attributes;

		let blockClasses = [
			className
		];

		let imageUrl = null;
		if(imageId) {
			imageUrl = imageData.sizes.large ? imageData.sizes.large.url : imageData.url;
		}

		return (

			<div className={ blockClasses.join(' ') } key="tabbed-content-tab">
				<div className="inner">

					{ (title != '') && <RichText.Content tagName="h3" className="tab-title" value={ title } /> }

					{ imageUrl && <div class="tab-image"><img src={ imageUrl } /></div> }

					<div className="tab-contents">
						<div className="inner">

							<InnerBlocks.Content />

						</div>
					</div>

				</div>
			</div>

		);
	},


} );
