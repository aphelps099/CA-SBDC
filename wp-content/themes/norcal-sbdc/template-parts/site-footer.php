<footer id="footer" role="contentinfo">
	<div class="inner">
		<div class="container">

			<div class="upper">
				<div class="inner">

					<div id="site-footer-regional-info">

						<div class="contact">
							<h3><?php _e( 'Questions?', 'crown_theme' ) ?> <span class="bubble"><?php _e( 'Call us.', 'crown_theme' ) ?></span></h3>
							<?php echo do_shortcode( '[contact_info context="phone-hours"]' ); ?>
						</div>

						<?php $excluded_center_ids = get_posts( array( 'post_type' => 'center', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_query' => array( array( 'key' => '__center_options', 'value' => 'hide-from-index' ) ) ) ); ?>
						<?php $centers = get_posts( array( 'post_type' => 'center', 'posts_per_page' => -1, 'post__not_in' => $excluded_center_ids, 'orderby' => 'title', 'order' => 'ASC' ) ); ?>
						<?php if ( ! empty( $centers ) ) { ?>
							<div class="regional-offices">
								<h3><?php _e( 'Regional Offices', 'crown_theme' ) ?>:</h3>
								<ul>
									<?php foreach ( $centers as $center ) { ?>
										<li>
											<?php if ( ( $website = get_post_meta( $center->ID, 'center_website', true ) ) ) { ?>
												<a href="<?php echo $website; ?>" target="_blank"><?php echo $center->post_title; ?></a>
											<?php } else { ?>
												<?php echo $center->post_title; ?>
											<?php } ?>
										</li>
									<?php } ?>
								</ul>
							</div>
						<?php } ?>

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

					<?php /* to be used in center sites ?>

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

					<?php */ ?>

					<div class="social-ctas">

						<?php ct_social_links( array( 'title' => 'Stay Connected' ) ); ?>

						<?php $google_reviews_link = get_option( 'theme_config_footer_google_reviews_link' ); ?>
						<?php if ( ! empty( $google_reviews_link ) ) { ?>
							<a href="<?php echo $google_reviews_link; ?>" target="_blank" class="google-reviews-link">
								<span class="inner">Review us on Google</span>
							</a>
						<?php } ?>

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

					<div id="site-footer-meta">

						<?php $description = apply_filters( 'crown_site_footer_description', '' ); ?>
						<?php if ( ! empty( $description ) ) { ?>
							<div id="site-description"><?php echo apply_filters( 'the_content', $description ); ?></div>
						<?php } ?>
	
						<div class="accreditations">
							<div class="inner">

								<div class="logo powered-by-sba">
									<a href="https://www.sba.gov" target="_blank"><img src="<?php echo Crown_Theme::get_uri(); ?>/assets/img/logos/powered-by-sba.png" alt="Powered by U.S. Small Business Association"></a>
								</div>

								<div class="logo ca-gobed">
									<a href="https://business.ca.gov" target="_blank"><img src="<?php echo Crown_Theme::get_uri(); ?>/assets/img/logos/ca-osba.png" alt="California Governor's Office of Business and Economic Development"></a>
								</div>

							</div>
						</div>

						<div class="logos">
							<div class="inner">

								<div class="logo sbdc-accredited-member">
									<img src="<?php echo Crown_Theme::get_uri(); ?>/assets/img/logos/sbdc-accredited-member-alt.png" alt="America's SBDC Accredited Member">
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