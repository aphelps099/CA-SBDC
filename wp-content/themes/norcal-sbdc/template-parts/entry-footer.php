<footer class="entry-footer">
	<div class="inner">

		<div class="continue-reading">
			<h4><?php _e( 'Continue Reading', 'crown_theme' ) ?></h4>
			<div class="links">
				<?php next_post_link( '%link', '<span class="label">' . __( 'Next Post', 'crown_theme' ) . '</span> <span class="title">%title</span>' ); ?>
				<?php previous_post_link( '%link', '<span class="label">' . __( 'Previous Post', 'crown_theme' ) . '</span> <span class="title">%title</span>' ); ?>
			</div>
		</div>

	</div>
</footer>