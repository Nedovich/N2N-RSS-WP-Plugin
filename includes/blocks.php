<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Block.
 */
function n2n_register_block() {
	wp_register_script(
		'n2n-block-js',
		N2N_AGGREGATOR_URL . 'assets/block.js',
		array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-server-side-render' ),
		filemtime( N2N_AGGREGATOR_PATH . 'assets/block.js' )
	);

	register_block_type( 'n2n-aggregator/news-feed', array(
		'editor_script' => 'n2n-block-js',
		'render_callback' => 'n2n_block_render_callback',
		'attributes' => array(
			'postsToShow' => array( 'type' => 'number', 'default' => 6 ),
			'categoryId'  => array( 'type' => 'string', 'default' => '' ),
			'tagId'       => array( 'type' => 'string', 'default' => '' ),
			'layout'      => array( 'type' => 'string', 'default' => 'grid' ),
			'showImage'   => array( 'type' => 'boolean', 'default' => true ),
			'showExcerpt' => array( 'type' => 'boolean', 'default' => true ),
			'openInNewTab'=> array( 'type' => 'boolean', 'default' => true ),
		)
	) );
}
add_action( 'init', 'n2n_register_block' );

/**
 * Block Render Callback.
 * Maps attributes to Renderer args.
 */
function n2n_block_render_callback( $attributes ) {
	$args = array(
		'posts_to_show' => isset($attributes['postsToShow']) ? $attributes['postsToShow'] : 6,
		'category_id'   => isset($attributes['categoryId']) ? $attributes['categoryId'] : '',
		'tag_id'        => isset($attributes['tagId']) ? $attributes['tagId'] : '',
		'layout'        => isset($attributes['layout']) ? $attributes['layout'] : 'grid',
		'show_image'    => isset($attributes['showImage']) ? $attributes['showImage'] : true,
		'show_excerpt'  => isset($attributes['showExcerpt']) ? $attributes['showExcerpt'] : true,
		'new_tab'       => isset($attributes['openInNewTab']) ? $attributes['openInNewTab'] : true,
	);

	return n2n_render_news_query( $args );
}

/**
 * Enqueue Styles.
 */
function n2n_enqueue_assets() {
	wp_enqueue_style(
		'n2n-style',
		N2N_AGGREGATOR_URL . 'assets/style.css',
		array(),
		filemtime( N2N_AGGREGATOR_PATH . 'assets/style.css' )
	);
}
add_action( 'wp_enqueue_scripts', 'n2n_enqueue_assets' );
add_action( 'enqueue_block_editor_assets', 'n2n_enqueue_assets' );
