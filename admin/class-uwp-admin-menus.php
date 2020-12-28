<?php
/**
 * Setup menus in WP admin.
 *
 * @author   GeoDirectory Team <info@wpgeodirectory.com>
 * @category Admin
 * @package  userswp/Admin
 * @version  1.0.24
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * UsersWP_Admin_Menus Class.
 */
class UsersWP_Admin_Menus {

    /**
     * Hook in tabs.
     */
    public function __construct() {
        // Add menus
        add_action( 'admin_menu', array( $this, 'admin_menu' ), 9 );
	    add_action( 'admin_menu', array( $this, 'tools_menu' ), 80 );
        add_action( 'admin_menu', array( $this, 'status_menu' ), 90 );
        add_action( 'admin_menu', array( $this, 'addons_menu' ), 99 );
    }

    /**
     * Add menu items.
     */
    public function admin_menu() {

        $install_type = uwp_get_installation_type();

        // Proceed if main site or pages on all sites or specific blog id
        $proceed = false;
        $show_builder = false;
        switch ( $install_type ) {
            case "single":
	        case "multi_na_all":
	        case "multi_not_na":
                $proceed = true;
                $show_builder = true;
                break;
            case "multi_na_site_id":
	            $blog_id = null;
	            if (defined( 'UWP_ROOT_PAGES' )) {
		            $blog_id = UWP_ROOT_PAGES;
	            }

	            $current_blog_id = get_current_blog_id();
	            if ( !empty($blog_id) && is_int( (int)$blog_id ) && $blog_id == $current_blog_id  ) {
		            $proceed = true;
		            $show_builder = true;
	            }
                break;
            case "multi_na_default":
                $is_main_site = is_main_site();
                if ( $is_main_site ) {
                    $proceed = true;
                    $show_builder = true;
                }
                break;
            default:
                $proceed = false;

        }

        if ( ! $proceed ) {
            return;
        }


        add_menu_page(
            __( 'UsersWP Settings', 'userswp' ),
            __( 'UsersWP', 'userswp' ),
            'manage_options',
            'userswp',
            array( 'UsersWP_Admin_Settings', 'output' ),
            'dashicons-groups',
            70
        );

        if ( $show_builder ) {
            add_submenu_page(
                "userswp",
                __( 'Form Builder', 'userswp' ),
                __( 'Form Builder', 'userswp' ),
                'manage_options',
                'uwp_form_builder',
                array( 'UsersWP_Form_Builder', 'output' )
            );
        }

    }

    /**
     * Add menu item.
     */
    public function tools_menu() {
        add_submenu_page(
            "userswp",
            __('UsersWP Tools', 'userswp'),
            __('Tools', 'userswp'),
            'manage_options',
            'uwp_tools',
            array( $this, 'tools_page' )
        );
    }

    /**
     * Add menu item.
     */
    public function status_menu() {
        add_submenu_page(
            "userswp",
            __('Status', 'userswp'),
            __('Status', 'userswp'),
            'manage_options',
            'uwp_status',
            array( $this, 'status_page' )
        );
    }

    /**
     * Add menu item.
     */
    public function addons_menu() {
        if ( !apply_filters( 'uwp_show_addons_page', true ) ) {
            return;
        }

        add_submenu_page(
            "userswp",
            __( 'UsersWP Extensions', 'userswp' ),
            __( 'Extensions', 'userswp' ),
            'manage_options',
            'uwp-addons',
            array( $this, 'addons_page' )
        );
    }

	/**
	 * Init the status page.
	 */
	public function tools_page() {
		UsersWP_Tools::output();
	}

    /**
     * Init the status page.
     */
    public function status_page() {
        UsersWP_Status::output();
    }

    /**
     * Init the status page.
     */
    public function addons_page() {
        $addon_obj = new UsersWP_Admin_Addons();
        $addon_obj->output();
    }

}