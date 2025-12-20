<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Meta Fields for REST API (n8n).
 */
function n2n_register_meta() {
	
	// 1. External Article URL
	register_post_meta( 'aggregated_news', 'external_url', array(
		'type'              => 'string',
		'description'       => 'Original article URL',
		'single'            => true,
		'sanitize_callback' => 'esc_url_raw',
		'show_in_rest'      => true,
	) );

	// 2. External Image URL (Hotlink)
	register_post_meta( 'aggregated_news', 'external_image_url', array(
		'type'              => 'string',
		'description'       => 'Hotlinked image URL',
		'single'            => true,
		'sanitize_callback' => 'esc_url_raw',
		'show_in_rest'      => true,
	) );
}
add_action( 'init', 'n2n_register_meta' );
