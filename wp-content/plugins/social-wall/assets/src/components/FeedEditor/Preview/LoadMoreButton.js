import SbUtils from '../../../Utils/SbUtils'
import Button from '../../Common/Button'

const LoadMoreButton = ( { feedSettings } ) => {
    return (
        feedSettings?.showbutton === true &&
        <section className='sb-load-button-ctn'>
            <Button
                text={feedSettings?.buttontext}
                full-width={true}
                customClass='sb-load-button'
            />
        </section>
    )
}

export default LoadMoreButton;