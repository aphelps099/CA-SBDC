<?php $announcement = apply_filters( 'crown_site_announcement', null ); ?>
<?php if ( $announcement ) { ?>
	<div id="site-announcement" data-announcement-id="<?php echo esc_attr( md5( json_encode( $announcement ) ) ); ?>">
		<div class="inner">

			<button type="button" class="dismiss">Dismiss</button>

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