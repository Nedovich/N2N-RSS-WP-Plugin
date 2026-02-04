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
	
	// Event Grouping Logic
	$sibling_count  = (int) get_post_meta( $post_id, 'sibling_count', true );
	$event_key_hash = get_post_meta( $post_id, 'event_key_hash', true );
	
	// Short Summary Logic
	$summary = get_post_meta( $post_id, 'n2n_short_summary', true );
	// Fallback if old data and meta missing (display standard excerpt or nothing? User said "shown in shortcode", assume meta is source of truth)
	if ( ! $summary ) {
		// Optional: Fallback to excerpt if meta is missing (migration)
		// But strictly user asked for "short summary field".
		// We will stick to the meta. If empty in DB, it shows empty.
		$summary = ''; 
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

			<?php if ( $show_excerpt && $summary ) : ?>
				<div class="n2n-card-excerpt">
					<?php echo esc_html( $summary ); ?>
				</div>
			<?php endif; ?>

			<div class="n2n-card-meta">
				<span><?php echo get_the_date( '', $post_id ); ?></span>
				
				<?php if ( $sibling_count > 0 && $event_key_hash ) : ?>
					<a href="<?php echo esc_url( home_url( '/event/' . $event_key_hash . '/' ) ); ?>" class="n2n-group-icon" title="<?php esc_attr_e( 'View related coverage', 'n2n-aggregator' ); ?>">
						<span class="dashicons dashicons-images-alt2"></span>
						<span class="n2n-sibling-count"><?php echo esc_html( $sibling_count + 1 ); ?></span>
					</a>
				<?php endif; ?>
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
		'grid_columns'   => 3,
		'exclude_tags'   => array(),
	);
	$args = wp_parse_args( $args, $defaults );

	// Build Query
	$query_args = array(
		'post_type'           => 'aggregated_news',
		'posts_per_page'      => intval( $args['posts_to_show'] ),
		'post_status'         => ! empty( $args['post_status'] ) ? $args['post_status'] : 'publish',
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
			// 'operator' defaults to 'IN', which works for string or array
		);
	} elseif ( ! empty( $args['category_id'] ) ) {
		$tax_query[] = array(
			'taxonomy' => 'category',
			'field'    => 'term_id',
			'terms'    => $args['category_id'],
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
			'terms'    => $args['tag_id'],
		);
	}

	// 2.1 Exclude Tags (Slug)
	if ( ! empty( $args['exclude_tags'] ) ) {
		// Ensure array
		$exclude_tags = is_array( $args['exclude_tags'] ) ? $args['exclude_tags'] : explode( ',', $args['exclude_tags'] );
		$exclude_tags = array_map( 'trim', $exclude_tags );
		$exclude_tags = array_filter( $exclude_tags ); // Remove empty

		if ( ! empty( $exclude_tags ) ) {
			$tax_query[] = array(
				'taxonomy' => 'post_tag',
				'field'    => 'slug',
				'terms'    => $exclude_tags,
				'operator' => 'NOT IN',
			);
		}
	}

	// Apply Tax Query if exists
	if ( ! empty( $tax_query ) ) {
		// If more than one, 'relation' defaults to AND, but explicit is good.
		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'AND'; // Implicitly AND, but let's be strict.
		}
		$query_args['tax_query'] = $tax_query;
	}

	// META QUERY BUILDER
	$meta_query = array();

	// 3. Event Key Filter
	if ( ! empty( $args['event_key'] ) ) {
		$meta_query[] = array(
			'key'   => 'event_key_v2',
			'value' => sanitize_text_field( $args['event_key'] ),
		);
	}

	// 4. Main Event Logic
	// If explicit 'is_event_main' argument is passed
	if ( isset( $args['is_event_main'] ) ) {
		$is_main = filter_var( $args['is_event_main'], FILTER_VALIDATE_BOOLEAN );

		if ( $is_main ) {
			// SHOW Main Events:
			// STRICT: Only is_event_main = 1
			$meta_query[] = array(
				'key'   => 'is_event_main',
				'value' => '1',
				'compare' => '=',
			);
		} else {
			// SHOW Sub Events:
			// Must explicitly be 0 / false
			$meta_query[] = array(
				'key'     => 'is_event_main',
				'value'   => '0', // Stored as false/0
				'compare' => '=',
			);
		}
	}

	// 5. Exclude current post (for Related query)
	if ( ! empty( $args['exclude_post_id'] ) ) {
		$query_args['post__not_in'] = array( absint( $args['exclude_post_id'] ) );
	}

	if ( ! empty( $meta_query ) ) {
		$query_args['meta_query'] = $meta_query;
	}

	$query = new WP_Query( $query_args );

	if ( ! $query->have_posts() ) return '';

	$layout_class = 'n2n-layout-' . sanitize_html_class( $args['layout'] );
	
	// Grid Cols Class
	if ( 'grid' === $args['layout'] && ! empty( $args['grid_columns'] ) ) {
		$cols = intval( $args['grid_columns'] );
		// Limit to 2-6
		if ( $cols >= 2 && $cols <= 6 ) {
			$layout_class .= ' n2n-grid-cols-' . $cols;
		}
	}
	
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
