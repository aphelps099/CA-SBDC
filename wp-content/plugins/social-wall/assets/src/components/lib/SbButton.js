const SbButton = ({ type, text, content, className, onClick, isSubmit }) => {
	switch (type) {
		case 'brand':
			return (
				<button
					type={isSubmit ? 'submit' : 'button'}
					onClick={onClick}
					className={`sw-button-base sw-button-brand ${className}`}
				>
					{text}
				</button>
			);
		case 'blue':
			return (
				<button
					type={isSubmit ? 'submit' : 'button'}
					onClick={onClick}
					className={`sw-button-base sw-button-blue ${className}`}
				>
					{text}
				</button>
			);

		case 'custom':
			return (
				<button
					type={isSubmit ? 'submit' : 'button'}
					onClick={onClick}
					className={`sw-button-base ${className}`}
				>
					{content}
				</button>
			);
		case 'base':
			return (
				<button
					type={isSubmit ? 'submit' : 'button'}
					onClick={onClick}
					className={`sw-button-base ${className}`}
				>
					{text}
				</button>
			);
		default:
			return (
				<button
					type={isSubmit ? 'submit' : 'button'}
					onClick={onClick}
					className={`sw-button-base ${className}`}
				>
					{text}
				</button>
			);
	}
};

export default SbButton;
