
// import helper functions
import CrownBlocks from '../common.js';

// import CSS
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InnerBlocks, RichText, Editable, MediaUpload, BlockControls, AlignmentToolbar, InspectorControls, PanelColorSettings, URLInputButton, URLInput } = wp.blockEditor;
const { PanelBody, Popover, RadioControl, ColorPicker, ColorPalette, ToolbarButton, ToolbarGroup, Button, ButtonGroup, Icon, RangeControl, FocalPointPicker, ToggleControl, TextControl, SelectControl } = wp.components;
const { getColorObjectByColorValue } = wp.blockEditor;

const ALLOWED_BLOCKS = [ 'crown-blocks/button' ];


registerBlockType('crown-blocks/button-group', {
	title: 'Button Group',
	icon: <svg width="24px" height="16px" viewBox="0 0 24 16" version="1.1" xmlns="http://www.w3.org/2000/svg"><g fill-rule="evenodd"><g transform="translate(0.000000, -4.000000)"><path d="M21,4 C22.6568542,4 24,5.4326888 24,7.2 L24,16.8 C24,18.5673112 22.6568542,20 21,20 L3,20 C1.34314575,20 0,18.5673112 0,16.8 L0,7.2 C0,5.4326888 1.34314575,4 3,4 L21,4 Z M21,6 L3,6 C2.48716416,6 2.06449284,6.38604019 2.00672773,6.88337887 L2,7 L2,14 C2,14.5128358 2.38604019,14.9355072 2.88337887,14.9932723 L3,15 L21,15 C21.5128358,15 21.9355072,14.6139598 21.9932723,14.1166211 L22,14 L22,7 C22,6.48716416 21.6139598,6.06449284 21.1166211,6.00672773 L21,6 Z" id="button"></path></g></g></svg>,
	category: 'layout',
	keywords: [ 'btn', 'link', 'crown-blocks' ],

	supports: {},

	attributes: {
		horizontalAlignment: { type: 'string', default: 'none' }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const TEMPLATE = [
			[ 'crown-blocks/button', {} ]
		];

		const {
			horizontalAlignment
		} = attributes;

		let blockClasses = [ className ];
		if(typeof horizontalAlignment != 'undefined') blockClasses.push('button-horizontal-alignment-' + horizontalAlignment);

		return [

			<div class="crown-block-editor-container">

				<BlockControls>
					<AlignmentToolbar
						value={ horizontalAlignment }
						onChange={ (value) => { setAttributes({ horizontalAlignment: value }); } }
					/>
				</BlockControls>

				<div className={ blockClasses.join(' ') } key="button-group">
					<div className="inner">

						<InnerBlocks allowedBlocks={ ALLOWED_BLOCKS } template={ TEMPLATE } orientation="horizontal" />

					</div>
				</div>

			</div>

		];
	},

	
	save: ({ attributes, className }) => {
		
		const {
			horizontalAlignment
		} = attributes;

		let blockClasses = [ className ];
		if(typeof horizontalAlignment != 'undefined') blockClasses.push('button-horizontal-alignment-' + horizontalAlignment);

		return (

			<div className={ blockClasses.join(' ') } key="button-group">
				<div className="inner">

					<InnerBlocks.Content />

				</div>
			</div>

		);
	},


} );
