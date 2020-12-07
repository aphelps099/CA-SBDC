<header id="header" role="banner">
	
	<div class="inner">
		<div class="container">
			<div class="inner">

				<div id="site-branding">
					<div id="site-title">
						<a href="<?php echo home_url( '/' ); ?>">
							<?php echo get_bloginfo( 'name' ); ?>
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
								'depth' => 3,
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
					<span class="icon"></span>
					<span class="label">Menu</span>
				</button>

			</div>
		</div>
	</div>

</header>