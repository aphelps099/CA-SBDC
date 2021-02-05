<?php $form_id = isset( $args['form_id'] ) ? $args['form_id'] : null; ?>
<?php if ( ! empty( $form_id ) && function_exists( 'gravity_form' ) ) { ?>
	<?php $form = GFAPI::get_form( $form_id ); ?>
	<div id="form-<?php echo $form_id; ?>-modal" class="modal fade form" tabindex="-1">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title"><?php echo $form['title']; ?></h4>
					<button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
				</div>
				<div class="modal-body">
					<?php gravity_form( $form_id, false, true, false, null, true, -1, true ); ?>
				</div>
			</div>
		</div>
	</div>
<?php } ?>