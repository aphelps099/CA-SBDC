const Spacer = ({ space }) => {
	switch (space) {
		case 'sm':
			return <div className={'sw-mt-2'} />;
		case 'md':
			return <div className={'sw-mt-4'} />;
		case 'lg':
			return <div className={'sw-mt-6'} />;
		default:
			return <div className={'sw-mt-2'} />;
	}
};

export default Spacer;
