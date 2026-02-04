<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REWRITE RULES
 * URL Structure: site.com/event/{event_hash}/
 */

/**
 * 1. Register Query Var
 */
add_filter( 'query_vars', 'n2n_register_query_vars' );
function n2n_register_query_vars( $vars ) {
	$vars[] = 'n2n_event_hash';
	return $vars;
}

/**
 * 2. Add Rewrite Rule
 * Regex: ^event/([a-f0-9]{32})/?
 * Matches 32-char MD5 hex string.
 */
add_action( 'init', 'n2n_add_rewrite_rules' );
function n2n_add_rewrite_rules() {
	add_rewrite_rule(
		'^event/([a-f0-9]{32})/?$',
		'index.php?n2n_event_hash=$matches[1]',
		'top'
	);
	
	// Flush rules if needed (dev only ideally, but safeguards installation)
	// We will call flush_rewrite_rules() on activation in main file.
}
