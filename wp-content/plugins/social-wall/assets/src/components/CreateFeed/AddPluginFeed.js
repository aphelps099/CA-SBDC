import { __ } from '@wordpress/i18n';
import { useState, useContext } from 'react'
import FeedsContext from '../../context/FeedsContext.js'
import ajaxRequest from '../../utils/AjaxRequest';
import { useForm } from 'react-hook-form';
import { ReactComponent as FacebookIcon } from '../../../images/facebook.svg';
import { ReactComponent as InstagramIcon } from '../../../images/instagram.svg';
import { ReactComponent as TwitterIcon } from '../../../images/twitter.svg';
import { ReactComponent as YouTubeIcon } from '../../../images/youtube.svg';
import { ReactComponent as TikTokIcon } from '../../../images/tiktok.svg';
import { ReactComponent as IconPlus } from '../../../images/icon-plus.svg';
import { ReactComponent as FacebookGrayed } from '../../../images/grayed-facebook-logo.svg';
import { ReactComponent as InstagramGrayed } from '../../../images/grayed-instagram-logo.svg';
import { ReactComponent as TwitterGrayed } from '../../../images/grayed-twitter-logo.svg';
import { ReactComponent as YouTubeGrayed } from '../../../images/grayed-youtube-logo.svg';
import { ReactComponent as TikTokGrayed } from '../../../images/grayed-tiktok-logo.svg';
import { ReactComponent as ChevronRight } from '../../../images/chevron-right.svg';
import { ReactComponent as LoaderSVG } from '../../../images/loader.svg';

