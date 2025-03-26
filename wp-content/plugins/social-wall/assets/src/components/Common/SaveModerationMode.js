import { __ } from "@wordpress/i18n";
import { useContext, useEffect, useRef } from "react";
import SbUtils from "../../Utils/SbUtils";
import FeedEditorContext from "../Context/FeedEditorContext";
import Button from "./Button";

const SaveModerationMode = ( props ) => {
    const {
        sbCustomizer,
        editorTopLoader,
        editorFeedSettings,
        editorNotification,
        editorFeedData,
        editorModerationCurrentList,
        editorFeedStyling,
        editorModerationMode,
        editorActiveSection
    } = useContext( FeedEditorContext );

    const settingsRef = useRef( editorFeedSettings.feedSettings )
    useEffect(() => {
        settingsRef.current = editorFeedSettings.feedSettings
    }, [ editorFeedSettings.feedSettings ]);


    const saveModerationMode = () => {
        editorActiveSection.setActiveSection( null )
        if( editorFeedSettings.feedSettings.moderationType === 'allow' ){
            editorFeedSettings.setFeedSettings(  {
                ...editorFeedSettings.feedSettings,
                moderationAllowList : [ ...editorModerationCurrentList.moderationCurrentListSelected ]
            }  );
            settingsRef.current.moderationAllowList = [ ...editorModerationCurrentList.moderationCurrentListSelected ]
        }
        else if( editorFeedSettings.feedSettings.moderationType === 'block' ){
            editorFeedSettings.setFeedSettings(  {
                ...editorFeedSettings.feedSettings,
                moderationBlockList : [ ...editorModerationCurrentList.moderationCurrentListSelected ]
            }  );
            settingsRef.current.moderationBlockList = [ ...editorModerationCurrentList.moderationCurrentListSelected ]
        }
        editorModerationMode.setModerationMode( false )


        setTimeout(() => {
            SbUtils.saveFeedData( editorFeedData, editorFeedStyling, settingsRef, sbCustomizer, editorTopLoader, editorNotification,  false, true, true );
        }, 10)
    }

    return (
        <div className='sb-savemoderaionmode-buttons'>
            <Button
                size='medium'
                type='primary'
                text={ __( 'Save and Exit', 'sb-customizer' ) }
                icon='success'
                onClick={ () => {
                    saveModerationMode()
                } }
            />
            <Button
                size='medium'
                type='secondary'
                boxshadow='false'
                text={ __( 'Cancel', 'sb-customizer' ) }
                onClick={ () => {
                    editorModerationMode.setModerationMode( false )
                    editorActiveSection.setActiveSection( null )
                } }
            />
        </div>

    )
}

export default SaveModerationMode;