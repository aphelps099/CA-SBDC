
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


registerBlockType('crown-blocks/callout', {
	title: 'Callout',
	icon: 'star-filled',
	category: 'widgets',
	keywords: [ 'icon', 'stat', 'crown-blocks' ],

	supports: {},

	attributes: {
		configuration: { type: 'string', default: 'icon' },
		iconImageId: { type: 'number' },
		iconImageData: { type: 'object' },
		stat: { type: 'string', default: '' },
		overrideStatMinWidth: { type: 'boolean', default: false },
		statMinWidth: { type: 'number', default: 8 }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		let disallowedBlocks = [];
		const ALLOWED_BLOCKS = wp.blocks.getBlockTypes().map(block => block.name).filter(blockName => !disallowedBlocks.includes(blockName));

		const {
			configuration,
			iconImageId,
			iconImageData,
			stat,
			overrideStatMinWidth,
			statMinWidth
		} = attributes;

		let blockClasses = [
			className,
			'configuration-' + configuration
		];

		let iconImageUrl = null;
		if(configuration == 'icon' && iconImageId) {
			iconImageUrl = iconImageData.sizes.medium_large ? iconImageData.sizes.medium_large.url : iconImageData.url;
			blockClasses.push('has-icon');
		}

		if(configuration == 'stat' && overrideStatMinWidth) {
			blockClasses.push('stat-min-width-' + statMinWidth);
		}

		return [

			<InspectorControls key="inspector-controls">

				<PanelBody title={ 'Appearance' } initialOpen={ true }>

					<ButtonGroup style={ { marginBottom: '16px' } }>
						<Button isPrimary={ configuration == 'icon' } isSecondary={ configuration != 'icon' } onClick={ (e) => setAttributes({ configuration: 'icon' }) }>Icon</Button>
						<Button isPrimary={ configuration == 'stat' } isSecondary={ configuration != 'stat' } onClick={ (e) => setAttributes({ configuration: 'stat' }) }>Stat</Button>
					</ButtonGroup>

					{ !! (configuration == 'icon') && <MediaUpload
						onSelect={ (media) => { setAttributes({ iconImageId: media.id, iconImageData: media }); } }
						type="image"
						value={ iconImageId }
						render={ ({ open }) => (
							<div className={ 'crown-blocks-media-upload' }>
								{ iconImageId && <Button className={ 'image-preview' } onClick={ open }><img src={ iconImageData.sizes.medium ? iconImageData.sizes.medium.url : iconImageData.sizes.thumbnail.url } /></Button> }
								<Button className={ 'button' } onClick={ open }>Select Icon</Button>
								{ iconImageId && <Button className={ 'button is-link is-destructive' } onClick={ (e) => { setAttributes({ iconImageId: null, iconImageData: null }); } }>Remove Icon</Button> }
							</div>
						) }
					/> }

					{ !! (configuration == 'stat') && <TextControl
						label="Stat"
						value={ stat }
						onChange={ (value) => setAttributes({ stat: value }) }
					/> }

					{ !! (configuration == 'stat') && <ToggleControl
						label={ 'Override minimum width' }
						checked={ overrideStatMinWidth }
						onChange={ (value) => { setAttributes({ overrideStatMinWidth: value }); } }
					/> }

					{ !! (configuration == 'stat' && overrideStatMinWidth) && <RangeControl
						label="Minimum Width"
						value={ statMinWidth }
						onChange={ (value) => setAttributes({ statMinWidth: value }) }
						min={ 0 }
						max={ 20 }
					/> }

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<div className={ blockClasses.join(' ') } key="callout">
					<div className="inner">

						{ (configuration == 'icon' && iconImageUrl) && <div className="callout-icon">
							<div class="inner">
								<img src={ iconImageUrl } />
							</div>
						</div> }

						{ (configuration == 'icon' && !iconImageUrl) && <div className="callout-icon">
							<div class="inner">
								<div class="icon-placeholder"></div>
							</div>
						</div> }

						{ (configuration == 'stat') && <div className="callout-stat">
							<div class="inner">
								<span class="stat">{ stat }</span>
							</div>
						</div> }

						<div className="callout-contents">
							<div className="inner">
								<InnerBlocks allowedBlocks={ ALLOWED_BLOCKS } />
							</div>
						</div>

					</div>
				</div>

			</div>

		];
	},

	
	save: ({ attributes, className }) => {
		
		const {
			configuration,
			iconImageId,
			iconImageData,
			stat,
			overrideStatMinWidth,
			statMinWidth
		} = attributes;

		let blockClasses = [
			className,
			'configuration-' + configuration
		];

		let iconImageUrl = null;
		if(configuration == 'icon' && iconImageId) {
			iconImageUrl = iconImageData.sizes.medium_large ? iconImageData.sizes.medium_large.url : iconImageData.url;
			blockClasses.push('has-icon');
		}

		if(configuration == 'stat' && overrideStatMinWidth) {
			blockClasses.push('stat-min-width-' + statMinWidth);
		}

		return (

			<div className={ blockClasses.join(' ') } key="callout">
				<div className="inner">

					{ (configuration == 'icon' && iconImageUrl) && <div className="callout-icon">
						<div class="inner">
							<img src={ iconImageUrl } />
						</div>
					</div> }

					{ (configuration == 'stat') && <div className="callout-stat">
						<div class="inner">
							<span class="stat">{ stat }</span>
						</div>
					</div> }

					<div className="callout-contents">
						<div className="inner">
							<InnerBlocks.Content />
						</div>
					</div>

				</div>
			</div>

		);
	},


} );
