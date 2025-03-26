import SbUtils from '../../Utils/SbUtils'

const Select = ( props ) => {

    const slug = 'sb-input',
          actionsList =  [ 'onFocus', 'onChange', 'onClick' ],
          classesList =  [ 'size' ];

    return (
        <div
            className={'sb-select-ctn sb-input-ctn sbsw-fs ' + SbUtils.getClassNames( props, slug, classesList)}
        >
            { props.label && <div className='sb-dark2-text sb-label sb-text-tiny sbsw-fs'>{props.label}</div> }
            <div className='sb-input-insider sbsw-fs'>
                {
                    ( props.leadingText || props.leadingIcon) &&
                    <span className='sb-input-leading-txt sb-dark2-text'>
                        { SbUtils.printIcon( props.leadingIcon ) }
                        { ( props.leadingText && props.leadingIcon ) && '\u00A0\u00A0' }
                        { props.leadingText }
                    </span>
                }
                <select
                    placeholder={ props.placeholder }
                    { ...SbUtils.getElementActions( props, actionsList ) }
                    name={ props?.name }
                    value={ props?.value }
                >
                    { props.children }
                </select>
                {
                    ( props.trailingText || props.trailingIcon) &&
                    <span className='sb-input-trailing-txt sb-dark2-text'>
                        { SbUtils.printIcon( props.trailingIcon ) }
                        { ( props.trailingText && props.trailingIcon ) && '\u00A0\u00A0' }
                        { props.trailingText }
                    </span>
                }
            </div>
            { props.description && <div className='sb-dark2-text sb-caption sb-text-tiny sbsw-fs'>{props.description}</div> }
        </div>
    )

}

export default Select;
