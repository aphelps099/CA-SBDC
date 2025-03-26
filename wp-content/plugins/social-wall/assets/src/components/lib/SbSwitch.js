import { useContext } from 'react';
import SettingsContext from '../../context/SettingsContext';

const SbSwitch = ({ id, checked, helpText, onChange, model }) => {
	const { register } = useContext(SettingsContext);

	return (
		<>
			<label htmlFor={id} className="sb-checkbox">
				<input
					onChange={onChange}
					{...register(model)}
					type="checkbox"
					id={id}
					defaultChecked={checked}
				/>
				<span className="toggle-track">
					<div className="toggle-indicator"></div>
				</span>
			</label>

			<div className={'sm:sw-w-2/3 sw-text-gray-600 sw-text-sm'}>
				{helpText}
			</div>
		</>
	);
};

export default SbSwitch;
