import { useContext } from 'react'
import SbUtils from '../../../utils/SbUtils';
import OwlCarousel from 'react-owl-carousel';
import 'owl.carousel/dist/assets/owl.carousel.css'
import 'owl.carousel/dist/assets/owl.theme.default.css'
import SinglePost from './SinglePost'
import FeedEditorContext from '../../../context/FeedEditorContext';

const CarouselLayout = ( { feedSettings, posts } ) => {

    const { editorActiveDevice, sbCustomizer } = useContext( FeedEditorContext );

    let desktopNumber = feedSettings.carouselcols;

    switch ( editorActiveDevice.device ) {
        case 'mobile':
            desktopNumber = feedSettings.carouselcolsmobile;
            break;
        case 'tablet':
            desktopNumber = feedSettings.carouselcolstablet;
            break;
        default :
            desktopNumber = feedSettings.carouselcols;
            break;
    }

    let responsiveLayoutData =  {
        480 : {
            items: feedSettings.carouselcolsmobile,
            rows: feedSettings.carouselrowsmobile
        },
        600 : {
            items: feedSettings.carouselcolstablet,
            rows: feedSettings.carouselrowstablet
        },
        1024 : {
            items: desktopNumber,
            rows: feedSettings.carouselrows
        }
    };

    return (
        <OwlCarousel
            responsive={ responsiveLayoutData }
            margin={ parseInt( feedSettings.itemspacing ) }
            loop={ feedSettings.carouselloop === 'infinity' }
            rewind={ feedSettings.carouselloop === 'rewind' }
            nav={ feedSettings.carouselarrows }
            dots={ feedSettings.carouselpag }
            autoplay={ feedSettings.carouselautoplay }
            autoplayTimeout={ feedSettings.carouseltime }
            navText={[window?.sbsw_admin?.iconsList?.prev, window?.sbsw_admin?.iconsList?.next]}
        >
            {
                posts.map( (post, postIndex) =>
                    <SinglePost
                        post={post}
                        postIndex={postIndex}
                        feedSettings={feedSettings}
                        key={postIndex}
                    />
                )
            }
        </OwlCarousel>
    )
}

export default CarouselLayout;