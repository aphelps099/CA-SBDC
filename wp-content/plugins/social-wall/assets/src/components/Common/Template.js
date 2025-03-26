import SbUtils from '../../Utils/SbUtils'

const Template = ( props ) => {

    return (
        <div
            className={ 'sb-template-item' + (props?.customClass ? ' '+props?.customClass : '') }
            data-checked={props.isChecked}
            onClick={ () => {
                if( props?.onClick )
                    props.onClick()
            }}
        >
            <div className='sb-template-icon sbsw-fs'>
                { SbUtils.printIcon( props.type + '-template', 'sb-template-svg' ) }
            </div>
            <div className='sb-template-name sb-bold sb-text-small'>
                { props.title }
            </div>
        </div>
    )
}

export default Template;
