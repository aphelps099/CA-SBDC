
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


registerBlockType('crown-blocks/team-member-index', {
	title: 'Team Member Index',
	description: 'Displays all the members of the team.',
	icon: 'groups',
	category: 'widgets',
	keywords: [ 'staff', 'people', 'crown-blocks' ],

	supports: {},

	attributes: {
		postsPerPage: { type: 'number', default: 60 },
		scrollAnchor: { type: 'string', default: '' }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			postsPerPage,
			scrollAnchor
		} = attributes;

		let postsPerPageOptions = [];
		for(let i = 1; i <= 10; i++) {
			postsPerPageOptions.push({ label: i * 10, value: i * 10 });
		}

		let blockClasses = [ className ];

		return [

			<InspectorControls key="inspector-controls">

				<PanelBody title={ 'Appearance' } initialOpen={ true }>

					<SelectControl
						label="Number of team members to display per page"
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

				<ServerSideRender block="crown-blocks/team-member-index" attributes={ attributes } />

			</div>

		];
	}


} );
