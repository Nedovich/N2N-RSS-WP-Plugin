<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the 'aggregated_news' custom post type.
 */
function n2n_register_cpt_aggregated_news() {
	$labels = array(
		'name'                  => _x( 'Aggregated News', 'Post Type General Name', 'n2n-aggregator' ),
		'singular_name'         => _x( 'News', 'Post Type Singular Name', 'n2n-aggregator' ),
		'menu_name'             => __( 'Aggregated News', 'n2n-aggregator' ),
		'name_admin_bar'        => __( 'News Item', 'n2n-aggregator' ),
		'archives'              => __( 'News Archives', 'n2n-aggregator' ),
		'attributes'            => __( 'Item Attributes', 'n2n-aggregator' ),
		'parent_item_colon'     => __( 'Parent Item:', 'n2n-aggregator' ),
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
		'featured_image'        => __( 'Featured Image', 'n2n-aggregator' ),
		'set_featured_image'    => __( 'Set featured image', 'n2n-aggregator' ),
		'remove_featured_image' => __( 'Remove featured image', 'n2n-aggregator' ),
		'use_featured_image'    => __( 'Use as featured image', 'n2n-aggregator' ),
		'insert_into_item'      => __( 'Insert into item', 'n2n-aggregator' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'n2n-aggregator' ),
		'items_list'            => __( 'Items list', 'n2n-aggregator' ),
		'items_list_navigation' => __( 'Items list navigation', 'n2n-aggregator' ),
		'filter_items_list'     => __( 'Filter items list', 'n2n-aggregator' ),
	);
	
	$args = array(
		'label'                 => __( 'News', 'n2n-aggregator' ),
		'description'           => __( 'Aggregated news items.', 'n2n-aggregator' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'excerpt' ), // No editor, no thumbnail to avoid confusion (per requirements)
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
		'show_in_rest'          => true, // Essential for n8n
	);
	
	register_post_type( 'aggregated_news', $args );
}
add_action( 'init', 'n2n_register_cpt_aggregated_news' );

/**
 * Add custom columns to the admin list screen.
 */
function n2n_set_custom_edit_aggregated_news_columns( $columns ) {
	// Remove default date and add it back at the end if you want specific order, 
	// or just modify existing.
	// We want: Title, Categories, Tags, Date.
	
	// Default columns: cb, title, date. We'll add taxonomies.
	$new_columns = array(
		'cb' => $columns['cb'],
		'title' => $columns['title'],
		'categories' => __( 'Categories', 'n2n-aggregator' ),
		'tags' => __( 'Tags', 'n2n-aggregator' ),
		'date' => $columns['date'],
	);
	return $new_columns;
}
add_filter( 'manage_aggregated_news_posts_columns', 'n2n_set_custom_edit_aggregated_news_columns' );

/**
 * Render custom columns content.
 */
function n2n_custom_aggregated_news_column( $column, $post_id ) {
	switch ( $column ) {
		case 'categories' :
			$terms = get_the_term_list( $post_id, 'category', '', ',', '' );
			if ( is_string( $terms ) ) {
				echo $terms;
			} else {
				// Fallback or empty
				echo '—';
			}
			break;

		case 'tags' :
			$terms = get_the_term_list( $post_id, 'post_tag', '', ',', '' );
			if ( is_string( $terms ) ) {
				echo $terms;
			} else {
				echo '—';
			}
			break;
	}
}
add_action( 'manage_aggregated_news_posts_custom_column' , 'n2n_custom_aggregated_news_column', 10, 2 );
