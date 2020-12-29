
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


registerBlockType('crown-blocks/event-header', {
	title: 'Event Header',
	description: 'Displays at the top of the individual event template.',
	icon: 'calendar',
	category: 'layout',
	keywords: [ 'crown-blocks' ],

	supports: {},

	attributes: {
		backgroundImageId: { type: 'number' },
		backgroundImageData: { type: 'object' },
		backgroundImageFocalPoint: { type: 'object', default: { x: 0.5, y: 0.5 } }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			backgroundImageId,
			backgroundImageData,
			backgroundImageFocalPoint
		} = attributes;

		let blockAtts = {
			className: className,
			backgroundImageId: backgroundImageId,
			backgroundImageFocalPoint: backgroundImageFocalPoint
		};

		let blockClasses = [ className ];

		return [

			<InspectorControls key="inspector-controls">

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

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<ServerSideRender block="crown-blocks/event-header" attributes={ blockAtts } />

			</div>

		];
	}


} );
