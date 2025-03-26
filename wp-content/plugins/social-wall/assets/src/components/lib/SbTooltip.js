import ReactTooltip from 'react-tooltip';

const sbTooltip = ({ id, className, backGroundColor }) => {
	return (
		<ReactTooltip
			className={`sw-font-semibold ${className}`}
			id={id}
			type="dark"
			effect="solid"
			backgroundColor={backGroundColor ? backGroundColor : '#2C324C'}
			delayHide={100}
		/>
	);
};

export default sbTooltip;
