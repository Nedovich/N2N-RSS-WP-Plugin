<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register post meta fields for 'aggregated_news' to expose them in REST API.
 */
function n2n_register_aggregated_news_meta() {
	
	// External Article URL
	register_post_meta( 'aggregated_news', 'external_url', array(
		'type'              => 'string',
		'description'       => 'URL to the original article.',
		'single'            => true,
		'sanitize_callback' => 'esc_url_raw',
		'show_in_rest'      => true,
	) );

	// External Image URL (Hotlink)
	register_post_meta( 'aggregated_news', 'external_image_url', array(
		'type'              => 'string',
		'description'       => 'URL to the external image.',
		'single'            => true,
		'sanitize_callback' => 'esc_url_raw',
		'show_in_rest'      => true,
	) );
}
add_action( 'init', 'n2n_register_aggregated_news_meta' );
