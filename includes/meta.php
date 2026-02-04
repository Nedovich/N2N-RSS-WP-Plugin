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

	// 3. Event Key (v2) - Identifies the event group
	register_post_meta( 'aggregated_news', 'event_key_v2', array(
		'type'              => 'string',
		'description'       => 'Unique key for the event group',
		'single'            => true,
		'sanitize_callback' => 'sanitize_text_field',
		'show_in_rest'      => true,
	) );

	// 4. Event Date
	register_post_meta( 'aggregated_news', 'event_date', array(
		'type'              => 'string', // Storing as string YYYY-MM-DD for simplicity
		'description'       => 'Date of the event',
		'single'            => true,
		'sanitize_callback' => 'sanitize_text_field',
		'show_in_rest'      => true,
	) );

	// 5. Article Count
	register_post_meta( 'aggregated_news', 'article_count', array(
		'type'              => 'integer',
		'description'       => 'Number of articles in this event',
		'single'            => true,
		'sanitize_callback' => 'absint',
		'show_in_rest'      => true,
	) );

	// 6. Article Role (Is Event Main?)
	register_post_meta( 'aggregated_news', 'is_event_main', array(
		'type'              => 'boolean',
		'description'       => 'Is this the main article for the event?',
		'single'            => true,
		'sanitize_callback' => 'rest_sanitize_boolean',
		'show_in_rest'      => true,
		'default'           => false, 
	) );
	
	// 7. Main Publish Date
	register_post_meta( 'aggregated_news', 'main_publish_date', array(
		'type'              => 'string',
		'description'       => 'Original publish date from source',
		'single'            => true,
		'sanitize_callback' => 'sanitize_text_field',
		'show_in_rest'      => true,
	) );
}
add_action( 'init', 'n2n_register_meta' );
