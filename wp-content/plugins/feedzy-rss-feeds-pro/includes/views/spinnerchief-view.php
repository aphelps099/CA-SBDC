<?php
$spinnerchief_username = '';
if ( isset( $this->settings['spinnerchief_username'] ) ) {
	$spinnerchief_username = $this->settings['spinnerchief_username'];
}

$spinnerchief_password = '';
if ( isset( $this->settings['spinnerchief_password'] ) ) {
	$spinnerchief_password = $this->settings['spinnerchief_password'];
}

$spinnerchief_key = '';
if ( isset( $this->settings['spinnerchief_key'] ) ) {
	$spinnerchief_key = $this->settings['spinnerchief_key'];
}

$status = 'Invalid';
$spinnerchief_licence = '';
$licence_status_color = 'red';
$spinnerchief_last_check = __( 'Never', 'feedzy-rss-feeds' );
if ( isset( $this->settings['spinnerchief_licence'] ) ) {
	$spinnerchief_licence = $this->settings['spinnerchief_licence'];
	if ( $spinnerchief_licence === 'yes' ) {
		$status = 'Valid';
		$licence_status_color = '#62c370';
	}
}
if ( isset( $this->settings['spinnerchief_last_check'] ) ) {
	$spinnerchief_last_check = $this->settings['spinnerchief_last_check'];
}
if ( isset( $this->settings['spinnerchief_message'] ) && ! empty( $this->settings['spinnerchief_message'] ) ) {
	$status = $this->settings['spinnerchief_message'];
}

?>
				<h2>SpinnerChief</h2>
				<div class="fz-form-group">
					<b><?php echo __( 'API Status:', 'feedzy-rss-feeds' ); ?> </b> <span id="spinnerchief_api_status" style="color:<?php echo $licence_status_color; ?>"><?php echo $status; ?></span><div> <?php echo __( 'Last check: ', 'feedzy-rss-feeds' ) . $spinnerchief_last_check; ?></div>
				</div>
				<div class="fz-form-group">
					<label><?php echo __( 'The SpinnerChief username:', 'feedzy-rss-feeds' ); ?></label>
				</div>
				<div class="fz-form-group">
					<input type="text" id="spinnerchief_username" class="fz-form-control" name="spinnerchief_username" value="<?php echo $spinnerchief_username; ?>" placeholder="<?php echo __( 'SpinnerChief Username', 'feedzy-rss-feeds' ); ?>"/>
				</div>
				<div class="fz-form-group">
					<label><?php echo __( 'The SpinnerChief password:', 'feedzy-rss-feeds' ); ?></label>
				</div>
				<div class="fz-form-group fz-input-group">
					<input type="password" id="spinnerchief_password" class="fz-form-control" name="spinnerchief_password" value="<?php echo $spinnerchief_password; ?>" placeholder="<?php echo __( 'SpinnerChief Password', 'feedzy-rss-feeds' ); ?>"/>
				</div>

				<div class="fz-form-group fz-input-group">
					<input type="password" id="spinnerchief_key" class="fz-form-control" name="spinnerchief_key" value="<?php echo $spinnerchief_key; ?>" placeholder="<?php echo __( 'SpinnerChief API Key', 'feedzy-rss-feeds' ); ?>"/>
					<div class="fz-input-group-btn">
						<button id="check_spinnerchief_api" type="button" class="fz-btn fz-btn-submit fz-btn-activate" onclick="return ajaxUpdate();"><?php echo __( 'Check & Save', 'feedzy-rss-feeds' ); ?></button>
					</div>
				</div>

<script type="text/javascript">
	function ajaxUpdate() {

		var spinnerchief_data = {
			'spinnerchief_username': jQuery( '#spinnerchief_username' ).val(),
			'spinnerchief_password': jQuery( '#spinnerchief_password' ).val(),
			'spinnerchief_key': jQuery( '#spinnerchief_key' ).val(),
		}

		var data = {
			'action': 'update_settings_page',
			'feedzy_settings': spinnerchief_data,
		};

		jQuery( '#check_spinnerchief_api' ).prop( 'disabled', true );
		jQuery( '#check_spinnerchief_api' ).html('<?php echo __( 'Checking ...', 'feedzy-rss-feeds' ); ?>');
		jQuery.post( ajaxurl, data, function( response ) {
			jQuery( '#check_spinnerchief_api' ).prop( 'disabled', false );
			jQuery( '#check_spinnerchief_api' ).html('<?php echo __( 'Check & Save', 'feedzy-rss-feeds' ); ?>');
			location.reload();
		}, 'json');

		return false;
	};
</script>
