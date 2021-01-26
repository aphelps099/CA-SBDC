
// import helper functions
import CrownBlocks from '../common.js';
import { withSelect } from '@wordpress/data';

// import CSS
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InnerBlocks, RichText, Editable, MediaUpload, BlockControls, AlignmentToolbar, InspectorControls, PanelColorSettings, URLInputButton, URLInput } = wp.blockEditor;
const { PanelBody, Popover, BaseControl, RadioControl, ColorPicker, ColorPalette, ToolbarButton, ToolbarGroup, Button, ButtonGroup, Icon, RangeControl, FocalPointPicker, ToggleControl, TextControl, SelectControl, FormTokenField, AnglePickerControl } = wp.components;
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
		backgroundGradientEnabled: { type: 'boolean', default: false },
		backgroundGradientAngle: { type: 'number', default: 180 },
		backgroundColor: { type: 'string', default: '' },
		backgroundColorSecondary: { type: 'string', default: '' },
		backgroundImageId: { type: 'number', default: 0 },
		backgroundImagePreviewSrc: { type: 'string', default: '' },
		backgroundImageFocalPoint: { type: 'object', default: { x: 0.5, y: 0.5 } },
		backgroundImageOpacity: { type: 'number', default: 100 },
		backgroundImageGrayscale: { type: 'number', default: 0 },
		backgroundImageBlendMode: { type: 'string', default: 'normal' },
		backgroundImageContain: { type: 'boolean', default: false },
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			backgroundGradientEnabled,
			backgroundGradientAngle,
			backgroundColor,
			backgroundColorSecondary,
			backgroundImageId,
			backgroundImagePreviewSrc,
			backgroundImageFocalPoint,
			backgroundImageOpacity,
			backgroundImageGrayscale,
			backgroundImageBlendMode,
			backgroundImageContain
		} = attributes;

		let bgColorSettings = [];
		if(!backgroundGradientEnabled) {
			bgColorSettings.push({
				label: 'Background Color',
				value: backgroundColor,
				onChange: (value) => { setAttributes({ backgroundColor: value, backgroundColorSecondary: value }); }
			});
		} else {
			bgColorSettings.push({
				label: 'Background Gradient Start Color',
				value: backgroundColor,
				onChange: (value) => { setAttributes({ backgroundColor: value }); }
			});
			bgColorSettings.push({
				label: 'Background Gradient End Color',
				value: backgroundColorSecondary,
				onChange: (value) => { setAttributes({ backgroundColorSecondary: value }); }
			});
		}

		return [

			<InspectorControls key="inspector-controls">

				<PanelBody title={ 'Styles' } initialOpen={ true }>
					
					<ToggleControl
						label={ 'Enable Gradient background' }
						checked={ backgroundGradientEnabled }
						onChange={ (value) => { setAttributes({ backgroundGradientEnabled: value }); } }
					/>

					{ backgroundGradientEnabled && <AnglePickerControl
						label={ 'Gradient Angle' }
						value={ backgroundGradientAngle }
						onChange={ (value) => { setAttributes({ backgroundGradientAngle: value }); } }
					/> }

				</PanelBody>
				
				<PanelColorSettings
					title={ 'Background Color' }
					initialOpen={ true }
					colorSettings={ bgColorSettings }
				/>

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

					{/* { !! backgroundImageId && <ToggleControl
						label={ 'Contain background image' }
						checked={ backgroundImageContain }
						onChange={ (value) => { setAttributes({ backgroundImageContain: value }); } }
					/> } */}

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<ServerSideRender block="crown-blocks/post-header" attributes={ attributes } />

			</div>

		];
	}


} );
