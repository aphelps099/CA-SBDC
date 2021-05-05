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
				'groupByCenter' => array( 'type' => 'boolean', 'default' => false ),
				'postsPerPage' => array( 'type' => 'string', 'default' => '10' ),
				'filterCenters' => array( 'type' => 'array', 'default' => array(), 'items' => array( 'type' => 'object' ) )
			);
		}


		public static function render( $atts, $content ) {
			global $post;

			$group_by_center = $atts['groupByCenter'];

			$filters = (object) array(
				'category' => (object) array( 'key' => 'tm_category', 'queried' => null, 'options' => array() ),
				'expertise' => (object) array( 'key' => 'tm_expertise', 'queried' => null, 'options' => array() ),
				'center' => (object) array( 'key' => 'tm_center', 'queried' => null, 'options' => array() ),
				'letter' => (object) array( 'key' => 'tm_letter', 'queried' => null, 'options' => array() ),
				'lang' => (object) array( 'key' => 'tm_lang', 'queried' => null, 'options' => array() )
			);

			$prefiltered = false;

			// $atts['postsPerPage'] = 1;
			$query_args = array(
				'post_type' => array( 'team_member', 'team_member_s' ),
				'posts_per_page' => $atts['postsPerPage'],
				'orderby' => 'meta_value',
				'order' => 'ASC',
				'meta_key' => 'team_member_name_last_comma_first_lc',
				'paged' => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
				'tax_query' => array(),
				'meta_query' => array()
			);

			if ( ! $group_by_center && ! empty( $atts['filterCenters'] ) ) {

				$prefiltered = true;

				$query_args['tax_query'][] = array( 'taxonomy' => 'post_center', 'terms' => array_map( function( $n ) { return $n['id']; }, $atts['filterCenters'] ) );

			} else {

				$filters->category->queried = isset( $_GET[ $filters->category->key ] ) ? ( is_array( $_GET[ $filters->category->key ] ) ? $_GET[ $filters->category->key ] : array_filter( array_map( 'trim', explode( ',', $_GET[ $filters->category->key ] ) ), function( $n ) { return ! empty( $n ); } ) ) : array();
				if ( ! empty( $filters->category->queried ) && ! in_array( -1, $filters->category->queried ) ) $query_args['tax_query'][] = array( 'taxonomy' => 'team_member_category', 'terms' => $filters->category->queried );

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

			}

			if ( $group_by_center ) {
				$query_args['posts_per_page'] = -1;
				$query_args['fields'] = 'ids';
			}

			if ( ! in_array( -1, $filters->category->queried ) && ! is_main_site() ) {
				$query_args['post_type'] = array( 'team_member' );
				$group_by_center = false;
			}

			$query = null;
			if ( function_exists( 'relevanssi_do_query' ) && isset( $query_args['s'] ) && ! empty( $query_args['s'] ) ) {
				$query = new WP_Query();
				$query->parse_query( $query_args );
				relevanssi_do_query( $query );
			} else {
				$query = new WP_Query( $query_args );
			}

			$filters_action = remove_query_arg( array(
				$filters->category->key,
				$filters->expertise->key,
				$filters->center->key,
				$filters->letter->key,
				$filters->lang->key
			) );
			$filters_action = preg_replace( '/\/page\/\d+\/(\?.*)?$/', "/$1", $filters_action );

			if ( ! $prefiltered ) {

				$filters->category->options = array_map( function( $n ) use ( $filters ) {
					return (object) array( 'value' => $n->term_id, 'label' => $n->name, 'selected' => in_array( $n->term_id, $filters->category->queried ) );
				}, get_terms( array( 'taxonomy' => 'team_member_category' ) ) );
				if ( ! empty( $filters->category->options ) && ! is_main_site() ) {
					$filters->category->options[] = (object) array( 'value' => -1, 'label' => 'All', 'selected' => in_array( -1, $filters->category->queried ) );
				}

				$filters->expertise->options = array_map( function( $n ) use ( $filters ) {
					return (object) array( 'value' => $n->term_id, 'label' => $n->name, 'selected' => in_array( $n->term_id, $filters->expertise->queried ) );
				}, get_terms( array( 'taxonomy' => 'team_member_expertise' ) ) );

				$filters->center->options = array_map( function( $n ) use ( $filters ) {
					return (object) array( 'value' => $n->term_id, 'label' => $n->name, 'selected' => in_array( $n->term_id, $filters->center->queried ) );
				}, get_terms( array( 'taxonomy' => 'post_center' ) ) );
				$center_filters = get_option( 'team_member_options_center_filters', array() );
				if ( ! empty( $center_filters ) ) {
					$filters->center->options = array_filter( $filters->center->options, function( $n ) use ( $center_filters ) { return in_array( $n->value, $center_filters ); } );
				}

				$filters->letter->options = array_map( function( $n ) use ( $filters ) {
					return (object) array( 'value' => strtolower( $n ), 'label' => $n, 'selected' => strtolower( $n ) == $filters->letter->queried );
				}, array( 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z' ) );

				$filters->lang->options = array(
					(object) array( 'value' => 'multilingual', 'label' => 'Multilingual', 'selected' => in_array( 'multilingual', $filters->lang->queried ) )
				);

			}

			$block_class = array( 'wp-block-crown-blocks-team-member-index', 'post-feed-block', $atts['className'] );
			$block_id = 'post-feed-block-' . md5( json_encode( array( 'team-member-index', $atts ) ) );

			ob_start();
			// print_r($centers);
			?>

				<div id="<?php echo $block_id; ?>" class="<?php echo implode( ' ', $block_class ); ?>">
					<div class="inner">

						<?php if ( ! $prefiltered ) { ?>

							<form action="<?php echo $filters_action; ?>" method="get" class="feed-filters">

								<header class="filters-header">

									<button type="button" class="filters-toggle"><span><?php _e( 'Filter', 'crown_blocks' ); ?></span></button>
									<button type="button" class="filters-clear"><span><?php _e( 'Clear', 'crown_blocks' ); ?></span></button>
									<button type="button" class="filters-close"><span><?php _e( 'Close', 'crown_blocks' ); ?></span></button>

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

						<?php } ?>

						<div class="ajax-loader infinite">
							<div class="ajax-content">

								<div class="post-feed item-count-<?php echo $query->post_count; ?>" data-item-count="<?php echo $query->post_count; ?>">
									<div class="inner infinite-loader-container">
	
										<?php if ( ! $query->have_posts() ) { ?>

											<div class="alert-wrapper">
												<div class="alert alert-info no-results">
													<h4>No Team Members Found</h4>
													<p>Please try adjusting your selected filters above.</p>
												</div>
											</div>

										<?php } else { ?>
										
											<?php if ( $group_by_center ) { ?>
												
												<?php $output_team_member_ids = array(); ?>

												<?php $centers = get_terms( array( 'taxonomy' => 'post_center' ) ); ?>
												<?php
													if ( ! is_main_site() ) {

														$src_site = get_current_blog_id();
														switch_to_blog( get_main_site_id() );
														$center_terms = get_terms( array(
															'taxonomy' => 'post_center',
															'hide_empty' => false,
															'meta_query' => array(
																array( 'key' => 'center_site_id', 'value' => $src_site )
															)
														) );
														restore_current_blog();

														if ( ! empty( $center_terms ) ) {
															$current_center_term = null;
															foreach ( $centers as $i => $center ) {
																if ( $center->name == $center_terms[0]->name ) {
																	$current_center_term = $center;
																	unset($centers[$i]);
																	break;
																}
															}
															if ( $current_center_term ) array_unshift( $centers, $current_center_term );
														}

													}
												?>
												<?php foreach ( $centers as $center ) { ?>

													<?php
														$sub_query_args = array(
															'post_type' => array( 'team_member', 'team_member_s' ),
															'posts_per_page' => -1,
															'orderby' => 'meta_value',
															'order' => 'ASC',
															'meta_key' => 'team_member_name_last_comma_first_lc',
															'tax_query' => array( array( 'taxonomy' => 'post_center', 'terms' => $center->term_id ) ),
															'post__in' => $query->get_posts()
														);
														$sub_query = null;
														if ( function_exists( 'relevanssi_do_query' ) && isset( $sub_query_args['s'] ) && ! empty( $sub_query_args['s'] ) ) {
															$sub_query = new WP_Query();
															$sub_query->parse_query( $sub_query_args );
															relevanssi_do_query( $query );
														} else {
															$sub_query = new WP_Query( $sub_query_args );
														}
													?>

													<?php if ( $sub_query->have_posts() ) { ?>

														<h2 class="team-group-title"><span><?php echo $center->name; ?></span></h2>

														<?php while ( $sub_query->have_posts() ) { ?>
															<?php $output_team_member_ids[] = get_the_ID(); ?>
															<?php $sub_query->the_post(); ?>
															<?php self::render_team_member( false ); ?>
														<?php } ?>
														<?php wp_reset_postdata(); ?>

													<?php } ?>

												<?php } ?>

											<?php } else { ?>

												<?php while ( $query->have_posts() ) { ?>
													<?php $query->the_post(); ?>
													<?php self::render_team_member(); ?>
												<?php } ?>
												<?php wp_reset_postdata(); ?>

											<?php } ?>

										<?php } ?>
	
									</div>
								</div>
								
								<?php if ( ! $group_by_center ) { ?>
									<?php //self::render_pagination( $query, 5 ); ?>
									<?php self::render_pagination_infinite( $query ); ?>
								<?php } ?>

							</div>
						</div>

					</div>
				</div>

			<?php
			$output = ob_get_clean();

			return $output;
		}


		protected static function render_team_member( $display_centers = true ) {
			global $post;

			$switched_site = false;
			$team_member_site_title = null;
			$syn_id = null;
			if ( get_post_type() == 'team_member_s' ) {
				$syn_id = get_the_ID();
				$original_post_id = get_post_meta( get_the_ID(), '_original_post_id', true );
				switch_to_blog( get_post_meta( get_the_ID(), '_original_site_id', true ) );
				$post = get_post( $original_post_id );
				setup_postdata( $post );
				if ( ! is_main_site() ) $team_member_site_title = get_bloginfo( 'name' );
				$switched_site = true;
			}

			?>
				<article <?php post_class( $syn_id ? 'post-' . $syn_id : '' ); ?>>
					<a href="<?php the_permalink(); ?>" data-post-id="<?php echo $syn_id ? $syn_id : get_the_ID(); ?>">
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
									<span class="overlay-label"><?php _e( 'View Bio', 'crown_blocks' ); ?></span>
								</div>
							</div>

							<div class="entry-teaser">

								<div class="push">

									<header class="entry-header <?php echo ! $display_centers ? 'no-border' : ''; ?>">

										<h3 class="entry-title"><?php the_title(); ?></h3>

										<?php $job_title = get_post_meta( get_the_ID(), 'team_member_job_title', true ); ?>
										<?php if ( ! empty( $job_title ) ) { ?>
											<p class="entry-job-title"><?php echo $job_title; ?></p>
										<?php } ?>

									</header>
									
									<?php if ( $display_centers ) { ?>
										<?php if ( $team_member_site_title ) { ?>
											<p class="entry-centers">
												<span class="center"><?php echo $team_member_site_title; ?></span>
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
												<!-- <p class="entry-centers">
													<span class="center"><?php echo get_bloginfo( 'name' ); ?></span>
												</p> -->
											<?php } ?>
										<?php } ?>
									<?php } ?>

								</div>

								<!-- <p class="entry-link">
									<span><?php _e( 'View My Bio', 'crown_blocks' ); ?></span>
								</p> -->

							</div>

						</div>
					</a>
				</article>
			<?php

			if ( $switched_site ) restore_current_blog();
		}


		public static function get_member_details( $post_id ) {
			global $post;

			$post = get_post( $post_id );
			if ( ! $post || ! in_array( $post->post_type, array( 'team_member', 'team_member_s' ) ) ) return '';
			setup_postdata( $post );

			$switched_site = false;
			$team_member_site_title = null;
			if ( get_post_type() == 'team_member_s' ) {
				$original_post_id = get_post_meta( get_the_ID(), '_original_post_id', true );
				switch_to_blog( get_post_meta( get_the_ID(), '_original_site_id', true ) );
				$post = get_post( $original_post_id );
				setup_postdata( $post );
				if ( ! is_main_site() ) $team_member_site_title = get_bloginfo( 'name' );
				$switched_site = true;
			}

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

								<?php if ( $team_member_site_title ) { ?>
									<p class="entry-centers">
										<span class="center"><?php echo $team_member_site_title; ?></span>
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
										<!-- <p class="entry-centers">
											<span class="center"><?php echo get_bloginfo( 'name' ); ?></span>
										</p> -->
									<?php } ?>
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
			$output = ob_get_clean();

			if ( $switched_site ) restore_current_blog();
			wp_reset_postdata();

			return $output;
		}


		public static function get_ajax_member_details() {
			global $post;

			$response = (object) array(
				'id' => '',
				'content' => ''
			);

			$id = isset( $_GET['id'] ) ? $_GET['id'] : '';
			$response->id = $id;

			if ( empty( $id ) ) wp_send_json( $response );

			$response->content = self::get_member_details( $id );
			wp_send_json( $response );
		}


	}
	Crown_Block_Team_Member_Index::init();
}