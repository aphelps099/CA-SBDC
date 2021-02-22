<?php
	$form = isset( $args['form'] ) ? array_merge( array(
		'id' => null,
		'type' => '',
		'display_title' => false,
		'display_description' => true,
		'field_values' => null
	), $args['form'] ) : null;
?>
<?php if ( ! empty( $form ) && isset( $form['id'] ) && function_exists( 'gravity_form' ) ) { ?>
	<?php $gf = GFAPI::get_form( $form['id'] ); ?>
	<?php $modal_id = 'form-' . $form['id'] . '-modal'; ?>
	<?php if ( $form['type'] == 'event-registration-zoom-meeting' ) $modal_id = 'form-event-registration-zoom-meeting-' . $form['field_values']['meeting_id'] . '-modal'; ?>
	<div id="<?php echo $modal_id; ?>" class="modal fade form" tabindex="-1">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title"><?php echo $gf['title']; ?></h4>
					<button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
				</div>
				<div class="modal-body">
					<?php gravity_form( $form['id'], $form['display_title'], $form['display_description'], false, $form['field_values'], true, -1, true ); ?>
				</div>
			</div>
		</div>
	</div>
<?php } ?>