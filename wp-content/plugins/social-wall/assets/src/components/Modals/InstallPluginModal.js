import { __ } from "@wordpress/i18n";
import { useState, useContext } from "react";
import SbUtils from "../../Utils/SbUtils";
import Button from "../Common/Button";
import FeedEditorContext from "../../context/FeedEditorContext";

const InstallPluginModal = ( props ) => {

    const { editorFeedData, editorTopLoader, editorNotification, sbCustomizer } = useContext( FeedEditorContext );
	const [pluginSuccess, setPluginSuccess] = useState({});
    const [loading, setLoading] = useState(false);

    const installBtnText = () => {
        let btnText = '';
		if ( pluginSuccess.success ) {
			btnText = pluginSuccess.data.msg
		} else {
			if ( loading ) {
				btnText = !props.addSourceData.isPluginInstalled ? 'Installing' : 'Activating';
			} else {
				btnText = !props.addSourceData.isPluginInstalled ? 'Install Plugin' : 'Activate Plugin';
			}
		}

        return btnText;
    }

    const installBtnIcon = () => {
        if ( pluginSuccess.success ) {
            return 'success'
        } else {
            return loading ? 'loader' : 'chevron-right';
        }
    }

    const installPlugin = () => {
        SbUtils.installPlugin( editorFeedData, editorTopLoader, editorNotification, sbCustomizer, props.addSourceData, props.setPluginInstallModal, pluginSuccess, setPluginSuccess, setLoading );
    }

    return (
        <div className='sb-plugin-install-modal sbsw-fs sw-bg-white sw-shadow-lg sw-rounded-lg sw-h-48 sw-p-6 sw-pt-5'>
                <div className='sw-flex sw-gap-4 sw-items-start'>
                    <div className="sw-w-6 sw-pt-1">
                        { SbUtils.printIcon(props.addSourceData.id) }
                    </div>
                    <div>
                        <h4 className='sw-text-lg sw-font-semibold'>{!props.addSourceData.isPluginInstalled ? 'Install' : 'Activate'} {props.addSourceData.title} Plugin</h4>
                        <p className='sw-text-sm sw-mt-2'>To add an {props.addSourceData.title} Feed to the wall, you need to {!props.addSourceData.isPluginInstalled ? 'install' : 'activate'} {props.addSourceData.title} plugin first</p>
                    </div>
                </div>
                <div className='sw-flex sw-justify-end sw-mt-8 sw-gap-2'>
               
                    <Button
                        size='medium'
                        type='secondary'
                        text={ pluginSuccess.success ? __( 'Close', 'sb-customizer' ) : __( 'Cancel', 'sb-customizer' ) }
                        onClick={ () => {
                            props.setPluginInstallModal(false)
                        }}
                    />
                    <Button
                        size='medium'
                        type='primary'
                        text={ installBtnText() }
                        icon={ installBtnIcon() }
                        iconPosition='right'
                        onClick={ () => {
                            setLoading(true);
                            installPlugin();
                        }}
                    />
                </div>
        </div>
    )
}
export default InstallPluginModal;