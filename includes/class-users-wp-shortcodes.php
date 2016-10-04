<?php
/**
 * Shortcode related functions
 *
 * This class defines all code necessary for UsersWP shortcodes.
 *
 * @link       http://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    Users_WP
 * @subpackage Users_WP/includes
 */

/**
 * Define the shortcodes functionality.
 *
 * @since      1.0.0
 * @package    Users_WP
 * @subpackage Users_WP/includes
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class Users_WP_Shortcodes {

    private $templates;


    public function __construct() {

        $this->load_dependencies();
        $this->templates = new Users_WP_Templates();


    }

    private function load_dependencies() {

        /**
         * The class responsible for defining all front end templates
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-users-wp-templates.php';


    }


    public function register() {
        $template = $this->templates->uwp_locate_template('register');

        ob_start();
        if ($template) {
            include($template);
        }
        $output = ob_get_contents();
        ob_end_clean();

        return trim($output);
    }

    public function login() {
        $template = $this->templates->uwp_locate_template('login');

        ob_start();
        if ($template) {
            include($template);
        }
        $output = ob_get_contents();
        ob_end_clean();

        return trim($output);
    }

    public function forgot() {
        $template = $this->templates->uwp_locate_template('forgot');

        ob_start();
        if ($template) {
            include($template);
        }
        $output = ob_get_contents();
        ob_end_clean();

        return trim($output);
    }

    public function account() {
        $template = $this->templates->uwp_locate_template('account');

        ob_start();
        if ($template) {
            include($template);
        }
        $output = ob_get_contents();
        ob_end_clean();

        return trim($output);
    }

    public function profile() {
        $template = $this->templates->uwp_locate_template('profile');

        ob_start();
        if ($template) {
            include($template);
        }
        $output = ob_get_contents();
        ob_end_clean();

        return trim($output);
    }

    public function users() {
        $template = $this->templates->uwp_locate_template('users');

        ob_start();
        if ($template) {
            include($template);
        }
        $output = ob_get_contents();
        ob_end_clean();

        return trim($output);
    }

}