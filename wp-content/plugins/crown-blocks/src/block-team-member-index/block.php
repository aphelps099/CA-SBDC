<?php

if(!class_exists('Crown_Block_Team_Member_Index')) {
	class Crown_Block_Team_Member_Index extends Crown_Block {


		public static $name = 'team-member-index';


		public static function init() {
			parent::init();
		}


		public static function get_attributes() {
			return array(
				'className' => array( 'type' => 'string', 'default' => '' ),
				'postsPerPage' => array( 'type' => 'number', 'default' => 60 ),
				'scrollAnchor' => array( 'type' => 'string', 'default' => '' )
			);
		}


		public static function render( $atts, $content ) {
			global $post;

			$queryArgs = array(
				'post_type' => 'team_member',
				'posts_per_page' => $atts['postsPerPage'],
				'orderby' => 'meta_value',
				'order' => 'ASC',
				'meta_key' => 'team_member_name_last_comma_first',
				'paged' => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
				'tax_query' => array()
			);

			$queried_category = isset( $_GET['category'] ) ? intval( $_GET['category'] ) : 0;
			if ( ! empty( $queried_category ) ) $queryArgs['tax_query'][] = array( 'taxonomy' => 'team_member_category', 'terms' => $queried_category );

			$queried_service = isset( $_GET['service'] ) ? intval( $_GET['service'] ) : 0;
			if ( ! empty( $queried_service ) ) $queryArgs['tax_query'][] = array( 'taxonomy' => 'team_member_service', 'terms' => $queried_service );

			$queried_location = isset( $_GET['location'] ) ? intval( $_GET['location'] ) : 0;
			if ( ! empty( $queried_location ) ) $queryArgs['tax_query'][] = array( 'taxonomy' => 'team_member_location', 'terms' => $queried_location );

			$queried_search = isset( $_GET['keywords'] ) ? trim( $_GET['keywords'] ) : '';
			if ( ! empty( $queried_search ) ) $queryArgs['s'] = $queried_search;

			$statistics = get_repeater_entries( 'blog', 'team_member_statistics' );
			if ( ! empty( $statistics ) ) $queryArgs['posts_per_page'] -= 1;
			
			$query = new WP_Query( $queryArgs );

			$filters_action = add_query_arg( array() );
			$filters_action = preg_replace( '/\/page\/\d+\/(\?.*)?$/', "/$1", $filters_action );
			if ( ! empty( $atts['scrollAnchor'] ) ) $filters_action .= '#' . $atts['scrollAnchor'];

			// $category_options = array();
			// $category_options[] = (object) array( 'value' => 0, 'label' => 'All', 'selected' => empty( $queried_category ) );
			// foreach ( get_terms( array( 'taxonomy' => 'team_member_category' ) ) as $term ) {
			// 	$category_options[] = (object) array( 'value' => $term->term_id, 'label' => $term->name, 'selected' => $term->term_id == $queried_category );
			// }

			$dropdown_args = array(
				'echo' => false,
				'hierarchical' => true,
				'orderby' => 'name',
				'hide_if_empty' => true
			);
			$categories_dropdown = wp_dropdown_categories( array_merge( $dropdown_args, array(
				'taxonomy' => 'team_member_category',
				'name' => 'category',
				'selected' => $queried_category,
				'show_option_all' => 'All Team Members'
			) ) );
			$services_dropdown = wp_dropdown_categories( array_merge( $dropdown_args, array(
				'taxonomy' => 'team_member_service',
				'name' => 'service',
				'selected' => $queried_service,
				'show_option_all' => 'Services Provided'
			) ) );
			$locations_dropdown = wp_dropdown_categories( array_merge( $dropdown_args, array(
				'taxonomy' => 'team_member_location',
				'name' => 'location',
				'selected' => $queried_location,
				'show_option_all' => 'Location'
			) ) );

			$block_class = array( 'wp-block-crown-blocks-team-member-index', $atts['className'] );

			$stat_slots = array( 8, 6, 9, 7, 0 );
			if ( get_query_var( 'paged' ) ) {
				$slot_shuffle = get_query_var( 'paged' ) - 1;
				$stat_slots = array_merge( array_slice( $stat_slots, $slot_shuffle % count( $stat_slots ) ), array_slice( $stat_slots, 0, $slot_shuffle % count( $stat_slots ) ) );
				$stat_shuffle = (get_query_var( 'paged' ) - 1) * intval( $atts['postsPerPage'] ) / 10 - ( get_query_var( 'paged' ) - 1);
				$statistics = array_merge( array_slice( $statistics, $stat_shuffle % count( $statistics ) ), array_slice( $statistics, 0, $stat_shuffle % count( $statistics ) ) );
			}

			ob_start();
			// print_r($atts);
			?>

				<div class="<?php echo implode( ' ', $block_class ); ?>">
					<div class="inner">

						<?php //if ( count( $category_options ) > 1 ) { ?>
						<?php if ( ! empty( $categories_dropdown ) || ! empty( $services_dropdown ) || ! empty( $locations_dropdown ) ) { ?>
							<form action="<?php echo $filters_action; ?>" method="get" class="filters">
								<h4>Filter By</h4>
								<?php /*if ( count( $category_options ) > 1 ) { ?>
									<div class="field categories">
										<ul class="options">
											<?php foreach ( $category_options as $option ) { ?>
												<li>
													<label>
														<input type="radio" name="category" value="<?php echo $option->value; ?>" <?php echo $option->selected ? 'checked' : ''; ?>>
														<span class="label"><?php echo $option->label; ?></span>
													</label>
												</li>
											<?php } ?>
										</ul>
									</div>
								<?php }*/ ?>
								<?php if ( ! empty( $services_dropdown ) ) { ?>
									<div class="field services"><?php echo $services_dropdown; ?></div>
								<?php } ?>
								<?php if ( ! empty( $locations_dropdown ) ) { ?>
									<div class="field locations"><?php echo $locations_dropdown; ?></div>
								<?php } ?>
								<?php if ( ! empty( $categories_dropdown ) ) { ?>
									<div class="field categories"><?php echo $categories_dropdown; ?></div>
								<?php } ?>
								<div class="field search">
									<input type="text" placeholder="Search" name="keywords" value="<?php echo esc_attr( $queried_search ); ?>">
								</div>
								<footer class="form-actions">
									<button type="submit" class="btn btn-primary">Submit</button>
								</footer>
							</form>
						<?php } ?>

						<div class="post-feed item-count-<?php echo $query->post_count; ?>" data-item-count="<?php echo $query->post_count; ?>">
							<div class="inner">

								<?php if ( ! $query->have_posts() ) { ?>
									<div class="alert-wrapper">
										<div class="alert alert-info no-results">
											<h4 class="alert-heading">No Team Members Found</h4>
											<p class="mb-0">Please try adjusting your selected filters above.</p>
										</div>
									</div>
								<?php } ?>
								
								<?php $slot_index = 0; ?>
								<?php //for($i = 0; $i < 30; $i++) { ?>
								<?php while ( $query->have_posts() ) { ?>

									<?php $query->the_post(); ?>
									<article <?php post_class(); ?>>
										<a href="#team-member-modal-<?php echo get_the_ID(); ?>">

											<div class="entry-photo">
												<?php $image_src = wp_get_attachment_image_url( get_post_meta( get_the_ID(), 'team_member_headshot_photo', true ), 'medium_large' ); ?>
												<?php $image_secondary_src = wp_get_attachment_image_url( get_post_meta( get_the_ID(), 'team_member_headshot_photo_secondary', true ), 'medium_large' ); ?>
												<?php if ( ! empty( $image_src ) ) { ?>
													<div class="image" style="background-image: url(<?php echo $image_src; ?>);">
														<img src="<?php echo $image_src; ?>">
													</div>
													<?php if ( ! empty( $image_secondary_src ) ) { ?>
														<div class="image secondary" style="background-image: url(<?php echo $image_secondary_src; ?>);">
															<img src="<?php echo $image_secondary_src; ?>">
														</div>
													<?php } ?>
												<?php } ?>
											</div>

											<div class="entry-contents">
												<h4 class="entry-title"><?php the_title(); ?></h4>
											</div>

										</a>
									</article>

									<?php $slot_index++; ?>

									<?php $stat_slot = $stat_slots[ floor( $slot_index / 10 ) % count( $stat_slots ) ]; ?>
									<?php if ( ! empty( $statistics ) && ( $slot_index + 1 ) % 10 == $stat_slot ) { ?>
										<?php $statistic = array_shift( $statistics ); ?>
										<?php $statistics[] = $statistic; ?>
										<aside class="stat">
											<div class="inner odometer-statistic">
												<?php if ( ! empty( $statistic['icon'] ) ) { ?>
													<?php $icon_src = wp_get_attachment_image_url( $statistic['icon'], 'medium' ); ?>
													<div class="stat-icon" <?php echo $icon_src ? 'style="background-image: url(' . $icon_src . ');"' : ''; ?>></div>
												<?php } ?>
												<?php if ( ! empty( $statistic['value'] ) ) { ?><p class="stat-value"><?php echo $statistic['value']; ?></p><?php } ?>
												<?php if ( ! empty( $statistic['label'] ) ) { ?><p class="stat-label"><?php echo $statistic['label']; ?></p><?php } ?>
											</div>
										</aside>
										<?php $slot_index++; ?>
									<?php } ?>

								<?php } ?>
								<?php //} ?>
								<?php wp_reset_postdata(); ?>

							</div>
						</div>

						<?php self::render_pagination( $query, 5, $atts['scrollAnchor'] ); ?>

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
	Crown_Block_Team_Member_Index::init();
}