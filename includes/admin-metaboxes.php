<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add meta boxes to 'aggregated_news'.
 */
function n2n_add_aggregated_news_metaboxes() {
	add_meta_box(
		'n2n_external_url_box',
		__( 'External Article URL', 'n2n-aggregator' ),
		'n2n_render_external_url_box',
		'aggregated_news',
		'side',
		'high'
	);

	add_meta_box(
		'n2n_external_image_box',
		__( 'External Image (Hotlink)', 'n2n-aggregator' ),
		'n2n_render_external_image_box',
		'aggregated_news',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'n2n_add_aggregated_news_metaboxes' );

/**
 * Render External URL Meta Box.
 */
function n2n_render_external_url_box( $post ) {
	wp_nonce_field( 'n2n_save_meta_data', 'n2n_meta_nonce' );
	$value = get_post_meta( $post->ID, 'external_url', true );
	?>
	<p>
		<label for="n2n_external_url" class="screen-reader-text"><?php esc_html_e( 'External URL', 'n2n-aggregator' ); ?></label>
		<input type="url" id="n2n_external_url" name="n2n_external_url" value="<?php echo esc_attr( $value ); ?>" class="widefat" placeholder="https://example.com/original-article">
	</p>
	<p class="description">
		<?php esc_html_e( 'Visitors will be redirected to this URL when opening the news item.', 'n2n-aggregator' ); ?>
	</p>
	<?php
}

/**
 * Render External Image Meta Box with Live Preview.
 */
function n2n_render_external_image_box( $post ) {
	$value = get_post_meta( $post->ID, 'external_image_url', true );
	?>
	<p>
		<label for="n2n_external_image_url" class="screen-reader-text"><?php esc_html_e( 'External Image URL', 'n2n-aggregator' ); ?></label>
		<input type="url" id="n2n_external_image_url" name="n2n_external_image_url" value="<?php echo esc_attr( $value ); ?>" class="widefat" placeholder="https://example.com/image.jpg">
	</p>
	<div id="n2n-image-preview" style="margin-top: 10px; text-align: center; background: #f0f0f1; border: 1px solid #c3c4c7; min-height: 50px; display: flex; align-items: center; justify-content: center;">
		<?php if ( $value ) : ?>
			<img src="<?php echo esc_url( $value ); ?>" style="max-width: 100%; height: auto; display: block;" alt="<?php esc_attr_e( 'Preview', 'n2n-aggregator' ); ?>">
		<?php else : ?>
			<span class="description"><?php esc_html_e( 'No image', 'n2n-aggregator' ); ?></span>
		<?php endif; ?>
	</div>
	<script>
	// Simple inline JS for preview
	(function($){
		var input = document.getElementById('n2n_external_image_url');
		var preview = document.getElementById('n2n-image-preview');
		if(input && preview){
			input.addEventListener('input', function(e){
				var url = e.target.value.trim();
				if(url) {
					preview.innerHTML = '<img src="' + url + '" style="max-width: 100%; height: auto; display: block;">';
				} else {
					preview.innerHTML = '<span class="description"><?php esc_js( __( 'No image', 'n2n-aggregator' ) ); ?></span>';
				}
			});
		}
	})(jQuery);
	</script>
	<?php
}

/**
 * Save Meta Box Data.
 */
function n2n_save_aggregated_news_meta( $post_id ) {
	// Check nonce
	if ( ! isset( $_POST['n2n_meta_nonce'] ) || ! wp_verify_nonce( $_POST['n2n_meta_nonce'], 'n2n_save_meta_data' ) ) {
		return;
	}

	// Check autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check permissions
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Save External URL
	if ( isset( $_POST['n2n_external_url'] ) ) {
		update_post_meta( $post_id, 'external_url', esc_url_raw( $_POST['n2n_external_url'] ) );
	}

	// Save External Image URL
	if ( isset( $_POST['n2n_external_image_url'] ) ) {
		update_post_meta( $post_id, 'external_image_url', esc_url_raw( $_POST['n2n_external_image_url'] ) );
	}
}
add_action( 'save_post_aggregated_news', 'n2n_save_aggregated_news_meta' );
