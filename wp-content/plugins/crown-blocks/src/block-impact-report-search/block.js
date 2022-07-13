
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


registerBlockType('crown-blocks/impact-report-search', {
	title: 'Impact Report Search',
	description: 'Displays search form for impact reports.',
	icon: 'media-document',
	category: 'widgets',
	keywords: [ 'feed', 'crown-blocks' ],

	supports: {},

	attributes: {
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
		} = attributes;

		let blockClasses = [ className ];

		return [

			<div class="crown-block-editor-container">

				<ServerSideRender block="crown-blocks/impact-report-search" attributes={ attributes } />

			</div>

		];
	}


} );
