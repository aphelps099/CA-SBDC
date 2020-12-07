<?php


if ( ! function_exists( 'ct_social_links' ) ) {
	function ct_social_links( $args = array() ) {

		$args = array_merge( array(
			'title' => '',
			'links' => array(
				'facebook',
				'twitter',
				'google_plus',
				'linkedin',
				'instagram',
				'youtube',
				'vimeo',
				'pinterest'
			)
		), $args );

		$links = array();
		foreach ( $args['links'] as $slug ) {
			$link = (object) array(
				'slug' => $slug,
				'label' => ucwords( str_replace( '_', ' ', $slug ) ),
				'url' => get_option( 'theme_config_' . $slug . '_profile_url', '' ),
				'icon' => ct_get_icon( 'brands/' . $slug, 'font-awesome' )
			);
			if ( empty( $link->url ) ) continue;
			if ( $link->slug == 'facebook' ) {
				$link->icon = ct_get_icon( 'brands/facebook-f', 'font-awesome' );
			} else if ( $link->slug == 'google_plus' ) {
				$link->label = 'Google+';
				$link->icon = ct_get_icon( 'brands/google-plus-g', 'font-awesome' );
			} else if ( $link->slug == 'linkedin' ) {
				$link->label = 'LinkedIn';
				$link->icon = ct_get_icon( 'brands/linkedin-in', 'font-awesome' );
			} else if ( $link->slug == 'youtube' ) {
				$link->label = 'YouTube';
			} else if ( $link->slug == 'pinterest' ) {
				$link->icon = ct_get_icon( 'brands/pinterest-p', 'font-awesome' );
			}
			$links[] = $link;
		}
		if ( empty( $links ) ) return;

		?>
			<div class="social-links">

				<?php if ( ! empty( $args['title'] ) ) { ?>
					<h4><?php echo $args['title']; ?></h4>
				<?php } ?>

				<ul class="menu">
					<?php foreach ( $links as $link ) { ?>
						<li class="menu-item <?php echo $link->slug; ?>">
							<a href="<?php echo $link->url; ?>" target="_blank">
								<span class="icon"><?php echo $link->icon; ?></span>
								<span class="label"><?php echo $link->label; ?></span>
							</a>
						</li>
					<?php } ?>
				</ul>

			</div>
		<?php

	}
}


if ( ! function_exists( 'ct_social_sharing_links' ) ) {
	function ct_social_sharing_links( $args = array() ) {

		$post_id = get_the_ID();
		$post = get_post( $post_id );
		if ( ! $post ) return;

		$content = (object) array(
			'url' => get_permalink( $post ),
			'text' => get_the_title( $post ),
			'img' => ct_get_image_src( get_post_thumbnail_id(), 'medium_large' ),
			'via' => '',
			'hashtags' => ''
		);
		if ( empty( $content->url ) ) return;

		$args = array_merge( array(
			'title' => __( 'Share', 'crown_theme' ),
			'links' => array(
				'linkedin',
				'facebook',
				'twitter',
				'email'
			)
		), $args );

		$links = array();
		foreach ( $args['links'] as $slug ) {
			$link = (object) array(
				'slug' => $slug,
				'label' => ucwords( str_replace( '_', ' ', $slug ) ),
				'url' => '',
				'icon' => ct_get_icon( 'brands/' . $slug, 'font-awesome' )
			);
			if ( $link->slug == 'facebook' ) {
				$link_data = array( 'u' => $content->url );
				$link->url = 'https://www.facebook.com/sharer/sharer.php?' . http_build_query( $link_data );
				$link->icon = ct_get_icon( 'brands/facebook-f', 'font-awesome' );
			} else if ( $link->slug == 'twitter' ) {
				$link_data = array( 'url' => $content->url );
				if ( ! empty( $content->text ) ) $link_data['text'] = $content->text;
				if ( ! empty( $content->via ) ) $link_data['via'] = $content->via;
				if ( ! empty( $content->hashtags ) ) $link_data['hashtags'] = $content->hashtags;
				$link->url = 'https://twitter.com/intent/tweet?' . http_build_query( $link_data );
			} else if ( $link->slug == 'linkedin' ) {
				$link->label = 'LinkedIn';
				$link_data = array( 'url' => $content->url, 'source' => $content->url );
				if ( ! empty( $content->text ) ) $link_data['title'] = $content->text;
				$link->url = 'https://www.linkedin.com/shareArticle?mini=true&' . http_build_query( $link_data );
				$link->icon = ct_get_icon( 'brands/linkedin-in', 'font-awesome' );
			} else if ( $link->slug == 'email' ) {
				$link_data = array( 'body' => $content->url );
				if ( ! empty( $content->text ) ) $link_data['subject'] = $content->text;
				$link->url = 'mailto:?' . http_build_query( $link_data, '', '&', PHP_QUERY_RFC3986 );
				$link->icon = ct_get_icon( 'solid/envelope', 'font-awesome' );
			}
			$links[] = $link;
		}
		if ( empty( $links ) ) return;

		?>
			<div class="social-sharing-links">

				<?php if ( ! empty( $args['title'] ) ) { ?>
					<h4><?php echo $args['title']; ?></h4>
				<?php } ?>

				<ul class="menu">
					<?php foreach ( $links as $link ) { ?>
						<li class="menu-item <?php echo $link->slug; ?>">
							<a href="<?php echo $link->url; ?>" target="_blank">
								<span class="icon"><?php echo $link->icon; ?></span>
								<span class="label"><?php echo $link->label; ?></span>
							</a>
						</li>
					<?php } ?>
				</ul>

			</div>
		<?php

	}
}


