<?php

function content_client_story_s_filter_crown_localized_post_url( $url, $post_id ) {
	$current_site_id = get_current_blog_id();
	restore_current_blog();
	$syn_id = get_posts( array(
		'post_type' => 'any',
		'posts_per_page' => 1,
		'fields' => 'ids',
		'post_status' => 'any',
		'meta_query' => array(
			array( 'key' => '_original_site_id', 'value' => $current_site_id ),
			array( 'key' => '_original_post_id', 'value' => $post_id )
		)
	) );
	if ( ! empty( $syn_id ) ) $url = get_permalink( $syn_id[0] );
	switch_to_blog( $current_site_id );
	return $url;
}
add_filter( 'crown_localized_post_url', 'content_client_story_s_filter_crown_localized_post_url', 100, 2 );

function content_client_story_s_filter_crown_localized_index_url( $url, $option_key ) {
	$current_site_id = get_current_blog_id();
	restore_current_blog();
	if ( ( $index_page_id = get_option( $option_key ) ) && ( $index_page = get_post( $index_page_id ) ) ) {
		$url = get_permalink( $index_page_id );
	}
	switch_to_blog( $current_site_id );
	return $url;
}
add_filter( 'crown_localized_index_url', 'content_client_story_s_filter_crown_localized_index_url', 100, 2 );

function content_client_story_s_filter_crown_localized_index_blocks( $blocks, $option_key ) {
	$current_site_id = get_current_blog_id();
	restore_current_blog();
	if ( ( $index_page_id = get_option( $option_key ) ) && ( $index_page = get_post( $index_page_id ) ) ) {
		$blocks = parse_blocks( $index_page->post_content );
	}
	switch_to_blog( $current_site_id );
	return $blocks;
}
add_filter( 'crown_localized_index_blocks', 'content_client_story_s_filter_crown_localized_index_blocks', 100, 2 );


$original_post_id = get_post_meta( get_the_ID(), '_original_post_id', true );
switch_to_blog( get_post_meta( get_the_ID(), '_original_site_id', true ) );
$post = get_post( $original_post_id );
setup_postdata( $post );

get_template_part( 'template-parts/content', 'client_story' );

restore_current_blog();
wp_reset_postdata();


remove_filter( 'crown_localized_post_url', 'content_client_story_s_filter_crown_localized_post_url' );
remove_filter( 'crown_resource_index_url', 'content_client_story_s_filter_crown_localized_index_url' );
remove_filter( 'crown_localized_index_blocks', 'content_client_story_s_filter_crown_localized_index_blocks' );