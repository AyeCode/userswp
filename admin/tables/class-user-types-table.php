<?php
/**
 * The UsersWP user types list table.
 *
 * @link       http://wpgeodirectory.com
 * @since      1.2.3.11
 *
 * @package    UserWP
 * @subpackage UserWP/Admin/Tables
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class UsersWP_User_Types_Table
 *
 * @since 1.2.3.11
 */
class UsersWP_User_Types_Table extends WP_List_Table {
    /**
     * Stores admin notices to be displayed
     *
     * @var array
     */
    private $admin_notices = array();

    /**
     * Constructor - Sets up the table properties and actions
     *
     */
    public function __construct() {
        parent::__construct(
            array(
                'singular' => __( 'User Type', 'userswp' ),
                'plural'   => __( 'User Types', 'userswp' ),
                'ajax'     => true,
            )
        );

		$this->process_bulk_action();
		$this->display_admin_notices();

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    /**
     * Prepares table data for display
     *
     * Gets columns configuration, fetches table data, handles search filtering,
     * sorting, and pagination of items.
     *
     * @return void
     */
    public function prepare_items() {
        $columns  = $this->get_columns();
        $hidden   = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->table_data();

        $search = isset( $_REQUEST['s'] ) ? sanitize_text_field( $_REQUEST['s'] ) : '';
        if ( $search ) {
            $data = $this->filter_data( $data, $search );
        }

        // usort( $data, array( $this, 'sort_data' ) );

        $per_page     = $this->get_items_per_page( 'user_types_per_page', 10 );
        $current_page = $this->get_pagenum();
        $total_items  = count( $data );

        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => $per_page,
            )
        );

        $data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

