<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor Widget: N2N News Feed
 */
class N2N_Widget_News_Feed extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 */
	public function get_name() {
		return 'n2n-news-feed';
	}

	/**
	 * Get widget title.
	 */
	public function get_title() {
		return esc_html__( 'Aggregated News Feed', 'n2n-aggregator' );
	}

	/**
	 * Get widget icon.
	 */
	public function get_icon() {
		return 'eicon-post-list';
	}

	/**
	 * Get widget categories.
	 */
	public function get_categories() {
		return [ 'n2n-widgets' ];
	}

	/**
	 * Helper: Get Taxonomy Options with Caching.
	 */
	protected function get_taxonomy_options( $taxonomy ) {
		// Static cache to prevent multiple DB calls in one request (e.g. valid for widget list)
		static $options_cache = [];

		if ( isset( $options_cache[ $taxonomy ] ) ) {
			return $options_cache[ $taxonomy ];
		}

		$options = [];
		$terms   = get_terms( [
			'taxonomy'   => $taxonomy,
			'hide_empty' => false, // Requested: false
		] );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->slug ] = $term->name;
			}
		}

		$options_cache[ $taxonomy ] = $options;
		return $options;
	}

	/**
	 * Register widget controls.
	 * Updated: using register_controls instead of _register_controls (deprecated)
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Content', 'n2n-aggregator' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		// Post Count
		$this->add_control(
			'posts_to_show',
			[
				'label'   => esc_html__( 'Posts to Show', 'n2n-aggregator' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 6,
				'min'     => 1,
				'max'     => 50,
				'step'    => 1,
			]
		);

		// Layout
		$this->add_control(
			'layout',
			[
				'label'   => esc_html__( 'Layout', 'n2n-aggregator' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => [
					'grid' => esc_html__( 'Grid', 'n2n-aggregator' ),
					'list' => esc_html__( 'List', 'n2n-aggregator' ),
				],
			]
		);

		// Grid Columns (Conditional)
		$this->add_control(
			'grid_columns',
			[
				'label'     => esc_html__( 'Grid Columns', 'n2n-aggregator' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 3,
				'options'   => [
					2 => esc_html__( '2 Columns', 'n2n-aggregator' ),
					3 => esc_html__( '3 Columns', 'n2n-aggregator' ),
					4 => esc_html__( '4 Columns', 'n2n-aggregator' ),
					5 => esc_html__( '5 Columns', 'n2n-aggregator' ),
					6 => esc_html__( '6 Columns', 'n2n-aggregator' ),
				],
				'condition' => [
					'layout' => 'grid',
				],
			]
		);

		// Category
		$this->add_control(
			'category',
			[
				'label'       => esc_html__( 'Filter by Category', 'n2n-aggregator' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'options'     => $this->get_taxonomy_options( 'category' ),
				'multiple'    => true, 
				'description' => esc_html__( 'Select categories to filter.', 'n2n-aggregator' ),
			]
		);

		// Tag
		$this->add_control(
			'tag',
			[
				'label'       => esc_html__( 'Filter by Tag', 'n2n-aggregator' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'options'     => $this->get_taxonomy_options( 'post_tag' ),
				'multiple'    => true,
				'description' => esc_html__( 'Select tags to filter.', 'n2n-aggregator' ),
			]
		);

		// Exclude Tags
		$this->add_control(
			'exclude_tags',
			[
				'label'       => esc_html__( 'Exclude Tags', 'n2n-aggregator' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'options'     => $this->get_taxonomy_options( 'post_tag' ),
				'multiple'    => true,
				'description' => esc_html__( 'Select tags to exclude from results.', 'n2n-aggregator' ),
			]
		);
		
		$this->add_control(
			'hr_options',
			[
				'type' => \Elementor\Controls_Manager::DIVIDER,
			]
		);

		// Display Options
		$this->add_control(
			'show_image',
			[
				'label'        => esc_html__( 'Show Image', 'n2n-aggregator' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'n2n-aggregator' ),
				'label_off'    => esc_html__( 'Hide', 'n2n-aggregator' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'show_excerpt',
			[
				'label'        => esc_html__( 'Show Summary', 'n2n-aggregator' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'n2n-aggregator' ),
				'label_off'    => esc_html__( 'Hide', 'n2n-aggregator' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);
		
		$this->add_control(
			'new_tab',
			[
				'label'        => esc_html__( 'Open in New Tab', 'n2n-aggregator' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'n2n-aggregator' ),
				'label_off'    => esc_html__( 'No', 'n2n-aggregator' ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'only_main_events_info',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => '<small>' . esc_html__( 'Note: Only "Main Event" items are shown in this widget.', 'n2n-aggregator' ) . '</small>',
				'content_classes' => 'elementor-descriptor',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on the frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		// Safe Read & Type Casting
		// Renderer expects 'posts_to_show' (int)
		$posts_to_show = isset( $settings['posts_to_show'] ) ? intval( $settings['posts_to_show'] ) : 6;
		
		// Map Elementor Switcher ('yes' / empty) to Boolean for Renderer
		// Renderer expects booleans for these fields
		$show_image   = isset( $settings['show_image'] ) && 'yes' === $settings['show_image'];
		$show_excerpt = isset( $settings['show_excerpt'] ) && 'yes' === $settings['show_excerpt'];
		$new_tab      = isset( $settings['new_tab'] ) && 'yes' === $settings['new_tab'];

		// Prepare Arguments for Renderer
		$args = array(
			'posts_to_show' => $posts_to_show, // Maps strictly to renderer expectation
			'category'      => isset( $settings['category'] ) ? $settings['category'] : '',
			'tag'           => isset( $settings['tag'] ) ? $settings['tag'] : '',
			'layout'        => isset( $settings['layout'] ) ? $settings['layout'] : 'grid',
			'show_image'    => $show_image,
			'show_excerpt'  => $show_excerpt,
			'new_tab'       => $new_tab,
			'is_event_main' => true, // Enforce Main Event logic
			'grid_columns'  => isset( $settings['grid_columns'] ) ? intval( $settings['grid_columns'] ) : 3,
			'exclude_tags'  => isset( $settings['exclude_tags'] ) ? $settings['exclude_tags'] : [],
		);

		// Use existing renderer function
		// No re-write, direct call.
		echo n2n_render_news_query( $args );
	}
}
