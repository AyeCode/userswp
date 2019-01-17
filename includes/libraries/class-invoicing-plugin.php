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
            self::$instance->setup_globals();
            self::$instance->includes();
            self::$instance->setup_actions();
        }

        return self::$instance;
    }

    private function __construct() {
        self::$instance = $this;
    }

    private function setup_globals() {
        
    }
    
    private function setup_actions() {
        if ( is_admin() ) {
            add_filter( 'uwp_available_tab_items', array( $this, 'available_tab_items' ) );
        } else {
            add_filter( 'uwp_profile_tabs', array( $this, 'add_profile_tabs' ), 10, 3 );
            add_action( 'uwp_profile_invoices_tab_content', array( $this, 'add_profile_invoices_tab_content' ) );
            add_action( 'uwp_dashboard_links', array( $this, 'dashboard_output' ),10,2 );

        }

        do_action( 'uwp_wpi_setup_actions', $this );
    }

    /**
     * Add GD quick links to the logged in Dashboard.
     *
     * @param $args
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

    private function includes() {
        do_action( 'uwp_gd_include_files' );

        if ( is_admin() ) {
            do_action( 'uwp_gd_include_admin_files' );
        }
    }

    /**
     * Registers the current addon tab items in "Choose the tabs to display in UsersWP Profile" setting.
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @param       array     $tabs_arr    Existing tabs array.
     *
     * @return      array     Tabs array.
     */
    public function available_tab_items( $tabs_arr ) {
        $tabs_arr['invoices'] = __( 'Invoices', 'userswp' );

        return $tabs_arr;
    }

    /**
     * Adds tab in user profile page.
     *
     * @since       1.0.0
     * @package     userswp
     *
     * @param       array     $tabs             Existing tabs array.
     * @param       object    $user             User object.
     * @param       array     $allowed_tabs     Allowed tabs array.
     *
     * @return      array     Tabs array.
     */
    function add_profile_tabs($tabs, $user,$allowed_tabs) {

        if (in_array('invoices', $allowed_tabs) && (get_current_user_id() == $user->ID)) {
            $i_counts = $this->invoice_count($user->ID);
            if($i_counts > 0) {
                $tabs['invoices'] = array(
                    'title' => __('Invoices', 'userswp'),
                    'count' => $i_counts
                );
            }
        }

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
        $this->profile_gd_invoices_content($user);
    }

    public function profile_gd_invoices_content($user) {
        if (!is_user_logged_in()) {
            return;
        }

        if (get_current_user_id() != $user->ID) {
            return;
        }
        ?>
        <h3><?php echo __('Invoices', 'userswp'); ?></h3>

        <div class="uwp-profile-item-block">
            <?php
            $paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

            $args = array(
                'post_type' => 'wpi_invoice',
                'post_status' => array_keys(wpinv_get_invoice_statuses()),
                'posts_per_page' => uwp_get_option('profile_no_of_items', 10),
                'author' => $user->ID,
                'paged' => $paged,
            );
            // The Query
            $the_query = new WP_Query($args);

            do_action('uwp_before_profile_invoice_items', $user);

            // The Loop
            if ($the_query->have_posts()) {
                echo '<ul class="uwp-profile-item-ul">';
                while ($the_query->have_posts()) {
                    $the_query->the_post();
                    $wpi_invoice = new WPInv_Invoice( get_the_ID() );
                    do_action('uwp_before_profile_invoice_item', $wpi_invoice, $user);
                    ?>
                    <li class="uwp-profile-item-li uwp-profile-item-clearfix">
                        <?php do_action('uwp_before_profile_invoice_title', $wpi_invoice, $user); ?>
                        <h3 class="uwp-profile-item-title">
                            <a href="<?php echo get_the_permalink(); ?>"><?php _e('Invoice','userswp');?> <?php echo get_the_title(); ?></a>
                        </h3>
                        <?php do_action('uwp_after_profile_invoice_title', $wpi_invoice, $user); ?>
                        <?php do_action('uwp_before_profile_invoice_date', $wpi_invoice, $user); ?>
                        <time class="uwp-profile-item-time published" datetime="<?php echo get_the_time('c'); ?>">
                            <?php echo get_the_date(); ?>
                        </time>
                        <?php do_action('uwp_after_profile_invoice_date', $wpi_invoice, $user); ?>
                        <div class="uwp-profile-item-summary">
                            <?php do_action('uwp_before_profile_invoice_summary', $wpi_invoice, $user); ?>
                            <div class="uwp-order-status">
                                <?php
                                echo __('Invoice Status: ', 'userswp').$wpi_invoice->get_status( true ) . ( $wpi_invoice->is_recurring() && $wpi_invoice->is_parent() ? ' <span class="wpi-suffix">' . __( '(r)', 'invoicing' ) . '</span>' : '' );
                                ?>
                            </div>
                            <div class="uwp-order-total">
                                <?php
                                echo __('Invoice Total: ', 'userswp'). $wpi_invoice->get_total( true );
                                ?>
                            </div>
                            <?php do_action('uwp_after_profile_invoice_summary', $wpi_invoice, $user); ?>
                            <?php
                            $actions = array();

                            if ( 'wpi-pending' == $wpi_invoice->post_status && $wpi_invoice->needs_payment() ) {
                                $actions['pay'] = array(
                                    'url'  => $wpi_invoice->get_checkout_payment_url(),
                                    'name' => __( 'Pay Now', 'invoicing' ),
                                    'class' => 'btn-uwp-pay-now'
                                );
                            }

                            $cart_items = $wpi_invoice->get_cart_details();
                            if ( !empty( $cart_items )) {
                                foreach ($cart_items as $key => $cart_item) {
                                    $item_id    = $cart_item['id'];
                                    $wpi_item   = $item_id > 0 ? new WPInv_Item( $item_id ) : NULL;
                                    if ( !empty( $cart_item ) && !empty( $cart_item['meta']['post_id'] ) && $wpi_item->get_type() == 'package' ) {
                                        $post_id = $cart_item['meta']['post_id'];
                                        $post_ink = get_permalink( $post_id );
                                        if($post_ink ){
                                            $actions['listing'] = array(
                                                'url'  => $post_ink,
                                                'name' => __( 'Listing', 'invoicing' ),
                                                'class' => 'btn-uwp-listing'
                                            );
                                        }
                                    }
                                }
                            }

                            $actions = apply_filters( 'wpinv_user_profile_invoice_actions', $actions, $wpi_invoice );
                            if ( $actions ) {
                                foreach ( $actions as $key => $action ) {
                                    $class = !empty($action['class']) ? sanitize_html_class($action['class']) : '';
                                    echo '<a href="' . esc_url( $action['url'] ) . '" class="btn btn-sm ' . $class . ' ' . sanitize_html_class( $key ) . '" ' . ( !empty($action['attrs']) ? $action['attrs'] : '' ) . '>' . $action['name'] . '</a>';
                                }
                            }
                            ?>
                        </div>
                    </li>
                    <?php
                    do_action('uwp_after_profile_invoice_item', $wpi_invoice, $user);
                }
                echo '</ul>';
                /* Restore original Post Data */
                wp_reset_postdata();
            } else {
                // no posts found
                echo "<p>".__('No Invoices Found.', 'userswp')."</p>";
            }

            do_action('uwp_after_profile_invoice_items', $user);

            do_action('uwp_profile_pagination', $the_query->max_num_pages);
            ?>
        </div>
        <?php
    }

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
$userswp_geodirectory = UsersWP_Invoicing_Plugin::get_instance();