if ( ! function_exists( 'ct_app_links' ) ) {
	function ct_app_links( $args = array() ) {

		$args = array_merge( array(
			'title' => '',
			'links' => array(
				'ios',
				'android'
			)
		), $args );

		$links = array();
		foreach ( $args['links'] as $slug ) {
			$link = (object) array(
				'slug' => $slug,
				'label' => ucwords( str_replace( '_', ' ', $slug ) ),
				'url' => get_option( 'theme_config_' . $slug . '_app_url', '' ),
				'icon' => ct_get_icon( 'brands/' . $slug, 'font-awesome' )
			);
			if ( empty( $link->url ) ) continue;
			if ( $link->slug == 'ios' ) {
				$link->label = 'iOS';
				$link->icon = ct_get_icon( 'brands/apple', 'font-awesome' );
			}
			$links[] = $link;
		}
		if ( empty( $links ) ) return;

		?>
			<div class="app-links">

				<?php if ( ! empty( $args['title'] ) ) { ?>
					<h4><?php echo $args['title']; ?></h4>
				<?php } ?>

				<ul class="menu">
					<?php foreach ( $links as $link ) { ?>
						<li class="menu-item <?php echo $link->slug; ?>">
							<a href="<?php echo $link->url; ?>" target="_blank">
								<span class="icon"><?php echo $link->icon; ?></span>
								<span class="label"><?php echo $link->label; ?></span>
							</a>
						</li>
					<?php } ?>
				</ul>

			</div>
		<?php

	}
}


