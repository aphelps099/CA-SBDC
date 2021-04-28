<footer id="footer" role="contentinfo">
	<div class="inner">
		<div class="container">

			<div class="upper">

				<div class="logos-description">

					<div class="logos">
						<div class="inner">

							<div class="logo americas-sbdc-california">
								<img src="<?php echo Crown_Theme::get_uri(); ?>/assets/img/logos/americas-sbdc-california-white-180h.png" alt="America's SBDC California">
							</div>

							<div class="logo site-logo">
								<?php $logo = get_option( 'theme_config_site_logo_light' ); ?>
								<?php if ( ! empty( $logo ) ) { ?>
									<?php echo wp_get_attachment_image( $logo, 'medium_large', false, array( 'class' => 'light' ) ); ?>
								<?php } else { ?>
									<img src="<?php echo Crown_Theme::get_uri(); ?>/assets/img/logos/americas-sbdc-norcal-white-180h.png" class="light" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
								<?php } ?>
							</div>

							<div class="logo sbdc-accredited-member">
								<img src="<?php echo Crown_Theme::get_uri(); ?>/assets/img/logos/sbdc-accredited-member.png" alt="America's SBDC Accredited Member">
							</div>

						</div>
					</div>

					<?php $description = apply_filters( 'crown_site_footer_description', '' ); ?>
					<?php if ( ! empty( $description ) ) { ?>
						<div id="site-description"><?php echo apply_filters( 'the_content', $description ); ?></div>
					<?php } ?>

				</div>

				<div class="logo powered-by-sba">
					<a href="https://www.sba.gov" target="_blank"><img src="<?php echo Crown_Theme::get_uri(); ?>/assets/img/logos/powered-by-sba.png" alt="Powered by U.S. Small Business Association"></a>
				</div>

			</div>

			<div class="lower">

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
</footer>