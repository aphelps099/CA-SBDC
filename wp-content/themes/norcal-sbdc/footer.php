
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

		<?php $modal_forms = ct_get_footer_modal_forms(); ?>
		<?php foreach( $modal_forms as $modal_form ) { ?>
			<?php get_template_part( 'template-parts/modal-form', null, array( 'form' => $modal_form ) ); ?>
		<?php } ?>

		<script>
			ctSetVw();
		</script>

		<?php wp_footer(); ?>

	</body>

</html>