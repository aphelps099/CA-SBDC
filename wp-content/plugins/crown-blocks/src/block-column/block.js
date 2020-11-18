
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


registerBlockType('crown-blocks/column', {
	title: 'Column',
	icon: 'columns',
	category: 'layout',
	keywords: [ 'column', 'sidebar', 'crown-blocks' ],
	parent: [ 'crown-blocks/multi-column' ],

	supports: {
		// inserter: false
	},

	attributes: {

		responsiveDeviceMode: { type: 'string', default: 'xl' },

		overrideVerticalSpacingXl: { type: 'boolean', default: false },
		paddingTopXl: { type: 'number', default: 0 },
		paddingBottomXl: { type: 'number', default: 0 },
		overflowBottomXl: { type: 'number', default: 0 },
		overrideHorizontalSpacingXl: { type: 'boolean', default: false },
		paddingLeftXl: { type: 'number', default: 0 },
		paddingRightXl: { type: 'number', default: 0 },

		overrideVerticalSpacingLg: { type: 'boolean', default: false },
		paddingTopLg: { type: 'number', default: 0 },
		paddingBottomLg: { type: 'number', default: 0 },
		overflowBottomLg: { type: 'number', default: 0 },
		overrideHorizontalSpacingLg: { type: 'boolean', default: false },
		paddingLeftLg: { type: 'number', default: 0 },
		paddingRightLg: { type: 'number', default: 0 },

		overrideVerticalSpacingMd: { type: 'boolean', default: false },
		paddingTopMd: { type: 'number', default: 0 },
		paddingBottomMd: { type: 'number', default: 0 },
		overflowBottomMd: { type: 'number', default: 0 },
		overrideHorizontalSpacingMd: { type: 'boolean', default: false },
		paddingLeftMd: { type: 'number', default: 0 },
		paddingRightMd: { type: 'number', default: 0 },

		overrideVerticalSpacingSM: { type: 'boolean', default: false },
		paddingTopSM: { type: 'number', default: 0 },
		paddingBottomSM: { type: 'number', default: 0 },
		overflowBottomSM: { type: 'number', default: 0 },
		overrideHorizontalSpacingSM: { type: 'boolean', default: false },
		paddingLeftSM: { type: 'number', default: 0 },
		paddingRightSM: { type: 'number', default: 0 },

		overrideVerticalSpacingXs: { type: 'boolean', default: false },
		paddingTopXs: { type: 'number', default: 0 },
		paddingBottomXs: { type: 'number', default: 0 },
		overflowBottomXs: { type: 'number', default: 0 },
		overrideHorizontalSpacingXs: { type: 'boolean', default: false },
		paddingLeftXs: { type: 'number', default: 0 },
		paddingRightXs: { type: 'number', default: 0 },

		verticalAlignment: { type: 'string', default: '' },

	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			responsiveDeviceMode,

			overrideVerticalSpacingXl,
			paddingTopXl,
			paddingBottomXl,
			overflowBottomXl,
			overrideHorizontalSpacingXl,
			paddingLeftXl,
			paddingRightXl,

			overrideVerticalSpacingLg,
			paddingTopLg,
			paddingBottomLg,
			overflowBottomLg,
			overrideHorizontalSpacingLg,
			paddingLeftLg,
			paddingRightLg,

			overrideVerticalSpacingMd,
			paddingTopMd,
			paddingBottomMd,
			overflowBottomMd,
			overrideHorizontalSpacingMd,
			paddingLeftMd,
			paddingRightMd,

			overrideVerticalSpacingSm,
			paddingTopSm,
			paddingBottomSm,
			overflowBottomSm,
			overrideHorizontalSpacingSm,
			paddingLeftSm,
			paddingRightSm,

			overrideVerticalSpacingXs,
			paddingTopXs,
			paddingBottomXs,
			overflowBottomXs,
			overrideHorizontalSpacingXs,
			paddingLeftXs,
			paddingRightXs,

			verticalAlignment

		} = attributes;

		let blockClasses = [
			className
		];

		if(overrideHorizontalSpacingXl) {
			blockClasses.push('contents-pl-xl-' + paddingLeftXl);
			blockClasses.push('contents-pr-xl-' + paddingRightXl);
		}
		if(overrideVerticalSpacingXl) {
			blockClasses.push('contents-pt-xl-' + paddingTopXl);
			blockClasses.push('contents-pb-xl-' + paddingBottomXl);
			if(overflowBottomXl > 0) blockClasses.push('contents-ob-xl-' + overflowBottomXl);
		}

		if(overrideHorizontalSpacingLg) {
			blockClasses.push('contents-pl-lg-' + paddingLeftLg);
			blockClasses.push('contents-pr-lg-' + paddingRightLg);
		}
		if(overrideVerticalSpacingLg) {
			blockClasses.push('contents-pt-lg-' + paddingTopLg);
			blockClasses.push('contents-pb-lg-' + paddingBottomLg);
			if(overflowBottomLg > 0) blockClasses.push('contents-ob-lg-' + overflowBottomLg);
		}

		if(overrideHorizontalSpacingMd) {
			blockClasses.push('contents-pl-md-' + paddingLeftMd);
			blockClasses.push('contents-pr-md-' + paddingRightMd);
		}
		if(overrideVerticalSpacingMd) {
			blockClasses.push('contents-pt-md-' + paddingTopMd);
			blockClasses.push('contents-pb-md-' + paddingBottomMd);
			if(overflowBottomMd > 0) blockClasses.push('contents-ob-md-' + overflowBottomMd);
		}

		if(overrideHorizontalSpacingSm) {
			blockClasses.push('contents-pl-sm-' + paddingLeftSm);
			blockClasses.push('contents-pr-sm-' + paddingRightSm);
		}
		if(overrideVerticalSpacingSm) {
			blockClasses.push('contents-pt-sm-' + paddingTopSm);
			blockClasses.push('contents-pb-sm-' + paddingBottomSm);
			if(overflowBottomSm > 0) blockClasses.push('contents-ob-sm-' + overflowBottomSm);
		}

		if(overrideHorizontalSpacingXs) {
			blockClasses.push('contents-pl-' + paddingLeftXs);
			blockClasses.push('contents-pr-' + paddingRightXs);
		}
		if(overrideVerticalSpacingXs) {
			blockClasses.push('contents-pt-' + paddingTopXs);
			blockClasses.push('contents-pb-' + paddingBottomXs);
			if(overflowBottomXs > 0) blockClasses.push('contents-ob-' + overflowBottomXs);
		}

		if(verticalAlignment != '') blockClasses.push('vertical-alignment-' + verticalAlignment);

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

				{ responsiveDeviceMode == 'xl' && <PanelBody title={ 'Spacing' } initialOpen={ true }>
					
					<ToggleControl
						label={ 'Override horizontal spacing' }
						checked={ overrideHorizontalSpacingXl }
						onChange={ (value) => { setAttributes({ overrideHorizontalSpacingXl: value }); } }
					/>

					{ !! overrideHorizontalSpacingXl && <RangeControl
						label="Left Padding"
						value={ paddingLeftXl }
						onChange={ (value) => setAttributes({ paddingLeftXl: value }) }
						min={ 0 }
						max={ 20 }
					/> }

					{ !! overrideHorizontalSpacingXl && <RangeControl
						label="Right Padding"
						value={ paddingRightXl }
						onChange={ (value) => setAttributes({ paddingRightXl: value }) }
						min={ 0 }
						max={ 20 }
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

					{ !! overrideVerticalSpacingXl && <RangeControl
						label="Bottom Overflow"
						value={ overflowBottomXl }
						onChange={ (value) => setAttributes({ overflowBottomXl: value }) }
						min={ 0 }
						max={ 20 }
					/> }

				</PanelBody> }

				{ responsiveDeviceMode == 'lg' && <PanelBody title={ 'Spacing' } initialOpen={ true }>
					
					<ToggleControl
						label={ 'Override horizontal spacing' }
						checked={ overrideHorizontalSpacingLg }
						onChange={ (value) => { setAttributes({ overrideHorizontalSpacingLg: value }); } }
					/>

					{ !! overrideHorizontalSpacingLg && <RangeControl
						label="Left Padding"
						value={ paddingLeftLg }
						onChange={ (value) => setAttributes({ paddingLeftLg: value }) }
						min={ 0 }
						max={ 20 }
					/> }

					{ !! overrideHorizontalSpacingLg && <RangeControl
						label="Right Padding"
						value={ paddingRightLg }
						onChange={ (value) => setAttributes({ paddingRightLg: value }) }
						min={ 0 }
						max={ 20 }
					/> }

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

					{ !! overrideVerticalSpacingLg && <RangeControl
						label="Bottom Overflow"
						value={ overflowBottomLg }
						onChange={ (value) => setAttributes({ overflowBottomLg: value }) }
						min={ 0 }
						max={ 20 }
					/> }

				</PanelBody> }

				{ responsiveDeviceMode == 'md' && <PanelBody title={ 'Spacing' } initialOpen={ true }>
					
					<ToggleControl
						label={ 'Override horizontal spacing' }
						checked={ overrideHorizontalSpacingMd }
						onChange={ (value) => { setAttributes({ overrideHorizontalSpacingMd: value }); } }
					/>

					{ !! overrideHorizontalSpacingMd && <RangeControl
						label="Left Padding"
						value={ paddingLeftMd }
						onChange={ (value) => setAttributes({ paddingLeftMd: value }) }
						min={ 0 }
						max={ 20 }
					/> }

					{ !! overrideHorizontalSpacingMd && <RangeControl
						label="Right Padding"
						value={ paddingRightMd }
						onChange={ (value) => setAttributes({ paddingRightMd: value }) }
						min={ 0 }
						max={ 20 }
					/> }

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

					{ !! overrideVerticalSpacingMd && <RangeControl
						label="Bottom Overflow"
						value={ overflowBottomMd }
						onChange={ (value) => setAttributes({ overflowBottomMd: value }) }
						min={ 0 }
						max={ 20 }
					/> }

				</PanelBody> }

				{ responsiveDeviceMode == 'sm' && <PanelBody title={ 'Spacing' } initialOpen={ true }>
					
					<ToggleControl
						label={ 'Override horizontal spacing' }
						checked={ overrideHorizontalSpacingSm }
						onChange={ (value) => { setAttributes({ overrideHorizontalSpacingSm: value }); } }
					/>

					{ !! overrideHorizontalSpacingSm && <RangeControl
						label="Left Padding"
						value={ paddingLeftSm }
						onChange={ (value) => setAttributes({ paddingLeftSm: value }) }
						min={ 0 }
						max={ 20 }
					/> }

					{ !! overrideHorizontalSpacingSm && <RangeControl
						label="Right Padding"
						value={ paddingRightSm }
						onChange={ (value) => setAttributes({ paddingRightSm: value }) }
						min={ 0 }
						max={ 20 }
					/> }

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

					{ !! overrideVerticalSpacingSm && <RangeControl
						label="Bottom Overflow"
						value={ overflowBottomSm }
						onChange={ (value) => setAttributes({ overflowBottomSm: value }) }
						min={ 0 }
						max={ 20 }
					/> }

				</PanelBody> }

				{ responsiveDeviceMode == 'xs' && <PanelBody title={ 'Spacing' } initialOpen={ true }>
					
					<ToggleControl
						label={ 'Override horizontal spacing' }
						checked={ overrideHorizontalSpacingXs }
						onChange={ (value) => { setAttributes({ overrideHorizontalSpacingXs: value }); } }
					/>

					{ !! overrideHorizontalSpacingXs && <RangeControl
						label="Left Padding"
						value={ paddingLeftXs }
						onChange={ (value) => setAttributes({ paddingLeftXs: value }) }
						min={ 0 }
						max={ 20 }
					/> }

					{ !! overrideHorizontalSpacingXs && <RangeControl
						label="Right Padding"
						value={ paddingRightXs }
						onChange={ (value) => setAttributes({ paddingRightXs: value }) }
						min={ 0 }
						max={ 20 }
					/> }

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

					{ !! overrideVerticalSpacingXs && <RangeControl
						label="Bottom Overflow"
						value={ overflowBottomXs }
						onChange={ (value) => setAttributes({ overflowBottomXs: value }) }
						min={ 0 }
						max={ 20 }
					/> }

				</PanelBody> }

				{ responsiveDeviceMode == 'xl' && <PanelBody title={ 'Vertical Alignment' } initialOpen={ true }>

					<ButtonGroup>
						<Button isPrimary={ verticalAlignment == 'top' } isSecondary={ verticalAlignment != 'top' } onClick={ (e) => setAttributes({ verticalAlignment: 'top' }) }>Top</Button>
						<Button isPrimary={ verticalAlignment == 'center' } isSecondary={ verticalAlignment != 'center' } onClick={ (e) => setAttributes({ verticalAlignment: 'center' }) }>Center</Button>
						<Button isPrimary={ verticalAlignment == 'bottom' } isSecondary={ verticalAlignment != 'bottom' } onClick={ (e) => setAttributes({ verticalAlignment: 'bottom' }) }>Bottom</Button>
					</ButtonGroup>

				</PanelBody> }

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<div className={ blockClasses.join(' ') } key="column">
					<div className="inner">

						<div className="column-contents">
							<div className="inner">

								<InnerBlocks templateLock={ false } />

							</div>
						</div>

					</div>
				</div>

			</div>

		];
	},

	
	save: ({ attributes, className }) => {
		
		const {
			
			overrideVerticalSpacingXl,
			paddingTopXl,
			paddingBottomXl,
			overflowBottomXl,
			overrideHorizontalSpacingXl,
			paddingLeftXl,
			paddingRightXl,

			overrideVerticalSpacingLg,
			paddingTopLg,
			paddingBottomLg,
			overflowBottomLg,
			overrideHorizontalSpacingLg,
			paddingLeftLg,
			paddingRightLg,

			overrideVerticalSpacingMd,
			paddingTopMd,
			paddingBottomMd,
			overflowBottomMd,
			overrideHorizontalSpacingMd,
			paddingLeftMd,
			paddingRightMd,

			overrideVerticalSpacingSm,
			paddingTopSm,
			paddingBottomSm,
			overflowBottomSm,
			overrideHorizontalSpacingSm,
			paddingLeftSm,
			paddingRightSm,

			overrideVerticalSpacingXs,
			paddingTopXs,
			paddingBottomXs,
			overflowBottomXs,
			overrideHorizontalSpacingXs,
			paddingLeftXs,
			paddingRightXs,

			verticalAlignment

		} = attributes;

		let blockClasses = [
			className
		];

		if(overrideHorizontalSpacingXl) {
			blockClasses.push('contents-pl-xl-' + paddingLeftXl);
			blockClasses.push('contents-pr-xl-' + paddingRightXl);
		}
		if(overrideVerticalSpacingXl) {
			blockClasses.push('contents-pt-xl-' + paddingTopXl);
			blockClasses.push('contents-pb-xl-' + paddingBottomXl);
			if(overflowBottomXl > 0) blockClasses.push('contents-ob-xl-' + overflowBottomXl);
		}

		if(overrideHorizontalSpacingLg) {
			blockClasses.push('contents-pl-lg-' + paddingLeftLg);
			blockClasses.push('contents-pr-lg-' + paddingRightLg);
		}
		if(overrideVerticalSpacingLg) {
			blockClasses.push('contents-pt-lg-' + paddingTopLg);
			blockClasses.push('contents-pb-lg-' + paddingBottomLg);
			if(overflowBottomLg > 0) blockClasses.push('contents-ob-lg-' + overflowBottomLg);
		}

		if(overrideHorizontalSpacingMd) {
			blockClasses.push('contents-pl-md-' + paddingLeftMd);
			blockClasses.push('contents-pr-md-' + paddingRightMd);
		}
		if(overrideVerticalSpacingMd) {
			blockClasses.push('contents-pt-md-' + paddingTopMd);
			blockClasses.push('contents-pb-md-' + paddingBottomMd);
			if(overflowBottomMd > 0) blockClasses.push('contents-ob-md-' + overflowBottomMd);
		}

		if(overrideHorizontalSpacingSm) {
			blockClasses.push('contents-pl-sm-' + paddingLeftSm);
			blockClasses.push('contents-pr-sm-' + paddingRightSm);
		}
		if(overrideVerticalSpacingSm) {
			blockClasses.push('contents-pt-sm-' + paddingTopSm);
			blockClasses.push('contents-pb-sm-' + paddingBottomSm);
			if(overflowBottomSm > 0) blockClasses.push('contents-ob-sm-' + overflowBottomSm);
		}

		if(overrideHorizontalSpacingXs) {
			blockClasses.push('contents-pl-' + paddingLeftXs);
			blockClasses.push('contents-pr-' + paddingRightXs);
		}
		if(overrideVerticalSpacingXs) {
			blockClasses.push('contents-pt-' + paddingTopXs);
			blockClasses.push('contents-pb-' + paddingBottomXs);
			if(overflowBottomXs > 0) blockClasses.push('contents-ob-' + overflowBottomXs);
		}

		if(verticalAlignment != '') blockClasses.push('vertical-alignment-' + verticalAlignment);

		return (

			<div className={ blockClasses.join(' ') } key="column">
				<div className="inner">

					<div className="column-contents">
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
				verticalAlignment: { type: 'string', default: '' }
			},

			save: ({ attributes, className }) => {
				
				const {
					verticalAlignment
				} = attributes;

				let blockClasses = [
					className
				];

				if(verticalAlignment != '') blockClasses.push('vertical-alignment-' + verticalAlignment);

				return (

					<div className={ blockClasses.join(' ') } key="column">
						<div className="inner">

							<div className="column-contents">
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
