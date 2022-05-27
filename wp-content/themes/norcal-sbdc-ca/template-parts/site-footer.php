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

						<div id="google-translate"></div>
						<script>
							function googleTranslateElementInit() {
								new google.translate.TranslateElement({ pageLanguage: 'en' }, 'google-translate');
							}
						</script>
						<script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

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
								<h3>Made with #SmallBusinessLove</h3>
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