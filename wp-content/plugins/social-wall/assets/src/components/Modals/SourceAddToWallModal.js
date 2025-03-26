import { __ } from "@wordpress/i18n";
import { useState, useContext } from "react";
import SbUtils from "../../Utils/SbUtils";
import Button from "../Common/Button";
import FeedEditorContext from "../../context/FeedEditorContext";
import ajaxRequest from '../../utils/AjaxRequest';

const SourceAddToWallModal = ( props ) => {

    const { editorFeedData, editorTopLoader, editorNotification, sbCustomizer } = useContext( FeedEditorContext );
    const sourcePlugin = props.addSourceData;

    const [updateSourceFeed, setUpdateSourceFeed] = useState([]);

    const handleUpdateSourceFeed = (plugin, feed, feedName) => {
        updateSourceFeed[plugin] = {
            id: feed,
			wallPlugin: plugin,
			feedName: feedName
		};
        props.setActiveFeedInUpdateModal({...props.activeFeedInUpdateModal, id: feed})
    }

    const updateWallSource = () => {
        SbUtils.updateFeedSource( editorFeedData, editorTopLoader, editorNotification, sbCustomizer, updateSourceFeed, props.setAddSourceToWall, sourcePlugin );
    }

    return (
        <div className='sb-embedfeed-modal sbsw-fs'>
            <div className='sb-embedfeed-modal-heading sbsw-fs'>
                <h4 className='sb-h4'>{sourcePlugin.title}</h4>
                <p>Choose from one of the {sourcePlugin.title} plugin feeds that you would like to use as a source.</p>
            </div>
            <div className="sb-modal-content">
                <div>
                    <div className="sw-bg-slate-50 sw-py-3 sw-px-8 sw-flex sw-items-center sw-justify-between sw-border-gray-200 sw-border-t">
                        <span className="sw-font-bold sw-text-xs">Select Feed</span>
                        <a href={sourcePlugin.builderUrl} className="sw-text-sb-blue sw-font-bold sw-text-xs">+ New</a>
                    </div>
                    { sourcePlugin.hasFeeds && (
                        <div className="sw-py-4 sw-px-8  sw-overflow-y-scroll sw-h-52">
                            <ul>
                                {sourcePlugin.feeds.map(feed => {
                                    return (
                                        <li>
                                            <input id={sourcePlugin.id + '-' + feed.id} type="radio" name={sourcePlugin.id} onChange={() => handleUpdateSourceFeed(sourcePlugin.id, feed.id, feed.feed_name)} checked={feed.id == props.activeFeedInUpdateModal.id} /> 
                                            <label htmlFor={sourcePlugin.id + '-' + feed.id} className='sw-text-xs'>
                                                <span  className='sw-ml-2 sw-text-sb-gray-2 sw-text-stone-700'>{feed.feed_name}</span>
                                            </label>
                                        </li>
                                    )
                                })}
                            </ul>
                        </div>
                    ) }
                    { !sourcePlugin.hasFeeds && (
                        <div className='sw-px-4 sw-py-10 sw-text-center'>
                            <p className='sw-text-xs sw-text-sb-gray-2'>
                                No existing Twitter feeds found. <br/> 
                                <a href={sourcePlugin.builderUrl} className="sw-underline sw-underline-offset-2">Create a new feed</a> for it to appear here.
                            </p>
                        </div>
                    ) }
                </div>
            </div>
            { sourcePlugin.hasFeeds && (
                <div className='sb-embedfeed-modal-actbtns sw-border-gray-200 sw-border-t'>
                    <Button
                        size='medium'
                        type='secondary'
                        text={ __( 'Cancel', 'sb-customizer' ) }
                        onClick={ () => {
                            props.setAddSourceToWall(false)
                        }}
                    />
                    <Button
                        size='medium'
                        type='primary'
                        text={ __( 'Save Changes', 'sb-customizer' ) }
                        icon='success'
                        onClick={ () => {
                            updateWallSource();
                        }}
                    />
                </div>
            ) }     
        </div>
    )
}
export default SourceAddToWallModal;