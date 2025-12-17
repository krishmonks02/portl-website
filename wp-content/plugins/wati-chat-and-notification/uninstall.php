<?php
/**
 * WATI Chat and Notification
 * Unscheduling the events.
 *
 * @package WATI-Chat-And-Notification
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}


wp_clear_scheduled_hook( 'wati_cartflow_ca_update_order_status_action' );
