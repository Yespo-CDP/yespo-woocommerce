<?php

/**
 * Yespo
 *
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * @package   Yespo
 * @author    Yespo Omnichannel CDP <vadym.gmurya@asper.pro>
 * @copyright 2022 Yespo
 * @license   GPL 3.0+
 * @link      https://yespo.io/
 */

// If uninstall not called from WordPress, then exit.
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Loop for uninstall
 *
 * @return void
 */
function y_uninstall_multisite() {
	if ( is_multisite() ) {
		/** @var array<\WP_Site> $blogs */
		$blogs = get_sites();

		if ( !empty( $blogs ) ) {
			foreach ( $blogs as $blog ) {
				switch_to_blog( (int) $blog->blog_id );
				y_uninstall();
				restore_current_blog();
			}

			return;
		}
	}

	y_uninstall();
}

/**
 * What happen on uninstall?
 *
 * @global WP_Roles $wp_roles
 * @return void
 */
function y_uninstall() { // phpcs:ignore
    global $wpdb;

    $contact_log = $wpdb->prefix . 'yespo_contact_log';
    $export_status_log = $wpdb->prefix . 'yespo_export_status_log';
    $order_log = $wpdb->prefix . 'yespo_order_log';
    $table_yespo_queue = $wpdb->prefix . 'yespo_queue';
    $table_yespo_queue_items = $wpdb->prefix . 'yespo_queue_items';
    $table_yespo_queue_orders = $wpdb->prefix . 'yespo_queue_orders';
    $table_yespo_curl_json = $wpdb->prefix . 'yespo_curl_json'; //logging jsons to yespo
    $table_yespo_auth_log = $wpdb->prefix . 'yespo_auth_log'; //auth logging

    $wpdb->query( "DROP TABLE IF EXISTS $contact_log" );
    $wpdb->query( "DROP TABLE IF EXISTS $export_status_log" );
    $wpdb->query( "DROP TABLE IF EXISTS $order_log" );
    $wpdb->query( "DROP TABLE IF EXISTS $table_yespo_queue" );
    $wpdb->query( "DROP TABLE IF EXISTS $table_yespo_queue_items" );
    $wpdb->query( "DROP TABLE IF EXISTS $table_yespo_queue_orders" );
    $wpdb->query( "DROP TABLE IF EXISTS $table_yespo_curl_json" );
    $wpdb->query( "DROP TABLE IF EXISTS $table_yespo_auth_log" );

    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM $wpdb->usermeta WHERE meta_key = %s",
            'yespo_contact_id'
        )
    );

    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->postmeta} WHERE meta_key IN (%s, %s, %s) AND post_id IN (
            SELECT ID FROM {$wpdb->posts} WHERE post_type IN ('shop_order', 'shop_order_placehold')
        )",
            'sent_order_to_yespo',
            'order_time',
            'customer_removed'
        )
    );

    delete_option('yespo_options');
    delete_option('yespo-version');

    
    if (wp_next_scheduled('yespo_export_data_cron')) {
        wp_clear_scheduled_hook('yespo_export_data_cron');
    }

}

y_uninstall_multisite();
