import $ from 'jquery';
import { addFilter } from '@wordpress/hooks';
// eslint-disable-next-line prettier/prettier
import type { Token, OAuthResponseData } from './typings/global';
import { pollLocalStorage, showPicker, submitOAuthForm } from './helpers';
import {
	addButtonClickHandlerInsert,
	addButtonClickHandlerDisconnect,
	disableColumnMappingSection,
	enableColumnMappingSection,
} from './settings-feed-shared';
import { GOOGLE_TOKEN_KEY } from './constants';

interface SpreadSheetSelectedResponse {
	success: boolean;
	data: {
		spreadsheetLinkMarkup: string;
		spreadsheetName: string;
		sheets: Array<{ title: string; id: string }>;
	};
}
interface SheetSelectedResponse {
	success: boolean;
	data: {
		controlsHTML: string;
		spreadsheetLinkMarkup: string;
	};
}

const { gpgs_settings_strings: strings } = window;

/**
 * Register handlers for all buttons, radio inputs, etc.
 */
$(function () {
	// TODO do not do this lol.
	if (strings.token) {
		setGoogleAuthToken(strings.token);
	}

	addButtonClickHandlerInsert({
		nonce: strings.nonce,
		formId: strings.form_id,
		feedId: strings.feed_id,
		ajaxUrl: strings.ajax_url,
	});

	addButtonClickHandlerDisconnect({
		nonce: strings.nonce,
		formId: strings.form_id,
		feedId: strings.feed_id,
		ajaxUrl: strings.ajax_url,
	});

	addRadioChangeHandlerSelect();

	hideSheetSelect();

	addOAuthSpinner();

	if (
		$('#gform_setting_google_sheet_url_field').find('.gforms_note_success')
			.length
	) {
		//  If the page is ready and a Google Sheet link was successfully loaded, enable editing of column mapping.
		enableColumnMappingSection();
	} else {
		//Otherwise, we are creating a new feed and this should be disabled until the user selects a sheet.
		disableColumnMappingSection();
	}
});

addFilter(
	'i18n.gettext',
	'gp-google-sheets/override-generic-map-column-labels',
	(translation: string, text: string, domain: string) => {
		if (text === 'Select a Field') {
			return strings.select_a_column;
		}

		if (text === 'Add Custom Key') {
			return strings.add_new_column;
		}

		return translation;
	}
);

/**
 * Hides the sheet select component
 */
function hideSheetSelect() {
	$('#gform_setting_google_sheet_id_selector').hide();
}

/**
 * Shows the sheet select component
 */
function showSheetSelect() {
	$('#gform_setting_google_sheet_id_selector').show();
}

function openOAuthTab() {
	const url = `${strings.gwiz_oauth_service_url}/oauth/google`;

	const state = {
		oauth_random_string: strings.oauth_random_string,
		oauth_validation_token: strings.oauth_validation_token,
		user_id: strings.user_id,
	};

	submitOAuthForm({
		oAuthServiceUrl: url,
		inputs: [
			['licenseId', strings.gravity_perks_license_id],
			['licenseHash', strings.gravity_perks_license_hash],
			[
				'redirectUrl',
				`${strings.ajax_url}?action=gpgs_feed_settings_oauth_callback`,
			],
			['perkId', strings.slug],
			['siteUrl', strings.site_url],
			['state', JSON.stringify(state)],
		],
	});

	const oauthDataLocalStorageKey = `${GOOGLE_TOKEN_KEY}_${strings.oauth_random_string}`;

	/**
	 * Start checking local storage for the Google Token.
	 * This way we know when the user has finished oauthing so that we can
	 * store the token so that it will be saved when the feed is created.
	 */
	pollLocalStorage({
		itemKey: oauthDataLocalStorageKey,
		handleItem: (item) => {
			// remove the item from local storage to prevent it from interfering with future polling.
			localStorage.removeItem(oauthDataLocalStorageKey);

			const data: OAuthResponseData = JSON.parse(item);

			if (data.success === '0' || !data.token) {
				addErrorMessage(data.message);
				return;
			}

			setGoogleAuthToken(data.token);

			showPicker({
				token: data.token,
				showErrorMessage: addErrorMessage,
				onDataFound: ({ sheet_url: sheetUrl }) => {
					if (sheetUrl) {
						handleGoogleSpreadsheetURLUpdate(sheetUrl);
					}

					hideOAuthSpinner();
				},
				strings,
			});
		},
	});
}

function addErrorMessage(message: string) {
	$('#gf-admin-notices-wrapper').append(
		`<div class="notice notice-error gf-notice"><p><strong>${strings.error_authenticating_with_google} ${message}</strong></p></div>`
	);
}

function replaceSpreadsheetLinkMarkup(html: string) {
	// Hide the select/create radio buttons
	$(
		'#gform_setting_google_sheet_url_field span.gform-settings-input__container'
	).remove();

	// Remove any existing links
	$('#gform_setting_google_sheet_url_field > p').remove();

	// Add the link to the spreadsheet
	$('#gform_setting_google_sheet_url_field').append(html);
}

