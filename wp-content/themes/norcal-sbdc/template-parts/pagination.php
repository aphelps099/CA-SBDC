<?php

$prev_text = __( 'Previous', 'crown_theme' );
$next_text = __( 'Next', 'crown_theme' );

$posts_pagination = get_the_posts_pagination(
	array(
		'prev_text' => __( $prev_text, 'crown_theme' ),
		'next_text' => __( $next_text, 'crown_theme' ),
		'mid_size' => 2
	)
);

// // If we're not outputting the previous page link, prepend a placeholder with `visibility: hidden` to take its place.
// if ( strpos( $posts_pagination, 'prev page-numbers' ) === false ) {
// 	$posts_pagination = str_replace( '<div class="nav-links">', '<div class="nav-links"><span class="prev page-numbers placeholder" aria-hidden="true">' . $prev_text . '</span>', $posts_pagination );
// }

// // If we're not outputting the next page link, append a placeholder with `visibility: hidden` to take its place.
// if ( strpos( $posts_pagination, 'next page-numbers' ) === false ) {
// 	$posts_pagination = str_replace( '</div>', '<span class="next page-numbers placeholder" aria-hidden="true">' . $next_text . '</span></div>', $posts_pagination );
// }

?>

<?php if ( $posts_pagination ) { ?>
	<div class="pagination-wrapper">
		<?php echo $posts_pagination; ?>
	</div>
<?php } ?>