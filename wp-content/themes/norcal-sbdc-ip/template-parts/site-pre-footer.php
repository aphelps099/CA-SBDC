<div id="pre-footer">
	<div class="inner">
		<div class="container">

			<div id="site-footer-header"><?php echo get_bloginfo( 'name' ); ?></div>

			<div class="contents">

				<div class="menus">

					<nav id="footer-primary-navigation">
						<?php
							wp_nav_menu( array(
								'theme_location' => 'footer_primary',
								'container' => '',
								'menu_id' => 'footer-primary-menu',
								'depth' => 2,
								'fallback_cb' => false
							) );
						?>
					</nav>

					<nav id="footer-primary-cta-links">
						<?php
							wp_nav_menu( array(
								'theme_location' => 'footer_cta_links',
								'container' => '',
								'menu_id' => 'footer-primary-cta-links-menu',
								'depth' => 1,
								'fallback_cb' => false
							) );
						?>
					</nav>

				</div>

				<div class="social-ctas">
					<?php ct_social_links( array( 'title' => 'Stay Connected' ) ); ?>
				</div>

			</div>

		</div>
	</div>
</div>