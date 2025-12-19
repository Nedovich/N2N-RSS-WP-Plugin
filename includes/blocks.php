<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Gutenberg Block.
 */
function n2n_register_block() {
	// Register the editor script
	wp_register_script(
		'n2n-aggregator-block-editor',
		N2N_AGGREGATOR_URL . 'assets/block.js',
		array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-server-side-render' ),
		filemtime( N2N_AGGREGATOR_PATH . 'assets/block.js' )
	);

	// Register the block
	register_block_type( 'n2n-aggregator/news-feed', array(
		'editor_script' => 'n2n-aggregator-block-editor',
		'render_callback' => 'n2n_render_block',
		'attributes' => array(
			'layout' => array( 'type' => 'string', 'default' => 'grid' ),
			'postsToShow' => array( 'type' => 'number', 'default' => 6 ),
			'categoryId' => array( 'type' => 'string', 'default' => '' ),
			'tagId' => array( 'type' => 'string', 'default' => '' ),
			'showImage' => array( 'type' => 'boolean', 'default' => true ),
			'showExcerpt' => array( 'type' => 'boolean', 'default' => true ),
			'openInNewTab' => array( 'type' => 'boolean', 'default' => true ),
		)
	) );
}
add_action( 'init', 'n2n_register_block' );

/**
 * Block Render Callback.
 * Maps block attributes to n2n_render_news_feed args (snake_case vs camelCase).
 */
function n2n_render_block( $attributes ) {
	$args = array(
		'posts_to_show' => isset( $attributes['postsToShow'] ) ? $attributes['postsToShow'] : 6,
		'category_id'   => isset( $attributes['categoryId'] ) ? $attributes['categoryId'] : '',
		'tag_id'        => isset( $attributes['tagId'] ) ? $attributes['tagId'] : '',
		'layout'        => isset( $attributes['layout'] ) ? $attributes['layout'] : 'grid',
		'show_image'    => isset( $attributes['showImage'] ) ? $attributes['showImage'] : true,
		'show_excerpt'  => isset( $attributes['showExcerpt'] ) ? $attributes['showExcerpt'] : true,
		'open_in_new_tab' => isset( $attributes['openInNewTab'] ) ? $attributes['openInNewTab'] : true,
	);
	return n2n_render_news_feed( $args );
}

/**
 * Enqueue frontend styles.
 */
function n2n_enqueue_frontend_assets() {
	wp_enqueue_style(
		'n2n-aggregator-style',
		N2N_AGGREGATOR_URL . 'assets/style.css',
		array(),
		filemtime( N2N_AGGREGATOR_PATH . 'assets/style.css' )
	);
}
add_action( 'wp_enqueue_scripts', 'n2n_enqueue_frontend_assets' );
// Enqueue in editor as well for accurate preview
add_action( 'enqueue_block_editor_assets', 'n2n_enqueue_frontend_assets' );
