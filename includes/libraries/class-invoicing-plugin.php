<?php
/**
 * Invoicing plugin related functions
 *
 * This class defines all code necessary for Invoicing plugin.
 *
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Invoicing_Plugin {
    private static $instance;

    public static function get_instance() {
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof UsersWP_Invoicing_Plugin ) ) {
            self::$instance = new UsersWP_Invoicing_Plugin;
            self::$instance->setup_actions();
        }

        return self::$instance;
    }

    public function __construct() {
        self::$instance = $this;
    }

	/**
	 * Setup action hooks
	 */
    private function setup_actions() {
        if ( is_admin() ) {
	        add_filter( 'uwp_profile_tabs_predefined_fields', array( $this, 'add_profile_tabs_predefined_fields' ), 10, 2 );
	        add_filter( 'uwp_exclude_privacy_settings_tabs', array( $this, 'exclude_privacy_settings' ) );
        } else {
            add_action( 'uwp_profile_invoices_tab_content', array( $this, 'add_profile_invoices_tab_content' ) );
            add_action( 'uwp_dashboard_links', array( $this, 'dashboard_output' ), 10, 2 );

        }

        do_action( 'uwp_wpi_setup_actions', $this );
    }

	/**
	 * Add quick links to the logged in Dashboard.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       array     $links    Links to output
	 * @param       string     $args    Arguments
	 *
	 * @return      array
	 */
    public function dashboard_output($links, $args){

        // check its not disabled
        if(empty($args['disable_wpi'])){

            // My invoices
            //if (in_array('invoices', $allowed_tabs) && (get_current_user_id() == $user->ID)) {
            $user_id = get_current_user_id();
                $i_counts = $this->invoice_count($user_id);
                if($i_counts > 0) {
                    $links['wpi_invoicing'][] = array(
                        'optgroup' => 'open',
                        'text' => esc_attr( __( 'My Invoices', 'userswp' ) )
                    );
                    $links['wpi_invoicing'][] = array(
                        'url' => uwp_build_profile_tab_url( $user_id, 'invoices', '' ),
                        'text' => esc_attr( __( 'View Invoices', 'userswp' ) )
                    );
                    $links['wpi_invoicing'][] = array(
                        'optgroup' => 'close',
                    );
                }
            //}

        }

        return $links;
    }

	/**
	 * Adds predefined field in for profile tabs.
	 *
	 * @package     userswp
	 *
	 * @param       array     $fields            Predefined field array.
	 * @param       string    $form_type          Form type.
	 *
	 * @return      array    $fields    Predefined field array.
	 */
    public function add_profile_tabs_predefined_fields($fields, $form_type){
        if('profile-tabs' != $form_type){
            return $fields;
        }

	    $fields[] = array(
		    'tab_type'   => 'standard',
		    'tab_name'   => __('Invoices','userswp'),
		    'tab_icon'   => 'fas fa-file-invoice',
		    'tab_key'    => 'invoices',
		    'tab_content'=> '[wpinv_history]',
		    'tab_privacy' => '2',
		    'user_decided' => '0',
	    );

        $fields[] = array(
            'tab_type'   => 'standard',
            'tab_name'   => __('Subscriptions','userswp'),
            'tab_icon'   => 'fas fa-dollar-sign',
            'tab_key'    => 'invoice_subscriptions',
            'tab_content'=> '[wpinv_subscriptions]',
            'tab_privacy' => '2',
            'user_decided' => '0',
        );

        if(defined('WPINV_QUOTES_VERSION')){
            $fields[] = array(
                'tab_type'   => 'standard',
                'tab_name'   => __('Quotes','userswp'),
                'tab_icon'   => 'fas fa-file-invoice',
                'tab_key'    => 'quotes',
                'tab_content'=> '[wpinv_quote_history]',
                'tab_privacy' => '2',
                'user_decided' => '0',
            );
        }


        return $fields;
    }

    public function exclude_privacy_settings($tabs) {

        $tabs[] = 'invoices';
        $tabs[] = 'invoice_subscriptions';
        $tabs[] = 'quotes';

        return $tabs;
    }

    /**
     * Adds Invoices tab content.
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @param       object    $user             User object.
     *
     * @return      void
     */
    public function add_profile_invoices_tab_content($user) {
        if(is_user_logged_in() && isset($user->ID) && get_current_user_id() == $user->ID){
	        echo do_shortcode('[wpinv_history]');
        }
    }

	/**
	 * Returns invoices count
	 *
	 * @since       1.0.0
	 * @package     userswp
	 *
	 * @param       int     $user_id    User ID
	 *
	 * @return      int
	 */
    public function invoice_count($user_id) {
        global $wpdb;

        $post_status_array = array_keys(wpinv_get_invoice_statuses());
        $post_status = "'" . implode("','", $post_status_array) . "'";

        $count = $wpdb->get_var('
                 SELECT COUNT(ID)
                 FROM ' . $wpdb->posts. '
                 WHERE post_author = "' . $user_id . '"
                 AND post_status IN ('.$post_status.')
                 AND post_type = "wpi_invoice"'
        );
        return $count;
    }
}
$userswp_wpinv = UsersWP_Invoicing_Plugin::get_instance();