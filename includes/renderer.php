<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render News Feed.
 * Used by Shortcode and Block.
 *
 * @param array $args Attributes.
 * @return string HTML output.
 */
function n2n_render_news_feed( $user_args = array() ) {
	// Defaults
	$defaults = array(
		'posts_to_show' => 6,
		'category_id'   => '',
		'tag_id'        => '',
		'layout'        => 'list', // list | grid
		'show_image'    => true,
		'show_excerpt'  => true,
		'open_in_new_tab' => true,
	);

	// Parse Args
	$args = wp_parse_args( $user_args, $defaults );

	// Build Query
	$query_args = array(
		'post_type'      => 'aggregated_news',
		'posts_per_page' => intval( $args['posts_to_show'] ),
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	// Filtering
	if ( ! empty( $args['category_id'] ) ) {
		$query_args['cat'] = intval( $args['category_id'] );
	}
	if ( ! empty( $args['tag_id'] ) ) {
		$query_args['tag_id'] = intval( $args['tag_id'] );
	}

	$query = new WP_Query( $query_args );

	if ( ! $query->have_posts() ) {
		return ''; // No posts, return empty or optional message
	}

	$layout_class = 'n2n-layout-' . sanitize_html_class( $args['layout'] );
	$wrapper_classes = 'n2n-news-feed ' . $layout_class;

	ob_start();
	?>
	<div class="<?php echo esc_attr( $wrapper_classes ); ?>">
		<?php while ( $query->have_posts() ) : $query->the_post(); 
			$post_id = get_the_ID();
			$title = get_the_title();
			
			// Determine Link: Always goes to permalink (handled by redirect/interstitial logic)
			// OR if user wants direct link in card, requirements say:
			// "Link should go to the aggregated_news permalink (so redirect/interstitial logic is centralized)"
			$permalink = get_permalink();
			$external_image = get_post_meta( $post_id, 'external_image_url', true );
			
			$target_attr = $args['open_in_new_tab'] ? 'target="_blank" rel="noopener noreferrer"' : '';
			?>
			<article class="n2n-news-item">
				<?php if ( $args['show_image'] && $external_image ) : ?>
					<div class="n2n-news-image">
						<a href="<?php echo esc_url( $permalink ); ?>" <?php echo $target_attr; ?>>
							<img src="<?php echo esc_url( $external_image ); ?>" alt="<?php echo esc_attr( $title ); ?>">
						</a>
					</div>
				<?php endif; ?>

				<div class="n2n-news-content">
					<h3 class="n2n-news-title">
						<a href="<?php echo esc_url( $permalink ); ?>" <?php echo $target_attr; ?>><?php echo esc_html( $title ); ?></a>
					</h3>
					
					<?php if ( $args['show_excerpt'] ) : ?>
						<div class="n2n-news-excerpt">
							<?php the_excerpt(); ?>
						</div>
					<?php endif; ?>

					<div class="n2n-news-meta">
						<span class="n2n-date"><?php echo get_the_date(); ?></span>
					</div>
				</div>
			</article>
		<?php endwhile; ?>
	</div>
	<?php
	wp_reset_postdata();
	return ob_get_clean();
}
