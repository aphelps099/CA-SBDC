
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


registerBlockType('crown-blocks/featured-client-story', {
	title: 'Featured Client Story',
	description: 'Display the most recent client story marked as "featured".',
	icon: 'testimonial',
	category: 'widgets',
	keywords: [ 'crown-blocks' ],

	supports: {},

	attributes: {
		label:  { type: 'string', default: 'Featured Client' },
		backgroundColor: { type: 'string', default: '#108DBC' },
		borderColor: { type: 'string', default: '#D44457' },
		textColor: { type: 'string', default: 'auto' }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			label,
			backgroundColor,
			borderColor,
			textColor
		} = attributes;

		return [

			<InspectorControls key="inspector-controls">

				<PanelColorSettings
					title={ 'Colors' }
					initialOpen={ true }
					colorSettings={ [
						{
							label: 'Background Color',
							value: backgroundColor,
							onChange: (value) => setAttributes({ backgroundColor: value })
						},
						{
							label: 'Border Color',
							value: borderColor,
							onChange: (value) => setAttributes({ borderColor: value })
						}
					] }
				/>

				<PanelBody title={ 'Content' } initialOpen={ true }>

					<TextControl
						label="Label"
						value={ label }
						onChange={ (value) => setAttributes({ label: value }) }
					/>

				</PanelBody>

				<PanelBody title={ 'Text Color' } initialOpen={ true }>

					<ButtonGroup>
						<Button isPrimary={ textColor == 'auto' } isSecondary={ textColor != 'auto' } onClick={ (e) => setAttributes({ textColor: 'auto' }) }>Auto</Button>
						<Button isPrimary={ textColor == 'dark' } isSecondary={ textColor != 'dark' } onClick={ (e) => setAttributes({ textColor: 'dark' }) }>Dark</Button>
						<Button isPrimary={ textColor == 'light' } isSecondary={ textColor != 'light' } onClick={ (e) => setAttributes({ textColor: 'light' }) }>Light</Button>
					</ButtonGroup>

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<ServerSideRender block="crown-blocks/featured-client-story" attributes={ attributes } />

			</div>

		];
	}


} );
