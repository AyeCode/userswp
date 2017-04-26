<?php
/*
Plugin Name: UsersWP
Plugin URI: https://wpgeodirectory.com
Description: User management plugin.
Version: 1.0.1
Author: GeoDirectory team
Author URI: https://wpgeodirectory.com
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain: userswp
Domain Path: /languages
Requires at least: 3.1
Tested up to: 4.6
*/

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define( 'USERSWP_VERSION', '1.0.1' );

define( 'USERSWP_PATH', plugin_dir_path( __FILE__ ) );

define( 'USERSWP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-users-wp-activator.php
 */
function activate_users_wp() {
    if (is_main_site()) {
        require_once('includes/class-users-wp-activator.php');
        Users_WP_Activator::activate();    
    }
}
/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-users-wp-deactivator.php
 */
function deactivate_users_wp() {
    require_once('includes/class-users-wp-deactivator.php');
    Users_WP_Deactivator::deactivate();
}
register_activation_hook( __FILE__, 'activate_users_wp' );
register_deactivation_hook( __FILE__, 'deactivate_users_wp' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once('includes/class-users-wp.php');
require_once('includes/helpers.php');
require_once('widgets/login.php');
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
    $plugin = new Users_WP();
    $plugin->run();
}
run_users_wp();