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
	add_meta_box(
		'n2n_event_info_box',
		__( 'Event Info (Read Only)', 'n2n-aggregator' ),
		'n2n_render_event_box',
		'aggregated_news',
		'side',
		'default'
	);
	add_meta_box(
		'n2n_summary_box',
		__( 'Short Summary (Max 50 chars)', 'n2n-aggregator' ),
		'n2n_render_summary_box',
		'aggregated_news',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'n2n_add_metaboxes' );

/**
 * Render Short Summary Box.
 */
function n2n_render_summary_box( $post ) {
	$val = get_post_meta( $post->ID, 'n2n_short_summary', true );
	?>
	<p>
		<label class="screen-reader-text" for="n2n_short_summary"><?php esc_html_e( 'Short Summary', 'n2n-aggregator' ); ?></label>
		<textarea id="n2n_short_summary" name="n2n_short_summary" class="widefat" rows="2" maxlength="100" placeholder="<?php esc_attr_e( 'Enter short summary (approx 50 chars)', 'n2n-aggregator' ); ?>"><?php echo esc_textarea( $val ); ?></textarea>
		<span class="description"><?php esc_html_e( 'Shown in news lists. If empty, first 50 chars of content will be used.', 'n2n-aggregator' ); ?></span>
	</p>
	<?php
}

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
 * Render Event Info Box (Read Only).
 */
function n2n_render_event_box( $post ) {
	$event_key = get_post_meta( $post->ID, 'event_key_v2', true );
	$main_date = get_post_meta( $post->ID, 'main_publish_date', true );
	$is_main   = get_post_meta( $post->ID, 'is_event_main', true );
	?>
	<p>
		<label class="screen-reader-text" for="n2n_event_key">Event Key</label>
		<strong>Event Key:</strong><br>
		<input type="text" value="<?php echo esc_attr( $event_key ); ?>" class="widefat" readonly disabled style="background:#f0f0f1; color:#666;">
	</p>
	<p>
		<label class="screen-reader-text" for="n2n_main_date">Main Publish Date</label>
		<strong>Main Publish Date:</strong><br>
		<input type="text" value="<?php echo esc_attr( $main_date ); ?>" class="widefat" readonly disabled style="background:#f0f0f1; color:#666;">
	</p>
	<p>
		<strong>Is Main Event?</strong> 
		<?php echo $is_main ? '<span style="color:green; font-weight:bold;">YES</span>' : '<span style="color:#666;">No</span>'; ?>
	</p>
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

	// Short Summary Logic
	if ( isset( $_POST['n2n_short_summary'] ) ) {
		$summary = sanitize_textarea_field( $_POST['n2n_short_summary'] );
		
		// Auto-populate if empty
		if ( empty( $summary ) ) {
			// Try to get from $_POST first (more reliable on save)
			if ( ! empty( $_POST['content'] ) ) {
				$text_only = wp_strip_all_tags( wp_unslash( $_POST['content'] ) );
			} else {
				// Fallback to DB (might be empty if new post)
				$content = get_post_field( 'post_content', $post_id );
				$text_only = wp_strip_all_tags( $content );
			}
			
			// Rigid 50 char limit as requested
			$summary = mb_substr( $text_only, 0, 50 );
		}
		
		update_post_meta( $post_id, 'n2n_short_summary', $summary );
	}
}
add_action( 'save_post_aggregated_news', 'n2n_save_meta_data' );
