<?php get_header(); ?>

<?php if ( have_posts() ) { ?>
	<?php while ( have_posts() ) { ?>
		<?php the_post(); ?>
		<?php if ( class_exists( 'Crown_Block_Team_Member_Index' ) && method_exists( 'Crown_Block_Team_Member_Index', 'get_member_details' ) ) { ?>
			<?php echo Crown_Block_Team_Member_Index::get_member_details( get_the_ID() ); ?>
		<?php } ?>
	<?php } ?>
<?php } ?>

<?php get_footer(); ?>