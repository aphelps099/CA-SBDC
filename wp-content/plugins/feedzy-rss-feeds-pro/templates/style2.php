<?php
/**
 * Style 1 Template File for FEEDZY RSS Feeds
 * Styles work if Feed title is set to 'yes' when using this template
 * Another way is to write the styles in your theme style.css and
 * target the classe/id 's you add here
 *
 * @package feedzy-rss-feeds-pro
 * @subpackage feedzy-rss-feeds-pro/templates
 */
?>
<div class="<?php echo feedzy_feed_class(); ?>">
	<?php if ( $feed_title['use_title'] ) { ?>
		<h2>
			<a href="<?php echo feedzy_feed_link(); ?>" class="<?php echo $feed_title['rss_title_class']; ?>" rel="noopener">
				<?php echo feedzy_feed_title(); ?>
			</a>
			<span class="<?php echo $feed_title['rss_description_class']; ?>">
				<?php echo feedzy_feed_desc(); ?>
			</span>
		</h2>
	<?php } ?>
	<ul class="feedzy-style2">
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
				<div class="rss_content_wrap">
					<span class="title">
						<a href="<?php echo feedzy_feed_item_link( $item ); ?>" target="<?php echo $item['item_url_target']; ?>"  rel="noopener <?php echo isset( $item['item_url_follow'] ) ? $item['item_url_follow'] : ''; ?>">
							<?php echo feedzy_feed_item_title( $item ); ?>
						</a>
					</span>
					<div class="<?php echo $item['item_content_class']; ?>"
						 style="<?php echo $item['item_content_style']; ?>">
						<small class="meta"><?php echo feedzy_feed_item_meta( $item ); ?></small>

						<p class="description"><?php echo feedzy_feed_item_desc( $item ); ?></p>

						<?php echo feedzy_feed_item_media( $item ); ?>

						<?php if ( ! empty( $item['item_price'] ) && $sc['price'] !== 'no' ) { ?>
							<div class="price-wrap">
								<a href="<?php echo feedzy_feed_item_link( $item ); ?>" target="_blank" rel="noopener"><button class="price"> <?php echo feedzy_feed_item_price( $item ); ?></button></a>
							</div>
						<?php } ?>
					</div>
				</div>
			</li>
			<?php
		}// End foreach.
		?>
	</ul>
</div>
