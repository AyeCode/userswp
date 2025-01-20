<?php
/**
 * The form builder functionality of the plugin.
 *
 * @link       http://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    UsersWP
 * @subpackage UsersWP/Admin/Settings
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The form builder functionality of the plugin.
 *
 * @package    UsersWP
 * @subpackage UsersWP/Admin/Settings
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_User_Types {

    public static function get_register_forms() {
        $register_forms = (array) uwp_get_option( 'multiple_registration_forms', array() );
        return $register_forms;
    }

    /**
     * Output the user types list or edit form.
     *
     * @param string $tab Current tab.
     */
    public static function output( string $tab = '' ) {
        do_action( 'uwp_user_types_start' );

        if ( isset( $_GET['form'] ) ) {
            self::output_edit_form( $_GET['form'] );
        } else {
            self::display_table();
        }
    }

    /**
     * Output the edit form for a user type.
     */
    private static function output_edit_form( $form_type ) {
        if ( $form_type === 'add' ) {
            self::display_add_form();
        } else {
            self::display_edit_form();
        }
    }

    /**
     * Display the 'add' user type form.
     */
    private static function display_add_form() {
		$all_pages = wp_list_pluck( get_pages(), 'post_title', 'ID' );
		$user_roles = uwp_get_user_roles();
		$registration_form_actions = uwp_get_registration_form_actions();
		$current_role = get_option( 'default_role' );

		// Remove admin role
		unset( $user_roles['administrator'] );
		?>
        <div class="multiple-registration-form wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e( 'Add User Type', 'userswp' ); ?></h1>
            <form class="uwp_user_type_form" id="uwp_user_type_form" method="POST" style="max-width: 700px;">
                <input type="hidden" name="action" value="add">
                <table class="form-table bsui userswp" id="uwp-form-more-options">
                    <?php
                    wp_nonce_field( 'uwp-create-register-form-nonce', 'uwp_create_register_form_nonce' );
                    self::render_text_field(
                        'uwp_form_title',
                        'form_title',
                        __( 'Title:', 'userswp' ),
                        __( 'Title of the form', 'userswp' ),
                        '',
                        array(
                            'required' => 'required'
                        )
                    );

                    if ( ! empty( $user_roles ) && is_array( $user_roles ) ) {
                        self::render_select_field(
                            'multiple_registration_user_role',
                            'user_role',
                            __( 'User Role to Assign:', 'userswp' ),
                            __( 'Role to assign when user register via this form.', 'userswp' ),
                            $user_roles,
                            $current_role
                        );
                        }

                    self::render_select_field(
                        'uwp_registration_action',
                        'reg_action',
                        __( 'Registration Action:', 'userswp' ),
                        __( 'Select how registration should be handled.', 'userswp' ),
                        $registration_form_actions,
                        ''
                    );

                    self::render_select_field(
                        'register_redirect_to',
                        'redirect_to',
                        __( 'Redirect Page:', 'userswp' ),
                        __( 'Set the page to redirect the user to after signing up.', 'userswp' ),
                        self::get_redirect_options(),
                        '',
                        false,
                        array( 'style' => 'display:none;' )
                    );

                    self::render_text_field(
                        'register_redirect_custom_url',
                        'custom_url',
                        __( 'Custom Redirect URL:', 'userswp' ),
                        __( 'Set the page to redirect the user to after signing up. If default redirect has been set then WordPress default will be used.', 'userswp' ),
                        '',
                        array( 'style' => 'display:none;' )
                    );

                    self::render_select_field(
                        'multiple_registration_gdpr_page',
                        'gdpr_page',
                        __( 'GDPR Policy Page:', 'userswp' ),
                        __( 'Page to link when GDPR policy page custom field added to form. If not set then default setting will be used.', 'userswp' ),
                        $all_pages,
                        '',
                        esc_attr__( 'Select a page&hellip;', 'userswp' )
                    );

                    self::render_select_field(
                        'multiple_registration_tos_page',
                        'tos_page',
                        __( 'TOS Page:', 'userswp' ),
                        __( 'Page to link when Terms and Conditions custom field added to form. If not set then default setting will be used.', 'userswp' ),
                        $all_pages,
                        '',
                        esc_attr__( 'Select a page&hellip;', 'userswp' )
                    );
                    ?>
                </table>

                <?php
                do_action( 'uwp_user_type_form_before_submit', array() );
                ?>

                <div class="bsui">
                    <div class="alert alert-success d-none"></div>
                    <div class="alert alert-danger d-none"></div>
                </div>

                <div class="bsui">
                    <button class="btn btn-md btn-primary" id="form_update" type="submit" name="form_update">
                        <?php esc_html_e( 'Add User Type', 'userswp' ); ?>
                    </button>
                </div>
            </form>
        </div>
        <?php
    }

    /**
     * Display the 'edit' user type form.
     */
    private static function display_edit_form() {
        $form_id = isset( $_GET['form'] ) ? (int) $_GET['form'] : 1;
        $register_forms = self::get_register_forms();

        if ( ! empty( $_GET['form_type'] ) && $_GET['form_type'] === 'new' ) {
            $new_added = ! empty( $register_forms ) ? end( $register_forms ) : array();
            $form_id = ! empty( $new_added['id'] ) ? $new_added['id'] : 1;
        }

        ?>
        <div class="multiple-registration-form wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e( 'Edit User Type', 'userswp' ); ?></h1>
            <form class="uwp_user_type_form" id="uwp_user_type_form" method="POST" style="max-width: 700px;">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="manage_field_form_id" class="manage_field_form_id"
                        id="manage_field_form_id" value="<?php echo esc_attr( $form_id ); ?>">
                <?php
                do_action( 'uwp_user_type_form_before', $form_id );
                self::update_form( $form_id );
                wp_nonce_field( 'uwp-update-register-form-nonce', 'uwp_update_register_form_nonce' );
                do_action( 'uwp_user_type_form_after', $form_id );
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Output the user types list.
     */
    private static function display_table() {
        $nonce = wp_create_nonce( 'uwp-create-register-form-nonce' );
        $table = new UsersWP_User_Types_Table();
        $table->prepare_items();
        ?>
        <div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'User Types', 'userswp' ); ?></h1>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=uwp_user_types&form=add' ) ); ?>" class="page-title-action">
                <?php esc_html_e( 'Add User Type', 'userswp' ); ?>
            </a>

            <p><?php esc_html_e( 'Create and manage different user types with custom roles and registration forms.', 'userswp' ); ?></p>

			<form method="POST">
				<?php $table->display(); ?>
			</form>
		</div>
        <?php
    }

    /**
     * Update the user type form.
     *
     * @param int $form_id The form ID.
     */
    public static function update_form( int $form_id ) {
        $register_forms = self::get_register_forms();
        $form_key = array_search( $form_id, wp_list_pluck( $register_forms, 'id' ) );
        $current_form = $register_forms[ $form_key ];
        $current_action = ! empty( $current_form['reg_action'] ) ? $current_form['reg_action'] : uwp_get_option( 'uwp_registration_action', false );

        $user_roles = uwp_get_user_roles();
        $all_pages = wp_list_pluck( get_pages(), 'post_title', 'ID' );
        $registration_form_actions = uwp_get_registration_form_actions();
        $current_role = get_option( 'default_role' );

        // Remove admin role
        unset( $user_roles['administrator'] );

        $user_role = ! empty( $current_form['user_role'] ) ? $current_form['user_role'] : '';
        if ( ! empty( $user_role ) && in_array( $user_role, array_keys( $user_roles ), true ) ) {
            $current_role = $user_role;
        }
        ?>
        <table class="form-table bsui userswp" id="uwp-form-more-options">
            <?php
            self::render_text_field(
                'uwp_form_title',
                'form_title',
                __( 'Title:', 'userswp' ),
                __( 'Title of the form', 'userswp' ),
                ! empty( $current_form['title'] ) ? $current_form['title'] : ''
            );

            if ( ! empty( $user_roles ) && is_array( $user_roles ) ) {
                self::render_select_field(
                    'multiple_registration_user_role',
                    'user_role',
                    __( 'User Role to Assign:', 'userswp' ),
                    __( 'Role to assign when user register via this form.', 'userswp' ),
                    $user_roles,
                    $current_role
                );
            }

            self::render_select_field(
                'uwp_registration_action',
                'reg_action',
                __( 'Registration Action:', 'userswp' ),
                __( 'Select how registration should be handled.', 'userswp' ),
                $registration_form_actions,
                $current_action
            );

            self::render_select_field(
                'register_redirect_to',
                'redirect_to',
                __( 'Redirect Page:', 'userswp' ),
                __( 'Set the page to redirect the user to after signing up.', 'userswp' ),
                self::get_redirect_options(),
                ! empty( $current_form['redirect_to'] ) ? $current_form['redirect_to'] : '',
                false,
                array( 'style' => 'display:none;' )
            );

            self::render_text_field(
                'register_redirect_custom_url',
                'custom_url',
                __( 'Custom Redirect URL:', 'userswp' ),
                __( 'Set the page to redirect the user to after signing up. If default redirect has been set then WordPress default will be used.', 'userswp' ),
                ! empty( $current_form['custom_url'] ) ? $current_form['custom_url'] : '',
                array( 'style' => 'display:none;' )
            );

            self::render_select_field(
                'multiple_registration_gdpr_page',
                'gdpr_page',
                __( 'GDPR Policy Page:', 'userswp' ),
                __( 'Page to link when GDPR policy page custom field added to form. If not set then default setting will be used.', 'userswp' ),
                $all_pages,
                ! empty( $current_form['gdpr_page'] ) ? (int) $current_form['gdpr_page'] : '',
                esc_attr__( 'Select a page&hellip;', 'userswp' )
            );

            self::render_select_field(
                'multiple_registration_tos_page',
                'tos_page',
                __( 'TOS Page:', 'userswp' ),
                __( 'Page to link when Terms and Conditions custom field added to form. If not set then default setting will be used.', 'userswp' ),
                $all_pages,
                ! empty( $current_form['tos_page'] ) ? (int) $current_form['tos_page'] : '',
                esc_attr__( 'Select a page&hellip;', 'userswp' )
            );
            ?>
        </table>

        <?php do_action( 'uwp_user_type_form_before_submit', $current_form ); ?>

        <div class="bsui">
            <div class="alert alert-success d-none"></div>
            <div class="alert alert-danger d-none"></div>
        </div>

        <div class="bsui">
            <button class="btn btn-sm btn-primary" id="form_update" type="submit" name="form_update">
                <?php esc_html_e( 'Update', 'userswp' ); ?>
            </button>
            <a href="<?php echo esc_url( add_query_arg( 'form', $form_id, admin_url( 'admin.php?page=uwp_form_builder&tab=account' ) ) ); ?>"
                class="btn btn-sm btn-link"
                target="_blank"><?php esc_html_e( 'Edit registration form', 'userswp' ); ?></a>
        </div>
        <?php
    }

    /**
     * Render a text field.
     *
     * @param string $id        Field id.
     * @param string $name        Field name.
     * @param string $label       Field label.
     * @param string $description Field description.
     * @param string $value       Field value.
     * @param array $attributes       Field attributes.
     */
    private static function render_text_field( string $id, string $name, string $label, string $description, string $value, array $attributes = array() ) {
        $attr_string = '';
        foreach ( $attributes as $attr => $attr_value ) {
            $attr_string .= ' ' . $attr . '="' . esc_attr( $attr_value ) . '"';
        }
        ?>
        <tr<?php echo $attr_string; ?>>
            <th>
                <?php
                echo esc_html( $label );
                echo wp_kses_post( uwp_help_tip( $description ) );
                ?>
            </th>
            <td>
                <input id="<?php echo esc_attr( $id ); ?>" type="text" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" class="bsui form-control" />
                <div class="invalid-feedback"></div>
            </td>
        </tr>
        <?php
    }

    /**
     * Render a select field.
     *
     * @param string $id        Field id.
     * @param string $name        Field name.
     * @param string $label       Field label.
     * @param string $description Field description.
     * @param array  $options     Field options.
     * @param string $value       Field value.
     * @param string $placeholder       Field placeholder.
     * @param array $attributes       Field attributes.
     */
    private static function render_select_field( string $id, string $name, string $label, string $description, array $options, string $value, $placeholder = null, array $attributes = array() ) {
        $placeholder = is_null( $placeholder ) ? sprintf( __( 'Select a %s&hellip;', 'userswp' ), strtolower( $label ) ) : $placeholder;
        $attr_string = '';
        foreach ( $attributes as $attr => $attr_value ) {
            $attr_string .= ' ' . $attr . '="' . esc_attr( $attr_value ) . '"';
        }
        ?>
        <tr <?php echo $attr_string; ?>>
            <th>
                <?php
                echo esc_html( $label );
                echo wp_kses_post( uwp_help_tip( $description ) );
                ?>
            </th>
            <td>
                <?php
                aui()->select(
                    array(
                        'id'          => esc_attr( $id ),
                        'name'        => $name,
                        'placeholder' => $placeholder,
                        'options'     => $options,
                        'value'       => $value,
                        'no_wrap'     => true,
                        'select2'     => true,
                        'class'       => 'w-100',
                    ),
                    true
                );
                ?>
            </td>
        </tr>
        <?php
    }

    private static function get_redirect_options() {
        $pages = get_pages();
        $pages_options = array(
            '-1' => __( 'Last User Page', 'userswp' ),
            '0'  => __( 'Default Redirect', 'userswp' ),
            '-2' => __( 'Custom Redirect', 'userswp' ),
        );
        if ( $pages ) {
            foreach ( $pages as $page ) {
                $pages_options[ $page->ID ] = $page->post_title;
            }
        }

        return $pages_options;
    }
}
