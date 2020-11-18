<footer class="entry-footer">
	<div class="inner">

		<?php if ( post_type_supports( get_post_type( get_the_ID() ), 'author' ) && is_singular() ) { ?>
			<div class="entry-author">
				<h6 class="label"><?php _e( 'Written By:', 'crown_theme' ) ?></h6>
				<p class="value">
					<a class="author-link" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" rel="author"><?php the_author(); ?></a>
				</p>
			</div>
		<?php } ?>

		<?php if ( has_tag() ) { ?>
			<div class="entry-tags">
				<h6 class="label"><?php _e( 'Tags', 'crown_theme' ) ?></h6>
				<ul class="value">
					<?php the_tags( '<li>', '</li> <li>', '</li>' ); ?>
				</ul>
			</div>
		<?php } ?>

	</div>
</footer>