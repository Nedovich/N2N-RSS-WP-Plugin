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
		'posts_to_show' => (int) $atts['posts'],
		'is_event_main' => true, // STRICT: Only show main events in main list
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

/**
 * Shortcode: [antigravity_event_related]
 * Shows related news for the same event (sub-stories).
 */
function n2n_event_related_shortcode( $atts ) {
	// Only works inside singular post loop
	if ( ! is_singular( 'aggregated_news' ) ) {
		return '';
	}

	$post_id = get_the_ID();
	$event_key = get_post_meta( $post_id, 'event_key_v2', true );

	// If no event key, this isn't part of an event group
	if ( empty( $event_key ) ) {
		return '';
	}

	$args = [
		'event_key'       => $event_key,
		'is_event_main'   => false, // Only show sub-stories
		'exclude_post_id' => $post_id, // Don't show self
		'posts_to_show'   => 10, // Reasonable default
		'layout'          => 'list', // Default to list for related
	];

	// Allow overrides if needed in future, but for now strict per requirements
	
	// Add title if needed? User didn't specify UI details, so just returning list.
	return n2n_render_news_query( $args );
}
add_shortcode( 'antigravity_event_related', 'n2n_event_related_shortcode' );