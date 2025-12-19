<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Settings Page.
 */
function n2n_add_settings_page() {
	add_options_page(
		__( 'N2N Aggregator Settings', 'n2n-aggregator' ),
		__( 'N2N Aggregator', 'n2n-aggregator' ),
		'manage_options',
		'n2n-aggregator',
		'n2n_render_settings_page'
	);
}
add_action( 'admin_menu', 'n2n_add_settings_page' );

/**
 * Register Settings.
 */
function n2n_register_settings() {
	register_setting( 'n2n_aggregator_options', 'n2n_redirect_mode', array(
		'type' => 'string',
		'default' => 'direct',
		'sanitize_callback' => 'sanitize_key',
	));

	register_setting( 'n2n_aggregator_options', 'n2n_interstitial_countdown', array(
		'type' => 'integer',
		'default' => 0,
		'sanitize_callback' => 'absint',
	));

	register_setting( 'n2n_aggregator_options', 'n2n_redirect_status', array(
		'type' => 'integer',
		'default' => 301,
		'sanitize_callback' => 'absint',
	));
}
add_action( 'admin_init', 'n2n_register_settings' );

/**
 * Render Settings Page.
 */
function n2n_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'n2n_aggregator_options' );
			do_settings_sections( 'n2n_aggregator_options' );
			?>
			<table class="form-table">
				<!-- Redirect Mode -->
				<tr>
					<th scope="row"><label for="n2n_redirect_mode"><?php esc_html_e( 'Redirect Mode', 'n2n-aggregator' ); ?></label></th>
					<td>
						<select name="n2n_redirect_mode" id="n2n_redirect_mode">
							<option value="direct" <?php selected( get_option( 'n2n_redirect_mode', 'direct' ), 'direct' ); ?>><?php esc_html_e( 'Direct Redirect', 'n2n-aggregator' ); ?></option>
							<option value="interstitial" <?php selected( get_option( 'n2n_redirect_mode' ), 'interstitial' ); ?>><?php esc_html_e( 'Interstitial Page', 'n2n-aggregator' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Choose how visitors reach the original article.', 'n2n-aggregator' ); ?></p>
					</td>
				</tr>

				<!-- Countdown -->
				<tr>
					<th scope="row"><label for="n2n_interstitial_countdown"><?php esc_html_e( 'Countdown (Seconds)', 'n2n-aggregator' ); ?></label></th>
					<td>
						<input type="number" name="n2n_interstitial_countdown" id="n2n_interstitial_countdown" value="<?php echo esc_attr( get_option( 'n2n_interstitial_countdown', 0 ) ); ?>" class="small-text">
						<p class="description"><?php esc_html_e( 'Set to 0 to disable auto-redirect on interstitial page.', 'n2n-aggregator' ); ?></p>
					</td>
				</tr>

				<!-- HTTP Status -->
				<tr>
					<th scope="row"><label for="n2n_redirect_status"><?php esc_html_e( 'Redirect HTTP Status', 'n2n-aggregator' ); ?></label></th>
					<td>
						<select name="n2n_redirect_status" id="n2n_redirect_status">
							<option value="301" <?php selected( get_option( 'n2n_redirect_status', 301 ), 301 ); ?>>301 (Permanent)</option>
							<option value="302" <?php selected( get_option( 'n2n_redirect_status' ), 302 ); ?>>302 (Temporary)</option>
						</select>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
