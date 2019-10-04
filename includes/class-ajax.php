<?php
/**
 * Ajax related functions
 *
 * This class defines all code necessary to handle UsersWP ajax requests.
 *
 * @since      1.0.0
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Ajax {
    
    public function __construct() {
        add_action( 'init', array( $this, 'handler' ), 0 );
    }
    
    /**
     * Handles the create custom field and sort custom field ajax requests.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
    public function handler()
    {


        // EVENT => nopriv
        $ajax_events = array(
            'notice_clear_try_bootstrap'   => false,
        );

        foreach ( $ajax_events as $ajax_event => $nopriv ) {
            add_action( 'wp_ajax_uwp_' . $ajax_event, array( __CLASS__, $ajax_event ) );

            if ( $nopriv ) {
                add_action( 'wp_ajax_nopriv_uwp_' . $ajax_event, array( __CLASS__, $ajax_event ) );
            }
        }


        // @todo this need updated to new style above.
        //if ((isset($_REQUEST['uwp_ajax']) && $_REQUEST['uwp_ajax'] == 'admin_ajax') || isset($_REQUEST['create_field']) || isset($_REQUEST['sort_create_field'])) {
        if ((isset($_REQUEST['uwp_ajax']) && $_REQUEST['uwp_ajax'] == 'admin_ajax')) {
            if (current_user_can('manage_options')) {
                if (isset($_REQUEST['create_field'])) {
                    $this->create_field($_REQUEST);
                    $this->uwp_die();
                }
            } else {
                $login_page = uwp_get_page_id('login_page', false);
                if ($login_page) {
                    wp_redirect(get_permalink($login_page));
                } else {
                    wp_redirect(home_url('/'));
                }
                $this->uwp_die();
            }
        }
    }

    /**
     * A quick delete of the try bootstrap notice option.
     */
    public static function notice_clear_try_bootstrap(){
        if (current_user_can('manage_options')) {
            delete_option("uwp_notice_try_bootstrap");
        }
    }

    /**
     * Kill WordPress execution and display HTML message with error message.
     *
     * @since       1.0.0
     * @package     userswp
     * @param       string      $message    Optional. Error message.
     * @param       string      $title      Optional. Error title.
     * @param       int         $status     The HTTP response code. Default 200 for Ajax requests, 500 otherwise.
     * @return      void
     */
    public function uwp_die( $message = '', $title = '', $status = 400 ) {
        add_filter( 'wp_die_ajax_handler', '_uwp_die_handler', 10, 3 );
        add_filter( 'wp_die_handler', '_uwp_die_handler', 10, 3 );
        wp_die( $message, $title, array( 'response' => $status ));
    }
}