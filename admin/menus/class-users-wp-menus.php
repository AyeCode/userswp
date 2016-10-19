<?php
/**
 * The nav menu specific functionality of the plugin.
 *
 * @link       http://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    Users_WP
 * @subpackage Users_WP/admin/menus
 */

/**
 * The Nav menu specific functionality of the plugin.
 *
 * @package    Users_WP
 * @subpackage Users_WP/admin/menus
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class Users_WP_Menus {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {

        $this->load_dependencies();
    }

    private function load_dependencies() {
        require_once dirname( __FILE__ ) . '/class-users-wp-menu-checklist.php';

    }

    /**
     * Load new metabox for nav menu ui.
     *
     * @since 1.0.0
     * @return void
     */
    public function users_wp_admin_menu_metabox() {
        add_meta_box( 'add-users-wp-nav-menu', esc_html__( 'Users WP', 'uwp' ), array($this, 'users_wp_admin_do_wp_nav_menu_metabox'), 'nav-menus', 'side', 'default' );
        add_action( 'admin_print_footer_scripts', array($this, 'users_wp_admin_wp_nav_menu_restrict_items') );
    }

    /**
     * Build and populate the users_wp metabox into the menu manager ui.
     *
     * @since 1.0.0
     * @return void
     */
    public function users_wp_admin_do_wp_nav_menu_metabox() {

        global $nav_menu_selected_id;

        $walker = new Users_WP_Walker_Nav_Menu_Checklist( false );
        $args   = array( 'walker' => $walker );

        $post_type_name = 'users_wp';

        $tabs = array();

        $tabs['common']['label']  = __( 'Common', 'uwp' );
        $tabs['common']['pages']  = $this->users_wp_nav_menu_get_common_pages();

        $tabs['loggedin']['label']  = __( 'Logged-In', 'uwp' );
        $tabs['loggedin']['pages']  = $this->users_wp_nav_menu_get_loggedin_pages();

        $tabs['loggedout']['label']  = __( 'Logged-Out', 'uwp' );
        $tabs['loggedout']['pages']  = $this->users_wp_nav_menu_get_loggedout_pages();

        ?>

        <div id="users-wp-menu" class="posttypediv">
            <h4><?php esc_html_e( 'Common', 'uwp' ) ?></h4>
            <p><?php esc_html_e( 'Common links are visible to everyone.', 'uwp' ) ?></p>

            <div id="tabs-panel-posttype-<?php echo $post_type_name; ?>-common" class="tabs-panel tabs-panel-active">
                <ul id="users_wp-menu-checklist-common" class="categorychecklist form-no-clear">
                    <?php
                    if ($tabs['common']['pages']) {
                        echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $tabs['common']['pages'] ), 0, (object) $args );
                    }
                    ?>
                </ul>
            </div>
            <h4><?php esc_html_e( 'Logged-In', 'uwp' ) ?></h4>
            <p><?php esc_html_e( 'Logged-In links are not visible to visitors who are not logged in.', 'uwp' ) ?></p>

            <div id="tabs-panel-posttype-<?php echo $post_type_name; ?>-loggedin" class="tabs-panel tabs-panel-active">
                <ul id="users_wp-menu-checklist-loggedin" class="categorychecklist form-no-clear">
                    <?php
                    if ($tabs['loggedin']['pages']) {
                        echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $tabs['loggedin']['pages'] ), 0, (object) $args );
                    }
                    ?>
                </ul>
            </div>

            <h4><?php esc_html_e( 'Logged-Out', 'uwp' ) ?></h4>
            <p><?php esc_html_e( 'Logged-Out links are not visible to users who are logged in.', 'uwp' ) ?></p>

            <div id="tabs-panel-posttype-<?php echo $post_type_name; ?>-loggedin" class="tabs-panel tabs-panel-active">
                <ul id="users_wp-menu-checklist-loggedin" class="categorychecklist form-no-clear">
                    <?php
                    if ($tabs['loggedout']['pages']) {
                        echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $tabs['loggedout']['pages'] ), 0, (object) $args );
                    }
                    ?>
                </ul>
            </div>

            <p class="button-controls">
			<span class="add-to-menu">
				<input type="submit"<?php if ( function_exists( 'wp_nav_menu_disabled_check' ) ) : wp_nav_menu_disabled_check( $nav_menu_selected_id ); endif; ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu', 'uwp' ); ?>" name="add-custom-menu-item" id="submit-users-wp-menu" />
				<span class="spinner"></span>
			</span>
            </p>
        </div><!-- /#users_wp-menu -->

        <?php

    }

    /**
     * Create a fake post object for the wp menu manager.
     * This function creates the list of Logged-In only pages,
     * for the admin menu manager.
     *
     * @since 1.0.0
     * @return mixed A URL or an array of pages.
     */
    public function
    users_wp_nav_menu_get_common_pages() {

        global $uwp_options;
        $user_profile_page = esc_attr( get_option('users_wp_user_profile_page', false));
        $users_list_page = esc_attr( get_option('users_wp_users_list_page', false));

        $users_wp_menu_items = array();

        if ($user_profile_page) {
            $page = get_post( $user_profile_page );
            $users_wp_menu_items[] = array(
                'name' => $page->post_title,
                'slug' => $page->post_name,
                'link' => get_permalink( $page->ID ),
            );
        }

        if ($users_list_page) {
            $page = get_post( $users_list_page );
            $users_wp_menu_items[] = array(
                'name' => $page->post_title,
                'slug' => $page->post_name,
                'link' => get_permalink( $page->ID ),
            );
        }


        $users_wp_menu_items = apply_filters( 'users_wp_nav_menu_get_common_pages', $users_wp_menu_items );

        // If there's nothing to show, we're done
        if ( count( $users_wp_menu_items ) < 1 ) {
            return false;
        }

        $page_args = array();

        foreach ( $users_wp_menu_items as $users_wp_item ) {
            $page_args[ $users_wp_item['slug'] ] = (object) array(
                'ID'             => -1,
                'post_title'     => $users_wp_item['name'],
                'post_author'    => 0,
                'post_date'      => 0,
                'post_excerpt'   => $users_wp_item['slug'],
                'post_type'      => 'page',
                'post_status'    => 'publish',
                'comment_status' => 'closed',
                'guid'           => $users_wp_item['link']
            );
        }

        return $page_args;

    }

    /**
     * Create a fake post object for the wp menu manager.
     * This function creates the list of Logged-In only pages,
     * for the admin menu manager.
     *
     * @since 1.0.0
     * @return mixed A URL or an array of pages.
     */
    public function users_wp_nav_menu_get_loggedin_pages() {

        $account_page = esc_attr( get_option('users_wp_account_page', false));


        $users_wp_menu_items = array();

        if ($account_page) {
            $page = get_post( $account_page );
            $users_wp_menu_items[] = array(
                'name' => $page->post_title,
                'slug' => $page->post_name,
                'link' => get_permalink( $page->ID ),
            );
        }

        $users_wp_menu_items[] = array(
            'name' => __( 'Log out', 'uwp' ),
            'slug' => 'logout',
            'link' => wp_login_url(),
        );

        $users_wp_menu_items = apply_filters( 'users_wp_nav_menu_get_loggedin_pages', $users_wp_menu_items );

        // If there's nothing to show, we're done
        if ( count( $users_wp_menu_items ) < 1 ) {
            return false;
        }

        $page_args = array();

        foreach ( $users_wp_menu_items as $users_wp_item ) {
            $page_args[ $users_wp_item['slug'] ] = (object) array(
                'ID'             => -1,
                'post_title'     => $users_wp_item['name'],
                'post_author'    => 0,
                'post_date'      => 0,
                'post_excerpt'   => $users_wp_item['slug'],
                'post_type'      => 'page',
                'post_status'    => 'publish',
                'comment_status' => 'closed',
                'guid'           => $users_wp_item['link']
            );
        }

        return $page_args;

    }

    /**
     * Create a fake post object for the wp menu manager.
     * This function creates the list of Logged-Out only pages,
     * for the admin menu manager.
     *
     * @since 1.0.0
     * @return mixed A URL or an array of pages.
     */
    public function users_wp_nav_menu_get_loggedout_pages() {

        $register_page = esc_attr( get_option('users_wp_register_page', false));
        $login_page = esc_attr( get_option('users_wp_login_page', false));
        $forgot_pass_page = esc_attr( get_option('users_wp_forgot_pass_page', false));

        $users_wp_menu_items = array();

        if ($register_page) {
            $page = get_post( $register_page );
            $users_wp_menu_items[] = array(
                'name' => $page->post_title,
                'slug' => $page->post_name,
                'link' => get_permalink( $page->ID ),
            );
        }

        if ($login_page) {
            $page = get_post( $login_page );
            $users_wp_menu_items[] = array(
                'name' => $page->post_title,
                'slug' => $page->post_name,
                'link' => get_permalink( $page->ID ),
            );
        }

        if ($forgot_pass_page) {
            $page = get_post( $forgot_pass_page );
            $users_wp_menu_items[] = array(
                'name' => $page->post_title,
                'slug' => $page->post_name,
                'link' => get_permalink( $page->ID ),
            );
        }

        $users_wp_menu_items = apply_filters( 'users_wp_nav_menu_get_loggedout_pages', $users_wp_menu_items );

        // If there's nothing to show, we're done
        if ( count( $users_wp_menu_items ) < 1 ) {
            return false;
        }

        $page_args = array();

        foreach ( $users_wp_menu_items as $users_wp_item ) {
            $page_args[ $users_wp_item['slug'] ] = (object) array(
                'ID'             => -1,
                'post_title'     => $users_wp_item['name'],
                'post_author'    => 0,
                'post_date'      => 0,
                'post_excerpt'   => $users_wp_item['slug'],
                'post_type'      => 'page',
                'post_status'    => 'publish',
                'comment_status' => 'closed',
                'guid'           => $users_wp_item['link']
            );
        }

        return $page_args;

    }

    /**
     * Restrict various items from view if editing a users_wp menu.
     *
     * @since 1.0.0
     * @return void
     */
    public function users_wp_admin_wp_nav_menu_restrict_items() {
        ?>
        <script type="text/javascript">
            jQuery( '#menu-to-edit').on( 'click', 'a.item-edit', function() {
                var settings  = jQuery(this).closest( '.menu-item-bar' ).next( '.menu-item-settings' );
                var css_class = settings.find( '.edit-menu-item-classes' );

                if( css_class.val().match("^users_wp-") ) {
                    css_class.attr( 'readonly', 'readonly' );
                    settings.find( '.field-url' ).css( 'display', 'none' );
                }
            });
        </script>
        <?php
    }
}
