<?php

if(!class_exists('Crown_Block_Case_Study_Index')) {
	class Crown_Block_Case_Study_Index extends Crown_Block {


		public static $name = 'case-study-index';


		public static function init() {
			parent::init();
		}


		public static function get_attributes() {
			return array(
				'className' => array( 'type' => 'string', 'default' => '' ),
				'postsPerPage' => array( 'type' => 'string', 'default' => '8' ),
				'scrollAnchor' => array( 'type' => 'string', 'default' => '' )
			);
		}


		public static function render( $atts, $content ) {
			global $post;
			
			$queryArgs = array(
				'post_type' => 'case_study',
				'posts_per_page' => $atts['postsPerPage'],
				'paged' => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
				'tax_query' => array()
			);

			$queried_service = isset( $_GET['service'] ) ? intval( $_GET['service'] ) : 0;
			if ( ! empty( $queried_service ) ) $queryArgs['tax_query'][] = array( 'taxonomy' => 'case_study_service', 'terms' => $queried_service );
			
			$queried_client_type = isset( $_GET['client_type'] ) ? intval( $_GET['client_type'] ) : 0;
			if ( ! empty( $queried_client_type ) ) $queryArgs['tax_query'][] = array( 'taxonomy' => 'case_study_client_type', 'terms' => $queried_client_type );

			$queried_location = isset( $_GET['location'] ) ? intval( $_GET['location'] ) : 0;
			if ( ! empty( $queried_location ) ) $queryArgs['tax_query'][] = array( 'taxonomy' => 'case_study_location', 'terms' => $queried_location );

			$query = new WP_Query( $queryArgs );
			if ( ! $query->have_posts() ) return '';

			$filters_action = add_query_arg( array() );
			$filters_action = preg_replace( '/\/page\/\d+\/(\?.*)?$/', "/$1", $filters_action );
			if ( ! empty( $atts['scrollAnchor'] ) ) $filters_action .= '#' . $atts['scrollAnchor'];

			$dropdown_args = array(
				'echo' => false,
				'hierarchical' => true,
				'orderby' => 'name',
				'hide_if_empty' => true
			);
			$services_dropdown = wp_dropdown_categories( array_merge( $dropdown_args, array(
				'taxonomy' => 'case_study_service',
				'name' => 'service',
				'selected' => $queried_service,
				'show_option_all' => 'Services Provided'
			) ) );
			$client_types_dropdown = wp_dropdown_categories( array_merge( $dropdown_args, array(
				'taxonomy' => 'case_study_client_type',
				'name' => 'client_type',
				'selected' => $queried_client_type,
				'show_option_all' => 'Client Type'
			) ) );
			$locations_dropdown = wp_dropdown_categories( array_merge( $dropdown_args, array(
				'taxonomy' => 'case_study_location',
				'name' => 'location',
				'selected' => $queried_location,
				'show_option_all' => 'Location'
			) ) );

			$block_class = array( 'wp-block-crown-blocks-case-study-index', $atts['className'] );

			ob_start();
			// print_r($atts);
			?>

				<div class="<?php echo implode( ' ', $block_class ); ?>">
					<div class="inner">

						<?php if ( ! empty( $services_dropdown ) || ! empty( $client_types_dropdown ) || ! empty( $locations_dropdown ) ) { ?>
							<form action="<?php echo $filters_action; ?>" method="get" class="filters">
								<h4>Filter By</h4>
								<?php if ( ! empty( $services_dropdown ) ) { ?>
									<div class="field services"><?php echo $services_dropdown; ?></div>
								<?php } ?>
								<?php if ( ! empty( $client_types_dropdown ) ) { ?>
									<div class="field client-types"><?php echo $client_types_dropdown; ?></div>
								<?php } ?>
								<?php if ( ! empty( $locations_dropdown ) ) { ?>
									<div class="field locations"><?php echo $locations_dropdown; ?></div>
								<?php } ?>
								<footer class="form-actions">
									<button type="submit" class="btn btn-primary">Submit</button>
								</footer>
							</form>
						<?php } ?>

						<div class="post-feed item-count-<?php echo $query->post_count; ?>" data-item-count="<?php echo $query->post_count; ?>">
							<div class="inner">

								<?php while ( $query->have_posts() ) { ?>
									<?php $query->the_post(); ?>
									<?php if ( class_exists( 'Crown_Case_Studies' ) ) Crown_Case_Studies::the_case_study(); ?>
								<?php } ?>
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
	Crown_Block_Case_Study_Index::init();
}