import { useState, useMemo } from 'react'
import SbUtils from './utils/SbUtils'
import FeedEditorContext from './context/FeedEditorContext';
import Notification from './components/Global/Notification';
import Header from './components/FeedEditor/Header';
import Sidebar from './components/FeedEditor/Sidebar';
import Preview from './components/FeedEditor/Preview';

function FeedEditor() {
    const sbCustomizer = window.sbsw_admin;

    // Active Tab
    const [ activeTab, setActiveTab ] = useState( SbUtils.findElementById( sbCustomizer.customizerData, 'id', sbCustomizer.feedEditor.defaultTab ) );
    const editorActiveTab = useMemo(
        () => ({ activeTab, setActiveTab }),
		[activeTab, setActiveTab]
    );

    // Active Section Data
    const [ activeSection, setActiveSection ] = useState( null );
    const editorActiveSection = useMemo(
        () => ({ activeSection, setActiveSection }),
		[activeSection, setActiveSection]
    );

    // Feed Data
    const [ feedDataInitial, setFeedDataInitial ] = useState( sbCustomizer.feedData ); //
    const [ feedData, setFeedData ] = useState( sbCustomizer.feedData );
    const editorFeedData = useMemo(
        () => ({ feedData, setFeedData }),
		[feedData, setFeedData]
    );

    // Feed Settings
    const [ feedSettings, setFeedSettings ] = useState( sbCustomizer.feedData.settings );
    const editorFeedSettings = useMemo(
        () => ({ feedSettings, setFeedSettings }),
		[feedSettings, setFeedSettings]
    );

    const editorFeedStyling = useMemo( () => {
        return SbUtils.getFeedStyling( feedSettings, sbCustomizer.customizerData ) ;
    },  [ feedSettings, sbCustomizer.customizerData] );

    const [ loader, setLoader ] = useState( false );
    const editorTopLoader = useMemo(
        () => ({ loader, setLoader }),
		[ loader, setLoader ]
    );

    const [ breadCrumb, setBreadCrumb ] = useState( null );
    const editorBreadCrumb = useMemo(
        () => ({ breadCrumb, setBreadCrumb }),
		[breadCrumb, setBreadCrumb]
    );

    const [ device, setDevice ] = useState( 'desktop' );
    const editorActiveDevice = useMemo(
        () => ({ device, setDevice }),
		[device, setDevice]
    );

    const [ activeViews, setActiveViews ] = useState( { } );
    const editorActiveViews = useMemo(
        () => ({ activeViews, setActiveViews }),
		[activeViews, setActiveViews]
    );

    const [ notification, setNotification ] = useState({
        active : false
    });
    const editorNotification = useMemo(
        () => ({ notification, setNotification }),
		[ notification, setNotification ]
    );
    
    return (
        <FeedEditorContext.Provider
            value={{
                editorActiveTab,
                editorActiveSection,
                sbCustomizer,
                editorFeedSettings,
                editorBreadCrumb,
                editorActiveDevice,
                editorActiveViews,
                feedDataInitial,
                editorFeedData,
                // editorFeedStyling,
                editorTopLoader,
                editorNotification
            }}
        >
            <Header/>

            <section
                className='sb-customoizer-ctn sbsw-fs'
            >
                <Sidebar />
                <Preview />
            </section>

            <Notification
                active={ editorNotification.notification?.active }
                icon={ editorNotification.notification?.icon }
                text={ editorNotification.notification?.text }
                type={ editorNotification.notification?.type }
            />

            <style data-style="sb-styling">
                { editorFeedStyling }
            </style>
        </FeedEditorContext.Provider>
    )
}

export default FeedEditor