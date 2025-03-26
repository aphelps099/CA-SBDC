import { __ } from '@wordpress/i18n';
import { useContext, useState } from 'react'
import FeedEditorContext from '../../context/FeedEditorContext';
import ModalContainer from '../Global/ModalContainer';
import CustomizerEmbedModal from '../Modals/CustomizerEmbedModal';
import Button from '../Common/Button';
import SbUtils from '../../utils/SbUtils';
import { ReactComponent as Close } from '../../../images/feed-editor/close.svg';
import { ReactComponent as EditIcon } from '../../../images/feed-editor/pen.svg';
import { ReactComponent as SuccessIcon } from '../../../images/feed-editor/success.svg';

export default function Header() {

    const {
        sbCustomizer,
        editorFeedData,
        editorActiveViews,
        editorFeedStyling,
        editorFeedSettings,
        editorTopLoader,
        editorNotification
    } = useContext( FeedEditorContext ) ;

    const [ embedModal, setEmbedModal ] = useState( false );

    //Back Button Click
    const backButtonClick = () => {
        window.location.href = sbCustomizer.swAdminPage;
     }

    const saveUpdateHeader = ( exit = false ) => {
        SbUtils.saveFeedData( editorFeedData, editorFeedStyling, editorFeedSettings, sbCustomizer, editorTopLoader, editorNotification,  exit );
    }

    return (
        <section className='sb-customizer-header sb-header sbsw-fs'>
            <div class="sb-header-content sbsw-fs">
                <button 
                    class="sb-btn sb-close-btn sb-btn-secondary sb-btn-medium" 
                    data-icon-position="left" 
                    data-boxshadow="false" 
                    data-onlyicon="true"
                    onClick={ () => {
                        backButtonClick()
                    } }
                >
                    <span class="sb-btn-icon">
                        <Close/>
                    </span> Close Editor
                </button>
                <div class="sb-header-feedname-ctn">
                    {
                        editorActiveViews.activeViews?.headerInput &&
                        <div class="sb-input-insider sbsw-fs">
                            <input 
                                type="text" 
                                value={ editorFeedData.feedData.feed_info.feed_name }
                                onChange={ ( event ) => {
                                    const newFeedName = event.currentTarget.value;
                                    editorFeedData.setFeedData( {
                                        ...editorFeedData.feedData,
                                        feed_info : {
                                            ...editorFeedData.feedData.feed_info,
                                            feed_name : newFeedName
                                        }
                                    } )
                                } }
                                style={ { width : (( editorFeedData.feedData.feed_info.feed_name.length + 2 ) * 7) + 'px' } }
                            />
                        </div>
                    }
                    {
                        !editorActiveViews.activeViews?.headerInput &&
                        <span className="sb-bold sb-standard-p">{ editorFeedData.feedData.feed_info.feed_name }</span>
                    }
                    { editorFeedData.feedData.feed_info.id !== 'legacy' && (
                        <button 
                            class="sb-btn sb-header-edit-btn sb-btn-secondary sb-btn-small" data-icon-position="left" 
                            data-boxshadow="false" 
                            data-onlyicon="false"
                            onClick={ () => {
                                editorActiveViews.setActiveViews(
                                    {
                                        ...editorActiveViews.activeViews,
                                        headerInput : !editorActiveViews.activeViews?.headerInput
                                    }
                                )
                            }}
                        >
                            <span class="sb-btn-icon">
                                {!editorActiveViews.activeViews?.headerInput && <EditIcon/>}
                                {editorActiveViews.activeViews?.headerInput && <SuccessIcon/>}
                            </span>
                        </button>
                    )}
                </div>
                <div class="sb-header-action-btns">
                    <Button
                        type='secondary'
                        size='medium'
                        icon='help'
                        boxshadow={false}
                        text={ __( 'Help', 'sb-customizer' ) }
                    />
                    { editorFeedData.feedData.feed_info.id !== 'legacy' && (
                        <Button
                            type='secondary'
                            size='medium'
                            icon='code'
                            boxshadow={false}
                            text={ __( 'Embed', 'sb-customizer' ) }
                            onClick={ () => {
                                saveUpdateHeader()
                                setEmbedModal( true )
                            } }
                            />
                    )}
                    <Button
                        type='primary'
                        size='medium'
                        icon='success'
                        onClick={ () => {
                            saveUpdateHeader()
                        }}
                        text={ __( 'Save', 'sb-customizer' ) }
                    />
                </div>
            </div>

            {
                embedModal &&
                <ModalContainer
                    size='small'
                    closebutton={ true }
                    onClose={ () => {
                        setEmbedModal( false )
                    } }
                >
                    <CustomizerEmbedModal
                        editorFeedData={ editorFeedData }
                        editorNotification={ editorNotification }
                        sbCustomizer= {sbCustomizer }
                    />
                </ModalContainer>
            }

            {
                editorTopLoader?.loader &&
                <div className='sb-loadingbar-ctn'></div>
            }
        </section>
    )
}
