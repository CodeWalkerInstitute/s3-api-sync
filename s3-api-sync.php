<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              zekeswepson.com
 * @since             1.0.0
 * @package           S3_Api_Sync
 *
 * @wordpress-plugin
 * Plugin Name:       S3 API Sync
 * Plugin URI:        http://codewalker.institute
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Zeke Swepson
 * Author URI:        zekeswepson.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       s3-api-sync
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGIN_NAME_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-s3-api-sync-activator.php
 */
function activate_s3_api_sync() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-s3-api-sync-activator.php';
	S3_Api_Sync_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-s3-api-sync-deactivator.php
 */
function deactivate_s3_api_sync() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-s3-api-sync-deactivator.php';
	S3_Api_Sync_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_s3_api_sync' );
register_deactivation_hook( __FILE__, 'deactivate_s3_api_sync' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-s3-api-sync.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_s3_api_sync() {

	$plugin = new S3_Api_Sync();
	$plugin->run();

}
run_s3_api_sync();
