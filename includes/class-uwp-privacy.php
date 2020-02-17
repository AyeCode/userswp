<?php
/**
 * Privacy/GDPR related functionality which ties into WordPress functionality.
 */

defined( 'ABSPATH' ) || exit;

/**
 * UsersWP_Privacy Class.
 */
class UsersWP_Privacy extends UsersWP_Abstract_Privacy {

    /**
     * Init - hook into events.
     */
    public function __construct() {
        parent::__construct( __( 'UsersWP', 'userswp' ) );

        // Include supporting classes.
        include_once 'class-uwp-privacy-exporters.php';
        include_once 'class-uwp-privacy-erasers.php';

        // This hook registers userswp data exporters.
        $this->add_exporter( 'uwp-customer-data', __( 'UsersWP User Data', 'userswp' ), array( 'UsersWP_Privacy_Exporters', 'user_data_exporter' ) );

        // This hook registers userswp data erasers.
        $this->add_eraser( 'uwp-customer-data', __( 'UsersWP User Data', 'userswp' ), array( 'UsersWP_Privacy_Erasers', 'user_data_eraser' ) );
    }

    /**
     * Add privacy policy content for the privacy policy page.
     *
     * @since 1.0.14
     */
    public function get_privacy_message() {
        $content = '<div class="wp-suggested-text">'.
                   '<h2>' . __( 'User data/profile', 'userswp' ) . '</h2>' .
                   '<p class="privacy-policy-tutorial">' . __( 'Example privacy texts.', 'userswp' ) . '</p>' .
                   '<p>' . __( 'We collect information about you during the registration and edit profile process on our site. This information may include, but is not limited to, your name, email address, phone number, address, IP and any other details that might be requested from you for the purpose of building your public profile.', 'userswp' ) . '</p>' .
                   '<p>' . __( 'Handling this data also allows us to:', 'userswp' ) . '</p>' .
                   '<ul>' .
                   '<li>' . __( '- Send you important account/order/service information.', 'userswp' ) . '</li>' .
                   '<li>' . __( '- Display this information in a public facing manner (such as a web page or API request) and allow website users to search and view submitted information.', 'userswp' ) . '</li>' .
                   '<li>' . __( '- Respond to your queries or complaints.', 'userswp' ) . '</li>' .
                   '<li>' . __( '- Set up and administer your account, provide technical and/or customer support, and to verify your identity. We do this on the basis of our legitimate business interests.', 'userswp' ) . '</li>' .
                   '</ul>' .
                   '<p>' . __( 'Any profile information provided to this site may be displayed publicly.', 'userswp' ) . '</p>'.
                   '</div>';

        return apply_filters( 'uwp_privacy_policy_content', $content );
    }

}

new UsersWP_Privacy();
