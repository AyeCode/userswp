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

    protected $loader;

    public function __construct($loader) {

        $this->loader = $loader;
        $this->load_dependencies();
        $this->templates = new Users_WP_Templates($loader);


    }

    private function load_dependencies() {

        /**
         * The class responsible for defining all front end templates
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-users-wp-templates.php';


    }


    public function register() {
        return $this->uwp_generate_shortcode('register');
    }

    public function login() {
        return $this->uwp_generate_shortcode('login');
    }

    public function forgot() {
        return $this->uwp_generate_shortcode('forgot');
    }

    public function change() {
        return $this->uwp_generate_shortcode('change');
    }

    public function reset() {
        return $this->uwp_generate_shortcode('reset');
    }

    public function account() {
        return $this->uwp_generate_shortcode('account');
    }

    public function profile() {
        return $this->uwp_generate_shortcode('profile');
    }

    public function users() {
        return $this->uwp_generate_shortcode('users');
    }
    
    public function uwp_generate_shortcode($type = 'register') {
        $template = $this->templates->uwp_locate_template($type);

        ob_start();
        if ($template) {
            include($template);
        }
        $output = ob_get_contents();
        ob_end_clean();

        return trim($output);
    }

}