
// import helper functions
import CrownBlocks from '../common.js';
import { withSelect } from '@wordpress/data';

// import CSS
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InnerBlocks, RichText, Editable, MediaUpload, BlockControls, AlignmentToolbar, InspectorControls, PanelColorSettings, URLInputButton, URLInput } = wp.blockEditor;
const { PanelBody, Popover, BaseControl, RadioControl, ColorPicker, ColorPalette, ToolbarButton, ToolbarGroup, Button, ButtonGroup, Icon, RangeControl, FocalPointPicker, ToggleControl, TextControl, TextareaControl, SelectControl, FormTokenField } = wp.components;
const { getColorObjectByColorValue } = wp.blockEditor;
const { serverSideRender: ServerSideRender } = wp;


registerBlockType('crown-blocks/client-story-header', {
	title: 'Client Story Header',
	description: 'Displays at the top of the individual client story template.',
	icon: 'testimonial',
	category: 'layout',
	keywords: [ 'crown-blocks' ],

	supports: {},

	attributes: {
		introContent: { type: 'string' },
		featuredImageId: { type: 'string' },
		featuredImageData: { type: 'object' },
		featuredImageFocalPoint: { type: 'object', default: { x: 0.5, y: 0.5 } }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			introContent,
			featuredImageId,
			featuredImageData,
			featuredImageFocalPoint
		} = attributes;

		let blockAtts = {
			className: className,
			introContent: introContent,
			featuredImageId: featuredImageId,
			featuredImageFocalPoint: featuredImageFocalPoint
		};

		return [

			<InspectorControls key="inspector-controls">

				<PanelBody title={ 'Content' } initialOpen={ true }>

					<TextareaControl
						label="Intro Content"
						value={ introContent }
						onChange={ (value) => setAttributes({ introContent: value }) }
					/>

				</PanelBody>

				<PanelBody title={ 'Featured Image' } className={ 'crown-blocks-background-image' } initialOpen={ true }>

					{ !! featuredImageId && <FocalPointPicker 
						label="Focal Point"
						url={ featuredImageData.sizes.medium ? featuredImageData.sizes.medium.url : featuredImageData.sizes.thumbnail.url }
						dimensions={ { width: 400, height: 100 } }
						value={ featuredImageFocalPoint }
						onChange={ (value) => setAttributes({ featuredImageFocalPoint: value }) } 
					/> }

					<MediaUpload
						onSelect={ (media) => { setAttributes({ featuredImageId: media.id + '', featuredImageData: media, featuredImageFocalPoint: { x: 0.5, y: 0.5 } }); } }
						type="image"
						value={ featuredImageId }
						render={ ({ open }) => (
							<div className={ 'crown-blocks-media-upload' }>
								<Button className={ 'button' } onClick={ open }>Select Image</Button>
								{ featuredImageId && <Button className={ 'button is-link is-destructive' } onClick={ (e) => { setAttributes({ featuredImageId: '', featuredImageData: null }); } }>Remove Image</Button> }
							</div>
						) }
					/>

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<ServerSideRender block="crown-blocks/client-story-header" attributes={ blockAtts } />

			</div>

		];
	}


} );
