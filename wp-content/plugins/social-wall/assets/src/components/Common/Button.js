import SbUtils from '../../Utils/SbUtils';

const Button = ( props ) => {

    const   slug = 'sb-btn',
            classesList =  [ 'type', 'size' ],
            actionsList =  [ 'onClick' ],
            attributesList = [
                { 'icon-position' : 'left'},
                'full-width',
                'boxshadow',
                'disabled'
            ];

    return (
        <button
            className={ SbUtils.getClassNames( props, slug, classesList ) }
           { ...SbUtils.getElementActions( props, actionsList ) }
           { ...SbUtils.getElementAttributes( props, attributesList ) }
           data-onlyicon={ props.text !== undefined }
        >
            { props.iconPosition == 'left' || !props.iconPosition && (
                <>
                    {SbUtils.printIcon( props.icon, 'sb-btn-icon', false, props?.iconSize ) }
                    {props.text}
                </>
            )}
            { props.iconPosition == 'right' && (
                <>
                    {props.text}
                    {SbUtils.printIcon( props.icon, 'sb-btn-icon', false, props?.iconSize ) }
                </>
            )}
            {
                props?.tooltip !== undefined &&
                SbUtils.printTooltip( props.tooltip, props?.tooltipType, props?.tooltipPosition )
            }
        </button>
    )
}

export default Button;