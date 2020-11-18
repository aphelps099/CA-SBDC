
// import helper functions
import CrownBlocks from '../common.js';

// import CSS
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InnerBlocks, RichText, Editable, MediaUpload, BlockControls, AlignmentToolbar, InspectorControls, PanelColorSettings, URLInput } = wp.blockEditor;
const { PanelBody, RadioControl, ColorPicker, Button, ButtonGroup, Icon, RangeControl, FocalPointPicker, ToggleControl, TextControl, SelectControl } = wp.components;
const { getColorObjectByColorValue } = wp.blockEditor;


registerBlockType('crown-blocks/container', {
	title: 'Container',
	description: 'Wrap content in a container to break up the page.',
	icon: <svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg"><g fill-rule="evenodd"><g><path d="M15,22 L15,24 L9,24 L9,22 L15,22 Z M2,16.999 L2,20 C2,21.1045695 2.8954305,22 4,22 L4,22 L7,22 L7,24 L3,24 C1.34314575,24 -1.27867033e-14,22.6568542 -1.29896094e-14,21 L1.22124533e-15,17 L2,16.999 Z M24,17 L24,21 C24,22.6568542 22.6568542,24 21,24 L17,24 L17,22 L20,22 C21.0543618,22 21.9181651,21.1841222 21.9945143,20.1492623 L22,20 L22,16.999 L24,17 Z M24,9 L24,15 L22,14.999 L22,8.999 L24,9 Z M2,8.999 L2,14.999 L1.22124533e-15,15 L1.22124533e-15,9 L2,8.999 Z M21,4.4408921e-15 C22.6568542,4.13653291e-15 24,1.34314575 24,3 L24,7 L22,6.999 L22,4 C22,2.9456382 21.1841222,2.08183488 20.1492623,2.00548574 L20,2 L17,2 L17,4.4408921e-15 L21,4.4408921e-15 Z M7,4.4408921e-15 L7,2 L4,2 C2.9456382,2 2.08183488,2.81587779 2.00548574,3.85073766 L2,4 L2,6.999 L1.22124533e-15,7 L-1.29896094e-14,3 C-1.31925155e-14,1.34314575 1.34314575,4.74525129e-15 3,4.4408921e-15 L7,4.4408921e-15 Z M15,2 L9,2 L9,4.4408921e-15 L15,4.4408921e-15 L15,2 Z" id="container"></path></g></g></svg>,
	category: 'layout',
	keywords: [ 'section', 'box', 'crown-blocks' ],

	supports: {
		align: [ 'wide', 'full' ],
		anchor: true
	},

	attributes: {
		align: { type: 'string', default: '' },
		responsiveDeviceMode: { type: 'string', default: 'xl' },
		restictContentWidth: { type: 'boolean', default: true },
		contentsMaxWidth: { type: 'number', default: 6 },

		overrideVerticalSpacingXl: { type: 'boolean', default: false },
		paddingTopXl: { type: 'number', default: 2 },
		paddingBottomXl: { type: 'number', default: 2 },
		overrideHorizontalSpacingXl: { type: 'boolean', default: false },
		paddingXXl: { type: 'number', default: 2 },
		overflowXXl: { type: 'number', default: 0 },

		overrideVerticalSpacingLg: { type: 'boolean', default: false },
		paddingTopLg: { type: 'number', default: 2 },
		paddingBottomLg: { type: 'number', default: 2 },
		overrideHorizontalSpacingLg: { type: 'boolean', default: false },
		paddingXLg: { type: 'number', default: 2 },
		overflowXLg: { type: 'number', default: 0 },

		overrideVerticalSpacingMd: { type: 'boolean', default: false },
		paddingTopMd: { type: 'number', default: 2 },
		paddingBottomMd: { type: 'number', default: 2 },
		overrideHorizontalSpacingMd: { type: 'boolean', default: false },
		paddingXMd: { type: 'number', default: 2 },
		overflowXMd: { type: 'number', default: 0 },

		overrideVerticalSpacingSm: { type: 'boolean', default: false },
		paddingTopSm: { type: 'number', default: 2 },
		paddingBottomSm: { type: 'number', default: 2 },
		overrideHorizontalSpacingSm: { type: 'boolean', default: false },
		paddingXSm: { type: 'number', default: 2 },
		overflowXSm: { type: 'number', default: 0 },

		overrideVerticalSpacingXs: { type: 'boolean', default: false },
		paddingTopXs: { type: 'number', default: 2 },
		paddingBottomXs: { type: 'number', default: 2 },
		overrideHorizontalSpacingXs: { type: 'boolean', default: false },
		paddingXXs: { type: 'number', default: 2 },
		overflowXXs: { type: 'number', default: 0 },

		dropShadowEnabled: { type: 'boolean', default: false },

		backgroundWaveTopEnabled: { type: 'boolean', default: false },
		backgroundWaveTopColor: { type: 'string', default: '#FFFFFF' },
		backgroundWaveBottomEnabled: { type: 'boolean', default: false },
		backgroundWaveBottomColor: { type: 'string', default: '#FFFFFF' },

		backgroundColor: { type: 'string', default: '#F7F7F7' },
		backgroundImageId: { type: 'number' },
		backgroundImageData: { type: 'object' },
		backgroundImageFocalPoint: { type: 'object', default: { x: 0.5, y: 0.5 } },
		backgroundImageOpacity: { type: 'number', default: 100 },
		backgroundImageGrayscale: { type: 'number', default: 0 },
		backgroundImageBlendMode: { type: 'string', default: 'normal' },
		backgroundImageContain: { type: 'boolean', default: false },
		textColor: { type: 'string', default: 'auto' }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		let disallowedBlocks = [];
		const ALLOWED_BLOCKS = wp.blocks.getBlockTypes().map(block => block.name).filter(blockName => !disallowedBlocks.includes(blockName));

		const {
			align,

			responsiveDeviceMode,
			restictContentWidth,
			contentsMaxWidth,

			overrideVerticalSpacingXl,
			paddingTopXl,
			paddingBottomXl,
			overrideHorizontalSpacingXl,
			paddingXXl,
			overflowXXl,

			overrideVerticalSpacingLg,
			paddingTopLg,
			paddingBottomLg,
			overrideHorizontalSpacingLg,
			paddingXLg,
			overflowXLg,

			overrideVerticalSpacingMd,
			paddingTopMd,
			paddingBottomMd,
			overrideHorizontalSpacingMd,
			paddingXMd,
			overflowXMd,

			overrideVerticalSpacingSm,
			paddingTopSm,
			paddingBottomSm,
			overrideHorizontalSpacingSm,
			paddingXSm,
			overflowXSm,

			overrideVerticalSpacingXs,
			paddingTopXs,
			paddingBottomXs,
			overrideHorizontalSpacingXs,
			paddingXXs,
			overflowXXs,
			
			dropShadowEnabled,

			backgroundWaveTopEnabled,
			backgroundWaveTopColor,
			backgroundWaveBottomEnabled,
			backgroundWaveBottomColor,

			backgroundColor,
			backgroundImageId,
			backgroundImageData,
			backgroundImageFocalPoint,
			backgroundImageOpacity,
			backgroundImageGrayscale,
			backgroundImageBlendMode,
			backgroundImageContain,
			textColor
		} = attributes;

		let blockClasses = [ className ];

		if(textColor == 'auto' && backgroundColor) {
			blockClasses.push('text-color-' + (CrownBlocks.isDarkColor(backgroundColor) ? 'light' : 'dark'));
		} else if(textColor != 'auto') {
			blockClasses.push('text-color-' + textColor);
		}

		let blockStyle = {};
		if(backgroundColor) {
			blockStyle.backgroundColor = backgroundColor;
			let settings = wp.data.select('core/editor').getEditorSettings();
			if(settings.colors) {
				let colorObject = getColorObjectByColorValue(settings.colors, backgroundColor);
				if(colorObject) blockClasses.push('bg-color-' + colorObject.slug);
			}
		}

		if(restictContentWidth) {
			blockClasses.push('contents-mw-' + contentsMaxWidth);
		}

		if(overrideVerticalSpacingXl) {
			blockClasses.push('contents-pt-xl-' + paddingTopXl);
			blockClasses.push('contents-pb-xl-' + paddingBottomXl);
		}
		if(overrideHorizontalSpacingXl) {
			blockClasses.push('contents-px-xl-' + paddingXXl);
			if(overflowXXl > 0) blockClasses.push('contents-ox-xl-' + overflowXXl);
		}

		if(overrideVerticalSpacingLg) {
			blockClasses.push('contents-pt-lg-' + paddingTopLg);
			blockClasses.push('contents-pb-lg-' + paddingBottomLg);
		}
		if(overrideHorizontalSpacingLg) {
			blockClasses.push('contents-px-lg-' + paddingXLg);
			if(overflowXLg > 0) blockClasses.push('contents-ox-lg-' + overflowXLg);
		}

		if(overrideVerticalSpacingMd) {
			blockClasses.push('contents-pt-md-' + paddingTopMd);
			blockClasses.push('contents-pb-md-' + paddingBottomMd);
		}
		if(overrideHorizontalSpacingMd) {
			blockClasses.push('contents-px-md-' + paddingXMd);
			if(overflowXMd > 0) blockClasses.push('contents-ox-md-' + overflowXMd);
		}

		if(overrideVerticalSpacingSm) {
			blockClasses.push('contents-pt-sm-' + paddingTopSm);
			blockClasses.push('contents-pb-sm-' + paddingBottomSm);
		}
		if(overrideHorizontalSpacingSm) {
			blockClasses.push('contents-px-sm-' + paddingXSm);
			if(overflowXSm > 0) blockClasses.push('contents-ox-sm-' + overflowXSm);
		}

		if(overrideVerticalSpacingXs) {
			blockClasses.push('contents-pt-' + paddingTopXs);
			blockClasses.push('contents-pb-' + paddingBottomXs);
		}
		if(overrideHorizontalSpacingXs) {
			blockClasses.push('contents-ps-' + paddingXXs);
			if(overflowXXs > 0) blockClasses.push('contents-ox-' + overflowXXs);
		}

		if(dropShadowEnabled && align != 'full') {
			blockClasses.push('has-drop-shadow');
		}

		if(backgroundWaveTopEnabled && align == 'full') {
			blockClasses.push('bg-wave-top');
		}
		if(backgroundWaveBottomEnabled && align == 'full') {
			blockClasses.push('bg-wave-bottom');
		}

		let backgroundImageUrl = null;
		if(backgroundImageId) {
			backgroundImageUrl = backgroundImageData.sizes.fullscreen ? backgroundImageData.sizes.fullscreen.url : backgroundImageData.url;
			blockClasses.push('has-bg-image');
		}

		let bgColorSettings = [{
			label: 'Background Color',
			value: backgroundColor,
			onChange: (value) => setAttributes({ backgroundColor: value ? value : '' })
		}];
		if(backgroundWaveTopEnabled && align == 'full') {
			bgColorSettings.push({
				label: 'Top Wave Color',
				value: backgroundWaveTopColor,
				onChange: (value) => setAttributes({ backgroundWaveTopColor: value })
			});
		}
		if(backgroundWaveBottomEnabled && align == 'full') {
			bgColorSettings.push({
				label: 'Bottom Wave Color',
				value: backgroundWaveBottomColor,
				onChange: (value) => setAttributes({ backgroundWaveBottomColor: value })
			});
		}

		return [

			<InspectorControls key="inspector-controls">

				<div class="crown-blocks-responsive-device-mode-toggles">
					<ButtonGroup>
						<Button isPrimary={ responsiveDeviceMode == 'xl' } onClick={ (e) => setAttributes({ responsiveDeviceMode: 'xl' }) }>
							<span class="inner">
								<Icon icon={
									<svg class="bi bi-display" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
										<path d="M5.75 13.5c.167-.333.25-.833.25-1.5h4c0 .667.083 1.167.25 1.5H11a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1h.75z"/>
										<path fill-rule="evenodd" d="M13.991 3H2c-.325 0-.502.078-.602.145a.758.758 0 0 0-.254.302A1.46 1.46 0 0 0 1 4.01V10c0 .325.078.502.145.602.07.105.17.188.302.254a1.464 1.464 0 0 0 .538.143L2.01 11H14c.325 0 .502-.078.602-.145a.758.758 0 0 0 .254-.302 1.464 1.464 0 0 0 .143-.538L15 9.99V4c0-.325-.078-.502-.145-.602a.757.757 0 0 0-.302-.254A1.46 1.46 0 0 0 13.99 3zM14 2H2C0 2 0 4 0 4v6c0 2 2 2 2 2h12c2 0 2-2 2-2V4c0-2-2-2-2-2z"/>
									</svg>
								} />
								<span class="label">Desktop <span class="sub-label">Widescreen</span> <span class="sub-label">1200px</span></span>
							</span>
						</Button>
						<Button isPrimary={ responsiveDeviceMode == 'lg' } onClick={ (e) => setAttributes({ responsiveDeviceMode: 'lg' }) }>
							<span class="inner">
								<Icon icon={
									<svg class="bi bi-tablet-landscape" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd" d="M1 4v8a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1zm-1 8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H2a2 2 0 0 0-2 2v8z"/>
										<path fill-rule="evenodd" d="M14 8a1 1 0 1 0-2 0 1 1 0 0 0 2 0z"/>
									</svg>
								} />
								<span class="label">Tablet <span class="sub-label">Landscape</span> <span class="sub-label">992px</span></span>
							</span>
						</Button>
						<Button isPrimary={ responsiveDeviceMode == 'md' } onClick={ (e) => setAttributes({ responsiveDeviceMode: 'md' }) }>
							<span class="inner">
								<Icon icon={
									<svg class="bi bi-tablet" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd" d="M12 1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H4z"/>
										<path fill-rule="evenodd" d="M8 14a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
									</svg>
								} />
								<span class="label">Tablet <span class="sub-label">Portrait</span> <span class="sub-label">768px</span></span>
							</span>
						</Button>
						<Button isPrimary={ responsiveDeviceMode == 'sm' } onClick={ (e) => setAttributes({ responsiveDeviceMode: 'sm' }) }>
							<span class="inner">
								<Icon icon={
									<svg class="bi bi-phone-landscape" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd" d="M1 4.5v6a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-6a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1zm-1 6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H2a2 2 0 0 0-2 2v6z"/>
										<path fill-rule="evenodd" d="M14 7.5a1 1 0 1 0-2 0 1 1 0 0 0 2 0z"/>
									</svg>
								} />
								<span class="label">Mobile <span class="sub-label">Landscape</span> <span class="sub-label">576px</span></span>
							</span>
						</Button>
						<Button isPrimary={ responsiveDeviceMode == 'xs' } onClick={ (e) => setAttributes({ responsiveDeviceMode: 'xs' }) }>
							<span class="inner">
								<Icon icon={
									<svg class="bi bi-phone" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd" d="M11 1H5a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM5 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H5z"/>
										<path fill-rule="evenodd" d="M8 14a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
									</svg>
								} />
								<span class="label">Mobile <span class="sub-label">Portrait</span> <span class="sub-label">Base</span></span>
							</span>
						</Button>
					</ButtonGroup>
				</div>

				{ responsiveDeviceMode == 'xl' && <PanelBody title={ 'Spacing' } initialOpen={ false }>

					<ToggleControl
						label={ 'Restrict content width' }
						checked={ restictContentWidth }
						onChange={ (value) => { setAttributes({ restictContentWidth: value }); } }
					/>

					{ !! restictContentWidth && <RangeControl
						label="Max Content Width"
						value={ contentsMaxWidth }
						onChange={ (value) => setAttributes({ contentsMaxWidth: value }) }
						min={ 1 }
						max={ 6 }
					/> }

					<ToggleControl
						label={ 'Override vertical spacing' }
						checked={ overrideVerticalSpacingXl }
						onChange={ (value) => { setAttributes({ overrideVerticalSpacingXl: value }); } }
					/>

					{ !! overrideVerticalSpacingXl && <RangeControl
						label="Top Padding"
						value={ paddingTopXl }
						onChange={ (value) => setAttributes({ paddingTopXl: value }) }
						min={ 0 }
						max={ 20 }
					/> }

					{ !! overrideVerticalSpacingXl && <RangeControl
						label="Bottom Padding"
						value={ paddingBottomXl }
						onChange={ (value) => setAttributes({ paddingBottomXl: value }) }
						min={ 0 }
						max={ 20 }
					/> }

					<ToggleControl
						label={ 'Override horizontal spacing' }
						checked={ overrideHorizontalSpacingXl }
						onChange={ (value) => { setAttributes({ overrideHorizontalSpacingXl: value }); } }
					/>

					{ !! overrideHorizontalSpacingXl && <RangeControl
						label="Horizontal Padding"
						value={ paddingXXl }
						onChange={ (value) => setAttributes({ paddingXXl: value }) }
						min={ 0 }
						max={ 20 }
					/> }

					{ !! overrideHorizontalSpacingXl && <RangeControl
						label="Horizontal Overflow"
						value={ overflowXXl }
						onChange={ (value) => setAttributes({ overflowXXl: value }) }
						min={ 0 }
						max={ 20 }
					/> }

				</PanelBody> }

				{ responsiveDeviceMode == 'lg' && <PanelBody title={ 'Spacing' } initialOpen={ true }>

					<ToggleControl
						label={ 'Override vertical spacing' }
						checked={ overrideVerticalSpacingLg }
						onChange={ (value) => { setAttributes({ overrideVerticalSpacingLg: value }); } }
					/>

					{ !! overrideVerticalSpacingLg && <RangeControl
						label="Top Padding"
						value={ paddingTopLg }
						onChange={ (value) => setAttributes({ paddingTopLg: value }) }
						min={ 0 }
						max={ 20 }
					/> }

					{ !! overrideVerticalSpacingLg && <RangeControl
						label="Bottom Padding"
						value={ paddingBottomLg }
						onChange={ (value) => setAttributes({ paddingBottomLg: value }) }
						min={ 0 }
						max={ 20 }
					/> }

					<ToggleControl
						label={ 'Override horizontal spacing' }
						checked={ overrideHorizontalSpacingLg }
						onChange={ (value) => { setAttributes({ overrideHorizontalSpacingLg: value }); } }
					/>

					{ !! overrideHorizontalSpacingLg && <RangeControl
						label="Horizontal Padding"
						value={ paddingXLg }
						onChange={ (value) => setAttributes({ paddingXLg: value }) }
						min={ 0 }
						max={ 20 }
					/> }

					{ !! overrideHorizontalSpacingLg && <RangeControl
						label="Horizontal Overflow"
						value={ overflowXLg }
						onChange={ (value) => setAttributes({ overflowXLg: value }) }
						min={ 0 }
						max={ 20 }
					/> }

				</PanelBody> }

				{ responsiveDeviceMode == 'md' && <PanelBody title={ 'Spacing' } initialOpen={ true }>

					<ToggleControl
						label={ 'Override vertical spacing' }
						checked={ overrideVerticalSpacingMd }
						onChange={ (value) => { setAttributes({ overrideVerticalSpacingMd: value }); } }
					/>

					{ !! overrideVerticalSpacingMd && <RangeControl
						label="Top Padding"
						value={ paddingTopMd }
						onChange={ (value) => setAttributes({ paddingTopMd: value }) }
						min={ 0 }
						max={ 20 }
					/> }

					{ !! overrideVerticalSpacingMd && <RangeControl
						label="Bottom Padding"
						value={ paddingBottomMd }
						onChange={ (value) => setAttributes({ paddingBottomMd: value }) }
						min={ 0 }
						max={ 20 }
					/> }

					<ToggleControl
						label={ 'Override horizontal spacing' }
						checked={ overrideHorizontalSpacingMd }
						onChange={ (value) => { setAttributes({ overrideHorizontalSpacingMd: value }); } }
					/>

					{ !! overrideHorizontalSpacingMd && <RangeControl
						label="Horizontal Padding"
						value={ paddingXMd }
						onChange={ (value) => setAttributes({ paddingXMd: value }) }
						min={ 0 }
						max={ 20 }
					/> }

					{ !! overrideHorizontalSpacingMd && <RangeControl
						label="Horizontal Overflow"
						value={ overflowXMd }
						onChange={ (value) => setAttributes({ overflowXMd: value }) }
						min={ 0 }
						max={ 20 }
					/> }

				</PanelBody> }

				{ responsiveDeviceMode == 'sm' && <PanelBody title={ 'Spacing' } initialOpen={ true }>

					<ToggleControl
						label={ 'Override vertical spacing' }
						checked={ overrideVerticalSpacingSm }
						onChange={ (value) => { setAttributes({ overrideVerticalSpacingSm: value }); } }
					/>

					{ !! overrideVerticalSpacingSm && <RangeControl
						label="Top Padding"
						value={ paddingTopSm }
						onChange={ (value) => setAttributes({ paddingTopSm: value }) }
						min={ 0 }
						max={ 20 }
					/> }

					{ !! overrideVerticalSpacingSm && <RangeControl
						label="Bottom Padding"
						value={ paddingBottomSm }
						onChange={ (value) => setAttributes({ paddingBottomSm: value }) }
						min={ 0 }
						max={ 20 }
					/> }

					<ToggleControl
						label={ 'Override horizontal spacing' }
						checked={ overrideHorizontalSpacingSm }
						onChange={ (value) => { setAttributes({ overrideHorizontalSpacingSm: value }); } }
					/>

					{ !! overrideHorizontalSpacingSm && <RangeControl
						label="Horizontal Padding"
						value={ paddingXSm }
						onChange={ (value) => setAttributes({ paddingXSm: value }) }
						min={ 0 }
						max={ 20 }
					/> }

					{ !! overrideHorizontalSpacingSm && <RangeControl
						label="Horizontal Overflow"
						value={ overflowXSm }
						onChange={ (value) => setAttributes({ overflowXSm: value }) }
						min={ 0 }
						max={ 20 }
					/> }

				</PanelBody> }

				{ responsiveDeviceMode == 'xs' && <PanelBody title={ 'Spacing' } initialOpen={ true }>

					<ToggleControl
						label={ 'Override vertical spacing' }
						checked={ overrideVerticalSpacingXs }
						onChange={ (value) => { setAttributes({ overrideVerticalSpacingXs: value }); } }
					/>

					{ !! overrideVerticalSpacingXs && <RangeControl
						label="Top Padding"
						value={ paddingTopXs }
						onChange={ (value) => setAttributes({ paddingTopXs: value }) }
						min={ 0 }
						max={ 20 }
					/> }

					{ !! overrideVerticalSpacingXs && <RangeControl
						label="Bottom Padding"
						value={ paddingBottomXs }
						onChange={ (value) => setAttributes({ paddingBottomXs: value }) }
						min={ 0 }
						max={ 20 }
					/> }

					<ToggleControl
						label={ 'Override horizontal spacing' }
						checked={ overrideHorizontalSpacingXs }
						onChange={ (value) => { setAttributes({ overrideHorizontalSpacingXs: value }); } }
					/>

					{ !! overrideHorizontalSpacingXs && <RangeControl
						label="Horizontal Padding"
						value={ paddingXXs }
						onChange={ (value) => setAttributes({ paddingXXs: value }) }
						min={ 0 }
						max={ 20 }
					/> }

					{ !! overrideHorizontalSpacingXs && <RangeControl
						label="Horizontal Overflow"
						value={ overflowXXs }
						onChange={ (value) => setAttributes({ overflowXXs: value }) }
						min={ 0 }
						max={ 20 }
					/> }

				</PanelBody> }
				
				{ responsiveDeviceMode == 'xl' && <PanelBody title={ 'Styles' } initialOpen={ false }>
					
					{ align != 'full' && <ToggleControl
						label={ 'Enable drop shadow' }
						checked={ dropShadowEnabled }
						onChange={ (value) => { setAttributes({ dropShadowEnabled: value }); } }
					/> }

					{ align == 'full' && <ToggleControl
						label={ 'Enable top wave' }
						checked={ backgroundWaveTopEnabled }
						onChange={ (value) => { setAttributes({ backgroundWaveTopEnabled: value }); } }
					/> }

					{ align == 'full' && <ToggleControl
						label={ 'Enable bottom wave' }
						checked={ backgroundWaveBottomEnabled }
						onChange={ (value) => { setAttributes({ backgroundWaveBottomEnabled: value }); } }
					/> }

				</PanelBody> }

				{ responsiveDeviceMode == 'xl' && <PanelColorSettings
					title={ 'Background Color' }
					initialOpen={ false }
					colorSettings={ bgColorSettings }
				/> }

				{ responsiveDeviceMode == 'xl' && <PanelBody title={ 'Background Image' } className={ 'crown-blocks-background-image' } initialOpen={ false }>

					{ !! backgroundImageId && <FocalPointPicker 
						label="Focal Point"
						url={ backgroundImageData.sizes.medium ? backgroundImageData.sizes.medium.url : backgroundImageData.sizes.thumbnail.url }
						dimensions={ { width: 400, height: 100 } }
						value={ backgroundImageFocalPoint }
						onChange={ (value) => setAttributes({ backgroundImageFocalPoint: value }) } 
					/> }

					<MediaUpload
						onSelect={ (media) => { setAttributes({ backgroundImageId: media.id, backgroundImageData: media, backgroundImageFocalPoint: { x: 0.5, y: 0.5 } }); } }
						type="image"
						value={ backgroundImageId }
						render={ ({ open }) => (
							<div className={ 'crown-blocks-media-upload' }>
								{/* { backgroundImageId && <Button className={ 'image-preview' } onClick={ open }><img src={ backgroundImageData.sizes.medium ? backgroundImageData.sizes.medium.url : backgroundImageData.sizes.thumbnail.url } /></Button> } */}
								<Button className={ 'button' } onClick={ open }>Select Image</Button>
								{ backgroundImageId && <Button className={ 'button is-link is-destructive' } onClick={ (e) => { setAttributes({ backgroundImageId: null, backgroundImageData: null }); } }>Remove Image</Button> }
							</div>
						) }
					/>

					{ !! backgroundImageId && <RangeControl
						label="Opacity"
						value={ backgroundImageOpacity }
						onChange={ (value) => setAttributes({ backgroundImageOpacity: value }) }
						min={ 0 }
						max={ 100 }
					/> }

					{ !! backgroundImageId && <RangeControl
						label="Grayscale"
						value={ backgroundImageGrayscale }
						onChange={ (value) => setAttributes({ backgroundImageGrayscale: value }) }
						min={ 0 }
						max={ 100 }
					/> }

					{ !! backgroundImageId && <SelectControl
						label="Blend Mode"
						value={ backgroundImageBlendMode }
						onChange={ (value) => setAttributes({ backgroundImageBlendMode: value }) }
						options={ [
							{ label: 'Normal', value: 'normal' },
							{ label: 'Multiply', value: 'multiply' },
							{ label: 'Screen', value: 'screen' },
							{ label: 'Overlay', value: 'overlay' },
							{ label: 'Soft Light', value: 'soft-light' },
							{ label: 'Hard Light', value: 'hard-light' },
							{ label: 'Darken', value: 'darken' },
							{ label: 'Lighten', value: 'lighten' }
						] }
					/> }

					{ !! backgroundImageId && <ToggleControl
						label={ 'Contain background image' }
						checked={ backgroundImageContain }
						onChange={ (value) => { setAttributes({ backgroundImageContain: value }); } }
					/> }

				</PanelBody> }

				{ responsiveDeviceMode == 'xl' && <PanelBody title={ 'Text Color' } initialOpen={ false }>

					<ButtonGroup>
						<Button isPrimary={ textColor == 'auto' } isSecondary={ textColor != 'auto' } onClick={ (e) => setAttributes({ textColor: 'auto' }) }>Auto</Button>
						<Button isPrimary={ textColor == 'dark' } isSecondary={ textColor != 'dark' } onClick={ (e) => setAttributes({ textColor: 'dark' }) }>Dark</Button>
						<Button isPrimary={ textColor == 'light' } isSecondary={ textColor != 'light' } onClick={ (e) => setAttributes({ textColor: 'light' }) }>Light</Button>
					</ButtonGroup>

				</PanelBody> }

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<div className={ blockClasses.join(' ') } style={ blockStyle } key="container">
					<div className="container-bg">
						{ backgroundImageUrl && <div className={ 'bg-image' } style={ {
							backgroundImage: 'url(' + backgroundImageUrl + ')',
							opacity: (backgroundImageOpacity / 100),
							backgroundPosition: `${ backgroundImageFocalPoint.x * 100 }% ${ backgroundImageFocalPoint.y * 100 }%`,
							filter: `grayscale(${ backgroundImageGrayscale / 100 })`,
							mixBlendMode: backgroundImageBlendMode,
							backgroundSize: backgroundImageContain ? 'contain' : 'cover'
						} }></div> }
						{ (backgroundWaveTopEnabled && align == 'full') && <svg class="wave-top" style={ { fill: backgroundWaveTopColor } } width="1440px" height="130px" viewBox="0 0 1440 130" version="1.1" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
							<path fill-rule="evenodd" d="M1440.00021,0 L1440.00021,5.48999154 C1325.76192,-5.02699514 1218.42852,1.58221704 1118,25.3176281 C821.177799,95.4689878 643.66117,119.408195 527.515345,126.332608 C376.707877,135.323487 202.533839,120.790372 4.99323133,82.7332625 L-4.54747351e-13,81.766 L-4.54747351e-13,0 L1440.00021,0 Z"></path>
						</svg> }
						{ (backgroundWaveBottomEnabled && align == 'full') && <svg class="wave-bottom" style={ { fill: backgroundWaveBottomColor } } width="1440px" height="130px" viewBox="0 0 1440 130" version="1.1" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
							<path fill-rule="evenodd" d="M1440.00021,5.48999154 L1440.00021,130 L0,130 L0,81.7662281 L4.99323133,82.7332625 C202.533839,120.790372 376.707877,135.323487 527.515345,126.332608 C643.66117,119.408195 821.177799,95.4689878 1118,25.3176281 C1218.42852,1.58221704 1325.76192,-5.02699514 1440.00021,5.48999154 Z"></path>
						</svg> }
					</div>
					<div className="inner">
						<div className="container-contents">
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
			align,
			restictContentWidth,
			contentsMaxWidth,

			overrideVerticalSpacingXl,
			paddingTopXl,
			paddingBottomXl,
			overrideHorizontalSpacingXl,
			paddingXXl,
			overflowXXl,

			overrideVerticalSpacingLg,
			paddingTopLg,
			paddingBottomLg,
			overrideHorizontalSpacingLg,
			paddingXLg,
			overflowXLg,

			overrideVerticalSpacingMd,
			paddingTopMd,
			paddingBottomMd,
			overrideHorizontalSpacingMd,
			paddingXMd,
			overflowXMd,

			overrideVerticalSpacingSm,
			paddingTopSm,
			paddingBottomSm,
			overrideHorizontalSpacingSm,
			paddingXSm,
			overflowXSm,

			overrideVerticalSpacingXs,
			paddingTopXs,
			paddingBottomXs,
			overrideHorizontalSpacingXs,
			paddingXXs,
			overflowXXs,

			dropShadowEnabled,

			backgroundWaveTopEnabled,
			backgroundWaveTopColor,
			backgroundWaveBottomEnabled,
			backgroundWaveBottomColor,

			backgroundColor,
			backgroundImageId,
			backgroundImageData,
			backgroundImageFocalPoint,
			backgroundImageOpacity,
			backgroundImageGrayscale,
			backgroundImageBlendMode,
			backgroundImageContain,
			textColor
		} = attributes;

		let blockClasses = [ className ];

		if(textColor == 'auto' && backgroundColor) {
			blockClasses.push('text-color-' + (CrownBlocks.isDarkColor(backgroundColor) ? 'light' : 'dark'));
		} else if(textColor != 'auto') {
			blockClasses.push('text-color-' + textColor);
		}

		let blockStyle = {};
		if(backgroundColor) {
			blockStyle.backgroundColor = backgroundColor;
			let settings = wp.data.select('core/editor').getEditorSettings();
			if(settings.colors) {
				let colorObject = getColorObjectByColorValue(settings.colors, backgroundColor);
				if(colorObject) blockClasses.push('bg-color-' + colorObject.slug);
			}
		}

		if(restictContentWidth) {
			blockClasses.push('contents-mw-' + contentsMaxWidth);
		}

		if(overrideVerticalSpacingXl) {
			blockClasses.push('contents-pt-xl-' + paddingTopXl);
			blockClasses.push('contents-pb-xl-' + paddingBottomXl);
		}
		if(overrideHorizontalSpacingXl) {
			blockClasses.push('contents-px-xl-' + paddingXXl);
			if(overflowXXl > 0) blockClasses.push('contents-ox-xl-' + overflowXXl);
		}

		if(overrideVerticalSpacingLg) {
			blockClasses.push('contents-pt-lg-' + paddingTopLg);
			blockClasses.push('contents-pb-lg-' + paddingBottomLg);
		}
		if(overrideHorizontalSpacingLg) {
			blockClasses.push('contents-px-lg-' + paddingXLg);
			if(overflowXLg > 0) blockClasses.push('contents-ox-lg-' + overflowXLg);
		}

		if(overrideVerticalSpacingMd) {
			blockClasses.push('contents-pt-md-' + paddingTopMd);
			blockClasses.push('contents-pb-md-' + paddingBottomMd);
		}
		if(overrideHorizontalSpacingMd) {
			blockClasses.push('contents-px-md-' + paddingXMd);
			if(overflowXMd > 0) blockClasses.push('contents-ox-md-' + overflowXMd);
		}

		if(overrideVerticalSpacingSm) {
			blockClasses.push('contents-pt-sm-' + paddingTopSm);
			blockClasses.push('contents-pb-sm-' + paddingBottomSm);
		}
		if(overrideHorizontalSpacingSm) {
			blockClasses.push('contents-px-sm-' + paddingXSm);
			if(overflowXSm > 0) blockClasses.push('contents-ox-sm-' + overflowXSm);
		}

		if(overrideVerticalSpacingXs) {
			blockClasses.push('contents-pt-' + paddingTopXs);
			blockClasses.push('contents-pb-' + paddingBottomXs);
		}
		if(overrideHorizontalSpacingXs) {
			blockClasses.push('contents-ps-' + paddingXXs);
			if(overflowXXs > 0) blockClasses.push('contents-ox-' + overflowXXs);
		}

		if(dropShadowEnabled && align != 'full') {
			blockClasses.push('has-drop-shadow');
		}

		if(backgroundWaveTopEnabled && align == 'full') {
			blockClasses.push('bg-wave-top');
		}
		if(backgroundWaveBottomEnabled && align == 'full') {
			blockClasses.push('bg-wave-bottom');
		}

		let backgroundImageUrl = null;
		if(backgroundImageId) {
			backgroundImageUrl = backgroundImageData.sizes.fullscreen ? backgroundImageData.sizes.fullscreen.url : backgroundImageData.url;
			blockClasses.push('has-bg-image');
		}

		return (

			<div className={ blockClasses.join(' ') } style={ blockStyle } key="container">
				<div className="container-bg">
					{ backgroundImageUrl && <div className={ 'bg-image' } style={ {
						backgroundImage: 'url(' + backgroundImageUrl + ')',
						opacity: (backgroundImageOpacity / 100),
						backgroundPosition: `${ backgroundImageFocalPoint.x * 100 }% ${ backgroundImageFocalPoint.y * 100 }%`,
						filter: `grayscale(${ backgroundImageGrayscale / 100 })`,
						mixBlendMode: backgroundImageBlendMode,
						backgroundSize: backgroundImageContain ? 'contain' : 'cover'
					} }></div> }
					{ (backgroundWaveTopEnabled && align == 'full') && <svg class="wave-top" style={ { fill: backgroundWaveTopColor } } width="1440px" height="130px" viewBox="0 0 1440 130" version="1.1" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
						<path fill-rule="evenodd" d="M1440.00021,0 L1440.00021,5.48999154 C1325.76192,-5.02699514 1218.42852,1.58221704 1118,25.3176281 C821.177799,95.4689878 643.66117,119.408195 527.515345,126.332608 C376.707877,135.323487 202.533839,120.790372 4.99323133,82.7332625 L-4.54747351e-13,81.766 L-4.54747351e-13,0 L1440.00021,0 Z"></path>
					</svg> }
					{ (backgroundWaveBottomEnabled && align == 'full') && <svg class="wave-bottom" style={ { fill: backgroundWaveBottomColor } } width="1440px" height="130px" viewBox="0 0 1440 130" version="1.1" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
						<path fill-rule="evenodd" d="M1440.00021,5.48999154 L1440.00021,130 L0,130 L0,81.7662281 L4.99323133,82.7332625 C202.533839,120.790372 376.707877,135.323487 527.515345,126.332608 C643.66117,119.408195 821.177799,95.4689878 1118,25.3176281 C1218.42852,1.58221704 1325.76192,-5.02699514 1440.00021,5.48999154 Z"></path>
					</svg> }
				</div>
				<div className="inner">
					<div className="container-contents">
						<div className="inner">
							<InnerBlocks.Content />
						</div>
					</div>
				</div>
			</div>

		);
	}


} );
