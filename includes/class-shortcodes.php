<?php
/**
 * Shortcode related functions
 *
 * This class defines all code necessary for UsersWP shortcodes.
 *
 * @since      1.0.0
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class Users_WP_Shortcodes {

    private $templates;

    protected $loader;

    /**
     *
     *
     * @since   1.0.0
     * @package UsersWP
     * @return void
     */
    public function __construct($loader) {

        $this->loader = $loader;
        $this->load_dependencies();
        $this->templates = new Users_WP_Templates($loader);


    }

    /**
     *
     *
     * @since   1.0.0
     * @package UsersWP
     * @return void
     */
    private function load_dependencies() {

        /**
         * The class responsible for defining all front end templates
         */
        require_once dirname(dirname( __FILE__ )) . '/includes/class-templates.php';


    }
    
    /**
     *
     *
     * @since   1.0.0
     * @package UsersWP
     * @return void
     */
    public function register($atts) {
        if (is_user_logged_in()) {
            return "";
        }
        $args = shortcode_atts(
            array(
                'role_id'   => '0',
            ),
            $atts
        );
        global $uwp_register_role_id;
        $uwp_register_role_id = (int) $args['role_id'];
        return $this->uwp_generate_shortcode('register');
    }

    /**
     *
     *
     * @since   1.0.0
     * @package UsersWP
     * @return void
     */
    public function login() {
        if (is_user_logged_in()) {
            return "";
        }
        return $this->uwp_generate_shortcode('login');
    }

    /**
     *
     *
     * @since   1.0.0
     * @package UsersWP
     * @return void
     */
    public function forgot() {
        return $this->uwp_generate_shortcode('forgot');
    }

    /**
     *
     *
     * @since   1.0.0
     * @package UsersWP
     * @return void
     */
    public function change() {
        return $this->uwp_generate_shortcode('change');
    }

    /**
     *
     *
     * @since   1.0.0
     * @package UsersWP
     * @return void
     */
    public function reset() {
        return $this->uwp_generate_shortcode('reset');
    }

    /**
     *
     *
     * @since   1.0.0
     * @package UsersWP
     * @return void
     */
    public function account() {
        return $this->uwp_generate_shortcode('account');
    }

    /**
     *
     *
     * @since   1.0.0
     * @package UsersWP
     * @return void
     */
    public function profile() {
        return $this->uwp_generate_shortcode('profile');
    }

    /**
     *
     *
     * @since   1.0.0
     * @package UsersWP
     * @return void
     */
    public function users() {
        return $this->uwp_generate_shortcode('users');
    }

    /**
     *
     *
     * @since   1.0.0
     * @package UsersWP
     * @return void
     */
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