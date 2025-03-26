import { useState, useContext } from 'react'
import FeedEditorContext from '../../context/FeedEditorContext'
import SbUtils from '../../utils/SbUtils'
import ModalContainer from '../Global/ModalContainer'
import SourceDeleteModal from '../Modals/SourceDeleteModal'
import SourceAddToWallModal from '../Modals/SourceAddToWallModal'
import InstallPluginModal from '../Modals/InstallPluginModal'

const SourcesView = () => {
    const { sbCustomizer, editorFeedData } = useContext( FeedEditorContext );
    const [ deleteSource, setDeleteSource ] = useState(false)
    const [ deletePluginData, setDeletePluginData ] = useState('')
    const [ addSourceToWall, setAddSourceToWall ] = useState('')
    const [ addSourceData, setAddSourceData ] = useState(false)
    const [ activeFeedInUpdateModal, setActiveFeedInUpdateModal ] = useState([])
	const [pluginInstallModal, setPluginInstallModal] = useState(false);
    const wallPlugins = editorFeedData.feedData.wall_plugins;
    const swPlugins = sbCustomizer.pluginsFeeds;
    let otherPlugins = swPlugins.filter(plugin => !Object.keys(wallPlugins).includes(plugin['id']));

    const updateFeedSource = ( plugin ) => {
        const editPluginData = swPlugins.filter(swPlugin => {
            return swPlugin.id == plugin[0]
        });
        setAddSourceToWall(true);
        setAddSourceData(editPluginData[0]);
        setActiveFeedInUpdateModal(plugin[1])
    }

    const updateFeedorInstallWallPlugin = (plugin) => {
        setAddSourceData(plugin);
        if ( plugin.isPluginActive ) {
            setAddSourceToWall(true);
        } else {
            setPluginInstallModal(true)            
        }
    }

    return (
        <>
            <div className='sb-customview sb-sourceview-ctn sbsw-fs'>
                <div className="sb-source-list">
                    { Object.entries(wallPlugins).map((plugin, index) => {
                        return (
                            <div className="sb-plugin-source">
                                <div className="sb-source-info">
                                    <div className="sb-source-plugin-logo">
                                        { SbUtils.printIcon(plugin[0]) }
                                    </div>
                                    <div>
                                        <h4>{plugin[0]}</h4>
                                        <p>{plugin[1]['feedName']}</p>
                                    </div>
                                </div>
                                <div className="sb-action-btns">
                                    <button
                                        onClick={() => {
                                            updateFeedSource(plugin)
                                        }}
                                    >
                                        { SbUtils.printIcon('pen', 'edit-icon') }
                                    </button>
                                    { Object.entries(wallPlugins).length > 1 && (
                                        <button
                                            onClick={() => {
                                                setDeletePluginData(plugin)
                                                setDeleteSource(true)
                                            }}
                                            >
                                            { SbUtils.printIcon('delete', 'delete-icon') }
                                        </button>
                                    ) }
                                </div>
                            </div>
                        )
                    })}

                    { otherPlugins.length && otherPlugins.map(plugin => {
                        return (
                            <div className="sb-plugin-source no-bg">
                                <div className="sb-source-info">
                                    <div className="sb-source-plugin-logo">
                                        { SbUtils.printIcon(plugin.id) }
                                    </div>
                                    <div>
                                        <h4>{plugin.title}</h4>
                                    </div>
                                </div>
                                <div className="sb-action-btns">
                                    <button 
                                        className='sb-btn sb-add-to-wall'
                                        onClick={() => {
                                            updateFeedorInstallWallPlugin(plugin)
                                        }}
                                    >
                                        { SbUtils.printIcon('icon-plus') }
                                        Add to Wall
                                    </button>
                                </div>
                            </div>
                        )
                    }) }
                </div>
            </div>

            {
                deleteSource &&
                <ModalContainer
                    size='xs'
                    closebutton={ true }
                    onClose={ () => {
                        setDeleteSource( false )
                    } }
                >
                    <SourceDeleteModal
                        setDeleteSource= { setDeleteSource }
                        deletePluginData={ deletePluginData }
                    />
                </ModalContainer>
            }
            
            {
                addSourceToWall &&
                <ModalContainer
                    size='small'
                    closebutton={ true }
                    onClose={ () => {
                        setAddSourceToWall( false )
                    } }
                >
                    <SourceAddToWallModal
                        setAddSourceToWall= { setAddSourceToWall }
                        addSourceData={ addSourceData }
                        activeFeedInUpdateModal={ activeFeedInUpdateModal }
                        setActiveFeedInUpdateModal={ setActiveFeedInUpdateModal }
                    />
                </ModalContainer>
            }

            { pluginInstallModal && 
                <ModalContainer
                    size='xs'
                    closebutton={ true }
                    onClose={ () => {
                        setPluginInstallModal( false )
                    } }
                >
                    <InstallPluginModal
                        addSourceData={ addSourceData }
                        setPluginInstallModal={ setPluginInstallModal }
                    />
                </ModalContainer>
            }
        </>
    )
}

export default SourcesView