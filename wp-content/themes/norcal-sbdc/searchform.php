<?php

$aria_label = ! empty( $args['aria_label'] ) ? 'aria-label="' . esc_attr( $args['aria_label'] ) . '"' : '';
$placeholder = ! empty( $args['placeholder'] ) ? $args['placeholder'] : 'Search&hellip;';
$submit_label = ! empty( $args['submit_label'] ) ? $args['submit_label'] : 'Search';

?>
<form role="search" <?php echo $aria_label; ?> method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label>
		<span class="sr-only"><?php _e( 'Search for:', 'crown_theme' ); ?></span>
		<span class="icon"><?php ct_icon( 'search' ); ?></span>
		<input type="search" class="search-field" placeholder="<?php echo esc_attr_x( $placeholder, 'placeholder', 'crown_theme' ); ?>" value="<?php echo get_search_query(); ?>" name="s">
	</label>
	<button type="submit" class="search-submit"><?php _e( $submit_label, 'crown_theme' ); ?></button>
</form>