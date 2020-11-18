<?php $announcement = apply_filters( 'crown_site_announcement', null ); ?>
<?php if ( $announcement ) { ?>
	<div id="site-announcement">
		<div class="inner">
			<div class="container">
				<div class="inner">

					<?php if ( ! empty( $announcement->message ) ) { ?>	
						<p class="message"><?php echo $announcement->message; ?></p>
					<?php } ?>

					<?php if ( ! empty( $announcement->link->url ) ) { ?>	
						<p class="link">
							<a href="<?php echo $announcement->link->url; ?>" target="<?php echo in_array( 'open-new-window', $announcement->link->options ) ? '_blank' : '_self'; ?>" class="btn btn-sm btn-sky-blue">
								<?php echo ! empty( $announcement->link->label ) ? $announcement->link->label : 'Learn More'; ?>
							</a>
						</p>
					<?php } ?>

				</div>
			</div>
		</div>
	</div>
<?php } ?>