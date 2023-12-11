import $ from 'jquery';

import {
	addButtonClickHandlerInsert,
	addButtonClickHandlerDisconnect,
} from './settings-feed-shared';

const { gpgs_settings_feed_edit_strings: strings } = window;

$(document).ready(() => {
	addConnectedUI();
});

/**
 * Adds the "disconnect button" and "insert test row button" as rendered during feed editing.
 */
function addConnectedUI() {
	// if a sheet has been disconected, do not show the "connected" UI
	if (!strings.sheet_url) {
		return;
	}

	let linkText = strings.spreadsheet_name;
	if (strings.sheet_name) {
		linkText = `${linkText} (${strings.sheet_name})`;
	}

	if (strings.error_message) {
		$(
			'#gform_setting_google_sheet_url_field > .gform-settings-input__container'
		).hide();

		$('#gform_setting_google_sheet_url_field').append(
			`<div>
				<p class="alert error">
					${strings.error_message}
					</br>
					</br>
	    			<span>
	    				<a href="${strings.sheet_url}" target="_blank">${linkText}</a>
	    			</span>
	    			<span style="align-self: self-end;margin-left: auto !important;vertical-align: middle;">
	    			<a id="disconnect" class="button add-new-h2">${strings.disconnect}<span class="spinner spinner_disconnect"></span></a>
	    			</span>
				</p>
			</div>`
		);

		return;
	}

	$('#gform_setting_google_sheet_url_field').append(
		`<div class="alert gforms_note_success" style="display: flex;align-items: center;margin-bottom:0;">
	    	<span>
	    		<a href="${strings.sheet_url}" target="_blank">${linkText}</a>
	    	</span>
	    	<span style="align-self: self-end;margin-left: auto !important;vertical-align: middle;">
	    	<a id="insert_test_row" class="button add-new-h2">${strings.insert_test_row}<span class="spinner spinner_insert_test_row"></span></a>
	    	<a id="disconnect" class="button add-new-h2">${strings.disconnect}<span class="spinner spinner_disconnect"></span></a>
	    	</span>
	    </div>`
	);

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
}
