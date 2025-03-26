<?php
/**
 * Smash Balloon Social Wall Header Template
 * If enabled on the "Customize" tab, the social media filter is
 * displayed by this template
 *
 * @version 1.0 Social Wall by Smash Balloon
 *
 */

// Don't load directly
if (!defined('ABSPATH')) {
	die('-1');
}
?>

<div class="sb-wall-header">
	<div class="sbsw-filter-bar">
		<div class="sbsw-single-filter sbsw-single-filter-all">
			<a href="javascript:void(0);" data-plugin="all"><?php echo SW_Display_Elements::get_icon('heart'); ?> <span><?php esc_html_e('All', 'social-wall'); ?></span></a>
		</div>
	<?php foreach ($plugins_in_feed as $plugin) :?>

		<?php if ($plugin === 'instagram') : ?>
		<div class="sbsw-single-filter sbsw-single-filter-<?php echo esc_attr($plugin); ?>">
			<a href="javascript:void(0);" data-plugin="<?php echo esc_attr($plugin); ?>"><?php echo SW_Display_Elements::get_icon($plugin); ?> <span><?php esc_html_e('Instagram', 'social-wall'); ?></span></a>
		</div>
		<?php elseif ($plugin === 'facebook') : ?>
		<div class="sbsw-single-filter sbsw-single-filter-<?php echo esc_attr($plugin); ?>">
			<a href="javascript:void(0);" data-plugin="<?php echo esc_attr($plugin); ?>"><?php echo SW_Display_Elements::get_icon($plugin); ?> <span><?php esc_html_e('Facebook', 'social-wall'); ?></span></a>
		</div>
		<?php elseif ($plugin === 'twitter') :
            $x_class = function_exists('ctf_should_rebrand_to_x') && ctf_should_rebrand_to_x() ? ' sbsw-single-filter-x-branding' : '';
            $x_text =   function_exists('ctf_should_rebrand_to_x') && ctf_should_rebrand_to_x() ? __('X (Twitter)', 'social-wall') : __('Twitter', 'social-wall');
            ?>
		<div class="sbsw-single-filter sbsw-single-filter-<?php echo esc_attr($plugin); ?><?php echo esc_attr($x_class); ?>">
			<a href="javascript:void(0);" data-plugin="<?php echo esc_attr($plugin); ?>"><?php echo SW_Display_Elements::get_icon($plugin); ?> <span><?php echo esc_html($x_text); ?></span></a>
		</div>
		<?php elseif ($plugin === 'youtube') : ?>
		<div class="sbsw-single-filter sbsw-single-filter-<?php echo esc_attr($plugin); ?>">
			<a href="javascript:void(0);" data-plugin="<?php echo esc_attr($plugin); ?>"><?php echo SW_Display_Elements::get_icon($plugin); ?> <span><?php esc_html_e('YouTube', 'social-wall'); ?></span></a>
		</div>
		<?php elseif ($plugin === 'tiktok') : ?>
		<div class="sbsw-single-filter sbsw-single-filter-<?php echo esc_attr($plugin); ?>">
			<a href="javascript:void(0);" data-plugin="<?php echo esc_attr($plugin); ?>"><?php echo SW_Display_Elements::get_icon($plugin); ?> <span><?php esc_html_e('TikTok', 'social-wall'); ?></span></a>
		</div>
		<?php endif; ?>
	<?php endforeach; ?>

	</div>
</div>