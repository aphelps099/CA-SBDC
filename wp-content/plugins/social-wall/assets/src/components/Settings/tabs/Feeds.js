import { __, sprintf } from '@wordpress/i18n';
import HTMLReactParser from "html-react-parser";
import SbSettingsCard from '../../lib/SbSettingsCard';
import Spacer from '../../lib/Spacer';
import SbUtils from '../../../Utils/SbUtils';
import { useState, useContext } from 'react';
import SettingsContext from '../../../context/SettingsContext';

const Feeds = () => {
	const { register } = useContext(SettingsContext);

	const [ gdprOption, setGdprOption ] = useState( window.sbsw_admin.settings.gdpr );
	const [ gdprToolTip, setGdprToolTip ] = useState( false );

	const toggleGdprTooltip = () => {
		setGdprToolTip( ! gdprToolTip );
	}

	return (
		<>
			<SbSettingsCard
				leftCol={
					<>
						<h2 className={'sw-text-lg sw-font-semibold'}>
							{__('Caching', 'social-wall')}
						</h2>
					</>
				}
				rightCol={
					<>
						<div className={'sw-flex sw-flex-col sm:sw-flex-row sm:sw-items-center sw-gap-3 sm:gap-0'}>
							<span className={'sw-text-sm'}>
								{__('Check for new posts', 'social-wall')}
							</span>
							<select
								{...register('cache_cron_interval')}
								id="cache_cron_interval"
								defaultValue={
									window.sbsw_admin.settings.cache_cron_interval
								}
								className="sw-select sm:sw-ml-4 sw-w-52"
							>
								<option defaultValue={'30mins'} value="30mins">
									{__('Every 30 minutes', 'social-wall')}
								</option>
								<option value="1hour">
									{__('Every hour', 'social-wall')}
								</option>
								<option value="12hours">
									{__('Every 12 hours', 'social-wall')}
								</option>
								<option value="24hours">
									{__('Every 24 hours', 'social-wall')}
								</option>
							</select>

							<button
								className={'sw-button-base sm:sw-ml-2 sw-text-sm sw-font-medium sw-button-cache-clear'}
								onClick={() => {
									SbUtils.clearExistingCache();
								}}
							>
								<svg
									width="20"
									height="20"
									viewBox="0 0 20 20"
									fill="none"
									xmlns="http://www.w3.org/2000/svg"
								>
									<path
										d="M15.8333 6.66665L12.4999 9.99998H14.9999C14.9999 11.3261 14.4731 12.5978 13.5355 13.5355C12.5978 14.4732 11.326 15 9.99992 15C9.16658 15 8.35825 14.7916 7.66658 14.4166L6.44992 15.6333C7.51083 16.3085 8.74237 16.667 9.99992 16.6666C11.768 16.6666 13.4637 15.9643 14.714 14.714C15.9642 13.4638 16.6666 11.7681 16.6666 9.99998H19.1666L15.8333 6.66665ZM4.99992 9.99998C4.99992 8.6739 5.5267 7.40213 6.46438 6.46445C7.40207 5.52676 8.67384 4.99998 9.99992 4.99998C10.8333 4.99998 11.6416 5.20831 12.3333 5.58331L13.5499 4.36665C12.489 3.69144 11.2575 3.33296 9.99992 3.33331C8.23181 3.33331 6.53612 4.03569 5.28587 5.28593C4.03563 6.53618 3.33325 8.23187 3.33325 9.99998H0.833252L4.16658 13.3333L7.49992 9.99998"
										fill="#141B38"
									/>
								</svg>
								<span className={'sw-ml-2'}>
									{__('Clear All Caches', 'social-wall')}
								</span>
							</button>
						</div>
					</>
				}
			/>
			<hr />
			
			<SbSettingsCard
				leftCol={
					<>
						<h2 className={'sw-text-lg sw-font-semibold'}>
							{__('GDPR', 'social-wall')}
						</h2>
					</>
				}
				rightCol={
					<>
						<div className={'sw-flex sw-flex-col sm:sw-flex-col sm:sw-items-start sw-gap-3 sm:gap-0'}>
							<select
								{...register('gdpr')}
								id="gdpr"
								defaultValue={
									window.sbsw_admin.settings.gdpr
								}
								className="sw-select sw-w-96"
								onChange={(e) => {
									setGdprOption(e.target.value);
								}}
							>
								<option defaultValue={'automatic'} value="automatic">
									{__('Automatic', 'social-wall')}
								</option>
								<option value="yes">
									{__('Yes', 'social-wall')}
								</option>
								<option value="no">
									{__('No', 'social-wall')}
								</option>
							</select>
							{ ! window.sbsw_admin.settings.gdpr_plugin_detected && (
								<div className={'sw-w-2/3 sw-text-slate-500 sw-leading-6'}>
									{ HTMLReactParser(sprintf(__('No GDPR consent plugin detected. Install a compatible <a href="%s" target="_blank" class="sw-underline">GDPR consent plugin</a>, or manually enable the setting to display a GDPR compliant version of the feed to all visitors.', 'social-wall' ), 'https://smashballoon.com/doc/instagram-feed-gdpr-compliance/?instagram')) }
								</div>
							)}
							
							{ window.sbsw_admin.settings.gdpr_plugin_detected && gdprOption === 'yes' && (
								<div className={'sw-w-2/3 sw-text-slate-500 sw-leading-6'}>
									{ __( 'No requests will be made to third-party websites. To accommodate this, some features of the plugin will be limited.', 'social-wall')}
									<span className={'sw-font-semibold sw-ml-2 sw-underline sw-mt-1 sw-cursor-pointer'} onClick={() => toggleGdprTooltip()}>{ __( 'What will be limited?', 'social-wall' ) }</span>
								</div>
							)}
							
							{ window.sbsw_admin.settings.gdpr_plugin_detected && gdprOption === 'no' && (
								<div className={'sw-w-2/3 sw-text-slate-500 sw-leading-6'}>
									{ __('The plugin will function as normal and load images and videos directly from Instagram', 'social-wall') }
								</div>
							)}

							{ window.sbsw_admin.settings.gdpr_plugin_detected && gdprOption === 'automatic' && (
								<div class="sb-gdpr-active">
									<div className={'sw-flex sw-flex-row'}>
										<span class="gdpr-active-icon">
											<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M10.0003 1.66667C5.41699 1.66667 1.66699 5.41667 1.66699 10C1.66699 14.5833 5.41699 18.3333 10.0003 18.3333C14.5837 18.3333 18.3337 14.5833 18.3337 10C18.3337 5.41667 14.5837 1.66667 10.0003 1.66667ZM8.33366 14.1667L4.16699 10L5.34199 8.82501L8.33366 11.8083L14.6587 5.48334L15.8337 6.66667L8.33366 14.1667Z" fill="#59AB46"></path>
											</svg>
										</span>
										<span className={'sw-flex sw-flex-col'}>
											<span className={'sw-font-semibold sw-ml-2'}>
												{window.sbsw_admin.settings.gdpr_plugin_detected}
											</span>
											<span className={'sw-font-semibold sw-ml-2 sw-underline sw-mt-1 sw-cursor-pointer'} onClick={() => toggleGdprTooltip()}>What will be limited?</span>
										</span>
									</div>
								</div>
							)}
							
							{ gdprOption !== 'no' && gdprToolTip && (
								<div class="sb-gdpr-info-tooltip">
									<span class="sb-gdpr-info-headline">Features that would be disabled or limited include: </span> 
									<ul class="sb-gdpr-info-list">
										<li>Only local images (not from Instagram's CDN) will be displayed in the feed.</li>
										<li>Placeholder blank images will be displayed until images are available.</li>
										<li>To view videos, visitors will click a link to view the video on Instagram.</li>
										<li>Carousel posts will only show the first image in the lightbox.</li>
										<li>The maximum image resolution will be 640 pixels wide in the lightbox.</li>
									</ul>
								</div>
							)}
						</div>
					</>
				}
			/>

			<Spacer />

			<SbSettingsCard
				leftCol={
					<>
						<h2 className={'sw-text-lg sw-font-semibold'}>
							{__('Custom CSS', 'social-wall')}
						</h2>
					</>
				}
				rightCol={
					<>
						<textarea
							{...register('custom_css')}
							placeholder={__(
								'Enter your custom CSS here',
								'social-wall'
							)}
							rows={4}
							defaultValue={window.sbsw_admin.settings.custom_css}
							className={
								'sw-w-full sw-border sw-border-gray-300 sw-p-3 sw-text-base'
							}
						/>
					</>
				}
			/>

			<hr />

			<SbSettingsCard
				leftCol={
					<>
						<h2 className={'sw-text-lg sw-font-semibold'}>
							{__('Custom Javascript', 'social-wall')}
						</h2>
					</>
				}
				rightCol={
					<>
						<textarea
							{...register('custom_js')}
							placeholder={__(
								'Enter your custom Javascript here',
								'social-wall'
							)}
							rows={4}
							defaultValue={window.sbsw_admin.settings.custom_js}
							className={
								'sw-w-full sw-border sw-border-gray-300 sw-p-3 sw-text-base'
							}
						/>
					</>
				}
			/>
		</>
	);
};

export default Feeds;
