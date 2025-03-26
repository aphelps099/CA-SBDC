import SbUtils from "../../../Utils/SbUtils";

const YouTubeFeedItem = ( { post, feedSettings, pluginName } ) => {

    function toTimestamp(strDate){
        var datum = Date.parse(strDate);
        return datum/1000;
    }

    return (
        <>
            <div className="sbsw-follow">
                <a href={post?.account_link} target="_blank" rel="nofollow noopener">
                    <>
                        {SbUtils.printIcon( 'youtube-logo', 'follow-icon' ) }
                        <span>Subscribe</span>
                    </>
                </a>
            </div>
            <div className="sbsw-post-item-inner">
                <div className="sbsw-item-header sbsw-no-avatar">
                    <div className="sbsw-identity">
                        <a href={post.permalink}>
                            <div className={"sbsw-author " + (post.avatar && feedSettings?.postElements.includes('avatar') ? 'has-avatar' : '')}>
                                {
                                    feedSettings?.postElements.includes('avatar') && post.avatar && (
                                        <div className="sbsw-author-avatar">
                                            <img src={post.avatar} alt="" />
                                        </div>
                                    )
                                }
                                {
                                    feedSettings?.postElements.includes('username') && (
                                        <div className="sbsw-author-name">
                                            {post.identity_text}
                                        </div>
                                    )
                                }
                                { 
                                    feedSettings?.postElements.includes('date') && pluginName == 'youtube' && (
                                        <div className="sbsw-date">
                                            { feedSettings?.dateBeforeText + ' ' + SbUtils.printDate(toTimestamp(post?.snippet.publishedAt), feedSettings ) + ' ' + feedSettings?.dateAfterText}
                                        </div>
                                    )
                                }
                            </div>
                        </a>
                        <div className="sbsw-icon">
                            <a href={post.permalink}>
                                { SbUtils.printIcon( 'youtube-logo' ) }
                            </a>
                        </div>
                    </div>
                </div>
                { 
                    feedSettings?.postElements.includes('media') && (
                        <div className="sbsw-item-media">
                            <img src={post?.media} alt="" />
                        </div>
                    )
                }
                { 
                    feedSettings?.postElements.includes('text') && (
                        post.snippet.title && (
                            <div className="sbsw-item-bottom-content">
                                <div className="sbsw-content-text">
                                    <p>{post.snippet.title}</p>
                                </div>
                            </div>
                        )
                    )
                }

                <div className="sbsw-item-footer">
                    <div className="sbsw-item-bottom">
                        { 
                            feedSettings?.postElements.includes('summary') && pluginName == 'youtube' && (
                                <div className="sbsw-item-stats">
                                    <div className="sbsw-item-views">
                                        { SbUtils.printIcon( 'eye' ) }
                                        <span className='sbsw-summary-text'>{post.stats_data.views_count? post.stats_data.views_count : '0'}</span>
                                    </div>
                                    <div className="sbsw-item-likes">
                                        { SbUtils.printIcon( 'heart' ) }
                                        <span className='sbsw-summary-text'>{post.stats_data.likes_count ? post.stats_data.likes_count : '0'}</span>
                                    </div>
                                    <div className="sbsw-item-comments">
                                        { SbUtils.printIcon( 'comment' ) }
                                        <span className='sbsw-summary-text'>{post.stats_data.comments_count ? post.stats_data.comments_count : '0'}</span>
                                    </div>
                                </div>
                            )
                        }
                        <div className="sbsw-item-share">
                            { SbUtils.printIcon( 'share' ) }
                        </div>
                    </div>
                </div>
            </div>
        </>
    )
}

export default YouTubeFeedItem;