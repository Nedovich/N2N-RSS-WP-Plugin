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

// Include core functionality
require_once N2N_AGGREGATOR_PATH . 'includes/post-type.php';
require_once N2N_AGGREGATOR_PATH . 'includes/meta.php';
require_once N2N_AGGREGATOR_PATH . 'includes/admin-metaboxes.php';
require_once N2N_AGGREGATOR_PATH . 'includes/settings.php';
require_once N2N_AGGREGATOR_PATH . 'includes/redirect-interstitial.php';
require_once N2N_AGGREGATOR_PATH . 'includes/renderer.php';
require_once N2N_AGGREGATOR_PATH . 'includes/blocks.php';
require_once N2N_AGGREGATOR_PATH . 'includes/shortcode.php';

// Activation hook to flush rewrite rules
register_activation_hook( __FILE__, 'n2n_aggregator_activate' );

function n2n_aggregator_activate() {
	// Trigger CPT registration
	n2n_register_cpt_aggregated_news();
	
	// Flush rewrite rules
	flush_rewrite_rules();
}

// Deactivation hook
register_deactivation_hook( __FILE__, 'n2n_aggregator_deactivate' );

function n2n_aggregator_deactivate() {
	flush_rewrite_rules();
}
