import { __ } from '@wordpress/i18n';
import { useState, useMemo, useEffect } from 'react'
import Header from '../components/Header';
import HeaderMobile from '../components/HeaderMobile';
import { useNavigate } from 'react-router-dom';
import LegacyFeed from '../components/lib/LegacyFeed';
import SbTable from '../components/lib/SbTable';
import EmptyState from '../components/lib/EmptyState';
import SbButton from '../components/lib/SbButton';
import AllFeedsContext from '../context/AllFeedsContext.js'

const AllFeeds = () => {
	const navigate = useNavigate();
	const [ready, setReady] = useState(false);
	const [socialWallFeeds, setSocialWallFeeds] = useState(null);
	const sbSWFeeds = useMemo(
		() => ({ socialWallFeeds, setSocialWallFeeds }),
		[socialWallFeeds, setSocialWallFeeds]
	);

	useEffect(() => {
		setSocialWallFeeds(window.sbsw_admin.swFeeds);
		setReady(true);
	}, []);

	return (
		<>
			<Header title={__('Social Wall', 'social-wall')} />
			<HeaderMobile title={__('Social Wall', 'social-wall')} />
			<div className={'sw-p-6 sw-antialiased lg:sw-p-14 sw-all-feeds-page'}>
				<div className={'sw-flex sw-items-center'}>
					<h1 className={'sw-font-semibold sw-text-3xl'}>
						{__('All Feeds', 'social-wall')}
					</h1>

					<SbButton
						onClick={() => navigate('/create-feed')}
						type={'custom'}
						className={'sw-bg-sb-blue hover:sw-bg-sb-blue-hover sw-ml-6 sw-text-sm sw-rounded-md sw-text-white sw-text-xs sw-font-semibold sw-add-new-feed-btn sw-shadow-sb5'}
						content={
							<>
								<div
									className={
										'sw-flex sw-gap-2 sw-items-center'
									}
								>
									<svg
										width="16"
										height="16"
										viewBox="0 0 16 16"
										fill="none"
										xmlns="http://www.w3.org/2000/svg"
									>
										<path
											d="M12.6667 8.66666H8.66668V12.6667H7.33334V8.66666H3.33334V7.33333H7.33334V3.33333H8.66668V7.33333H12.6667V8.66666Z"
											fill="white"
										/>
									</svg>
									<span>{__('Add New', 'social-wall')}</span>
								</div>
							</>
						}
					/>
				</div>

				{ready && (
					<AllFeedsContext.Provider 
						value={{sbSWFeeds}} 
					>
						{ window.sbsw_admin.legacyFeeds.legacy_feed_exists && (
								<LegacyFeed />
						)}
						{ sbSWFeeds.socialWallFeeds.length > 0 && (
								<SbTable />
						)}
						{ sbSWFeeds.socialWallFeeds.length == 0 && ! window.sbsw_admin.legacyFeeds.legacy_feed_exists && (
							<EmptyState />
						)}
					</AllFeedsContext.Provider>
				)}
			</div>
		</>
	);
};

export default AllFeeds;
