import { __ } from '@wordpress/i18n';
import { useContext } from 'react';
import SbUtils from '../../../utils/SbUtils';
import FeedEditorContext from '../../../context/FeedEditorContext';

const ClearCache =  ( {section} ) => {
    const {
        sbCustomizer,
        editorFeedData,
        editorActiveViews,
        editorFeedStyling,
        editorFeedSettings,
        editorTopLoader,
        editorNotification
    } = useContext( FeedEditorContext ) ;

    const clearFeedCache = ( exit = false ) => {
        SbUtils.clearFeedCache( editorFeedData, editorFeedStyling, editorFeedSettings, sbCustomizer, editorTopLoader, editorNotification, exit );
    }

    return (
        <button 
            onClick={() => {
                clearFeedCache()
            }}
        >
            {SbUtils.printIcon( section.icon )} { section.heading }
        </button>
    )
}

export default ClearCache;