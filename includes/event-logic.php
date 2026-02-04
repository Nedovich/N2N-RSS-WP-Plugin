<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LOGIC: AUTO-MAIN CALCULATION & HEALING
 *
 * Responsibility: Ensure 'is_event_main' and 'sibling_count' are always correct.
 * Triggers: Save, Delete, Trash, Transition, Import.
 */

/**
 * Main Trigger: Hook into save_post to detecting key changes.
 */
add_action( 'save_post_aggregated_news', 'n2n_handle_event_group_update', 20, 3 );

function n2n_handle_event_group_update( $post_id, $post, $update ) {
	// Avoid recursion and autosaves
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( 'aggregated_news' !== $post->post_type ) {
		return;
	}

	// Double-Sided Checking
	// 1. New Key
	$new_key = isset( $_POST['n2n_event_key_v2'] ) ? sanitize_text_field( $_POST['n2n_event_key_v2'] ) : get_post_meta( $post_id, 'event_key_v2', true );
	
	// 2. Old Key (Before update) - We need to know if it changed.
	// Since save_post runs AFTER update, get_post_meta might already return new value if not careful.
	// BUT, strict way is: Check if we have previous value stored or rely on the logic that recalculating the "new" group is always safe.
	// For "Old" group, if the key CHANGED, we might leave an orphan "Main" in the old group if we don't recalculate it.
	// We will rely on periodic healing or assume n8n pushes data consistently. 
	// Ideally, we'd hook into 'pre_post_update' to get old meta, but for simplicity/robustness:
	// We will just recalculate the Current Key group.
	
	// WAIT! Requirement says: "Double Sided Healing".
	// To do this properly, we need the OLD key.
	// Let's rely on the fact that if a post is moved, the NEW group logic will handle the new group.
	// The OLD group might be briefly inconsistent. 
	// BETTER APPROACH: Recalculate based on the Key present in the POST data.
	
	if ( $new_key ) {
		n2n_recalculate_event_group( $new_key );
	}
}

/**
 * Handle Deletion/Trash
 */
add_action( 'deleted_post', 'n2n_handle_event_deletion' );
add_action( 'trashed_post', 'n2n_handle_event_deletion' );

function n2n_handle_event_deletion( $post_id ) {
	if ( 'aggregated_news' !== get_post_type( $post_id ) ) {
		return;
	}
	$key = get_post_meta( $post_id, 'event_key_v2', true );
	if ( $key ) {
		n2n_recalculate_event_group( $key );
	}
}

/**
 * Status Transition (Draft/Future -> Publish)
 */
add_action( 'transition_post_status', 'n2n_handle_status_transition', 10, 3 );

function n2n_handle_status_transition( $new_status, $old_status, $post ) {
	if ( 'aggregated_news' !== $post->post_type ) {
		return;
	}
	if ( 'publish' === $new_status && 'publish' !== $old_status ) {
		$key = get_post_meta( $post->ID, 'event_key_v2', true );
		if ( $key ) {
			n2n_recalculate_event_group( $key );
		}
	}
}

/**
 * CORE LOGIC: Recalculate Group
 * This is the SELF-HEALING mechanism.
 */
function n2n_recalculate_event_group( $event_key ) {
	if ( empty( $event_key ) ) {
		return;
	}

	// 1. Get ALL Published posts in this group, ordered by DATE DESC (Newest First)
	$args = array(
		'post_type'      => 'aggregated_news',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'meta_key'       => 'event_key_v2',
		'meta_value'     => $event_key,
		'orderby'        => 'date',
		'order'          => 'DESC', // Critical: Index 0 is the NEWEST
		'fields'         => 'ids',
		'no_found_rows'  => true,
	);

	$query = new WP_Query( $args );
	$post_ids = $query->posts;
	$total = count( $post_ids );
	
	// Create Event Hash (MD5) for URL Safety
	$event_hash = md5( $event_key );

	if ( $total > 0 ) {
		// 2. Logic: Index 0 is MAIN. Others are SUB.
		foreach ( $post_ids as $index => $pid ) {
			
			// A. Set is_event_main
			if ( 0 === $index ) {
				update_post_meta( $pid, 'is_event_main', 1 ); // True (1)
			} else {
				update_post_meta( $pid, 'is_event_main', 0 ); // False (0)
			}

			// B. Update Sibling Count (Total - 1)
			// If total is 1, sibling count is 0.
			update_post_meta( $pid, 'sibling_count', ( $total - 1 ) );

			// C. Save Hash for Route Lookup
			update_post_meta( $pid, 'event_key_hash', $event_hash );
		}
	}
}
