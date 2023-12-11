import $ from 'jquery';
import { pollLocalStorage, showPicker, submitOAuthForm } from './helpers';
// eslint-disable-next-line import/no-unresolved
import { Token } from './typings/global';
import { GOOGLE_TOKEN_KEY } from './constants';

const { gpgs_settings_plugin_strings: strings } = window;

interface TokenHealthResponse {
	success: boolean;
	data: {
		token_data: Array<{
			tokenIsHealthy: boolean;
			googleEmail: string;
			userId: number;
			userDisplayName: string;
			userEditLink: string;
			belongsToCurrentUser: string;
			connectedFeeds: Array<{
				feed_id: number;
				spreadsheet_id: string;
				form_title: string;
				feed_name: string;
				form_id: number;
				feed_url: string;
			}>;
			legacyToken: boolean;
		}>;
	};
}

interface GPPASpreadsheetResponse {
	success: boolean;
	data: {
		markup: string;
	};
}

/**
 * Adds the "connect button" as rendered during feed creation
 */
function addConnectButton() {
	$('#gpgs_connect_to_google_button').remove();

	$('#gform_setting_connect_button').append(
		`<div id="gpgs_connect_to_google_button" class="">
			<button onclick="return false;" class="gform_settings_button button" id="plugin_connect">
				${strings.connect}
				<span class="spinner spinner_connect"></span>
			</button>
		</div>`
	);

	addButtonClickHandlerPluginConnect();
}

/**
 * Adds the "connect button" as rendered during feed deletion
 */
function addDisconnectButton() {
	$('#gpgs_connect_to_google_button').remove();

	const connectedAsText = strings.connected_to_google;

	const legacyTokenWarning = strings.token_is_legacy
		? `<span id="gpgs_connect_to_google_legacy_warning">${WARNING} ${strings.legacy_token}</span>`
		: '';

	const markup = `<div id="gpgs_connect_to_google_button" class="alert gforms_note_success" style="
	    display: flex;
	    align-items: center;
	">
		<span id="gpgs_connected_to_google">${connectedAsText}</span>
		${legacyTokenWarning}
		<button onclick="return false;" class="gform_settings_button button" id="plugin_disconnect" style="
		    align-self: self-end;
		">
			${strings.disconnect}
			<span class="spinner spinner_connect"></span>
		</button>
	</div>`;

	$('#gform_setting_connect_button').append(markup);

	addButtonClickHandlerPluginDisconnect();
}

function addButtonClickHandlerPluginConnect() {
	removeButtonClickHandlerPluginConnect();
	removeButtonClickHandlerPluginDisconnect();

	$('#plugin_connect').on('click.gpgsConnectButton', function () {
		// add loading spinner
		$('.spinner.spinner_connect').addClass('is-active');

		const state = {
			oauth_random_string: strings.oauth_random_string,
			oauth_validation_token: strings.oauth_validation_token,
			user_id: strings.user_id,
		};

		submitOAuthForm({
			oAuthServiceUrl: `${strings.gwiz_oauth_service_url}/oauth/google`,
			inputs: [
				[
					'redirectUrl',
					`${strings.ajax_url}?action=gpgs_plugin_settings_oauth_callback`,
				],
				['licenseId', strings.gravity_perks_license_id],
				['licenseHash', strings.gravity_perks_license_hash],
				['perkId', strings.slug],
				['siteUrl', strings.site_url],
				['state', JSON.stringify(state)],
			],
		});

		const localStorageKey = `${GOOGLE_TOKEN_KEY}_${strings.oauth_random_string}`;

		pollLocalStorage({
			itemKey: localStorageKey,
			handleItem: (item) => {
				// remove the item so that it's existence doesn't interfere with future polling.
				localStorage.removeItem(localStorageKey);

				const data = JSON.parse(item);

				if (data.success === '0') {
					$('#plugin_connect > span').removeClass('is-active');

					addErrorMessage(data.message);
					return;
				}

				strings.token = data.token;

				// this must come before changing the "id" prop for the button or it will not remove the event handler from the button.
				removeButtonClickHandlerPluginConnect();

				$('#plugin_connect > span')
					.removeClass('spinner_connect')
					.addClass('spinner_disconnect');

				strings.token_is_legacy = '';

				addDisconnectButton();

				renderTokenHealth();

				renderGPPASection();
			},
		});
	});
}

function addErrorMessage(message: string) {
	$('#gf-admin-notices-wrapper').append(
		`<div class="notice notice-error gf-notice"><p><strong>${strings.error_authenticating_with_google} ${message}</strong></p></div>`
	);
}

