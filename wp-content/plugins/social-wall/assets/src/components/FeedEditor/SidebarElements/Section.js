import { __ } from '@wordpress/i18n';
import { useContext } from 'react';
import SbUtils from '../../../utils/SbUtils';
import FeedEditorContext from '../../../context/FeedEditorContext';
import ClearCache from './ClearCache';

const Section = ( { section, secIndex,  editorActiveSection, editorBreadCrumb, parentSection } ) => {
    const {
            editorFeedData,
            sbCustomizer,
            editorFeedSettings
        } = useContext( FeedEditorContext );
    
    if ( section.id == undefined ) {
        return (
            <div
                className='sb-customizer-sidebar-sec-el sbsw-fs'
                data-separator={ section.separator }
                onClick={() => {
                    editorActiveSection.setActiveSection( section )
                }}
                key={ secIndex }
                >
                { SbUtils.printIcon( section.icon, 'sb-customizer-sidebar-sec-el-icon' ) }
                <span className='sb-small-p sb-bold sb-dark-text'>{ section.heading }</span>
            </div>
        );
    }

    if ( section.id == 'clear_cache' ) {
        return (
            <div
                className='sb-customizer-settings-clear-cache sbsw-fs'
                >
                <ClearCache section={section} />
            </div>
        )
    }
}

export default Section;