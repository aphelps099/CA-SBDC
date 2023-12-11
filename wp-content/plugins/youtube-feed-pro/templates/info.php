<?php
/**
 * Info Template
 * Adds channel information to the feed items
 *
 * @version 2.1 by Smash Balloon
 *
 */

 use SmashBalloon\YouTubeFeed\SBY_Parse;
 use SmashBalloon\YouTubeFeed\Pro\SBY_Parse_Pro;
 use SmashBalloon\YouTubeFeed\SBY_Display_Elements;
 use SmashBalloon\YouTubeFeed\Pro\SBY_Display_Elements_Pro;

if ( $context === 'item' ) {
	$channel_id = SBY_Parse::get_channel_id( $header_data );
	$avatar = SBY_Parse::get_item_avatar( $post, isset( $settings['feed_avatars'] ) ? $settings['feed_avatars'] : null );
	$channel_title = SBY_Parse::get_channel_title( $header_data );
	$subscriber_count = SBY_Parse_Pro::get_subscriber_count( $header_data );
}
$show_subscribe_button = $settings['enablesubscriberlink'];
$subscribe_button_text = $settings['subscribetext'];

$subscribe_bar_attr = SBY_Display_Elements_Pro::get_list_type_subscribe_bar_attr( $settings, $post_index );

$youtube_icon = '<svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M6.66732 10.0634L10.1273 8.0634L6.66732 6.0634V10.0634ZM14.374 4.8434C14.4607 5.15673 14.5207 5.57673 14.5607 6.11006C14.6073 6.6434 14.6273 7.1034 14.6273 7.5034L14.6673 8.0634C14.6673 9.5234 14.5607 10.5967 14.374 11.2834C14.2073 11.8834 13.8207 12.2701 13.2207 12.4367C12.9073 12.5234 12.334 12.5834 11.454 12.6234C10.5873 12.6701 9.79398 12.6901 9.06065 12.6901L8.00065 12.7301C5.20732 12.7301 3.46732 12.6234 2.78065 12.4367C2.18065 12.2701 1.79398 11.8834 1.62732 11.2834C1.54065 10.9701 1.48065 10.5501 1.44065 10.0167C1.39398 9.4834 1.37398 9.0234 1.37398 8.6234L1.33398 8.0634C1.33398 6.6034 1.44065 5.53006 1.62732 4.8434C1.79398 4.2434 2.18065 3.85673 2.78065 3.69006C3.09398 3.6034 3.66732 3.5434 4.54732 3.5034C5.41398 3.45673 6.20732 3.43673 6.94065 3.43673L8.00065 3.39673C10.794 3.39673 12.534 3.5034 13.2207 3.69006C13.8207 3.85673 14.2073 4.2434 14.374 4.8434Z" fill="white"/>
</svg>';

?>

