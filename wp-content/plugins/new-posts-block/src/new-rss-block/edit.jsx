const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const { PanelBody, Placeholder, RangeControl, Button, Disabled, TextControl, ToggleControl, Toolbar, ServerSideRender } = wp.components;
const { BlockControls, InspectorControls } = wp.editor;

class NewRSSEdit extends Component {
	constructor() {
		super( ...arguments );

		this.state = {
			editing: ! this.props.attributes.feedURL,
		};

		this.toggleDisplayNewMark = this.toggleDisplayNewMark.bind( this );
		this.onSubmitURL = this.onSubmitURL.bind( this );
	}

	toggleDisplayNewMark() {
		const { displayNewMark } = this.props.attributes;
		const { setAttributes } = this.props;

		setAttributes( { displayNewMark: ! displayNewMark } );
	}

	onSubmitURL( event ) {
		event.preventDefault();

		const { feedURL } = this.props.attributes;
		if ( feedURL ) {
			this.setState( { editing: false } );
		}
	}

	render() {
		const { attributes, setAttributes } = this.props;
		const { feedURL, postsToShow, newMarkDays, newMarkText, displayNewMark } = attributes;

		if ( this.state.editing ) {
			return (
				<Placeholder
					icon="rss"
					label="RSS"
				>
					<form onSubmit={ this.onSubmitURL }>
						<TextControl
							placeholder={ __( 'Enter URL here…', 'new-posts-block' ) }
							value={ feedURL }
							onChange={ ( value ) => setAttributes( { feedURL: value } ) }
							className={ 'components-placeholder__input' }
						/>
						<Button isLarge type="submit">
							{ __( 'Use URL', 'new-posts-block' ) }
						</Button>
					</form>
				</Placeholder>
			);
		}

		const toolbarControls = [
			{
				icon: 'edit',
				title: __( 'Edit RSS URL', 'new-posts-block' ),
				onClick: () => this.setState( { editing: true } ),
			},
		];

		const inspectorControls = (
			<InspectorControls>
				<PanelBody title={ __( 'New Posts Settings', 'new-posts-block' ) }>
					<RangeControl
						label={ __( 'Number of items', 'new-posts-block' ) }
						onChange={ ( value ) => { setAttributes( { postsToShow: value } ) }}
						value={ postsToShow }
						min={ 1 }
						max={ 10 }
					/>
					<RangeControl
						label={ __( 'Number of days for new marks', 'new-posts-block' ) }
						onChange={ ( value ) => { setAttributes( { newMarkDays: value } ) }}
						value={ newMarkDays }
						min={ 0 }
						max={ 100 }
					/>
					<TextControl
						label={ __( 'New mark text', 'new-posts-block' ) }
						value={ newMarkText }
						onChange={ ( value ) => { setAttributes( { newMarkText: value } ) } }
					/>
					<ToggleControl
						label={ __( 'Display new mark', 'new-posts-block' ) }
						checked={ displayNewMark }
						onChange={ this.toggleDisplayNewMark }
					/>
				</PanelBody>
			</InspectorControls>
		);

		return (
			<Fragment>
				<BlockControls>
					<Toolbar controls={ toolbarControls } />
				</BlockControls>
				{ inspectorControls }
				<Disabled>
					<ServerSideRender
						block="new-posts-block/new-rss"
						attributes={ this.props.attributes }
					/>
				</Disabled>
			</Fragment>
		);
	}
}

export default NewRSSEdit;
