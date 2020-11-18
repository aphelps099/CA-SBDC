<header id="header" role="banner">
	
	<div class="inner">
		<div class="container">
			<div class="inner">

				<div id="site-branding">
					<?php if ( has_custom_logo() ) { ?>
						<div id="site-logo">
							<?php echo get_custom_logo(); ?>
						</div>
					<?php } else { ?>
						<div id="site-title">
							<a href="<?php echo home_url( '/' ); ?>"><?php echo get_bloginfo( 'name' ); ?></a>
						</div>
					<?php } ?>
				</div>

				<button id="mobile-menu-toggle" type="button">
					<span class="icon"></span>
					<span class="label">Menu</span>
				</button>

				<nav id="header-primary-navigation">
					<?php
						wp_nav_menu(
							array(
								'theme_location' => 'header_primary',
								'container' => '',
								'menu_id' => 'header-primary-navigation-menu',
								'depth' => 2
							)
						);
					?>
				</nav>

			</div>
		</div>
	</div>

</header>