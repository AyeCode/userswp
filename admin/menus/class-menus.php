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

        <div id="users-wp-endpoints" class="posttypediv">
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

            <p class="button-controls wp-clearfix" data-items-type="users-wp-endpoints">
                <span class="list-controls hide-if-no-js">
                    <input type="checkbox" id="users-wp-endpoints-tab" class="select-all">
                    <label for="users-wp-endpoints-tab"><?php _e( 'Select all', 'userswp' ); ?></label>
                </span>

                <span class="add-to-menu">
                    <input type="submit" class="button submit-add-to-menu right" value="<?php esc_attr_e( 'Add to menu', 'userswp' ); ?>" name="add-users-wp-endpoints-item" id="submit-users-wp-endpoints">
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
            $users_wp_menu_items['users'] = $users_page_data;
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
            $users_wp_menu_items['account'] = $account_page_data;
        }

        if (!empty($change_page_data)) {
            $users_wp_menu_items['change'] = $change_page_data;
        }

        if (!empty($profile_page_data)) {
            $users_wp_menu_items['profile'] = $profile_page_data;
        }

        $users_wp_menu_items['logout'] = array(
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
            $users_wp_menu_items['register'] = $register_page_data;
        }

        if (!empty($login_page_data)) {
            $users_wp_menu_items['login'] = $login_page_data;
        }

        if (!empty($forgot_page_data)) {
            $users_wp_menu_items['forgot'] = $forgot_page_data;
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

//	    print_r($users_wp_menu_items);

        $page_args = array();

        foreach ( $users_wp_menu_items as $type => $users_wp_item ) {
            $page_args[ $users_wp_item['slug'] ] = (object) array(
                'ID'             => -1,
                'post_title'     => $users_wp_item['name'],
                'post_author'    => 0,
                'post_date'      => 0,
                'post_excerpt'   => $users_wp_item['slug'],
                'post_type'      => 'page',
                'post_status'    => 'publish',
                'comment_status' => 'closed',
                'guid'           => $users_wp_item['link'],
                'lightbox_class' => "users-wp-$type-nav",
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
        if ( !is_admin() && current_user_can( 'manage_options' ) ) {
            $wp_admin_bar->add_node( array(
                'parent' => 'appearance',
                'id'     => 'userswp',
                'title'  => __( 'UsersWP', 'userswp' ),
                'href'   => admin_url( 'admin.php?page=userswp' )
            ) );
        }
    }

	/**
	 * Returns endpoints for UWP menu in setup wizard.
	 *
	 * @since 1.2.0.13
	 * @return array
	 */
	public function get_endpoints() {
		$items = array();
		$items['pages'] = array();
		$loop_index = 999;
		$uwp_pages = array('users_page','profile_page','register_page','login_page','account_page','forgot_page','logout');
		if( !empty( $uwp_pages) ) {
			foreach ( $uwp_pages as $page ) {
				if( !empty( $page ) && 'logout' != $page ) {
					$page_data = uwp_get_page_url_data($page, 'array');
				} else{
					$page_data = array(
						'name' => __( 'Log out', 'userswp' ),
						'slug' => 'logout',
						'link' => wp_login_url(),
					);
				}
				$page_class = !empty( $page ) ? str_replace('_page','',$page) : '';
				$item = new stdClass();
				$item->object =  'custom';
				$item->menu_item_parent = 0;
				$item->type = 'custom';
				$item->title = $page_data['name'];
				$item->url = $page_data['link'];
				$item->target = '';
				$item->attr_title = '';
				$item->classes = array("users-wp-menu users-wp-$page_class-nav");
				$item->xfn = '';
				$items['pages'][] = $item;
			}
		}

		return apply_filters( 'uwp_menu_items', $items,$loop_index );
	}
}