<footer id="footer" role="contentinfo">
	<div class="inner">
		<div class="container">

			<div class="upper">
				<div class="inner">

					<div id="site-footer-branding">

						<div id="site-footer-logo">
							<a href="<?php echo home_url( '/' ); ?>">
								<?php $logo = get_option( 'theme_config_site_logo_color' ); ?>
								<?php if ( ! empty( $logo ) ) { ?>
									<?php echo wp_get_attachment_image( $logo, 'medium_large', false ); ?>
								<?php } else { ?>
									<img src="<?php echo Crown_Theme::get_uri(); ?>/assets/img/logos/americas-sbdc-norcal-400w.png" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
								<?php } ?>
							</a>
						</div>

						<div id="site-footer-tagline">
							<p><?php echo get_bloginfo( 'description' ); ?></p>
						</div>

					</div>

					<div id="site-footer-links">

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

						<?php ct_social_links( array( 'title' => 'Stay Connected' ) ); ?>

					</div>

				</div>
			</div>

			<div class="lower">
				<div class="inner">

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

					<div id="site-footer-meta">

						<?php $description = apply_filters( 'crown_site_footer_description', '' ); ?>
						<?php if ( ! empty( $description ) ) { ?>
							<div id="site-description">
								<h3>
									<span class="icon">
										<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-heart" viewBox="0 0 16 16">
											<path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01L8 2.748zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143c.06.055.119.112.176.171a3.12 3.12 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15z"/>
										</svg>
									</span>
									<span>Made with #SmallBusinessLove</span>
								</h3>
								<?php echo apply_filters( 'the_content', $description ); ?>
							</div>
						<?php } ?>
	
						<div class="accreditations">
							<div class="inner">

								<div class="logo powered-by-sba">
									<a href="https://www.sba.gov" target="_blank"><img src="<?php echo Crown_Theme::get_uri(); ?>/assets/img/logos/powered-by-sba.png" alt="Powered by U.S. Small Business Association"></a>
								</div>

							</div>
						</div>

					</div>

				</div>
			</div>

		</div>
	</div>
</footer>