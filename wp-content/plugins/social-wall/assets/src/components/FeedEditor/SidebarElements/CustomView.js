import { __ } from '@wordpress/i18n';
import { useContext } from 'react'
import FeedEditorContext from '../../../context/FeedEditorContext';
import SourcesView from '../../Common/SourcesView';

const CustomView = ( { customView } ) => {
    const customViewId = customView.viewId;

    const { editorFeedData } = useContext( FeedEditorContext );

    // Do not show sources control for legacy feeds
    if ( editorFeedData.feedData.feed_info.id == 'legacy' ) {
        return (
            <div className='sw-p-4 sw-px-5 sw-bg-sb-bg-alert sw-text-sb-color-alert sw-font-medium'>
                { __( 'Source control is disabled for legacy feeds', 'social-wall' ) }
            </div>
        )
    }
        
    return (
        <>
            { customViewId == 'sources' && (
                <SourcesView/>
            )}
        </>
    )
}

export default CustomView;