
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


registerBlockType('crown-blocks/event-registration-form', {
	title: 'Event Registration Form',
	description: 'Embed a Zoom meeting registration form.',
	icon: 'calendar',
	category: 'widgets',
	keywords: [ 'zoom', 'form', 'crown-blocks' ],

	supports: {},

	attributes: {
		meetingId: { type: 'string', default: '' }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			meetingId
		} = attributes;

		return [

			<InspectorControls key="inspector-controls">

				<PanelBody title={ 'Zoom Configuration' } initialOpen={ true }>

					<TextControl
						label="Meeting ID"
						value={ meetingId }
						onChange={ (value) => setAttributes({ meetingId: value }) }
					/>

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<ServerSideRender block="crown-blocks/event-registration-form" attributes={ attributes } />

			</div>

		];
	}


} );
