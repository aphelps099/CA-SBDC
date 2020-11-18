<div id="mobile-menu">
	<div class="scrollable">
		<div class="inner">
			<div class="menu-contents">
	
				<nav id="mobile-menu-primary-navigation">
					<?php
						wp_nav_menu(
							array(
								'theme_location' => 'mobile_menu_primary',
								'container' => '',
								'menu_id' => 'mobile-menu-primary-navigation-menu',
								'depth' => 3
							)
						);
					?>
				</nav>

			</div>
		</div>
	</div>
</div>