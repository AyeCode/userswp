<?php
/**
 * The form builder functionality of the plugin.
 *
 * @link       http://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    userswp
 * @subpackage userswp/admin/settings
 */

/**
 * The form builder functionality of the plugin.
 *
 * @package    userswp
 * @subpackage userswp/admin/settings
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_User_Types {
	public static function output( $tab = '' ) {

		do_action( 'uwp_user_types_start' );
		if ( isset( $_GET['form'] ) ) {
			$form_id = $_GET[ 'form' ];
            $current_form        = ! empty( $_GET['form'] ) ? (int) $_GET['form'] : 1;
            $register_forms      = uwp_get_option( 'multiple_registration_forms' );
            if ( ! empty( $_GET['form_type'] ) && $_GET['form_type'] === 'new' ) {
                $new_added    = ! empty( $register_forms ) ? end( $register_forms ) : array();
                $current_form = ! empty( $new_added['id'] ) ? $new_added['id'] : 1;
            }
            ?>

            <div class="multiple-registration-form wrap">
                <h1 class="wp-heading-inline"><?php esc_html_e( 'Edit user type ', 'userswp' ); ?></h1>
                <button
                    data-nonce="<?php echo esc_attr( wp_create_nonce( 'uwp-create-register-form-nonce' ) ); ?>"
                    class="page-title-action register-form-create" type="button"
                    name="register_form_create"
                    id="form_create">
                    <?php esc_html_e( 'Add User Type', 'userswp' ); ?>
                </button>
            <form class="uwp_user_type_form" id="uwp_user_type_form" method="POST">
                <input type="hidden" name="manage_field_form_id" class="manage_field_form_id"
                       id="manage_field_form_id"
                       value="<?php echo esc_attr( $current_form ); ?>">
				<?php do_action( 'uwp_user_type_form_before', $current_form ); ?>
                <?php self::update_form ( $form_id ); ?>
                <input type="hidden" name="uwp_update_register_form_nonce" value="<?php echo wp_create_nonce( 'uwp-update-register-form-nonce' ); ?>" />
				<?php do_action( 'uwp_user_type_form_after', $current_form ); ?>
            </form>
        </div>
		<?php

		} else {

            $admin_list_table = new UWP_Admin_List_Table();
            $admin_list_table->prepare_items();
            ?>
            <div class="wrap">
                <h1 class="wp-heading-inline"><?php _e( 'User Types', 'userswp' ); ?></h1>
<!--                <div class="bsui">-->
                <button data-nonce="<?php echo wp_create_nonce( 'uwp-create-register-form-nonce' ); ?>"
                        class="page-title-action register-form-create" type="button"
                        name="register_form_create"
                        id="form_create"><?php _e( 'Add User Type', 'userswp' ); ?></button>
<!--                </div>-->
		<?php

		$admin_list_table->display();
		?>
		</div>
	    <?php
		}
	}

    public static function update_form( $form_id) {
            $register_forms      = uwp_get_option( 'multiple_registration_forms' );
            $form_key = array_search( $form_id, wp_list_pluck ( $register_forms, 'id' ) );
	        $user_roles          = uwp_get_user_roles();
	        $current_role        = get_option( 'default_role' );
            // Remove admin role
            unset( $user_roles['administrator'] );
            $current_form = $register_forms[ $form_key ];

	        $user_role   = ! empty( $current_form['user_role'] ) ? $current_form['user_role'] : '';
            if ( ! empty( $user_role ) && in_array( $user_role, array_keys( $user_roles ), true ) ) {
                $current_role = $user_role;
            }

	        $current_action = uwp_get_option( 'uwp_registration_action', false );
	        $current_action = ! empty( $current_form['reg_action'] ) ? $current_form['reg_action'] : $current_action;
            $all_pages      = wp_list_pluck( get_pages(), 'post_title', 'ID' );

            ?>
            <table class="form-table bsui userswp" id="uwp-form-more-options" style="display:block;">
                <tr>
                    <th>
                        <?php
                            esc_html_e( 'Title:', 'userswp' );
                            echo wp_kses_post(
                                uwp_help_tip(
                                    __( 'For example, Members', 'userswp' )
                                )
                            );
                        ?>
                    </th>
                    <td>
                        <input
                            type="text"
                            name="form_title"
                            value="<?php echo esc_attr( ! empty( $current_form['title'] ) ? $current_form['title'] : '' ); ?>"
                            class="form-control"
                            required
                        />
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php
                            esc_html_e( 'User Role to Assign:', 'userswp' );
                            echo wp_kses_post(
                                uwp_help_tip(
                                    __( 'Role to assign this user type.', 'userswp' )
                                )
                            );
                        ?>
                    </th>
                    <td>
                        <?php
                            aui()->select(
                                array(
                                    'name'        => 'user_role',
                                    'id'          => 'multiple_registration_user_role',
                                    'placeholder' => __( 'Select a role&hellip;', 'userswp' ),
                                    'options'     => $user_roles,
                                    'value'       => $current_role,
                                    'no_wrap'     => true,
                                    'select2'     => true,
                                    'class'       => 'w-100',
                                ),
                                true
                            );
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php
                            esc_html_e( 'Registration Action:', 'userswp' );
                            echo wp_kses_post(
                                uwp_help_tip(
                                    __( 'Select how registration should be handled.', 'userswp' )
                                )
                            );
                        ?>
                    </th>
                    <td>
                        <?php
                            aui()->select(
                                array(
                                    'name'        => 'reg_action',
                                    'id'          => 'uwp_registration_action',
                                    'placeholder' => __( 'Select an action&hellip;', 'userswp' ),
                                    'options'     => uwp_get_registration_form_actions(),
                                    'value'       => $current_action,
                                    'no_wrap'     => true,
                                    'select2'     => true,
                                    'class'       => 'w-100',
                                ),
                                true
                            );
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php
                            esc_html_e( 'Redirect Page:', 'userswp' );
                            echo wp_kses_post(
                                uwp_help_tip(
                                    __( 'Set the page to redirect the user to after signing up.', 'userswp' )
                                )
                            );
                        ?>
                    </th>
                    <td>
                        <?php
                            aui()->select(
                                array(
                                    'name'        => 'redirect_to',
                                    'id'          => 'register_redirect_to',
                                    'placeholder' => __( 'Select a page&hellip;', 'userswp' ),
                                    'options'     => array_replace(
                                        array(
                                            '-1' => __( 'Last User Page', 'userswp' ),
                                            '0'  => __( 'Default Redirect', 'userswp' ),
                                            '-2' => __( 'Custom Redirect', 'userswp' ),
                                        ),
                                        $all_pages
                                    ),
                                    'value'       => ! empty( $current_form['redirect_to'] ) ? $current_form['redirect_to'] : '',
                                    'no_wrap'     => true,
                                    'select2'     => true,
                                    'class'       => 'w-100',
                                ),
                                true
                            );
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php
                            esc_html_e( 'Custom Redirect URL:', 'userswp' );
                            echo wp_kses_post(
                                uwp_help_tip(
                                    __( 'Set the page to redirect the user to after signing up. If default redirect has been set then WordPress default will be used.', 'userswp' )
                                )
                            );
                        ?>
                    </th>
                    <td>
                        <?php
                            aui()->input(
                                array(
                                    'name'        => 'custom_url',
                                    'id'          => 'register_redirect_custom_url',
                                    'placeholder' => __( 'Enter URL&hellip;', 'userswp' ),
                                    'value'       => ! empty( $current_form['custom_url'] ) ? $current_form['custom_url'] : '',
                                    'no_wrap'     => true,
                                    'class'       => 'w-100',
                                ),
                                true
                            );
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php
                            esc_html_e( 'GDPR Policy Page:', 'userswp' );
                            echo wp_kses_post(
                                uwp_help_tip(
                                    __( 'Page to link when GDPR policy page custom field added to form. If not set then default setting will be used.', 'userswp' )
                                )
                            );
                        ?>
                    </th>
                    <td>
                        <?php
                            aui()->select(
                                array(
                                    'name'        => 'gdpr_page',
                                    'id'          => 'multiple_registration_gdpr_page',
                                    'placeholder' => __( 'Select a page&hellip;', 'userswp' ),
                                    'options'     => $all_pages,
                                    'value'       => ! empty( $current_form['gdpr_page'] ) ? (int) $current_form['gdpr_page'] : '',
                                    'no_wrap'     => true,
                                    'select2'     => true,
                                    'class'       => 'w-100',
                                ),
                                true
                            );
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php
                            esc_html_e( 'TOS Page:', 'userswp' );
                            echo wp_kses_post(
                                uwp_help_tip(
                                    __( 'Page to link when Terms and Conditions custom field added to form. If not set then default setting will be used.', 'userswp' )
                                )
                            );
                        ?>
                    </th>
                    <td style="min-width: 26.2rem;">
                        <?php
                            aui()->select(
                                array(
                                    'name'        => 'tos_page',
                                    'id'          => 'multiple_registration_tos_page',
                                    'placeholder' => __( 'Select a page&hellip;', 'userswp' ),
                                    'options'     => $all_pages,
                                    'value'       => ! empty( $current_form['tos_page'] ) ? (int) $current_form['tos_page'] : '',
                                    'no_wrap'     => true,
                                    'select2'     => true,
                                    'class'       => 'w-100',
                                ),
                                true
                            );
                        ?>
                    </td>
                </tr>
            </table>

            <?php do_action( 'uwp_user_type_form_before_submit', $current_form ); ?>

            <div class="bsui">
                <button
                    class="btn btn-sm btn-primary"
                    id="form_update"
                    type="submit"
                    name="form_update"
                >
                    <?php esc_html_e( 'Update', 'userswp' ); ?>
                </button>
                <a
                    href="<?php echo esc_url( add_query_arg( 'form', (int) $form_id, admin_url( 'admin.php?page=uwp_form_builder&tab=account' ) ) ); ?>"
                    class="btn btn-sm btn-link"
                    target="_blank"
                ><?php esc_html_e( 'Edit registration form', 'userswp' ); ?></a>
            </div>
        <?php
    }
}
