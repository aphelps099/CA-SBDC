<footer id="footer" role="contentinfo">
	<div class="inner">
		<div class="container">

			<div class="upper">
				<div class="inner">

					<div id="site-footer-branding">
						<div id="site-footer-title">
							<a href="<?php echo home_url( '/' ); ?>"><?php echo get_bloginfo( 'name' ); ?></a>
						</div>
					</div>

					<div class="footer-widget-areas">
						<div class="inner">
							
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

					<?php ct_social_links( array( 'title' => 'Stay Connected' ) ); ?>

				</div>
			</div>

			<div class="lower">
				<div class="inner">

					<div id="site-footer-meta">

						<?php $description = apply_filters( 'crown_site_footer_description', '' ); ?>
						<?php if ( ! empty( $description ) ) { ?>
							<div id="site-description"><?php echo apply_filters( 'the_content', $description ); ?></div>
						<?php } ?>
	
						<div class="accreditations">
							<div class="inner">

								<div class="logo ca-gobed">
									<img src="<?php echo Crown_Theme::get_uri(); ?>/assets/img/logos/ca-gobed.png" alt="California Governor's Office of Business and Economic Development">
								</div>

								<div class="logo sbdc-accredited-member">
									<img src="<?php echo Crown_Theme::get_uri(); ?>/assets/img/logos/sbdc-accredited-member.png" alt="America's SBDC Accredited Member">
								</div>

							</div>
						</div>

						<div class="logos">
							<div class="inner">

								<div class="logo powered-by-sba">
									<img src="<?php echo Crown_Theme::get_uri(); ?>/assets/img/logos/powered-by-sba.png" alt="Powered by U.S. Small Business Association">
								</div>

							</div>
						</div>

					</div>

					<?php $copyright = apply_filters( 'crown_site_footer_copyright', '' ); ?>
					<?php if ( ! empty( $copyright ) ) { ?>
						<p id="site-copyright"><?php echo $copyright; ?></p>
					<?php } ?>

					<nav id="footer-legal-links">
						<?php
							wp_nav_menu( array(
								'theme_location' => 'footer_legal_links',
								'container' => '',
								'menu_id' => 'footer-legal-links-menu',
								'depth' => 1,
								'fallback_cb' => false
							) );
						?>
					</nav>

				</div>
			</div>

		</div>
	</div>
</footer>