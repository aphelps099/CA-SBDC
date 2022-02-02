<header id="header" role="banner">
	<div class="bg"></div>
	
	<div class="inner">
		<div class="container">
			<div class="inner">

				<div id="site-branding">

					<div id="site-logo">
						<a href="<?php echo home_url( '/' ); ?>">

							<?php $logo = get_option( 'theme_config_site_logo_color' ); ?>
							<?php if ( ! empty( $logo ) ) { ?>
								<?php echo wp_get_attachment_image( $logo, 'medium_large', false, array( 'class' => 'dark' ) ); ?>
							<?php } else { ?>
								<img src="<?php echo Crown_Theme::get_uri(); ?>/assets/img/logos/americas-sbdc-norcal-180h.png" class="dark" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
							<?php } ?>

							<?php $logo = get_option( 'theme_config_site_logo_light' ); ?>
							<?php if ( ! empty( $logo ) ) { ?>
								<?php echo wp_get_attachment_image( $logo, 'medium_large', false, array( 'class' => 'light' ) ); ?>
							<?php } else { ?>
								<img src="<?php echo Crown_Theme::get_uri(); ?>/assets/img/logos/americas-sbdc-norcal-white-180h.png" class="light" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
							<?php } ?>

						</a>
					</div>

					<div id="site-title">
						<a href="<?php echo home_url( '/' ); ?>">
							<div class="title"><?php echo get_bloginfo( 'name' ); ?></div>
							<div class="tagline"><?php echo get_bloginfo( 'description' ); ?></div>
						</a>
					</div>

				</div>

				<nav id="header-primary-navigation">
					<?php
						$mega_menu = apply_filters( 'crown_mega_menu', null );
						if ( ! empty( $mega_menu ) ) {
							ct_nav_mega_menu( array(
								'menu' => $mega_menu,
								'id' => 'header-primary-navigation-menu'
							) );
						} else {
							wp_nav_menu( array(
								'theme_location' => 'header_primary',
								'container' => '',
								'menu_id' => 'header-primary-navigation-menu',
								'depth' => 2,
								'fallback_cb' => false
							) );
						}
					?>
				</nav>

				<nav id="header-primary-cta-links">
					<?php
						wp_nav_menu( array(
							'theme_location' => 'header_cta_links',
							'container' => '',
							'menu_id' => 'header-primary-cta-links-menu',
							'depth' => 1,
							'fallback_cb' => false
						) );
					?>
				</nav>

				<button id="mobile-menu-toggle" type="button">
					<span class="label">Menu</span>
					<span class="icon"></span>
				</button>

			</div>
		</div>
	</div>

</header>