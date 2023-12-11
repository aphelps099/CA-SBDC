/**
 * Shared functions for use within the feed settings context.
 */
import $ from 'jquery';

interface ClickHandlerArgs {
	nonce: string;
	formId: string;
	feedId: string;
	ajaxUrl: string;
}

/**
 * Adds "insert test row" event handler
 */
export function addButtonClickHandlerInsert({
	nonce,
	formId,
	feedId,
	ajaxUrl,
}: ClickHandlerArgs) {
	$('#insert_test_row').off('click.gpgsInsertTestRowHandler');

	$('#insert_test_row').on('click.gpgsInsertTestRowHandler', function (eee) {
		const $button = $(this);
		if ($button.data('isInserting')) {
			return;
		}

		$button.data('isInserting', true);
		// show spinner
		$('.spinner.spinner_insert_test_row').addClass('is-active');
		const data = {
			action: 'gpgs_insert_test_row',
			_ajax_nonce: nonce,
			form_id: formId,
			feed_id: feedId,
		};
		jQuery.post(ajaxUrl, data, function (response) {
			$button.data('isInserting', false);
			$('.gform-settings__content').prepend(response.data.message);
			// hide spinner
			$('.spinner.spinner_insert_test_row').removeClass('is-active');
		});
	});
}

/**
 * Adds "disconnected sheet" event handler
 */
export function addButtonClickHandlerDisconnect({
	nonce,
	formId,
	feedId,
	ajaxUrl,
}: ClickHandlerArgs) {
	$('#feed_disconnect,#disconnect').off('click.gpgsDisconnectSheetHandler');

	$('#feed_disconnect,#disconnect').on(
		'click.gpgsDisconnectSheetHandler',
		function () {
			// show spinner
			$('.spinner.spinner_disconnect').addClass('is-active');
			const data = {
				action: 'gpgs_disconnect',
				_ajax_nonce: nonce,
				form_id: formId,
				feed_id: feedId,
			};
			jQuery.post(ajaxUrl, data, function (response) {
				if (response.success) {
					// Refresh the page so the user can connect a new sheet.
					location.reload();
				}

				// hide spinner
				$('.spinner.spinner_disconnect').removeClass('is-active');
			});
		}
	);
}

function disableSettingsSection(selector: string) {
	$(selector)
		.addClass('gpgs-disabled-settings-section')
		.attr('inert', 'true');
}

function enableSettingsSection(selector: string) {
	$(selector)
		.removeClass('gpgs-disabled-settings-section')
		.removeAttr('inert');
}

export function disableSheetSettingsSection() {
	disableSettingsSection('#gform-settings-section-google-sheets-settings');
}

export function enableSheetSettingsSection() {
	enableSettingsSection('#gform-settings-section-google-sheets-settings');
}

export function disableColumnMappingSection() {
	disableSettingsSection('#gform-settings-section-column-mapping');
}

export function enableColumnMappingSection() {
	enableSettingsSection('#gform-settings-section-column-mapping');
}
