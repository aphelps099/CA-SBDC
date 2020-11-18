

<?php $related_post_ids = ct_get_related_posts( get_the_ID(), array(), array( 'fields' => 'ids' ) ); ?>
<?php if ( ! empty( $related_post_ids ) && count( $related_post_ids ) >= 2 ) { ?>
	<aside id="related-content" class="wp-block-crown-blocks-container alignfull text-color-light" style="background-color:#24282F">
		<div class="container-bg"></div>
		<div class="inner">
			<div class="container-contents">
				<div class="inner">

					<h2 class="has-text-align-center is-style-section-heading"><?php _e( 'Related Articles', 'crown_theme' ); ?></h2>

					<?php echo render_block( array(
						'blockName' => 'crown-blocks/recent-posts',
						'attrs' => array(
							'manuallySelectPosts' => true,
							'filterPostsInclude' => array_map( function( $n ) { return array( 'id' => $n ); }, $related_post_ids )
						),
						'innerContent' => array()
					) ); ?>

				</div>
			</div>
		</div>
	</aside>
<?php } ?>