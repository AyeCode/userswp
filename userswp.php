<?php
/*
Plugin Name: UsersWP
Plugin URI: https://userswp.io
Description: User management plugin.
Version: 1.0.2
Author: AyeCode Ltd
Author URI: https://userswp.io
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain: userswp
Domain Path: /languages
Requires at least: 3.1
Tested up to: 4.7
Update URL: https://github.com/UsersWP/userswp/
*/

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define( 'USERSWP_VERSION', '1.0.2' );

define( 'USERSWP_PATH', plugin_dir_path( __FILE__ ) );

define( 'USERSWP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-users-wp-activator.php
 * @param $network_wide
 */
function activate_users_wp($network_wide) {
    if (is_multisite()) {
        if ($network_wide) {
            $main_blog_id = (int) get_network()->site_id;
            // Switch to the new blog.
            switch_to_blog( $main_blog_id );

            require_once('includes/class-users-wp-activator.php');
            Users_WP_Activator::activate();

            // Restore original blog.
            restore_current_blog();
        } else {
            require_once('includes/class-users-wp-activator.php');
            Users_WP_Activator::activate();
        }
    } else {
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


/**
 * Show update plugin admin notification.
 */
if(is_admin()){
    if (!function_exists('ayecode_show_update_plugin_requirement')) {//only load the update file if needed
        function ayecode_show_update_plugin_requirement() {
            if ( !defined( 'WP_EASY_UPDATES_ACTIVE' ) ) {
                ?>
                <div class="notice notice-warning is-dismissible">
                    <p>
                        <strong>
                            <?php
                            echo sprintf( __( 'The plugin %sWP Easy Updates%s is required to check for and update some installed plugins, please install it now.', 'geodirectory' ), '<a href="https://wpeasyupdates.com/" target="_blank" title="WP Easy Updates">', '</a>' );
                            ?>
                        </strong>
                    </p>
                </div>
                <?php
            }
        }

        add_action( 'admin_notices', 'ayecode_show_update_plugin_requirement' );
    }
}