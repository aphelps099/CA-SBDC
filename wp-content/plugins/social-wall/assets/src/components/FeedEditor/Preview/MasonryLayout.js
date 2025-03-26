import { useContext } from 'react'
import Masonry, {ResponsiveMasonry} from "react-responsive-masonry"
import SinglePost from './SinglePost'
import FeedEditorContext from '../../../context/FeedEditorContext'

const MasonryLayout = ( { feedSettings, posts } ) => {
    const { editorActiveDevice, sbCustomizer } = useContext( FeedEditorContext );
    let desktopNumber = feedSettings.masonrycols;

    switch ( editorActiveDevice.device ) {
        case 'mobile':
            desktopNumber = parseInt(feedSettings.masonrycolsmobile);
            break;
        case 'tablet':
            desktopNumber = parseInt(feedSettings.masonrycolstablet);
            break;
        default :
            desktopNumber = parseInt(feedSettings.masonrycols);
            break;
    }

    const masonryBreakPoint =  {
        350 : feedSettings.masonrymobilecols,
        750 : feedSettings.masonrytabletcols,
        900 : desktopNumber
    };

    return (
        <ResponsiveMasonry
            columnsCountBreakPoints={ masonryBreakPoint }
        >
            <Masonry gutter={`${feedSettings.itemspacingvertical}px ${feedSettings.itemspacing}px`}>
                {
                    posts.map( (post, postIndex) =>
                        <SinglePost
                            post={post}
                            postIndex={postIndex}
                            feedSettings={feedSettings}
                            key={postIndex}
                        />
                    )
                }
            </Masonry>
        </ResponsiveMasonry>
    )
}

export default MasonryLayout;