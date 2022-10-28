import './style.scss';
import './editor.scss';

import edit from './edit';
import icon from './icon';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

registerBlockType( 'new-posts-block/new-posts', {
	title: __( 'New Posts', 'new-posts-block' ),
	icon,
	category: 'widgets',
	description: __( 'Display a list of new posts.', 'new-posts-block' ),
	keywords: [ __( 'new posts', 'new-posts-block' ), __( 'recent posts', 'new-posts-block' ) ],
	supports: {
		align: true,
		html: false,
	},
	attributes: {
		postTypeSlug: {
			type: 'string',
			default: 'post',
		},
		orderBy: {
			type: 'string',
			default: 'date',
		},
		postsToShow: {
			type: 'number',
			default: 5,
		},
		newMarkDays: {
			type: 'number',
			default: 30,
		},
		newMarkText: {
			type: 'string',
			default: 'NEW!',
		},
		displayNewMark: {
			type: 'boolean',
			default: true,
		},
		excludePosts: {
			type: 'string',
		},
	},
	edit,
	save() {
		return null;
	}
});
