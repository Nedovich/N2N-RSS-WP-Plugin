<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function n2n_news_shortcode( $atts ) {

	$atts = shortcode_atts(
		[
			'posts'    => 6,
			'category' => '',
			'tag'      => '',
			'layout'   => '',
		],
		$atts,
		'n2n_news'
	);

	$args = [
		'posts_per_page' => (int) $atts['posts'],
	];

	if ( ! empty( $atts['category'] ) ) {
		$args['category'] = sanitize_text_field( $atts['category'] );
	}

	if ( ! empty( $atts['tag'] ) ) {
		$args['tag'] = sanitize_text_field( $atts['tag'] );
	}

	if ( ! empty( $atts['layout'] ) ) {
		$args['layout'] = sanitize_text_field( $atts['layout'] );
	}

	return n2n_render_news_query( $args );
}

add_shortcode( 'n2n_news', 'n2n_news_shortcode' );