import axios from 'axios';

const AjaxRequest = (action, data = {}) => {
	// eslint-disable-next-line no-undef
	const formData = new FormData();

	formData.append('action', action);
	formData.append('nonce', window.sbsw_admin.nonce);

	Object.entries(data).forEach(([key, value]) => {
		formData.append(key, value);
	});

	return axios.post(window.sbsw_admin.ajax_url, formData);
};

export default AjaxRequest;
