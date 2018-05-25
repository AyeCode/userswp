<?php
/**
 * Shortcode related functions
 *
 * This class defines all code necessary for UsersWP shortcodes.
 *
 * @since      1.0.0
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Shortcodes {

    private $templates;
    
    public function __construct($templates) {
        $this->templates = $templates;
    }
    
    /**
     * Returns the UsersWP register page template content.
     *
     * @since       1.0.0
     * @package     userswp
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
     * @package     userswp
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
     * @package     userswp
     * @return      string     UsersWP template content.
     */
    public function forgot() {
        return $this->uwp_generate_shortcode('forgot');
    }

    /**
     * Returns the UsersWP change password page template content.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      string     UsersWP template content.
     */
    public function change() {
        return $this->uwp_generate_shortcode('change');
    }

    /**
     * Returns the UsersWP reset password page template content.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      string     UsersWP template content.
     */
    public function reset() {
        return $this->uwp_generate_shortcode('reset');
    }

    /**
     * Returns the UsersWP account page template content.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      string     UsersWP template content.
     */
    public function account() {
        return $this->uwp_generate_shortcode('account');
    }

    /**
     * Returns the UsersWP profile page template content.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      string     UsersWP template content.
     */
    public function profile() {
        return $this->uwp_generate_shortcode('profile');
    }

    /**
     * Returns the UsersWP users page template content.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      string     UsersWP template content.
     */
    public function users() {
        return $this->uwp_generate_shortcode('users');
    }

    /**
     * Returns the UsersWP template content based on type.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string      $type       Template type.
     * @return      string                  UsersWP template content.
     */
    public function uwp_generate_shortcode($type = 'register') {

        $template = $this->templates->uwp_locate_template($type);

        ob_start();
        echo '<div class="uwp_page">';
        if ($template) {
            include($template);
        }
        echo '</div>';
        $output = ob_get_contents();
        ob_end_clean();

        return trim($output);
    }

}