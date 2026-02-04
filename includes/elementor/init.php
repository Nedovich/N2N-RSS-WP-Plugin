<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor Integration Bootstrap
 */
function n2n_elementor_init() {

	// Check if Elementor is installed and active
	if ( ! did_action( 'elementor/loaded' ) ) {
		return;
	}

	// 1. Register Widget Categories
	add_action( 'elementor/elements/categories_registered', 'n2n_elementor_add_category' );

	// 2. Register Widgets
	add_action( 'elementor/widgets/register', 'n2n_elementor_register_widgets' );
}
add_action( 'plugins_loaded', 'n2n_elementor_init' );

/**
 * Register N2N Widget Category
 */
function n2n_elementor_add_category( $elements_manager ) {
	$elements_manager->add_category(
		'n2n-widgets',
		[
			'title' => esc_html__( 'N2N Aggregator', 'n2n-aggregator' ),
			'icon'  => 'fa fa-plug',
		]
	);
}

/**
 * Register N2N Widgets
 */
function n2n_elementor_register_widgets( $widgets_manager ) {
	require_once __DIR__ . '/widgets/news-widget.php';
	
	$widgets_manager->register( new \N2N_Widget_News_Feed() );
}
