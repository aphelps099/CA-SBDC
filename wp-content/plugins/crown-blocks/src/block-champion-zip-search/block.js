
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


registerBlockType('crown-blocks/champion-zip-search', {
	title: 'Champion Zip Search',
	description: 'Display a search tool for finding the nearest champion.',
	icon: 'businessman',
	category: 'widgets',
	keywords: [ 'search', 'locations', 'crown-blocks' ],

	supports: {},

	attributes: {},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {} = attributes;

		return [

			<div class="crown-block-editor-container">

				<ServerSideRender block="crown-blocks/champion-zip-search" attributes={ attributes } />

			</div>

		];
	}


} );
