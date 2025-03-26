const SbCard = ({ content, wrapperStyle }) => {
	return (
		<div>
			<div
				className={`sw-bg-white sw-shadow-sb sw-flex sw-p-6 ${
					wrapperStyle ? wrapperStyle : ''
				}`}
			>
				<div className={'sw-w-full'}>{content}</div>
			</div>
		</div>
	);
};

export default SbCard;
