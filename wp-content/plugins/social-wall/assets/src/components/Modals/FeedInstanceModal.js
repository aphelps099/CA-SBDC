import { __ } from "@wordpress/i18n";
import { useState } from "react";
import SbUtils from "../../Utils/SbUtils";
import Button from "../Common/Button";

const FeedInstanceModal = ( {feed, setOpenInstanceModal, copyFeedShortcode, getFeedPlugins} ) => {

    return (
        <div className='sbsw-fs  sw-bg-white sw-shadow-lg sw-rounded-lg sw-p-6 sw-pt-5'>
            <div className="sw-flex sw-justify-between sw-items-start">
                <div>
                    <h4>{feed.feed_name}</h4>
                    <span
                        className={
                            'sw-text-gray-500 sw-font-normal sw-text-sm sw-feed-plugins sw-mt-1'
                        }
                        dangerouslySetInnerHTML={{__html: getFeedPlugins(feed.feeds) }}
                    >
                    </span>
                </div>
                <button
                    onClick={() => setOpenInstanceModal(false)}
                >
                    {SbUtils.printIcon('close', 'sw-instance-modal-icon')}
                </button>
            </div>
            <div>
                <table className="sw-w-full sw-mt-4 sw-border-zinc-200 sw-border sw-instance-table">
                    <thead className="sw-bg-sb-neutral">
                        <tr className="sw-text-left">
                            <th className="sw-px-5 sw-py-1 sw-font-normal sw-text-xs sw-border-b sw-border-zinc-200">Page</th>
                            <th className="sw-px-5 sw-py-1 sw-font-normal sw-text-xs sw-border-b sw-border-zinc-200">Location</th>
                            <th className="sw-px-5 sw-py-1 sw-font-normal sw-text-xs sw-border-b sw-border-zinc-200">Shortcode</th>
                            <th className="sw-px-5 sw-py-1 sw-font-normal sw-text-xs sw-border-b sw-border-zinc-200"></th>
                        </tr>
                    </thead>
                    <tbody>
                        {feed.location_summary.map((location) => {
                            return(
                                <>
                                    <tr className="even:sw-bg-sb-neutral odd:sw-bg-white">
                                        <td className="sw-py-2 sw-px-5 sw-font-semibold sw-feed-page-name sw-w-2/5">
                                            <a href={location.link}>
                                                {location.page_text}
                                            </a>
                                        </td>
                                        <td className="sw-py-2 sw-px-5">{location.html_location}</td>
                                        <td className="sw-flex sw-py-2 sw-px-5 sw-items-center">
                                            {location.shortcode}
                                            <div
												className={
													'sw-ml-2 sw-bg-white sw-p-2 sw-border sw-border-gray-200 sw-rounded sw-cursor-pointer'
												}
                                                onClick={() => copyFeedShortcode(feed.id)}
											>
												{ SbUtils.printIcon('copy') }
											</div>
                                        </td>
                                        <td className="sw-py-2 sw-px-2">
                                            <a href={location.link} target="_blank">
                                                { SbUtils.printIcon('chevron-right') }
                                            </a>    
                                        </td>
                                    </tr>
                                </>
                            )
                        })}
                    </tbody>
                    <tfoot className="sw-bg-sb-neutral">
                        <tr className="sw-text-left">
                            <th className="sw-px-5 sw-py-1 sw-font-normal sw-text-xs sw-border-t sw-border-zinc-200">Page</th>
                            <th className="sw-px-5 sw-py-1 sw-font-normal sw-text-xs sw-border-t sw-border-zinc-200">Location</th>
                            <th className="sw-px-5 sw-py-1 sw-font-normal sw-text-xs sw-border-t sw-border-zinc-200">Shortcode</th>
                            <th className="sw-px-5 sw-py-1 sw-font-normal sw-text-xs sw-border-t sw-border-zinc-200"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    )
}
export default FeedInstanceModal;