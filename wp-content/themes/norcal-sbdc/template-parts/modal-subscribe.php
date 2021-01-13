<?php $form_id = get_option( 'theme_config_modal_subscribe_form' ); ?>
<?php if ( ! empty( $form_id ) && function_exists( 'gravity_form' ) ) { ?>
	<div id="subscribe-modal" class="modal fade" tabindex="-1">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title"><span><?php _e( 'SBDC', 'crown_theme' ); ?></span><?php _e( 'Intel&trade;', 'crown_theme' ); ?></h4>
					<button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
				</div>
				<div class="modal-body">
					<div class="subscribe-contents">

						<?php $title = get_option( 'theme_config_modal_subscribe_title' ); ?>
						<?php if ( ! empty( $title ) ) { ?>
							<h3 class="subscribe-title"><?php echo $title; ?></h3>
						<?php } ?>

						<?php $description = get_option( 'theme_config_modal_subscribe_description' ); ?>
						<?php if ( ! empty( $description ) ) { ?>
							<p class="subscribe-description"><?php echo $description; ?></p>
						<?php } ?>

					</div>
					<div class="subscribe-form">
						<?php gravity_form( $form_id, false, false, false, null, true, 10000000, true ); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php } ?>