<div class="sby_info sby_info_<?php echo esc_attr( $context ); ?>">
	<?php if ( ($context === 'item' && 0 === $post_index && $settings['layout'] === 'list') || sby_doing_customizer( $settings ) ) : 
		if ( $show_subscribe_button || sby_doing_customizer( $settings ) ) :	
	?>
		<div class="sby-player-info" <?php echo $subscribe_bar_attr; ?>>
			<div class="sby-channel-info-bar">
				<?php if ( $header_data ) : ?>
					<span class="sby-avatar">
						<?php 
							printf(
								'<img src="%s" />',
								esc_attr( $avatar )
							);
						?>
					</span>
					<span class="sby-channel-name"><?php echo esc_html( $channel_title ); ?></span>
					<span class="sby-channel-subscriber-count">
						<?php echo SBY_Display_Elements_Pro::escaped_formatted_count_string( $subscriber_count, '' ); ?>
					</span>
				<?php endif; ?>
                    <span class="sby-channel-subscribe-btn">
                        <?php
                        printf(
                            '<a href="%s" target="_blank" rel="noopener noreferrer">%s <span %s>%s</span></a>',
                            'http://www.youtube.com/channel/'. esc_attr( $channel_id ) .'?sub_confirmation=1&feature=subscribe-embed-click',
                            $youtube_icon,
                            SBY_Display_Elements::get_subscribe_button_attribute( $settings ),
                            esc_html( $subscribe_button_text )
                        );
                        ?>
                    </span>
			</div>
		</div>
	<?php endif; endif; ?>

	<?php if ( SBY_Display_Elements_Pro::should_show_element( 'title', $context, $settings ) ) : ?>
		<p class="sby_video_title_wrap" <?php echo $video_title_attr; ?>>
			<span class="sby_video_title"><?php echo sby_esc_html_with_br( $title ); ?></span>
		</p>
	<?php endif; ?>

	<?php if ( SBY_Display_Elements_Pro::should_show_element( 'meta', $context, $settings ) ) : ?>
		<p class="sby_meta" <?php echo $sby_meta_color_styles; ?>>

			<?php if ( SBY_Display_Elements_Pro::should_show_element( 'user', $context, $settings ) ) : ?>
				<span class="sby_username_wrap" <?php echo $sby_meta_size_color_styles; echo $username_attr; ?>>
					<span class="sby_username"><?php echo esc_html( $username ); ?></span>
				</span>
			<?php endif; ?>
			<?php if ( SBY_Display_Elements_Pro::should_show_element( 'views', $context, $settings ) ) : ?>
				<span class="sby_view_count_wrap" <?php echo $sby_meta_size_color_styles; echo $views_attr; ?>>
					<span class="sby_view_count"><?php echo $views_count_string; ?></span>
				</span>
			<?php endif; ?>
			<?php if ( SBY_Display_Elements_Pro::should_show_element( 'date', $context, $settings ) ) : ?>
				<span class="sby_date_wrap" <?php echo $date_attr; ?>>
					<span class="sby_date sby_live_broadcast_type_<?php echo esc_attr( $live_broadcast_type ); ?>"><?php echo $formatted_date_string; ?></span>
				</span>
			<?php endif; ?>
		</p>
	<?php endif; ?>

	<?php if ( $live_broadcast_type !== 'none'
	           && SBY_Display_Elements_Pro::should_show_element( 'countdown', $context, $settings ) ) : ?>
		<p class="sby_ls_message_wrap" <?php echo $countdown_attr; ?>>
			<span class="sby_ls_message"><?php echo $live_streaming_time_string; ?></span>
		</p>
	<?php endif; ?>

	<?php if ( $context === 'item' && SBY_Display_Elements_Pro::should_show_element( 'stats', $context, $settings ) ) : ?>
		<p class="sby_stats" <?php echo $stats_attr; ?>>
            <span class="sby_likes" <?php echo $sby_meta_size_color_styles; ?>>
                <?php echo SBY_Display_Elements_Pro::get_icon( 'likes', $icon_type, $sby_meta_size_color_styles ); ?>
                <span class="sby_like_count"><?php echo $likes_count; ?></span>
            </span>
			<span class="sby_comments" <?php echo $sby_meta_size_color_styles; ?>>
                <?php echo SBY_Display_Elements_Pro::get_icon( 'comments', $icon_type, $sby_meta_size_color_styles ); ?>
                <span class="sby_comment_count"><?php echo $comments_count; ?></span>
            </span>
		</p>
	<?php endif; ?>

	<?php if ( $context === 'item' && SBY_Display_Elements_Pro::should_show_element( 'description', $context, $settings ) ) : ?>
		<p class="sby_caption_wrap sby_item_caption_wrap" <?php echo $description_attr; ?>>
			<span class="sby_caption" <?php echo $sby_info_styles; ?>><?php echo sby_esc_html_with_br( $caption ); ?></span><span class="sby_expand"> <a href="#"><span class="sby_more">...</span></a></span>
		</p>
    <?php endif; ?>
</div>