function addSuccessMessage(message: string) {
	$('#gf-admin-notices-wrapper').append(
		`<div class="notice notice-success gf-notice"><p><strong>${message}</strong></p></div>`
	);
}

function addButtonClickHandlerPluginDisconnect() {
	removeButtonClickHandlerPluginConnect();
	removeButtonClickHandlerPluginDisconnect();

	// handler for the Disconnect button
	$('#plugin_disconnect').on('click.gpgsDisconnectButton', function () {
		if (
			// eslint-disable-next-line no-alert
			!confirm(
				'Are you sure you want to disconnect? Doing so might unintentionally break existing feeds.'
			)
		) {
			return;
		}

		// show spinner
		$('.spinner.spinner_disconnect').addClass('is-active');

		const data = {
			action: 'gpgs_disconnect_plugin',
			_ajax_nonce: strings.nonce,
		};

		jQuery.post(strings.ajax_url, data, function (response) {
			if (response.success) {
				// hide disconnect button, nothing to disconnect
				$(
					'#plugin_disconnect,.tooltip_google_sheet_disconnect'
				).remove();

				addConnectButton();

				// Remove success feedback from Client ID and Client Secret
				$(
					'#gform_setting_client_id .gform-settings-input__container'
				).removeClass(
					'gform-settings-input__container--feedback-success'
				);
				$(
					'#gform_setting_client_secret .gform-settings-input__container'
				).removeClass(
					'gform-settings-input__container--feedback-success'
				);

				renderTokenHealth();

				renderGPPASection();
			}
			// hide spinner
			$('.spinner.spinner_disconnect').removeClass('is-active');
		});
	});
}

function removeButtonClickHandlerPluginConnect() {
	$('#plugin_connect').off('click.gpgsConnectButton');
}

function removeButtonClickHandlerPluginDisconnect() {
	$('#plugin_disconnect').off('click.gpgsDisconnectButton');
}

function checkForOAuthMessage() {
	const u = new URL(window.location.href);
	const p = new URLSearchParams(u.search);

	const message = p.get('gwiz_oauth_message');

	if (!message) {
		return;
	}

	const success = p.get('gwiz_oauth_success');

	const alertClassList = ['alert'];

	if (success) {
		alertClassList.push('gforms_note_success');
	} else {
		alertClassList.push('gforms_note_error');
	}

	$('.gform-settings__content').prepend(
		// @ts-ignore
		`<div class="${alertClassList.join(
			' '
		)}" role="alert">${strings.failed_to_connect_with_error_message
			.replace('%1$s', strings.plugin_title)
			.replace('%2$s', message)}</div>`
	);
}

function disableConnectButtonDueToMissingLicense() {
	$('#plugin_connect').prop('disabled', true);
	$('#plugin_connect').after(
		`<p style="align-self: center;margin: 0 0 0 1rem;">${strings.connecting_requires_valid_license}</p>`
	);

	$('#gpgs_connect_to_google_button').css('display', 'flex');
}

function createIcon(url: string, text: string = '') {
	return `<span class="gpgs_status_icon" style="background-image: url('${url}');">${text}</span>`;
}

const TOKEN_HEALTHY = createIcon(
	`${strings.site_url}/wp-content/plugins/gp-google-sheets/assets/green-checkmark.svg`,
	'Connected'
);

const TOKEN_UNHEALTHY = createIcon(
	`${strings.site_url}/wp-content/plugins/gp-google-sheets/assets/red-x.svg`,
	'Disconnected'
);

const WARNING = createIcon(
	`${strings.site_url}/wp-content/plugins/gp-google-sheets/assets/warning.svg`,
	'Warning'
);

