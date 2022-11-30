<?php

if(!class_exists('Crown_Block_Client_Story_Index')) {
	class Crown_Block_Client_Story_Index extends Crown_Block {


		public static $name = 'client-story-index';


		public static function init() {
			parent::init();
		}


		public static function get_attributes() {
			return array(
				'className' => array( 'type' => 'string', 'default' => '' ),
				'postsPerPage' => array( 'type' => 'string', 'default' => '6' ),
				'quoteContent' => array( 'type' => 'string', 'default' => '' ),
				'quoteSourceName' => array( 'type' => 'string', 'default' => '' ),
				'quoteSourceJobTitle' => array( 'type' => 'string', 'default' => '' ),
				'quoteSourcePhotoId' => array( 'type' => 'string', 'default' => '' )
			);
		}


		public static function render( $atts, $content ) {
			global $post;

			$filters = (object) array(
				'industry' => (object) array( 'key' => 'cs_industry', 'queried' => null, 'options' => array() ),
				'letter' => (object) array( 'key' => 'cs_letter', 'queried' => null, 'options' => array() ),
				'search' => (object) array( 'key' => 'cs_search', 'queried' => null, 'options' => array() ),
				'center' => (object) array( 'key' => 'cs_center', 'queried' => null, 'options' => array() )
			);

			// $atts['postsPerPage'] = 1;
			$query_args = array(
				'post_type' => array( 'client_story', 'client_story_s' ),
				'posts_per_page' => $atts['postsPerPage'],
				'paged' => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
				'tax_query' => array(),
				'meta_query' => array(
					array(
						'relation' => 'OR',
						array( 'key' => 'hide_from_index', 'value' => true, 'compare' => '!=' ),
						array( 'key' => 'hide_from_index', 'compare' => 'NOT EXISTS' )
					)
				)
			);

			$filters->industry->queried = isset( $_GET[ $filters->industry->key ] ) ? ( is_array( $_GET[ $filters->industry->key ] ) ? $_GET[ $filters->industry->key ] : array_filter( array_map( 'trim', explode( ',', $_GET[ $filters->industry->key ] ) ), function( $n ) { return ! empty( $n ); } ) ) : array();
			if ( ! empty( $filters->industry->queried ) ) $query_args['tax_query'][] = array( 'taxonomy' => 'client_story_industry', 'terms' => $filters->industry->queried );

			$filters->letter->queried = isset( $_GET[ $filters->letter->key ] ) ? strtolower( trim( $_GET[ $filters->letter->key ] ) ) : '';
			if ( ! empty( $filters->letter->queried ) ) {
				$query_args['meta_query'][] = array( 'key' => 'client_story_initial_lc', 'value' => $filters->letter->queried );
				$query_args['order_by'] = 'title';
				$query_args['order'] = 'ASC';
			}

			$filters->search->queried = isset( $_GET[ $filters->search->key ] ) ? trim( $_GET[ $filters->search->key ] ) : '';
			if ( ! empty( $filters->search->queried ) ) $query_args['s'] = $filters->search->queried;

			$filters->center->queried = isset( $_GET[ $filters->center->key ] ) ? ( is_array( $_GET[ $filters->center->key ] ) ? $_GET[ $filters->center->key ] : array_filter( array_map( 'trim', explode( ',', $_GET[ $filters->center->key ] ) ), function( $n ) { return ! empty( $n ); } ) ) : array();
			if ( ! empty( $filters->center->queried ) ) $query_args['tax_query'][] = array( 'taxonomy' => 'post_center', 'terms' => $filters->center->queried );

			$filters = apply_filters( 'crown_block_client_story_index_filters', $filters );
			$query_args = apply_filters( 'crown_block_client_story_index_query_args', $query_args, $filters );

			$query = null;
			if ( function_exists( 'relevanssi_do_query' ) && isset( $query_args['s'] ) && ! empty( $query_args['s'] ) ) {
				$query = new WP_Query();
				$query->parse_query( $query_args );
				relevanssi_do_query( $query );
			} else {
				$query = new WP_Query( $query_args );
			}

			// $filters_action = remove_query_arg( array(
			// 	$filters->industry->key,
			// 	$filters->letter->key,
			// 	$filters->search->key,
			// 	$filters->center->key
			// ) );
			$filters_action = remove_query_arg( array_map( function( $n ) { return $n->key; }, (array) $filters ) );
			$filters_action = preg_replace( '/\/page\/\d+\/(\?.*)?$/', "/$1", $filters_action );

			if ( property_exists( $filters, 'industry' ) ) {
				$filters->industry->options = array_map( function( $n ) use ( $filters ) {
					return (object) array( 'value' => $n->term_id, 'label' => $n->name, 'selected' => in_array( $n->term_id, $filters->industry->queried ) );
				}, get_terms( array( 'taxonomy' => 'client_story_industry' ) ) );
			}

			if ( property_exists( $filters, 'letter' ) ) {
				$filters->letter->options = array_map( function( $n ) use ( $filters ) {
					return (object) array( 'value' => strtolower( $n ), 'label' => $n, 'selected' => strtolower( $n ) == $filters->letter->queried );
				}, array( 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '#' ) );
			}

			$pull_quote = '';
			if ( ! property_exists( $filters, 'region' ) && ! empty( $atts['quoteContent'] ) ) {
				ob_start();
				?>
					<blockquote class="is-style-large">
						<?php echo apply_filters( 'the_content', $atts['quoteContent'] ); ?>
						<?php if ( ! empty( $atts['quoteSourceName'] ) || ! empty( $atts['quoteSourceJobTitle'] ) || ! empty( $atts['quoteSourcePhotoId'] ) ) { ?>
							<cite>
								<?php if ( ! empty( $atts['quoteSourcePhotoId'] ) ) { ?>
									<span class="photo"><?php echo wp_get_attachment_image( $atts['quoteSourcePhotoId'], 'thumbnail' ); ?></span>
								<?php } ?>
								<?php if ( ! empty( $atts['quoteSourceName'] ) ) { ?>
									<span class="name"><?php echo $atts['quoteSourceName']; ?></span>
								<?php } ?>
								<?php if ( ! empty( $atts['quoteSourceJobTitle'] ) ) { ?>
									<span class="job-title"><?php echo $atts['quoteSourceJobTitle']; ?></span>
								<?php } ?>
							</cite>
						<?php } ?>
					</blockquote>
				<?php
				$pull_quote = ob_get_clean();
			}
			$pull_quote_position = -1;
			if ( ! empty( $pull_quote ) && ! $query->is_paged && empty( $query_args['tax_query'] ) && empty( $filters->letter->queried ) && ! isset( $query_args['s'] ) ) {
				$pull_quote_position = ceil( $query->post_count / 2 );
			}

			$block_class = array( 'wp-block-crown-blocks-client-story-index', 'post-feed-block', $atts['className'] );
			$block_id = 'post-feed-block-' . md5( json_encode( array( 'client-story-index', $atts ) ) );

			ob_start();
			// print_r($query_args);
			?>

				<div id="<?php echo $block_id; ?>" class="<?php echo implode( ' ', $block_class ); ?>">
					<div class="inner">

						<form action="<?php echo $filters_action; ?>" method="get" class="feed-filters">

							<header class="filters-header">

								<?php if ( ! property_exists( $filters, 'region' ) ) { ?>
									<button type="button" class="filters-toggle"><span><?php _e( 'Filter', 'crown_blocks' ); ?></span></button>
									<button type="button" class="filters-clear"><span><?php _e( 'Clear', 'crown_blocks' ); ?></span></button>
									<button type="button" class="filters-close"><span><?php _e( 'Close', 'crown_blocks' ); ?></span></button>
								<?php } ?>

								<?php if ( property_exists( $filters, 'region' ) ) { ?>
									<ul class="options singular quick-filters">
										<?php foreach ( $filters->region->options as $option ) { ?>
											<li class="option">
												<label>
													<input type="checkbox" name="<?php echo $filters->region->key; ?>[]" value="<?php echo esc_attr( $option->value ); ?>" <?php echo $option->selected ? 'checked' : ''; ?>>
													<span class="label"><?php echo $option->label; ?></span>
												</label>
											</li>
										<?php } ?>
									</ul>
								<?php } ?>

								<?php if ( property_exists( $filters, 'search' ) ) { ?>
									<button type="button" class="search-field-toggle"><span><?php _e( 'Search', 'crown_blocks' ); ?></span></button>
									<div class="search-field-spacer"></div>
									<div class="search-field">
										<input type="text" name="<?php echo $filters->search->key; ?>" value="<?php echo esc_attr( $filters->search->queried ); ?>" placeholder="<?php echo esc_attr( __( 'Search' ), 'crown_blocks' ); ?>">
									</div>
								<?php } ?>

								<nav class="filters-nav">
									<ul>
										<?php if ( property_exists( $filters, 'letter' ) && ! empty( $filters->letter->options ) ) { ?><li><button type="button" data-tab="letter"><?php _e( 'Business Name', 'crown_blocks' ); ?></button></li><?php } ?>
										<?php if ( property_exists( $filters, 'industry' ) && ! empty( $filters->industry->options ) ) { ?><li><button type="button" data-tab="industry"><?php _e( 'Industry', 'crown_blocks' ); ?></button></li><?php } ?>
									</ul>
								</nav>

								<div class="spacer"></div>

							</header>

							<div class="filters-tabs">
								<div class="inner">

									<?php if ( property_exists( $filters, 'letter' ) && ! empty( $filters->letter->options ) ) { ?>
										<div class="filters-tab" data-tab="letter">
											<ul class="options singular name">
												<?php foreach ( $filters->letter->options as $option ) { ?>
													<li class="option">
														<label>
															<input type="checkbox" name="<?php echo $filters->letter->key; ?>" value="<?php echo esc_attr( $option->value ); ?>" <?php echo $option->selected ? 'checked' : ''; ?>>
															<span class="label"><?php echo $option->label; ?></span>
														</label>
													</li>
												<?php } ?>
											</ul>
										</div>
									<?php } ?>

									<?php if ( property_exists( $filters, 'industry' ) && ! empty( $filters->industry->options ) ) { ?>
										<div class="filters-tab" data-tab="industry">
											<ul class="options industry">
												<?php foreach ( $filters->industry->options as $option ) { ?>
													<li class="option">
														<label>
															<input type="checkbox" name="<?php echo $filters->industry->key; ?>[]" value="<?php echo esc_attr( $option->value ); ?>" <?php echo $option->selected ? 'checked' : ''; ?>>
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
													<h4>No Client Stories Found</h4>
													<p>Please try adjusting your selected filters above.</p>
												</div>
											</div>
										<?php } ?>
										
										<?php while ( $query->have_posts() ) { ?>
											<?php $query->the_post(); ?>

											<?php $permalink = get_the_permalink(); ?>

											<?php
												$switched_site = false;
												$client_story_site_title = null;
												if ( get_post_type() == 'client_story_s' ) {
													$original_post_id = get_post_meta( get_the_ID(), '_original_post_id', true );
													switch_to_blog( get_post_meta( get_the_ID(), '_original_site_id', true ) );
													$post = get_post( $original_post_id );
													setup_postdata( $post );
													if ( ! is_main_site() ) $client_story_site_title = get_bloginfo( 'name' );
													$switched_site = true;
												}
											?>

											<?php $color = get_post_meta( get_the_ID(), 'client_story_color', true ); ?>
											<?php if ( empty( $color ) ) $color = '#E0E0E0'; ?>

											<article <?php post_class(); ?>>
												<a href="<?php echo $permalink; ?>" data-post-id="<?php echo get_the_ID(); ?>" class="text-color-<?php echo self::is_dark_color( $color ) ? 'light' : 'dark'; ?>" style="background-color: <?php echo $color; ?>;">
													<div class="inner">

														<?php $image_src = has_post_thumbnail() ? wp_get_attachment_image_url( get_post_thumbnail_id(), 'medium_large' ) : false; ?>
														<?php if ( ! empty( $image_src ) ) { ?>
															<div class="entry-featured-image">
																<div class="image" style="background-image: url(<?php echo $image_src; ?>);">
																	<?php echo wp_get_attachment_image( get_post_thumbnail_id(), 'medium_large' ) ?>
																</div>
															</div>
														<?php } ?>

														<div class="entry-teaser">
		
															<header class="entry-header">

																<?php if ( $client_story_site_title ) { ?>
																	<p class="entry-centers">
																		<span class="center"><?php echo $client_story_site_title; ?></span>
																	</p>
																<?php } else { ?>
																	<?php $centers = get_the_terms( get_the_ID(), 'post_center' ); ?>
																	<?php if ( ! empty( $centers ) ) { ?>
																		<p class="entry-centers">
																			<?php foreach ( $centers as $term ) { ?>
																				<span class="center"><?php echo $term->name; ?></span>
																			<?php } ?>
																		</p>
																	<?php } else if ( ! is_main_site() ) { ?>
																		<p class="entry-centers">
																			<span class="center"><?php echo get_bloginfo( 'name' ); ?></span>
																		</p>
																	<?php } ?>
																<?php } ?>
	
																<h3 class="entry-title"><?php the_title(); ?></h3>

																<?php if ( property_exists( $filters, 'region' ) ) { ?>
																	<?php $industries = get_the_terms( get_the_ID(), 'client_story_industry' ); ?>
																	<?php if ( ! empty( $industries ) ) { ?>
																		<p class="entry-industries">
																			<?php foreach ( $industries as $term ) { ?>
																				<span class="industry"><?php echo $term->name; ?></span>
																			<?php } ?>
																		</p>
																	<?php } ?>
																<?php } ?>

																<?php if ( ! empty( $post->post_excerpt ) ) { ?>
																	<div class="entry-excerpt"><?php echo apply_filters( 'the_content', $post->post_excerpt ); ?></div>
																<?php } ?>
	
															</header>

															<div class="entry-link"><span></span><span></span></div>
		
														</div>

													</div>
												</a>
											</article>

											<?php if ( $switched_site ) restore_current_blog(); ?>

											<?php if ( $query->current_post + 1 == $pull_quote_position ) { ?>
												<?php echo $pull_quote; ?>
											<?php } ?>

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


	}
	Crown_Block_Client_Story_Index::init();
}