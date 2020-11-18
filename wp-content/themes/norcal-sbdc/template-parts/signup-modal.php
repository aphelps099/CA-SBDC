<?php
	$modal = (object) array(
		'contact_form_title' => get_option( 'theme_config_signup_modal_contact_form_title' ),
		'contact_form_embed_script' => get_option( 'theme_config_signup_modal_contact_form_embed_script' ),
		'contact_form_disclaimer' => get_option( 'theme_config_signup_modal_contact_form_disclaimer' ),
	);
?>

<?php if ( ! empty( $modal->contact_form_embed_script ) ) { ?>
	<div id="signup-modal" class="modal fade contact-form" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<?php if ( ! empty( $modal->contact_form_title ) ) { ?>
						<h5 class="modal-title"><?php echo $modal->contact_form_title; ?></h5>
					<?php } ?>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">
							<svg class="bi bi-x" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
								<path fill-rule="evenodd" d="M11.854 4.146a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708-.708l7-7a.5.5 0 0 1 .708 0z"/>
								<path fill-rule="evenodd" d="M4.146 4.146a.5.5 0 0 0 0 .708l7 7a.5.5 0 0 0 .708-.708l-7-7a.5.5 0 0 0-.708 0z"/>
							</svg>
						</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="contact-form-container">
						<?php echo $modal->contact_form_embed_script; ?>
					</div>
					<?php if ( ! empty( $modal->contact_form_disclaimer ) ) { ?>
						<p class="disclaimer"><?php echo nl2br( $modal->contact_form_disclaimer ); ?></p>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
<?php } ?>