async function renderGPPASection() {
	$('#gp-google-sheets-add-spreadsheet').off('.gpgsGppaSection');

	if (!strings.token) {
		$(
			'#gform-settings-section-populate-anything-integration > .gform-settings-panel__content'
		)
			.children()
			.remove();

		$(
			'#gform-settings-section-populate-anything-integration > .gform-settings-panel__content'
		).append(
			`<div class="gpgs_token_health_error">
				<p>${strings.gppa_section_no_global_token}</p>
			</div>`
		);

		return;
	}

	const data = {
		action: 'gpgs_get_spreadsheets_markup',
		_ajax_nonce: strings.nonce,
	};

	const resp: GPPASpreadsheetResponse = await jQuery.get(
		strings.ajax_url,
		data
	);

	$('#gform-settings-section-populate-anything-integration').show();

	if (!resp.success || !resp.data.markup) {
		$(
			'#gform-settings-section-populate-anything-integration > .gform-settings-panel__content'
		)
			.children()
			.remove();

		$(
			'#gform-settings-section-populate-anything-integration > .gform-settings-panel__content'
		).append(
			`<div class="gpgs_token_health_error">
				<p>${strings.gppa_section_error_fetching}</p>
			</div>`
		);

		return;
	}

	$(
		'#gform-settings-section-populate-anything-integration > .gform-settings-panel__content'
	)
		.children()
		.remove();

	$(
		'#gform-settings-section-populate-anything-integration > .gform-settings-panel__content'
	).append(resp.data.markup);

	window.gform_initialize_tooltips();

	$('#gp-google-sheets-add-spreadsheet').on(
		'click.gpgsGppaSection',
		(event) => {
			event.preventDefault();

			showPicker({
				token: strings.token as unknown as Token,
				showErrorMessage: addErrorMessage,
				onDataFound: () => {
					// Refresh the page to show the new sheet.
					window.location.reload();
				},
				strings,
			});
		}
	);
}

async function renderTokenHealth() {
	const data = {
		action: 'gpgs_token_health',
		_ajax_nonce: strings.nonce,
	};

	const resp: TokenHealthResponse = await jQuery.get(strings.ajax_url, data);

	if (!resp.success) {
		$('#gform_setting_token_health_placeholder').children().remove();

		$('#gform_setting_token_health_placeholder').append(
			`<div class="gpgs_token_health_error">
				<p>${strings.token_health_error_fetching}</p>
			</div>`
		);

		return;
	}

	if (resp.data.token_data.length === 0) {
		$('#gform_setting_token_health_placeholder').children().remove();

		$('#gform_setting_token_health_placeholder').append(
			`<div class="gpgs_token_health_error">
				<p>${strings.no_connected_tokens}</p>
			</div>`
		);

		return;
	}

	resp.data.token_data.sort((a, b) => {
		if (a.userDisplayName === null) {
			return 1;
		}

		if (b.userDisplayName === null) {
			return -1;
		}

		return 0;
	});

	window.gpgsToggleTokenFeeds = function (el) {
		const $el = $(el);
		const $tr = $el.closest('tr');

		$tr.toggleClass('gpgs-token-feeds-visible');

		if ($tr.hasClass('gpgs-token-feeds-visible')) {
			$el.text('Hide Feeds');
		} else {
			$el.text('Show Feeds');
		}

		return false;
	};

	const table =
		/*html*/
		`
		<table class="gform-table gform-table--responsive gform-table--no-outer-border gform-table--license-ui">
			<thead>
				<tr>
				  <th scope="user_display_name">User</th>
				  <th scope="email">Google Account Email</th>
				  <th scope="connected_feeds">Connected Feeds</th>
				  <th scope="is_healthy" class="gpgs_token_health_status">Status</th>
				  <th scope="reconnect"></th>
				</tr>
	  		</thead>
			<tbody>
				${resp.data.token_data
					.map(
						({
							tokenIsHealthy,
							googleEmail,
							connectedFeeds,
							userDisplayName,
							userEditLink,
							belongsToCurrentUser,
							legacyToken,
						}) => {
							let tokenHealthText = TOKEN_HEALTHY;
							let reconnectButton = '';

							let classes = '';

							if (legacyToken) {
								reconnectButton = `${WARNING} ${strings.legacy_token}`;
							}

							if (!tokenIsHealthy) {
								tokenHealthText = TOKEN_UNHEALTHY;

								if (belongsToCurrentUser) {
									reconnectButton = /*html*/ `<button id="gpgs_reconnect_token" class="button button-secondary" onclick="return false;">Reconnect</button>`;
									classes =
										'gpgs_unhealthy_token_reconnect_row';
								}
							}

							let userCol = `<td scope="row" class="gpgs_token_health_user_display_name"><a href="${userEditLink}">${userDisplayName}</a></td>`;

							if (userDisplayName === null) {
								userCol =
									'<td scope="row" class="gpgs_token_health_user_display_name">–</td>';
							}

							/*html*/
							return `
							<tr class=${classes}>
								${userCol}
    							<td scope="row" class="gpgs_token_health_google_email">${
									googleEmail || ''
								}</td>
    							<td scope="row" class="gpgs_token_health_connected_feeds">
									${
										connectedFeeds?.length
											? `<a class="gpgs_token_toggle_feeds" onClick="gpgsToggleTokenFeeds(this);event.preventDefault();" href="#">Show Feeds</a> (${connectedFeeds.length})`
											: ''
									}
								</td>
								<td scope="row" class="gpgs_token_health_status">${tokenHealthText}</td>
								<td scope="row" class="gpgs_token_health_reconnect_button">${reconnectButton}</td>
    						</tr>
							<tr class="gpgs-token-feeds gpgs_border_top">
								<td colspan="5" class="gpgs_light_grey_background">
									<table class="gform-table gform-table--responsive gform-table--no-outer-border gform-table--license-ui gpgs_light_grey_background gpgs_token_connected_feed_table">
										<thead class="gpgs_border_bottom">
											<tr>
												<th>Form</th>
												<th>Feed</th>
											</tr>
										</thead>
									<tbody>
										${connectedFeeds
											.filter(
												({
													form_title: formTitle,
													feed_name: feedName,
												}) => {
													return (
														formTitle && feedName
													);
												}
											)
											.map(
												({
													form_title: formTitle,
													feed_name: feedName,
													feed_url: feedUrl,
													form_id: formId,
												}) => {
													/*html*/
													return `
														<tr class="gpgs_token_connected_feed_list_row">
															<td><a href="${strings.admin_url}?page=gf_edit_forms&id=${formId}" target="_blank" rel=”noopener” rel=”noreferrer”>${formTitle}</a></td>
															<td><a href="${feedUrl}" target="_blank" rel=”noopener” rel=”noreferrer”>${feedName}</a></td>
														</tr>
													`;
												}
											)
											.join('')}
									</tbody>
									</table>
								</td>
							</tr>
						`;
						}
					)
					.join('')}
  			</tbody>
		</table>
	`;

	$('#gform_setting_token_health_placeholder').children().remove();

	$('#gform_setting_token_health_placeholder').append(table);

	tokenHealthReconnectEventHandler();
}

