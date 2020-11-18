
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


registerBlockType('crown-blocks/featured-case-study-logos', {
	title: 'Featured Case Study Logos',
	description: 'Display the logos of the latest case studies published to the portfolio.',
	icon: 'portfolio',
	category: 'widgets',
	keywords: [ 'projects', 'portfolio', 'crown-blocks' ],

	supports: {},

	attributes: {
		maxPostCount:  { type: 'number', default: 12 },
		manuallySelectPosts: { type: 'boolean', default: false },
		filterServices: { type: 'array', default: [] },
		filterClientTypes: { type: 'array', default: [] },
		filterPostsExclude: { type: 'array', default: [] },
		filterPostsInclude: { type: 'array', default: [] }
	},


	edit: withSelect((select) => {
		return {
			posts: select('core').getEntityRecords('postType', 'case_study', { per_page: -1 }),
			services: select('core').getEntityRecords('taxonomy', 'case_study_service', { per_page: -1 }),
			client_types: select('core').getEntityRecords('taxonomy', 'case_study_client_type', { per_page: -1 })
		};
    })(({ posts, services, client_types, attributes, className, isSelected, setAttributes }) => {
		// console.log(posts, services, client_types);
		if(!posts || !services || !client_types) return '';

		const {
			maxPostCount,
			manuallySelectPosts,
			filterServices,
			filterClientTypes,
			filterPostsExclude,
			filterPostsInclude
		} = attributes;

		let maxPostCountOptions = [];
		for(let i = 1; i <= 20; i++) {
			maxPostCountOptions.push({ label: i, value: i });
		}

		let availableServices = {};
		let serviceSuggestions = [];
		for(let i in services) {
			let token = services[i].name + ' (ID: ' + services[i].id + ')'
			availableServices[token] = services[i];
			serviceSuggestions.push(token);
		}

		let availableClientTypes = {};
		let clientTypeSuggestions = [];
		for(let i in client_types) {
			let token = client_types[i].name + ' (ID: ' + client_types[i].id + ')'
			availableClientTypes[token] = client_types[i];
			clientTypeSuggestions.push(token);
		}

		let availablePostsExclude = {};
		let postsExcludeSuggestions = [];
		for(let i in posts) {
			let token = posts[i].title.rendered + ' (ID: ' + posts[i].id + ')'
			availablePostsExclude[token] = posts[i];
			postsExcludeSuggestions.push(token);
		}

		let availablePostsInclude = {};
		let postsIncludeSuggestions = [];
		for(let i in posts) {
			let token = posts[i].title.rendered + ' (ID: ' + posts[i].id + ')'
			availablePostsInclude[token] = posts[i];
			postsIncludeSuggestions.push(token);
		}

		let blockClasses = [ className ];

		return [

			<InspectorControls key="inspector-controls">

				<PanelBody title={ 'Appearance' } initialOpen={ true }>

					<SelectControl
						label="Max number of logos to display"
						value={ maxPostCount }
						onChange={ (value) => setAttributes({ maxPostCount: value }) }
						options={ maxPostCountOptions }
					/>

				</PanelBody>

				<PanelBody title={ 'Filtering Options' } initialOpen={ true }>

					<ToggleControl
						label={ 'Manually select case studies' }
						checked={ manuallySelectPosts }
						onChange={ (value) => { setAttributes({ manuallySelectPosts: value }); } }
					/>

					{ !! !manuallySelectPosts && <FormTokenField 
						label="Filter by Service"
						value={ filterServices } 
						suggestions={ serviceSuggestions } 
						onChange={ (tokens) => {
							let matchedTokens = [];
							for(let i in tokens) {
								let token = typeof tokens[i] === 'string' ? tokens[i] : (tokens[i].value ? tokens[i].value : '');
								if(serviceSuggestions.includes(token)) {
									matchedTokens.push(token);
								}
							}
							let filterServices = [];
							for(let i in matchedTokens) {
								if(availableServices[matchedTokens[i]]) {
									filterServices.push({ value: matchedTokens[i], id: availableServices[matchedTokens[i]].id });
								}
							}
							setAttributes({ filterServices: filterServices })
						} }
						placeholder="Search services..."
					/> }

					{ !! !manuallySelectPosts && <FormTokenField 
						label="Filter by Client Type"
						value={ filterClientTypes } 
						suggestions={ clientTypeSuggestions } 
						onChange={ (tokens) => {
							let matchedTokens = [];
							for(let i in tokens) {
								let token = typeof tokens[i] === 'string' ? tokens[i] : (tokens[i].value ? tokens[i].value : '');
								if(clientTypeSuggestions.includes(token)) {
									matchedTokens.push(token);
								}
							}
							let filterClientTypes = [];
							for(let i in matchedTokens) {
								if(availableClientTypes[matchedTokens[i]]) {
									filterClientTypes.push({ value: matchedTokens[i], id: availableClientTypes[matchedTokens[i]].id });
								}
							}
							setAttributes({ filterClientTypes: filterClientTypes })
						} }
						placeholder="Search client types..."
					/> }

					{ !! !manuallySelectPosts && <FormTokenField 
						label="Exclude Specific Case Studies from Feed"
						value={ filterPostsExclude } 
						suggestions={ postsExcludeSuggestions } 
						onChange={ (tokens) => {
							let matchedTokens = [];
							for(let i in tokens) {
								let token = typeof tokens[i] === 'string' ? tokens[i] : (tokens[i].value ? tokens[i].value : '');
								if(postsExcludeSuggestions.includes(token)) {
									matchedTokens.push(token);
								}
							}
							let filterPostsExclude = [];
							for(let i in matchedTokens) {
								if(availablePostsExclude[matchedTokens[i]]) {
									filterPostsExclude.push({ value: matchedTokens[i], id: availablePostsExclude[matchedTokens[i]].id });
								}
							}
							setAttributes({ filterPostsExclude: filterPostsExclude })
						} }
						placeholder="Search case studies..."
					/> }

					{ !! manuallySelectPosts && <FormTokenField 
						label="Select which case studies to display (add in desired order)"
						value={ filterPostsInclude } 
						suggestions={ postsIncludeSuggestions } 
						onChange={ (tokens) => {
							let matchedTokens = [];
							for(let i in tokens) {
								let token = typeof tokens[i] === 'string' ? tokens[i] : (tokens[i].value ? tokens[i].value : '');
								if(postsIncludeSuggestions.includes(token)) {
									matchedTokens.push(token);
								}
							}
							let filterPostsInclude = [];
							for(let i in matchedTokens) {
								if(availablePostsInclude[matchedTokens[i]]) {
									filterPostsInclude.push({ value: matchedTokens[i], id: availablePostsInclude[matchedTokens[i]].id });
								}
							}
							setAttributes({ filterPostsInclude: filterPostsInclude })
						} }
						placeholder="Search case studies..."
					/> }

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<ServerSideRender block="crown-blocks/featured-case-study-logos" attributes={ attributes } />

			</div>

		];
	})


} );
