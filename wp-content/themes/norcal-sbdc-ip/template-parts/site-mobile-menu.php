<div id="mobile-menu">
	<div class="scrollable">
		<div class="inner">
			<div class="menu-contents">

				<header id="mobile-menu-header">
					<div class="upper">

						<div id="mobile-menu-title">
							<div class="title"><?php echo get_bloginfo( 'name' ); ?></div>
						</div>

						<button id="mobile-menu-close" type="button">
							<span class="label">Close</span>
							<span class="icon"></span>
						</button>

					</div>
				</header>

				<nav id="mobile-menu-primary-navigation">
					<?php
						$mega_menu = apply_filters( 'crown_mega_menu', null );
						if ( ! empty( $mega_menu ) ) {
							ct_nav_mega_menu( array(
								'menu' => $mega_menu,
								'id' => 'mobile-menu-primary-navigation-menu'
							) );
						} else {
							wp_nav_menu( array(
								'theme_location' => 'mobile_menu_primary',
								'container' => '',
								'menu_id' => 'mobile-menu-primary-navigation-menu',
								'depth' => 3,
								'fallback_cb' => false
							) );
						}
					?>
				</nav>

				<nav id="mobile-menu-primary-cta-links">
					<?php
						wp_nav_menu( array(
							'theme_location' => 'mobile_cta_links',
							'container' => '',
							'menu_id' => 'mobile-menu-primary-cta-links-menu',
							'depth' => 1,
							'fallback_cb' => false
						) );
					?>
				</nav>

			</div>
		</div>
	</div>
</div>