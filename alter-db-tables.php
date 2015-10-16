<?php
/**
 * @package   Alter_DB_Tables
 * @author    Alberto Cocchiara <info@altertech.it>
 * @license   GPL-2.0+
 * @link      http://altertech.it
 * @copyright 2015 AlterTech
 *
 * @wordpress-plugin
 * Plugin Name:       DB Tables Import/Export
 * Plugin URI:        http://blog.altertech.it/alter-db-tables/
 * Description:       DB Tables Import / Export by AlterTech two new tools to import and export, you can choose the tables to be imported with the file .csv or export them to json and csv.
 * Version:           1.0.0
 * Author:            Alberto Cocchiara
 * Author URI:        http://blog.altertech.it/author/alberto-cocchiara/
 * Text Domain:       alter-db-tables
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
	die;
}
/*
 * Load Language wrapper function for WPML/Ceceppa Multilingua/Polylang
 */
//require_once( plugin_dir_path( __FILE__ ) . 'languages/language.php' );
add_action('plugins_loaded', 'customize_wp_login_page_lang_ready');
function customize_wp_login_page_lang_ready() {
	load_plugin_textdomain( 'alter-db-tables', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
}
/*
 * Load public class to display login page customization
 *
 */
require_once( plugin_dir_path( __FILE__ ) . 'public/alter-db-tables-class.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook( __FILE__, array( 'Alter_DB_Tables', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Alter_DB_Tables', 'deactivate' ) );
add_action( 'plugins_loaded', array( 'Alter_DB_Tables', 'get_instance' ) );

/* ----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 * ---------------------------------------------------------------------------- */
    if( !get_option( 'alter-db-tables-wp_enable_rewrite_rules' ) ) {} else {
       // Create new rewrite rule
add_action( 'plugins_loaded', array( 'Alter_DB_Tables_Admin_Advanced', 'get_instance' ), 1 );
}

if ( is_admin()  ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/alter-db-tables-admin-class.php' );
	add_action( 'plugins_loaded', array( 'Alter_DB_Tables_Admin', 'get_instance' ) );
}
