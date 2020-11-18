
// import helper functions
import CrownBlocks from '../common.js';

// import CSS
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InnerBlocks, RichText, Editable, MediaUpload, BlockControls, AlignmentToolbar, InspectorControls, PanelColorSettings, URLInput } = wp.blockEditor;
const { PanelBody, RadioControl, ColorPicker, Button, ButtonGroup, Icon, RangeControl, FocalPointPicker, ToggleControl, TextControl, SelectControl } = wp.components;
// import { SortableContainer, SortableElement, SortableHandle, arrayMove } from 'react-sortable-hoc';

let disallowedBlocks = [];
const ALLOWED_BLOCKS = wp.blocks.getBlockTypes().map(block => block.name).filter(blockName => !disallowedBlocks.includes(blockName));


registerBlockType('crown-blocks/container-featured-image', {
	title: 'Featured Image Container',
	description: 'Wrap content in a container with an image.',
	icon: <svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg"><g fill-rule="evenodd"><g><path d="M15,22 L15,24 L9,24 L9,22 L15,22 Z M2,16.999 L2,20 C2,21.1045695 2.8954305,22 4,22 L4,22 L7,22 L7,24 L3,24 C1.34314575,24 -1.27867033e-14,22.6568542 -1.29896094e-14,21 L1.22124533e-15,17 L2,16.999 Z M24,17 L24,21 C24,22.6568542 22.6568542,24 21,24 L17,24 L17,22 L20,22 C21.0543618,22 21.9181651,21.1841222 21.9945143,20.1492623 L22,20 L22,16.999 L24,17 Z M24,9 L24,15 L22,14.999 L22,8.999 L24,9 Z M2,8.999 L2,14.999 L1.22124533e-15,15 L1.22124533e-15,9 L2,8.999 Z M21,4.4408921e-15 C22.6568542,4.13653291e-15 24,1.34314575 24,3 L24,7 L22,6.999 L22,4 C22,2.9456382 21.1841222,2.08183488 20.1492623,2.00548574 L20,2 L17,2 L17,4.4408921e-15 L21,4.4408921e-15 Z M7,4.4408921e-15 L7,2 L4,2 C2.9456382,2 2.08183488,2.81587779 2.00548574,3.85073766 L2,4 L2,6.999 L1.22124533e-15,7 L-1.29896094e-14,3 C-1.31925155e-14,1.34314575 1.34314575,4.74525129e-15 3,4.4408921e-15 L7,4.4408921e-15 Z M15,2 L9,2 L9,4.4408921e-15 L15,4.4408921e-15 L15,2 Z" id="container"></path></g></g></svg>,
	category: 'layout',
	keywords: [ 'section', 'image', 'crown-blocks' ],

	supports: {
		align: [ 'wide', 'full' ],
		anchor: true
	},

	attributes: {
		align: { type: 'string', default: '' },
		backgroundColor: { type: 'string', default: '#002F87' },
		accentColor: { type: 'string', default: '#FFFFFF' },
		currentFeaturedImageIndex: { type: 'number', default: 0 },
		featuredImageIds: { type: 'array', default: [] },
		featuredImageData: { type: 'array', default: [] },
		featuredImageFocalPoint: { type: 'object', default: { x: 0.5, y: 0.5 } },
		featuredImageLeft: { type: 'boolean', default: false },
		featuredImageWaveOverlay: { type: 'boolean', default: false },
		featuredImageWidth: { type: 'number', default: 5 },
		textColor: { type: 'string', default: 'auto' },
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			align,
			backgroundColor,
			accentColor,
			currentFeaturedImageIndex,
			featuredImageIds,
			featuredImageData,
			featuredImageFocalPoint,
			featuredImageLeft,
			featuredImageWaveOverlay,
			featuredImageWidth,
			textColor
		} = attributes;

		let blockClasses = [
			className
		];

		if(textColor == 'auto' && backgroundColor) {
			blockClasses.push('text-color-' + (CrownBlocks.isDarkColor(backgroundColor) ? 'light' : 'dark'));
		} else if(textColor != 'auto') {
			blockClasses.push('text-color-' + textColor);
		}

		if(featuredImageWaveOverlay) blockClasses.push('has-wave-overlay');

		let blockStyle = {
			backgroundColor: attributes.backgroundColor,
		};

		let featuredImageUrl = null;
		if(featuredImageIds.length) {
			featuredImageUrl = featuredImageData[currentFeaturedImageIndex].sizes.large ? featuredImageData[currentFeaturedImageIndex].sizes.large.url : featuredImageData[currentFeaturedImageIndex].url;
			blockClasses.push('has-featured-image');
			if(featuredImageLeft) blockClasses.push('featured-image-left');
			blockClasses.push('featured-image-width-' + featuredImageWidth);
		}

		let colorSettings = [{
			label: 'Background Color',
			value: backgroundColor,
			onChange: (value) => setAttributes({ backgroundColor: value ? value : '' })
		}];
		if(featuredImageWaveOverlay && featuredImageIds.length) {
			colorSettings.push({
				label: 'Accent Color',
				value: accentColor,
				onChange: (value) => setAttributes({ accentColor: value ? value : '' })
			});
		}

		return [

			<InspectorControls key="inspector-controls">

				<PanelBody title={ 'Featured Image Area' } className={ 'crown-blocks-featured-image' } initialOpen={ true }>

					{ !! featuredImageIds.length && <ol class="crown-blocks-media-images">
						{ featuredImageData.map((media, index) =>
							<li key={ media.id } class={ index == currentFeaturedImageIndex ? 'active' : '' } onClick={ (e) => {
								let nodes = Array.prototype.slice.call(e.target.closest('li').parentNode.children);
								let index = nodes.indexOf(e.target.closest('li'));
								setAttributes({ currentFeaturedImageIndex: index, featuredImageFocalPoint: featuredImageData[index].focalPoint });
							} }>
								<img src={ media.sizes.thumbnail.url } />
							</li>
						) }
					</ol> }

					{ !! featuredImageIds.length && <FocalPointPicker 
						label="Focal Point"
						url={ featuredImageData[currentFeaturedImageIndex].sizes.medium ? featuredImageData[currentFeaturedImageIndex].sizes.medium.url : featuredImageData[currentFeaturedImageIndex].sizes.thumbnail.url }
						dimensions={ { width: 400, height: 100 } }
						value={ featuredImageFocalPoint }
						onChange={ (value) => {
							featuredImageData[currentFeaturedImageIndex].focalPoint = value;
							setAttributes({ featuredImageFocalPoint: value, featuredImageData: featuredImageData })
						} } 
					/> }

					<MediaUpload
						onSelect={ (media) => {
							let featuredImageIds = media.map((n) => { return n.id; });
							let featuredImageData = media.map((n) => { n.focalPoint = { x: 0.5, y: 0.5 }; return n; })
							setAttributes({ featuredImageIds: featuredImageIds, featuredImageData: featuredImageData, currentFeaturedImageIndex: 0 });
						} }
						type="image"
						multiple="true"
						value={ featuredImageIds }
						render={ ({ open }) => (
							<div className={ 'crown-blocks-media-upload' }>
								{/* { featuredImageId && <Button className={ 'image-preview' } onClick={ open }><img src={ featuredImageData.sizes.medium ? featuredImageData.sizes.medium.url : featuredImageData.sizes.thumbnail.url } /></Button> } */}
								<Button className={ 'components-button is-secondary' } onClick={ open }>Select Image(s)</Button>
								{ !! featuredImageIds.length && <Button className={ 'components-button is-destructive' } onClick={ (e) => {
									featuredImageIds.splice(currentFeaturedImageIndex, 1);
									featuredImageData.splice(currentFeaturedImageIndex, 1);
									let imageIndex = currentFeaturedImageIndex < featuredImageIds.length ? currentFeaturedImageIndex : Math.min(0, featuredImageIds.length - 1);
									setAttributes({ featuredImageIds: featuredImageIds, featuredImageData: featuredImageData, currentFeaturedImageIndex: imageIndex });
								} }>Remove Image</Button> }
							</div>
						) }
					/>

					{ !! featuredImageIds.length && <ToggleControl
						label={ 'Display image on left' }
						checked={ featuredImageLeft }
						onChange={ (value) => { setAttributes({ featuredImageLeft: value }); } }
					/> }

					{ !! featuredImageIds.length && <ToggleControl
						label={ 'Add wave overlay' }
						checked={ featuredImageWaveOverlay }
						onChange={ (value) => { setAttributes({ featuredImageWaveOverlay: value }); } }
					/> }

					<RangeControl
						label="Area Width"
						value={ featuredImageWidth }
						onChange={ (value) => setAttributes({ featuredImageWidth: value }) }
						min={ 2 }
						max={ 9 }
					/>

				</PanelBody>

				<PanelColorSettings
					title={ 'Colors' }
					initialOpen={ true }
					colorSettings={ colorSettings }
				/>

				<PanelBody title={ 'Text Color' } initialOpen={ false }>

					<ButtonGroup>
						<Button isPrimary={ textColor == 'auto' } isSecondary={ textColor != 'auto' } onClick={ (e) => setAttributes({ textColor: 'auto' }) }>Auto</Button>
						<Button isPrimary={ textColor == 'dark' } isSecondary={ textColor != 'dark' } onClick={ (e) => setAttributes({ textColor: 'dark' }) }>Dark</Button>
						<Button isPrimary={ textColor == 'light' } isSecondary={ textColor != 'light' } onClick={ (e) => setAttributes({ textColor: 'light' }) }>Light</Button>
					</ButtonGroup>

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<div className={ blockClasses.join(' ') } style={ blockStyle } key="container-featured-image">
					<div className="inner">

						{ featuredImageUrl && <div className="container-featured-image">
							<div class="inner">
								<div class="slider">
									<div className={ 'image' } style={ {
										backgroundImage: 'url(' + featuredImageUrl + ')',
										backgroundPosition: `${ featuredImageData[currentFeaturedImageIndex].focalPoint.x * 100 }% ${ featuredImageData[currentFeaturedImageIndex].focalPoint.y * 100 }%`,
									} }>
										<img src={ featuredImageUrl } />
									</div>
								</div>
							</div>
							{ (featuredImageWaveOverlay && featuredImageLeft) && <svg class="wave-right" width="68px" height="535px" viewBox="0 0 68 535" version="1.1" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
								<path class="accent" style={ { fill: accentColor } } d="M68,0 L23,0 C4.26756121,72.8303495 -2.86343785,137.629156 1.6070028,194.396419 C5.0155262,237.679076 23.0735701,310.315996 50.4625328,423.754476 C56.2102422,447.560102 59.7227312,484.641944 61,535 L68,535 L68,0 Z"></path>
								<path class="primary" style={ { fill: backgroundColor } } d="M68,0 L29,0 C10.2675612,72.8303495 3.13656215,137.629156 7.6070028,194.396419 C11.0155262,237.679076 29.0735701,310.315996 56.4625328,423.754476 C62.2102422,447.560102 65.7227312,484.641944 67,535 L68,535 L68,0 Z"></path>
							</svg> }
							{ (featuredImageWaveOverlay && !featuredImageLeft) && <svg class="wave-left" width="68px" height="535px" viewBox="0 0 68 535" version="1.1" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
								<path class="accent" style={ { fill: accentColor } } d="M0,0 L30,0 C11.2675612,72.8303495 4.13656215,137.629156 8.6070028,194.396419 C12.0155262,237.679076 30.0735701,310.315996 57.4625328,423.754476 C63.2102422,447.560102 66.7227312,484.641944 68,535 L0,535 L0,0 Z"></path>
								<path class="primary" style={ { fill: backgroundColor } } d="M0,0 L24,0 C5.26756121,72.8303495 -1.86343785,137.629156 2.6070028,194.396419 C6.0155262,237.679076 24.0735701,310.315996 51.4625328,423.754476 C57.2102422,447.560102 60.7227312,484.641944 62,535 L0,535 L0,0 Z"></path>
							</svg> }
						</div> }

						<div className="container-contents">
							<div className="inner">
								<InnerBlocks />
							</div>
						</div>

					</div>
				</div>

			</div>

		];
	},

	
	save: ({ attributes, className }) => {

		const {
			align,
			backgroundColor,
			accentColor,
			featuredImageIds,
			featuredImageData,
			featuredImageFocalPoint,
			featuredImageLeft,
			featuredImageWaveOverlay,
			featuredImageWidth,
			textColor
		} = attributes;

		let blockClasses = [
			className
		];

		if(textColor == 'auto' && backgroundColor) {
			blockClasses.push('text-color-' + (CrownBlocks.isDarkColor(backgroundColor) ? 'light' : 'dark'));
		} else if(textColor != 'auto') {
			blockClasses.push('text-color-' + textColor);
		}

		if(featuredImageWaveOverlay) blockClasses.push('has-wave-overlay');

		let blockStyle = {
			backgroundColor: attributes.backgroundColor,
		};

		if(featuredImageIds.length) {
			// featuredImageUrl = featuredImageData.sizes.medium_large ? featuredImageData.sizes.medium_large.url : featuredImageData.url;
			blockClasses.push('has-featured-image');
			if(featuredImageLeft) blockClasses.push('featured-image-left');
			blockClasses.push('featured-image-width-' + featuredImageWidth);
		}

		return (

			<div className={ blockClasses.join(' ') } style={ blockStyle } key="container-featured-image">
				<div className="inner">

					{ featuredImageIds.length && <div className="container-featured-image">
						<div class="inner">
							<div class="slider">
								{ featuredImageData.map((media, index) =>
									<div className={ 'image' } style={ {
										backgroundImage: 'url(' + (media.sizes.large ? media.sizes.large.url : media.url) + ')',
										backgroundPosition: `${ media.focalPoint.x * 100 }% ${ media.focalPoint.y * 100 }%`,
									} }>
										<img src={ media.sizes.large ? media.sizes.large.url : media.url } />
									</div>
								) }
							</div>
						</div>
						{ (featuredImageWaveOverlay && featuredImageLeft) && <svg class="wave-right" width="68px" height="535px" viewBox="0 0 68 535" version="1.1" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
							<path class="accent" style={ { fill: accentColor } } d="M68,0 L23,0 C4.26756121,72.8303495 -2.86343785,137.629156 1.6070028,194.396419 C5.0155262,237.679076 23.0735701,310.315996 50.4625328,423.754476 C56.2102422,447.560102 59.7227312,484.641944 61,535 L68,535 L68,0 Z"></path>
							<path class="primary" style={ { fill: backgroundColor } } d="M68,0 L29,0 C10.2675612,72.8303495 3.13656215,137.629156 7.6070028,194.396419 C11.0155262,237.679076 29.0735701,310.315996 56.4625328,423.754476 C62.2102422,447.560102 65.7227312,484.641944 67,535 L68,535 L68,0 Z"></path>
						</svg> }
						{ (featuredImageWaveOverlay && !featuredImageLeft) && <svg class="wave-left" width="68px" height="535px" viewBox="0 0 68 535" version="1.1" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
							<path class="accent" style={ { fill: accentColor } } d="M0,0 L30,0 C11.2675612,72.8303495 4.13656215,137.629156 8.6070028,194.396419 C12.0155262,237.679076 30.0735701,310.315996 57.4625328,423.754476 C63.2102422,447.560102 66.7227312,484.641944 68,535 L0,535 L0,0 Z"></path>
							<path class="primary" style={ { fill: backgroundColor } } d="M0,0 L24,0 C5.26756121,72.8303495 -1.86343785,137.629156 2.6070028,194.396419 C6.0155262,237.679076 24.0735701,310.315996 51.4625328,423.754476 C57.2102422,447.560102 60.7227312,484.641944 62,535 L0,535 L0,0 Z"></path>
						</svg> }
					</div> }

					<div className="container-contents">
						<div className="inner">
							<InnerBlocks.Content />
						</div>
					</div>

				</div>
			</div>

		);
	},


	deprecated: [

		{
			attributes: {
				align: { type: 'string', default: '' },
				backgroundColor: { type: 'string', default: '#002F87' },
				accentColor: { type: 'string', default: '#FFFFFF' },
				currentFeaturedImageIndex: { type: 'number', default: 0 },
				featuredImageIds: { type: 'array', default: [] },
				featuredImageData: { type: 'array', default: [] },
				featuredImageFocalPoint: { type: 'object', default: { x: 0.5, y: 0.5 } },
				featuredImageLeft: { type: 'boolean', default: false },
				featuredImageWidth: { type: 'number', default: 5 },
				textColor: { type: 'string', default: 'auto' },
			},

			save: ({ attributes, className }) => {

				const {
					align,
					backgroundColor,
					accentColor,
					featuredImageIds,
					featuredImageData,
					featuredImageFocalPoint,
					featuredImageLeft,
					featuredImageWidth,
					textColor
				} = attributes;
		
				let blockClasses = [
					className
				];
		
				if(textColor == 'auto' && backgroundColor) {
					blockClasses.push('text-color-' + (CrownBlocks.isDarkColor(backgroundColor) ? 'light' : 'dark'));
				} else if(textColor != 'auto') {
					blockClasses.push('text-color-' + textColor);
				}
		
				let blockStyle = {
					backgroundColor: attributes.backgroundColor,
				};
		
				if(featuredImageIds.length) {
					// featuredImageUrl = featuredImageData.sizes.medium_large ? featuredImageData.sizes.medium_large.url : featuredImageData.url;
					blockClasses.push('has-featured-image');
					if(featuredImageLeft) blockClasses.push('featured-image-left');
					blockClasses.push('featured-image-width-' + featuredImageWidth);
				}
		
				return (
		
					<div className={ blockClasses.join(' ') } style={ blockStyle } key="container-featured-image">
						<div className="inner">
		
							{ featuredImageIds.length && <div className="container-featured-image">
								<div class="inner">
									<div class="slider">
										{ featuredImageData.map((media, index) =>
											<div className={ 'image' } style={ {
												backgroundImage: 'url(' + (media.sizes.large ? media.sizes.large.url : media.url) + ')',
												backgroundPosition: `${ media.focalPoint.x * 100 }% ${ media.focalPoint.y * 100 }%`,
											} }>
												<img src={ media.sizes.large ? media.sizes.large.url : media.url } />
											</div>
										) }
									</div>
								</div>
								{ featuredImageLeft && <svg class="wave-right" width="67px" height="535px" viewBox="0 0 67 535" version="1.1" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
									<path class="accent" style={ { fill: accentColor } } d="M67,0 L23,0 C4.26756121,72.8303495 -2.86343785,137.629156 1.6070028,194.396419 C5.0155262,237.679076 23.0735701,310.315996 50.4625328,423.754476 C56.2102422,447.560102 59.7227312,484.641944 61,535 L67,535 L67,0 Z" id="Mask" fill="#000000"></path>
									<path class="primary" style={ { fill: backgroundColor } } d="M67,0 L29,0 C10.2675612,72.8303495 3.13656215,137.629156 7.6070028,194.396419 C11.0155262,237.679076 29.0735701,310.315996 56.4625328,423.754476 C62.2102422,447.560102 65.7227312,484.641944 67,535 L67,0 Z" id="Mask" fill="#000000"></path>
								</svg> }
								{ !featuredImageLeft && <svg class="wave-left" width="67px" height="535px" viewBox="0 0 67 535" version="1.1" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
									<path class="accent" style={ { fill: accentColor } } d="M0,0 L29,0 C10.2675612,72.8303495 3.13656215,137.629156 7.6070028,194.396419 C11.0155262,237.679076 29.0735701,310.315996 56.4625328,423.754476 C62.2102422,447.560102 65.7227312,484.641944 67,535 L0,535 L0,0 Z"></path>
									<path class="primary" style={ { fill: backgroundColor } } d="M0,0 L23,0 C4.26756121,72.8303495 -2.86343785,137.629156 1.6070028,194.396419 C5.0155262,237.679076 23.0735701,310.315996 50.4625328,423.754476 C56.2102422,447.560102 59.7227312,484.641944 61,535 L0,535 L0,0 Z"></path>
								</svg> }
							</div> }
		
							<div className="container-contents">
								<div className="inner">
									<InnerBlocks.Content />
								</div>
							</div>
		
						</div>
					</div>
		
				);
			}
		},

        {
            attributes: {
				align: { type: 'string', default: '' },
				backgroundColor: { type: 'string', default: '#002F87' },
				accentColor: { type: 'string', default: '#FFFFFF' },
				currentFeaturedImageIndex: { type: 'number', default: 0 },
				featuredImageIds: { type: 'array', default: [] },
				featuredImageData: { type: 'array', default: [] },
				featuredImageFocalPoint: { type: 'object', default: { x: 0.5, y: 0.5 } },
				featuredImageLeft: { type: 'boolean', default: false },
				featuredImageWidth: { type: 'number', default: 5 },
				textColor: { type: 'string', default: 'auto' },
			},
 
            save: ({ attributes, className }) => {

				const {
					align,
					backgroundColor,
					accentColor,
					featuredImageIds,
					featuredImageData,
					featuredImageFocalPoint,
					featuredImageLeft,
					featuredImageWidth,
					textColor
				} = attributes;
		
				let blockClasses = [
					className
				];
		
				if(textColor == 'auto' && backgroundColor) {
					blockClasses.push('text-color-' + (CrownBlocks.isDarkColor(backgroundColor) ? 'light' : 'dark'));
				} else if(textColor != 'auto') {
					blockClasses.push('text-color-' + textColor);
				}
		
				let blockStyle = {
					backgroundColor: attributes.backgroundColor,
				};
		
				if(featuredImageIds.length) {
					// featuredImageUrl = featuredImageData.sizes.medium_large ? featuredImageData.sizes.medium_large.url : featuredImageData.url;
					blockClasses.push('has-featured-image');
					if(featuredImageLeft) blockClasses.push('featured-image-left');
					blockClasses.push('featured-image-width-' + featuredImageWidth);
				}
		
				return (
		
					<div className={ blockClasses.join(' ') } style={ blockStyle } key="container-featured-image">
						<div className="inner">
		
							{ featuredImageIds.length && <div className="container-featured-image">
								<div class="inner">
									<div class="slider">
										{ featuredImageData.map((media, index) =>
											<div className={ 'image' } style={ {
												backgroundImage: 'url(' + (media.sizes.medium_large ? media.sizes.medium_large.url : media.url) + ')',
												backgroundPosition: `${ media.focalPoint.x * 100 }% ${ media.focalPoint.y * 100 }%`,
											} }>
												<img src={ media.sizes.medium_large ? media.sizes.medium_large.url : media.url } />
											</div>
										) }
									</div>
								</div>
								{ featuredImageLeft && <svg class="wave-right" width="67px" height="535px" viewBox="0 0 67 535" version="1.1" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
									<path class="accent" style={ { fill: accentColor } } d="M67,0 L23,0 C4.26756121,72.8303495 -2.86343785,137.629156 1.6070028,194.396419 C5.0155262,237.679076 23.0735701,310.315996 50.4625328,423.754476 C56.2102422,447.560102 59.7227312,484.641944 61,535 L67,535 L67,0 Z" id="Mask" fill="#000000"></path>
									<path class="primary" style={ { fill: backgroundColor } } d="M67,0 L29,0 C10.2675612,72.8303495 3.13656215,137.629156 7.6070028,194.396419 C11.0155262,237.679076 29.0735701,310.315996 56.4625328,423.754476 C62.2102422,447.560102 65.7227312,484.641944 67,535 L67,0 Z" id="Mask" fill="#000000"></path>
								</svg> }
								{ !featuredImageLeft && <svg class="wave-left" width="67px" height="535px" viewBox="0 0 67 535" version="1.1" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
									<path class="accent" style={ { fill: accentColor } } d="M0,0 L29,0 C10.2675612,72.8303495 3.13656215,137.629156 7.6070028,194.396419 C11.0155262,237.679076 29.0735701,310.315996 56.4625328,423.754476 C62.2102422,447.560102 65.7227312,484.641944 67,535 L0,535 L0,0 Z"></path>
									<path class="primary" style={ { fill: backgroundColor } } d="M0,0 L23,0 C4.26756121,72.8303495 -2.86343785,137.629156 1.6070028,194.396419 C5.0155262,237.679076 23.0735701,310.315996 50.4625328,423.754476 C56.2102422,447.560102 59.7227312,484.641944 61,535 L0,535 L0,0 Z"></path>
								</svg> }
							</div> }
		
							<div className="container-contents">
								<div className="inner">
									<InnerBlocks.Content />
								</div>
							</div>
		
						</div>
					</div>
		
				);
			}
		}
		
    ]


} );
