<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Shortcode [n2n_news_feed].
 */
function n2n_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'posts'       => 6,
		'category'    => '',
		'tag'         => '',
		'layout'      => 'grid',
		'show_image'  => '1',
		'show_excerpt'=> '1',
		'new_tab'     => '1',
	), $atts, 'n2n_news_feed' );

	$args = array(
		'posts_to_show' => intval( $atts['posts'] ),
		'category_id'   => $atts['category'],
		'tag_id'        => $atts['tag'],
		'layout'        => $atts['layout'],
		'show_image'    => filter_var( $atts['show_image'], FILTER_VALIDATE_BOOLEAN ),
		'show_excerpt'  => filter_var( $atts['show_excerpt'], FILTER_VALIDATE_BOOLEAN ),
		'open_in_new_tab' => filter_var( $atts['new_tab'], FILTER_VALIDATE_BOOLEAN ),
	);

	return n2n_render_news_feed( $args );
}
add_shortcode( 'n2n_news_feed', 'n2n_shortcode' );