function handleGoogleSpreadsheetURLUpdate(googleSheetUrl: string) {
	const url = googleSheetUrl.split('?')[0];

	// Update the hidden field with the sheet URL
	(document.getElementById('google_sheet_url') as HTMLInputElement).value =
		url;

	(document.getElementById('sheet_was_picked') as HTMLInputElement).value =
		'1';

	// Get the columns out of the sheet, populate field map
	const postData = {
		action: 'gpgs_select_spreadsheet',
		_ajax_nonce: strings.nonce,
		id: strings.form_id,
		feed_id: strings.feed_id,
		sheet_url: url,
		sheet_was_picked: '1',
	};

	jQuery.get(
		strings.ajax_url,
		postData,
		(response: SpreadSheetSelectedResponse) => {
			if (!response.success) {
				return;
			}

			replaceSpreadsheetLinkMarkup(response.data.spreadsheetLinkMarkup);

			// Add the spreadsheet's sheets to the "Sheet" select
			const { sheets, spreadsheetName } = response.data;

			strings.spreadsheetName = spreadsheetName;

			for (const sheet of sheets) {
				$('#google_sheet_id_selector').append(
					`<option label="${sheet.title}" value="${sheet.id}" id="gpgs_selected_sheet_${sheet.id}"></option>`
				);
			}

			showSheetSelect();
			addSheetSelectorEventHandler();

			// Auto select the first sheet if there is only one
			if (sheets.length === 1) {
				$(`#gpgs_selected_sheet_${sheets[0].id}`).attr(
					'selected',
					'selected'
				);

				// trigger the change on the <select/> so that the event.target is correct.
				// if triggering directly on the <option/> the event.target is the <option/> and not the <select/>
				// due to event bubbling.
				$('#google_sheet_id_selector').trigger('change');
			}
		}
	);
}

function addSheetSelectorEventHandler() {
	// remove the event handler if already bound so that we don't end up with multiple listeners
	$('#google_sheet_id_selector').off('change.gpgsSheetSelected');

	// bind the new handler
	$('#google_sheet_id_selector').on('change.gpgsSheetSelected', (event) => {
		disableColumnMappingSection();

		const sheetId = $(event.target).val();

		// If no sheetId, then the default "Select a Sheet" option has been selected which does not have a any sheet associated with it.
		if (!sheetId) {
			return;
		}

		const options = $(event.target).find('option');
		let sheetName = '';

		options.each((index, $option) => {
			if ($option.value === sheetId) {
				sheetName = $option.label;
			}
		});

		(document.getElementById('google_sheet_id') as HTMLInputElement).value =
			sheetId as string;

		// Get the columns out of the sheet, populate field map
		const postData = {
			action: 'gpgs_select_sheet',
			_ajax_nonce: strings.nonce,
			id: strings.form_id,
			feed_id: strings.feed_id,
			sheet_url: $('#google_sheet_url').val(),
			spreadsheet_name: strings.spreadsheetName,
			sheet_id: sheetId,
			sheet_name: sheetName,
			sheet_was_picked: '1',
		};

		jQuery.get(
			strings.ajax_url,
			postData,
			(response: SheetSelectedResponse) => {
				// Extract the <script block out of the controls
				const matches = response.data.controlsHTML.match(
					/<script type=\"text\/javascript\">(.+)<\/script>/
				);

				replaceSpreadsheetLinkMarkup(
					response.data.spreadsheetLinkMarkup
				);

				if (matches?.length) {
					// Remove the script from the markup
					const controlsHTML = response.data.controlsHTML.replace(
						matches[0],
						''
					);

					$('#gform_setting_column_mapping').html(controlsHTML);

					enableColumnMappingSection();

					// Run the initialization script for the field map field
					try {
						// eslint-disable-next-line no-eval
						eval(matches[1]);
					} catch (e) {
						// eslint-disable-next-line no-console
						console.warn(e);
					}
				}
			}
		);
	});
}

function addRadioChangeHandlerSelect() {
	$('#google_sheet_url_field0').on('change', function () {
		if (
			$(
				"input[name='_gform_setting_google_sheet_url_field']:checked"
			).val() === 'create'
		) {
			enableColumnMappingSection();
		}
	});
	$('#google_sheet_url_field1').on('change', function () {
		// If the value is "select", then the "Select existing sheet" radio button was selected.
		const selectRadioChecked =
			$(
				"input[name='_gform_setting_google_sheet_url_field']:checked"
			).val() === 'select';

		if (!selectRadioChecked) {
			return;
		}

		showOAuthSpinner();

		disableColumnMappingSection();

		if (!strings.token) {
			openOAuthTab();
		} else {
			showPicker({
				token: strings.token,
				showErrorMessage: addErrorMessage,
				onDataFound: ({ sheet_url: sheetUrl }) => {
					if (sheetUrl) {
						handleGoogleSpreadsheetURLUpdate(sheetUrl);
					}

					hideOAuthSpinner();
				},
				strings,
			});
		}
	});
}

function setGoogleAuthToken(token: Token) {
	strings.token = token;
}

const SPINNER_ID = 'gpgs_oauth_spinner';
function addOAuthSpinner() {
	$(`#${SPINNER_ID}`).remove();

	$('#gform-settings-radio-choice-google_sheet_url_field1').append(
		`<span id="${SPINNER_ID}" class="spinner spinner_connect"></span>`
	);
}

function showOAuthSpinner() {
	$(`#${SPINNER_ID}`).addClass('is-active');
}

function hideOAuthSpinner() {
	$(`#${SPINNER_ID}`).removeClass('is-active');
}
