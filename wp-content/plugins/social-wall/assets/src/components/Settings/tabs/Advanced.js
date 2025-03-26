import { useContext } from 'react';
import { __ } from '@wordpress/i18n';
import SbSwitch from '../../lib/SbSwitch';
import SbSettingsCard from '../../lib/SbSettingsCard';
import { getSwitchBool } from '../../../utils/helper';
import SettingsContext from '../../../context/SettingsContext';

const Advanced = () => {
	const { sbSettings } = useContext(SettingsContext);

	return (
		<>
			<SbSettingsCard
				leftCol={
					<h2 className={'sw-text-lg sw-font-semibold'}>
						{__('AJAX theme loading fix', 'social-wall')}
					</h2>
				}
				rightCol={
					<SbSwitch
						checked={getSwitchBool(
							sbSettings.settings.ajaxtheme
						)}
						model={'ajaxtheme'}
						id={'ajax-theme-loading'}
					/>
				}
			/>
			<hr />
			<SbSettingsCard
				leftCol={
					<h2 className={'sw-text-lg sw-font-semibold'}>
						{__('Custom Templates', 'social-wall')}
					</h2>
				}
				rightCol={
					<SbSwitch
						checked={getSwitchBool(
							sbSettings.settings.customtemplates
						)}
						model={'customtemplates'}
						id={'custom-templates'}
						helpText={__(
							"The default HTML for the feed can be replaced with custom templates added to your theme's folder. Enable this setting to use these templates. See this guide.",
							'social-wall'
						)}
					/>
				}
			/>
		</>
	);
};

export default Advanced;
