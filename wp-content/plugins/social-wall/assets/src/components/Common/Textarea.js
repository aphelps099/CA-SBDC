import SbUtils from '../../Utils/SbUtils'

const Textarea = ( props ) => {

    const slug = 'sb-textarea',
          actionsList =  [ 'onFocus', 'onKeyDown', 'onKeyUp', 'onChange', 'onBlur' ],
          classesList =  [ 'size' ];

    return (
        <div
            className={'sb-textarea-ctn sb-input-ctn sbsw-fs' + SbUtils.getClassNames( props, slug, classesList)}
        >
            { props.label && <div className='sb-dark2-text sb-label sb-text-tiny sbsw-fs'>{props.label}</div> }
            <div className='sb-input-insider sbsw-fs'>
                <textarea
                        maxLength={ props.maxLength }
                        minLength={ props.minLength }
                        cols={ props.cols }
                        rows={ props.rows }
                        name={ props?.name }
                        value={ props?.value }
                        placeholder={ props.placeholder }
                        { ...SbUtils.getElementActions( props, actionsList ) }
                >
                </textarea>
            </div>
            { props.description && <div className='sb-dark2-text sb-caption sb-text-tiny sbsw-fs'>{props.description}</div> }
        </div>
    )

}

export default Textarea;
