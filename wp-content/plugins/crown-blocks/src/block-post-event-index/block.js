
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


registerBlockType('crown-blocks/post-event-index', {
	title: 'Post/Event Index',
	description: 'Displays all the posts & events published.',
	icon: 'admin-post',
	category: 'widgets',
	keywords: [ 'news', 'events', 'crown-blocks' ],

	supports: {},

	attributes: {
		postsPerPage: { type: 'string', default: '6' }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			postsPerPage
		} = attributes;

		let postsPerPageOptions = [];
		for(let i = 4; i <= 20; i += 2) {
			postsPerPageOptions.push({ label: i, value: i });
		}

		let blockClasses = [ className ];

		return [

			// <InspectorControls key="inspector-controls">

			// 	<PanelBody title={ 'Appearance' } initialOpen={ true }>

			// 		<SelectControl
			// 			label="Number of posts to display per page"
			// 			value={ postsPerPage }
			// 			onChange={ (value) => setAttributes({ postsPerPage: value }) }
			// 			options={ postsPerPageOptions }
			// 		/>

			// 	</PanelBody>

			// </InspectorControls>,

			<div class="crown-block-editor-container">

				<ServerSideRender block="crown-blocks/post-event-index" attributes={ attributes } />

			</div>

		];
	}


} );
