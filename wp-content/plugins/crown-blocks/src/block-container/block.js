
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

const spacingProfiles = [
	{
		spacingProfile:    'Default',
		paddingTopXl:      4,
		paddingBottomXl:   4,
		paddingXXl:        6,
		paddingTopLg:      4,
		paddingBottomLg:   4,
		paddingXLg:        6,
		paddingTopMd:      4,
		paddingBottomMd:   4,
		paddingXMd:        6,
		paddingTopSm:      4,
		paddingBottomSm:   4,
		paddingXSm:        6,
		paddingTopXs:      4,
		paddingBottomXs:   4,
		paddingXXs:        6
	},
	{
		spacingProfile:    'Standard Page Section',
		paddingTopXl:      10,
		paddingBottomXl:   10,
		paddingXXl:        5,
		paddingTopLg:      10,
		paddingBottomLg:   10,
		paddingXLg:        4,
		paddingTopMd:      7,
		paddingBottomMd:   7,
		paddingXMd:        3,
		paddingTopSm:      5,
		paddingBottomSm:   5,
		paddingXSm:        2,
		paddingTopXs:      5,
		paddingBottomXs:   5,
		paddingXXs:        2
	},
	{
		spacingProfile:    'None',
		paddingTopXl:      0,
		paddingBottomXl:   0,
		paddingXXl:        0,
		paddingTopLg:      0,
		paddingBottomLg:   0,
		paddingXLg:        0,
		paddingTopMd:      0,
		paddingBottomMd:   0,
		paddingXMd:        0,
		paddingTopSm:      0,
		paddingBottomSm:   0,
		paddingXSm:        0,
		paddingTopXs:      0,
		paddingBottomXs:   0,
		paddingXXs:        0
	}
];


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
		spacingProfile: { type: 'string', default: 'Default' },

		paddingTopXl:    { type: 'number', default: 4 },
		paddingBottomXl: { type: 'number', default: 4 },
		paddingXXl:      { type: 'number', default: 6 },

		paddingTopLg:    { type: 'number', default: 4 },
		paddingBottomLg: { type: 'number', default: 4 },
		paddingXLg:      { type: 'number', default: 6 },

		paddingTopMd:    { type: 'number', default: 4 },
		paddingBottomMd: { type: 'number', default: 4 },
		paddingXMd:      { type: 'number', default: 6 },

		paddingTopSm:    { type: 'number', default: 4 },
		paddingBottomSm: { type: 'number', default: 4 },
		paddingXSm:      { type: 'number', default: 6 },

		paddingTopXs:    { type: 'number', default: 4 },
		paddingBottomXs: { type: 'number', default: 4 },
		paddingXXs:      { type: 'number', default: 6 },

		dropShadowEnabled: { type: 'boolean', default: false },

		backgroundColor: { type: 'string', default: '#F1F4F7' },
		backgroundColorSlug: { type: 'string', default: 'ghost' },
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
			spacingProfile,

			paddingTopXl,
			paddingBottomXl,
			paddingXXl,

			paddingTopLg,
			paddingBottomLg,
			paddingXLg,

			paddingTopMd,
			paddingBottomMd,
			paddingXMd,

			paddingTopSm,
			paddingBottomSm,
			paddingXSm,

			paddingTopXs,
			paddingBottomXs,
			paddingXXs,
			
			dropShadowEnabled,

			backgroundColor,
			backgroundColorSlug,
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
			if(backgroundColorSlug) blockClasses.push('bg-color-' + backgroundColorSlug);
		}

		if(restictContentWidth) {
			blockClasses.push('contents-mw-' + contentsMaxWidth);
		}

		if(paddingTopXl >= 0) blockClasses.push('contents-pt-xl-' + paddingTopXl);
		if(paddingTopXl < 0) blockClasses.push('contents-ot-xl-' + Math.abs(paddingTopXl));
		if(paddingBottomXl >= 0) blockClasses.push('contents-pb-xl-' + paddingBottomXl);
		if(paddingBottomXl < 0) blockClasses.push('contents-ob-xl-' + Math.abs(paddingBottomXl));
		if(paddingXXl >= 0) blockClasses.push('contents-px-xl-' + paddingXXl);
		if(paddingXXl < 0) blockClasses.push('contents-ox-xl-' + Math.abs(paddingXXl));

		if(paddingTopLg >= 0) blockClasses.push('contents-pt-lg-' + paddingTopLg);
		if(paddingTopLg < 0) blockClasses.push('contents-ot-lg-' + Math.abs(paddingTopLg));
		if(paddingBottomLg >= 0) blockClasses.push('contents-pb-lg-' + paddingBottomLg);
		if(paddingBottomLg < 0) blockClasses.push('contents-ob-lg-' + Math.abs(paddingBottomLg));
		if(paddingXLg >= 0) blockClasses.push('contents-px-lg-' + paddingXLg);
		if(paddingXLg < 0) blockClasses.push('contents-ox-lg-' + Math.abs(paddingXLg));

		if(paddingTopMd >= 0) blockClasses.push('contents-pt-md-' + paddingTopMd);
		if(paddingTopMd < 0) blockClasses.push('contents-ot-md-' + Math.abs(paddingTopMd));
		if(paddingBottomMd >= 0) blockClasses.push('contents-pb-md-' + paddingBottomMd);
		if(paddingBottomMd < 0) blockClasses.push('contents-ob-md-' + Math.abs(paddingBottomMd));
		if(paddingXMd >= 0) blockClasses.push('contents-px-md-' + paddingXMd);
		if(paddingXMd < 0) blockClasses.push('contents-ox-md-' + Math.abs(paddingXMd));

		if(paddingTopSm >= 0) blockClasses.push('contents-pt-sm-' + paddingTopSm);
		if(paddingTopSm < 0) blockClasses.push('contents-ot-sm-' + Math.abs(paddingTopSm));
		if(paddingBottomSm >= 0) blockClasses.push('contents-pb-sm-' + paddingBottomSm);
		if(paddingBottomSm < 0) blockClasses.push('contents-ob-sm-' + Math.abs(paddingBottomSm));
		if(paddingXSm >= 0) blockClasses.push('contents-px-sm-' + paddingXSm);
		if(paddingXSm < 0) blockClasses.push('contents-ox-sm-' + Math.abs(paddingXSm));

		if(paddingTopXs >= 0) blockClasses.push('contents-pt-' + paddingTopXs);
		if(paddingTopXs < 0) blockClasses.push('contents-ot-' + Math.abs(paddingTopXs));
		if(paddingBottomXs >= 0) blockClasses.push('contents-pb-' + paddingBottomXs);
		if(paddingBottomXs < 0) blockClasses.push('contents-ob-' + Math.abs(paddingBottomXs));
		if(paddingXXs >= 0) blockClasses.push('contents-px-' + paddingXXs);
		if(paddingXXs < 0) blockClasses.push('contents-ox-' + Math.abs(paddingXXs));

		if(dropShadowEnabled && align != 'full') {
			blockClasses.push('has-drop-shadow');
		}

		let backgroundImageUrl = null;
		if(backgroundImageId) {
			backgroundImageUrl = backgroundImageData.sizes.fullscreen ? backgroundImageData.sizes.fullscreen.url : backgroundImageData.url;
			blockClasses.push('has-bg-image');
		}

		let setSpacingProfile = (value) => {
			let profile = _.find(spacingProfiles, function(n) { return n.spacingProfile == value; });
			if(typeof profile !== 'undefined') setAttributes(profile);
			setAttributes({ spacingProfile: value });
		};
		let spacingProfileOptions = [{ label: 'Custom', value: '' }];
		for(let i in spacingProfiles) {
			spacingProfileOptions.push({ label: spacingProfiles[i].spacingProfile, value: spacingProfiles[i].spacingProfile });
		}

		let setSpacingAttribute = (atts) => {
			atts.spacingProfile = '';
			setAttributes(atts);
		};

		let bgColorSettings = [{
			label: 'Background Color',
			value: backgroundColor,
			onChange: (value) => {
				let settings = wp.data.select('core/editor').getEditorSettings();
				let colorSlug = '';
				if(settings.colors) {
					let colorObject = getColorObjectByColorValue(settings.colors, value);
					if(colorObject) colorSlug = colorObject.slug;
				}
				setAttributes({ backgroundColor: value, backgroundColorSlug: colorSlug });
			}
		}];

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

				<PanelBody title={ 'Spacing' } initialOpen={ true }>

					<SelectControl
						label="Spacing Profile"
						value={ spacingProfile }
						onChange={ setSpacingProfile }
						options={ spacingProfileOptions }
					/>

					{ (responsiveDeviceMode == 'xl') && <div>

						<RangeControl
							label="Top Padding"
							value={ paddingTopXl }
							onChange={ (value) => setSpacingAttribute({ paddingTopXl: value }) }
							min={ -40 }
							max={ 40 }
							step={ 1 }
							marks={ [{ value: 40, label: '0' }] }
						/>

						<RangeControl
							label="Bottom Padding"
							value={ paddingBottomXl }
							onChange={ (value) => setSpacingAttribute({ paddingBottomXl: value }) }
							min={ -40 }
							max={ 40 }
							step={ 1 }
							marks={ [{ value: 40, label: '0' }] }
						/>

						<RangeControl
							label="Horizontal Padding"
							value={ paddingXXl }
							onChange={ (value) => setSpacingAttribute({ paddingXXl: value }) }
							min={ -40 }
							max={ 40 }
							step={ 1 }
							marks={ [{ value: 40, label: '0' }] }
						/>

					</div> }

					{ (responsiveDeviceMode == 'lg') && <div>

						<RangeControl
							label="Top Padding"
							value={ paddingTopLg }
							onChange={ (value) => setSpacingAttribute({ paddingTopLg: value }) }
							min={ -40 }
							max={ 40 }
							step={ 1 }
							marks={ [{ value: 40, label: '0' }] }
						/>

						<RangeControl
							label="Bottom Padding"
							value={ paddingBottomLg }
							onChange={ (value) => setSpacingAttribute({ paddingBottomLg: value }) }
							min={ -40 }
							max={ 40 }
							step={ 1 }
							marks={ [{ value: 40, label: '0' }] }
						/>

						<RangeControl
							label="Horizontal Padding"
							value={ paddingXLg }
							onChange={ (value) => setSpacingAttribute({ paddingXLg: value }) }
							min={ -40 }
							max={ 40 }
							step={ 1 }
							marks={ [{ value: 40, label: '0' }] }
						/>

					</div> }

					{ (responsiveDeviceMode == 'md') && <div>

						<RangeControl
							label="Top Padding"
							value={ paddingTopMd }
							onChange={ (value) => setSpacingAttribute({ paddingTopMd: value }) }
							min={ -40 }
							max={ 40 }
							step={ 1 }
							marks={ [{ value: 40, label: '0' }] }
						/>

						<RangeControl
							label="Bottom Padding"
							value={ paddingBottomMd }
							onChange={ (value) => setSpacingAttribute({ paddingBottomMd: value }) }
							min={ -40 }
							max={ 40 }
							step={ 1 }
							marks={ [{ value: 40, label: '0' }] }
						/>

						<RangeControl
							label="Horizontal Padding"
							value={ paddingXMd }
							onChange={ (value) => setSpacingAttribute({ paddingXMd: value }) }
							min={ -40 }
							max={ 40 }
							step={ 1 }
							marks={ [{ value: 40, label: '0' }] }
						/>

					</div> }

					{ (responsiveDeviceMode == 'sm') && <div>

						<RangeControl
							label="Top Padding"
							value={ paddingTopSm }
							onChange={ (value) => setSpacingAttribute({ paddingTopSm: value }) }
							min={ -40 }
							max={ 40 }
							step={ 1 }
							marks={ [{ value: 40, label: '0' }] }
						/>

						<RangeControl
							label="Bottom Padding"
							value={ paddingBottomSm }
							onChange={ (value) => setSpacingAttribute({ paddingBottomSm: value }) }
							min={ -40 }
							max={ 40 }
							step={ 1 }
							marks={ [{ value: 40, label: '0' }] }
						/>

						<RangeControl
							label="Horizontal Padding"
							value={ paddingXSm }
							onChange={ (value) => setSpacingAttribute({ paddingXSm: value }) }
							min={ -40 }
							max={ 40 }
							step={ 1 }
							marks={ [{ value: 40, label: '0' }] }
						/>

					</div> }

					{ (responsiveDeviceMode == 'xs') && <div>

						<RangeControl
							label="Top Padding"
							value={ paddingTopXs }
							onChange={ (value) => setSpacingAttribute({ paddingTopXs: value }) }
							min={ -40 }
							max={ 40 }
							step={ 1 }
							marks={ [{ value: 40, label: '0' }] }
						/>

						<RangeControl
							label="Bottom Padding"
							value={ paddingBottomXs }
							onChange={ (value) => setSpacingAttribute({ paddingBottomXs: value }) }
							min={ -40 }
							max={ 40 }
							step={ 1 }
							marks={ [{ value: 40, label: '0' }] }
						/>

						<RangeControl
							label="Horizontal Padding"
							value={ paddingXXs }
							onChange={ (value) => setSpacingAttribute({ paddingXXs: value }) }
							min={ -40 }
							max={ 40 }
							step={ 1 }
							marks={ [{ value: 40, label: '0' }] }
						/>

					</div> }

					{ (responsiveDeviceMode == 'xl') && <div>

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
							showTooltip={ false }
							withInputField={ false }
							marks={ [{ value: 0, label: '360' }, { value: 1, label: '540' }, { value: 2, label: '720' }, { value: 3, label: '900' }, { value: 4, label: '1080' }, { value: 5, label: '1260' }] }
						/> }

					</div> }

				</PanelBody>

				{/* { responsiveDeviceMode == 'xl' && <PanelBody title={ 'Styles' } initialOpen={ true }>
					
					{ align != 'full' && <ToggleControl
						label={ 'Enable drop shadow' }
						checked={ dropShadowEnabled }
						onChange={ (value) => { setAttributes({ dropShadowEnabled: value }); } }
					/> }

				</PanelBody> } */}

				{ responsiveDeviceMode == 'xl' && <PanelColorSettings
					title={ 'Background Color' }
					initialOpen={ true }
					colorSettings={ bgColorSettings }
				/> }

				{ responsiveDeviceMode == 'xl' && <PanelBody title={ 'Background Image' } className={ 'crown-blocks-background-image' } initialOpen={ true }>

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

					{/* { !! backgroundImageId && <ToggleControl
						label={ 'Contain background image' }
						checked={ backgroundImageContain }
						onChange={ (value) => { setAttributes({ backgroundImageContain: value }); } }
					/> } */}

				</PanelBody> }

				{ responsiveDeviceMode == 'xl' && <PanelBody title={ 'Text Color' } initialOpen={ true }>

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

			responsiveDeviceMode,
			restictContentWidth,
			contentsMaxWidth,

			paddingTopXl,
			paddingBottomXl,
			paddingXXl,

			paddingTopLg,
			paddingBottomLg,
			paddingXLg,

			paddingTopMd,
			paddingBottomMd,
			paddingXMd,

			paddingTopSm,
			paddingBottomSm,
			paddingXSm,

			paddingTopXs,
			paddingBottomXs,
			paddingXXs,
			
			dropShadowEnabled,

			backgroundColor,
			backgroundColorSlug,
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
			if(backgroundColorSlug) blockClasses.push('bg-color-' + backgroundColorSlug);
		}

		if(restictContentWidth) {
			blockClasses.push('contents-mw-' + contentsMaxWidth);
		}

		if(paddingTopXl >= 0) blockClasses.push('contents-pt-xl-' + paddingTopXl);
		if(paddingTopXl < 0) blockClasses.push('contents-ot-xl-' + Math.abs(paddingTopXl));
		if(paddingBottomXl >= 0) blockClasses.push('contents-pb-xl-' + paddingBottomXl);
		if(paddingBottomXl < 0) blockClasses.push('contents-ob-xl-' + Math.abs(paddingBottomXl));
		if(paddingXXl >= 0) blockClasses.push('contents-px-xl-' + paddingXXl);
		if(paddingXXl < 0) blockClasses.push('contents-ox-xl-' + Math.abs(paddingXXl));

		if(paddingTopLg >= 0) blockClasses.push('contents-pt-lg-' + paddingTopLg);
		if(paddingTopLg < 0) blockClasses.push('contents-ot-lg-' + Math.abs(paddingTopLg));
		if(paddingBottomLg >= 0) blockClasses.push('contents-pb-lg-' + paddingBottomLg);
		if(paddingBottomLg < 0) blockClasses.push('contents-ob-lg-' + Math.abs(paddingBottomLg));
		if(paddingXLg >= 0) blockClasses.push('contents-px-lg-' + paddingXLg);
		if(paddingXLg < 0) blockClasses.push('contents-ox-lg-' + Math.abs(paddingXLg));

		if(paddingTopMd >= 0) blockClasses.push('contents-pt-md-' + paddingTopMd);
		if(paddingTopMd < 0) blockClasses.push('contents-ot-md-' + Math.abs(paddingTopMd));
		if(paddingBottomMd >= 0) blockClasses.push('contents-pb-md-' + paddingBottomMd);
		if(paddingBottomMd < 0) blockClasses.push('contents-ob-md-' + Math.abs(paddingBottomMd));
		if(paddingXMd >= 0) blockClasses.push('contents-px-md-' + paddingXMd);
		if(paddingXMd < 0) blockClasses.push('contents-ox-md-' + Math.abs(paddingXMd));

		if(paddingTopSm >= 0) blockClasses.push('contents-pt-sm-' + paddingTopSm);
		if(paddingTopSm < 0) blockClasses.push('contents-ot-sm-' + Math.abs(paddingTopSm));
		if(paddingBottomSm >= 0) blockClasses.push('contents-pb-sm-' + paddingBottomSm);
		if(paddingBottomSm < 0) blockClasses.push('contents-ob-sm-' + Math.abs(paddingBottomSm));
		if(paddingXSm >= 0) blockClasses.push('contents-px-sm-' + paddingXSm);
		if(paddingXSm < 0) blockClasses.push('contents-ox-sm-' + Math.abs(paddingXSm));

		if(paddingTopXs >= 0) blockClasses.push('contents-pt-' + paddingTopXs);
		if(paddingTopXs < 0) blockClasses.push('contents-ot-' + Math.abs(paddingTopXs));
		if(paddingBottomXs >= 0) blockClasses.push('contents-pb-' + paddingBottomXs);
		if(paddingBottomXs < 0) blockClasses.push('contents-ob-' + Math.abs(paddingBottomXs));
		if(paddingXXs >= 0) blockClasses.push('contents-px-' + paddingXXs);
		if(paddingXXs < 0) blockClasses.push('contents-ox-' + Math.abs(paddingXXs));

		if(dropShadowEnabled && align != 'full') {
			blockClasses.push('has-drop-shadow');
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
