<?php $gated_content_settings = get_post_gated_content_settings(); ?>
<div id="main-gated-content">
	<div class="inner">

		<div class="gated-content-preview">
			<div class="inner">
				<?php get_template_part( 'template-parts/content', get_post_type() ); ?>
			</div>
		</div><!-- .entry-content -->

		<div class="gated-content-form">
			<div class="inner">
				
				<?php if ( ! empty( $gated_content_settings->title ) ) { ?>
					<h3><?php echo $gated_content_settings->title; ?></h3>
				<?php } ?>
	
				<div class="form-container"><?php echo $gated_content_settings->embed_script; ?></div>
	
				<?php if ( ! empty( $gated_content_settings->disclaimer ) ) { ?>
					<div class="disclaimer"><?php echo apply_filters( 'the_content', $gated_content_settings->disclaimer ); ?></div>
				<?php } ?>
			
			</div>
		</div>

	</div>
</div>