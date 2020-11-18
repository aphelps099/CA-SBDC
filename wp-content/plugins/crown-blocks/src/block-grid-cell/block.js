
// import helper functions
import CrownBlocks from '../common.js';

// import CSS
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InnerBlocks, RichText, Editable, MediaUpload, BlockControls, AlignmentToolbar, InspectorControls, PanelColorSettings, URLInput } = wp.blockEditor;
const { PanelBody, RadioControl, ColorPicker, Button, ButtonGroup, Icon, RangeControl, FocalPointPicker, ToggleControl, TextControl, TextareaControl, SelectControl } = wp.components;
const { PlainText } = wp.editor;


registerBlockType('crown-blocks/grid-cell', {
	title: 'Grid Cell',
	icon: 'screenoptions',
	category: 'layout',
	keywords: [ 'columns', 'cards', 'crown-blocks' ],
	parent: [ 'crown-blocks/grid' ],

	supports: {
		// inserter: false
	},

	attributes: {

	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const TEMPLATE = [
			[ 'core/paragraph', { placeholder: 'Enter cell content...' } ]
		];

		const {

		} = attributes;

		let blockClasses = [
			className
		];

		return [

			<div class="crown-block-editor-container">

				<div className={ blockClasses.join(' ') } key="grid-cell">
					<div className="inner">

						<div className="grid-cell-contents">
							<div className="inner">

								<InnerBlocks template={ TEMPLATE } templateLock={ false } />

							</div>
						</div>

					</div>
				</div>

			</div>

		];
	},

	
	save: ({ attributes, className }) => {
		
		const {

		} = attributes;

		let blockClasses = [
			className
		];

		return (

			<div className={ blockClasses.join(' ') } key="grid-cell">
				<div className="inner">

					<div className="grid-cell-contents">
						<div className="inner">

							<InnerBlocks.Content />

						</div>
					</div>

				</div>
			</div>

		);
	}


} );
