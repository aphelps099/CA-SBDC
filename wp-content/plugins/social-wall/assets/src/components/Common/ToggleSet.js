import SbUtils from '../../Utils/SbUtils'

const ToggleSet = ( props ) => {

    const slug = 'sb-toggleset',
          parentActionsList =  [ 'parentOnClick' ],
          elementsActionsList =  [ 'onClick' ],
          classesList =  [ 'size' ];

    const onChooseElement = ( value ) => {
        props.onClick( value )
    }

    return (
        <div
            className={'sb-toggleset-ctn sbsw-fs ' + SbUtils.getClassNames( props, slug, classesList)}
            { ...SbUtils.getElementActions( props, parentActionsList ) }
        >
            {
                props?.options?.map( ( opt, optInd ) => {
                    return (
                        <div
                            className='sb-toggleset-elem sbsw-fs'
                            { ...SbUtils.getElementActions( props, elementsActionsList ) }
                            key={ optInd }
                            data-active={ props?.value === opt.value }
                            data-description={ opt.description !== undefined }
                            onClick={ () => {
                                onChooseElement( opt.value )
                            }}
                        >
                            <div className='sb-toggleset-elem-deco sb-tr-2'></div>
                            {
                                opt.icon &&
                                <div className='sb-toggleset-elem-icon'>{ SbUtils.printIcon( opt.icon ) }</div>
                            }
                            <div className='sb-toggleset-elem-label'>
                                <span className={(props.strongLabel ? 'sb-bold ' : '') + 'sbsw-fs'}>{ opt.label }</span>
                                {
                                    opt.description &&
                                    <span className='sb-toggleset-elem-description sb-text-tiny sb-dark2-text sbsw-fs'>{ opt.description }</span>
                                }
                            </div>

                        </div>
                    )
                })
            }

        </div>
    )
}

export default ToggleSet;