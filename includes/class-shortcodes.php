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