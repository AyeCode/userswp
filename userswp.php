<?php
/*
Plugin Name: UsersWP
Plugin URI: https://userswp.io/
Description: User management plugin.
Version: 1.0.16
Author: AyeCode Ltd
Author URI: https://userswp.io
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain: userswp
Domain Path: /languages
Requires at least: 3.1
Tested up to: 4.9
*/

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define( 'USERSWP_NAME', 'userswp' );

define( 'USERSWP_VERSION', '1.0.16' );

define( 'USERSWP_PATH', plugin_dir_path( __FILE__ ) );

define( 'USERSWP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-activator.php
 * @param $network_wide
 */
function activate_users_wp($network_wide) {
    require_once('includes/class-activator.php');
    UsersWP_Activator::activate($network_wide);
}
/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-deactivator.php
 */
function deactivate_users_wp() {
    require_once('includes/class-deactivator.php');
    UsersWP_Deactivator::deactivate();
}
register_activation_hook( __FILE__, 'activate_users_wp' );
register_deactivation_hook( __FILE__, 'deactivate_users_wp' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once('includes/class-userswp.php');

add_action( 'admin_init', 'uwp_automatic_upgrade' );
function uwp_automatic_upgrade(){
    $uwp_db_version = get_option('uwp_db_version');

    if ( $uwp_db_version != USERSWP_VERSION ) {
        require_once('includes/class-activator.php');

        UsersWP_Activator::activate(is_plugin_active_for_network( 'userswp/userswp.php' ));
    }
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
    $plugin = new UsersWP();
    $plugin->run();
}
run_users_wp();