<?php
$wordai_username = '';
if ( isset( $this->settings['wordai_username'] ) ) {
	$wordai_username = $this->settings['wordai_username'];
}

$wordai_pass = '';
if ( isset( $this->settings['wordai_hash'] ) ) {
	$wordai_pass = $this->settings['wordai_hash'];
}

$status = 'Invalid';
$wordai_licence = '';
$licence_status_color = 'red';
$wordai_last_check = __( 'Never', 'feedzy-rss-feeds' );
if ( isset( $this->settings['wordai_licence'] ) ) {
	$wordai_licence = $this->settings['wordai_licence'];
	if ( $wordai_licence === 'yes' ) {
		$status = 'Valid';
		$licence_status_color = '#62c370';
	}
}
if ( isset( $this->settings['wordai_last_check'] ) ) {
	$wordai_last_check = $this->settings['wordai_last_check'];
}
if ( isset( $this->settings['wordai_message'] ) && ! empty( $this->settings['wordai_message'] ) ) {
	$status = $this->settings['wordai_message'];
}
?>
				<h2>WordAi</h2>
				<div class="fz-form-group">
					<b><?php echo __( 'API Status:', 'feedzy-rss-feeds' ); ?> </b> <span id="wordai_api_status" style="color:<?php echo $licence_status_color; ?>"><?php echo $status; ?></span><div> <?php echo __( 'Last check: ', 'feedzy-rss-feeds' ) . $wordai_last_check; ?></div>
				</div>
				<div class="fz-form-group">
					<label><?php echo __( 'The WordAi account email:', 'feedzy-rss-feeds' ); ?></label>
				</div>
				<div class="fz-form-group">
					<input type="text" id="wordai_username" class="fz-form-control" name="wordai_username" value="<?php echo $wordai_username; ?>" placeholder="<?php echo __( 'WordAi Email', 'feedzy-rss-feeds' ); ?>"/>
				</div>
				<div class="fz-form-group">
					<label><?php echo __( 'The WordAi account password (not the API hash key/hash):', 'feedzy-rss-feeds' ); ?></label>
				</div>
				<div class="fz-form-group fz-input-group">
					<input type="password" id="wordai_pass" class="fz-form-control" name="wordai_pass" value="<?php echo $wordai_pass; ?>" placeholder="<?php echo __( 'WordAi Password', 'feedzy-rss-feeds' ); ?>"/>
					<div class="fz-input-group-btn">
						<button id="check_wordai_api" type="button" class="fz-btn fz-btn-submit fz-btn-activate" onclick="return ajaxUpdate();"><?php echo __( 'Check & Save', 'feedzy-rss-feeds' ); ?></button>
					</div>
				</div>

<script type="text/javascript">
	function ajaxUpdate() {

		var wordai_data = {
			'wordai_username': jQuery( '#wordai_username' ).val(),
			'wordai_pass': jQuery( '#wordai_pass' ).val(),
		}

		var data = {
			'action': 'update_settings_page',
			'feedzy_settings': wordai_data,
		};

		jQuery( '#check_wordai_api' ).prop( 'disabled', true );
		jQuery( '#check_wordai_api' ).html('<?php echo __( 'Checking ...', 'feedzy-rss-feeds' ); ?>');
		jQuery.post( ajaxurl, data, function( response ) {
			jQuery( '#check_wordai_api' ).prop( 'disabled', false );
			jQuery( '#check_wordai_api' ).html('<?php echo __( 'Check & Save', 'feedzy-rss-feeds' ); ?>');
			location.reload();
		}, 'json');

		return false;
	};
</script>
