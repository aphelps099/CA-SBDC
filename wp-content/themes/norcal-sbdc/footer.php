
						</div><!-- .inner -->
					</div><!-- .container -->
				</div><!-- .inner -->
			</div><!-- #main -->

			<?php get_template_part( 'template-parts/site-pre-footer' ); ?>
			<?php get_template_part( 'template-parts/site-footer' ); ?>
		
		</div><!-- #page -->

		<?php get_template_part( 'template-parts/site-mobile-menu' ); ?>
		<?php get_template_part( 'template-parts/lightbox-gallery' ); ?>

		<?php get_template_part( 'template-parts/modal-subscribe' ); ?>
		<?php get_template_part( 'template-parts/modal-video' ); ?>

		<?php $modal_form_ids = apply_filters( 'ct_footer_modal_form_ids', array() ); ?>
		<?php foreach( $modal_form_ids as $modal_form_id ) { ?>
			<?php get_template_part( 'template-parts/modal-form', null, array( 'form_id' => $modal_form_id ) ); ?>
		<?php } ?>

		<script>
			ctSetVw();
		</script>

		<?php wp_footer(); ?>

	</body>

</html>