function tokenHealthReconnectEventHandler() {
	$('#gpgs_reconnect_token').off('click.gpgsReconnectButton');

	$('#gpgs_reconnect_token').on('click.gpgsReconnectButton', () => {
		const state = {
			// namepsace the random string with the user id so that it is unique per user and doesn't conflict with the global one.
			oauth_random_string: `${strings.oauth_random_string}_${strings.user_id}`,
			oauth_validation_token: strings.oauth_validation_token,
			user_id: strings.user_id,
		};

		submitOAuthForm({
			oAuthServiceUrl: `${strings.gwiz_oauth_service_url}/oauth/google`,
			inputs: [
				[
					'redirectUrl',
					`${strings.ajax_url}?action=gpgs_plugin_settings_reconnect_oauth_callback`,
				],
				['licenseId', strings.gravity_perks_license_id],
				['licenseHash', strings.gravity_perks_license_hash],
				['perkId', strings.slug],
				['siteUrl', strings.site_url],
				['state', JSON.stringify(state)],
			],
		});

		const localStorageKey = `${GOOGLE_TOKEN_KEY}_${strings.oauth_random_string}_${strings.user_id}`;

		pollLocalStorage({
			itemKey: localStorageKey,
			handleItem: (item) => {
				// remove the item so that it's existence doesn't interfere with future polling.
				localStorage.removeItem(localStorageKey);

				const data = JSON.parse(item);

				if (data.success === '0') {
					addErrorMessage(data.message);
					return;
				}

				const $reconnectedRow = $(
					'.gpgs_unhealthy_token_reconnect_row'
				);
				$reconnectedRow
					.find('.gpgs_token_health_status')
					.text(TOKEN_HEALTHY);
				$reconnectedRow
					.find('.gpgs_token_health_reconnect_button')
					.children()
					.remove();

				addSuccessMessage('Successfully reconnected to Google Sheets!');
			},
		});
	});
}

function registerHandlers() {
	$(document).ready(function () {
		/**
		 * if no token or license key, disable the connect button
		 * leave it enabled in the case that there is a token but no license key
		 * so that users can still disconnect as this should not require a valid license key.
		 */
		if (!strings.token) {
			addConnectButton();

			if (!strings.gravity_perks_license_id) {
				disableConnectButtonDueToMissingLicense();
			}
		} else {
			addDisconnectButton();
		}

		checkForOAuthMessage();

		renderTokenHealth();
		renderGPPASection();
	});
}

registerHandlers();
