import { __ } from '@wordpress/i18n';
import { useContext, useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import SbButton from './SbButton';
import SbTooltip from './SbTooltip';
import SbUtils from '../../utils/SbUtils';
import ajaxRequest from '../../utils/AjaxRequest';
import AllFeedsContext from '../../context/AllFeedsContext';
import ModalContainer from '../Global/ModalContainer';
import FeedInstanceModal from '../Modals/FeedInstanceModal'

const SbTable = () => {
	const navigate = useNavigate();

	const [ openDeleteModal, setOpenDeleteModal ] = useState(false);
	const [ deleteFeedObj, setDeleteFeedObj ] = useState(false);
	const [ openInstanceModal, setOpenInstanceModal ] = useState(false);
	const [ instanceModalFeed, setInstanceModalFeed ] = useState([]);

    const [ selectedFeeds, setSelectedFeeds ] = useState( [] );
    const [ bulkAction, setBulkAction ] = useState( '' );

	const { sbSWFeeds } = useContext( AllFeedsContext );
	const location = useLocation();

	const bulkActionsList = {
        'false'  : __( 'Bulk Actions', 'social-wall' ),
        'bulk-delete'  : __( 'Delete', 'social-wall' )
    };

    const selectFeed = ( feedId ) => {
        const sFeeds = Object.values( selectedFeeds ),
              feedsList = Object.values( sbSWFeeds.socialWallFeeds );
        if( feedId === 'all' ){
            if( sFeeds.length === feedsList.length ){
                setSelectedFeeds( [] );
            }else{
                let feeds = [];
                feedsList.forEach(feed => {
                    feeds.push( feed.id )
                })
                setSelectedFeeds( feeds )
            }
        }else{
            if( !sFeeds.includes( feedId ) ){
                sFeeds.push(feedId)
            }else{
                sFeeds.splice( sFeeds.indexOf( feedId ), 1 );
            }
            setSelectedFeeds(sFeeds)
        }
    }

	const bulkDelete = () => {
		if ( selectedFeeds.length && bulkAction == 'bulk-delete' ) {
			setOpenDeleteModal(true);
		}
	}

	// refresh the feeds if the user navigates from the editor
	// once after creating the feed
	if ( location.state !== null && location.state.refreshFeeds ) {
		ajaxRequest('sw_get_feeds', {}).then(
			(response) => {
				if ( response.data.success ) {
					sbSWFeeds.setSocialWallFeeds(response.data.data.feeds)
				}
			}
		);
	}

	/**
	 * Copy feed shortcode
	 * @param {interger} feed 
	 * 
	 * @since 2.0
	 */
	const copyFeedShortcode = ( feed ) => {
		const el = document.createElement('textarea');
		el.className = 'sw-cp-clpboard';
		el.value = '[social-wall feed='+ feed +']';
		document.body.appendChild(el);
		el.select();
		document.execCommand('copy');
		document.body.removeChild(el);
		console.log('feed copied');
	}

	// Duplicate a feed and refresh the feeds table
	const duplicateFeed = ( feed ) => {
		let data = {
			feed: feed,
		}
		ajaxRequest('sw_duplicate_feed', data).then(
			(response) => {
				if ( response.data.success ) {
					sbSWFeeds.setSocialWallFeeds(response.data.data.feeds)
				}
			}
		);
	}

	// Delete a feed and refresh the feeds table
	const deleteFeed = ( feed ) => {
		let data = {
			feed: feed,
		}
		ajaxRequest('sw_delete_feed', data).then(
			(response) => {
				if ( response.data.success ) {
					sbSWFeeds.setSocialWallFeeds(response.data.data.feeds)
					setOpenDeleteModal(false);
				}
			}
		);
	}

	// Bulk delete feeds and refresh the feeds table
	const bulkDeleteFeeds = ( feeds ) => {
		let data = {
			feeds: feeds,
		}
		ajaxRequest('sw_bulk_delete_feed', data).then(
			(response) => {
				if ( response.data.success ) {
					sbSWFeeds.setSocialWallFeeds(response.data.data.feeds)
					setOpenDeleteModal(false);
				}
			}
		);
	}

	// Open feed delete modal
	const openFeedDeleteModal = ( feed ) => {
		setOpenDeleteModal(true);
		setDeleteFeedObj(feed);
	}

	// Get feed plugins names
	const getFeedPlugins = ( name ) => {
		let plugins = name.split('+');
		var response = plugins.map(function(plugin, i) {
			return `<span>${plugin}</span>`;
		  }).join('');

		return response;
	}

	const navigateToEditor = ( feedId ) => {
		window.location = window.sbsw_admin.swAdminPage + '&feed_id=' + feedId;
		// navigate('/feed-editor/' + feedId);
	}

	return (
		<div>
			<div
				className={'sw-flex sw-items-center sw-justify-between sw-mt-8'}
			>
				<div className={'sw-flex sw-items-center sw-bulk-actions'}>
					<select 
						className={'sw-select-sm'}
						onChange={(e) => {
							setBulkAction(e.target.value)
						}}
						>
						{
                            Object.keys(bulkActionsList).map( action => {
                                return (
                                    <option key={ action } value={ action } >{ bulkActionsList[action] }</option>
                                )
                            })
                        }
					</select>

					<SbButton
						type={'base'}
						className={'sw-text-sm sw-rounded-none sw-ml-2 hover:sw-bg-slate-50'}
						text={__('Apply', 'social-wall')}
						onClick={() => bulkDelete()}
					/>
				</div>

				<span className={'sw-text-sm'}>
					{sbSWFeeds.socialWallFeeds.length} {__('items', 'social-wall')}
				</span>
			</div>
			<div className="sw-relative sw-overflow-x-auto sw-shadow-md sw-mt-2 sw-feeds-table">
				<table className="sw-w-full sw-text-sm sw-text-left sw-text-gray-500 sb-table">
					<thead className="sw-text-sm sw-text-gray-700 sw-bg-white sw-border-b sw-border-b-neutral-200">
						<tr>
							<th scope="col" className="sw-pl-4 sw-pr-2 sw-py-2">
								<div className="sw-flex sw-items-center">
									<input
										id="checkbox-all"
										type="checkbox"
										className="sw-w-4 sw-h-4 sw-text-blue-600 sw-bg-gray-100 sw-border-gray-100 sw-rounded focus:sw-ring-blue-500 focus:sw-ring-2"
										onChange={ () => {
											selectFeed( 'all' )
										} }
									/>
									<label
										htmlFor="checkbox-all"
										className="sr-only"
									>
										Checkbox
									</label>
								</div>
							</th>
							<th scope="col" className="sw-pr-6 sw-pl-2 sw-py-2 sw-text-xs">
								{__('Name', 'social-wall')}
							</th>
							<th scope="col" className="sw-pr-6 sw-pl-2 sw-py-2 sw-text-xs">
								{__('Shortcode', 'social-wall')}
							</th>
							<th scope="col" className="sw-pr-6 sw-pl-2 sw-py-2 sw-text-xs">
								{__('Instances', 'social-wall')}
							</th>
							<th scope="col" className="sw-pr-6 sw-pl-2 sw-py-2 sw-text-xs">
								{__('Actions', 'social-wall')}
							</th>
						</tr>
					</thead>
					<tbody>
						{sbSWFeeds.socialWallFeeds.map((feed) => {
							return (
								<tr
									key={feed.id}
									className="odd:sw-bg-sb-neutral even:sw-bg-white sw-border-b"
								>
									<td className="sw-w-4 sw-pr-2 sw-pt-6 sw-p-4 sw-align-top">
										<div className="sw-flex sw-items-center">
											<input
												id="sb-table"
												type="checkbox"
												className="sw-w-4 sw-h-4 sw-text-blue-600 sw-bg-gray-100 sw-border-gray-300 sw-rounded focus:sw-ring-blue-500"
												checked={ selectedFeeds.includes( feed.id ) }
												onChange={ () => {
													selectFeed( feed.id )
												} }
											/>
											<label
												htmlFor="sb-table"
												className="sr-only"
											>
												Checkbox
											</label>
										</div>
									</td>
									<td className="sw-pr-6 sw-pl-2 sw-py-4 sw-font-medium sw-text-sb-blue sw-whitespace-nowrap sw-flex sw-flex-col">
										<span className={'sw-cursor-pointer sw-font-semibold'} onClick={() => navigateToEditor(feed.id)} >
											{feed.feed_name}
										</span>
										<span
											className={
												'sw-text-gray-500 sw-font-normal sw-text-xs sw-feed-plugins'
											}
											dangerouslySetInnerHTML={{__html: getFeedPlugins(feed.feeds) }}
										>
										</span>
									</td>
									<td className="sw-pr-6 sw-pl-2 sw-py-4">
										<div
											className={
												'sw-flex sw-items-center sw-text-xs'
											}
											onClick={() => copyFeedShortcode(feed.id)}
										>
											<span>[social-wall feed={feed.id}]</span>
											<div
												className={
													'sw-ml-2 sw-bg-white sw-p-1 sw-border sw-border-gray-200 sw-cursor-pointer'
												}
											>
												{ SbUtils.printIcon('copy') }
											</div>
										</div>
									</td>
									<td className="sw-pr-6 sw-pl-2 sw-py-4 sw-text-xs">
										{__('Used in', 'social-wall')}{' '}
										<span
											className={
												'sw-font-semibold sw-underline sw-cursor-pointer'
											}
											onClick={() => {
												if ( feed.instance_count > 0 ) {
													setOpenInstanceModal(true)
													setInstanceModalFeed(feed)
												}
											}}
										>
											{__(feed.instance_count + ' places', 'social-wall')}
										</span>
									</td>
									<td className="sw-pr-6 sw-pl-2 sw-py-4">
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
												onClick={() => navigateToEditor(feed.id)}
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
												onClick={() => duplicateFeed(feed.id)}
											>
												<div
													data-tip={__(
														'Duplicate',
														'social-wall'
													)}
													data-for={'duplicate'}
													className={
														'sw-bg-white sw-p-1.5 sw-border sw-cursor-pointer sw-border-gray-200 sw-rounded sw-text-gray-900 hover:sw-bg-sb-blue-light hover:sw-text-white'
													}
												>
													<SbTooltip
														id={'duplicate'}
													/>
													<svg width="14" height="14" viewBox="0 0 12 12" xmlns="http://www.w3.org/2000/svg" fill="currentColor">
														<path d="M8 0.5H2C1.45 0.5 1 0.95 1 1.5V8.5H2V1.5H8V0.5ZM9.5 2.5H4C3.45 2.5 3 2.95 3 3.5V10.5C3 11.05 3.45 11.5 4 11.5H9.5C10.05 11.5 10.5 11.05 10.5 10.5V3.5C10.5 2.95 10.05 2.5 9.5 2.5ZM9.5 10.5H4V3.5H9.5V10.5Z"/>
													</svg>
												</div>
											</div>

											<div
												className={
													'sw-flex sw-items-center'
												}
												onClick={() => openFeedDeleteModal(feed)}
											>
												<div
													data-tip={__(
														'Delete',
														'social-wall'
													)}
													data-for={'delete'}
													className={
														'sw-bg-white sw-p-1.5 sw-border sw-cursor-pointer sw-border-red-200 sw-rounded sw-text-red-600 hover:sw-bg-red-600 hover:sw-text-white'
													}
												>
													<SbTooltip id={'delete'} />
													<svg width="14" height="14" viewBox="0 0 10 12" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
														<path d="M0.99998 10.6667C0.99998 11.4 1.59998 12 2.33331 12H7.66665C8.39998 12 8.99998 11.4 8.99998 10.6667V2.66667H0.99998V10.6667ZM2.33331 4H7.66665V10.6667H2.33331V4ZM7.33331 0.666667L6.66665 0H3.33331L2.66665 0.666667H0.333313V2H9.66665V0.666667H7.33331Z"/>
													</svg>
												</div>
											</div>
										</div>
									</td>
								</tr>
							);
						})}
					</tbody>

					<tfoot className="sw-text-sm sw-text-gray-700 sw-bg-gray-50 sw-border-b sw-border-t-neutral-200">
						<tr>
							<th scope="col" className="sw-pl-4 sw-pr-2 sw-py-2">
								<div className="sw-flex sw-items-center">
									<input
										id="checkbox-all"
										type="checkbox"
										className="sw-w-4 sw-h-4 sw-text-blue-600 sw-bg-gray-100 sw-border-gray-100 sw-rounded focus:sw-ring-blue-500 focus:sw-ring-2"
										onChange={ () => {
											selectFeed( 'all' )
										} }
									/>
									<label
										htmlFor="checkbox-all"
										className="sr-only"
									>
										Checkbox
									</label>
								</div>
							</th>
							<th scope="col" className="sw-pr-6 sw-pl-2 sw-py-2 sw-text-xs">
								{__('Name', 'social-wall')}
							</th>
							<th scope="col" className="sw-pr-6 sw-pl-2 sw-py-2 sw-text-xs">
								{__('Shortcode', 'social-wall')}
							</th>
							<th scope="col" className="sw-pr-6 sw-pl-2 sw-py-2 sw-text-xs">
								{__('Instances', 'social-wall')}
							</th>
							<th scope="col" className="sw-pr-6 sw-pl-2 sw-py-2 sw-text-xs">
								{__('Actions', 'social-wall')}
							</th>
						</tr>
					</tfoot>
				</table>
			</div>

			{openDeleteModal && (
				<div className='sw-modal-wrapper'>
					<div className="sw-modal-content">
						{ bulkAction !== 'bulk-delete' && (
							<>
								<h3>Delete "{deleteFeedObj.feed_name}" </h3>
								<p>You are going to delete this feed. You will lose all the feeds with it's settings. Are you sure you want to continue?</p>
							</>
						)}
						{ bulkAction == 'bulk-delete' && (
							<>
								<h3>Delete feeds? </h3>
								<p>You are going to delete these feeds. You will lose all the feeds with it's settings. Are you sure you want to continue?</p>
							</>
						)}
						<div className="sw-action-buttons">
							<button 
								className='sb-red sb-btn-red' 
								onClick={() => 
									bulkAction == 'bulk-delete' ? 
									bulkDeleteFeeds(selectedFeeds) :
									deleteFeed(deleteFeedObj.id)
							}>
								Confirm
							</button>
							<button className='sb-red sb-btn-grey' onClick={() => setOpenDeleteModal(false)}>Cancel</button>
						</div>
					</div>
				</div>
			)}

			{ openInstanceModal && (
				<ModalContainer 
					customClasses="sw-instance-modal"
				>
					<FeedInstanceModal
						feed={instanceModalFeed}
						setOpenInstanceModal={setOpenInstanceModal}
						copyFeedShortcode={copyFeedShortcode}
						getFeedPlugins={getFeedPlugins}
					>
					</FeedInstanceModal>
				</ModalContainer>
			)}
		</div>
	);
};

export default SbTable;
