<?php

if(!class_exists('Crown_Block_Faq_Index')) {
	class Crown_Block_Faq_Index extends Crown_Block {


		public static $name = 'faq-index';


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

			if ( is_singular( 'faq' ) ) {
				return self::render_single_faq( $atts, $content );
			}

			$filters = (object) array(
				'topic' => (object) array( 'key' => 'f_topic', 'queried' => null, 'options' => array() ),
				'search' => (object) array( 'key' => 'f_search', 'queried' => null, 'options' => array() )
			);

			// $atts['postsPerPage'] = 1;
			$query_args = array(
				'post_type' => 'faq',
				'posts_per_page' => $atts['postsPerPage'],
				'paged' => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
				'tax_query' => array(),
				'meta_query' => array()
			);

			$filters->topic->queried = isset( $_GET[ $filters->topic->key ] ) ? ( is_array( $_GET[ $filters->topic->key ] ) ? $_GET[ $filters->topic->key ] : array_filter( array_map( 'trim', explode( ',', $_GET[ $filters->topic->key ] ) ), function( $n ) { return ! empty( $n ); } ) ) : array();
			if ( ! empty( $filters->topic->queried ) ) $query_args['tax_query'][] = array( 'taxonomy' => 'faq_topic', 'terms' => $filters->topic->queried );

			$filters->search->queried = isset( $_GET[ $filters->search->key ] ) ? trim( $_GET[ $filters->search->key ] ) : '';
			if ( ! empty( $filters->search->queried ) ) $query_args['s'] = $filters->search->queried;

			$query = new WP_Query( $query_args );

			$filters_action = remove_query_arg( array(
				$filters->topic->key,
				$filters->search->key
			) );
			$filters_action = preg_replace( '/\/page\/\d+\/(\?.*)?$/', "/$1", $filters_action );

			$filters->topic->options = array_map( function( $n ) use ( $filters ) {
				return (object) array( 'value' => $n->term_id, 'label' => $n->name, 'selected' => in_array( $n->term_id, $filters->topic->queried ) );
			}, get_terms( array( 'taxonomy' => 'faq_topic' ) ) );

			$block_class = array( 'wp-block-crown-blocks-faq-index', 'post-feed-block', $atts['className'] );
			$block_id = 'post-feed-block-' . md5( json_encode( array( 'faq-index', $atts ) ) );

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

								<button type="button" class="search-field-toggle"><span><?php _e( 'Search', 'crown_blocks' ); ?></span></button>
								<div class="search-field-spacer"></div>
								<div class="search-field">
									<input type="text" name="<?php echo $filters->search->key; ?>" value="<?php echo esc_attr( $filters->search->queried ); ?>" placeholder="<?php echo esc_attr( __( 'Search' ), 'crown_blocks' ); ?>">
								</div>

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

								<div class="post-feed item-count-<?php echo $query->post_count; ?>" data-item-count="<?php echo $query->post_count; ?>">
									<div class="inner infinite-loader-container">
	
										<?php if ( ! $query->have_posts() ) { ?>
											<div class="alert-wrapper">
												<div class="alert alert-info no-results">
													<h4>No FAQs Found</h4>
													<p>Please try adjusting your selected filters above.</p>
												</div>
											</div>
										<?php } ?>
										
										<?php while ( $query->have_posts() ) { ?>
											<?php $query->the_post(); ?>
											<article <?php post_class(); ?>>
												<a href="<?php the_permalink(); ?>" data-post-id="<?php echo get_the_ID(); ?>">
													<div class="inner">

														<h3 class="entry-title"><?php the_title(); ?></h3>
		
														<?php $relative_time = self::get_relative_time( get_the_time( 'Y-m-d H:i:s' ) ); ?>
														<?php if ( $relative_time ) { ?>
															<p class="entry-date"><?php _e( 'Answered', 'crown_blocks' ); ?> <?php echo abs( $relative_time->value ) . ' ' . $relative_time->units_contextual . ( $relative_time->value <= 0 ? ' ago' : '' ); ?></p>
														<?php } else { ?>
															<p class="entry-date"><?php _e( 'Answered', 'crown_blocks' ); ?> <?php echo get_the_time( 'j F, Y' ); ?></p>
														<?php } ?>
		
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


		protected static function render_single_faq( $atts, $content ) {
			global $post;

			$topic_ids = wp_get_post_terms( get_the_ID(), 'faq_topic', array( 'fields' => 'ids' ) );
			$primary_topic_id = get_post_meta( get_the_ID(), '_primary_term_faq_topic', true );
			if ( ! empty( $primary_topic_id ) && in_array( $primary_topic_id, $topic_ids ) ) array_unshift( $topic_ids, $primary_topic_id );
			$topic_ids = array_values( array_unique( $topic_ids ) );
			$primary_topic_id = ! empty( $topic_ids ) ? $topic_ids[0] : null;
			
			$topic_faqs = array();
			if ( $primary_topic_id ) {
				$topic_faqs = get_posts( array(
					'post_type' => 'faq',
					'posts_per_page' => -1,
					'tax_query' => array(
						array( 'taxonomy' => 'faq_topic', 'terms' => $primary_topic_id )
					)
				) );
			}

			$index_page_url = '';
			if ( ( $index_page_id = get_option( 'theme_config_index_page_faq' ) ) && ( $index_page = get_post( $index_page_id ) ) ) {
				$index_page_url = get_permalink( $index_page_id );
			}

			ob_start();
			?>

				<div class="wp-block-crown-blocks-faq-index-single">

					<div class="entry-contents">
	
						<header class="entry-header">

							<?php $topics = get_the_terms( get_the_ID(), 'faq_topic' ); ?>
							<?php if ( ! empty( $topics ) ) { ?>
								<p class="entry-topic">
									<?php foreach ( $topics as $term ) { ?>
										<?php if ( ! empty( $index_page_url ) ) { ?>
											<a class="topic" href="<?php echo add_query_arg( 'f_topic', $term->term_id, $index_page_url ); ?>"><?php echo $term->name; ?></a>
										<?php } else { ?>
											<span class="topic"><?php echo $term->name; ?></span>
										<?php } ?>
									<?php } ?>
								</p>
							<?php } ?>
	
							<h2 class="entry-title"><?php the_title(); ?></h2>
	
							<?php $relative_time = self::get_relative_time( get_the_time( 'Y-m-d H:i:s' ) ); ?>
							<?php if ( $relative_time ) { ?>
								<p class="entry-date"><?php _e( 'Answered', 'crown_blocks' ); ?> <?php echo abs( $relative_time->value ) . ' ' . $relative_time->units_contextual . ( $relative_time->value <= 0 ? ' ago' : '' ); ?></p>
							<?php } else { ?>
								<p class="entry-date"><?php _e( 'Answered', 'crown_blocks' ); ?> <?php echo get_the_time( 'j F, Y' ); ?></p>
							<?php } ?>
	
						</header>
	
						<div class="entry-content">
							<?php the_content(); ?>
						</div>
	
					</div>
	
					<div class="entry-sidebar">

						<?php if ( ! empty( $index_page_url ) ) { ?>
							<a href="<?php echo $index_page_url; ?>" class="return-to-index"><?php _e( 'All FAQs', 'crown_blocks' ); ?></a>
						<?php } ?>

						<?php if ( ! empty( $topic_faqs ) && count( $topic_faqs ) > 0 ) { ?>
							<nav class="related-entries">
								<h4><?php _e( 'Questions in this Section', 'crown_blocks' ); ?></h4>
								<ul class="menu">
									<?php foreach ( $topic_faqs as $faq ) { ?>
										<li class="menu-item <?php echo $faq->ID == get_the_ID() ? 'current-menu-item' : ''; ?>">
											<a href="<?php echo get_permalink( $faq->ID ); ?>"><?php echo get_the_title( $faq->ID ); ?></a>
										</li>
									<?php } ?>
								</ul>
							</nav>
						<?php } ?>

					</div>
					
				</div>

			<?php
			return ob_get_clean();
		}


	}
	Crown_Block_Faq_Index::init();
}