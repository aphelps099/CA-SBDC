<footer id="footer" role="contentinfo">
	<div class="inner">
		<div class="container">

			<div class="upper">
				<div class="inner">

					<div id="site-footer-branding">

						<div id="site-footer-logo">
							<a href="<?php echo home_url( '/' ); ?>">
								<?php $logo = get_option( 'theme_config_site_logo_light' ); ?>
								<?php if ( ! empty( $logo ) ) { ?>
									<?php echo wp_get_attachment_image( $logo, 'medium_large', false ); ?>
								<?php } else { ?>
									<img src="<?php echo Crown_Theme::get_uri(); ?>/assets/img/logos/americas-sbdc-norcal-white-180h.png" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
								<?php } ?>
							</a>
						</div>

					</div>

					<div id="site-footer-contents">

						<nav id="footer-primary-navigation">
							<?php
								wp_nav_menu( array(
									'theme_location' => 'mobile_menu_primary',
									'container' => '',
									'menu_id' => 'footer-primary-navigation-menu',
									'depth' => 2,
									'fallback_cb' => false
								) );
							?>
						</nav>

						<div id="site-footer-content">
							<div class="inner">
								<div class="powered-by-sba">
									<div class="logo">
										<a href="https://www.sba.gov" target="_blank"><img src="<?php echo Crown_Theme::get_uri(); ?>/assets/img/logos/powered-by-sba-light.png" alt="Powered by U.S. Small Business Association"></a>
									</div>
									<div class="content">
										<?php echo apply_filters( 'the_content', get_option( 'theme_config_explore_menu_footer_content' ) ); ?>
									</div>
								</div>
								<div class="networked">
									<h3>Networked With</h3>
									<div class="logos">
										<div class="logo">
											<img src="<?php echo Crown_Theme::get_uri(); ?>/assets/img/logos/ca-gobed.png" alt="">
										</div>
									</div>
								</div>
								<div class="accreditations">
									<h3>Accreditations and Certifications</h3>
									<div class="logos">
										<div class="logo">
											<img src="<?php echo Crown_Theme::get_uri(); ?>/assets/img/logos/sbdc-accredited-member-light.png" alt="America's SBDC Accredited Member">
										</div>
									</div>
								</div>
							</div>
						</div>

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

				</div>
			</div>

		</div>
	</div>
</footer>