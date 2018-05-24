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
        $this->add_eraser( 'uwp-customer-data', __( 'UsersWP User Data', 'woocommerce' ), array( 'UsersWP_Privacy_Erasers', 'user_data_eraser' ) );
    }

    /**
     * Add privacy policy content for the privacy policy page.
     *
     * @since 1.0.14
     */
    public function get_privacy_message() {
        $content = '
			<div contenteditable="false">' .
            '<p class="wp-policy-help">' .
            __( 'UsersWP uses the following privacy.', 'userswp' ) .
            '</p>' .
            '</div>';

        return apply_filters( 'uwp_privacy_policy_content', $content );
    }

}

new UsersWP_Privacy();
