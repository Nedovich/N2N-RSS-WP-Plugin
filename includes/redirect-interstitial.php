<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle Direct Redirect mode.
 * Hook into template_redirect.
 */
function n2n_handle_direct_redirect() {
	if ( ! is_singular( 'aggregated_news' ) ) {
		return;
	}

	$mode = get_option( 'n2n_redirect_mode', 'direct' );
	if ( 'direct' !== $mode ) {
		return;
	}

	// If mode IS direct, let's redirect.
	$external_url = get_post_meta( get_the_ID(), 'external_url', true );
	if ( $external_url ) {
		$status = get_option( 'n2n_redirect_status', 301 );
		wp_redirect( $external_url, $status );
		exit;
	}
}
add_action( 'template_redirect', 'n2n_handle_direct_redirect' );

/**
 * Handle Interstitial Mode Content.
 * Hook into 'the_content' to assume control of the single page display.
 */
function n2n_interstitial_content_filter( $content ) {
	if ( ! is_singular( 'aggregated_news' ) || in_the_loop() === false ) {
		return $content;
	}

	// Only modify main query content
	if ( ! is_main_query() ) {
		return $content;
	}

	$mode = get_option( 'n2n_redirect_mode', 'direct' );
	if ( 'interstitial' !== $mode ) {
		// If direct mode, template_redirect should have fired. 
		// If not (e.g. valid URL missing), show normal content.
		return $content;
	}

	$external_url = get_post_meta( get_the_ID(), 'external_url', true );
	if ( ! $external_url ) {
		// Fallback if no URL
		return $content;
	}

	// Prepare Interstitial Output
	$countdown = get_option( 'n2n_interstitial_countdown', 0 );
	$image_url = get_post_meta( get_the_ID(), 'external_image_url', true );

	ob_start();
	?>
	<div class="n2n-interstitial-container">
		
		<?php if ( $image_url ) : ?>
			<div class="n2n-interstitial-image">
				<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php the_title_attribute(); ?>">
			</div>
		<?php endif; ?>

		<div class="n2n-interstitial-excerpt">
			<?php the_excerpt(); ?>
		</div>

		<div class="n2n-interstitial-action">
			<a href="<?php echo esc_url( $external_url ); ?>" class="button n2n-continue-btn">
				<?php esc_html_e( 'Continue to original article', 'n2n-aggregator' ); ?>
			</a>
			<?php if ( $countdown > 0 ) : ?>
				<p class="n2n-countdown-msg">
					<?php printf( esc_html__( 'Redirecting in %s seconds...', 'n2n-aggregator' ), '<span id="n2n-timer">' . absint( $countdown ) . '</span>' ); ?>
				</p>
				<script>
				(function(){
					var count = <?php echo absint( $countdown ); ?>;
					var timerSpan = document.getElementById('n2n-timer');
					var redirectUrl = "<?php echo esc_url_raw( $external_url ); ?>";
					
					var interval = setInterval(function(){
						count--;
						if(timerSpan) timerSpan.innerText = count;
						if(count <= 0){
							clearInterval(interval);
							window.location.href = redirectUrl;
						}
					}, 1000);
				})();
				</script>
			<?php endif; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
add_filter( 'the_content', 'n2n_interstitial_content_filter' );
