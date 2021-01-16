<?php

if(!class_exists('Crown_Block_Event_Index')) {
	class Crown_Block_Event_Index extends Crown_Block {


		public static $name = 'event-index';


		public static function init() {
			parent::init();
		}


		public static function get_attributes() {
			return array(
				'className' => array( 'type' => 'string', 'default' => '' ),
				'postsPerPage' => array( 'type' => 'string', 'default' => '10' )
			);
		}


		public static function render( $atts, $content ) {
			global $post;
			if ( ! class_exists( 'Crown_Events' ) ) return '';

			$filters = (object) array(
				'topic' => (object) array( 'key' => 'e_topic', 'queried' => null, 'options' => array() ),
				'series' => (object) array( 'key' => 'e_series', 'queried' => null, 'options' => array() ),
				'month' => (object) array( 'key' => 'e_month', 'queried' => null, 'options' => array() ),
				'center' => (object) array( 'key' => 'e_center', 'queried' => null, 'options' => array() ),
			);

			$event_args = array(
				'from' => date( 'Y-m-d H:i:s' ),
				'tax_query' => array()
			);

			$filters->topic->queried = isset( $_GET[ $filters->topic->key ] ) ? ( is_array( $_GET[ $filters->topic->key ] ) ? $_GET[ $filters->topic->key ] : array_filter( array_map( 'trim', explode( ',', $_GET[ $filters->topic->key ] ) ), function( $n ) { return ! empty( $n ); } ) ) : array();
			if ( ! empty( $filters->topic->queried ) ) $event_args['tax_query'][] = array( 'taxonomy' => 'post_topic', 'terms' => $filters->topic->queried );

			$filters->series->queried = isset( $_GET[ $filters->series->key ] ) ? ( is_array( $_GET[ $filters->series->key ] ) ? $_GET[ $filters->series->key ] : array_filter( array_map( 'trim', explode( ',', $_GET[ $filters->series->key ] ) ), function( $n ) { return ! empty( $n ); } ) ) : array();
			if ( ! empty( $filters->series->queried ) ) $event_args['tax_query'][] = array( 'taxonomy' => 'event_series', 'terms' => $filters->series->queried );

			$filters->month->queried = isset( $_GET[ $filters->month->key ] ) ? trim( $_GET[ $filters->month->key ] ) : '';
			if ( ! empty( $filters->month->queried ) && preg_match( '/^(\d{1,2})-(\d{4})$/', $filters->month->queried, $matches ) ) {
				$m = str_pad( $matches[1], 2, '0', STR_PAD_LEFT );
				$y = $matches[2];
				$filters->month->queried = $m . '-' . $y;
				$queried_date = new DateTime( $y . '-' . $m . '-1 00:00:00' );
				$event_args['from'] = $queried_date->format( 'Y-m-d H:i:s' );
				$queried_date->modify( 'first day of next month' );
				$event_args['to'] = $queried_date->format( 'Y-m-d H:i:s' );
			}

			$filters->center->queried = isset( $_GET[ $filters->center->key ] ) ? ( is_array( $_GET[ $filters->center->key ] ) ? $_GET[ $filters->center->key ] : array_filter( array_map( 'trim', explode( ',', $_GET[ $filters->center->key ] ) ), function( $n ) { return ! empty( $n ); } ) ) : array();
			if ( ! empty( $filters->center->queried ) ) $event_args['tax_query'][] = array( 'taxonomy' => 'post_center', 'terms' => $filters->center->queried );

			$query_args = Crown_Events::get_event_query_args( $event_args );
			$query_args = array_merge( $query_args, array(
				'posts_per_page' => $atts['postsPerPage'],
				'paged' => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1
			) );
			$query = new WP_Query( $query_args );

			$filters_action = remove_query_arg( array(
				$filters->topic->key,
				$filters->series->key,
				$filters->month->key
			) );
			$filters_action = preg_replace( '/\/page\/\d+\/(\?.*)?$/', "/$1", $filters_action );

			$filters->topic->options = array_map( function( $n ) use ( $filters ) {
				return (object) array( 'value' => $n->term_id, 'label' => $n->name, 'selected' => in_array( $n->term_id, $filters->topic->queried ) );
			}, get_terms( array( 'taxonomy' => 'post_topic' ) ) );

			$filters->series->options = array_map( function( $n ) use ( $filters ) {
				return (object) array( 'value' => $n->term_id, 'label' => $n->name, 'selected' => in_array( $n->term_id, $filters->series->queried ) );
			}, get_terms( array( 'taxonomy' => 'event_series' ) ) );

			$current_month = new DateTime( current_time( 'Y-m-d H:i:s' ) );
			$current_month->modify( 'first day of this month' )->modify( 'today' );
			$n = clone $current_month;
			$n->modify( '4 months ago' );
			for ( $i = 0; $i < 12; $i++ ) {
				$filters->month->options[] = (object) array( 'value' => $n->format( 'm-Y' ), 'label' => $n->format( 'M' ), 'is_past' => $n < $current_month, 'selected' => $n->format( 'm-Y' ) == $filters->month->queried );
				$n->modify( '+1 month' );
			}

			$block_class = array( 'wp-block-crown-blocks-event-index', 'post-feed-block', $atts['className'] );
			$block_id = 'post-feed-block-' . md5( json_encode( array( 'event-index', $atts ) ) );

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

								<nav class="filters-nav">
									<ul>
										<?php if ( ! empty( $filters->topic->options ) ) { ?><li><button type="button" data-tab="topic"><?php _e( 'Topic', 'crown_blocks' ); ?></button></li><?php } ?>
										<?php if ( ! empty( $filters->month->options ) ) { ?><li><button type="button" data-tab="month"><?php _e( 'Month', 'crown_blocks' ); ?></button></li><?php } ?>
										<?php if ( ! empty( $filters->series->options ) ) { ?><li><button type="button" data-tab="series"><?php _e( 'Series', 'crown_blocks' ); ?></button></li><?php } ?>
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

									<?php if ( ! empty( $filters->month->options ) ) { ?>
										<div class="filters-tab" data-tab="month">
											<ul class="options singular month">
												<?php foreach ( $filters->month->options as $option ) { ?>
													<li class="option <?php echo $option->is_past ? 'past' : ''; ?>">
														<label>
															<input type="checkbox" name="<?php echo $filters->month->key; ?>" value="<?php echo esc_attr( $option->value ); ?>" <?php echo $option->selected ? 'checked' : ''; ?>>
															<span class="label"><?php echo $option->label; ?></span>
														</label>
													</li>
												<?php } ?>
											</ul>
										</div>
									<?php } ?>

									<?php if ( ! empty( $filters->series->options ) ) { ?>
										<div class="filters-tab" data-tab="series">
											<ul class="options series">
												<?php foreach ( $filters->series->options as $option ) { ?>
													<li class="option">
														<label>
															<input type="checkbox" name="<?php echo $filters->series->key; ?>[]" value="<?php echo esc_attr( $option->value ); ?>" <?php echo $option->selected ? 'checked' : ''; ?>>
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

						<div class="ajax-loader">
							<div class="ajax-content">

								<div class="post-feed item-count-<?php echo $query->post_count; ?>" data-item-count="<?php echo $query->post_count; ?>">
									<div class="inner">
	
										<?php if ( ! $query->have_posts() ) { ?>
											<div class="alert-wrapper">
												<div class="alert alert-info no-results">
													<h4>No Events Found</h4>
													<p>Please try adjusting your selected filters above.</p>
												</div>
											</div>
										<?php } ?>
										
										<?php while ( $query->have_posts() ) { ?>
											<?php $query->the_post(); ?>
											<article <?php post_class(); ?>>
												<a href="<?php the_permalink(); ?>" data-post-id="<?php echo get_the_ID(); ?>">
													<div class="inner">

														<div class="entry-event-date-container">
															<?php if ( function_exists( 'ct_event_date' ) ) ct_event_date( get_the_ID() ); ?>
														</div>

														<div class="entry-teaser">

															<header class="entry-header">

																<?php $centers = get_the_terms( get_the_ID(), 'post_center' ); ?>
																<?php if ( ! empty( $centers ) ) { ?>
																	<p class="entry-centers">
																		<?php foreach ( $centers as $term ) { ?>
																			<span class="center"><?php echo $term->name; ?></span>
																		<?php } ?>
																	</p>
																<?php } ?>

																<h3 class="entry-title"><?php the_title(); ?></h3>

															</header>

															<div class="entry-excerpt"><?php the_excerpt(); ?></div>

															<p class="entry-link">
																<span class="btn btn-link btn-link-blue btn-has-arrow-icon"><?php _e( 'View Event Details', 'crown_blocks' ); ?></span>
															</p>

														</div>

													</div>
												</a>
											</article>
										<?php } ?>
										<?php wp_reset_postdata(); ?>
	
									</div>
								</div>
	
								<?php self::render_pagination( $query, 5 ); ?>

							</div>
						</div>

					</div>
				</div>

			<?php
			$output = ob_get_clean();

			return $output;
		}


		protected static function render_pagination( $query, $max_page_links = 5, $scroll_anchor = '' ) {

			$pagination = self::get_pagination( $query, $scroll_anchor );
			if(!$pagination) return;

			$index_min = max( 1, $pagination->current_page - ceil( ( $max_page_links - 2 ) / 2 ) );
			$index_max = min( count( $pagination->pages ) - 2, $pagination->current_page - 1 + floor( ( $max_page_links - 2 ) / 2 ) );

			$index_min = $index_min == 1 ? 0 : $index_min;
			$index_max = $index_max == count( $pagination->pages ) - 2 ? count( $pagination->pages ) - 1 : $index_max;

			if ( $index_max - $index_min + 2 < $max_page_links ) {
				if ( $index_min == 0 ) {
					$index_max = $index_max + ( $max_page_links - ( $index_max - $index_min + 2 ) );
					$index_max = $index_max == count( $pagination->pages ) - 2 ? count( $pagination->pages ) - 1 : $index_max;
				} else if ( $index_max == count( $pagination->pages ) - 1 ) {
					$index_min = $index_min - ( $max_page_links - ( $index_max - $index_min + 2 ) );
					$index_min = $index_min == 1 ? 0 : $index_min;
				}
			}

			?>
				<div class="pagination-wrapper">
					<nav class="navigation pagination">
						<h3 class="screen-reader-text">Page Navigation</h3>
						<div class="nav-links">
	
							<?php if ( ! empty( $pagination->prev ) ) { ?>
								<a class="page-numbers prev" href="<?php echo esc_attr( $pagination->prev ); ?>"><span>Previous</span></a>
							<?php } else { ?>
								<span class="page-numbers prev"><span>Previous</span></span>
							<?php } ?>
	
							<?php if ( $index_min > 0 ) { ?>
								<a class="page-numbers <?php echo $pagination->current_page == 1 ? 'current' : ''; ?>" href="<?php echo esc_attr( $pagination->pages[0] ); ?>"><span>1</span></a>
								<span class="page-numbers dots"><span>&hellip;</span></span>
							<?php } ?>
							
							<?php $page_links = array_slice( $pagination->pages, $index_min, $index_max - $index_min + 1 ); ?>
							<?php foreach ( $page_links as $i => $page_link ) { ?>
								<a class="page-numbers <?php echo $pagination->current_page == $i + $index_min + 1 ? 'current' : ''; ?>" href="<?php echo esc_attr( $page_link ); ?>"><span><?php echo $i + $index_min + 1; ?></span></a>
							<?php } ?>
	
							<?php if ( $index_max < count( $pagination->pages ) - 1 ) { ?>
								<span class="page-numbers dots"><span>&hellip;</span></span>
								<a class="page-numbers" href="<?php echo esc_attr( $pagination->pages[ count( $pagination->pages ) - 1 ] ); ?>"><span><?php echo count( $pagination->pages ); ?></span></a>
							<?php } ?>
	
							<?php if ( ! empty( $pagination->next ) ) { ?>
								<a class="page-numbers next" href="<?php echo esc_attr( $pagination->next ); ?>"><span>Next</span></a>
							<?php } else { ?>
								<span class="page-numbers next"><span>Next</span></span>
							<?php } ?>
	
						</div>
					</nav>
				</div>
			<?php
		}


		protected static function get_pagination( $query, $scroll_anchor = '' ) {

			$page = $query->get( 'paged' );
			if ( ! $page ) $page = 1;
			$maxPage = $query->max_num_pages;
			if ( $maxPage < 2 ) return false;

			$pagination = (object) array(
				'current_page' => $page,
				'pages' => array(),
				'next' => null,
				'prev' => null
			);

			for ( $i = 1; $i <= $maxPage; $i++ ) {
				$pagination->pages[] = get_pagenum_link( $i ) . ( ! empty( $scroll_anchor ) ? '#' . $scroll_anchor : '' );
			}

			$nextPage = intval( $page ) + 1;
			if ( ! is_single() && ( $nextPage <= $maxPage ) ) {
				$pagination->next = next_posts( $maxPage, false ) . ( ! empty( $scroll_anchor ) ? '#' . $scroll_anchor : '' );
			}

			if ( ! is_single() && $page > 1 ) {
				$pagination->prev = previous_posts( false ) . ( ! empty( $scroll_anchor ) ? '#' . $scroll_anchor : '' );
			}

			return $pagination;
		}


	}
	Crown_Block_Event_Index::init();
}