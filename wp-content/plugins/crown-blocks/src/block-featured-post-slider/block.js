
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


registerBlockType('crown-blocks/featured-post-slider', {
	title: 'Featured Post Slider',
	description: 'Display a set of posts published to the blog.',
	icon: 'admin-post',
	category: 'widgets',
	keywords: [ 'feed', 'recent', 'crown-blocks' ],

	supports: {},

	attributes: {
		maxPostCount:  { type: 'string', default: '9' },
		manuallySelectPosts: { type: 'boolean', default: false },
		excludePrevPosts: { type: 'boolean', default: false },
		filterCategories: { type: 'array', default: [] },
		filterTags: { type: 'array', default: [] },
		filterTopics: { type: 'array', default: [] },
		filterPostsExclude: { type: 'array', default: [] },
		filterPostsInclude: { type: 'array', default: [] }
	},


	edit: withSelect((select) => {
		return {
			posts: select('core').getEntityRecords('postType', 'post', { per_page: -1 }),
			categories: select('core').getEntityRecords('taxonomy', 'category', { per_page: -1 }),
			tags: select('core').getEntityRecords('taxonomy', 'post_tag', { per_page: -1 }),
			topics: select('core').getEntityRecords('taxonomy', 'post_topic', { per_page: -1 })
		};
    })(({ posts, categories, tags, topics, attributes, className, isSelected, setAttributes }) => {
		if(!posts || !categories|| !tags || !topics) return '';

		const {
			maxPostCount,
			manuallySelectPosts,
			excludePrevPosts,
			filterCategories,
			filterTags,
			filterTopics,
			filterPostsExclude,
			filterPostsInclude
		} = attributes;

		let availableCategories = {};
		let categorySuggestions = [];
		for(let i in categories) {
			let token = categories[i].name + ' (ID: ' + categories[i].id + ')'
			availableCategories[token] = categories[i];
			categorySuggestions.push(token);
		}

		let availableTags = {};
		let tagSuggestions = [];
		for(let i in tags) {
			let token = tags[i].name + ' (ID: ' + tags[i].id + ')'
			availableTags[token] = tags[i];
			tagSuggestions.push(token);
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
						label="Max number of posts to display"
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
						label={ 'Manually select posts' }
						checked={ manuallySelectPosts }
						onChange={ (value) => { setAttributes({ manuallySelectPosts: value }); } }
					/>

					{ !! !manuallySelectPosts && <ToggleControl
						label={ 'Exclude posts featured in other recent post feeds above this on the page (note: does not affect output in editor)' }
						checked={ excludePrevPosts }
						onChange={ (value) => { setAttributes({ excludePrevPosts: value }); } }
					/> }

					{ !! !manuallySelectPosts && <FormTokenField 
						label="Filter by Category"
						value={ filterCategories } 
						suggestions={ categorySuggestions } 
						onChange={ (tokens) => {
							let matchedTokens = [];
							for(let i in tokens) {
								let token = typeof tokens[i] === 'string' ? tokens[i] : (tokens[i].value ? tokens[i].value : '');
								if(categorySuggestions.includes(token)) {
									matchedTokens.push(token);
								}
							}
							let filterCategories = [];
							for(let i in matchedTokens) {
								if(availableCategories[matchedTokens[i]]) {
									filterCategories.push({ value: matchedTokens[i], id: availableCategories[matchedTokens[i]].id });
								}
							}
							setAttributes({ filterCategories: filterCategories })
						} }
						placeholder="Search categories..."
					/> }

					{ !! !manuallySelectPosts && <FormTokenField 
						label="Filter by Tag"
						value={ filterTags } 
						suggestions={ tagSuggestions } 
						onChange={ (tokens) => {
							let matchedTokens = [];
							for(let i in tokens) {
								let token = typeof tokens[i] === 'string' ? tokens[i] : (tokens[i].value ? tokens[i].value : '');
								if(tagSuggestions.includes(token)) {
									matchedTokens.push(token);
								}
							}
							let filterTags = [];
							for(let i in matchedTokens) {
								if(availableTags[matchedTokens[i]]) {
									filterTags.push({ value: matchedTokens[i], id: availableTags[matchedTokens[i]].id });
								}
							}
							setAttributes({ filterTags: filterTags })
						} }
						placeholder="Search tags..."
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
						label="Exclude Specific Posts from Feed"
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
						placeholder="Search posts..."
					/> }

					{ !! manuallySelectPosts && <FormTokenField 
						label="Select which posts to display (add in desired order)"
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
						placeholder="Search posts..."
					/> }

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<ServerSideRender block="crown-blocks/featured-post-slider" attributes={ attributes } />

			</div>

		];
	})


} );
