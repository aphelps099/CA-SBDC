

<?php $related_post_ids = ct_get_related_posts( get_the_ID(), array( 'post_topic' => 1 ), array( 'fields' => 'ids', 'post_type' => array( 'resource', 'resource_s' ) ) ); ?>
<?php if ( ! empty( $related_post_ids ) && count( $related_post_ids ) >= 1 ) { ?>
	<div id="related-content">
		<h3 class="related-content-title">Related Resources</h3>
		<?php
			$block = array(
				'blockName' => 'crown-blocks/featured-resource-slider',
				'attrs' => array(
					'manuallySelectPosts' => true,
					'filterPostsInclude' => array_map( function ( $n ) { return array( 'id' => $n ); }, $related_post_ids )
				)
			);
			echo render_block( $block );
		?>
	</div>
<?php } ?>