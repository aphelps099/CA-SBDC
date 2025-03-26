import SbUtils from '../../../Utils/SbUtils'
import InstagramFeedItem from './InstagramFeedItem';
import FacebookFeedItem from './FacebookFeedItem';
import YouTubeFeedItem from './YouTubeFeedItem';
import TwitterFeedItem from './TwitterFeedItem';
import TikTokFeedItem from './TikTokFeedItem';

const SinglePost = ( { post, postIndex, feedSettings } ) => {

    const pluginName = SbUtils.getPlugin( post );

    return (
        <div className={'sbsw-post-item sbsw-' + pluginName + "-item" } key={postIndex}>
            { pluginName == 'instagram' && 
                <InstagramFeedItem post={post} feedSettings={feedSettings} pluginName={pluginName} /> 
            } 
            { pluginName == 'facebook' && 
                <FacebookFeedItem post={post} feedSettings={feedSettings} pluginName={pluginName} /> 
            } 
            { pluginName == 'youtube' && 
                <YouTubeFeedItem post={post} feedSettings={feedSettings} pluginName={pluginName} /> 
            } 
            { pluginName == 'twitter' && 
                <TwitterFeedItem post={post} feedSettings={feedSettings} pluginName={pluginName} />
            }
            { pluginName == 'tiktok' && 
                <TikTokFeedItem post={post} feedSettings={feedSettings} pluginName={pluginName} />
            }
        </div>
    )
}

export default SinglePost;