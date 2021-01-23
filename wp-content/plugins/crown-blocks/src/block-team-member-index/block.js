
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
		postsPerPage: { type: 'string', default: '10' },
		filterCenters: { type: 'array', default: [] },
	},


	edit: withSelect((select) => {
		return {
			centers: select('core').getEntityRecords('taxonomy', 'post_center', { per_page: -1 })
		};
    })(({ centers, attributes, className, isSelected, setAttributes }) => {
		if(!centers) return '';

		const {
			postsPerPage,
			filterCenters
		} = attributes;

		let postsPerPageOptions = [];
		for(let i = 4; i <= 20; i += 2) {
			postsPerPageOptions.push({ label: i, value: i });
		}

		let availableCenters = {};
		let centerSuggestions = [];
		for(let i in centers) {
			let token = centers[i].name + ' (ID: ' + centers[i].id + ')'
			availableCenters[token] = centers[i];
			centerSuggestions.push(token);
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

				<PanelBody title={ 'Filtering Options' } initialOpen={ true }>

					<FormTokenField 
						label="Filter by SBDC"
						value={ filterCenters } 
						suggestions={ centerSuggestions } 
						onChange={ (tokens) => {
							let matchedTokens = [];
							for(let i in tokens) {
								let token = typeof tokens[i] === 'string' ? tokens[i] : (tokens[i].value ? tokens[i].value : '');
								if(centerSuggestions.includes(token)) {
									matchedTokens.push(token);
								}
							}
							let filterCenters = [];
							for(let i in matchedTokens) {
								if(availableCenters[matchedTokens[i]]) {
									filterCenters.push({ value: matchedTokens[i], id: availableCenters[matchedTokens[i]].id });
								}
							}
							setAttributes({ filterCenters: filterCenters })
						} }
						placeholder="Search centers..."
					/>

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<ServerSideRender block="crown-blocks/team-member-index" attributes={ attributes } />

			</div>

		];
	})


} );
