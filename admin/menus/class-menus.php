<?php
/**
 * The nav menu specific functionality of the plugin.
 *
 * @link       http://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    userswp
 * @subpackage userswp/admin/menus
 */

/**
 * The Nav menu specific functionality of the plugin.
 *
 * @package    userswp
 * @subpackage userswp/admin/menus
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Menus {
    
    /**
     * Load new metabox for nav menu ui.
     *
     * @since 1.0.0
     * @return void
     */
    public function users_wp_admin_menu_metabox() {
        add_meta_box( 'add-users-wp-nav-menu', esc_html__( 'UsersWP Endpoints', 'userswp' ), array($this, 'users_wp_admin_do_wp_nav_menu_metabox'), 'nav-menus', 'side', 'default' );
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

        $walker = new UsersWP_Walker_Nav_Menu_Checklist( false );
        $args   = array( 'walker' => $walker );

        $post_type_name = 'users_wp';

        $tabs = array();

        $tabs['common']['label']  = __( 'Common', 'userswp' );
        $tabs['common']['pages']  = $this->users_wp_nav_menu_get_common_pages();

        $tabs['loggedin']['label']  = __( 'Logged-In', 'userswp' );
        $tabs['loggedin']['pages']  = $this->users_wp_nav_menu_get_loggedin_pages();

        $tabs['loggedout']['label']  = __( 'Logged-Out', 'userswp' );
        $tabs['loggedout']['pages']  = $this->users_wp_nav_menu_get_loggedout_pages();

        ?>

        <div id="users-wp-menu" class="posttypediv">
            <h4><?php esc_html_e( 'Common', 'userswp' ) ?></h4>
            <p><?php esc_html_e( 'Common links are visible to everyone.', 'userswp' ) ?></p>

            <div id="tabs-panel-posttype-<?php echo $post_type_name; ?>-common" class="tabs-panel tabs-panel-active">
                <ul id="users_wp-menu-checklist-common" class="categorychecklist form-no-clear">
                    <?php
                    if ($tabs['common']['pages']) {
                        echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $tabs['common']['pages'] ), 0, (object) $args );
                    }
                    ?>
                </ul>
            </div>
            <h4><?php esc_html_e( 'Logged-In', 'userswp' ) ?></h4>
            <p><?php esc_html_e( 'Logged-In links are not visible to visitors who are not logged in.', 'userswp' ) ?></p>

            <div id="tabs-panel-posttype-<?php echo $post_type_name; ?>-loggedin" class="tabs-panel tabs-panel-active">
                <ul id="users_wp-menu-checklist-loggedin" class="categorychecklist form-no-clear">
                    <?php
                    if ($tabs['loggedin']['pages']) {
                        echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $tabs['loggedin']['pages'] ), 0, (object) $args );
                    }
                    ?>
                </ul>
            </div>

            <h4><?php esc_html_e( 'Logged-Out', 'userswp' ) ?></h4>
            <p><?php esc_html_e( 'Logged-Out links are not visible to users who are logged in.', 'userswp' ) ?></p>

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
			    <span class="list-controls">
					<a href="<?php echo admin_url( 'nav-menus.php?page-tab=all&selectall=1#users-wp-menu' ); ?>" class="select-all"><?php _e( 'Select all', 'geodirectory' ); ?></a>
				</span>
                <span class="add-to-menu">
                    <input type="submit"<?php if ( function_exists( 'wp_nav_menu_disabled_check' ) ) : wp_nav_menu_disabled_check( $nav_menu_selected_id ); endif; ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu', 'userswp' ); ?>" name="add-custom-menu-item" id="submit-users-wp-menu" />
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
    public function users_wp_nav_menu_get_common_pages() {

        $users_page_data = uwp_get_page_url_data('users_page', 'array');

        $users_wp_menu_items = array();


        if (!empty($users_page_data)) {
            $users_wp_menu_items[] = $users_page_data;
        }


        $users_wp_menu_items = apply_filters( 'users_wp_nav_menu_get_common_pages', $users_wp_menu_items );

        $page_args = $this->users_wp_admin_wp_nav_menu_page_args($users_wp_menu_items);

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

        $account_page_data = uwp_get_page_url_data('account_page', 'array');
        $change_page_data = uwp_get_page_url_data('change_page', 'array');
        $profile_page_data = uwp_get_page_url_data('profile_page', 'array');

        $users_wp_menu_items = array();

        if (!empty($account_page_data)) {
            $users_wp_menu_items[] = $account_page_data;
        }

        if (!empty($change_page_data)) {
            $users_wp_menu_items[] = $change_page_data;
        }

        if (!empty($profile_page_data)) {
            $users_wp_menu_items[] = $profile_page_data;
        }

        $users_wp_menu_items[] = array(
            'name' => __( 'Log out', 'userswp' ),
            'slug' => 'logout',
            'link' => wp_login_url(),
        );

        $users_wp_menu_items = apply_filters( 'users_wp_nav_menu_get_loggedin_pages', $users_wp_menu_items );

        $page_args = $this->users_wp_admin_wp_nav_menu_page_args($users_wp_menu_items);

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

        $register_page_data = uwp_get_page_url_data('register_page', 'array');
        $login_page_data = uwp_get_page_url_data('login_page', 'array');
        $forgot_page_data = uwp_get_page_url_data('forgot_page', 'array');

        $users_wp_menu_items = array();

        if (!empty($register_page_data)) {
            $users_wp_menu_items[] = $register_page_data;
        }

        if (!empty($login_page_data)) {
            $users_wp_menu_items[] = $login_page_data;
        }

        if (!empty($forgot_page_data)) {
            $users_wp_menu_items[] = $forgot_page_data;
        }


        $users_wp_menu_items = apply_filters( 'users_wp_nav_menu_get_loggedout_pages', $users_wp_menu_items );

        $page_args = $this->users_wp_admin_wp_nav_menu_page_args($users_wp_menu_items);

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

    /**
     * Prepare items for nav menu page arguments
     *
     * @since 1.0.0
     * @return mixed
     */
    public function users_wp_admin_wp_nav_menu_page_args($users_wp_menu_items) {
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
     * Add UsersWP setting page link to the WP admin bar.
     *
     * @since 1.1.2
     * @return void
     */
    public function admin_bar_menu($wp_admin_bar){
        if ( current_user_can( 'manage_options' ) ) {
            $wp_admin_bar->add_menu( array(
                'parent' => 'appearance',
                'id'     => 'userswp',
                'title'  => __( 'UsersWP', 'userswp' ),
                'href'   => admin_url( 'admin.php?page=userswp' )
            ) );
        }
    }
}