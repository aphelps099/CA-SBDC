
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


registerBlockType('crown-blocks/featured-webinar-slider', {
	title: 'Featured Webinar Slider',
	description: 'Display a set of webinars published to the webinar library.',
	icon: 'text-page',
	category: 'widgets',
	keywords: [ 'feed', 'recent', 'crown-blocks' ],

	supports: {},

	attributes: {
		maxPostCount:  { type: 'string', default: '3' },
		manuallySelectPosts: { type: 'boolean', default: false },
		excludePrevPosts: { type: 'boolean', default: false },
		filterTopics: { type: 'array', default: [] },
		filterPostsExclude: { type: 'array', default: [] },
		filterPostsInclude: { type: 'array', default: [] }
	},


	edit: withSelect((select) => {
		return {
			posts: select('core').getEntityRecords('postType', 'webinar', { per_page: -1 }),
			topics: select('core').getEntityRecords('taxonomy', 'post_topic', { per_page: -1 })
		};
    })(({ posts, topics, attributes, className, isSelected, setAttributes }) => {
		if(!posts || !topics) return '';

		const {
			maxPostCount,
			manuallySelectPosts,
			excludePrevPosts,
			filterTopics,
			filterPostsExclude,
			filterPostsInclude
		} = attributes;

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
						label="Max number of webinars to display"
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
						label={ 'Manually select webinars' }
						checked={ manuallySelectPosts }
						onChange={ (value) => { setAttributes({ manuallySelectPosts: value }); } }
					/>

					{ !! !manuallySelectPosts && <ToggleControl
						label={ 'Exclude webinars featured in other featured webinars sliders above this on the page (note: does not affect output in editor)' }
						checked={ excludePrevPosts }
						onChange={ (value) => { setAttributes({ excludePrevPosts: value }); } }
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
						label="Exclude Specific Webinars from Feed"
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
						placeholder="Search webinars..."
					/> }

					{ !! manuallySelectPosts && <FormTokenField 
						label="Select which webinars to display (add in desired order)"
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
						placeholder="Search webinars..."
					/> }

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<ServerSideRender block="crown-blocks/featured-webinar-slider" attributes={ attributes } />

			</div>

		];
	})


} );
