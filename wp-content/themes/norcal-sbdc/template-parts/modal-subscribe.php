<?php $form_id = get_option( 'theme_config_footer_subscribe_form' ); ?>
<?php if ( ! empty( $form_id ) && function_exists( 'gravity_form' ) ) { ?>
	<div id="subscribe-modal" class="modal fade" tabindex="-1">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title"><span><?php _e( 'SBDC', 'crown_theme' ); ?></span><?php _e( 'Intel&trade;', 'crown_theme' ); ?></h4>
					<button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
				</div>
				<div class="modal-body">
					<?php gravity_form( $form_id, true, true, false, null, true, 10000000, true ); ?>
				</div>
			</div>
		</div>
	</div>
<?php } ?>