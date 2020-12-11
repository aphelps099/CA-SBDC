
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

const ALLOWED_BLOCKS = [ 'crown-blocks/column' ];


registerBlockType('crown-blocks/multi-column', {
	title: 'Multi-Column Layout',
	icon: 'columns',
	category: 'layout',
	keywords: [ 'column', 'sidebar', 'crown-blocks' ],

	supports: {},

	attributes: {
		responsiveDeviceMode: { type: 'string', default: 'xl' },
		columnBreakpoint: { type: 'string', default: 'md' },
		columnCount: { type: 'number', default: 2 },

		columnLayoutXl: { type: 'number', default: 6 },
		columnSpacingXl: { type: 'number', default: 2 },

		overrideColumnLayoutLg: { type: 'boolean', default: false },
		columnLayoutLg: { type: 'number', default: 6 },
		columnSpacingLg: { type: 'number', default: 2 },

		overrideColumnLayoutMd: { type: 'boolean', default: false },
		columnLayoutMd: { type: 'number', default: 6 },
		columnSpacingMd: { type: 'number', default: 2 },

		overrideColumnLayoutSm: { type: 'boolean', default: false },
		columnLayoutSm: { type: 'number', default: 6 },
		columnSpacingSm: { type: 'number', default: 2 },

		overrideColumnLayoutXs: { type: 'boolean', default: false },
		columnLayoutXs: { type: 'number', default: 6 },
		columnSpacingXs: { type: 'number', default: 2 }

	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const TEMPLATE = [
			[ 'crown-blocks/column', {}, [
				[ 'core/paragraph', { placeholder: 'Enter column content...' } ]
			] ],
			[ 'crown-blocks/column', {}, [
				[ 'core/paragraph', { placeholder: 'Enter column content...' } ]
			] ]
		];

		const {
			responsiveDeviceMode,
			columnBreakpoint,
			columnCount,
			
			columnLayoutXl,
			columnSpacingXl,

			overrideColumnLayoutLg,
			columnLayoutLg,
			columnSpacingLg,

			overrideColumnLayoutMd,
			columnLayoutMd,
			columnSpacingMd,

			overrideColumnLayoutSm,
			columnLayoutSm,
			columnSpacingSm,

			overrideColumnLayoutXs,
			columnLayoutXs,
			columnSpacingXs

		} = attributes;

		let blockClasses = [
			className
		];

		blockClasses.push('column-breakpoint-' + columnBreakpoint);
		blockClasses.push('column-count-' + columnCount);

		let defaultLayoutBrakpoint = columnBreakpoint;
		if([ 'xs' ].includes(columnBreakpoint) && overrideColumnLayoutXs) {
			defaultLayoutBrakpoint = 'sm';
			blockClasses.push('column-layout-xs-' + columnLayoutXs);
			blockClasses.push('column-spacing-xs-' + columnSpacingXs);
		}
		if([ 'xs', 'sm' ].includes(columnBreakpoint) && overrideColumnLayoutSm) {
			defaultLayoutBrakpoint = 'md';
			blockClasses.push('column-layout-sm-' + columnLayoutSm);
			blockClasses.push('column-spacing-sm-' + columnSpacingSm);
		}
		if([ 'xs', 'sm', 'md' ].includes(columnBreakpoint) && overrideColumnLayoutMd) {
			defaultLayoutBrakpoint = 'lg';
			blockClasses.push('column-layout-md-' + columnLayoutMd);
			blockClasses.push('column-spacing-md-' + columnSpacingMd);
		}
		if([ 'xs', 'sm', 'md', 'lg' ].includes(columnBreakpoint) && overrideColumnLayoutLg) {
			defaultLayoutBrakpoint = 'xl';
			blockClasses.push('column-layout-lg-' + columnLayoutLg);
			blockClasses.push('column-spacing-lg-' + columnSpacingLg);
		}
		blockClasses.push('column-layout-' + defaultLayoutBrakpoint + '-' + columnLayoutXl);
		blockClasses.push('column-spacing-' + defaultLayoutBrakpoint + '-' + columnSpacingXl);

		return [

			<InspectorControls key="inspector-controls">

				<PanelBody title={ 'Column Layout Breakpoint' } initialOpen={ true }>

					<SelectControl
						value={ columnBreakpoint }
						onChange={ (value) => setAttributes({ columnBreakpoint: value, responsiveDeviceMode: 'xl' }) }
						options={ [
							{ label: 'Mobile - Portrait (Base)', value: 'xs' },
							{ label: 'Mobile - Landscape (576px)', value: 'sm' },
							{ label: 'Tablet - Portrait (768px)', value: 'md' },
							{ label: 'Tablet - Landscape (992px)', value: 'lg' },
							{ label: 'Desktop - Widescreen (1200px)', value: 'xl' }
						] }
					/>

				</PanelBody>

				{ [ 'xs', 'sm', 'md', 'lg' ].includes(columnBreakpoint) && <div class="crown-blocks-responsive-device-mode-toggles">
					<ButtonGroup>
						{ [ 'xs', 'sm', 'md', 'lg', 'xl' ].includes(columnBreakpoint) && <Button isPrimary={ responsiveDeviceMode == 'xl' } onClick={ (e) => setAttributes({ responsiveDeviceMode: 'xl' }) }>
							<span class="inner">
								<Icon icon={
									<svg class="bi bi-display" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
										<path d="M5.75 13.5c.167-.333.25-.833.25-1.5h4c0 .667.083 1.167.25 1.5H11a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1h.75z"/>
										<path fill-rule="evenodd" d="M13.991 3H2c-.325 0-.502.078-.602.145a.758.758 0 0 0-.254.302A1.46 1.46 0 0 0 1 4.01V10c0 .325.078.502.145.602.07.105.17.188.302.254a1.464 1.464 0 0 0 .538.143L2.01 11H14c.325 0 .502-.078.602-.145a.758.758 0 0 0 .254-.302 1.464 1.464 0 0 0 .143-.538L15 9.99V4c0-.325-.078-.502-.145-.602a.757.757 0 0 0-.302-.254A1.46 1.46 0 0 0 13.99 3zM14 2H2C0 2 0 4 0 4v6c0 2 2 2 2 2h12c2 0 2-2 2-2V4c0-2-2-2-2-2z"/>
									</svg>
								} />
								<span class="label">Desktop <span class="sub-label">Widescreen</span> <span class="sub-label">1200px</span></span>
							</span>
						</Button> }
						{ [ 'xs', 'sm', 'md', 'lg' ].includes(columnBreakpoint) && <Button isPrimary={ responsiveDeviceMode == 'lg' } onClick={ (e) => setAttributes({ responsiveDeviceMode: 'lg' }) }>
							<span class="inner">
								<Icon icon={
									<svg class="bi bi-tablet-landscape" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd" d="M1 4v8a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1zm-1 8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H2a2 2 0 0 0-2 2v8z"/>
										<path fill-rule="evenodd" d="M14 8a1 1 0 1 0-2 0 1 1 0 0 0 2 0z"/>
									</svg>
								} />
								<span class="label">Tablet <span class="sub-label">Landscape</span> <span class="sub-label">992px</span></span>
							</span>
						</Button> }
						{ [ 'xs', 'sm', 'md' ].includes(columnBreakpoint) && <Button isPrimary={ responsiveDeviceMode == 'md' } onClick={ (e) => setAttributes({ responsiveDeviceMode: 'md' }) }>
							<span class="inner">
								<Icon icon={
									<svg class="bi bi-tablet" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd" d="M12 1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H4z"/>
										<path fill-rule="evenodd" d="M8 14a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
									</svg>
								} />
								<span class="label">Tablet <span class="sub-label">Portrait</span> <span class="sub-label">768px</span></span>
							</span>
						</Button> }
						{ [ 'xs', 'sm' ].includes(columnBreakpoint) && <Button isPrimary={ responsiveDeviceMode == 'sm' } onClick={ (e) => setAttributes({ responsiveDeviceMode: 'sm' }) }>
							<span class="inner">
								<Icon icon={
									<svg class="bi bi-phone-landscape" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd" d="M1 4.5v6a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-6a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1zm-1 6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H2a2 2 0 0 0-2 2v6z"/>
										<path fill-rule="evenodd" d="M14 7.5a1 1 0 1 0-2 0 1 1 0 0 0 2 0z"/>
									</svg>
								} />
								<span class="label">Mobile <span class="sub-label">Landscape</span> <span class="sub-label">576px</span></span>
							</span>
						</Button> }
						{ [ 'xs' ].includes(columnBreakpoint) && <Button isPrimary={ responsiveDeviceMode == 'xs' } onClick={ (e) => setAttributes({ responsiveDeviceMode: 'xs' }) }>
							<span class="inner">
								<Icon icon={
									<svg class="bi bi-phone" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd" d="M11 1H5a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM5 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H5z"/>
										<path fill-rule="evenodd" d="M8 14a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
									</svg>
								} />
								<span class="label">Mobile <span class="sub-label">Portrait</span> <span class="sub-label">Base</span></span>
							</span>
						</Button> }
					</ButtonGroup>
				</div> }

				<PanelBody title={ 'Layout' } initialOpen={ true }>

					{ ([ 'xs', 'sm', 'md', 'lg', 'xl' ].includes(columnBreakpoint) && responsiveDeviceMode == 'xl') && <div>

						<RangeControl
							label="Column Distribution"
							value={ columnLayoutXl }
							onChange={ (value) => setAttributes({ columnLayoutXl: value }) }
							min={ 1 }
							max={ 11 }
							showTooltip={ true }
							withInputField={ false }
							marks={ [
								{ value: 2, label: '1:3' },
								{ value: 3, label: '1:2' },
								{ value: 5, label: '1:1' },
								{ value: 7, label: '2:1' },
								{ value: 8, label: '3:1' }
							] }
							renderTooltipContent={ (value) => {
								let fraction = CrownBlocks.reduce(parseInt(value), 12)
								return fraction[0] + ':' + (fraction[1] - fraction[0]);
							} }
						/>

						<RangeControl
							label="Column Spacing"
							value={ columnSpacingXl }
							onChange={ (value) => setAttributes({ columnSpacingXl: value }) }
							min={ 0 }
							max={ 16 }
							step={ 1 }
							withInputField={ false }
							marks={ [{ value: 0, label: '0' }, { value: 2, label: '2' }, { value: 4, label: '4' }, { value: 6, label: '6' }, { value: 8, label: '8' }, { value: 10, label: '10' }, { value: 12, label: '12' }, { value: 14, label: '14' }, { value: 16, label: '16' }] }
						/>

					</div> }

					{ ([ 'xs', 'sm', 'md', 'lg' ].includes(columnBreakpoint) && responsiveDeviceMode == 'lg') && <div>

						<ToggleControl
							label={ 'Override layout' }
							checked={ overrideColumnLayoutLg }
							onChange={ (value) => { setAttributes({ overrideColumnLayoutLg: value }); } }
						/>

						{ !! overrideColumnLayoutLg && <RangeControl
							label="Column Distribution"
							value={ columnLayoutLg }
							onChange={ (value) => setAttributes({ columnLayoutLg: value }) }
							min={ 1 }
							max={ 11 }
						/> }

						{ !! overrideColumnLayoutLg && <RangeControl
							label="Column Spacing"
							value={ columnSpacingLg }
							onChange={ (value) => setAttributes({ columnSpacingLg: value }) }
							min={ 0 }
							max={ 16 }
							step={ 1 }
						/> }
						
					</div> }

					{ ([ 'xs', 'sm', 'md' ].includes(columnBreakpoint) && responsiveDeviceMode == 'md') && <div>

						<ToggleControl
							label={ 'Override layout' }
							checked={ overrideColumnLayoutMd }
							onChange={ (value) => { setAttributes({ overrideColumnLayoutMd: value }); } }
						/>

						{ !! overrideColumnLayoutMd && <RangeControl
							label="Column Distribution"
							value={ columnLayoutMd }
							onChange={ (value) => setAttributes({ columnLayoutMd: value }) }
							min={ 1 }
							max={ 11 }
						/> }

						{ !! overrideColumnLayoutMd && <RangeControl
							label="Column Spacing"
							value={ columnSpacingMd }
							onChange={ (value) => setAttributes({ columnSpacingMd: value }) }
							min={ 0 }
							max={ 16 }
							step={ 1 }
						/> }
						
					</div> }

					{ ([ 'xs', 'sm' ].includes(columnBreakpoint) && responsiveDeviceMode == 'sm') && <div>

						<ToggleControl
							label={ 'Override layout' }
							checked={ overrideColumnLayoutSm }
							onChange={ (value) => { setAttributes({ overrideColumnLayoutSm: value }); } }
						/>

						{ !! overrideColumnLayoutSm && <RangeControl
							label="Column Distribution"
							value={ columnLayoutSm }
							onChange={ (value) => setAttributes({ columnLayoutSm: value }) }
							min={ 1 }
							max={ 11 }
						/> }

						{ !! overrideColumnLayoutSm && <RangeControl
							label="Column Spacing"
							value={ columnSpacingSm }
							onChange={ (value) => setAttributes({ columnSpacingSm: value }) }
							min={ 0 }
							max={ 16 }
							step={ 1 }
						/> }
						
					</div> }

					{ ([ 'xs' ].includes(columnBreakpoint) && responsiveDeviceMode == 'xs') && <div>

						<ToggleControl
							label={ 'Override layout' }
							checked={ overrideColumnLayoutXs }
							onChange={ (value) => { setAttributes({ overrideColumnLayoutXs: value }); } }
						/>

						{ !! overrideColumnLayoutXs && <RangeControl
							label="Column Distribution"
							value={ columnLayoutXs }
							onChange={ (value) => setAttributes({ columnLayoutXs: value }) }
							min={ 1 }
							max={ 11 }
						/> }

						{ !! overrideColumnLayoutXs && <RangeControl
							label="Column Spacing"
							value={ columnSpacingXs }
							onChange={ (value) => setAttributes({ columnSpacingXs: value }) }
							min={ 0 }
							max={ 16 }
							step={ 1 }
						/> }
						
					</div> }

				</PanelBody>

			</InspectorControls>,

			<div class="crown-block-editor-container">

				<div className={ blockClasses.join(' ') } key="multi-column">
					<div className="inner">

						<div className="multi-column-columns">
							<div className="inner">

								<InnerBlocks allowedBlocks={ ALLOWED_BLOCKS } template={ TEMPLATE } orientation="horizontal" templateLock="insert" />

							</div>
						</div>

					</div>
				</div>

			</div>

		];
	},

	
	save: ({ attributes, className }) => {
		
		const {
			columnBreakpoint,
			columnCount,
			
			columnLayoutXl,
			columnSpacingXl,

			overrideColumnLayoutLg,
			columnLayoutLg,
			columnSpacingLg,

			overrideColumnLayoutMd,
			columnLayoutMd,
			columnSpacingMd,

			overrideColumnLayoutSm,
			columnLayoutSm,
			columnSpacingSm,

			overrideColumnLayoutXs,
			columnLayoutXs,
			columnSpacingXs

		} = attributes;

		let blockClasses = [
			className
		];

		blockClasses.push('column-breakpoint-' + columnBreakpoint);
		blockClasses.push('column-count-' + columnCount);

		let defaultLayoutBrakpoint = columnBreakpoint;
		if([ 'xs' ].includes(columnBreakpoint) && overrideColumnLayoutXs) {
			defaultLayoutBrakpoint = 'sm';
			blockClasses.push('column-layout-xs-' + columnLayoutXs);
			blockClasses.push('column-spacing-xs-' + columnSpacingXs);
		}
		if([ 'xs', 'sm' ].includes(columnBreakpoint) && overrideColumnLayoutSm) {
			defaultLayoutBrakpoint = 'md';
			blockClasses.push('column-layout-sm-' + columnLayoutSm);
			blockClasses.push('column-spacing-sm-' + columnSpacingSm);
		}
		if([ 'xs', 'sm', 'md' ].includes(columnBreakpoint) && overrideColumnLayoutMd) {
			defaultLayoutBrakpoint = 'lg';
			blockClasses.push('column-layout-md-' + columnLayoutMd);
			blockClasses.push('column-spacing-md-' + columnSpacingMd);
		}
		if([ 'xs', 'sm', 'md', 'lg' ].includes(columnBreakpoint) && overrideColumnLayoutLg) {
			defaultLayoutBrakpoint = 'xl';
			blockClasses.push('column-layout-lg-' + columnLayoutLg);
			blockClasses.push('column-spacing-lg-' + columnSpacingLg);
		}
		blockClasses.push('column-layout-' + defaultLayoutBrakpoint + '-' + columnLayoutXl);
		blockClasses.push('column-spacing-' + defaultLayoutBrakpoint + '-' + columnSpacingXl);

		return (

			<div className={ blockClasses.join(' ') } key="multi-column">
				<div className="inner">

					<div className="multi-column-columns">
						<div className="inner">

							<InnerBlocks.Content />

						</div>
					</div>

				</div>
			</div>

		);
	},


} );
