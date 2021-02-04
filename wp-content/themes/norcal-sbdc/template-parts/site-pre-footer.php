<div id="pre-footer">
	<div class="inner">
		<div class="container">

			<div id="site-footer-header"><?php echo get_bloginfo( 'name' ); ?></div>

			<div class="contents">

				<?php $form_id = get_option( 'theme_config_footer_subscribe_form' ); ?>
				<?php if ( ! empty( $form_id ) && function_exists( 'gravity_form' ) ) { ?>
					<div id="footer-subscribe-form">
						<h6 class="form-overtitle"><span><?php _e( 'SBDC', 'crown_theme' ); ?></span><?php _e( 'Intel&trade;', 'crown_theme' ); ?></h6>
						<?php gravity_form( $form_id, true, true, false, null, true, 1000000, true ); ?>
					</div>
				<?php } ?>

				<div id="site-footer-logos">

					<div id="site-footer-logo">
						<a href="<?php echo home_url( '/' ); ?>">
							<img src="<?php echo Crown_Theme::get_uri(); ?>/assets/img/logos/americas-sbdc-norcal-400w.png" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
						</a>
					</div>

					<?php $host_logos = get_repeater_entries( 'blog', 'theme_config_footer_host_logos' ); ?>
					<?php if ( ! empty( $host_logos ) ) { ?>
						<div id="site-footer-host-logos">
							<p class="intro"><?php _e( 'Our Program is Proudly Hosted by:', 'crown_theme' ); ?></p>
							<div class="logos">
								<?php foreach( $host_logos as $logo ) { ?>
									<div class="logo">
										<?php if ( ! empty( $logo['link_url'] ) ) { ?><a href="<?php echo esc_attr( $logo['link_url'] ); ?>" target="_blank"><?php } ?>
										<?php echo wp_get_attachment_image( $logo['image'], 'medium' ); ?>
										<?php if ( ! empty( $logo['link_url'] ) ) { ?></a><?php } ?>
									</div>
								<?php } ?>
							</div>
						</div>
					<?php } ?>

				</div>

			</div>

		</div>
	</div>
</div>