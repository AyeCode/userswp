<?php
/**
 * Personal data erasers.
 */

defined( 'ABSPATH' ) || exit;

/**
 * UsersWP_Privacy_Erasers Class.
 */
class UsersWP_Privacy_Erasers {
    /**
     * Finds and erase user data by email address.
     *
     * @since 1.0.14
     * @param string $email_address The user email address.
     * @param int    $page  Page.
     * @return array An array of user data in name value pairs
     */
    public static function user_data_eraser( $email_address, $page ) {
        $response = array(
            'items_removed'  => false,
            'items_retained' => false,
            'messages'       => array(),
            'done'           => true,
        );

        $user = get_user_by( 'email', $email_address );

        if ( ! $user instanceof WP_User ) {
            return $response;
        }

        if ( $user && $user->ID ) {
            uwp_delete_usermeta_row($user->ID);
            $response['items_removed'] = true;
        }

        /**
         * Allow extensions to remove data for this user and adjust the response.
         *
         * @since 1.0.14
         * @param array    $response Array resonse data. Must include messages, num_items_removed, num_items_retained, done.
         * @param WP_User $user user object.
         */
        return apply_filters( 'uwp_privacy_erase_personal_data_user', $response, $user );
    }

}