if ( ! function_exists( 'ct_nav_mega_menu' ) ) {
	function ct_nav_mega_menu( $args = array() ) {

		$args = array_merge( array(
			'menu' => null,
			'id' => '',
			'class' => ''
		), $args );

		$menu = $args['menu'];
		if ( empty( $menu ) || empty( $menu->items ) ) return;

		?>
			<ul <?php echo ! empty( $args['id'] ) ? 'id="' . esc_attr( $args['id'] ) . '"' : ''; ?> class="menu mega-menu <?php echo esc_attr( $args['class'] ); ?>">
				
				<?php foreach ( $menu->items as $menu_item ) { ?>
					<li id="menu-item-<?php echo $menu_item->id; ?>" class="menu-item menu-item-<?php echo $menu_item->id; ?> mega-menu-item">

						<a <?php echo ! empty( $menu_item->link->href ) ? 'href="' . esc_attr( $menu_item->link->href ) . '"' : ''; ?> <?php echo ! empty( $menu_item->link->href ) ? 'target="' . $menu_item->link->target . '"' : ''; ?>>
							<span class="label"><?php echo $menu_item->title; ?></span>
						</a>

						<?php if ( ! in_array( $menu_item->type, array( 'disabled' ) ) ) { ?>
							<div id="sub-menu-<?php echo $menu_item->id; ?>" class="sub-menu sub-menu-<?php echo $menu_item->id; ?> mega-sub-menu <?php echo $menu_item->type; ?>">
								<div class="inner">

									<?php if ( $menu_item->sections->primary ) { ?>

										<section class="sub-menu-section primary <?php echo $menu_item->sections->primary->layout; ?>">
											<div class="container">
												<div class="inner">

													<?php if ( ! empty( $menu_item->sections->primary->title ) ) { ?>
														<h3><?php echo $menu_item->sections->primary->title; ?></h3>
													<?php } ?>

													<?php if ( ! empty( $menu_item->sections->primary->menus ) ) { ?>
														<div class="sub-menu-menus">
															<?php foreach ( $menu_item->sections->primary->menus as $sub_menu ) { ?>
																<div class="sub-menu-container">
																	<?php if ( ! empty( $sub_menu->title ) ) { ?>
																		<h4>
																			<?php if ( ! empty( $sub_menu->link->href ) ) { ?>
																				<a href="<?php echo esc_attr( $sub_menu->link->href ); ?>" target="<?php echo $sub_menu->link->target; ?>"><?php echo $sub_menu->title; ?></a>
																			<?php } else { ?>
																				<?php echo $sub_menu->title; ?>
																			<?php } ?>
																		</h4>
																	<?php } ?>
																	<?php if ( ! empty( $sub_menu->menu_id ) ) { ?>
																		<?php wp_nav_menu( array( 'menu' => $sub_menu->menu_id ) ); ?>
																	<?php } ?>
																</div>
															<?php } ?>
														</div>
													<?php } ?>

													<?php if ( ! empty( $menu_item->sections->primary->content ) ) { ?>
														<div class="sub-menu-content">
															<?php echo apply_filters( 'the_content', $menu_item->sections->primary->content ); ?>
														</div>
													<?php } ?>

													<?php if ( ! empty( $menu_item->sections->primary->cta_links ) ) { ?>
														<div class="sub-menu-cta-links">
															<ul>
																<?php foreach ( $menu_item->sections->primary->cta_links as $cta_link ) { ?>
																	<?php if ( ! empty( $cta_link->href ) ) { ?>
																		<li>
																			<a href="<?php echo esc_attr( $cta_link->href ); ?>" target="<?php echo $cta_link->target; ?>">
																				<?php echo ! empty( $cta_link->label ) ? $cta_link->label : 'Learn More'; ?>
																			</a>
																		</li>
																	<?php } ?>
																<?php } ?>
															</ul>
														</div>
													<?php } ?>

													<?php if ( $menu_item->type == 'events' ) { ?>
														<div class="sub-menu-events">
															<em>Upcoming events go here...</em>
														</div>
													<?php } ?>

												</div>
											</div>
										</section>

									<?php } ?>

									<?php if ( $menu_item->sections->secondary ) { ?>

										<section class="sub-menu-section secondary <?php echo $menu_item->sections->secondary->layout; ?>">
											<div class="container">
												<div class="inner">

													<?php if ( ! empty( $menu_item->sections->secondary->title ) ) { ?>
														<h3><?php echo $menu_item->sections->secondary->title; ?></h3>
													<?php } ?>

													<?php if ( ! empty( $menu_item->sections->secondary->menus ) ) { ?>
														<div class="sub-menu-menus">
															<?php foreach ( $menu_item->sections->secondary->menus as $sub_menu ) { ?>
																<div class="sub-menu-container">
																	<?php if ( ! empty( $sub_menu->title ) ) { ?>
																		<h4>
																			<?php if ( ! empty( $sub_menu->link->href ) ) { ?>
																				<a href="<?php echo esc_attr( $sub_menu->link->href ); ?>" target="<?php echo $sub_menu->link->target; ?>"><?php echo $sub_menu->title; ?></a>
																			<?php } else { ?>
																				<?php echo $sub_menu->title; ?>
																			<?php } ?>
																		</h4>
																	<?php } ?>
																	<?php if ( ! empty( $sub_menu->menu_id ) ) { ?>
																		<?php wp_nav_menu( array( 'menu' => $sub_menu->menu_id ) ); ?>
																	<?php } ?>
																</div>
															<?php } ?>
														</div>
													<?php } ?>

													<?php if ( ! empty( $menu_item->sections->secondary->content ) ) { ?>
														<div class="sub-menu-content">
															<?php echo apply_filters( 'the_content', $menu_item->sections->secondary->content ); ?>
														</div>
													<?php } ?>

													<?php if ( ! empty( $menu_item->sections->secondary->cta_links ) ) { ?>
														<div class="sub-menu-cta-links">
															<ul>
																<?php foreach ( $menu_item->sections->secondary->cta_links as $cta_link ) { ?>
																	<?php if ( ! empty( $cta_link->href ) ) { ?>
																		<li>
																			<a href="<?php echo esc_attr( $cta_link->href ); ?>" target="<?php echo $cta_link->target; ?>">
																				<?php echo ! empty( $cta_link->label ) ? $cta_link->label : 'Learn More'; ?>
																			</a>
																		</li>
																	<?php } ?>
																<?php } ?>
															</ul>
														</div>
													<?php } ?>

												</div>
											</div>
										</section>

									<?php } ?>

								</div>
							</div>
						<?php } ?>

					</li>
				<?php } ?>

			</ul>
		<?php

	}
}