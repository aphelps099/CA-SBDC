import { __ } from '@wordpress/i18n';
import { useContext, useState } from 'react';
import SbButton from './SbButton';
import SbTooltip from './SbTooltip';
import SbUtils from '../../utils/SbUtils';
import ajaxRequest from '../../utils/AjaxRequest';
import AllFeedsContext from '../../context/AllFeedsContext';

const LegacyFeed = () => {
    
    const [ showLegacyFeeds, setShowLegacyFeeds ] = useState(false);
    const [ showTooltipHelp, setShowTooltipHelp ] = useState(false);

	const navigateToEditor = ( feedId ) => {
		window.location = window.sbsw_admin.swAdminPage + '&feed_id=' + feedId;
		// navigate('/feed-editor/' + feedId);
	}

    return (
        <div className='sbsw-legacy-feeds-container sw-bg-white sw-mt-4 sw-shadow sw-shadow-sb5'>
            <div className="sbsw-legacy-feed-header sw-p-3 sw-px-6 sw-flex sw-justify-between">
                <div className='sw-flex sw-gap-3 sw-items-center'>
                    <h4 className='sw-text-lg sw-font-semibold'>Legacy Feeds</h4>
                    <p>What are Legacy Feeds?</p>
                    <div className='sb-cursor-pointer' onClick={() => setShowTooltipHelp(true)}>
                        { SbUtils.printIcon('info') }
                    </div>
                    { showTooltipHelp && (
                        <div class="sbsw-legacy-tooltip-ctn">
                            <div class="sbsw-tooltip-close" onClick={() => setShowTooltipHelp(false)}>
                                { SbUtils.printIcon('times') }
                            </div>
                            <div class="sbsw-tooltip-content">
                                <p>{ __('Legacy feeds are older feeds from before the version 6 update. You can edit settings for these feeds by using the "Settings" button to the right. These settings will apply to all legacy feeds, just like the settings before version 6, and work in the same way that they used to.', 'social-wall' )}</p>
                                <p>{ __( 'You can also create a new feed, which will now have it\'s own individual settings. Modifying settings for new feeds will not affect other feeds.', 'social-wall')} </p>
                            </div>
                            <div class="sb-pointer">
                                <svg width="21" height="11" viewBox="0 0 21 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8.59642 0.811655C9.39546 -0.135356 10.8545 -0.135357 11.6536 0.811654L20.25 11H0L8.59642 0.811655Z" fill="white"></path>
                                </svg>
                            </div>
                        </div>
                    )}
                </div>
                <div>
                    <button 
                        className='sw-flex sw-gap-2 sw-items-center  sw-bg-sb-neutral sw-font-semibold sw-text-sm hover:sw-bg-white sw-transition sw-legacy-settings-btn'
						onClick={() => navigateToEditor('legacy')}
                    >
                        { SbUtils.printIcon('settings') }
                        { __('Settings', 'social-wall') }
                    </button>
                </div>
            </div>
            {/* The below portions are made to not display but hence I worked on this, so keeping the codes for future purpose */}
            { showLegacyFeeds &&  ( 
                <div className="sbsw-legacy-feed-table">
                    <table>
                        <thead>
                            <tr>
                                <th width="10">Name</th>
                                <th width="80"></th>
                                <th width="10">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Legacy Feed</td>
                                <td></td>
                                <td>
                                <div
                                    className={
                                        'sw-flex sw-items-center sw-gap-1'
                                    }
                                >
                                    <div
                                        data-tip={__(
                                            'Edit',
                                            'social-wall'
                                        )}
                                        data-for={'edit'}
                                        className={
                                            'sw-flex sw-items-center'
                                        }
                                        onClick={() => navigateToEditor( 'legacy' )}
                                    >
                                        <SbTooltip id={'edit'} />
                                        <div
                                            className={
                                                'sw-bg-white sw-p-1.5 sw-border sw-cursor-pointer sw-border-gray-200 sw-rounded sw-text-gray-900 hover:sw-bg-sb-blue-light hover:sw-text-white'
                                            }
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width={14}
                                                height={14}
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </div>
                                    </div>

                                    <div
                                        className={
                                            'sw-flex sw-items-center'
                                        }
                                    >
                                        <div
                                            data-for={'duplicate'}
                                            className={
                                                'sw-bg-sb-neutral sw-p-1.5 sw-border sw-cursor-pointer sw-border-gray-200 sw-rounded sw-text-gray-900'
                                            }
                                        >
                                            <svg width="14" height="14" viewBox="0 0 12 12" xmlns="http://www.w3.org/2000/svg" fill="currentColor">
                                                <path d="M8 0.5H2C1.45 0.5 1 0.95 1 1.5V8.5H2V1.5H8V0.5ZM9.5 2.5H4C3.45 2.5 3 2.95 3 3.5V10.5C3 11.05 3.45 11.5 4 11.5H9.5C10.05 11.5 10.5 11.05 10.5 10.5V3.5C10.5 2.95 10.05 2.5 9.5 2.5ZM9.5 10.5H4V3.5H9.5V10.5Z"/>
                                            </svg>
                                        </div>
                                    </div>

                                    <div
                                        className={
                                            'sw-flex sw-items-center'
                                        }
                                    >
                                        <div
                                            data-for={'delete'}
                                            className={
                                                'sw-bg-sb-neutral sw-p-1.5 sw-border sw-cursor-pointer sw-border-gray-200 sw-rounded sw-text-gray-900'
                                            }
                                        >
                                            <svg width="14" height="14" viewBox="0 0 10 12" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0.99998 10.6667C0.99998 11.4 1.59998 12 2.33331 12H7.66665C8.39998 12 8.99998 11.4 8.99998 10.6667V2.66667H0.99998V10.6667ZM2.33331 4H7.66665V10.6667H2.33331V4ZM7.33331 0.666667L6.66665 0H3.33331L2.66665 0.666667H0.333313V2H9.66665V0.666667H7.33331Z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            )}
            <div className="sbsw-legacy-table-toggler">
                { showLegacyFeeds && (
                    <button onClick={() => setShowLegacyFeeds(true) }>
                        { __('Show Legacy Feeds', 'social-wall') }
                        <svg width="10" height="8" viewBox="0 0 8 6" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0.94 0.226685L4 3.28002L7.06 0.226685L8 1.16668L4 5.16668L0 1.16668L0.94 0.226685Z" fill="#0068A0"/></svg>
                    </button>
                )}
                { showLegacyFeeds && (
                    <button onClick={() => setShowLegacyFeeds(false) }>
                        { __('Hide Legacy Feeds', 'social-wall') }
                        <svg width="11" height="7" viewBox="0 0 11 7" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.325 6.84167L5.5 3.02501L1.675 6.84168L0.5 5.66668L5.5 0.666676L10.5 5.66668L9.325 6.84167Z" fill="#0068A0"></path></svg>
                    </button>
                )}
            </div>
        </div>
    )
}

export default LegacyFeed;