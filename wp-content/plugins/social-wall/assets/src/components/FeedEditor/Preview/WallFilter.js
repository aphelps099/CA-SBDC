import { __ } from '@wordpress/i18n';
import { useContext } from 'react'
import SbUtils from '../../../Utils/SbUtils'
import FeedEditorContext from '../../../context/FeedEditorContext';
import { ReactComponent as TikTokIcon } from '../../../../images/tiktok.svg';

const WallFilter = () => {
    const { editorFeedData } = useContext( FeedEditorContext );
    const wallPlugins = editorFeedData.feedData.wall_plugins;

    return (
        <div className="sbsw-wall-filters">
            { Object.entries(wallPlugins).length > 1 && (
                <>
                    <button className="sbsw-single-filter sbsw-single-filter-all">
                        { SbUtils.printIcon('heart', 'filter-icon') }
                        {__( 'All', 'social-wall' )}
                    </button>
                    { Object.entries(wallPlugins).map((plugin, index) => {
                        if ( plugin[0] == 'facebook' ) {
                            return (
                                <button className="sbsw-single-filter sbsw-single-filter-facebook">
                                    { SbUtils.printIcon('facebook', 'filter-icon') }
                                    {__( 'Facebook', 'social-wall' )}
                                </button>
                            )
                        }
                        if ( plugin[0] == 'instagram' ) {
                            return (
                                <button className="sbsw-single-filter sbsw-single-filter-instagram">
                                    { SbUtils.printIcon('instagram', 'filter-icon') }
                                    {__( 'Instagram', 'social-wall' )}
                                </button>
                            )
                        }
                        if ( plugin[0] == 'twitter' ) {
                            return (
                                <button className="sbsw-single-filter sbsw-single-filter-twitter">
                                    { SbUtils.printIcon('twitter', 'filter-icon') }
                                    {__( 'Twitter', 'social-wall' )}
                                </button>
                            )
                        }
                        if ( plugin[0] == 'youtube' ) {
                            return (
                                <button className="sbsw-single-filter sbsw-single-filter-youtube">
                                    { SbUtils.printIcon('youtube', 'filter-icon') }
                                    {__( 'YouTube', 'social-wall' )}
                                </button>
                            )
                        }
                        if ( plugin[0] == 'tiktok' ) {
                            return (
                                <button className="sbsw-single-filter sbsw-single-filter-tiktok">
                                    <div className="filter-icon"><TikTokIcon/></div>
                                    {__( 'TikTok', 'social-wall' )}
                                </button>
                            )
                        }
                    })}
                </>
            )}
        </div>
    )
}

export default WallFilter;