<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REDIRECT HEADER LOGIC ONLY.
 * Hook: template_redirect
 */
function n2n_handle_redirect_header() {

	// 0. Guard: Admin / Editor / REST
	if ( is_admin() ) {
		return;
	}

	// 1. Guard: Single Check
	if ( ! is_singular( 'aggregated_news' ) ) {
		return;
	}

	// 2. Guard: URL Check
	$url = get_post_meta( get_the_ID(), 'external_url', true );
	if ( empty( $url ) ) {
		return;
	}

	// 3. Logic: Redirect if Mode is Direct
	$mode = get_option( 'n2n_redirect_mode', 'direct' );

	if ( 'direct' === $mode ) {
		$status = get_option( 'n2n_redirect_status', 301 );
		wp_redirect( $url, $status );
		exit;
	}

	// Interstitial mode → DO NOTHING here
}
add_action( 'template_redirect', 'n2n_handle_redirect_header' );