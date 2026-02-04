<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Migration: Bulk Heal Events
 * Trigger: /wp-admin/admin.php?n2n_action=heal_events
 */
add_action( 'admin_init', 'n2n_trigger_bulk_migration' );

function n2n_trigger_bulk_migration() {
	if ( ! isset( $_GET['n2n_action'] ) || 'heal_events' !== $_GET['n2n_action'] ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized' );
	}

	echo '<h1>Running N2N Event Migration...</h1>';
	
	// Increase limits for bulk op
	set_time_limit( 300 );
	global $wpdb;

	// 1. Get ALL unique event keys from DB directly (fastest)
	$sql = "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = 'event_key_v2' AND meta_value != ''";
	$keys = $wpdb->get_col( $sql );

	if ( empty( $keys ) ) {
		echo '<p>No event keys found.</p>';
		exit;
	}

	echo '<p>Found ' . count( $keys ) . ' unique event groups. Processing...</p>';
	echo '<ul>';

	$count = 0;
	foreach ( $keys as $key ) {
		// Leverage existing healing logic
		if ( function_exists( 'n2n_recalculate_event_group' ) ) {
			n2n_recalculate_event_group( $key );
			$count++;
			
			if ( $count % 50 === 0 ) {
				echo "<li>Processed $count groups...</li>";
				flush(); // Attempt to push output
			}
		} else {
			echo '<li style="color:red">Error: n2n_recalculate_event_group function not found!</li>';
			exit;
		}
	}

	echo '</ul>';
	echo '<h3>Migration Complete! Processed ' . $count . ' groups.</h3>';
	echo '<p><a href="' . admin_url() . '">Return to Dashboard</a></p>';
	exit;
}
