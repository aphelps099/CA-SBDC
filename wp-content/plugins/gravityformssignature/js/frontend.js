/* global jQuery, Base64, SignatureEnabled, ResizeSignature, ClearSignature, LoadSignature */
/* eslint-disable new-cap */

jQuery( window ).on( 'gform_post_render', function( formId ) {

	jQuery( '.gfield_signature_container' ).each( function() {

		// If original width is already set, exit.
		if ( jQuery( this ).data( 'original-width' ) ) {
			return;
		}

		var width  = parseFloat( jQuery( this ).css( 'width' ) ),
			height = parseFloat( jQuery( this ).css( 'height' ) ),
			containerID = jQuery( this ).parent().parent().find( '.gfield_label' ).attr( 'for' );

		// Force reset button to work even when Signature is disabled.
		var $resetButton = jQuery( '#' + containerID + '_resetbutton' );
		$resetButton.click( function() {
			SignatureEnabled( containerID, true );
			ClearSignature( containerID );
			gformSignatureResize();
		} ).parent().append( '<button type="button" id="' + containerID + '_lockedReset" class="gform_signature_locked_reset" style="display:none;height:24px;cursor:pointer;padding: 0 0 0 1.8em;opacity:0.75;font-size:0.813em;border:0;background: transparent url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA0NDggNTEyIiBjbGFzcz0idW5kZWZpbmVkIj48cGF0aCBkPSJNNDAwIDIyNGgtMjR2LTcyQzM3NiA2OC4yIDMwNy44IDAgMjI0IDBTNzIgNjguMiA3MiAxNTJ2NzJINDhjLTI2LjUgMC00OCAyMS41LTQ4IDQ4djE5MmMwIDI2LjUgMjEuNSA0OCA0OCA0OGgzNTJjMjYuNSAwIDQ4LTIxLjUgNDgtNDhWMjcyYzAtMjYuNS0yMS41LTQ4LTQ4LTQ4em0tMTA0IDBIMTUydi03MmMwLTM5LjcgMzIuMy03MiA3Mi03MnM3MiAzMi4zIDcyIDcydjcyeiIgY2xhc3M9InVuZGVmaW5lZCIvPjwvc3ZnPg==) no-repeat left center;background-size:16px;">' + gform_signature_frontend_strings.lockedReset + '</button>' );

		// Trigger reset when Locked Reset button is clicked.
		jQuery( '#' + containerID + '_lockedReset' ).click( function() {
			jQuery( this ).hide();
			$resetButton.click();
		} );

		// Hide the status box so that our Locked Reset button display left-aligned.
		jQuery( '#' + containerID + '_status' ).hide();

		jQuery( this ).data( 'ratio', height / width );
		jQuery( this ).data( 'original-width', width );

	} );

} );

jQuery( document ).ready( function( $ ) {

	$( window ).on( 'load resize', function() {
		gformSignatureResize();
	} );

} );


/**
 * Handles updating of the signature field on document resize.
 *
 * @return {void}
 */
function gformSignatureResize() {
	/**
	 * Get the cached components of the signature field.
	 *
	 * @since 4.1
	 *
	 * @param {Object} $signatureContainer jQuery signature container object.
	 * @return {Object} Signature data object.
	 */
	function getSignature( $signatureContainer ) {
		var $gfield = $signatureContainer.closest( '.gfield' );

		return {
			$container: $signatureContainer,
			fieldID: $gfield.find( '.gfield_label' ).attr( 'for' ),
			gfieldWidth: $gfield.innerWidth(),
			width: $signatureContainer.data( 'original-width' ),
			dataInput: $signatureContainer.parent().find( 'input[name$="_data"]:eq( 0 )' ),
			fieldExists: function() {
				return typeof this.fieldID !== 'undefined';
			},
			dataInputExists: function() {
				return this.dataInput.length > 0;
			}
		};
	}

	/**
	 * Get the resized width of the signature.
	 *
	 * @since 4.1
	 *
	 * @param {Object} signature The signature data object.
	 * @return {*|null} The new signature width.
	 */
	function getNewSignatureWidth( signature ) {
		return signature.gfieldWidth > signature.width ? signature.width : signature.gfieldWidth;
	}

	/**
	 * Get the resized height of the signature.
	 *
	 * @since 4.1
	 *
	 * @param {Number} resizedSignatureWidth The width of the resized signature.
	 * @param {object} signature The original signature data.
	 * @return {number} The new signature height.
	 */
	function getNewSignatureHeight( resizedSignatureWidth, signature ) {
		return Math.round( resizedSignatureWidth * signature.$container.data( 'ratio' ) );
	}

	/**
	 * Get an object representing the signature's new height and width.
	 *
	 * @since 4.1
	 *
	 * @param {Object} signature The original signature object.
	 * @return {{width: (*|null), height: number}} The resized signature data.
	 */
	function getResizedSignature( signature ) {
		return {
			width: getNewSignatureWidth( signature ),
			height: getNewSignatureHeight( getNewSignatureWidth( signature ), signature )
		};
	}

	// Find every signature field on the page and resize it.
	jQuery( '.gfield_signature_container' ).each( function() {
		var signature = getSignature( jQuery( this ) );
		var resizedSignature = getResizedSignature( signature );
		var decodedSignatureData;

		if (
			!signature.fieldExists()
			|| !signature.dataInputExists()
		) {
			return;
		}

		decodedSignatureData = Base64.decode( signature.dataInput.val() );

		if ( decodedSignatureData && resizedSignature.width < signature.width ) {
			SignatureEnabled( signature.fieldID, false );
			jQuery( '#' + signature.fieldID + '_lockedReset' ).show();
			return;
		}

		// Resize signature.
		ResizeSignature( signature.fieldID, resizedSignature.width, resizedSignature.height );
		ClearSignature( signature.fieldID );

		if ( decodedSignatureData ) {
			LoadSignature( signature.fieldID, decodedSignatureData, 1 );
		}
	} );
}


