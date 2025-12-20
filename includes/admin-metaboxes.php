<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Meta Boxes for Aggregated News.
 */
function n2n_add_metaboxes() {
	add_meta_box(
		'n2n_external_url_box',
		__( 'External Article URL', 'n2n-aggregator' ),
		'n2n_render_url_box',
		'aggregated_news',
		'side',
		'high'
	);
	add_meta_box(
		'n2n_external_image_box',
		__( 'External Image (Hotlink)', 'n2n-aggregator' ),
		'n2n_render_image_box',
		'aggregated_news',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'n2n_add_metaboxes' );

/**
 * Render URL Box.
 */
function n2n_render_url_box( $post ) {
	wp_nonce_field( 'n2n_save_meta', 'n2n_meta_nonce' );
	$val = get_post_meta( $post->ID, 'external_url', true );
	?>
	<p>
		<label class="screen-reader-text" for="n2n_external_url"><?php esc_html_e( 'Article URL', 'n2n-aggregator' ); ?></label>
		<input type="url" id="n2n_external_url" name="n2n_external_url" value="<?php echo esc_attr( $val ); ?>" class="widefat" placeholder="https://example.com/original-article" required>
	</p>
	<p class="description">
		<?php esc_html_e( 'Visitors will be redirected to this URL.', 'n2n-aggregator' ); ?>
	</p>
	<?php
}

/**
 * Render Image Box with Preview.
 */
function n2n_render_image_box( $post ) {
	$val = get_post_meta( $post->ID, 'external_image_url', true );
	?>
	<p>
		<label class="screen-reader-text" for="n2n_external_image_url"><?php esc_html_e( 'Image URL', 'n2n-aggregator' ); ?></label>
		<input type="url" id="n2n_external_image_url" name="n2n_external_image_url" value="<?php echo esc_attr( $val ); ?>" class="widefat" placeholder="https://example.com/image.jpg">
	</p>
	<div id="n2n-img-preview" style="margin-top:10px; min-height:50px; background:#f0f0f1; border:1px solid #c3c4c7; padding:5px; text-align:center;">
		<?php if ( $val ) : ?>
			<img src="<?php echo esc_url( $val ); ?>" style="max-width:100%; height:auto; display:block;">
		<?php else : ?>
			<span class="description" style="line-height:50px;"><?php esc_html_e( 'No image', 'n2n-aggregator' ); ?></span>
		<?php endif; ?>
	</div>
	<script>
	// Minimal inline JS for preview
	document.getElementById('n2n_external_image_url').addEventListener('input', function(e) {
		var div = document.getElementById('n2n-img-preview');
		var url = e.target.value.trim();
		if (url) {
			div.innerHTML = '<img src="' + url + '" style="max-width:100%; height:auto; display:block;">';
		} else {
			div.innerHTML = '<span class="description" style="line-height:50px;"><?php esc_html_e( 'No image', 'n2n-aggregator' ); ?></span>';
		}
	});
	</script>
	<?php
}

/**
 * Save Meta Data.
 */
function n2n_save_meta_data( $post_id ) {
	if ( ! isset( $_POST['n2n_meta_nonce'] ) || ! wp_verify_nonce( $_POST['n2n_meta_nonce'], 'n2n_save_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// External URL
	if ( isset( $_POST['n2n_external_url'] ) ) {
		update_post_meta( $post_id, 'external_url', esc_url_raw( $_POST['n2n_external_url'] ) );
	}
	// Image URL
	if ( isset( $_POST['n2n_external_image_url'] ) ) {
		update_post_meta( $post_id, 'external_image_url', esc_url_raw( $_POST['n2n_external_image_url'] ) );
	}
}
add_action( 'save_post_aggregated_news', 'n2n_save_meta_data' );
