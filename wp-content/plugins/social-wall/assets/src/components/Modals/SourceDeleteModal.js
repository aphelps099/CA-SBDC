import { __ } from "@wordpress/i18n";
import { useState, useContext } from "react";
import SbUtils from "../../Utils/SbUtils";
import Button from "../Common/Button";
import FeedEditorContext from "../../context/FeedEditorContext";

const SourceDeleteModal = ( props ) => {

    const { editorFeedData, editorTopLoader, editorNotification, sbCustomizer } = useContext( FeedEditorContext );

    const removeWallSource = () => {
        SbUtils.removeFeedSource( editorFeedData, editorTopLoader, editorNotification, sbCustomizer, props.deletePluginData, props.setDeleteSource );
    }

    return (
        <div className='sb-plugin-delete-modal sbsw-fs sw-bg-white sw-shadow-lg sw-rounded-lg sw-h-48 sw-p-6 sw-pt-5'>
                <div className='sw-flex sw-gap-4 sw-items-start'>
                    <div>
                        <h4 className='sw-text-lg sw-font-semibold'>
                            Are you sure you want to delete the source?
                        </h4>
                        <p className='sw-text-sm sw-mt-2'>The <span className="sw-text-capital sb-bold">{props.deletePluginData[0]}</span> source <strong className="sw-text-italic">"{props.deletePluginData[1].feedName}"</strong> feed currently being used on this feed. Removing source this will remove <span className="sw-text-capital sb-bold">{props.deletePluginData[0]}</span> sources from this feed.</p>
                    </div>
                </div>
                <div className='sw-flex sw-justify-end sw-mt-8 sw-gap-2'>
                    <Button
                        size='medium'
                        type='secondary'
                        text={ __( 'Cancel', 'sb-customizer' ) }
                        onClick={ () => {
                            props.setDeleteSource(false)
                        }}
                    />
                    <Button
                        size='medium'
                        type='danger'
                        text={ __( 'Remove', 'sb-customizer' ) }
                        icon='delete'
                        onClick={ () => {
                            removeWallSource();
                        }}
                    />
                </div>
        </div>
    )
}
export default SourceDeleteModal;