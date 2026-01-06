<?php
/**
 * Plugin Name: N2N Aggregator
 * Description: News Aggregator backend for RSS data pushed from n8n.
 * Version: 1.0.0
 * Author: Nedim Esken
 * Text Domain: n2n-aggregator
 * Requires at least: 6.0
 * Tested up to: 6.7
 * License: GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'N2N_AGGREGATOR_VERSION', '1.0.0' );
define( 'N2N_AGGREGATOR_PATH', plugin_dir_path( __FILE__ ) );
define( 'N2N_AGGREGATOR_URL', plugin_dir_url( __FILE__ ) );

// Core Includes
require_once N2N_AGGREGATOR_PATH . 'includes/post-type.php';
require_once N2N_AGGREGATOR_PATH . 'includes/meta.php';
require_once N2N_AGGREGATOR_PATH . 'includes/admin-metaboxes.php';
require_once N2N_AGGREGATOR_PATH . 'includes/settings.php';
require_once N2N_AGGREGATOR_PATH . 'includes/renderer.php';
require_once N2N_AGGREGATOR_PATH . 'includes/redirect.php';
require_once N2N_AGGREGATOR_PATH . 'includes/blocks.php';
require_once N2N_AGGREGATOR_PATH . 'includes/shortcode.php';
require_once N2N_AGGREGATOR_PATH . 'admin/shortcode-builder.php';

// Activation
register_activation_hook( __FILE__, 'n2n_aggregator_activate' );
function n2n_aggregator_activate() {
	n2n_register_cpt_aggregated_news();
	flush_rewrite_rules();
}

// Deactivation
register_deactivation_hook( __FILE__, 'n2n_aggregator_deactivate' );
function n2n_aggregator_deactivate() {
	flush_rewrite_rules();
}

/**
 * INTERSTITIAL (Ad Page) LOGIC
 * Hook: the_content
 * Purpose: Replace content with interstitial if mode=interstitial
 * Location: Main file to allow Renderer to be pure View and Redirect to be pure Logic.
 */
function n2n_interstitial_filter_logic( $content ) {
	// 1. Guard Clauses (Logic)
	if ( ! is_singular( 'aggregated_news' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$mode = get_option( 'n2n_redirect_mode', 'direct' );
	if ( 'interstitial' !== $mode ) {
		return $content;
	}

	$url = get_post_meta( get_the_ID(), 'external_url', true );
	if ( empty( $url ) ) {
		return $content;
	}

	// 2. Prepare Data
	$image = get_post_meta( get_the_ID(), 'external_image_url', true );
	$countdown = get_option( 'n2n_countdown', 0 );

	// 3. Delegate to Renderer (View)
	return n2n_get_interstitial_html( $url, $image, $countdown );
}
add_filter( 'the_content', 'n2n_interstitial_filter_logic' );
