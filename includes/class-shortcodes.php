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
    
    public function __construct() {

        $this->templates = new Users_WP_Templates();

        add_shortcode( 'uwp_register',  array($this, 'register'));
        add_shortcode( 'uwp_login',     array($this, 'login'));
        add_shortcode( 'uwp_forgot',    array($this, 'forgot'));
        add_shortcode( 'uwp_change',    array($this, 'change'));
        add_shortcode( 'uwp_reset',     array($this, 'reset'));
        add_shortcode( 'uwp_account',   array($this, 'account'));
        add_shortcode( 'uwp_profile',   array($this, 'profile'));
        add_shortcode( 'uwp_users',     array($this, 'users'));
        

    }
    
    /**
     * Returns the UsersWP register page template content.
     *
     * @since       1.0.0
     * @package     UsersWP
     * @return      string     UsersWP template content.
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
     * Returns the UsersWP login page template content.
     *
     * @since       1.0.0
     * @package     UsersWP
     * @return      string     UsersWP template content.
     */
    public function login() {
        if (is_user_logged_in()) {
            return "";
        }
        return $this->uwp_generate_shortcode('login');
    }

    /**
     * Returns the UsersWP forgot password page template content.
     *
     * @since       1.0.0
     * @package     UsersWP
     * @return      string     UsersWP template content.
     */
    public function forgot() {
        return $this->uwp_generate_shortcode('forgot');
    }

    /**
     * Returns the UsersWP change password page template content.
     *
     * @since       1.0.0
     * @package     UsersWP
     * @return      string     UsersWP template content.
     */
    public function change() {
        return $this->uwp_generate_shortcode('change');
    }

    /**
     * Returns the UsersWP reset password page template content.
     *
     * @since       1.0.0
     * @package     UsersWP
     * @return      string     UsersWP template content.
     */
    public function reset() {
        return $this->uwp_generate_shortcode('reset');
    }

    /**
     * Returns the UsersWP account page template content.
     *
     * @since       1.0.0
     * @package     UsersWP
     * @return      string     UsersWP template content.
     */
    public function account() {
        return $this->uwp_generate_shortcode('account');
    }

    /**
     * Returns the UsersWP profile page template content.
     *
     * @since       1.0.0
     * @package     UsersWP
     * @return      string     UsersWP template content.
     */
    public function profile() {
        return $this->uwp_generate_shortcode('profile');
    }

    /**
     * Returns the UsersWP users page template content.
     *
     * @since       1.0.0
     * @package     UsersWP
     * @return      string     UsersWP template content.
     */
    public function users() {
        return $this->uwp_generate_shortcode('users');
    }

    /**
     * Returns the UsersWP template content based on type.
     *
     * @since       1.0.0
     * @package     UsersWP
     * @param       string      $type       Template type.
     * @return      string                  UsersWP template content.
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
new Users_WP_Shortcodes;