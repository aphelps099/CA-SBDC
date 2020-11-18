<footer id="footer" role="contentinfo">
	<div class="inner">
		<div class="container">

			<div class="upper">
				<div class="inner">

					<div id="footer-brand">

						<?php if ( has_custom_logo() ) { ?>
							<div id="footer-logo">
								<?php echo get_custom_logo(); ?>
							</div>
						<?php } ?>
	
					</div>

					<aside id="footer-widgets-1" class="footer-widgets">
						<?php dynamic_sidebar( 'footer-1' ); ?>
					</aside>

					<aside id="footer-widgets-2" class="footer-widgets">
						<?php dynamic_sidebar( 'footer-2' ); ?>
					</aside>

					<aside id="footer-widgets-3" class="footer-widgets">
						<?php dynamic_sidebar( 'footer-3' ); ?>
					</aside>

					<aside id="footer-widgets-4" class="footer-widgets">
						<?php dynamic_sidebar( 'footer-4' ); ?>
					</aside>

				</div>
			</div>

			<div class="lower">
				<div class="inner">

					<p id="site-copyright"><?php echo get_option( 'theme_config_footer_copyright' ); ?></p>

					<nav id="footer-legal-links">
						<?php
							wp_nav_menu( array(
								'theme_location' => 'footer_legal_links',
								'container' => '',
								'menu_id' => 'footer-legal-links-menu',
								'depth' => 1
							) );
						?>
					</nav>

					<?php ct_social_links(); ?>

				</div>
			</div>

		</div>
	</div>
</footer>