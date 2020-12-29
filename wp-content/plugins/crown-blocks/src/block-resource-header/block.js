
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


registerBlockType('crown-blocks/resource-header', {
	title: 'Resource Header',
	description: 'Displays at the top of the individual resource template.',
	icon: 'text-page',
	category: 'layout',
	keywords: [ 'crown-blocks' ],

	supports: {},

	attributes: {},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {} = attributes;

		return [

			<div class="crown-block-editor-container">

				<ServerSideRender block="crown-blocks/resource-header" attributes={ attributes } />

			</div>

		];
	}


} );
