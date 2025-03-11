<div class="misc-pub-section misc-pub-post-status misc-hurryt">
    <?php _e('Status:', "hurrytimer")?>
	<?php if ($isActive): ?>
        <b id="post-status-display" style="color:green"><?php _e('Active', "hurrytimer")?></b>
        <?php if (current_user_can('publish_posts')): ?>
            <a href="<?php echo $deactivateUrl ?>"><?php _e('Deactivate', "hurrytimer")?></a>
        <?php endif; ?>
	<?php else: ?>
        <b id="post-status-display" style="color:red"><?php _e('Inactive', "hurrytimer")?></b>
        <?php if (current_user_can('publish_posts')): ?>
            <a href="<?php echo $activateUrl ?>"><?php _e('Activate', "hurrytimer")?></a>
        <?php endif; ?>
	<?php endif;?>
    <a href="<?php echo esc_url($duplicateUrl); ?>" class="button" style="margin-left: 10px; margin-top: 10px;">Duplicate Campaign</a>
</div>
