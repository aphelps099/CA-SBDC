
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

const ALLOWED_BLOCKS = [ 'core/paragraph' ];

const TEMPLATE = [
	[ 'core/paragraph', {}, [] ]
];


registerBlockType('crown-blocks/testimonial', {
	title: 'Testimonial',
	icon: 'format-quote',
	category: 'widgets',
	keywords: [ 'quote', 'pullquote', 'crown-blocks' ],

	supports: {},

	attributes: {
		featuredImageId: { type: 'number' },
		featuredImageData: { type: 'object' },
		featuredImageFocalPoint: { type: 'object', default: { x: 0.5, y: 0.5 } },
		title: { selector: '.testimonial-title', source: 'children' },
		source: { selector: '.testimonial-source', source: 'children' }
	},


	edit: ({ attributes, className, isSelected, setAttributes }) => {

		const {
			featuredImageId,
			featuredImageData,
			featuredImageFocalPoint,
			title,
			source
		} = attributes;

		let blockClasses = [
			className
		];

		let featuredImageUrl = null;
		if(featuredImageId) {
			featuredImageUrl = featuredImageData.sizes.medium_large ? featuredImageData.sizes.medium_large.url : featuredImageData.url;
			blockClasses.push('has-featured-image');
		}

		return [

			<InspectorControls key="inspector-controls">

				<PanelBody title={ 'Featured Image' } className={ 'crown-blocks-featured-image' } initialOpen={ true }>

					{ !! featuredImageId && <FocalPointPicker 
						label="Focal Point"
						url={ featuredImageData.sizes.medium ? featuredImageData.sizes.medium.url : featuredImageData.sizes.thumbnail.url }
						dimensions={ { width: 400, height: 100 } }
						value={ featuredImageFocalPoint }
						onChange={ (value) => setAttributes({ featuredImageFocalPoint: value }) } 
					/> }

					<MediaUpload
						onSelect={ (media) => { setAttributes({ featuredImageId: media.id, featuredImageData: media, featuredImageFocalPoint: { x: 0.5, y: 0.5 } }); } }
						type="image"
						value={ featuredImageId }
						render={ ({ open }) => (
							<div className={ 'crown-blocks-media-upload' }>
								{/* { featuredImageId && <Button className={ 'image-preview' } onClick={ open }><img src={ featuredImageData.sizes.medium ? featuredImageData.sizes.medium.url : featuredImageData.sizes.thumbnail.url } /></Button> } */}
								<Button className={ 'button' } onClick={ open }>Select Image</Button>
								{ featuredImageId && <Button className={ 'button is-link is-destructive' } onClick={ (e) => { setAttributes({ featuredImageId: null, featuredImageData: null }); } }>Remove Image</Button> }
							</div>
						) }
					/>

				</PanelBody>

			</InspectorControls>,

			<blockquote class="crown-block-editor-container">

				<div className={ blockClasses.join(' ') } key="testimonial">
					<div className="inner">

						<div className="testimonial-featured-image">
							<div class="inner">
								{ featuredImageUrl && <div className={ 'image' } style={ {
									backgroundImage: 'url(' + featuredImageUrl + ')',
									backgroundPosition: `${ featuredImageFocalPoint.x * 100 }% ${ featuredImageFocalPoint.y * 100 }%`,
								} }>
									<img src={ featuredImageUrl } />
								</div> }
							</div>
						</div>

						<div className="testimonial-contents">
							<div className="inner">

								<RichText
									tagName="h3"
									className="testimonial-title"
									onChange={ (value) => setAttributes({ title: value }) } 
									value={ title }
									placeholder="Optional Title"
									allowedFormats={ [] }
								/>

								<div class="testimonial-quote">
									<div className="inner">
										<InnerBlocks allowedBlocks={ ALLOWED_BLOCKS } template={ TEMPLATE } />
									</div>
								</div>

								<cite class="testimonial-source-container">

									{ featuredImageUrl && <div className={ 'image' } style={ {
										backgroundImage: 'url(' + featuredImageUrl + ')',
										backgroundPosition: `${ featuredImageFocalPoint.x * 100 }% ${ featuredImageFocalPoint.y * 100 }%`,
									} }>
										<img src={ featuredImageUrl } />
									</div> }

									<RichText
										tagName="div"
										className="testimonial-source"
										onChange={ (value) => setAttributes({ source: value }) } 
										value={ source }
										placeholder="Optional Source"
										allowedFormats={ [ 'core/bold' ] }
									/>

								</cite>

							</div>
						</div>

					</div>
				</div>

			</blockquote>

		];
	},

	
	save: ({ attributes, className }) => {
		
		const {
			featuredImageId,
			featuredImageData,
			featuredImageFocalPoint,
			title,
			source
		} = attributes;

		let blockClasses = [
			className
		];

		let featuredImageUrl = null;
		if(featuredImageId) {
			featuredImageUrl = featuredImageData.sizes.medium_large ? featuredImageData.sizes.medium_large.url : featuredImageData.url;
			blockClasses.push('has-featured-image');
		}

		return (

			<blockquote className={ blockClasses.join(' ') } key="testimonial">
				<div className="inner">

					<div className="testimonial-featured-image">
						<div class="inner">
							{ featuredImageUrl && <div className={ 'image' } style={ {
								backgroundImage: 'url(' + featuredImageUrl + ')',
								backgroundPosition: `${ featuredImageFocalPoint.x * 100 }% ${ featuredImageFocalPoint.y * 100 }%`,
							} }>
								<img src={ featuredImageUrl } />
							</div> }
						</div>
					</div>

					<div className="testimonial-contents">
						<div className="inner">

							{ title != '' && <RichText.Content tagName="h3" className="testimonial-title" value={ title } /> }

							<div class="testimonial-quote">
								<div className="inner">
									<InnerBlocks.Content />
								</div>
							</div>

							{ source != '' && <cite class="testimonial-source-container">

								{ featuredImageUrl && <div className={ 'image' } style={ {
									backgroundImage: 'url(' + featuredImageUrl + ')',
									backgroundPosition: `${ featuredImageFocalPoint.x * 100 }% ${ featuredImageFocalPoint.y * 100 }%`,
								} }>
									<img src={ featuredImageUrl } />
								</div> }

								<RichText.Content tagName="div" className="testimonial-source" value={ source } />
								
							</cite> }

						</div>
					</div>

				</div>
			</blockquote>

		);
	},

	deprecated: [

		{
			attributes: {
				featuredImageId: { type: 'number' },
				featuredImageData: { type: 'object' },
				featuredImageFocalPoint: { type: 'object', default: { x: 0.5, y: 0.5 } },
				title: { selector: '.testimonial-title', source: 'children' },
				source: { selector: '.testimonial-source', source: 'children' }
			},
			
			save: ({ attributes, className }) => {
				
				const {
					featuredImageId,
					featuredImageData,
					featuredImageFocalPoint,
					title,
					source
				} = attributes;
		
				let blockClasses = [
					className
				];
		
				let featuredImageUrl = null;
				if(featuredImageId) {
					featuredImageUrl = featuredImageData.sizes.medium_large ? featuredImageData.sizes.medium_large.url : featuredImageData.url;
					blockClasses.push('has-featured-image');
				}
		
				return (
		
					<blockquote className={ blockClasses.join(' ') } key="testimonial">
						<div className="inner">
		
							<div className="testimonial-featured-image">
								<div class="inner">
									{ featuredImageUrl && <div className={ 'image' } style={ {
										backgroundImage: 'url(' + featuredImageUrl + ')',
										backgroundPosition: `${ featuredImageFocalPoint.x * 100 }% ${ featuredImageFocalPoint.y * 100 }%`,
									} }>
										<img src={ featuredImageUrl } />
									</div> }
								</div>
							</div>
		
							<div className="testimonial-contents">
								<div className="inner">
		
									{ title != '' && <RichText.Content tagName="h3" className="testimonial-title" value={ title } /> }
		
									<div class="testimonial-quote">
										<div className="inner">
											<InnerBlocks.Content />
										</div>
									</div>
		
									{ source != '' && <RichText.Content tagName="cite" className="testimonial-source" value={ source } /> }
		
								</div>
							</div>
		
						</div>
					</blockquote>
		
				);
			}
		}

	]


} );
