
// import helper functions
import CrownBlocks from '../common.js';
import { withSelect } from '@wordpress/data';

// import CSS
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InnerBlocks, RichText, Editable, MediaUpload, BlockControls, AlignmentToolbar, InspectorControls, PanelColorSettings, URLInputButton, URLInput } = wp.blockEditor;
const { PanelBody, Popover, BaseControl, RadioControl, ColorPicker, ColorPalette, ToolbarButton, ToolbarGroup, Button, ButtonGroup, Icon, RangeControl, FocalPointPicker, ToggleControl, TextControl, SelectControl, FormTokenField } = wp.components;
const { getColorObjectByColorValue } = wp.blockEditor;
const { serverSideRender: ServerSideRender } = wp;


registerBlockType('crown-blocks/post-header', {
	title: 'Post Header',
	description: 'Displays at the top of the individual post template.',
	icon: 'admin-post',
	category: 'layout',
	keywords: [ 'blog', 'crown-blocks' ],

	supports: {},

	attributes: {
		backgroundImageId: { type: 'number', default: 0 },
		backgroundImagePreviewSrc: { type: 'string', default: '' },
		backgroundImageFocalPoint: { type: 'object', default: { x: 0.5, y: 0.5 } }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			backgroundImageId,
			backgroundImagePreviewSrc,
			backgroundImageFocalPoint
		} = attributes;

		return [

			<InspectorControls key="inspector-controls">

				<PanelBody title={ 'Background Image' } className={ 'crown-blocks-background-image' } initialOpen={ true }>

					{ !! backgroundImageId && <FocalPointPicker 
						label="Focal Point"
						url={ backgroundImagePreviewSrc }
						dimensions={ { width: 400, height: 100 } }
						value={ backgroundImageFocalPoint }
						onChange={ (value) => setAttributes({ backgroundImageFocalPoint: value }) } 
					/> }

					<MediaUpload
						onSelect={ (media) => { setAttributes({ backgroundImageId: media.id, backgroundImagePreviewSrc: media.sizes.medium ? media.sizes.medium.url : media.sizes.thumbnail.url, backgroundImageFocalPoint: { x: 0.5, y: 0.5 } }); } }
						type="image"
						value={ backgroundImageId }
						render={ ({ open }) => (
							<div className={ 'crown-blocks-media-upload' }>
								{/* { backgroundImageId && <Button className={ 'image-preview' } onClick={ open }><img src={ backgroundImageData.sizes.medium ? backgroundImageData.sizes.medium.url : backgroundImageData.sizes.thumbnail.url } /></Button> } */}
								<Button className={ 'button' } onClick={ open }>Select Image</Button>
								{ !! backgroundImageId && <Button className={ 'button is-link is-destructive' } onClick={ (e) => { setAttributes({ backgroundImageId: 0, backgroundImagePreviewSrc: '' }); } }>Remove Image</Button> }
							</div>
						) }
					/>

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<ServerSideRender block="crown-blocks/post-header" attributes={ attributes } />

			</div>

		];
	}


} );
