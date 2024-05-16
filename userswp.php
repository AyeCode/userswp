<?php
/*
Plugin Name: UsersWP
Plugin URI: https://userswp.io/
Description: The only lightweight user profile plugin for WordPress. UsersWP features front end user profile, users directory, a registration and a login form.
Version: 1.2.10
Author: AyeCode Ltd
Author URI: https://userswp.io
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain: userswp
Domain Path: /languages
Requires at least: 4.9
Tested up to: 6.5
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'USERSWP_NAME' ) ) {
	define( 'USERSWP_NAME', 'userswp' );
}

if ( ! defined( 'USERSWP_VERSION' ) ) {
	define( 'USERSWP_VERSION', '1.2.10' );
}

if ( ! defined( 'USERSWP_PATH' ) ) {
	define( 'USERSWP_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'USERSWP_PLUGIN_URL' ) ) {
	define( 'USERSWP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'USERSWP_PLUGIN_FILE' ) ) {
	define( 'USERSWP_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'USERSWP_PLUGIN_BASENAME' ) ) {
	define( 'USERSWP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
if ( ! class_exists( 'UsersWP' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-userswp.php';
}
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function run_users_wp() {
	global $userswp;
	$userswp = new UsersWP();
}

run_users_wp();