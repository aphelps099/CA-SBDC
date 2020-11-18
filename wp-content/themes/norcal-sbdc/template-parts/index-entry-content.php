<article <?php post_class(); ?>>

	<div class="entry-thumbnail">
		<div class="inner">
			<a href="<?php the_permalink(); ?>">
				<?php ct_bg_image_css( get_post_thumbnail_id(), '#index-entries .post-' . get_the_ID() . ' .entry-thumbnail .image', array( 'xs' => 'medium_large' ) ); ?>
				<div class="image">
					<?php echo wp_get_attachment_image( get_post_thumbnail_id(), 'medium_large' ); ?>
				</div>
			</a>
		</div>
	</div>

	<div class="entry-header">
		<h4 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
	</div>

</article>