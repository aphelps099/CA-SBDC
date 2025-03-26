import { useState, useContext, useEffect } from 'react';
import Spacer from '../../lib/Spacer';
import { __ } from '@wordpress/i18n';
import SbSwitch from '../../lib/SbSwitch';
import SbButton from '../../lib/SbButton';
import SbUtils from '../../../Utils/SbUtils';
import SbSettingsCard from '../../lib/SbSettingsCard';
import { getSwitchBool } from '../../../utils/helper';
import SettingsContext from '../../../context/SettingsContext';
import { ReactComponent as Loader } from '../../../../images/loader.svg';
import { ReactComponent as Checkmark } from '../../../../images/green-checkmark.svg';

const General = () => {
	const { sbSettings, register, watch } = useContext(SettingsContext);
	const [ ajaxLoading, setAjaxLoading ] = useState(false);
	const [ licenseKey, setLicenseKey ] = useState(sbSettings.settings.license_key);
	const [ licenseStatus, setLicenseStatus ] = useState(sbSettings.settings.license_status);

	useEffect(() => {
		const subscription = watch((value, { name, type }) => setLicenseKey(value.license_key));
		return () => subscription.unsubscribe();
	}, [watch]);

	const activateLicense = () => {
		if ( !licenseKey.length ) {
			return;
		}
		setAjaxLoading(true);

		const formData = {
            action : 'sbsw_activate_license',
			license_key : licenseKey
        };

		SbUtils.ajaxPost(
            window.sbsw_admin.ajax_url,
            formData,
            ( data ) => { //Call Back Function
				setAjaxLoading(false);
                if ( data.success == true ) {
					setLicenseStatus('valid')
				}
            }
        )
	}

	const deactivateLicense = () => {
		if ( !licenseKey.length ) {
			return;
		}
		setAjaxLoading(true);

		const formData = {
            action : 'sbsw_deactivate_license',
			license_key : licenseKey
        };

		SbUtils.ajaxPost(
            window.sbsw_admin.ajax_url,
            formData,
            ( data ) => { //Call Back Function
                if ( data.success == true ) {
					setLicenseStatus('inactive')
					setAjaxLoading(false);
				}
            }
        )
	}

	return (
		<>
			<SbSettingsCard
				leftCol={
					<>
						<h2 className={'sw-text-lg sw-font-semibold'}>
							{__('License Key', 'social-wall')}
						</h2>
						<p className={'sw-mt-1 sw-text-sm sw-text-sb-gray'}>
							{__(
								'Your license key provides access to updates and support',
								'social-wall'
							)}
						</p>
					</>
				}
				rightCol={
					<>
						<h2 className={'sw-text-sm'}>
							Your{' '}
							<span className={'sw-font-semibold'}>
								Social Wall
							</span>{' '}
							license is {licenseStatus}!
						</h2>
						<div className={'sw-flex sw-items-start sw-mt-2'} data-license-status={licenseStatus}>
							<div className={'sm:sw-w-1/2'}>
								<input
									placeholder={__(
										'Paste your license key here',
										'social-wall'
									)}
									autoComplete="none"
									type="password"
									className={'sw-license-input sw-w-full sw-shadow-sb6'}
									onChange={(e) => {
										setLicenseKey(e.target.value)
									}}
									{...register('license_key')}
									defaultValue={sbSettings.settings.license_key}
								/>
								{ licenseStatus == 'valid' && (
									<span className='sw-license-active-sign'>
										<Checkmark/>
									</span>
								) }
								<div
									className={
										'sw-flex sw-w-full sw-items-center sw-justify-end sw-gap-3 sw-text-xs sw-mt-2'
									}
								>
									<span className={'sw-cursor-pointer'}>
										{__('Test Connection', 'social-wall')}
									</span>
									<span className={'sw-cursor-pointer'}>
										{__('Recheck License', 'social-wall')}
									</span>
								</div>
							</div>
							<div className={'sw-ml-2'}>
								{ licenseStatus == 'inactive' && (
									<SbButton
									type={'custom'}
									className={'sw-button-blue sw-license-activate-button sw-text-sm sw-shadow-sb5'}
									onClick={() => {
										activateLicense();
									}}
									content={
										<>
											{ ajaxLoading && (
												<Loader className="sw-mr-2" />
											)}
											{__('Activate', 'social-wall')}
										</>
									}
									/>
								)}
								{ (licenseStatus == 'valid' ||  licenseStatus == 'active') && (
									<SbButton
										type={'custom'}
										className={'sw-button-base sw-license-deactivate-button sw-text-sm sw-shadow-sb5'}
										onClick={() => {
											deactivateLicense();
										}}
										content={
											<>
												{ ajaxLoading && (
													<Loader className="sw-mr-2" />
												)}
												{__('Deactivate', 'social-wall')}
											</>
										}
									/>
								)}
							</div>
						</div>
					</>
				}
			/>

			<Spacer />

			<SbSettingsCard
				leftCol={
					<h2 className={'sw-text-lg sw-font-semibold sm:sw-pr-14'}>
						{__(
							'Preserve settings if plugin is removed',
							'social-wall'
						)}
					</h2>
				}
				rightCol={
					<SbSwitch
						checked={getSwitchBool(
							sbSettings.settings.preserve_settings
						)}
						model={'preserve_settings'}
						id={'preserve-settings'}
						helpText={__(
							'This will make sure that all of your feeds and settings are still saved even if the plugin is uninstalled',
							'social-wall'
						)}
					/>
				}
			/>
		</>
	);
};

export default General;
