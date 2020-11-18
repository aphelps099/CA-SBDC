
// import helper functions
import CrownBlocks from '../common.js';

// import CSS
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InnerBlocks, RichText, Editable, MediaUpload, BlockControls, AlignmentToolbar, InspectorControls, PanelColorSettings } = wp.blockEditor;
const { PanelBody, RadioControl, ColorPicker, Button, ButtonGroup, RangeControl, FocalPointPicker, ToggleControl, SelectControl } = wp.components;


const ALLOWED_BLOCKS = [ 'crown-blocks/layout-column' ];

const TEMPLATE = [
	[ 'crown-blocks/layout-column', {}, [] ],
	[ 'crown-blocks/layout-column', {}, [] ]
];


registerBlockType('crown-blocks/layout-two-column', {
	title: 'Two-Column Layout',
	icon: 'columns',
	category: 'layout',
	keywords: [ 'crown-blocks' ],

	supports: {},

	attributes: {
		columnLeftWidth: {
			type: 'number',
			default: 6
		},
		columnRightWidth: {
			type: 'number',
			default: 6
		},
		stackingBreakpoint: {
			type: 'string',
			default: 'phone'
		}
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			columnLeftWidth,
			columnRightWidth,
			stackingBreakpoint
		} = attributes;

		let blockClasses = [ className, 'layout-multi-column', 'stacking-breakpoint-' + stackingBreakpoint];
		blockClasses.push('column-distribution-' + columnLeftWidth + '-' + (12 - columnLeftWidth - columnRightWidth) + '-' + columnRightWidth);

		return [

			<InspectorControls key="inspector-controls">

				<PanelBody title={ 'Column Layout' } initialOpen={ true }>

					<RangeControl
						label={ 'Left Column Width' }
						value={ columnLeftWidth }
						onChange={ (value) => { setAttributes({ columnLeftWidth: value, columnRightWidth: Math.min(columnRightWidth, 12 - value) }); } }
						min={ 1 }
						max={ 11 }
						step={ 1 }
					/>

					<RangeControl
						label={ 'Right Column Width' }
						value={ columnRightWidth }
						onChange={ (value) => { setAttributes({ columnRightWidth: value, columnLeftWidth: Math.min(columnLeftWidth, 12 - value) }); } }
						min={ 1 }
						max={ 11 }
						step={ 1 }
					/>

					<SelectControl
						label="Stacking Breakpoint"
						help="Select the maximum device size where the columns should stack."
						value={ stackingBreakpoint }
						onChange={ (value) => setAttributes({ stackingBreakpoint: value }) }
						options={ [
							{ label: 'None', value: 'none' },
							{ label: 'Phone (768px)', value: 'phone' },
							{ label: 'Tablet (992px)', value: 'tablet' },
							{ label: 'Desktop (1200px)', value: 'desktop' }
						] }
					/>

				</PanelBody>

			</InspectorControls>,

			<div className={ blockClasses.join(' ') }>
				<div className="inner">
					<InnerBlocks allowedBlocks={ ALLOWED_BLOCKS } template={ TEMPLATE } templateLock={ 'insert' } />
				</div>
			</div>

		];
	},

	
	save: ({ attributes, className }) => {
		
		const {
			columnLeftWidth,
			columnRightWidth,
			stackingBreakpoint
		} = attributes;

		let blockClasses = [ className, 'layout-multi-column', 'stacking-breakpoint-' + stackingBreakpoint];
		blockClasses.push('column-distribution-' + columnLeftWidth + '-' + (12 - columnLeftWidth - columnRightWidth) + '-' + columnRightWidth);

		return (

			<div className={ blockClasses.join(' ') }>
				<div className="inner">
					<InnerBlocks.Content />
				</div>
			</div>

		);
	},


} );
