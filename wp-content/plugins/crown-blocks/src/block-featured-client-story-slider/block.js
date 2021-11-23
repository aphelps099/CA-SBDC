
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


registerBlockType('crown-blocks/featured-client-story-slider', {
	title: 'Featured Client Story Slider',
	description: 'Display a set of client stories published.',
	icon: 'testimonial',
	category: 'widgets',
	keywords: [ 'feed', 'recent', 'crown-blocks' ],

	supports: {},

	attributes: {
		maxPostCount:  { type: 'string', default: '3' },
		manuallySelectPosts: { type: 'boolean', default: false },
		excludePrevPosts: { type: 'boolean', default: false },
		filterIndustries: { type: 'array', default: [] },
		filterPostsExclude: { type: 'array', default: [] },
		filterPostsInclude: { type: 'array', default: [] }
	},


	edit: withSelect((select) => {
		return {
			posts: select('core').getEntityRecords('postType', 'client_story', { per_page: -1 }),
			industries: select('core').getEntityRecords('taxonomy', 'client_story_industry', { per_page: -1 })
		};
    })(({ posts, industries, attributes, className, isSelected, setAttributes }) => {
		if(!posts || !industries) return '';

		const {
			maxPostCount,
			manuallySelectPosts,
			excludePrevPosts,
			filterIndustries,
			filterPostsExclude,
			filterPostsInclude
		} = attributes;

		let availableIndustries = {};
		let industrySuggestions = [];
		for(let i in industries) {
			let token = industries[i].name + ' (ID: ' + industries[i].id + ')'
			availableIndustries[token] = industries[i];
			industrySuggestions.push(token);
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
						label="Max number of stories to display"
						value={ maxPostCount }
						onChange={ (value) => setAttributes({ maxPostCount: value }) }
						options={ [
							{ label: '1', value: '1' },
							{ label: '2', value: '2' },
							{ label: '3', value: '3' },
							{ label: '4', value: '4' },
							{ label: '5', value: '5' },
							{ label: '6', value: '6' },
							{ label: '7', value: '7' },
							{ label: '8', value: '8' },
							{ label: '9', value: '9' },
							{ label: '10', value: '10' }
						] }
					/>

				</PanelBody>

				<PanelBody title={ 'Filtering Options' } initialOpen={ true }>

					<ToggleControl
						label={ 'Manually select stories' }
						checked={ manuallySelectPosts }
						onChange={ (value) => { setAttributes({ manuallySelectPosts: value }); } }
					/>

					{ !! !manuallySelectPosts && <ToggleControl
						label={ 'Exclude stories featured in other recent story feeds above this on the page (note: does not affect output in editor)' }
						checked={ excludePrevPosts }
						onChange={ (value) => { setAttributes({ excludePrevPosts: value }); } }
					/> }

					{ !! !manuallySelectPosts && <FormTokenField 
						label="Filter by Industry"
						value={ filterIndustries } 
						suggestions={ industrySuggestions } 
						onChange={ (tokens) => {
							let matchedTokens = [];
							for(let i in tokens) {
								let token = typeof tokens[i] === 'string' ? tokens[i] : (tokens[i].value ? tokens[i].value : '');
								if(industrySuggestions.includes(token)) {
									matchedTokens.push(token);
								}
							}
							let filterIndustries = [];
							for(let i in matchedTokens) {
								if(availableIndustries[matchedTokens[i]]) {
									filterIndustries.push({ value: matchedTokens[i], id: availableIndustries[matchedTokens[i]].id });
								}
							}
							setAttributes({ filterIndustries: filterIndustries })
						} }
						placeholder="Search industries..."
					/> }

					{ !! !manuallySelectPosts && <FormTokenField 
						label="Exclude Specific Stories from Feed"
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
						placeholder="Search stories..."
					/> }

					{ !! manuallySelectPosts && <FormTokenField 
						label="Select which stories to display (add in desired order)"
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
						placeholder="Search stories..."
					/> }

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<ServerSideRender block="crown-blocks/featured-client-story-slider" attributes={ attributes } />

			</div>

		];
	})


} );
