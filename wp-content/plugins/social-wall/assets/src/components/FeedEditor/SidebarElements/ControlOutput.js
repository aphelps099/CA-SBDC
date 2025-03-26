import { useContext, useEffect, useRef } from 'react'
import FeedEditorContext from '../../../context/FeedEditorContext'
import Font from '../../Common/Font'
import Input from '../../Common/Input'
import Slider from '../../Common/Slider'
import Stroke from '../../Common/Stroke'
import Select from '../../Common/Select'
import Textarea from '../../Common/Textarea'
import Switcher from '../../Common/Switcher'
import Checkbox from '../../Common/Checkbox'
import Distance from '../../Common/Distance'
import BoxShadow from '../../Common/BoxShadow'
import ToggleSet from '../../Common/ToggleSet'
import ColorPicker from '../../Common/ColorPicker'
import BorderRadius from '../../Common/BorderRadius'
import ToggleButtons from '../../Common/ToggleButtons'
// import FeedTemplate from '../../Common/FeedTemplate'
// import FeedSources from '../../Common/FeedSources'
import SbUtils from '../../../Utils/SbUtils'
// import SaveModerationMode from '../../Common/SaveModerationMode'
// import Notice from '../../Global/Notice'

const ControlOutput = ( props ) => {

    const {
        sources,
        sbCustomizer,
        editorTopLoader,
        editorFeedSettings,
        editorNotification,
        editorFeedData,
        fbModal
    } = useContext( FeedEditorContext );

    const settingsRef = useRef( editorFeedSettings.feedSettings )
    useEffect(() => {
        settingsRef.current = editorFeedSettings.feedSettings
    }, [ editorFeedSettings.feedSettings ]);

    const onLeaveControlAction = [ 'input', 'textarea' ];

    const customControlAction = () => {
        if( props.control?.ajaxAction !== undefined ){
            switch ( props.control?.ajaxAction ) {
                case 'feedFlyPreview':
                    setTimeout(() => {
                        SbUtils.feedFlyPreview( editorFeedData, editorTopLoader, editorNotification, sbCustomizer, settingsRef )
                    }, 50);
                break;
                default:
                break;
            }
        }
    }
    const changeFeedEditorSetting = ( el ) => {
        let settingValue = null;
        switch ( props.control.type ) {
            case 'checkbox':
            case 'switcher':
                settingValue = editorFeedSettings.feedSettings[props.control.id] === props.control.options.enabled
                            ? props.control.options.disabled : props.control.options.enabled;
                break;
            case 'slider':
            case 'toggleset':
            case 'togglebuttons':
            case 'colorpicker':
            case 'font':
            case 'distance':
            case 'boxshadow':
            case 'borderradius':
            case 'stroke':
            case 'feedtemplate':
            case 'feedsources':
                settingValue = el;
                break;
            default:
                settingValue = el.currentTarget.value;
                break;
        }

        editorFeedSettings.setFeedSettings(  {
            ...editorFeedSettings.feedSettings,
            [ props.control.id ] : settingValue
        }  );

        if( !onLeaveControlAction.includes( props.control.type ) ){
            customControlAction();
        }
    }



    let printControlOuput = ( props ) => {
        let output = null;
            props = props.control;
        switch ( props.type ) {
            case 'input':
                output =
                    <Input
                        customClass='sb-input-control'
                        type={ props.inputType }
                        placeholder={ props.placeholder }
                        leadingText={ props.leadingText }
                        trailingText={ props.trailingText }
                        leadingIcon={ props.leadingIcon }
                        trailingIcon={ props.trailingIcon }
                        maxLength={ props.maxLength }
                        minLength={ props.minLength }
                        max={ props.max }
                        min={ props.min }
                        size='small'
                        disablebg={true}
                        value={ editorFeedSettings?.feedSettings[props.id] }
                        onChange={ ( event ) => {
                            changeFeedEditorSetting( event )
                        } }
                        onBlur={(e) => {
                            if (e.currentTarget === e.target) {
                                customControlAction()
                            }
                        }}
                    />;
            break;
            case 'textarea':
                output =
                    <Textarea
                        customClass='sb-input-control'
                        type={ props.inputType }
                        placeholder={ props.placeholder }
                        cols={ props.cols }
                        rows={ props.rows }
                        maxLength={ props.maxLength }
                        minLength={ props.minLength }
                        size='small'
                        value={ editorFeedSettings?.feedSettings[props.id] }
                        onChange={ ( event ) => {
                            changeFeedEditorSetting( event )
                        } }
                        onBlur={(e) => {
                            if (e.currentTarget === e.target) {
                                customControlAction()
                            }
                        }}
                    />;
            break;
            case 'select':
                const optKeys = Object.keys( props.options );
                output =
                    <Select
                        customClass='sb-select-control'
                        type={ props.inputType }
                        placeholder={ props.placeholder }
                        cols={ props.cols }
                        rows={ props.rows }
                        maxLength={ props.maxLength }
                        minLength={ props.minLength }
                        size='small'
                        value={ editorFeedSettings?.feedSettings[props.id] }
                        onChange={ ( event ) => {
                            changeFeedEditorSetting( event )
                        } }
                    >
                        {
                            optKeys.map( ( optKey, optInd ) => {
                                return (
                                    <option key={ optKey } value={ optKey }>{ props.options[optKey] }</option>
                                )
                            })
                        }
                    </Select>;
            break;
            case 'switcher':
                output =
                    <Switcher
                        customClass='sb-switcher-control'
                        value={ editorFeedSettings?.feedSettings[props.id] }
                        enabled={ props.options.enabled }
                        disabled={ props.options.disabled }
                        label={ props?.label }
                        labelDescription={ props?.labelDescription }
                        labelStrong={ props?.labelStrong }
                        onChange={ ( event ) => {
                            changeFeedEditorSetting( event )
                        } }
                    />;
            break;
            case 'checkbox':
                output =
                    <Checkbox
                        customClass='sb-checkbox-control'
                        value={ editorFeedSettings?.feedSettings[props.id] }
                        enabled={ props.options.enabled }
                        disabled={ props.options.disabled }
                        label={ props?.label }
                        labelStrong={ props?.labelStrong }
                        onChange={ ( event ) => {
                            changeFeedEditorSetting( event )
                        } }
                    />;
            break;
            case 'toggleset':
                output =
                    <ToggleSet
                        customClass='sb-togglset-control'
                        options={ props.options }
                        value={ editorFeedSettings?.feedSettings[props.id] }
                        strongLabel={ props.strongLabel }
                        onClick={ ( event ) => {
                            changeFeedEditorSetting( event )
                        } }
                    />;
            break;
            case 'togglebuttons':
                output =
                    <ToggleButtons
                        customClass='sb-togglbuttons-control'
                        options={ props.options }
                        value={ editorFeedSettings?.feedSettings[props.id] }
                        onClick={ ( event ) => {
                            changeFeedEditorSetting( event )
                        } }
                    />;
            break;
            case 'distance': //for Margins & Paddings
                output =
                    <Distance
                        customClass='sb-distance-control'
                        sides={ props.sides }
                        value={ editorFeedSettings?.feedSettings[props.id] }
                        onChange={ ( event ) => {
                            changeFeedEditorSetting( event )
                        } }
                    />;
            break;
            case 'slider':
                output =
                    <Slider
                        customClass='sb-slider-control'
                        unit={ props.unit }
                        label={ props.label }
                        labelIcon={ props.labelIcon }
                        inputNumber={ props.inputNumber !== false || props.inputNumber !== undefined }
                        value={ editorFeedSettings?.feedSettings[props.id] }
                        onChange={ ( event ) => {
                            changeFeedEditorSetting( event )
                        } }
                    />;
            break;
            case 'colorpicker':
                output =
                    <ColorPicker
                        customClass='sb-colorpicker-control'
                        default={ props?.default || '' }
                        value={ editorFeedSettings?.feedSettings[props.id] }
                        onChange={ ( event ) => {
                            changeFeedEditorSetting( event )
                        } }
                    />;
            break;
            case 'boxshadow':
                output =
                    <BoxShadow
                        customClass='sb-boxshadow-control'
                        label={ props.label }
                        value={ editorFeedSettings?.feedSettings[props.id] }
                        onChange={ ( event ) => {
                            changeFeedEditorSetting( event )
                        } }
                    />;
            break;
            case 'borderradius':
                output =
                    <BorderRadius
                        customClass='sb-borderradius-control'
                        label={ props.label }
                        value={ editorFeedSettings?.feedSettings[props.id] }
                        onChange={ ( event ) => {
                            changeFeedEditorSetting( event )
                        } }
                    />;
            break;
            case 'stroke':
                output =
                    <Stroke
                        customClass='sb-stroke-control'
                        label={ props.label }
                        value={ editorFeedSettings?.feedSettings[props.id] }
                        onChange={ ( event ) => {
                            changeFeedEditorSetting( event )
                        } }
                    />;
            break;
            case 'font':
                output =
                    <Font
                        customClass='sb-fontchooser-control'
                        value={ editorFeedSettings?.feedSettings[props.id] }
                        onChange={ ( event ) => {
                            changeFeedEditorSetting( event )
                        } }
                        />;
            break;
            case 'feedtemplate':
                output =
                    <FeedTemplate
                        customClass='sb-feedtemplate-control'
                        templatesList={ sbCustomizer.templatesList }
                        value={ editorFeedSettings?.feedSettings[props.id] }
                        onChange={ ( event ) => {
                            changeFeedEditorSetting( event )
                        } }
                    />;
            break;
            case 'feedsources':
                output =
                    <FeedSources
                        customClass='sb-feedsources-control'
                        sources={ sources }
                        value={ editorFeedSettings?.feedSettings[props.id] || [] }
                        fbModal={ fbModal }
                        onChange={ ( event ) => {
                            changeFeedEditorSetting( event )
                        } }
                    />;
            break;
            case 'savemoderation':
                output =
                    <SaveModerationMode
                        customClass='sb-savemoderaionmode-control'
                    />;
            break;
            case 'notice':
                output =
                    <Notice
                        customClass='sb-notice-control'
                        icon={ props.noticeIcon }
                        heading={ props.noticeHeading }
                        text={ props.noticeText }
                    />;
            break;

            default:
                output = null;
            break;

        }


        return output;
    }

    return printControlOuput( props );

}

export default ControlOutput;