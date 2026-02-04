<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TEMPLATE LOADER
 * Route 'n2n_event_hash' requests to custom template.
 */

/**
 * 1. Force Singular Flags (Pre-Query)
 * Trick themes into thinking this is a single page, not an archive.
 */
add_action( 'pre_get_posts', 'n2n_force_event_query_flags' );

function n2n_force_event_query_flags( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( get_query_var( 'n2n_event_hash' ) ) {
		$query->set( 'is_archive', false );
		$query->set( 'is_home', false );
		$query->set( 'is_404', false );
		$query->set( 'is_singular', true ); // Critical for theme interception
		
		// Prevent WP from doing a huge query we don't need
		$query->set( 'post_type', 'aggregated_news' ); 
		$query->set( 'posts_per_page', 1 ); 
	}
}

/**
 * 2. Template Interception
 * Priority 99: Run late to override theme logic.
 */
add_filter( 'template_include', 'n2n_template_loader', 99 );

function n2n_template_loader( $template ) {
	
	// Check if this is an N2N Event request
	if ( get_query_var( 'n2n_event_hash' ) ) {
		
		// Look for template in plugin directory
		$plugin_template = N2N_AGGREGATOR_PATH . 'templates/single-event-feed.php';
		
		if ( file_exists( $plugin_template ) ) {
			return $plugin_template;
		}
	}

	return $template;
}
