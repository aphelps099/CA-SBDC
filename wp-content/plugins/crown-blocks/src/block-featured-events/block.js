
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


registerBlockType('crown-blocks/featured-events', {
	title: 'Featured Events',
	description: 'Display a set of events.',
	icon: 'calendar',
	category: 'widgets',
	keywords: [ 'feed', 'upcoming', 'crown-blocks' ],

	supports: {},

	attributes: {
		configuration:  { type: 'string', default: 'slider' },
		maxPostCount:  { type: 'string', default: '3' },
		manuallySelectPosts: { type: 'boolean', default: false },
		filterCenters: { type: 'array', default: [] },
		filterTopics: { type: 'array', default: [] },
		filterSeries: { type: 'array', default: [] },
		filterPostsExclude: { type: 'array', default: [] },
		filterPostsInclude: { type: 'array', default: [] }
	},


	edit: withSelect((select) => {
		return {
			events: select('core').getEntityRecords('postType', 'event', { per_page: -1 }),
			events_s: select('core').getEntityRecords('postType', 'event_s', { per_page: -1 }),
			centers: select('core').getEntityRecords('taxonomy', 'post_center', { per_page: -1 }),
			topics: select('core').getEntityRecords('taxonomy', 'post_topic', { per_page: -1 }),
			series: select('core').getEntityRecords('taxonomy', 'event_series', { per_page: -1 }),
		};
    })(({ events, events_s, centers, topics, series, attributes, className, isSelected, setAttributes }) => {
		if(!events || !events_s || !centers|| !topics || !series) return '';

		const {
			configuration,
			maxPostCount,
			manuallySelectPosts,
			filterCenters,
			filterTopics,
			filterSeries,
			filterPostsExclude,
			filterPostsInclude
		} = attributes;

		let availableCenters = {};
		let centerSuggestions = [];
		for(let i in centers) {
			let token = centers[i].name + ' (ID: ' + centers[i].id + ')'
			availableCenters[token] = centers[i];
			centerSuggestions.push(token);
		}

		let availableTopics = {};
		let topicSuggestions = [];
		for(let i in topics) {
			let token = topics[i].name + ' (ID: ' + topics[i].id + ')'
			availableTopics[token] = topics[i];
			topicSuggestions.push(token);
		}

		let availableSeries = {};
		let seriesSuggestions = [];
		for(let i in series) {
			let token = series[i].name + ' (ID: ' + series[i].id + ')'
			availableSeries[token] = series[i];
			seriesSuggestions.push(token);
		}

		let availablePostsExclude = {};
		let postsExcludeSuggestions = [];
		let availablePostsInclude = {};
		let postsIncludeSuggestions = [];
		for(let i in events) {
			let token = events[i].title.rendered + ' (ID: ' + events[i].id + ')'
			availablePostsExclude[token] = events[i];
			availablePostsInclude[token] = events[i];
			postsExcludeSuggestions.push(token);
			postsIncludeSuggestions.push(token);
		}
		for(let i in events_s) {
			let token = events_s[i].title.rendered + ' (ID: ' + events_s[i].id + ')'
			availablePostsExclude[token] = events_s[i];
			availablePostsInclude[token] = events_s[i];
			postsExcludeSuggestions.push(token);
			postsIncludeSuggestions.push(token);
		}

		let blockClasses = [ className ];

		return [

			<InspectorControls key="inspector-controls">

				<PanelBody title={ 'Appearance' } initialOpen={ true }>

					<SelectControl
						label="Configuration"
						value={ configuration }
						onChange={ (value) => setAttributes({ configuration: value }) }
						options={ [
							{ label: 'Slider', value: 'slider' },
							{ label: 'List', value: 'list' }
						] }
					/>

					<SelectControl
						label="Max number of events to display"
						value={ maxPostCount }
						onChange={ (value) => setAttributes({ maxPostCount: value }) }
						options={ [
							{ label: '1', value: '1' },
							{ label: '2', value: '2' },
							{ label: '3', value: '3' },
							{ label: '4', value: '4' },
							{ label: '5', value: '5' },
							{ label: '6', value: '6' }
						] }
					/>

				</PanelBody>

				<PanelBody title={ 'Filtering Options' } initialOpen={ true }>

					<ToggleControl
						label={ 'Manually select events' }
						checked={ manuallySelectPosts }
						onChange={ (value) => { setAttributes({ manuallySelectPosts: value }); } }
					/>

					{ !! !manuallySelectPosts && <FormTokenField 
						label="Filter by Center"
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
						label="Filter by Series"
						value={ filterSeries } 
						suggestions={ seriesSuggestions } 
						onChange={ (tokens) => {
							let matchedTokens = [];
							for(let i in tokens) {
								let token = typeof tokens[i] === 'string' ? tokens[i] : (tokens[i].value ? tokens[i].value : '');
								if(seriesSuggestions.includes(token)) {
									matchedTokens.push(token);
								}
							}
							let filterSeries = [];
							for(let i in matchedTokens) {
								if(availableSeries[matchedTokens[i]]) {
									filterSeries.push({ value: matchedTokens[i], id: availableSeries[matchedTokens[i]].id });
								}
							}
							setAttributes({ filterSeries: filterSeries })
						} }
						placeholder="Search series..."
					/> }

					{ !! !manuallySelectPosts && <FormTokenField 
						label="Exclude Specific Events from Feed"
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
						placeholder="Search events..."
					/> }

					{ !! manuallySelectPosts && <FormTokenField 
						label="Select which events to display (add in desired order)"
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
						placeholder="Search events..."
					/> }

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<ServerSideRender block="crown-blocks/featured-events" attributes={ attributes } />

			</div>

		];
	})


} );
