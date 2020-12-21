
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


registerBlockType('crown-blocks/featured-resource-slider', {
	title: 'Featured Resource Slider',
	description: 'Display a set of resources published to the resource library.',
	icon: 'text-page',
	category: 'widgets',
	keywords: [ 'feed', 'recent', 'crown-blocks' ],

	supports: {},

	attributes: {
		maxPostCount:  { type: 'string', default: '3' },
		manuallySelectPosts: { type: 'boolean', default: false },
		excludePrevPosts: { type: 'boolean', default: false },
		filterTypes: { type: 'array', default: [] },
		filterTopics: { type: 'array', default: [] },
		filterPostsExclude: { type: 'array', default: [] },
		filterPostsInclude: { type: 'array', default: [] }
	},


	edit: withSelect((select) => {
		return {
			posts: select('core').getEntityRecords('postType', 'resource', { per_page: -1 }),
			types: select('core').getEntityRecords('taxonomy', 'resource_type', { per_page: -1 }),
			topics: select('core').getEntityRecords('taxonomy', 'post_topic', { per_page: -1 })
		};
    })(({ posts, types, topics, attributes, className, isSelected, setAttributes }) => {
		if(!posts || !types || !topics) return '';

		const {
			maxPostCount,
			manuallySelectPosts,
			excludePrevPosts,
			filterTypes,
			filterTopics,
			filterPostsExclude,
			filterPostsInclude
		} = attributes;

		let availableTypes = {};
		let typeSuggestions = [];
		for(let i in types) {
			let token = types[i].name + ' (ID: ' + types[i].id + ')'
			availableTypes[token] = types[i];
			typeSuggestions.push(token);
		}

		let availableTopics = {};
		let topicSuggestions = [];
		for(let i in topics) {
			let token = topics[i].name + ' (ID: ' + topics[i].id + ')'
			availableTopics[token] = topics[i];
			topicSuggestions.push(token);
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
						label="Max number of resources to display"
						value={ maxPostCount }
						onChange={ (value) => setAttributes({ maxPostCount: value }) }
						options={ [
							{ label: '3', value: '3' },
							{ label: '6', value: '6' },
							{ label: '9', value: '9' },
							{ label: '12', value: '12' },
							{ label: '15', value: '15' },
							{ label: '18', value: '18' }
						] }
					/>

				</PanelBody>

				<PanelBody title={ 'Filtering Options' } initialOpen={ true }>

					<ToggleControl
						label={ 'Manually select resources' }
						checked={ manuallySelectPosts }
						onChange={ (value) => { setAttributes({ manuallySelectPosts: value }); } }
					/>

					{ !! !manuallySelectPosts && <ToggleControl
						label={ 'Exclude resources featured in other featured resources sliders above this on the page (note: does not affect output in editor)' }
						checked={ excludePrevPosts }
						onChange={ (value) => { setAttributes({ excludePrevPosts: value }); } }
					/> }

					{ !! !manuallySelectPosts && <FormTokenField 
						label="Filter by Type"
						value={ filterTypes } 
						suggestions={ typeSuggestions } 
						onChange={ (tokens) => {
							let matchedTokens = [];
							for(let i in tokens) {
								let token = typeof tokens[i] === 'string' ? tokens[i] : (tokens[i].value ? tokens[i].value : '');
								if(typeSuggestions.includes(token)) {
									matchedTokens.push(token);
								}
							}
							let filterTypes = [];
							for(let i in matchedTokens) {
								if(availableTypes[matchedTokens[i]]) {
									filterTypes.push({ value: matchedTokens[i], id: availableTypes[matchedTokens[i]].id });
								}
							}
							setAttributes({ filterTypes: filterTypes })
						} }
						placeholder="Search types..."
					/> }

					{ !! !manuallySelectPosts && <FormTokenField 
						label="Filter by Topic"
						value={ filterTopics } 
						suggestions={ topicSuggestions } 
						onChange={ (tokens) => {
							let matchedTokens = [];
							for(let i in tokens) {
								let token = typeof tokens[i] === 'string' ? tokens[i] : (tokens[i].value ? tokens[i].value : '');
								if(topicSuggestions.includes(token)) {
									matchedTokens.push(token);
								}
							}
							let filterTopics = [];
							for(let i in matchedTokens) {
								if(availableTopics[matchedTokens[i]]) {
									filterTopics.push({ value: matchedTokens[i], id: availableTopics[matchedTokens[i]].id });
								}
							}
							setAttributes({ filterTopics: filterTopics })
						} }
						placeholder="Search topics..."
					/> }

					{ !! !manuallySelectPosts && <FormTokenField 
						label="Exclude Specific Resources from Feed"
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
						placeholder="Search resources..."
					/> }

					{ !! manuallySelectPosts && <FormTokenField 
						label="Select which resources to display (add in desired order)"
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
						placeholder="Search resources..."
					/> }

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<ServerSideRender block="crown-blocks/featured-resource-slider" attributes={ attributes } />

			</div>

		];
	})


} );
