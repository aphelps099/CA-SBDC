import { __ } from '@wordpress/i18n';
import SbUtils from '../../../Utils/SbUtils'
import ListLayout from './ListLayout'
import MasonryLayout from './MasonryLayout'
import CarouselLayout from './CarouselLayout'

const PostsWrapper = ( { feedSettings, posts } ) => {
    const postsLoopOutput = () => {
        switch ( feedSettings.layout ) {
            case 'masonry':
                return (
                    <MasonryLayout
                        posts={ posts }
                        feedSettings={ feedSettings }
                    />
                )
            case 'carousel':
                return (
                    <CarouselLayout
                        posts={ posts }
                        feedSettings={ feedSettings }
                    />
                )
            default:
                return (
                    <ListLayout
                        posts={ posts }
                        feedSettings={ feedSettings }
                    />
                )

        }
    }

    return (
        <section
            className='sb-feed-posts sb-fs'
            data-icon-size={feedSettings?.ratingIconSize}
            data-avatar-size='medium'
        >
        { postsLoopOutput() }
        </section>
    )
}
export default PostsWrapper;