import { useContext } from 'react'
import Masonry, {ResponsiveMasonry} from "react-responsive-masonry"
import SinglePost from './SinglePost'
import FeedEditorContext from '../../../context/FeedEditorContext'

const ListLayout = ( { feedSettings, posts } ) => {
    const { editorActiveDevice, sbCustomizer } = useContext( FeedEditorContext );

    return (
        <div className='sbsw-list-layout'>
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
        </div>
    )
}

export default ListLayout;