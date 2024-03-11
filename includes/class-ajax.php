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

        $ajax_events = $this->get_ajax_events();

        foreach ( $ajax_events as $ajax_event => $nopriv ) {
            add_action( 'wp_ajax_uwp_' . $ajax_event, array( $this, $ajax_event ) );

            if ( $nopriv ) {
                add_action( 'wp_ajax_nopriv_uwp_' . $ajax_event, array( $this, $ajax_event ) );
            }
        }
    }

    public function get_ajax_events(){
        // uwp_event_name => nopriv
        return array(
            'notice_clear_try_bootstrap'   => false,
            'wizard_setup_menu'   => false,
        );
    }

    /**
     * A quick delete of the try bootstrap notice option.
     */
    public function notice_clear_try_bootstrap(){
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
        wp_die( $message, $title, array( 'response' => $status )); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

	/**
	 * Function to setup menu in the setup wizard.
	 *
	 * @return      void
	 */
	public function wizard_setup_menu() {

		check_ajax_referer( 'uwp-wizard-setup-menu', 'security' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$menu_id = isset($_REQUEST['menu_id']) ?  sanitize_title_with_dashes($_REQUEST['menu_id']) : '';
		$menu_location = isset($_REQUEST['menu_location']) ?  sanitize_title_with_dashes($_REQUEST['menu_location']) : '';
		$result = UsersWP_Tools::setup_menu( $menu_id, $menu_location);

		if(is_wp_error( $result ) ){
			wp_send_json_error( $result->get_error_message() );
		}else{
			wp_send_json_success($result);
		}

		wp_die();

	}
}