const AddPluginFeed = ({
	checkIsActive, 
	selectedFeedSourceType, 
	handleSetSelectedFeedSourceType,
	selectedFeeds,
	handleSelectedFeeds,
	pluginModal, 
	handlePluginModal,
	modalPluginData,
	handleModalPluginData,
	loading,
	handleLoading,
	pluginSuccess,
	handlePluginSuccess
}) => {

    const { sbPluginsFeeds } = useContext(FeedsContext);

	const { register, handleSubmit } = useForm();
	
	const handleClick = (id) => {
		if (checkIsActive(id)) {
			handleSetSelectedFeedSourceType(
				selectedFeedSourceType.filter((item) => {
					return item !== id;
				})
			);
		} else {
			const arr = [];
			arr.push(id);
			handleSetSelectedFeedSourceType(selectedFeedSourceType.concat(arr));
		}
	};

	const displayFeedOrOpenPopup = ( plugin ) => {
		if ( !plugin.isPluginActive ) {
			handlePluginModal( true );
			handleModalPluginData( plugin );
			return;
		}
		plugin.hasExpanded = !plugin.hasExpanded;
	}

	const removePluginFromFeed = ( plugin ) => {
		plugin.hasExpanded = false;
		delete selectedFeeds[plugin.id];
	}

    // get the feed plugin icon
    const getPluginIcon = (plugin) => {
        let icon = '';
        switch( plugin ) {
            case 'facebook': 
                icon = <FacebookIcon/>
                break;
            case 'instagram': 
                icon = <InstagramIcon/>
                break;
            case 'twitter': 
                icon = <TwitterIcon/>
                break;
            case 'youtube': 
                icon = <YouTubeIcon/>
                break;
            case 'tiktok': 
                icon = <TikTokIcon/>
                break;
        }
        return icon;
    }

	const getPluginGrayedIcon = (plugin) => {
        let icon = '';
        switch( plugin ) {
            case 'facebook': 
                icon = <FacebookGrayed/>
                break;
            case 'instagram': 
                icon = <InstagramGrayed/>
                break;
            case 'twitter': 
                icon = <TwitterGrayed/>
                break;
            case 'youtube': 
                icon = <YouTubeGrayed/>
                break;
            case 'tiktok': 
                icon = <TikTokGrayed/>
                break;
        }
        return icon;
	}

	const cancelModal = () => {
		pluginSuccess.success = false;
		handlePluginModal(false)
	}

	const modalCancelBtnText = () => {
		let btnText = '';
		if ( pluginSuccess.success ) {
			btnText = __('Close', 'social-wall')
		} else {
			btnText = __('Cancel', 'social-wall');
		}

		return btnText
	}

	const modalInstallBtnText = () => {
		let btnText = '';
		if ( pluginSuccess.success ) {
			btnText = pluginSuccess.message
		} else {
			if ( loading ) {
				btnText = !modalPluginData.isPluginInstalled ? __('Installing', 'social-wall') : __('Activating', 'social-wall');
			} else {
				btnText = !modalPluginData.isPluginInstalled ? __('Install Plugin', 'social-wall') : __('Activate Plugin', 'social-wall');
			}
		}

		return btnText
	}

	const handleSelectedPluginFeeds = (plugin, feed, feedName) => {
		selectedFeeds[plugin] = {
			id: feed,
			feedName: feedName
		};
	}

	return (
		<>
			<h4 className={'sw-font-medium sw-text-xl'}>
				{__('Add feeds to your Social Wall', 'social-wall')}
			</h4>
			<p className='sw-text-xs sw-text-sb-gray-2 sw-mt-1'>
				{ __('Select platforms and feeds you want to add to the Wall.', 'social-wall')}
			</p>

			<div className={'sw-mt-7 sw-grid sw-grid-cols-1 sm:sw-grid-cols-2 lg:sw-grid-cols-3 sw-gap-4'}>
				
					{sbPluginsFeeds.pluginsFeeds.map((plugin) => {
						return (
							<div
								key={plugin.id}
								role="presentation"
								onClick={() => handleClick(plugin.id)}
								className={`sw-relative sw-border sw-border-gray-200 sw-rounded sw-overflow-y-hidden`}
							>
								<div
									className={
										'sw-py-3.5 sw-px-4 sw-flex sw-gap-1.5 sw-items-top'
									}
								>
									{getPluginIcon(plugin.id)}
									<div>
										<h2 className={'sw-text-sm sw-font-semibold'}>
											{plugin.title}
										</h2>
										{plugin.hasExpanded && (
											<p className='sw-text-xs sw-mt-1'>
												{__('Added to Wall', 'social-wall')}
												<button className='sw-underline sw-underline-offset-2 sw-ml-1.5 sw-font-semibold' onClick={() => removePluginFromFeed(plugin)}>
													{__('Remove', 'social-wall')}
												</button>
											</p>
										)}
									</div>
								</div>
								{ !plugin.hasExpanded && (
									<div className='sw-border-gray-200 sw-border-t sw-p-3 sw-bg-slate-50 sw-flex sw-justify-center'>
										<button className='sw-rounded sw-shadow-sb2 sw-border sw-font-semibold sw-w-full sw-p-2 sw-text-xs sw-flex sw-items-center sw-justify-center hover:sw-bg-slate-100 transition-colors' onClick={() => displayFeedOrOpenPopup(plugin)}>
											<IconPlus/>
											<span className='sw-ml-1'>{__('Add to Wall', 'social-wall')}</span>
										</button>
									</div>
								)}
								{ plugin.isPluginActive && plugin.hasExpanded && (
									<div className='sw-h-72	sw-overflow-hidden'>
										<div className={'sw-bg-slate-50 sw-py-2 sw-px-4 sw-flex sw-items-center sw-justify-between sw-border-gray-200 sw-border-t'}>
											<span className='sw-font-bold sw-text-xs'>
												{__('Select Feed', 'social-wall')}
											</span>
											<a href={plugin.builderUrl} className='sw-text-sb-blue sw-font-bold sw-text-xs'>{__('+ New', 'social-wall')}</a>
										</div>
										{plugin.hasFeeds && (
											<div className='sw-p-3 sw-pb-16 sw-overflow-y-scroll sw-h-feed-box-overflow'>
												<ul>
													{plugin.feeds.map(feed => {
														return (
															<li>
																<input id={plugin.id + '-' + feed.id} type="radio" name={plugin.id} onChange={() => handleSelectedPluginFeeds(plugin.id, feed.id, feed.feed_name)} /> 
																<label htmlFor={plugin.id + '-' + feed.id} className='sw-text-xs'>
																	<span  className='sw-ml-2 sw-text-sb-gray-2 sw-text-stone-700'>{feed.feed_name}</span>
																</label>
															</li>
														)
													})}
												</ul>
											</div>
										)}
										{!plugin.hasFeeds && (
											<div className='sw-px-4 sw-py-8 sw-text-center'>
												<p className='sw-text-xs sw-text-sb-gray-2'>
													{ sprintf(
														__('No existing %s feeds found.', 'social-wall'), 
														plugin.title
													)}
													<br/> 
													<a href={plugin.builderUrl} className="sw-underline sw-underline-offset-2">
														{__('Create a new feed', 'social-wall')}
													</a>
													{__(' for it to appear here.', 'social-wall')}
												</p>
											</div>
										)}
									</div>
								)}
							</div>
						);
					})}
				
			</div>

			{pluginModal && (
				<div className="sw-install-plugin-modal">
					<div className="sw-install-plugin-content">
						<div className="sw-plugin-popup sw-bg-white sw-shadow-lg sw-rounded-lg sw-h-48 sw-p-6 sw-pt-5">
							<div className='sw-flex sw-gap-4 sw-items-start'>
								<div className="sw-w-6 sw-pt-1">
									{getPluginGrayedIcon(modalPluginData.id)}
								</div>
								<div>
									<h4 className='sw-text-lg sw-font-semibold'>{!modalPluginData.isPluginInstalled ? 'Install' : 'Activate'} {modalPluginData.title} Plugin</h4>
									<p className='sw-text-sm sw-mt-2'>To add an {modalPluginData.title} Feed to the wall, you need to {!modalPluginData.isPluginInstalled ? 'install' : 'activate'} {modalPluginData.title} plugin first</p>
								</div>
							</div>
							<div className='sw-flex sw-justify-end sw-mt-8 sw-gap-2'>
								<button className='sw-py-2 sw-px-3 sw-border sw-border-sb-gray-3 sw-font-semibold sw-bg-slate-50 sw-shadow-sb4 sw-rounded hover:sw-bg-sb-hover-gray sw-transition' onClick={() => cancelModal()}>
									{modalCancelBtnText()}
								</button>
								<form
									onSubmit={handleSubmit(() => {
										handleLoading(true);
										let data = {
											plugin: modalPluginData.plugin,
											downloadPlugin: modalPluginData.download_plugin,
											installed: modalPluginData.isPluginInstalled
										}
										ajaxRequest('sw_install_plugin', data).then(
											(response) => {
												handleLoading(false);
												if ( response.data.success ) {
													handlePluginSuccess({
														success: true,
														message: response.data.data.msg
													});
													// Make another AJAX request to refresh the walll plugins feed
													ajaxRequest('sw_refresh_wall_plugins', {}).then(
														(response) => {
															if ( response.data.success ) {
																sbPluginsFeeds.setPluginsFeeds(response.data.data.feeds);
															}
														}
													);
												}
											}
										);
									})}
								>
									<button type='submit' className='sw-py-2 sw-px-3 sw-border sw-border-sb-blue sw-font-semibold sw-bg-sb-blue sw-text-white sw-shadow-sb3 sw-rounded sw-flex sw-plugin-install-btn hover:sw-bg-sb-hover-blue sw-transition'>
										{modalInstallBtnText()}
										{loading && (
											<LoaderSVG/>
										)}
										{!loading && !pluginSuccess.success && (
											<ChevronRight/>
										)}
									</button>
								</form>
							</div>
						</div>
					</div>
				</div>
			)}
		</>
	);
};

export default AddPluginFeed;
