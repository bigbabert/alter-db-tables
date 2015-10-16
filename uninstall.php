<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @package   Customize_WP_Config
 * @author    Alberto Cocchiara <info@altertech.it>
 * @license   GPL-2.0+
 * @link      http://altertech.it
 * @copyright 2015 AlterTech
 */
// If uninstall not called from WordPress, then exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}


if ( is_multisite() ) {

	$blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A );
	if ( $blogs ) {

		foreach ( $blogs as $blog ) {
			switch_to_blog( $blog[ 'blog_id' ] );
			restore_current_blog();
		}
	}
} else {
	
}