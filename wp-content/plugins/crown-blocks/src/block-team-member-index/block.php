<?php

if(!class_exists('Crown_Block_Team_Member_Index')) {
	class Crown_Block_Team_Member_Index extends Crown_Block {


		public static $name = 'team-member-index';


		public static function init() {
			parent::init();

			add_action( 'wp_ajax_get_block_team_member_index_member_details', array( __CLASS__, 'get_ajax_member_details' ) );
			add_action( 'wp_ajax_nopriv_get_block_team_member_index_member_details', array( __CLASS__, 'get_ajax_member_details' ) );

		}


		public static function get_attributes() {
			return array(
				'className' => array( 'type' => 'string', 'default' => '' ),
				'postsPerPage' => array( 'type' => 'string', 'default' => '10' )
			);
		}


		public static function render( $atts, $content ) {
			global $post;

			$filters = (object) array(
				'category' => (object) array( 'key' => 'tm_category', 'queried' => null, 'options' => array() ),
				'expertise' => (object) array( 'key' => 'tm_expertise', 'queried' => null, 'options' => array() ),
				'center' => (object) array( 'key' => 'tm_center', 'queried' => null, 'options' => array() ),
				'letter' => (object) array( 'key' => 'tm_letter', 'queried' => null, 'options' => array() ),
				'lang' => (object) array( 'key' => 'tm_lang', 'queried' => null, 'options' => array() )
			);

			// $atts['postsPerPage'] = 1;
			$query_args = array(
				'post_type' => 'team_member',
				'posts_per_page' => $atts['postsPerPage'],
				'orderby' => 'meta_value',
				'order' => 'ASC',
				'meta_key' => 'team_member_name_last_comma_first_lc',
				'paged' => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
				'tax_query' => array(),
				'meta_query' => array()
			);

			$filters->category->queried = isset( $_GET[ $filters->category->key ] ) ? ( is_array( $_GET[ $filters->category->key ] ) ? $_GET[ $filters->category->key ] : array_filter( array_map( 'trim', explode( ',', $_GET[ $filters->category->key ] ) ), function( $n ) { return ! empty( $n ); } ) ) : array();
			if ( ! empty( $filters->category->queried ) ) $query_args['tax_query'][] = array( 'taxonomy' => 'team_member_category', 'terms' => $filters->category->queried );

			$filters->expertise->queried = isset( $_GET[ $filters->expertise->key ] ) ? ( is_array( $_GET[ $filters->expertise->key ] ) ? $_GET[ $filters->expertise->key ] : array_filter( array_map( 'trim', explode( ',', $_GET[ $filters->expertise->key ] ) ), function( $n ) { return ! empty( $n ); } ) ) : array();
			if ( ! empty( $filters->expertise->queried ) ) $query_args['tax_query'][] = array( 'taxonomy' => 'team_member_expertise', 'terms' => $filters->expertise->queried );

			$filters->center->queried = isset( $_GET[ $filters->center->key ] ) ? ( is_array( $_GET[ $filters->center->key ] ) ? $_GET[ $filters->center->key ] : array_filter( array_map( 'trim', explode( ',', $_GET[ $filters->center->key ] ) ), function( $n ) { return ! empty( $n ); } ) ) : array();
			if ( ! empty( $filters->center->queried ) ) $query_args['tax_query'][] = array( 'taxonomy' => 'post_center', 'terms' => $filters->center->queried );

			$filters->letter->queried = isset( $_GET[ $filters->letter->key ] ) ? strtolower( trim( $_GET[ $filters->letter->key ] ) ) : '';
			if ( ! empty( $filters->letter->queried ) ) $query_args['meta_query'][] = array( 'key' => 'team_member_name_last_initial_lc', 'value' => $filters->letter->queried );

			$filters->lang->queried = isset( $_GET[ $filters->lang->key ] ) ? ( is_array( $_GET[ $filters->lang->key ] ) ? $_GET[ $filters->lang->key ] : array_filter( array_map( 'trim', explode( ',', $_GET[ $filters->lang->key ] ) ), function( $n ) { return ! empty( $n ); } ) ) : array();
			if ( ! empty( $filters->lang->queried ) ) $query_args['meta_query'][] = array( 'key' => '__team_member_options', 'compare' => 'IN', 'value' => $filters->lang->queried );

			// $queried_search = isset( $_GET['keywords'] ) ? trim( $_GET['keywords'] ) : '';
			// if ( ! empty( $queried_search ) ) $query_args['s'] = $queried_search;

			$query = new WP_Query( $query_args );

			$filters_action = remove_query_arg( array(
				$filters->category->key,
				$filters->expertise->key,
				$filters->center->key,
				$filters->letter->key,
				$filters->lang->key
			) );
			$filters_action = preg_replace( '/\/page\/\d+\/(\?.*)?$/', "/$1", $filters_action );

			$filters->category->options = array_map( function( $n ) use ( $filters ) {
				return (object) array( 'value' => $n->term_id, 'label' => $n->name, 'selected' => in_array( $n->term_id, $filters->category->queried ) );
			}, get_terms( array( 'taxonomy' => 'team_member_category' ) ) );

			$filters->expertise->options = array_map( function( $n ) use ( $filters ) {
				return (object) array( 'value' => $n->term_id, 'label' => $n->name, 'selected' => in_array( $n->term_id, $filters->expertise->queried ) );
			}, get_terms( array( 'taxonomy' => 'team_member_expertise' ) ) );

			$filters->center->options = array_map( function( $n ) use ( $filters ) {
				return (object) array( 'value' => $n->term_id, 'label' => $n->name, 'selected' => in_array( $n->term_id, $filters->center->queried ) );
			}, get_terms( array( 'taxonomy' => 'post_center' ) ) );

			$filters->letter->options = array_map( function( $n ) use ( $filters ) {
				return (object) array( 'value' => strtolower( $n ), 'label' => $n, 'selected' => strtolower( $n ) == $filters->letter->queried );
			}, array( 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z' ) );

			$filters->lang->options = array(
				(object) array( 'value' => 'multilingual', 'label' => 'Multilingual', 'selected' => in_array( 'multilingual', $filters->lang->queried ) )
			);

			$block_class = array( 'wp-block-crown-blocks-team-member-index', 'post-feed-block', $atts['className'] );
			$block_id = 'post-feed-block-' . md5( json_encode( array( 'team-member-index', $atts ) ) );

			ob_start();
			// print_r($query_args);
			?>

				<div id="<?php echo $block_id; ?>" class="<?php echo implode( ' ', $block_class ); ?>">
					<div class="inner">

						<form action="<?php echo $filters_action; ?>" method="get" class="feed-filters">

							<header class="filters-header">

								<button type="button" class="filters-toggle"><?php _e( 'Filter', 'crown_blocks' ); ?></button>

								<button type="button" class="filters-close"><?php _e( 'Close', 'crown_blocks' ); ?></button>

								<?php if ( ! empty( $filters->category->options ) ) { ?>
									<ul class="options singular quick-filters">
										<?php foreach ( $filters->category->options as $option ) { ?>
											<li class="option">
												<label>
													<input type="checkbox" name="<?php echo $filters->category->key; ?>[]" value="<?php echo esc_attr( $option->value ); ?>" <?php echo $option->selected ? 'checked' : ''; ?>>
													<span class="label"><?php echo $option->label; ?></span>
												</label>
											</li>
										<?php } ?>
									</ul>
								<?php } ?>

								<nav class="filters-nav">
									<ul>
										<?php if ( ! empty( $filters->letter->options ) ) { ?><li><button type="button" data-tab="letter"><?php _e( 'Name', 'crown_blocks' ); ?></button></li><?php } ?>
										<?php if ( ! empty( $filters->expertise->options ) ) { ?><li><button type="button" data-tab="expertise"><?php _e( 'Expertise', 'crown_blocks' ); ?></button></li><?php } ?>
										<?php if ( ! empty( $filters->center->options ) ) { ?><li><button type="button" data-tab="center"><?php _e( 'Center', 'crown_blocks' ); ?></button></li><?php } ?>
										<?php if ( ! empty( $filters->lang->options ) ) { ?><li><button type="button" data-tab="lang"><?php _e( 'Multilingual', 'crown_blocks' ); ?></button></li><?php } ?>
									</ul>
								</nav>

							</header>

							<div class="filters-tabs">
								<div class="inner">

									<?php if ( ! empty( $filters->letter->options ) ) { ?>
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
	
									<?php if ( ! empty( $filters->expertise->options ) ) { ?>
										<div class="filters-tab" data-tab="expertise">
											<ul class="options expertise">
												<?php foreach ( $filters->expertise->options as $option ) { ?>
													<li class="option">
														<label>
															<input type="checkbox" name="<?php echo $filters->expertise->key; ?>[]" value="<?php echo esc_attr( $option->value ); ?>" <?php echo $option->selected ? 'checked' : ''; ?>>
															<span class="label"><?php echo $option->label; ?></span>
														</label>
													</li>
												<?php } ?>
											</ul>
										</div>
									<?php } ?>
	
									<?php if ( ! empty( $filters->center->options ) ) { ?>
										<div class="filters-tab" data-tab="center">
											<ul class="options center">
												<?php foreach ( $filters->center->options as $option ) { ?>
													<li class="option">
														<label>
															<input type="checkbox" name="<?php echo $filters->center->key; ?>[]" value="<?php echo esc_attr( $option->value ); ?>" <?php echo $option->selected ? 'checked' : ''; ?>>
															<span class="label"><?php echo $option->label; ?></span>
														</label>
													</li>
												<?php } ?>
											</ul>
										</div>
									<?php } ?>
	
									<?php if ( ! empty( $filters->lang->options ) ) { ?>
										<div class="filters-tab" data-tab="lang">
											<ul class="options lang">
												<?php foreach ( $filters->lang->options as $option ) { ?>
													<li class="option">
														<label>
															<input type="checkbox" name="<?php echo $filters->lang->key; ?>[]" value="<?php echo esc_attr( $option->value ); ?>" <?php echo $option->selected ? 'checked' : ''; ?>>
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
													<h4>No Team Members Found</h4>
													<p>Please try adjusting your selected filters above.</p>
												</div>
											</div>
										<?php } ?>
										
										<?php while ( $query->have_posts() ) { ?>
											<?php $query->the_post(); ?>
											<article <?php post_class(); ?>>
												<a href="<?php the_permalink(); ?>" data-post-id="<?php echo get_the_ID(); ?>">
													<div class="inner">
	
														<div class="entry-headshot-photo">
															<div class="inner">
																<?php $image_src = wp_get_attachment_image_url( get_post_meta( get_the_ID(), 'team_member_headshot_photo', true ), 'medium_large' ); ?>
																<?php if ( ! empty( $image_src ) ) { ?>
																	<div class="image" style="background-image: url(<?php echo $image_src; ?>);">
																		<?php echo wp_get_attachment_image( get_post_meta( get_the_ID(), 'team_member_headshot_photo', true ), 'medium_large' ) ?>
																	</div>
																	<div class="image overlay" style="background-image: url(<?php echo $image_src; ?>);"></div>
																<?php } ?>
															</div>
														</div>
	
														<div class="entry-teaser">
	
															<div class="push">

																<header class="entry-header">
		
																	<h3 class="entry-title"><?php the_title(); ?></h3>
		
																	<?php $job_title = get_post_meta( get_the_ID(), 'team_member_job_title', true ); ?>
																	<?php if ( ! empty( $job_title ) ) { ?>
																		<p class="entry-job-title"><?php echo $job_title; ?></p>
																	<?php } ?>
		
																</header>
		
																<?php $centers = get_the_terms( get_the_ID(), 'post_center' ); ?>
																<?php if ( ! empty( $centers ) ) { ?>
																	<p class="entry-centers">
																		<?php foreach ( $centers as $term ) { ?>
																			<span class="center"><?php echo $term->name; ?></span>
																		<?php } ?>
																	</p>
																<?php } ?>

															</div>
	
															<p class="entry-link">
																<span><?php _e( 'View My Bio', 'crown_blocks' ); ?></span>
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


		public static function get_ajax_member_details() {
			global $post;

			$response = (object) array(
				'id' => '',
				'content' => ''
			);

			$id = isset( $_GET['id'] ) ? $_GET['id'] : '';
			$response->id = $id;

			if ( empty( $id ) || ! ( $post = get_post( $id ) ) || $post->post_type != 'team_member' ) wp_send_json( $response );

			setup_postdata( $post );
			ob_start();
			?>
				<article <?php post_class(); ?>>
					<div class="inner text-color-light">

						<div class="entry-photo-overview">
							
							<?php $image_src = wp_get_attachment_image_url( get_post_meta( get_the_ID(), 'team_member_headshot_photo', true ), 'medium_large' ); ?>
							<?php if ( ! empty( $image_src ) ) { ?>
								<div class="entry-headshot-photo">
									<div class="inner">
										<div class="image" style="background-image: url(<?php echo $image_src; ?>);">
											<?php echo wp_get_attachment_image( get_post_meta( get_the_ID(), 'team_member_headshot_photo', true ), 'medium_large' ) ?>
										</div>
									</div>
								</div>
							<?php } ?>

							<div class="entry-overview">

								<header class="entry-header">

									<h3 class="entry-title"><?php the_title(); ?></h3>

									<?php $job_title = get_post_meta( get_the_ID(), 'team_member_job_title', true ); ?>
									<?php if ( ! empty( $job_title ) ) { ?>
										<p class="entry-job-title"><?php echo $job_title; ?></p>
									<?php } ?>

								</header>

								<?php $links = array(); ?>
								<?php if ( ( $link = get_post_meta( get_the_ID(), 'team_member_linkedin', true ) ) ) $links[] = (object) array( 'key' => 'linkedin', 'url' => $link ); ?>
								<?php if ( ! empty( $links) ) { ?>
									<ul class="entry-contact-links">
										<?php foreach ( $links as $link ) { ?>
											<?php $label = __( 'Let\'s Connect', 'crown_blocks' ); ?>
											<li class="<?php echo $link->key; ?>"><a href="<?php echo esc_attr( $link->url ); ?>" target="_blank"><?php echo $label; ?></a></li>
										<?php } ?>
									</ul>
								<?php } ?>

							</div>

						</div>

						<div class="entry-contents">
							
							<?php $bio = get_post_meta( get_the_ID(), 'team_member_bio', true ); ?>
							<?php if ( ! empty( $bio ) ) { ?>
								<div class="push">
									<div class="entry-bio">
										<h4 class="bio-heading"><?php _e( 'About Me:', 'crown_blocks' ); ?></h4>
										<?php echo apply_filters( 'the_content', $bio ); ?>
									</div>
								</div>
							<?php } ?>

							<?php $expertise = get_the_terms( get_the_ID(), 'team_member_expertise' ); ?>
							<?php if ( ! empty( $expertise ) ) { ?>
								<p class="entry-expertise">
									<span class="label"><?php _e( 'Expertise:', 'crown_blocks' ); ?></span>
									<?php foreach ( $expertise as $term ) { ?>
										<span class="expertise"><?php echo $term->name; ?></span>
									<?php } ?>
								</p>
							<?php } ?>

						</div>

					</div>
				</div>
			<?php
			$response->content = ob_get_clean();

			wp_send_json( $response );
		}


	}
	Crown_Block_Team_Member_Index::init();
}