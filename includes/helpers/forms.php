<?php
/**
 * Returns the register form fields.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      array       Form fields.
 */
function get_register_form_fields() {
    global $wpdb;
    $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
    $extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';
    $fields = $wpdb->get_results($wpdb->prepare("SELECT fields.* FROM " . $table_name . " fields JOIN " . $extras_table_name . " extras ON extras.site_htmlvar_name = fields.htmlvar_name WHERE fields.form_type = %s AND fields.is_active = '1' AND fields.for_admin_use != '1' AND fields.is_register_field = '1' AND extras.form_type = 'register' ORDER BY extras.sort_order ASC", array('account')));
    $fields = apply_filters('uwp_get_register_form_fields', $fields);
    return $fields;
}

/**
 * Returns the register form validate-able fields.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       int         $role_id    Role ID from role addons. Default 0.
 *
 * @return      array                   Validate-able fields.
 */
function get_register_validate_form_fields($role_id) {
    global $wpdb;
    $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
    $extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';
    if ($role_id == 0) {
        $fields = $wpdb->get_results($wpdb->prepare("SELECT fields.* FROM " . $table_name . " fields JOIN " . $extras_table_name . " extras ON extras.site_htmlvar_name = fields.htmlvar_name WHERE fields.form_type = %s AND fields.field_type != 'fieldset' AND fields.field_type != 'file' AND fields.is_active = '1' AND fields.for_admin_use != '1' AND fields.is_register_field = '1' ORDER BY extras.sort_order ASC", array('account')));
    } else {
        $slug = get_post_field( 'post_name', $role_id );
        $fields = $wpdb->get_results($wpdb->prepare("SELECT fields.* FROM " . $table_name . " fields JOIN " . $extras_table_name . " extras ON extras.site_htmlvar_name = fields.htmlvar_name WHERE fields.form_type = %s AND fields.field_type != 'fieldset' AND fields.field_type != 'file' AND fields.is_active = '1' AND fields.for_admin_use != '1' AND fields.is_register_field = '1' AND FIND_IN_SET(%s, fields.user_roles) ORDER BY extras.sort_order ASC", array('account', $slug)));
    }

    $fields = apply_filters('uwp_get_register_validate_form_fields', $fields, $role_id);
    return $fields;
}

/**
 * Returns the change password form validate-able fields.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      array       Validate-able fields
 */
function get_change_validate_form_fields() {
    global $wpdb;
    $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
    $enable_old_password = uwp_get_option('change_enable_old_password', false);
    if ($enable_old_password == '1') {
        $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type != 'fieldset' AND field_type != 'file' AND is_active = '1' AND for_admin_use != '1' ORDER BY sort_order ASC", array('change')));
    } else {
        $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND field_type != 'fieldset' AND field_type != 'file' AND is_active = '1' AND for_admin_use != '1' AND htmlvar_name != 'uwp_change_old_password' ORDER BY sort_order ASC", array('change')));
    }
    return $fields;
}

/**
 * Returns the account form fields.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $extra_where    Extra where query.
 *
 * @return      array                       Form fields.
 */
function get_account_form_fields($extra_where = '') {
    global $wpdb;
    $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
    $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND is_active = '1' AND for_admin_use != '1' AND is_register_only_field = '0' " . $extra_where . " ORDER BY sort_order ASC", array('account', $extra_where)));
    return $fields;
}

/**
 * Returns the change password form fields.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      array       Form fields.
 */
function get_change_form_fields() {
    global $wpdb;
    $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
    $enable_old_password = uwp_get_option('change_enable_old_password', false);
    if ($enable_old_password == '1') {
        $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND is_active = '1' AND for_admin_use != '1' ORDER BY sort_order ASC", array('change')));
    } else {
        $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s AND is_active = '1' AND for_admin_use != '1' AND htmlvar_name != 'uwp_change_old_password' ORDER BY sort_order ASC", array('change')));
    }
    return $fields;
}

/**
 * Validates the submitted form data.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       array       $data           Submitted form data
 * @param       string      $type           Form type.
 * @param       array|bool  $fields         Fields applicable for validation.
 *
 * @return      array|mixed|void|WP_Error   Validated form data.
 */
function uwp_validate_fields($data, $type, $fields = false) {
    $validation = new UsersWP_Validation();
    return $validation->validate_fields($data, $type, $fields);
}

/**
 * Returns form field label. If empty returns the field title.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       object      $field      Field info.
 *
 * @return      string                  Label.
 */
function uwp_get_form_label($field) {
    if (isset($field->form_label) && !empty($field->form_label)) {
        $label = __($field->form_label, 'userswp');
    } else {
        $label = __($field->site_title, 'userswp');
    }
    return $label;
}

/**
 * Displays admin settings form.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      string      Form html.
 */
function uwp_display_form() {

    $page = isset( $_GET['page'] ) ? $_GET['page'] : 'userswp';
    $settings_array = uwp_get_settings_tabs();

    $active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $settings_array[$page] ) ? $_GET['tab'] : 'main';
    ob_start();
    ?>
    <form method="post" action="options.php">
        <?php
        $title = apply_filters('uwp_display_form_title', false, $page, $active_tab);
        if ($title) { ?>
            <h2 class="title"><?php echo $title; ?></h2>
        <?php } ?>

        <table class="uwp-form-table">
            <?php
            settings_fields( 'uwp_settings' );
            do_settings_fields( 'uwp_settings_' . $page .'_'.$active_tab, 'uwp_settings_' . $page .'_'.$active_tab );
            ?>
        </table>
        <?php submit_button(); ?>
    </form>

    <?php
    $output = ob_get_contents();
    ob_end_clean();

    return $output;
}