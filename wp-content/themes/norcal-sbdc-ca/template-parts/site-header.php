<header id="header" role="banner">
	
	<div class="inner">
		<div class="container">
			<div class="inner">

				<div id="site-branding">

					<div id="site-title">
						<a href="<?php echo home_url( '/' ); ?>">
							<div class="title"><span class="location">California</span> <span class="sbdc">SBDC</span></div>
						</a>
					</div>

				</div>

				<nav id="header-primary-navigation">
					<?php
						// $mega_menu = apply_filters( 'crown_mega_menu', null );
						// if ( ! empty( $mega_menu ) ) {
						// 	ct_nav_mega_menu( array(
						// 		'menu' => $mega_menu,
						// 		'id' => 'header-primary-navigation-menu'
						// 	) );
						// } else {
							wp_nav_menu( array(
								'theme_location' => 'header_primary',
								'container' => '',
								'menu_id' => 'header-primary-navigation-menu',
								'depth' => 2,
								'fallback_cb' => false
							) );
						// }
					?>
				</nav>

				<?php /*<nav id="header-primary-cta-links">
					<div id="google-translate"></div>
					<script>
						function googleTranslateElementInit() {
							new google.translate.TranslateElement({ pageLanguage: 'en' }, 'google-translate');
						}
					</script>
					<script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
					<?php
						wp_nav_menu( array(
							'theme_location' => 'header_cta_links',
							'container' => '',
							'menu_id' => 'header-primary-cta-links-menu',
							'depth' => 1,
							'fallback_cb' => false
						) );
					?>
				</nav>*/ ?>

				<?php /*<div id="header-search">
					<button class="toggle">
						<span class="icon"><?php ct_icon( 'search' ); ?></span>
						<span class="label">Search</span>
					</button>
					<?php echo get_search_form(); ?>
				</div>*/ ?>

				<button id="mobile-menu-toggle" type="button">
					<span class="label">Explore</span>
					<span class="icon"></span>
				</button>

			</div>
		</div>
	</div>

</header>