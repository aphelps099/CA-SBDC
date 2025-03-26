import { __ } from '@wordpress/i18n';
import ajaxRequest from '../utils/AjaxRequest';
import SbButton from '../components/lib/SbButton';
import { useEffect, useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import SbCard from '../components/lib/SbCard';
import FeedsContext from '../context/FeedsContext';
import AddPluginFeed from '../components/CreateFeed/AddPluginFeed';
import Header from '../components/Header';
import { ReactComponent as BuilderLoaderSVG } from '../../images/builder-loader.svg';

const CreateFeed = () => {
	const navigate = useNavigate();
	const [ready, setReady] = useState(false);
	const [loading, setLoading] = useState(false);
    const [ submitFeed, setSubmitFeed ] = useState( false );
	const [pluginSuccess, setPluginSuccess] = useState({});
	const [pluginsFeeds, setPluginsFeeds] = useState(null);
	const [selectedFeedSourceType, setSelectedFeedSourceType] = useState([]);
	const [selectedFeeds, setSelectedFeeds] = useState({});
	const [pluginModal, setPluginModal] = useState(false);
	const [modalPluginData, setModalPluginData] = useState(false);
	const sbPluginsFeeds = useMemo(
		() => ({ pluginsFeeds, setPluginsFeeds }),
		[pluginsFeeds, setPluginsFeeds]
	);
	const isActive = (id) => {
		return selectedFeedSourceType.includes(id);
	};
	const processCreateFeed = () => {
		// if no feeds are selected then return
		if ( 
			Object.keys(selectedFeeds).length === 0 && 
			Object.getPrototypeOf(selectedFeeds) === Object.prototype ) {
			return;
		}
		let data = {
			nonce: window.sbsw_admin.nonce,
			feeds: JSON.stringify(selectedFeeds)
		}
		setSubmitFeed(true);
		ajaxRequest('sw_create_feed', data).then(
			(response) => {
				if ( !response.data.success ) {
					return;
				}
				const feedId = response.data.data.feed_id;
				// navigate('/feed-editor/' + feedId, {state: {feedCreated: true}});
				window.location = window.sbsw_admin.swAdminPage + '&feed_id=' + feedId;
			}
		);
	}
	useEffect(() => {
		setPluginsFeeds(window.sbsw_admin.pluginsFeeds);
		setReady(true);
	}, []);

	return (
		<>
			<Header title={__('Social Wall', 'social-wall')} />
			<div className={'sw-px-6 sw-pt-6 sw-pb-16 sm:sw-p-6 sw-antialiased lg:sw-p-14'}>
				<div className={'sw-flex sw-flex-col'}>
					<div className='sw-flex sw-justify-between sw-flex-col sm:sw-flex-row'>
						<h1 className={'sw-font-semibold sw-text-3xl'}>
							{__('Create a Social Wall', 'social-wall')}
						</h1>
						<SbButton
							onClick={() => processCreateFeed()}
							type={'custom'}
							className={'sw-bg-sb-blue sw-mt-3 sm:sw-mt-0 sm:sw-ml-6 sw-rounded-md sw-text-white sw-text-sm sw-font-semibold sw-add-new-feed-btn sw-shadow-sb5'}
							content={
								<>
									<div
										className={
											'sw-flex sw-gap-1 sw-items-center'
										}
									>
										<span>{__('Create Wall', 'social-wall')}</span>

										<svg
											width="18"
											height="18"
											viewBox="0 0 20 21"
											fill="none"
											xmlns="http://www.w3.org/2000/svg"
										>
											<path
												d="M8.33339 5.00684L7.15839 6.18184L10.9751 10.0068L7.15839 13.8318L8.33339 15.0068L13.3334 10.0068L8.33339 5.00684Z"
												fill="white"
											/>
										</svg>
									</div>
								</>
							}
						/>
					</div>

					{ready && (
						<FeedsContext.Provider
							value={{ sbPluginsFeeds }}
						>
							<div className={'sw-mt-8'}>
								<SbCard content={
									<AddPluginFeed 
										checkIsActive={isActive} 
										selectedFeedSourceType={selectedFeedSourceType}
										handleSetSelectedFeedSourceType={setSelectedFeedSourceType}
										selectedFeeds={selectedFeeds}
										handleSelectedFeeds={setSelectedFeeds}
										pluginModal={pluginModal}
										handlePluginModal={setPluginModal}
										modalPluginData={modalPluginData}
										handleModalPluginData={setModalPluginData}
										loading={loading}
										handleLoading={setLoading}
										pluginSuccess={pluginSuccess}
										handlePluginSuccess={setPluginSuccess}
									/>
								} />
							</div>
						</FeedsContext.Provider>
					)}
				</div>

				<div
					className={
						'sw-sft-page-buttons sw-absolute sw-bottom-0 sw-left-0 sw-flex sw-w-full sw-border-t sw-border-neutral-300'
					}
				>
					<div
						className={
							'sw-flex sw-items-center sw-justify-between sw-px-6 sw-py-4 sw-w-full lg:sw-px-14'
						}
					>
						<SbButton
							onClick={() => navigate('/')}
							type={'custom'}
							className={'sw-px-6 sw-bg-white'}
							content={
								<>
									<div
										className={
											'sw-flex sw-gap-1 sw-items-center sw-text-sm sw-font-semibold'
										}
									>
										<svg
											width="16"
											height="16"
											viewBox="0 0 21 21"
											fill="none"
											xmlns="http://www.w3.org/2000/svg"
										>
											<path
												d="M13.3416 6.18184L12.1666 5.00684L7.16663 10.0068L12.1666 15.0068L13.3416 13.8318L9.52496 10.0068L13.3416 6.18184Z"
												fill="#141B38"
											/>
										</svg>

										<span>{__('Back', 'social-wall')}</span>
									</div>
								</>
							}
						/>

						<SbButton
							onClick={() => processCreateFeed()}
							type={'custom'}
							className={'sw-bg-sb-blue sw-ml-6 sw-text-sm sw-rounded-md sw-text-white sw-font-semibold sw-add-new-feed-btn'}
							content={
								<>
									<div
										className={
											'sw-flex sw-gap-1 sw-items-center'
										}
									>
										<span>{__('Create Wall', 'social-wall')}</span>

										<svg
											width="18"
											height="18"
											viewBox="0 0 20 21"
											fill="none"
											xmlns="http://www.w3.org/2000/svg"
										>
											<path
												d="M8.33339 5.00684L7.15839 6.18184L10.9751 10.0068L7.15839 13.8318L8.33339 15.0068L13.3334 10.0068L8.33339 5.00684Z"
												fill="white"
											/>
										</svg>
									</div>
								</>
							}
						/>
					</div>
				</div>
			</div>

			{submitFeed && (
				<div class="sb-full-screen-loader">
					<div class="sb-full-screen-loader-logo">
						<div class="sb-full-screen-loader-spinner"></div>
						<div class="sb-full-screen-loader-img">
							<BuilderLoaderSVG/>
						</div>
					</div>
					<div class="sb-full-screen-loader-txt">Loading...</div>
				</div>
			)}
		</>
	);
};

export default CreateFeed;
