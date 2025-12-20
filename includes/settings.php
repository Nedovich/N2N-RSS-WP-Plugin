<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Settings Page.
 */
function n2n_add_options_page() {
	add_options_page(
		'N2N Aggregator',
		'N2N Aggregator',
		'manage_options',
		'n2n-aggregator',
		'n2n_render_settings'
	);
}
add_action( 'admin_menu', 'n2n_add_options_page' );

/**
 * Register Settings.
 */
function n2n_register_plugin_settings() {
	register_setting( 'n2n_options', 'n2n_redirect_mode', array(
		'default' => 'direct',
		'sanitize_callback' => 'sanitize_key',
	) );
	register_setting( 'n2n_options', 'n2n_countdown', array(
		'default' => 0,
		'sanitize_callback' => 'absint',
	) );
	register_setting( 'n2n_options', 'n2n_redirect_status', array(
		'default' => 301,
		'sanitize_callback' => 'absint',
	) );
}
add_action( 'admin_init', 'n2n_register_plugin_settings' );

/**
 * Render Settings Page.
 */
function n2n_render_settings() {
	if ( ! current_user_can( 'manage_options' ) ) return;
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'N2N Aggregator Settings', 'n2n-aggregator' ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'n2n_options' );
			do_settings_sections( 'n2n_options' );
			?>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Redirect Mode', 'n2n-aggregator' ); ?></th>
					<td>
						<select name="n2n_redirect_mode">
							<option value="direct" <?php selected( get_option('n2n_redirect_mode'), 'direct' ); ?>>
								<?php esc_html_e( 'Direct Redirect', 'n2n-aggregator' ); ?>
							</option>
							<option value="interstitial" <?php selected( get_option('n2n_redirect_mode'), 'interstitial' ); ?>>
								<?php esc_html_e( 'Interstitial (Ad) Page', 'n2n-aggregator' ); ?>
							</option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Interstitial Countdown', 'n2n-aggregator' ); ?></th>
					<td>
						<input type="number" name="n2n_countdown" value="<?php echo esc_attr( get_option('n2n_countdown', 0) ); ?>" class="small-text">
						<p class="description"><?php esc_html_e( 'Seconds to wait before redirecting. 0 to disable auto-redirect.', 'n2n-aggregator' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'HTTP Status', 'n2n-aggregator' ); ?></th>
					<td>
						<select name="n2n_redirect_status">
							<option value="301" <?php selected( get_option('n2n_redirect_status', 301), 301 ); ?>>301 (Permanent)</option>
							<option value="302" <?php selected( get_option('n2n_redirect_status'), 302 ); ?>>302 (Temporary)</option>
						</select>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
