<footer class="entry-footer">
	<div class="inner">

		<div class="continue-reading">
			<h4><?php _e( 'Continue Reading', 'crown_theme' ) ?></h4>
			<div class="links">

				<?php

					$start = get_post_meta( get_the_ID(), 'event_start_timestamp_utc', true );
					$end = get_post_meta( get_the_ID(), 'event_end_timestamp_utc', true );

					$previous_event = null;
					$past_event_ids = Crown_Events::get_events( array( 'count' => 10, 'to' => $end, 'order' => 'DESC', 'fields' => 'ids' ) );
					foreach ( $past_event_ids as $i => $id ) {
						if ( $id == get_the_ID() && isset( $past_event_ids[ $i + 1 ] ) ) {
							$previous_event = get_post( $past_event_ids[ $i + 1 ] );
							break;
						} 
					}

					$next_event = null;
					$upcoming_event_ids = Crown_Events::get_events( array( 'count' => 10, 'from' => $start, 'order' => 'ASC', 'fields' => 'ids' ) );
					foreach ( $upcoming_event_ids as $i => $id ) {
						if ( $id == get_the_ID() && isset( $upcoming_event_ids[ $i + 1 ] ) ) {
							$next_event = get_post( $upcoming_event_ids[ $i + 1 ] );
							break;
						} 
					}

				?>

				<?php if ( $previous_event ) { ?>
					<a href="<?php echo get_permalink( $previous_event->ID ); ?>" rel="prev">
						<span class="label">Previous Entry</span>
						<span class="title"><?php echo get_the_title( $previous_event->ID ); ?></span>
					</a>
				<?php } ?>

				<?php if ( $next_event ) { ?>
					<a href="<?php echo get_permalink( $next_event->ID ); ?>" rel="next">
						<span class="label">Next Entry</span>
						<span class="title"><?php echo get_the_title( $next_event->ID ); ?></span>
					</a>
				<?php } ?>

			</div>
		</div>

	</div>
</footer>