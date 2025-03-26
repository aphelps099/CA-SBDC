import { useContext } from 'react';
import { __ } from '@wordpress/i18n';
import SbButton from '../lib/SbButton';
import SettingsContext from '../../context/SettingsContext';
import { ReactComponent as Loader } from '../../../images/loader.svg';
import { ReactComponent as Checkmark } from '../../../images/checkmark.svg';

const SaveSettingsButton = () => {
	const { isLoading, formSubmitted } = useContext(SettingsContext);

	return (
		<SbButton
			isSubmit={true}
			type={'custom'}
			className={'sw-button-blue sw-text-sm sw-shadow-sb2'}
			content={
				<>
					<div className={'sw-flex sw-items-center'}>
						{isLoading && (
							<div className={'sw-mr-2'}>
								<Loader />
							</div>
						)}
						{formSubmitted && (
							<div className={'sw-mr-2'}>
								<Checkmark />
							</div>
						)}
						<span>{__('Save Changes', 'social-wall')}</span>
					</div>
				</>
			}
		/>
	);
};

export default SaveSettingsButton;
