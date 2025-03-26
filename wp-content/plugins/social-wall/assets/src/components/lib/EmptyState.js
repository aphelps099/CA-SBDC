import React from 'react'
import { ReactComponent as CustomizeFeedType } from '../../../images/customizer-feed-type.svg'
import { ReactComponent as EmbedFeed } from '../../../images/embed-feed.svg'

function EmptyState() {
  return (
    <div>
<div className="cff-empty-state cff-fb-fs">
    <div className="cff-fb-wlcm-content cff-fb-fs">
        <div className="cff-fb-wlcm-inf-1 cff-fb-fs">
            <div className="cff-fb-inf-svg">
                <svg width="13" height="7" viewBox="0 0 13 7" fill="none" xmlns="http://www.w3.org/2000/svg" className="sb-head">
                    <path d="M1 6L5.5 1L11.5 6" stroke="#141B38" stroke-width="2" stroke-linejoin="round"></path>
                </svg>
                <svg width="85" height="62" viewBox="0 0 85 62" fill="none" xmlns="http://www.w3.org/2000/svg" className="sb-shaft">
                    <path d="M84.5 59C63.5 66 4.5 54 1.5 0.5" stroke="#141B38" stroke-width="2" stroke-linejoin="round"></path>
                </svg>
            </div>
            <div className="cff-fb-inf-cnt">
                <div className="cff-fb-inf-num">
                    <span>1</span>
                </div>
                <div className="cff-fb-inf-txt">
                    <h4>Create your Feed</h4>
                    <p className="sb-small-p">Connect accounts and create feeds to include in the wall</p>
                </div>
            </div>
        </div>
        <div className="cff-fb-wlcm-inf-2 cff-fb-fs">
            <div className="cff-fb-inf-cnt">
                <div className="cff-fb-inf-num">
                    <span>2</span>
                </div>
                <div className="cff-fb-inf-txt">
                    <h4>Customize your feed type</h4>
                    <p className="sb-small-p">Choose layouts, color schemes, and more</p>
                </div>
                <div className="cff-fb-inf-img">
                    <CustomizeFeedType/>
                </div>
            </div>
        </div>
        <div className="cff-fb-wlcm-inf-3 cff-fb-fs">
            <div className="cff-fb-inf-cnt">
                <div className="cff-fb-inf-img">
                    <EmbedFeed/>
                </div>
                <div className="cff-fb-inf-num">
                    <span>3</span>
                </div>
                <div className="cff-fb-inf-txt">
                    <h4>Embed your feed</h4>
                    <p className="sb-small-p">Easily add the feed anywhere on your website</p>
                </div>
            </div>
        </div>
    </div>
</div>
    </div>
  )
}

export default EmptyState