
// import helper functions
import CrownBlocks from '../common.js';

// import CSS
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InnerBlocks, RichText, Editable, MediaUpload, BlockControls, AlignmentToolbar, InspectorControls, PanelColorSettings, URLInput } = wp.blockEditor;
const { PanelBody, RadioControl, ColorPicker, Button, ButtonGroup, RangeControl, FocalPointPicker, ToggleControl, TextControl, TextareaControl, SelectControl } = wp.components;
const { PlainText } = wp.editor;

const ALLOWED_BLOCKS = [ 'crown-blocks/stat' ];

const TEMPLATE = [
	[ 'crown-blocks/stat', {}, [] ]
];


registerBlockType('crown-blocks/stat-table', {
	title: 'Statistic Table',
	icon: 'chart-line',
	category: 'widgets',
	keywords: [ 'data', 'number', 'crown-blocks' ],

	supports: {},

	attributes: {},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {} = attributes;

		let blockClasses = [
			className
		];

		return [

			<div class="crown-block-editor-container">

				<div className={ blockClasses.join(' ') } key="stat-table">
					<div className="inner">

						<InnerBlocks allowedBlocks={ ALLOWED_BLOCKS } template={ TEMPLATE } />

					</div>
				</div>

			</div>

		];
	},

	
	save: ({ attributes, className }) => {
		
		const {} = attributes;

		let blockClasses = [
			className
		];

		return (

			<div className={ blockClasses.join(' ') }>
				<div className="inner">

					<InnerBlocks.Content />

				</div>
			</div>

		);
	},


} );
