<?php

if(!class_exists('Crown_Block_Post_Event_Index')) {
	class Crown_Block_Post_Event_Index extends Crown_Block {


		public static $name = 'post-event-index';


		public static function init() {
			parent::init();
		}


		public static function get_attributes() {
			return array(
				'className' => array( 'type' => 'string', 'default' => '' ),
				'postsPerPage' => array( 'type' => 'string', 'default' => '6' )
			);
		}


		public static function render( $atts, $content ) {
			global $post;

			$filters = (object) array(
				'type' => (object) array( 'key' => 'p_type', 'queried' => null, 'options' => array() ),
				'topic' => (object) array( 'key' => 'p_topic', 'queried' => null, 'options' => array() ),
				'search' => (object) array( 'key' => 'p_search', 'queried' => null, 'options' => array() )
			);

			// $atts['postsPerPage'] = 1;
			$query_args = array(
				'post_type' => 'post',
				'posts_per_page' => $atts['postsPerPage'],
				'paged' => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
				'tax_query' => array(),
				'meta_query' => array()
			);

			$filters->type->queried = isset( $_GET[ $filters->type->key ] ) ? trim( $_GET[ $filters->type->key ] ) : 'post';
			if ( ! empty( $filters->type->queried ) ) $query_args['post_type'] = $filters->type->queried;

			$filters->topic->queried = isset( $_GET[ $filters->topic->key ] ) ? ( is_array( $_GET[ $filters->topic->key ] ) ? $_GET[ $filters->topic->key ] : array_filter( array_map( 'trim', explode( ',', $_GET[ $filters->topic->key ] ) ), function( $n ) { return ! empty( $n ); } ) ) : array();
			if ( ! empty( $filters->topic->queried ) ) $query_args['tax_query'][] = array( 'taxonomy' => 'post_topic', 'terms' => $filters->topic->queried );

			$filters->search->queried = isset( $_GET[ $filters->search->key ] ) ? trim( $_GET[ $filters->search->key ] ) : '';
			if ( ! empty( $filters->search->queried ) ) $query_args['s'] = $filters->search->queried;

			if ( $query_args['post_type'] == 'event' ) {
				$event_args = array(
					'from' => date( 'Y-m-d H:i:s' ),
					'tax_query' => $query_args['tax_query'],
					'include_syndicated' => true
				);
				$query_args = Crown_Events::get_event_query_args( $event_args );
				$query_args = array_merge( $query_args, array(
					'posts_per_page' => $atts['postsPerPage'],
					'paged' => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1
				) );
				if ( ! empty( $filters->search->queried ) ) $query_args['s'] = $filters->search->queried;
			}

			// $query_args['posts_per_page'] = 1;

			$query = null;
			if ( function_exists( 'relevanssi_do_query' ) && isset( $query_args['s'] ) && ! empty( $query_args['s'] ) ) {
				$query = new WP_Query();
				$query->parse_query( $query_args );
				relevanssi_do_query( $query );
			} else {
				$query = new WP_Query( $query_args );
			}

			$filters_action = remove_query_arg( array(
				$filters->type->key,
				$filters->topic->key,
				$filters->search->key
			) );
			$filters_action = preg_replace( '/\/page\/\d+\/(\?.*)?$/', "/$1", $filters_action );

			$filters->type->options = array();
			$filters->type->options[] = (object) array( 'value' => 'post', 'label' => 'News', 'selected' => $filters->type->queried == 'post' );
			$filters->type->options[] = (object) array( 'value' => 'event', 'label' => 'Events', 'selected' => $filters->type->queried == 'event' );

			$filters->topic->options = array_map( function( $n ) use ( $filters ) {
				return (object) array( 'value' => $n->term_id, 'label' => $n->name, 'selected' => in_array( $n->term_id, $filters->topic->queried ) );
			}, get_terms( array( 'taxonomy' => 'post_topic' ) ) );

			$block_class = array( 'wp-block-crown-blocks-post-event-index', 'post-feed-block', $atts['className'] );
			$block_id = 'post-feed-block-' . md5( json_encode( array( 'post-event-index', $atts ) ) );

			ob_start();
			// print_r($query_args);
			?>

				<div id="<?php echo $block_id; ?>" class="<?php echo implode( ' ', $block_class ); ?>">
					<div class="inner">

						<form action="<?php echo $filters_action; ?>" method="get" class="feed-filters">

							<header class="filters-header">

								<button type="button" class="filters-toggle"><span><?php _e( 'Filter', 'crown_blocks' ); ?></span></button>
								<button type="button" class="filters-clear"><span><?php _e( 'Clear', 'crown_blocks' ); ?></span></button>
								<button type="button" class="filters-close"><span><?php _e( 'Close', 'crown_blocks' ); ?></span></button>

								<?php if ( ! empty( $filters->type->options ) ) { ?>
									<ul class="options singular quick-filters">
										<?php foreach ( $filters->type->options as $option ) { ?>
											<li class="option">
												<label>
													<input type="radio" name="<?php echo $filters->type->key; ?>" value="<?php echo esc_attr( $option->value ); ?>" <?php echo $option->selected ? 'checked' : ''; ?>>
													<span class="label"><?php echo $option->label; ?></span>
												</label>
											</li>
										<?php } ?>
									</ul>
								<?php } ?>

								<?php /*<button type="button" class="search-field-toggle"><span><?php _e( 'Search', 'crown_blocks' ); ?></span></button>
								<div class="search-field-spacer"></div>
								<div class="search-field">
									<input type="text" name="<?php echo $filters->search->key; ?>" value="<?php echo esc_attr( $filters->search->queried ); ?>" placeholder="<?php echo esc_attr( __( 'Search' ), 'crown_blocks' ); ?>">
								</div>*/ ?>

								<nav class="filters-nav">
									<ul>
										<?php if ( ! empty( $filters->topic->options ) ) { ?><li><button type="button" data-tab="topic"><?php _e( 'Topic', 'crown_blocks' ); ?></button></li><?php } ?>
									</ul>
								</nav>

								<div class="spacer"></div>

							</header>

							<div class="filters-tabs">
								<div class="inner">

									<?php if ( ! empty( $filters->topic->options ) ) { ?>
										<div class="filters-tab" data-tab="topic">
											<ul class="options topic">
												<?php foreach ( $filters->topic->options as $option ) { ?>
													<li class="option">
														<label>
															<input type="checkbox" name="<?php echo $filters->topic->key; ?>[]" value="<?php echo esc_attr( $option->value ); ?>" <?php echo $option->selected ? 'checked' : ''; ?>>
															<span class="label"><?php echo $option->label; ?></span>
														</label>
													</li>
												<?php } ?>
											</ul>
										</div>
									<?php } ?>
	
								</div>
							</div>

							<footer class="filters-footer">
								<button type="submit"><?php _e( 'Submit', 'crown_blocks' ); ?></button>
							</footer>

						</form>

						<div class="ajax-loader infinite">
							<div class="ajax-content">

								<div class="post-feed item-count-<?php echo $query->post_count; ?> page-<?php echo $query_args['paged']; ?>" data-item-count="<?php echo $query->post_count; ?>">
									<div class="inner infinite-loader-container">
	
										<?php if ( ! $query->have_posts() ) { ?>
											<div class="alert-wrapper">
												<div class="alert alert-info no-results">
													<h4>No Entries Found</h4>
													<p>Please try adjusting your selected filters above.</p>
												</div>
											</div>
										<?php } ?>
										
										<?php while ( $query->have_posts() ) { ?>
											<?php $query->the_post(); ?>
											<article <?php post_class(); ?>>
												<a href="<?php the_permalink(); ?>" data-post-id="<?php echo get_the_ID(); ?>">
													<div class="inner">

														<div class="image-wrap">
															<div class="entry-featured-image">
																<div class="image">
																	<?php $image_src = has_post_thumbnail() ? wp_get_attachment_image_url( get_post_thumbnail_id(), 'medium_large' ) : false; ?>
																	<?php if ( ! empty( $image_src ) ) { ?>
																		<?php echo wp_get_attachment_image( get_post_thumbnail_id(), 'medium_large' ) ?>
																	<?php } ?>
																</div>
															</div>
															<div class="labels">
																<?php if ( get_post_type() == 'event' ) { ?>
																	<span class="type">Event</span>
																<?php } else if ( get_post_type() == 'post' ) { ?>
																	<span class="type">News</span>
																<?php } ?>
															</div>
														</div>
	
														<div class="entry-teaser">
	
															<header class="entry-header">

																<h3 class="entry-title"><?php the_title(); ?></h3>
																
																<?php if ( get_post_type() == 'event' ) { ?>
																	<?php if ( function_exists( 'ct_event_date' ) ) ct_event_date( get_the_ID(), true ); ?>
																<?php } else { ?>
																	<?php $relative_time = self::get_relative_time( get_the_time( 'Y-m-d H:i:s' ) ); ?>
																	<?php if ( $relative_time && ! in_array( $relative_time->units, array( 'years', 'months' ) ) ) { ?>
																		<p class="entry-date"><?php echo abs( $relative_time->value ) . ' ' . $relative_time->units_contextual . ( $relative_time->value <= 0 ? ' ago' : '' ); ?></p>
																	<?php } else { ?>
																		<p class="entry-date"><?php echo get_the_time( 'j F, Y' ); ?></p>
																	<?php } ?>
																<?php } ?>
	
															</header>

															<div class="entry-link"></div>
		
														</div>

													</div>
												</a>
											</article>

										<?php } ?>
										<?php wp_reset_postdata(); ?>
	
									</div>
								</div>
	
								<?php //self::render_pagination( $query, 5 ); ?>
								<?php self::render_pagination_infinite( $query ); ?>

							</div>
						</div>

					</div>
				</div>

			<?php
			$output = ob_get_clean();

			return $output;
		}


		public static function get_relative_time( $timestamp, $current_timestamp = null ) {

			$date = strtotime( $timestamp ) !== false ? new DateTime( $timestamp ) : false;
			if ( ! $date ) return false;

			$current_date = $current_timestamp !== null ? ( strtotime( $current_timestamp ) !== false ? new DateTime( $current_timestamp ) : false ) : new DateTime( current_time( 'Y-m-d H:i:s' ) );
			if ( ! $current_date ) return false;

			$diff = $current_date->diff( $date );

			$time = (object) array(
				'value' => 0,
				'units' => '',
				'units_contextual' => ''
			);

			if ( intval( $diff->format( '%y' ) ) > 0 ) {
				$time->value = intval( $diff->format( '%r%y' ) );
				$time->units = 'years';
				$time->units_contextual = abs( $time->value ) == 1 ? 'year' : 'years';
			} else if ( intval( $diff->format( '%m' ) ) > 0 ) {
				$time->value = intval( $diff->format( '%r%m' ) );
				$time->units = 'months';
				$time->units_contextual = abs( $time->value ) == 1 ? 'month' : 'months';
			} else if ( intval( $diff->format( '%d' ) ) > 0 ) {
				$time->value = intval( $diff->format( '%r%d' ) );
				$time->units = 'days';
				$time->units_contextual = abs( $time->value ) == 1 ? 'day' : 'days';
			} else if ( intval( $diff->format( '%h' ) ) > 0 ) {
				$time->value = intval( $diff->format( '%r%h' ) );
				$time->units = 'hours';
				$time->units_contextual = abs( $time->value ) == 1 ? 'hour' : 'hours';
			} else {
				$time->value = intval( $diff->format( '%r%i' ) );
				$time->units = 'minutes';
				$time->units_contextual = abs( $time->value ) == 1 ? 'minute' : 'minutes';
			}

			return $time;

		}


	}
	Crown_Block_Post_Event_Index::init();
}