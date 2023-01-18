<div id="mobile-menu">
	<div class="scrollable">
		<div class="inner">
			<div class="menu-contents">
				<div class="bg">
					<?php $bg_image = wp_get_attachment_image_url( get_option( 'theme_config_explore_menu_bg_image' ), 'fullscreen' ); ?>
					<?php if ( $bg_image ) { ?>
						<img src="<?php echo $bg_image; ?>">
					<?php } ?>
					<div class="logo"><?php echo ct_get_svg( 'assets/img/star.svg' ); ?></div>
				</div>
				<div class="inner">

					<header id="mobile-menu-header">
						<div class="upper">
							<div id="mobile-menu-branding">
								<div id="mobile-menu-logo">
									<a href="<?php echo home_url( '/' ); ?>">
										<?php $logo = get_option( 'theme_config_site_logo_light' ); ?>
										<?php if ( ! empty( $logo ) ) { ?>
											<?php echo wp_get_attachment_image( $logo, 'medium_large', false, array( 'class' => 'light' ) ); ?>
										<?php } else { ?>
											<img src="<?php echo Crown_Theme::get_uri(); ?>/assets/img/logos/americas-sbdc-norcal-white-180h.png" class="light" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
										<?php } ?>
									</a>
								</div>
							</div>
							<button id="mobile-menu-close" type="button">
								<span class="label">Close</span>
								<span class="icon"></span>
							</button>
						</div>
					</header>

					<?php echo get_search_form(); ?>

					<nav id="mobile-menu-primary-navigation">
						<?php
							// $mega_menu = apply_filters( 'crown_mega_menu', null );
							// if ( ! empty( $mega_menu ) ) {
							// 	ct_nav_mega_menu( array(
							// 		'menu' => $mega_menu,
							// 		'id' => 'mobile-menu-primary-navigation-menu'
							// 	) );
							// } else {
								wp_nav_menu( array(
									'theme_location' => 'mobile_menu_primary',
									'container' => '',
									'menu_id' => 'mobile-menu-primary-navigation-menu',
									'depth' => 2,
									'fallback_cb' => false
								) );
							// }
						?>
					</nav>

					<div id="mobile-menu-extended-cta">
						<div class="inner">
							<div class="contents">
								<div class="logo">
									<?php $logo = get_option( 'theme_config_site_logo_light' ); ?>
									<?php if ( ! empty( $logo ) ) { ?>
										<?php echo wp_get_attachment_image( $logo, 'medium_large', false, array( 'class' => 'light' ) ); ?>
									<?php } else { ?>
										<img src="<?php echo Crown_Theme::get_uri(); ?>/assets/img/logos/americas-sbdc-norcal-white-180h.png" class="light" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
									<?php } ?>
								</div>
								<div class="content">
									<?php echo apply_filters( 'the_content', get_option( 'theme_config_explore_menu_cta_content' ) ); ?>
								</div>
							</div>
							<nav id="mobile-menu-extended-cta-links">
								<?php
									wp_nav_menu( array(
										'theme_location' => 'header_cta_links',
										'container' => '',
										'menu_id' => 'mobile-menu-extended-cta-links-menu',
										'depth' => 1,
										'fallback_cb' => false
									) );
								?>
							</nav>
						</div>
					</div>

					<div id="mobile-menu-footer-contents">
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
										<img src="<?php echo Crown_Theme::get_uri(); ?>/assets/img/logos/gobiz-clearout.png" alt="">
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

			<footer id="mobile-menu-footer">
				<div class="upper">

					<nav id="mobile-menu-primary-cta-links">
						<?php
							wp_nav_menu( array(
								'theme_location' => 'header_cta_links',
								'container' => '',
								'menu_id' => 'mobile-menu-primary-cta-links-menu',
								'depth' => 1,
								'fallback_cb' => false
							) );
						?>
					</nav>

				</div>
				<div class="lower">
					
					<?php ct_social_links( array( 'title' => 'Stay Connected' ) ); ?>

				</div>
			</footer>

		</div>
	</div>
</div>