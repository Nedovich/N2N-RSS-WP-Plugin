<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get categories for dropdown
$categories = get_categories( array( 'hide_empty' => false ) );
$tags       = get_tags( array( 'hide_empty' => false ) );
?>
<div class="wrap n2n-builder-wrap">
	<h1><?php esc_html_e( 'Preview & Shortcode Builder', 'n2n-aggregator' ); ?></h1>
	
	<div class="n2n-builder-container">
		<!-- Controls Sidebar -->
		<div class="n2n-builder-controls">
			
			<div class="n2n-control-group">
				<label><?php esc_html_e( 'Posts Count', 'n2n-aggregator' ); ?></label>
				<input type="number" id="n2n-posts" value="6" min="1" max="50">
			</div>

			<div class="n2n-control-group">
				<label><?php esc_html_e( 'Category', 'n2n-aggregator' ); ?></label>
				<select id="n2n-category">
					<option value=""><?php esc_html_e( 'All Categories', 'n2n-aggregator' ); ?></option>
					<?php foreach ( $categories as $cat ) : ?>
						<!-- Using Slug for Shortcode readability -->
						<option value="<?php echo esc_attr( $cat->slug ); ?>"><?php echo esc_html( $cat->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="n2n-control-group">
				<label><?php esc_html_e( 'Tag', 'n2n-aggregator' ); ?></label>
				<select id="n2n-tag">
					<option value=""><?php esc_html_e( 'All Tags', 'n2n-aggregator' ); ?></option>
					<?php foreach ( $tags as $tag ) : ?>
						<option value="<?php echo esc_attr( $tag->slug ); ?>"><?php echo esc_html( $tag->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="n2n-control-group">
				<label><?php esc_html_e( 'Order By', 'n2n-aggregator' ); ?></label>
				<select id="n2n-order">
					<option value="date"><?php esc_html_e( 'Latest', 'n2n-aggregator' ); ?></option>
					<option value="rand"><?php esc_html_e( 'Random', 'n2n-aggregator' ); ?></option>
				</select>
			</div>

			<div class="n2n-control-group">
				<label><?php esc_html_e( 'Layout', 'n2n-aggregator' ); ?></label>
				<select id="n2n-layout">
					<option value="grid">Grid</option>
					<option value="list">List</option>
					<option value="card-small">Card Small</option>
					<option value="card-large">Card Large</option>
				</select>
			</div>

			<div class="n2n-control-group">
				<label><?php esc_html_e( 'Excerpt Length', 'n2n-aggregator' ); ?></label>
				<input type="number" id="n2n-excerpt-length" value="55" min="0">
			</div>

			<div class="n2n-control-group n2n-checkbox">
				<label>
					<input type="checkbox" id="n2n-show-image" checked>
					<?php esc_html_e( 'Show Image', 'n2n-aggregator' ); ?>
				</label>
			</div>

		</div>

		<!-- Main Area -->
		<div class="n2n-builder-main">
			
			<!-- Readonly Shortcode -->
			<div class="n2n-shortcode-output">
				<h3>Generated Shortcode</h3>
				<div class="n2n-copy-wrapper">
					<input type="text" id="n2n-generated-code" readonly>
					<button id="n2n-copy-btn" class="button button-primary">Copy</button>
				</div>
			</div>

			<!-- Live Preview -->
			<div class="n2n-preview-area">
				<h3>Live Preview</h3>
				<div id="n2n-preview-canvas">
					<span class="spinner is-active" style="float:none;"></span>
				</div>
			</div>

		</div>
	</div>
</div>
