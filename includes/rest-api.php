<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Extensions for N2N Aggregator.
 * 
 * Handles "Upsert" logic:
 * - If a post with the same 'external_url' exists, update it.
 * - Otherwise, create a new one.
 */

// Hook into the PRE-insert stage of the REST API controller
add_filter( 'rest_pre_insert_aggregated_news', 'n2n_rest_pre_insert_logic', 10, 2 );

function n2n_rest_pre_insert_logic( $prepared_post, $request ) {
	// Only run this check if we are creating a new post (no ID in request)
	// OR if we want to ensure we don't accidentally duplicate even if ID is missing.
	// Typically, n8n sends a POST to /wp/v2/aggregated_news without an ID for creation.
	
	$external_url = $request->get_param( 'external_url' );

	// Fallback: Check inside 'meta' object (standard WP REST behavior)
	if ( empty( $external_url ) ) {
		$meta_params = $request->get_param( 'meta' );
		if ( ! empty( $meta_params['external_url'] ) ) {
			$external_url = $meta_params['external_url'];
		}
	}

	// Logic removed from here - moved to after-insert


	if ( empty( $external_url ) ) {
		return $prepared_post;
	}

	// Check if a post with this external_url already exists
	$existing_posts = get_posts( array(
		'post_type'   => 'aggregated_news',
		'post_status' => 'any',
		'numberposts' => 1,
		'meta_key'    => 'external_url',
		'meta_value'  => $external_url,
		'fields'      => 'ids',
	) );

	if ( ! empty( $existing_posts ) ) {
		// Post exists! Set the ID so WordPress updates it instead of creating a new one.
		$prepared_post->ID = $existing_posts[0];
	}

	return $prepared_post;
}

/**
 * Register Top-Level REST Fields (Aliases to Meta).
 * This supports flat JSON payloads as requested:
 * { "event_key_v2": "..." } instead of { "meta": { "event_key_v2": "..." } }
 */
add_action( 'rest_api_init', 'n2n_register_rest_fields' );
// Hook for After Insert Logic (Auto-Main + Auto-Excerpt)
add_action( 'rest_after_insert_aggregated_news', 'n2n_after_insert_logic', 10, 2 );

function n2n_after_insert_logic( $post, $request ) {
	$post_id = $post->ID;

	// --- 1. Auto-Excerpt Logic ---
	// If the inserted post has empty excerpt, try to generate it from content
	if ( empty( $post->post_excerpt ) && ! empty( $post->post_content ) ) {
		$content_stripped = strip_tags( $post->post_content );
		$excerpt = wp_trim_words( $content_stripped, 55, '...' );
		
		// Update the post with new excerpt
		wp_update_post( array(
			'ID'           => $post_id,
			'post_excerpt' => $excerpt
		) );
	}

	// --- 1.5 Auto-Short-Summary Logic (Custom Field) ---
	$existing_summary = get_post_meta( $post_id, 'n2n_short_summary', true );
	if ( empty( $existing_summary ) && ! empty( $post->post_content ) ) {
		$content_stripped = strip_tags( $post->post_content );
		// Rigid 50 char limit
		$summary = mb_substr( $content_stripped, 0, 50 );
		update_post_meta( $post_id, 'n2n_short_summary', $summary );
	}

	// --- 2. Auto-Main Logic ---
	$event_key = get_post_meta( $post_id, 'event_key_v2', true );

	if ( empty( $event_key ) ) {
		return;
	}

	// 1. Find all posts with this event_key
	$args = array(
		'post_type'      => 'aggregated_news',
		'posts_per_page' => -1,
		'meta_key'       => 'event_key_v2',
		'meta_value'     => $event_key,
		'fields'         => 'ids',
		// Order by date DESC so the newest one (this one) is logically first
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	$related_ids = get_posts( $args );

	if ( empty( $related_ids ) ) {
		return;
	}

	// 2. Determine MAIN event (Latest one -> The one we just inserted/updated)
	// Since we are running AFTER insert, the current post is the latest.
	// We force the current post to be MAIN.
	update_post_meta( $post_id, 'is_event_main', true );

	// 3. Mark all OTHERS as NOT Main
	foreach ( $related_ids as $id ) {
		if ( $id != $post_id ) {
			update_post_meta( $id, 'is_event_main', false );
		}
	}
}

add_action( 'rest_api_init', 'n2n_register_rest_fields' );
function n2n_register_rest_fields() {
	
	// 1. Simple Fields (Direct Meta Map)
	$simple_fields = [ 
		'external_url', 
		'external_image_url', 
		'event_key_v2', 
		'event_date', 
		'article_count', 
		'article_count', 
		'main_publish_date',
		'n2n_short_summary'
	];

	foreach ( $simple_fields as $field ) {
		register_rest_field( 'aggregated_news', $field, array(
			'get_callback'    => function( $object ) use ( $field ) {
				return get_post_meta( $object['id'], $field, true );
			},
			'update_callback' => function( $value, $object ) use ( $field ) {
				return update_post_meta( $object->ID, $field, $value );
			},
			'schema'          => null,
		) );
	}

	// 2. Read-Only Field (Managed by Auto-Main logic)
	register_rest_field( 'aggregated_news', 'is_event_main', array(
		'get_callback'    => function( $object ) {
			return get_post_meta( $object['id'], 'is_event_main', true );
		},
		'update_callback' => null, // Read-only via REST input
		'schema'          => null,
	) );

	// 3. Tags (Custom Logic: Create/Assign)
	register_rest_field( 'aggregated_news', 'tags', array(
		'update_callback' => function( $value, $object ) {
			if ( ! is_array( $value ) ) {
				return;
			}
			// Array of Strings -> Set Post Tags (creates if not exists)
			wp_set_object_terms( $object->ID, $value, 'post_tag', false );
		},
		'schema' => array(
			'type'  => 'array',
			'items' => array( 'type' => 'string' ),
		),
	) );

	// 4. Categories (Custom Logic: Match Only)
	register_rest_field( 'aggregated_news', 'categories', array(
		'update_callback' => function( $value, $object ) {
			if ( ! is_array( $value ) ) {
				return;
			}

			$valid_ids = array();
			foreach ( $value as $cat_identity ) {
				// Check by Slug first, then Name
				$term = get_term_by( 'slug', $cat_identity, 'category' );
				if ( ! $term ) {
					$term = get_term_by( 'name', $cat_identity, 'category' );
				}

				if ( $term ) {
					$valid_ids[] = (int) $term->term_id;
				}
			}

			// If valid categories found, set them.
			// If empty, WP defaults to Uncategorized automatically or we can force it.
			// wp_set_object_terms with 'false' replaces existing terms.
			if ( ! empty( $valid_ids ) ) {
				wp_set_object_terms( $object->ID, $valid_ids, 'category', false );
			}
		},
		'schema' => array(
			'type'  => 'array',
			'items' => array( 'type' => 'string' ),
		),
	) );
}
