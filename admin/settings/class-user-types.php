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

            $current_form        = ! empty( $_GET['form'] ) ? (int) $_GET['form'] : 1;
            $register_forms      = uwp_get_option( 'multiple_registration_forms' );
            if ( ! empty( $_GET['form_type'] ) && $_GET['form_type'] === 'new' ) {
                $new_added    = ! empty( $register_forms ) ? end( $register_forms ) : array();
                $current_form = ! empty( $new_added['id'] ) ? $new_added['id'] : 1;
            }
            ?>

            <div class="multiple-registration-form">

            <form class="uwp_user_type_form" id="uwp_user_type_form" method="POST">
                <input type="hidden" name="manage_field_form_id" class="manage_field_form_id"
                       id="manage_field_form_id"
                       value="<?php echo esc_attr( $current_form ); ?>">
				<?php do_action( 'uwp_user_type_form_before', $current_form, $tab ); ?>
				<?php
				if ( ! empty( $register_forms ) && is_array( $register_forms ) ) { ?>
                    <table class="form-table bsui userswp" id="uwp-forms-main">
                        <tr>
                            <td>

                                <div class="d-inline-block align-top">

                                </div>

                            </td>
                        </tr>
                    </table>
				<?php } ?>
                <?php

                    $form_id = $_GET[ 'form' ];
                    self::update_form ( $form_id );
                ?>
                <input type="hidden" name="uwp_update_register_form_nonce" value="<?php echo wp_create_nonce( 'uwp-update-register-form-nonce' ); ?>" />
				<?php do_action( 'uwp_user_type_form_after', $current_form, $tab ); ?>
            </form>
        </div>
		<?php

		} else {

            $admin_list_table = new UWP_Admin_List_Table();
            $admin_list_table->prepare_items();
            ?>
            <div class="wrap">
            <div class="bsui">
            <button data-nonce="<?php echo wp_create_nonce( 'uwp-create-register-form-nonce' ); ?>"
                    class="btn btn-sm btn-primary register-form-create" type="button"
                    name="register_form_create"
                    id="form_create"><?php _e( 'Create Form', 'userswp' ); ?></button>
            </div>
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
            $current_form = $register_forms[ $form_key ];

	        $current_title  = ! empty( $current_form['title'] ) ? $current_form['title'] : '';
            $current_gdpr_page   = ! empty( $forms['gdpr_page'] ) ? (int) $forms['gdpr_page'] : - 1;
            $current_tos_page    = ! empty( $forms['tos_page'] ) ? (int) $forms['tos_page'] : - 1;
	        $user_role   = ! empty( $forms['user_role'] ) ? $forms['user_role'] : '';
            if ( ! empty( $user_role ) && in_array( $user_role, array_keys( $user_roles ) ) ) {
                $current_role = $user_role;
            }
	        $current_custom_url  = ! empty( $forms['custom_url'] ) ? $forms['custom_url'] : '';

	        $actions             = uwp_get_registration_form_actions();
	        $current_action      = uwp_get_option( 'uwp_registration_action', false );
	        $current_action      = ! empty( $forms['reg_action'] ) ? $forms['reg_action'] : $current_action;

            ?>
            <table class="form-table bsui userswp" id="uwp-form-more-options" style="display:block;">
                <tr>
                    <th><?php _e( 'Title:', 'userswp' ); echo uwp_help_tip(__('Title of the form', 'userswp')) ?></th>
                    <td>
                        <input type="text" name="form_title" value="<?php echo esc_attr($current_title); ?>"
                               class="regular-text">
                    </td>
                    <?php if ( ! empty( $user_roles ) && is_array( $user_roles ) ) { ?>
                        <th><?php _e( 'User Role to Assign:', 'userswp' ); echo uwp_help_tip(__('Role to assign when user register via this form.', 'userswp'))  ?></th>
                        <td>
                            <select name="user_role" id="multiple_registration_user_role"
                                    class="small-text aui-select2">
                                <?php
                                foreach ( $user_roles as $key => $user_role ) {
                                    ?>
                                    <option <?php selected( $current_role, $key ); ?>
                                            value="<?php echo esc_attr($key); ?>"><?php echo sprintf( __( '%s', 'userswp' ), $user_role ); ?></option>
                                <?php }
                                ?>
                            </select>
                        </td>
                    <?php } ?>
                </tr>
                <tr>
                    <th><?php _e( 'Registration Action:', 'userswp' ); echo uwp_help_tip(__('Select how registration should be handled.', 'userswp')) ?></th>
                    <td>
                        <select name="reg_action" id="uwp_registration_action"
                                class="small-text aui-select2">
                            <?php
                            foreach ( $actions as $key => $action ) {
                                ?>
                                <option <?php selected( $current_action, $key ); ?>
                                        value="<?php echo esc_attr($key); ?>"><?php echo sprintf( __( '%s', 'userswp' ), $action ); ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr style="display:none;">
                    <th><?php _e( 'Redirect Page:', 'userswp' ); echo uwp_help_tip(__('Set the page to redirect the user to after signing up.', 'userswp'))  ?></th>
                    <td>
                        <select name="redirect_to" id="register_redirect_to"
                                class="small-text aui-select2">
                            <?php
                            $pages         = get_pages();
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
                            foreach ( $pages_options as $key => $option ) {
                                ?>
                                <option <?php selected( $current_redirect_to, $key ); ?>
                                        value="<?php echo esc_attr($key); ?>"><?php echo sprintf( __( '%s', 'userswp' ), $option ); ?></option>
                            <?php } ?>
                        </select>
                    </td>
                    <th><?php _e( 'Custom Redirect URL:', 'userswp' ); echo uwp_help_tip(__( 'Set the page to redirect the user to after signing up. If default redirect has been set then WordPress default will be used.', 'userswp' )); ?></th>
                    <td>
                        <input type="text" name="custom_url" id="register_redirect_custom_url"
                               class="regular-text" value="<?php echo esc_attr($current_custom_url); ?>">
                    </td>
                </tr>
                <tr>
                    <th><?php _e( 'GDPR Policy Page:', 'userswp' ); echo uwp_help_tip(__('Page to link when GDPR policy page custom field added to form. If not set then default setting will be used.', 'userswp')); ?></th>
                    <td>
                        <?php
                        $args = array(
                            'name'             => 'gdpr_page',
                            'id'               => 'multiple_registration_gdpr_page',
                            'sort_column'      => 'menu_order',
                            'sort_order'       => 'ASC',
                            'show_option_none' => ' ',
                            'class'            => ' regular-text aui-select2 ',
                            'echo'             => false,
                            'selected'         => (int) $current_gdpr_page > 0 ? (int) $current_gdpr_page : - 1,
                        );
                        echo str_replace( ' id=', " data-placeholder='" . esc_attr__( 'Select a page&hellip;', 'userswp' ) . "' id=", wp_dropdown_pages( $args ) );
                        ?>
                    </td>
                    <th><?php _e( 'TOS Page:', 'userswp' ); echo uwp_help_tip(__('Page to link when Terms and Conditions custom field added to form. If not set then default setting will be used.', 'userswp'));?></th>
                    <td>
                        <?php
                        $args = array(
                            'name'             => 'tos_page',
                            'id'               => 'multiple_registration_tos_page',
                            'sort_column'      => 'menu_order',
                            'sort_order'       => 'ASC',
                            'show_option_none' => ' ',
                            'class'            => ' regular-text aui-select2 ',
                            'echo'             => false,
                            'selected'         => (int) $current_tos_page > 0 ? (int) $current_tos_page : - 1,
                        );
                        echo str_replace( ' id=', " data-placeholder='" . esc_attr__( 'Select a page&hellip;', 'userswp' ) . "' id=", wp_dropdown_pages( $args ) );
                        ?>
                    </td>
                </tr>
                <?php //do_action( 'uwp_user_type_form_before_submit', $current_form, $tab ); ?>
                <tr>
                    <td></td>
                    <td>
                        <button class="btn btn-sm btn-secondary" id="form_update" type="submit"
                                name="form_update"><?php _e( 'Update', 'userswp' ); ?></button>
		                    <?php if ( ! empty( $current_form ) && $current_form > 1 ) { ?>
                                <button data-id="<?php echo esc_attr($form_id); ?>"
                                        data-nonce="<?php echo wp_create_nonce( 'uwp-delete-register-form-nonce' ); ?>"
                                        class="btn btn-sm btn-danger register-form-remove" type="button"
                                        name="form_remove"><?php _e( 'Delete Form', 'userswp' ); ?></button>
		                    <?php } ?>

                    </td>
                </tr>
            </table>
        <?php
    }
}
