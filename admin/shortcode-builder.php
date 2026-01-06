<?php
/**
 * Admin Logic: Preview & Shortcode Builder
 * Handles Registration, Assets, and AJAX.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 1. Register Submenu Page
 * CPT Slug: aggregated_news
 */
function n2n_register_builder_page() {
	add_submenu_page(
		'edit.php?post_type=aggregated_news', // Parend: CPT Menu
		'Shortcode Builder',                  // Page Title
		'Shortcode Builder',                  // Menu Title
		'manage_options',                     // Capability
		'n2n-shortcode-builder',              // Slug
		'n2n_render_shortcode_builder'        // Callback
	);
}
add_action( 'admin_menu', 'n2n_register_builder_page' );

/**
 * 2. Render Callback
 * Loads View File.
 */
function n2n_render_shortcode_builder() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	
	require plugin_dir_path( __FILE__ ) . 'shortcode-builder-view.php';
}

/**
 * 3. Enqueue Assets
 * Loads JS/CSS only on builder page.
 */
function n2n_builder_assets( $hook ) {
	// Hook is derived from: aggregated_news (CPT) + _page_ + n2n-shortcode-builder (Slug)
	if ( 'aggregated_news_page_n2n-shortcode-builder' !== $hook ) {
		return;
	}

	wp_enqueue_style(
		'n2n-builder-css',
		N2N_AGGREGATOR_URL . 'admin/css/shortcode-builder.css'
	);
	
	wp_enqueue_script(
		'n2n-builder-js',
		N2N_AGGREGATOR_URL . 'admin/js/shortcode-builder.js',
		array('jquery'),
		false,
		true
	);

	// Enqueue Frontend Styles for authentic preview
	wp_enqueue_style( 'n2n-frontend-css', N2N_AGGREGATOR_URL . 'assets/style.css' );

	wp_localize_script( 'n2n-builder-js', 'n2n_ajax', array(
		'url'   => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( 'n2n_preview_nonce' )
	) );
}
add_action( 'admin_enqueue_scripts', 'n2n_builder_assets' );

/**
 * 4. AJAX Handler
 * Reuses existing renderer.
 */
function n2n_ajax_preview_handler() {
	check_ajax_referer( 'n2n_preview_nonce', 'nonce' );

	// Build Args from POST
	$args = array(
		'posts_to_show'  => intval( $_POST['posts'] ),
		'category'       => sanitize_text_field( $_POST['category'] ), // Now passes slug string
		'tag'            => sanitize_text_field( $_POST['tag'] ),      // New: Tag slug
		'layout'         => sanitize_text_field( $_POST['layout'] ),
		'orderby'        => sanitize_text_field( $_POST['orderby'] ),
		'excerpt_length' => intval( $_POST['excerpt_length'] ),
		'show_image'     => 'true' === $_POST['show_image'],
		'show_excerpt'   => true,
		'new_tab'        => true,
	);

	// REUSE Renderer
	echo n2n_render_news_query( $args );
	wp_die();
}
add_action( 'wp_ajax_n2n_preview_news', 'n2n_ajax_preview_handler' );
