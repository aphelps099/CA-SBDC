<?php
/**
 * Default Template File for FEEDZY RSS Feeds
 *
 * @package feedzy-rss-feeds-pro
 * @subpackage feedzy-rss-feeds-pro/templates
 */
?>
<div class="<?php echo feedzy_feed_class(); ?>">
	<?php if ( $feed_title['use_title'] ) { ?>
		<div class="rss_header">
			<h2>
				<a href="<?php echo feedzy_feed_link(); ?>" class="<?php echo $feed_title['rss_title_class']; ?>" rel="noopener">
					<?php echo feedzy_feed_title(); ?>
				</a>
				<span class="<?php echo $feed_title['rss_description_class']; ?>">
				<?php echo feedzy_feed_desc(); ?>
			</span>
			</h2>
		</div>
	<?php } ?>
	<ul class="feedzy-default">
		<?php foreach ( $feed_items as $item ) { ?>
			<li <?php echo $item['itemAttr']; ?> >
				<?php if ( ! empty( $item['item_img'] ) && $sc['thumb'] !== 'no' ) { ?>
					<div class="<?php echo $item['item_img_class']; ?>" style="<?php echo $item['item_img_style']; ?>">
						<a href="<?php echo feedzy_feed_item_link( $item ); ?>" target="<?php echo $item['item_url_target']; ?>" 
						   rel="noopener <?php echo isset( $item['item_url_follow'] ) ? $item['item_url_follow'] : ''; ?>"
						   title="<?php echo $item['item_url_title']; ?>"
						   style="<?php echo $item['item_img_style']; ?>">
							<?php echo feedzy_feed_item_image( $item ); ?>
						</a>
					</div>
				<?php } ?>
					<span class="title">
						<a href="<?php echo feedzy_feed_item_link( $item ); ?>" target="<?php echo $item['item_url_target']; ?>"  rel="noopener <?php echo isset( $item['item_url_follow'] ) ? $item['item_url_follow'] : ''; ?>">
							<?php echo feedzy_feed_item_title( $item ); ?>
						</a>
					</span>
					<div class="<?php echo $item['item_content_class']; ?>"
						 style="<?php echo $item['item_content_style']; ?>">

						<?php
						if ( ! empty( $item['item_meta'] ) ) {
							?>
							<small>
								<?php echo feedzy_feed_item_meta( $item ); ?>
							</small>
						<?php } ?>

						<?php
						if ( ! empty( $item['item_description'] ) ) {
							?>
							<p><?php echo feedzy_feed_item_desc( $item ); ?></p>
						<?php } ?>

						<?php echo feedzy_feed_item_media( $item ); ?>
					</div>
			</li>
			<?php
		}// End foreach.
		?>
	</ul>
</div>
