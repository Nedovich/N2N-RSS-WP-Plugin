<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register 'aggregated_news' Custom Post Type.
 * Strict: No editor, no thumbnail.
 */
function n2n_register_cpt_aggregated_news() {
	$labels = array(
		'name'                  => _x( 'Aggregated News', 'Post Type General Name', 'n2n-aggregator' ),
		'singular_name'         => _x( 'News', 'Post Type Singular Name', 'n2n-aggregator' ),
		'menu_name'             => __( 'Aggregated News', 'n2n-aggregator' ),
		'name_admin_bar'        => __( 'News Item', 'n2n-aggregator' ),
		'all_items'             => __( 'All News', 'n2n-aggregator' ),
		'add_new_item'          => __( 'Add New News Item', 'n2n-aggregator' ),
		'add_new'               => __( 'Add New', 'n2n-aggregator' ),
		'new_item'              => __( 'New Item', 'n2n-aggregator' ),
		'edit_item'             => __( 'Edit Item', 'n2n-aggregator' ),
		'update_item'           => __( 'Update Item', 'n2n-aggregator' ),
		'view_item'             => __( 'View Item', 'n2n-aggregator' ),
		'view_items'            => __( 'View Items', 'n2n-aggregator' ),
		'search_items'          => __( 'Search News', 'n2n-aggregator' ),
		'not_found'             => __( 'Not found', 'n2n-aggregator' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'n2n-aggregator' ),
	);

	$args = array(
		'label'                 => __( 'News', 'n2n-aggregator' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'excerpt', 'revisions' ), // STRICT: NO editor, NO thumbnail
		'taxonomies'            => array( 'category', 'post_tag' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'rewrite'               => array( 'slug' => 'news', 'with_front' => true ),
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
		'show_in_rest'          => true, // Required for n8n
	);

	register_post_type( 'aggregated_news', $args );
}
add_action( 'init', 'n2n_register_cpt_aggregated_news' );

/**
 * Custom Admin Columns.
 */
function n2n_aggregated_news_columns( $columns ) {
	$new_columns = array(
		'cb' => $columns['cb'],
		'title' => $columns['title'],
		'categories' => __( 'Categories', 'n2n-aggregator' ),
		'tags' => __( 'Tags', 'n2n-aggregator' ),
		'date' => $columns['date'],
	);
	return $new_columns;
}
add_filter( 'manage_aggregated_news_posts_columns', 'n2n_aggregated_news_columns' );

function n2n_aggregated_news_custom_column( $column, $post_id ) {
	switch ( $column ) {
		case 'categories':
			echo get_the_term_list( $post_id, 'category', '', ', ', '—' );
			break;
		case 'tags':
			echo get_the_term_list( $post_id, 'post_tag', '', ', ', '—' );
			break;
	}
}
add_action( 'manage_aggregated_news_posts_custom_column', 'n2n_aggregated_news_custom_column', 10, 2 );