        $this->_column_headers = array( $columns, $hidden, $sortable );
        $this->items = $data;
    }

    /**
     * Defines the table columns and their titles
     *
     * @return array Array of column names and their labels
     */
    public function get_columns() {
        $columns = array(
            'cb'           => '<input type="checkbox" />',
            'title'        => __( 'User Type', 'userswp' ),
            'user_role'    => __( 'User Role', 'userswp' ),
            'reg_action'   => __( 'Registration Action', 'userswp' ),
            'reg_link'     => __( 'Register Link', 'userswp' ),
            'reg_lightbox' => __( 'Register Lightbox', 'userswp' ),
            // 'slug'       => __( 'Slug', 'userswp' ),
        );

        $columns = apply_filters( 'uwp_user_types_table_columns', $columns );
        $columns['reorder'] = __( 'Reorder', 'userswp' );

        return $columns;
    }

    /**
     * Specifies which columns should be hidden by default
     *
     * @return array Array of hidden column names
     */
    public function get_hidden_columns() {
        return array();
    }

    /**
     * Defines sortable columns and their sort direction
     *
     * @return array Array of sortable columns with their sort direction
     */
    public function get_sortable_columns() {
        return array(
            'title'      => array( 'title', false ),
            'user_role'  => array( 'user_role', false ),
            'reg_action' => array( 'reg_action', false ),
        );
    }

    /**
     * Fetches and formats the table data
     *
     * @return array Formatted table data
     */
    private function table_data() {
        $data = array();
        $register_forms = (array) uwp_get_option( 'multiple_registration_forms', array() );

        $register_url = uwp_get_register_page_url();
        $settings = uwp_get_settings();
        $register_modal_form = ! empty( $settings['register_modal_form'] ) ? $settings['register_modal_form'] : array( 1 );

        foreach ( $register_forms as $index => $register_form ) {

            $slug = $register_form['title'] ? sanitize_title_with_dashes( $register_form['title'] ) : '';
            $reg_link_slug = $register_form['id'] > 1 ? add_query_arg( 'user_type', $slug, $register_url ) : $register_url;
            $reg_link_id = $register_form['id'] > 1 ? add_query_arg( 'user_type', absint( $register_form['id'] ), $register_url ) : $register_url;

            $copy_link_html = 'onclick="navigator.clipboard.writeText(this.href);aui_toast(\'uwp_user_reg_link_copied\', \'success\', \'' . esc_attr__( 'Link Copied!', 'userswp' ) . '\');return false;"';//;

            if ( isset( $register_form['id'], $register_form['title'] ) ) {
                $form_data = array(
                    'id'           => (int) $register_form['id'],
                    'title'        => $register_form['title'],
                    // 'slug'         => isset( $register_form['slug'] ) ? $register_form['slug'] : '',
                    'user_role'    => isset( $register_form['user_role'] ) ? $register_form['user_role'] : get_option( 'default_role' ),
                    'reg_action'   => isset( $register_form['reg_action'] ) ? $register_form['reg_action'] : 'auto_approve',
                    'reg_link'     => $register_form['id'] > 1
                        ? '<a href="' . esc_url( $reg_link_slug ) . '" ' . $copy_link_html . '  >' . esc_html__( 'Slug', 'userswp' ) . '</a> | <a href="' . esc_url( $reg_link_id ) . '" ' . $copy_link_html . '  >' . esc_html__( 'ID', 'userswp' ) . '</a>'
                        : '<a href="' . esc_url( $reg_link_id ) . '" ' . $copy_link_html . '  >' . esc_html__( 'Link', 'userswp' ) . '</a>',
                    'reg_lightbox' => in_array( $register_form['id'], $register_modal_form ) ? __( 'Yes', 'userswp' ) : __( 'No', 'userswp' ) . ' (<a href="' . esc_url( admin_url( 'admin.php?page=userswp&tab=general&section=register' ) ) . '">' . __( 'change', 'userswp' ) . '</a>)',
                    'order'        => $index,
                );

                $user_roles = uwp_get_user_roles();
                $reg_actions = uwp_get_registration_form_actions();

                $form_data['user_role'] = isset( $user_roles[ $form_data['user_role'] ] ) ? $user_roles[ $form_data['user_role'] ] : $form_data['user_role'];
                $form_data['reg_action'] = isset( $reg_actions[ $form_data['reg_action'] ] ) ? $reg_actions[ $form_data['reg_action'] ] : $form_data['reg_action'];

                $data[] = apply_filters( 'uwp_user_types_table_data', (array)$form_data, (array)$register_form );
            }
        }

        return $data;
    }

    /**
     * Handles default column value display
     *
     * @param array  $item        The current row item
     * @param string $column_name The current column name
     * @return mixed The column value or dash if empty
     */
    public function column_default( $item, $column_name ) {
        $value = isset( $item[ $column_name ] ) ? $item[ $column_name ] : '';
        $value = apply_filters( 'uwp_user_types_table_column_default', $value, $item, $column_name );

        return $value === '' ? '&mdash;' : $value;
    }

    /**
     * Customizes the display of the title column
     *
     * @param array $item The current row item
     * @return string Formatted title cell with row actions
     */
    public function column_title( $item ) {
        $edit_form_url = add_query_arg( 'form', (int) $item['id'], admin_url( 'admin.php?page=uwp_form_builder&tab=account' ) );
        $edit_link = admin_url( sprintf( 'admin.php?page=uwp_user_types&form=%d', $item['id'] ) );

        $title = sprintf(
            '<strong><a href="%s" class="row-title">%s</a></strong>',
            esc_url( $edit_link ),
            esc_html( $item['title'] )
        );

        $actions = array(
            'id'        => sprintf( '<span class="id">ID: %d</span>', $item['id'] ),
            'edit'      => sprintf( '<a href="%s">%s</a>', esc_url( $edit_link ), esc_html__( 'Edit', 'userswp' ) ),
            'edit-form' => sprintf( '<a href="%s">%s</a>', esc_url( $edit_form_url ), esc_html__( 'Edit Fields', 'userswp' ) ),
        );

        if ( $item['id'] > 1 ) {
            $actions['delete'] = sprintf(
                '<a href="#" class="register-form-remove" data-id="%d">%s</a>',
                $item['id'],
                esc_html__( 'Delete', 'userswp' )
            );
        }

        $actions = apply_filters( 'uwp_user_types_table_actions', $actions, $item );

        return $title . $this->row_actions( $actions );
    }

    public function column_reorder( $item ) {
        return sprintf(
            '<span class="dashicons dashicons-move uwp-user-type-handle" style="cursor: move;" data-id="%s"></span>',
            $item['id']
        );
    }

    /**
     * Renders the checkbox column for bulk actions
     *
     * @param array $item The current row item
     * @return string Checkbox HTML
     */
    public function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="user_types[]" value="%s" />',
            $item['id']
        );
    }

    /**
     * Handles custom sorting of table data
     *
     * Sorts based on column values using GET parameters for order and direction.
     * Sanitizes input parameters for security.
     *
     * @param array $a First item to compare
     * @param array $b Second item to compare
     * @return int Comparison result (-1, 0, 1)
     */
    private function sort_data( $a, $b ) {
        // Set defaults
        $orderby = 'title';
        $order   = 'asc';

        // If orderby is set, use this as the sort column
        if ( isset( $_GET['orderby'] ) && ! empty( $_GET['orderby'] ) ) {
            $orderby = sanitize_text_field( $_GET['orderby'] );
        }

        // If order is set use this as the order
        if ( isset( $_GET['order'] ) && ! empty( $_GET['order'] ) ) {
            $order = sanitize_text_field( $_GET['order'] );
        }

        $result = strcasecmp( $a[ $orderby ], $b[ $orderby ] );

        return ( $order === 'asc' ) ? $result : -$result;
    }

    /**
     * Filters table data based on search term
     *
     * Searches through all column values for matches with the search term.
     *
     * @param array  $data   Array of table data
     * @param string $search Search term to filter by
     * @return array Filtered data array
     */
    private function filter_data( $data, $search ) {
        $filtered_data = array_filter(
            $data,
            function ( $row ) use ( $search ) {
                foreach ( $row as $value ) {
                    if ( stripos( $value, $search ) !== false ) {
                        return true;
                    }
                }
                return false;
            }
        );

        return $filtered_data;
    }

    /**
     * Defines available bulk actions
     *
     * @return array Array of bulk actions
     */
    protected function get_bulk_actions() {
        $actions = array(
            'delete' => __( 'Delete', 'userswp' ),
        );
        return $actions;
    }

    /**
     * Processes bulk actions
     *
     * Adds admin notice after successful deletion.
     */
    public function process_bulk_action() {

        if ( 'delete' === $this->current_action() ) {
            $user_types = isset( $_POST['user_types'] ) ? array_map( 'absint', $_POST['user_types'] ) : array();

            if ( ! empty( $user_types ) ) {
                foreach ( $user_types as $user_type_id ) {
                    UsersWP_Admin::remove_registration_form( (int) $user_type_id );
                }
            }

            $this->add_admin_notice( __( 'Selected user types have been deleted.', 'userswp' ) );
        }
    }

    /**
     * Displays the table navigation
     *
     * @param string $which Location of the nav ('top' or 'bottom')
     */
    protected function display_tablenav( $which ) {
        ?>
        <div class="tablenav <?php echo esc_attr( $which ); ?>">
            <?php if ( $this->has_items() ) : ?>
                <div class="alignleft actions bulkactions">
                    <?php $this->bulk_actions( $which ); ?>
                </div>
            <?php
            endif;
            $this->pagination( $which );
            ?>
            <br class="clear" />
        </div>
        <?php
    }

    /**
     * Adds an admin notice to be displayed
     *
     * @param string $message Notice message
     * @param string $type    Notice type (success, error, warning, info)
     */
    public function add_admin_notice( $message, $type = 'success' ) {
        $this->admin_notices[] = array(
            'message' => $message,
            'type'    => $type,
        );
    }

    /**
     * Displays queued admin notices
     */
    public function display_admin_notices() {
        foreach ( $this->admin_notices as $notice ) {
            ?>
            <div class="notice notice-<?php echo esc_attr( $notice['type'] ); ?> is-dismissible">
                <p><?php echo esc_html( $notice['message'] ); ?></p>
            </div>
            <?php
        }
    }
}
