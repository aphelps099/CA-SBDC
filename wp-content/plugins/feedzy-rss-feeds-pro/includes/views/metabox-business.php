<?php if ( 'language-dropdown' === $row_slug ) { ?>
<div class="feedzy-row">
	<div class="label_description">
		<label class="feedzy-sr-only" for="f1-post-content"><?php echo __( 'Item Full Content Language', 'feedzy-rss-feeds' ); ?></label><br/>
		<small>
		<?php _e( 'If you choose to display the full content, you may want to specify the language of the website that will provide the full content. The default is English', 'feedzy-rss-feeds' ); ?>
		</small>
	</div>
	<div class="feedzy-separator dashicons dashicons-leftright"></div>
	<div class="form-group input-group form_item">
		<?php echo $language_dropdown; ?>
	</div>
</div>
<?php } ?>
