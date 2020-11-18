
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


registerBlockType('crown-blocks/case-study-index', {
	title: 'Case Study Index',
	description: 'Displays all the case studies published to the portfolio.',
	icon: 'portfolio',
	category: 'widgets',
	keywords: [ 'projects', 'portfolio', 'crown-blocks' ],

	supports: {},

	attributes: {
		postsPerPage: { type: 'string', default: '8' },
		scrollAnchor: { type: 'string', default: '' }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			postsPerPage,
			scrollAnchor
		} = attributes;

		let postsPerPageOptions = [];
		for(let i = 1; i <= 10; i++) {
			postsPerPageOptions.push({ label: i * 2, value: i * 2 });
		}

		let blockClasses = [ className ];

		return [

			<InspectorControls key="inspector-controls">

				<PanelBody title={ 'Appearance' } initialOpen={ true }>

					<SelectControl
						label="Number of case studies to display per page"
						value={ postsPerPage }
						onChange={ (value) => setAttributes({ postsPerPage: value }) }
						options={ postsPerPageOptions }
					/>

				</PanelBody>

				<PanelBody title={ 'Interactivity' } initialOpen={ true }>

					<TextControl
						label="HTML anchor to scroll to upon filtering"
						value={ scrollAnchor }
						onChange={ (value) => setAttributes({ scrollAnchor: value }) }
					/>

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<ServerSideRender block="crown-blocks/case-study-index" attributes={ attributes } />

			</div>

		];
	}


} );
