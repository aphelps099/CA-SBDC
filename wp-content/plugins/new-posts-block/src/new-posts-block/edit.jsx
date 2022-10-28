import pickBy from 'lodash/pickBy';
import isUndefined from 'lodash/isUndefined';
import uniqBy from 'lodash/uniqBy';
import classNames from 'classnames';

const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const { PanelBody, Placeholder, RangeControl, Spinner, SelectControl, TextControl, ToggleControl, BaseControl, FormTokenField } = wp.components;
const { InspectorControls } = wp.editor;
const { withSelect } = wp.data;
const { __experimentalGetSettings, dateI18n, getDate } = wp.date;

class NewPostsEdit extends Component {
	constructor() {
		super( ...arguments );
		this.toggleDisplayNewMark = this.toggleDisplayNewMark.bind( this );
		this.changedExcludePosts = this.changedExcludePosts.bind( this );
	}

	toggleDisplayNewMark() {
		const { displayNewMark } = this.props.attributes;
		const { setAttributes } = this.props;

		setAttributes( { displayNewMark: ! displayNewMark } );
	}

	changedExcludePosts( newExcludePosts ) {
		const { setAttributes } = this.props;
		const uniquePosts = uniqBy( newExcludePosts.map( e => parseInt( e, 10 ) ).filter( r => !isNaN( r ) ) );
		const postsString = uniquePosts.join( ',' );

		setAttributes( { excludePosts: postsString } );
	}

	render() {
		const { attributes, setAttributes, posts, postTypes } = this.props;
		const { postTypeSlug, orderBy, postsToShow, newMarkDays, newMarkText, displayNewMark, excludePosts, dateWidth } = attributes;

		const inspectorControls = (
			<InspectorControls>
				<PanelBody title={ __( 'New Posts Settings', 'new-posts-block' ) }>
					<SelectControl
						label={ __( 'Post Type', 'new-posts-block' ) }
						value={ postTypeSlug }
						options={ postTypes.map( type => ( { label: type.name, value: type.slug } ) ) }
						onChange={ ( value ) => { setAttributes( { postTypeSlug: value } ) } }
					/>
					<SelectControl
						label={ __( 'Sort order', 'new-posts-block' ) }
						value={ orderBy }
						options={[
							{ label: __( 'Public date', 'new-posts-block' ), value: 'date' },
							{ label: __( 'Update date', 'new-posts-block' ), value: 'modified' },
						]}
						onChange={ ( value ) => { setAttributes( { orderBy: value } ) } }
					/>
					<RangeControl
						label={ __( 'Number of items', 'new-posts-block' ) }
						onChange={ ( value ) => { setAttributes( { postsToShow: value } ) } }
						value={ postsToShow }
						min={ 1 }
						max={ 100 }
					/>
					<RangeControl
						label={ __( 'Number of days for new marks', 'new-posts-block' ) }
						onChange={ ( value ) => { setAttributes( { newMarkDays: value } ) } }
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
					<BaseControl>
						<FormTokenField
							label={ __( 'Post IDs to exclude', 'new-posts-block' ) }
							value={ excludePosts ? excludePosts.split( ',' ).map( e => parseInt( e, 10 ) ) : [] }
							onChange={ this.changedExcludePosts }
							maxSuggestions={ 20 }
						/>
					</BaseControl>
				</PanelBody>
			</InspectorControls>
		);

		const hasPosts = Array.isArray( posts ) && posts.length;
		if ( ! hasPosts ) {
			return (
				<Fragment>
					{ inspectorControls }
					<Placeholder
						icon = 'admin-post'
						label = { __( 'New Posts', 'new-posts-block' ) }
					>
						{ ! Array.isArray( posts ) ?
							<Spinner /> :
							__( 'No posts found.', 'new-posts-block' )
						}
					</Placeholder>
				</Fragment>
			);
		}

		const displayPosts = posts.length > postsToShow ? posts.slice( 0, postsToShow ) : posts;
		const settings = __experimentalGetSettings();
		const nowUnix = Number( getDate() );
		return (
			<Fragment>
				{ inspectorControls }
				<ul className={ classNames( this.props.className, 'new-posts' ) }>
				{ displayPosts.map( ( post ) => {
					const titleTrimmed = post.title.rendered.trim();
					const postDateUnix = Number( getDate( orderBy === 'date' ? post.date : post.modified ) );
					const diffDay = ( nowUnix - postDateUnix ) / 86400000;
					return (
						<li>
							<span className="post-date">
								{ dateI18n( settings.formats.date, ( orderBy === 'date' ? post.date : post.modified ) ) }
							</span>
							<div className="post-data">
								{ displayNewMark && newMarkDays > diffDay &&
									<span className={ 'new-mark' }>{ newMarkText }</span>
								}
								<span className="post-title">
									<a href={ post.link } target="_blank" rel="noreferrer noopener">
										{ titleTrimmed }
									</a>
								</span>
							</div>
						</li>
					);
				} ) }
				</ul>
			</Fragment>
		);
	}
}

export default withSelect( ( select, props ) => {
	const { getEntityRecords, getPostTypes } = select( 'core' );
	const { postTypeSlug, orderBy, postsToShow, excludePosts } = props.attributes;
	const postTypes = getPostTypes( { per_page: -1 } ) || [];
	const NewPostsQuery = pickBy( {
		order: 'desc',
		orderby: orderBy,
		per_page: postsToShow,
		exclude: excludePosts,
	}, ( value ) => ! isUndefined( value ) );
	return {
		posts: getEntityRecords( 'postType', postTypeSlug, NewPostsQuery ) || [],
		postTypes: postTypes.filter( postType => postType.viewable ).filter( postType => postType.rest_base !== 'media' ),
	};
} ) ( NewPostsEdit );
