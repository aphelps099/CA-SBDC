import { __ } from '@wordpress/i18n';
import { useContext } from 'react'
import SbUtils from '../../Utils/SbUtils';
import FeedEditorContext from '../../context/FeedEditorContext';
import WallFilter from './Preview/WallFilter';
import PostsWrapper from './Preview/PostsWrapper';
import LoadMoreButton from './Preview/LoadMoreButton';

function Preview() {
    const {
        editorFeedSettings,
        editorActiveDevice,
        editorFeedData,
    } = useContext( FeedEditorContext ),    
    feedSettings = editorFeedSettings.feedSettings,
    devices = [ 'mobile', 'tablet', 'desktop' ];

    const getPostsList = () => {
        let postsList = [...editorFeedData.feedData.posts.posts];
        switch (editorActiveDevice.device) {
            case 'tablet':
                return postsList.splice( 0, feedSettings.numtablet)
            case 'mobile':
                return postsList.splice( 0, feedSettings.nummobile)
            default:
                return postsList.splice( 0, feedSettings.numdesktop)
        }
    }

    return (
        <section className='sb-customizer-preview' data-preview-device={editorActiveDevice.device}>
            <section className='sb-preview-wrapper sbsw-fs sb-tr-2'>
                <section className='sb-preview-devices-top sbsw-fs'>
                    <span className='sb-bold sb-text-tiny sb-dark2-text'>{ __('Preview', 'sb-customizer') }</span>
                    <div className='sb-preview-devices-chooser'>
                        {
                            devices.reverse().map( device => {
                                return <button
                                            className='sb-preview-chooser-btn'
                                            data-device={device}
                                            key={device}
                                            data-active={editorActiveDevice.device === device}
                                            onClick={ () => {
                                                editorActiveDevice.setDevice(device)
                                            } }
                                        >
                                            { SbUtils.printIcon( device ) }
                                        </button>
                            })
                        }
                    </div>
                </section>
                <section className='sb-feed-wrapper sbsw-fs'>
                    <section
                        className='sb-feed-container sbsw-fs'
                        data-layout={feedSettings.layout}
                        data-theme={feedSettings.theme}
                        data-post-style={feedSettings.postStyle}
                        >
                        { feedSettings.masonryshowfilter && feedSettings.layout == 'masonry' && (
                            <WallFilter />
                        )}
                        <PostsWrapper
                            feedSettings={feedSettings}
                            posts={ getPostsList() }
                        />
                        <LoadMoreButton
                            feedSettings={feedSettings}
                        />
                    </section>
                </section>
            </section>
        </section>
    )
}

export default Preview