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
				// $link->icon = ct_get_icon( 'brands/facebook-f', 'font-awesome' );
			} else if ( $link->slug == 'google_plus' ) {
				$link->label = 'Google+';
				// $link->icon = ct_get_icon( 'brands/google-plus-g', 'font-awesome' );
			} else if ( $link->slug == 'linkedin' ) {
				$link->label = 'LinkedIn';
				// $link->icon = ct_get_icon( 'brands/linkedin-in', 'font-awesome' );
			} else if ( $link->slug == 'youtube' ) {
				$link->label = 'YouTube';
			} else if ( $link->slug == 'pinterest' ) {
				// $link->icon = ct_get_icon( 'brands/pinterest-p', 'font-awesome' );
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


if ( ! function_exists( 'ct_mega_menu_item_content' ) ) {
	function ct_mega_menu_item_content( $item_id ) {

		$meta = array(
			'content' => get_post_meta( $item_id, 'content', true ),
			'thumbnail' => get_post_meta( $item_id, 'thumbnail', true ),
			'related_post' => get_post_meta( $item_id, 'related_post', true )
		);

		?>
			<div class="contents">

				<?php if ( ! empty( $meta['content'] ) ) { ?>

					<div class="content">
						<div class="inner">
							<?php echo apply_filters( 'the_content', $meta['content'] ); ?>
						</div>
					</div>

				<?php } ?>

				<?php if ( ! empty( $meta['related_post'] ) && ( $related_post = get_post( $meta['related_post'] ) ) ) { ?>

					<div class="related-post">
						<div class="inner">

							<div <?php post_class( 'featured-post-cta', $related_post->ID ); ?>>
								<a href="<?php echo get_permalink( $related_post->ID ); ?>">
									<div class="cta-bg">
										<?php if ( $related_post->post_type == 'case_study' ) { ?>
											<?php $gallery_photos = get_post_meta( $related_post->ID, 'case_study_gallery_photos', true ); ?>
											<?php if ( ! empty( $gallery_photos ) ) { ?>
												<?php echo wp_get_attachment_image( $gallery_photos[0], 'medium_large' ); ?>
											<?php } ?>
										<?php } else { ?>
											<?php echo get_the_post_thumbnail( $related_post->ID, 'medium_large' ); ?>
										<?php } ?>
									</div>
									<div class="inner">
										<div class="cta-contents">
											<h4 class="cta-title"><?php echo get_the_title( $related_post->ID ); ?></h4>
										</div>
										<div class="cta-label"><span>Learn More</span></div>
									</div>
								</a>
							</div>

						</div>
					</div>

				<?php } else if ( ! empty( $meta['thumbnail'] ) ) { ?>

					<div class="thumbnail">
						<div class="inner">
							<?php echo wp_get_attachment_image( $meta['thumbnail'], 'medium_large' ); ?>
						</div>
					</div>

				<?php } ?>

			</div>
		<?php

	}
}