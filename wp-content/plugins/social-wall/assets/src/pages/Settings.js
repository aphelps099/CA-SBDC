import { useEffect, useMemo, useState } from 'react';
import { __ } from '@wordpress/i18n';
import { NavLink, Outlet } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import ajaxRequest from '../utils/AjaxRequest';
import SettingsContext from '../context/SettingsContext';
import SaveSettingsButton from '../components/Settings/SaveSettingsButton';
import Header from '../components/Header';
import HeaderMobile from '../components/HeaderMobile';

const Settings = () => {
	const { register, watch, handleSubmit } = useForm();

	const [ready, setReady] = useState(false);
	const [settings, setSettings] = useState(null);
	const [isLoading, setIsLoading] = useState(false);
	const [formSubmitted, setFormSubmitted] = useState(false);

	const sbSettings = useMemo(
		() => ({ settings, setSettings }),
		[settings, setSettings]
	);

	useEffect(() => {
		setSettings(window.sbsw_admin.settings);
		setReady(true);
	}, []);

	return (
		<>
			<Header title={__('Social Wall', 'social-wall')} />
			<HeaderMobile title={__('Social Wall', 'social-wall')} />
			{ready && (
				<SettingsContext.Provider
					value={{ sbSettings, register, watch, isLoading, formSubmitted }}
				>
					<form
						onSubmit={handleSubmit((data) => {
							setIsLoading(true);
							setFormSubmitted(false);

							ajaxRequest('sw_save_settings', data).then(
								(response) => {
									setIsLoading(false);

									if (response.data.success) {
										setFormSubmitted(true);
										setSettings(response.data.data);
									}
								}
							);
						})}
					>
						<div className={'sw-px-4 sw-pt-9 sm:sw-p-14'}>
							<h1 className={'sw-font-semibold sw-text-3xl'}>
								{__('Settings', 'social-wall')}
							</h1>

							<div className={'sw-mt-4 sm:sw-mt-10 '}>
								<div
									className={
										'sw-text-sm sw-font-medium sw-text-center sw-text-sb-gray sw-border-b sw-border-gray-200 sw-text-lg sw-flex sw-flex-col sm:sw-flex-row sw-items-stretch sw-justify-between sm:sw-items-center'
									}
								>
									<ul
										className={
											'sw-flex sw-flex-wrap sw-mb-2 sm:-sw-mb-2 sw-flex-col sm:sw-flex-row sw-settings-tab sw-text-base'
										}
									>
										<li>
											<NavLink
												className={(navData) =>
													navData.isActive
														? 'sw-tab sw-tab-active'
														: 'sw-tab'
												}
												to={'/settings/general'}
											>
												{__('General', 'social-wall')}
											</NavLink>
										</li>
										<li>
											<NavLink
												className={(navData) =>
													navData.isActive
														? 'sw-tab sw-tab-active'
														: 'sw-tab'
												}
												to={'/settings/feeds'}
											>
												{__('Feeds', 'social-wall')}
											</NavLink>
										</li>
										<li>
											<NavLink
												className={(navData) =>
													navData.isActive
														? 'sw-tab sw-tab-active'
														: 'sw-tab'
												}
												to={'/settings/advanced'}
											>
												{__('Advanced', 'social-wall')}
											</NavLink>
										</li>
									</ul>

									<SaveSettingsButton />
								</div>

								<div className={'sw-mt-4'}>
									<Outlet />

									<div
										className={
											'sw-flex sw-items-end sw-justify-end sw-mt-4'
										}
									>
										<SaveSettingsButton />
									</div>
								</div>
							</div>
						</div>
					</form>
				</SettingsContext.Provider>
			)}
		</>
	);
};

export default Settings;
