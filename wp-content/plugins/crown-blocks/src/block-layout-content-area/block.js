
// import helper functions
import CrownBlocks from '../common.js';

// import CSS
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InnerBlocks, RichText, Editable, MediaUpload, BlockControls, AlignmentToolbar, InspectorControls, PanelColorSettings } = wp.blockEditor;
const { PanelBody, RadioControl, ColorPicker, Button, ButtonGroup, RangeControl, FocalPointPicker, ToggleControl, SelectControl } = wp.components;


registerBlockType('crown-blocks/layout-content-area', {
	title: 'Content Area',
	icon: 'format-aside',
	category: 'layout',
	keywords: [ 'crown-blocks' ],

	supports: {},

	attributes: {
		contentWidthConstraint: {
			type: 'string',
			default: 'lg'
		},
		textAlign: {
			type: 'string',
			default: 'left'
		}
	},


	edit: ({ attributes, className, isSelected, setAttributes, focus }) => {

		let disallowedBlocks = [
			'crown-blocks/page-section-simple'
		];
		const ALLOWED_BLOCKS = wp.blocks.getBlockTypes().map(block => block.name).filter(blockName => !disallowedBlocks.includes(blockName));

		const {
			contentWidthConstraint,
			textAlign
		} = attributes;

		let blockClasses = [
			className,
			'content-area',
			'width-constraint-' + contentWidthConstraint,
			'text-align-' + textAlign
		];

		return [

			<InspectorControls key="inspector-controls">

				<PanelBody title={ 'Spacing' } initialOpen={ false }>

					<div className={ 'components-base-control' }>
						<div className={ 'components-base-control__field' }>

							<label className={ 'components-base-control__label' }>Content Width Constraint</label>
							<ButtonGroup>
								<Button isSmall={ true } isPrimary={ contentWidthConstraint == 'full' } onClick={ (e) => setAttributes({ contentWidthConstraint: 'full' }) }>Full</Button>
								<Button isSmall={ true } isPrimary={ contentWidthConstraint == 'lg' } onClick={ (e) => setAttributes({ contentWidthConstraint: 'lg' }) }>Large</Button>
								<Button isSmall={ true } isPrimary={ contentWidthConstraint == 'md' } onClick={ (e) => setAttributes({ contentWidthConstraint: 'md' }) }>Medium</Button>
								<Button isSmall={ true } isPrimary={ contentWidthConstraint == 'sm' } onClick={ (e) => setAttributes({ contentWidthConstraint: 'sm' }) }>Small</Button>
								<Button isSmall={ true } isPrimary={ contentWidthConstraint == 'xs' } onClick={ (e) => setAttributes({ contentWidthConstraint: 'xs' }) }>X-Small</Button>
							</ButtonGroup>

						</div>
					</div>

				</PanelBody>

			</InspectorControls>,

			<div className={ blockClasses.join(' ') }>
				{ !! focus && (
					<BlockControls>
						<AlignmentToolbar
							value={ textAlign }
							onChange={ (value) => { setAttributes({ textAlign: value }); } }
						/>
					</BlockControls>
				) }
				<div className="inner">
					<InnerBlocks allowedBlocks={ ALLOWED_BLOCKS } templateLock={ false } />
				</div>
			</div>

		];
	},

	
	save: ({ attributes, className }) => {
		
		const {
			contentWidthConstraint,
			textAlign
		} = attributes;

		let blockClasses = [
			className,
			'content-area',
			'width-constraint-' + contentWidthConstraint,
			'text-align-' + textAlign
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
