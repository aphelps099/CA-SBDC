
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


registerBlockType('crown-blocks/resource-index', {
	title: 'Resource Index',
	description: 'Displays all the resources published to the resource library.',
	icon: 'text-page',
	category: 'widgets',
	keywords: [ 'feed', 'crown-blocks' ],

	supports: {},

	attributes: {
		postsPerPage: { type: 'string', default: '12' }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			postsPerPage
		} = attributes;

		let postsPerPageOptions = [];
		for(let i = 6; i <= 24; i += 3) {
			postsPerPageOptions.push({ label: i, value: i });
		}

		let blockClasses = [ className ];

		return [

			<InspectorControls key="inspector-controls">

				<PanelBody title={ 'Appearance' } initialOpen={ true }>

					<SelectControl
						label="Number of resources to display per page"
						value={ postsPerPage }
						onChange={ (value) => setAttributes({ postsPerPage: value }) }
						options={ postsPerPageOptions }
					/>

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<ServerSideRender block="crown-blocks/resource-index" attributes={ attributes } />

			</div>

		];
	}


} );
