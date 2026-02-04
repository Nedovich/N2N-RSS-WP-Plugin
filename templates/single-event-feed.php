<?php
/**
 * Template Name: N2N Event Feed
 * Description: Virtual page for a specific event group.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<!-- N2N EVENT TEMPLATE LOADED -->
<?php

$event_hash = get_query_var( 'n2n_event_hash' );

// Safety Check
if ( ! $event_hash ) {
	echo '<p>' . esc_html__( 'Invalid Event ID.', 'n2n-aggregator' ) . '</p>';
	get_footer();
	exit;
}

// QUERY-TIME SOURCE OF TRUTH
// We do NOT filter by 'is_event_main'.
// We fetch ALL posts with this hash, ordered by Date DESC.
// The FIRST one is implicitly the MAIN one.
$args = array(
	'post_type'      => 'aggregated_news',
	'post_status'    => 'publish',
	'posts_per_page' => 50, // Limit to reasonable amount
	'meta_key'       => 'event_key_hash',
	'meta_value'     => $event_hash,
	'orderby'        => 'date',
	'order'          => 'DESC',
);

$query = new WP_Query( $args );

?>

<div class="n2n-event-page-container">
	
	<?php if ( $query->have_posts() ) : ?>
		
		<div class="n2n-event-header">
			<h1><?php esc_html_e( 'Related Coverage', 'n2n-aggregator' ); ?></h1>
		</div>

		<div class="n2n-event-feed">
			<?php 
			$index = 0;
			while ( $query->have_posts() ) : $query->the_post(); 
				
				// Render Different Layouts based on Index
				// Index 0 = Main Card
				// Index 1+ = Sub Cards
				
				// Reuse existing render function but force layout params
				$render_args = array(
					'show_image'   => ( 0 === $index ), // Only main has big image
					'show_excerpt' => ( 0 === $index ), // Only main has excerpt
					'layout'       => ( 0 === $index ) ? 'main' : 'sub', // Logic handled in CSS class
				);
				
				// We call the single item renderer directly for control
				// OR we can output custom HTML here. 
				// Let's use n2n_render_news_item() to stay DRY, but wrap it.
				?>
				
				<div class="n2n-event-item n2n-item-type-<?php echo ( 0 === $index ) ? 'main' : 'sub'; ?>">
					<?php echo n2n_render_news_item( get_the_ID(), $render_args ); ?>
				</div>

				<?php 
				$index++;
			endwhile; 
			?>
		</div>

	<?php else : ?>
		
		<p><?php esc_html_e( 'No articles found for this event.', 'n2n-aggregator' ); ?></p>

	<?php endif; wp_reset_postdata(); ?>

</div>

<style>
/* Inline Styles for Event Page (Move to CSS later ideally) */
.n2n-event-page-container {
	max-width: 800px;
	margin: 0 auto;
	padding: 20px;
}
.n2n-event-header h1 {
	margin-bottom: 30px;
	border-bottom: 2px solid #eee;
	padding-bottom: 10px;
}
.n2n-event-item {
	margin-bottom: 20px;
}
/* Main Card Style Override */
.n2n-event-item.n2n-item-type-main .n2n-news-card {
	border: 2px solid #0073aa; /* Highlight main */
}
.n2n-event-item.n2n-item-type-main .n2n-card-title {
	font-size: 1.5em;
}
/* Sub Card Style Override */
.n2n-event-item.n2n-item-type-sub .n2n-news-card {
	display: flex;
	align-items: center;
	padding: 10px;
}
.n2n-event-item.n2n-item-type-sub .n2n-card-image {
	display: none; /* Forced hidden via PHP arg too, but ensure here */
}
.n2n-event-item.n2n-item-type-sub .n2n-card-title {
	font-size: 1em;
	margin: 0;
}
</style>

<?php
get_footer();
