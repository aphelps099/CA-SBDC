import './style.scss';
import './editor.scss';

import edit from './edit';
import icon from './icon';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

registerBlockType( 'new-posts-block/new-rss', {
	title: __( 'New RSS', 'new-posts-block' ),
	icon,
	category: 'widgets',
	description: __( 'Display entries from any RSS or Atom feed.', 'new-posts-block' ),
	keywords: [ __( 'atom', 'new-posts-block' ), __( 'feed', 'new-posts-block' ) ],
	supports: {
		align: true,
		html: false,
	},
	attributes: {
		feedURL: {
			type: 'string',
			default: '',
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
	},
	edit,
	save() {
		return null;
	}
});
