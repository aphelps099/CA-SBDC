
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


registerBlockType('crown-blocks/client-story-index', {
	title: 'Client Story Index',
	description: 'Displays all the client stories published.',
	icon: 'testimonial',
	category: 'widgets',
	keywords: [ 'feed', 'crown-blocks' ],

	supports: {},

	attributes: {
		postsPerPage: { type: 'string', default: '6' },
		quoteContent: { type: 'string' },
		quoteSourceName: { type: 'string' },
		quoteSourceJobTitle: { type: 'string' },
		quoteSourcePhotoId: { type: 'string' },
		quoteSourcePhotoData: { type: 'object' },
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			postsPerPage,
			quoteContent,
			quoteSourceName,
			quoteSourceJobTitle,
			quoteSourcePhotoId,
			quoteSourcePhotoData
		} = attributes;

		let blockAtts = {
			className: className,
			postsPerPage: postsPerPage,
			quoteContent: quoteContent,
			quoteSourceName: quoteSourceName,
			quoteSourceJobTitle: quoteSourceJobTitle,
			quoteSourcePhotoId: quoteSourcePhotoId
		};

		let postsPerPageOptions = [];
		for(let i = 4; i <= 20; i += 2) {
			postsPerPageOptions.push({ label: i, value: i });
		}

		return [

			<InspectorControls key="inspector-controls">

				<PanelBody title={ 'Appearance' } initialOpen={ true }>

					<SelectControl
						label="Number of client stories to display per page"
						value={ postsPerPage }
						onChange={ (value) => setAttributes({ postsPerPage: value }) }
						options={ postsPerPageOptions }
					/>

				</PanelBody>

				<PanelBody title={ 'Pull Quote' } initialOpen={ true }>

					<TextareaControl
						label="Content"
						value={ quoteContent }
						onChange={ (value) => setAttributes({ quoteContent: value }) }
					/>

					<TextControl
						label="Source Name"
						value={ quoteSourceName }
						onChange={ (value) => setAttributes({ quoteSourceName: value }) }
					/>

					<TextControl
						label="Source Job Title"
						value={ quoteSourceJobTitle }
						onChange={ (value) => setAttributes({ quoteSourceJobTitle: value }) }
					/>

					<MediaUpload
						onSelect={ (media) => { setAttributes({ quoteSourcePhotoId: media.id + '', quoteSourcePhotoData: media }); } }
						type="image"
						value={ quoteSourcePhotoId }
						render={ ({ open }) => (
							<div className={ 'crown-blocks-media-upload' }>
								{ quoteSourcePhotoId && <Button className={ 'image-preview' } onClick={ open }><img src={ quoteSourcePhotoData.sizes.medium ? quoteSourcePhotoData.sizes.medium.url : quoteSourcePhotoData.sizes.thumbnail.url } /></Button> }
								<Button className={ 'button' } onClick={ open }>Select Image</Button>
								{ quoteSourcePhotoId && <Button className={ 'button is-link is-destructive' } onClick={ (e) => { setAttributes({ quoteSourcePhotoId: '' }); } }>Remove Image</Button> }
							</div>
						) }
					/>

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<ServerSideRender block="crown-blocks/client-story-index" attributes={ blockAtts } />

			</div>

		];
	}


} );
