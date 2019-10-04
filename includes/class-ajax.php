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
        add_action( 'init', array( $this, 'add_ajax_events' ), 0 );
    }
    
    /**
     * Handles the create custom field and sort custom field ajax requests.
     *
     * @since       1.0.0
     * @package     userswp
     * @return      void
     */
    public function add_ajax_events()
    {
        // uwp_event_name => nopriv
        $ajax_events = array(
            'notice_clear_try_bootstrap'   => false,
        );

        foreach ( $ajax_events as $ajax_event => $nopriv ) {
            add_action( 'wp_ajax_uwp_' . $ajax_event, array( __CLASS__, $ajax_event ) );

            if ( $nopriv ) {
                add_action( 'wp_ajax_nopriv_uwp_' . $ajax_event, array( __CLASS__, $ajax_event ) );
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