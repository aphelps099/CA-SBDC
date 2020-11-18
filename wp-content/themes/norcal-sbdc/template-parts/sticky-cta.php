<?php if ( is_singular( array( 'post' ) ) ) { ?>

	<?php
		$cta = (object) array(
			'title' => get_option( 'theme_config_sticky_cta_title' ),
			'description' => get_option( 'theme_config_sticky_cta_description' ),
			'link' => (object) array(
				'type' => get_option( 'theme_config_sticky_cta_link_type' ),
				'label' => get_option( 'theme_config_sticky_cta_link_label' ),
				'url' => get_option( 'theme_config_sticky_cta_link_url' ),
				'custom_url_options' => get_option( 'theme_config_sticky_cta_link_custom_url_options', array() ),
				'contact_form_title' => get_option( 'theme_config_sticky_cta_link_contact_form_title' ),
				'contact_form_embed_script' => get_option( 'theme_config_sticky_cta_link_contact_form_embed_script' ),
				'contact_form_disclaimer' => get_option( 'theme_config_sticky_cta_link_contact_form_disclaimer' ),
			)
		);
	?>

	<?php if ( ! empty( $cta->title ) || ! empty( $cta->description ) || ( $cta->link->type == 'custom-url' && ! empty( $cta->link->url ) ) || ( $cta->link->type == 'contact-form' && ! empty( $cta->link->contact_form_embed_script ) ) ) { ?>
		<div id="site-sticky-cta" class="call-to-action" data-hash="<?php echo esc_attr( md5( json_encode( $cta ) ) ); ?>">
			<div class="inner">

				<div class="teaser">
					<div class="inner">

						<button type="button" class="cta-dismiss">
							<span class="label">Close</span>
							<?php ct_icon( 'x-circle-fill' ); ?>
						</button>

						<?php if ( ! empty( $cta->title ) ) { ?>
							<h3 class="title"><?php echo $cta->title; ?></h3>
						<?php } ?>

						<?php if ( ! empty( $cta->description ) ) { ?>
							<div class="description"><?php echo apply_filters( 'the_content', $cta->description ); ?></div>
						<?php } ?>

						<?php if ( ( $cta->link->type == 'custom-url' && ! empty( $cta->link->url ) ) || ( $cta->link->type == 'contact-form' && ! empty( $cta->link->contact_form_embed_script ) ) ) { ?>
							<div class="link">
								<?php if ( $cta->link->type == 'custom-url' && ! empty( $cta->link->url ) ) { ?>
									<a href="<?php echo $cta->link->url; ?>" class="btn btn-primary btn-sm" <?php echo in_array( 'open-in-new-window', $cta->link->custom_url_options ) ? 'target="_blank"' : ''; ?>><?php echo ! empty( $cta->link->label ) ? $cta->link->label : __( 'Learn More', 'crown_theme' ); ?></a>
								<?php } else if ( $cta->link->type == 'contact-form' && ! empty( $cta->link->contact_form_embed_script ) ) { ?>
									<button type="button" class="btn btn-primary btn-sm contact-form-toggle"><?php echo ! empty( $cta->link->label ) ? $cta->link->label : __( 'Learn More', 'crown_theme' ); ?></button>
								<?php } ?>
							</div>
						<?php } ?>

					</div>
				</div>

				<?php if ( $cta->link->type == 'contact-form' && ! empty( $cta->link->contact_form_embed_script ) ) { ?>
					<div class="modal fade contact-form" tabindex="-1" role="dialog">
						<div class="modal-dialog modal-dialog-centered">
							<div class="modal-content">
								<div class="modal-header">
									<?php if ( ! empty( $cta->link->contact_form_title ) ) { ?>
										<h5 class="modal-title"><?php echo $cta->link->contact_form_title; ?></h5>
									<?php } ?>
									<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">
											<svg class="bi bi-x" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
												<path fill-rule="evenodd" d="M11.854 4.146a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708-.708l7-7a.5.5 0 0 1 .708 0z"/>
												<path fill-rule="evenodd" d="M4.146 4.146a.5.5 0 0 0 0 .708l7 7a.5.5 0 0 0 .708-.708l-7-7a.5.5 0 0 0-.708 0z"/>
											</svg>
										</span>
									</button>
								</div>
								<div class="modal-body">
									<div class="contact-form-container">
										<?php echo $cta->link->contact_form_embed_script; ?>
									</div>
									<?php if ( ! empty( $cta->link->contact_form_disclaimer ) ) { ?>
										<p class="disclaimer"><?php echo nl2br( $cta->link->contact_form_disclaimer ); ?></p>
									<?php } ?>
								</div>
							</div>
						</div>
					</div>
				<?php } ?>

			</div>
		</div>
	<?php } ?>

<?php } ?>