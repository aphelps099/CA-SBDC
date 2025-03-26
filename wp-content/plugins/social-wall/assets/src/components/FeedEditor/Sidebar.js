import { useContext, useMemo } from 'react'
import FeedEditorContext from '../../context/FeedEditorContext';
import SbUtils from '../../utils/SbUtils';
import GroupControl from './SidebarElements/GroupControl';
import SingleControl from './SidebarElements/SingleControl';
import Tab from './Tab';
import Section from './SidebarElements/Section';
import CheckBoxSection from './SidebarElements/CheckBoxSection';
import CustomView from './SidebarElements/CustomView'

const Sidebar = () => {

    const { editorActiveTab, editorActiveSection,  sbCustomizer, editorFeedSettings, editorBreadCrumb } = useContext( FeedEditorContext );

    let sidebarContentOutput = () => {
        if( editorActiveSection.activeSection === null ) {
            return (
                <div className='sb-customizer-sidebar-content sbsw-fs'>
                    <div className='sb-customizer-sidebar-tabs sbsw-fs'>
                        {
                            sbCustomizer.customizerData.map(( tab, tabIndex ) => {
                                return (
                                    <div
                                        className='sb-customizer-sidebar-tab'
                                        key={ tabIndex }
                                        data-active={ editorActiveTab.activeTab.id === tab.id }
                                        onClick={ () => {
                                                editorActiveTab.setActiveTab( tab )
                                            }
                                        }
                                    >
                                        <span className='sb-standard-p sb-bold'>{ tab.name }</span>
                                    </div>
                                )
                            })
                        }
                    </div>
                    <Tab />
                </div>
            );
        } else {
            /** Section Controls */
            let section = editorActiveSection.activeSection;
            return (
                <div className='sbsw-fs'>
                    <div className='sb-customizer-sidebar-header sbsw-fs'>
                        <div className='sb-customizer-sidebar-breadcrumbs sbsw-fs'>
                            {
                                ( editorBreadCrumb.breadCrumb === null || editorBreadCrumb.breadCrumb.length < 2 ) &&
                                <span
                                    className='sb-customizer-breadcrumbs-elm'
                                    onClick={ ( ) => {
                                        editorActiveSection.setActiveSection( null )
                                        editorBreadCrumb.setBreadCrumb( null )
                                    } }
                                >
                                    { editorActiveTab.activeTab.name }
                                </span>
                            }
                            {
                                editorBreadCrumb.breadCrumb !== null &&
                                editorBreadCrumb.breadCrumb.map( (br, brInd) =>  {

                                    //Small logic to check what BreadCrumb Links to show
                                    const checkDisplayBrLink = ( editorBreadCrumb.breadCrumb.length <= 2 ||
                                        editorBreadCrumb.breadCrumb.length + brInd > editorBreadCrumb.breadCrumb.length)

                                    if( checkDisplayBrLink ){
                                        return (
                                            <span
                                                className='sb-customizer-breadcrumbs-elm'
                                                key={ brInd }
                                                onClick={ ( ) => {
                                                    editorActiveSection.setActiveSection( br )
                                                    editorBreadCrumb.breadCrumb.splice( brInd );
                                                } }
                                            >
                                                { br.heading }
                                            </span>
                                        )
                                    }
                                } )
                            }
                        </div>

                        <h3>{ section.heading }</h3>
                        { section.description && <span className='sb-small-p sb-dark-text'>{section.description}</span> }
                    </div>
                    <div className='sb-customizer-sidebar-controls-ctn sbsw-fs'>
                    {
                            section.controls.map( ( element, elementInd ) => {
                                let showElement = SbUtils.checkControlCondition( element, editorFeedSettings.feedSettings );
                                element.dimmed = showElement === 'dimmed' ? true : null;

                                if( element.type === 'section' && showElement !== false ){
                                        /** Render nested sections*/
                                        elementInd = element.id;
                                        return (
                                            <Section
                                                key={ elementInd }
                                                section={ element }
                                                secIndex={ elementInd }
                                                editorActiveSection={ editorActiveSection }
                                                editorBreadCrumb={ editorBreadCrumb }
                                                parentSection={ section }
                                            />
                                        );
                                }else if( element.type === 'group'  && showElement !== false ){
                                    /** Render Group control*/
                                    return (
                                        <GroupControl
                                            key={ elementInd }
                                            group={ element }
                                            groupInd={ elementInd }
                                            editorFeedSettings={editorFeedSettings}
                                        />
                                    );
                                }else if( element.type === 'list'  && showElement !== false ){
                                    /** Render List control*/
                                    return (
                                        <ListControl
                                            key={ elementInd }
                                            list={ element }
                                            listInd={ elementInd }
                                        />
                                    );
                                } else if( element.type === 'checkboxsection'  && showElement !== false ){
                                    /** Render CheckBoxSection control*/
                                    return (
                                        <CheckBoxSection
                                            key={ elementInd }
                                            checkBoxSection={ element }
                                            CheckBoxSectionInd={ elementInd }
                                            editorBreadCrumb={ editorBreadCrumb }
                                            parentSection={ section }
                                        />
                                    );
                                } else if( element.type === 'separator'  && showElement !== false ){
                                    /** Render Separator control*/
                                    return (
                                        <div
                                            className='sb-separator-ctn sbsw-fs'
                                            style={{ marginTop : element.top +'px', marginBottom : element.bottom +'px' }}
                                            key={ elementInd }
                                        ></div>
                                    );
                                } else if( element.type === 'customview' ){
                                    /** Render Customview control*/
                                    return (
                                        <CustomView
                                            key={ elementInd }
                                            customView={ element }
                                        />
                                    );
                                } else{
                                    if( showElement !== false ){
                                        /** Render normal control*/
                                        return (
                                            <SingleControl
                                                key={ elementInd }
                                                control={ element }
                                                controlInd={ elementInd }
                                            />
                                        );
                                    }

                                }
                            })
                        }
                    </div>
                </div>
            );
        }

    }
    return (
        <section class="sb-customizer-sidebar">
            { sidebarContentOutput() }
        </section>
    )
}

export default Sidebar