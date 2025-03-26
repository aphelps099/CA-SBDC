const SbSettingsCard = ({ leftCol, rightCol }) => {
	return (
		<div>
			<div className={'sw-bg-white sw-shadow-sb sw-flex sw-pb-4 sw-p-6 sw-flex-col sm:sw-flex-row sw-settings-box sw-relative'}>
				<div className={'sm:sw-w-1/4 sm:sw-pr-4'}>{leftCol}</div>
				<div className={' sm:sw-ml-4 sm:sw-w-3/4'}>{rightCol}</div>
			</div>
		</div>
	);
};

export default SbSettingsCard;
