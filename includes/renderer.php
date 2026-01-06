<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * RENDERER: PURE HTML VIEW.
 * Responsibility: Generate HTML strings based on inputs.
 * Restrictions:
 * - NO external_url access (unless passed as arg)
 * - NO redirects
 * - NO is_singular checks
 * - NO global query checks
 */

/**
 * Render Single News Item Card.
 * @return string HTML
 */
function n2n_render_news_item( $post_id, $args = [] ) {
	$title     = get_the_title( $post_id );
	$permalink = get_permalink( $post_id ); // ALWAYS link to permalink
	$image_url = get_post_meta( $post_id, 'external_image_url', true );
	
	// Excerpt Logic
	$excerpt_length = isset($args['excerpt_length']) ? intval($args['excerpt_length']) : 55;
	$excerpt = get_the_excerpt( $post_id );
	if ( $excerpt_length > 0 ) {
		$excerpt = wp_trim_words( $excerpt, $excerpt_length, '...' );
	}

	$show_image   = ! empty( $args['show_image'] );
	$show_excerpt = ! empty( $args['show_excerpt'] );
	$new_tab      = ! empty( $args['new_tab'] );
	
	// Open SELF (permalink) in new tab if requested
	$target = $new_tab ? 'target="_blank" rel="noopener noreferrer"' : '';

	ob_start();
	?>
	<article class="n2n-news-card">
		<?php if ( $show_image && $image_url ) : ?>
			<div class="n2n-card-image">
				<a href="<?php echo esc_url( $permalink ); ?>" <?php echo $target; ?>>
					<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $title ); ?>">
				</a>
			</div>
		<?php endif; ?>

		<div class="n2n-card-content">
			<h3 class="n2n-card-title">
				<a href="<?php echo esc_url( $permalink ); ?>" <?php echo $target; ?>>
					<?php echo esc_html( $title ); ?>
				</a>
			</h3>

			<?php if ( $show_excerpt ) : ?>
				<div class="n2n-card-excerpt">
					<?php echo $excerpt; ?>
				</div>
			<?php endif; ?>

			<div class="n2n-card-meta">
				<span><?php echo get_the_date( '', $post_id ); ?></span>
			</div>
		</div>
	</article>
	<?php
	return ob_get_clean();
}

/**
 * Render News Query Loop.
 * @return string HTML
 */
function n2n_render_news_query( $args = [] ) {
	$defaults = array(
		'posts_to_show'  => 6,
		'category_id'    => '', // Legacy ID support
		'category'       => '', // Slug support
		'tag_id'         => '', // Legacy ID support
		'tag'            => '', // Slug support
		'layout'         => 'grid',
		'show_image'     => true,
		'show_excerpt'   => true,
		'excerpt_length' => 55,
		'orderby'        => 'date', // date | rand
		'new_tab'        => false,
	);
	$args = wp_parse_args( $args, $defaults );

	// Build Query
	$query_args = array(
		'post_type'           => 'aggregated_news',
		'posts_per_page'      => intval( $args['posts_to_show'] ),
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
	);

	// Order Handling
	if ( 'rand' === $args['orderby'] ) {
		$query_args['orderby'] = 'rand';
	} else {
		$query_args['orderby'] = 'date';
		$query_args['order']   = 'DESC';
	}

	// Tax Query Builder (AND logic)
	$tax_query = array();

	// 1. Category (Slug or ID)
	if ( ! empty( $args['category'] ) ) {
		$tax_query[] = array(
			'taxonomy' => 'category',
			'field'    => 'slug',
			'terms'    => $args['category'],
		);
	} elseif ( ! empty( $args['category_id'] ) ) {
		$tax_query[] = array(
			'taxonomy' => 'category',
			'field'    => 'term_id',
			'terms'    => intval( $args['category_id'] ),
		);
	}

	// 2. Tag (Slug or ID)
	if ( ! empty( $args['tag'] ) ) {
		$tax_query[] = array(
			'taxonomy' => 'post_tag',
			'field'    => 'slug',
			'terms'    => $args['tag'],
		);
	} elseif ( ! empty( $args['tag_id'] ) ) {
		$tax_query[] = array(
			'taxonomy' => 'post_tag',
			'field'    => 'term_id',
			'terms'    => intval( $args['tag_id'] ),
		);
	}

	// Apply Tax Query if exists
	if ( ! empty( $tax_query ) ) {
		// If more than one, 'relation' defaults to AND, but explicit is good.
		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'AND'; // Implicitly AND, but let's be strict.
		}
		$query_args['tax_query'] = $tax_query;
	}

	$query = new WP_Query( $query_args );

	if ( ! $query->have_posts() ) return '';

	$layout_class = 'n2n-layout-' . sanitize_html_class( $args['layout'] );
	$out = '<div class="n2n-news-feed ' . esc_attr( $layout_class ) . '">';

	while ( $query->have_posts() ) {
		$query->the_post();
		$out .= n2n_render_news_item( get_the_ID(), $args );
	}
	wp_reset_postdata();

	$out .= '</div>';
	return $out;
}

/**
 * Generate Interstitial HTML.
 * Pure View function.
 * @return string HTML
 */
function n2n_get_interstitial_html( $url, $image_url, $countdown = 0 ) {
	ob_start();
	?>
	<div class="n2n-interstitial-wrapper">
		<h1 class="n2n-interstitial-title"><?php the_title(); ?></h1>
		
		<?php if ( $image_url ) : ?>
			<div class="n2n-interstitial-img">
				<img src="<?php echo esc_url( $image_url ); ?>" alt="">
			</div>
		<?php endif; ?>

		<div class="n2n-interstitial-msg">
			<p><?php esc_html_e( 'You are being redirected to the original article...', 'n2n-aggregator' ); ?></p>
			<?php if ( $countdown > 0 ) : ?>
				<p class="n2n-timer-msg">
					<?php printf( esc_html__( 'Redirecting in %s seconds...', 'n2n-aggregator' ), '<span id="n2n-counter">' . absint( $countdown ) . '</span>' ); ?>
				</p>
			<?php endif; ?>
		</div>

		<div class="n2n-interstitial-actions">
			<a href="<?php echo esc_url( $url ); ?>" class="button n2n-btn-primary">
				<?php esc_html_e( 'Continue Reading', 'n2n-aggregator' ); ?>
			</a>
		</div>

		<?php if ( $countdown > 0 ) : ?>
			<script>
			setTimeout(function() {
				window.location.href = "<?php echo esc_url_raw( $url ); ?>";
			}, <?php echo absint( $countdown ) * 1000; ?>);
			</script>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}
