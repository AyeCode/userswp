<?php
/**
 * UsersWP extension activation handler
 *
 * @link       http://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    Users_WP
 * @subpackage Users_WP/includes
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * UsersWP Extension Activation Handler Class
 *
 * @since       1.0.0
 */
class Users_WP_Extension_Activation {

    public $plugin_name, $plugin_path, $plugin_file, $is_uwp_installed;

    /**
     * Setup the activation class
     *
     * @access      public
     * @since       1.0.0
     */
    public function __construct( $plugin_path, $plugin_file ) {
        // We need plugin.php!
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        $plugins = get_plugins();

        // Set plugin directory
        $plugin_path = array_filter( explode( '/', $plugin_path ) );
        $this->plugin_path = end( $plugin_path );

        // Set plugin file
        $this->plugin_file = $plugin_file;

        // Set plugin name
        if ( isset( $plugins[$this->plugin_path . '/' . $this->plugin_file]['Name'] ) ) {
            $this->plugin_name = str_replace( 'UsersWP - ', '', $plugins[$this->plugin_path . '/' . $this->plugin_file]['Name'] );
        } else {
            $this->plugin_name = __( 'This plugin', 'uwp' );
        }

        // Is UsersWP installed?
        foreach ( $plugins as $plugin_path => $plugin ) {
            if ( $plugin['Name'] == 'UsersWP' ) {
                $this->is_uwp_installed = true;
                break;
            }
        }
    }


    /**
     * Process plugin deactivation
     *
     * @access      public
     * @since       1.0.0
     * @return      void
     */
    public function run() {
        // Display notice
        add_action( 'admin_notices', array( $this, 'missing_uwp_notice' ) );
    }

    /**
     * Display notice if UsersWP isn't installed
     *
     * @access      public
     * @since       1.0.0
     * @return      string The notice to display
     */
    public function missing_uwp_notice() {
        if ( $this->is_uwp_installed ) {
            echo '<div class="error"><p>' . $this->plugin_name . sprintf( __( ' requires %sUsersWP%s. Please activate it to continue.', 'uwp' ), '<a href="https://wpgeodirectory.com/users-wp/" title="UsersWP" target="_blank">', '</a>' ) . '</p></div>';
        } else {
            echo '<div class="error"><p>' . $this->plugin_name . sprintf( __( ' requires %sUsersWP%s. Please install it to continue.', 'uwp' ), '<a href="https://wpgeodirectory.com/users-wp/" title="UsersWP" target="_blank">', '</a>' ) . '</p></div>';
        }
    }
}