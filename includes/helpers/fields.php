<?php
/**
 * Returns the General > Regsiter tab setting fields
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      array       Setting fields
 */
function uwp_settings_general_register_fields() {
    $fields =  array(
        'uwp_registration_action' => array(
            'id' => 'uwp_registration_action',
            'name' => __('Registration Action', 'userswp'),
            'desc' => __('Select how registration should be handled.', 'userswp'),
            'type' => 'select',
            'global' => false,
            'options' => uwp_registration_status_options(),
            'chosen' => true,
            'placeholder' => __( 'Select Option', 'userswp' ),
            'class' => 'uwp_label_block',
        ),
        'register_redirect_to' => array(
            'id' => 'register_redirect_to',
            'name' => __( 'Register Redirect Page', 'userswp' ),
            'desc' => __( 'Set the page to redirect the user to after signing up. If no page has been set WordPress default will be used.', 'userswp' ),
            'type' => 'select',
            'options' => uwp_get_pages(),
            'chosen' => true,
            'placeholder' => __( 'Select a page', 'userswp' ),
            'class' => 'uwp_label_block',
        ),
        'register_terms_page' => array(
            'id' => 'register_terms_page',
            'name' => __( 'Register TOS Page', 'userswp' ),
            'desc' => __( 'Terms of Service page. When set "Accept terms and Conditions" checkbox will appear on the register form.', 'userswp' ),
            'type' => 'select',
            'options' => uwp_get_pages(),
            'chosen' => true,
            'placeholder' => __( 'Select a page', 'userswp' ),
            'class' => 'uwp_label_block',
        ),
        'register_admin_notify' => array(
            'id'   => 'register_admin_notify',
            'name' => __( 'Enable admin email notification?', 'userswp' ),
            'desc' => 'When enabled an email will be sent to the admin for every user registration.',
            'type' => 'checkbox',
            'std'  => '0',
            'class' => 'uwp_label_inline',
        ),
    );
    return $fields;
}

/**
 * Returns the General > Login tab setting fields
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      array       Setting fields
 */
function uwp_settings_general_login_fields() {
    $fields =  array(
        'login_redirect_to' => array(
            'id' => 'login_redirect_to',
            'name' => __( 'Login Redirect Page', 'userswp' ),
            'desc' => __( 'Set the page to redirect the user to after logging in. If no page has been set WordPress default will be used.', 'userswp' ),
            'type' => 'select',
            'options' => uwp_get_pages(),
            'chosen' => true,
            'placeholder' => __( 'Select a page', 'userswp' ),
            'class' => 'uwp_label_block',
        ),
    );
    return $fields;
}

/**
 * Returns the General > Login tab setting fields
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      array       Setting fields
 */
function uwp_settings_general_wp_login_fields() {
    $fields =  array(
        'block_wp_login' => array(
            'id'   => 'block_wp_login',
            'name' => __( 'Redirect wp-login.php?', 'userswp' ),
            'desc' => 'When enabled /wp-login.php page will be redirected to UsersWP login page.',
            'type' => 'checkbox',
            'std'  => '0',
            'class' => 'uwp_label_inline',
        ),
    );
    return $fields;
}

/**
 * Returns the General > Logout tab setting fields
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      array       Setting fields
 */
function uwp_settings_general_logout_fields() {
    $fields =  array(
        'logout_redirect_to' => array(
            'id' => 'logout_redirect_to',
            'name' => __( 'Logout Redirect Page', 'userswp' ),
            'desc' => __( 'Set the page to redirect the user to after logging out. If no page has been set WordPress default will be used.', 'userswp' ),
            'type' => 'select',
            'options' => uwp_get_pages(),
            'chosen' => true,
            'placeholder' => __( 'Select a page', 'userswp' ),
            'class' => 'uwp_label_block',
        ),
    );
    return $fields;
}

/**
 * Returns the General > Delete tab setting fields
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      array       Setting fields
 */
function uwp_settings_general_delete_fields() {
    $fields =  array(
        'delete_redirect_to' => array(
            'id' => 'delete_redirect_to',
            'name' => __( 'Delete Redirect Page', 'userswp' ),
            'desc' => __( 'Set the page to redirect the user to after after they delete account. If no page has been set WordPress default will be used.', 'userswp' ),
            'type' => 'select',
            'options' => uwp_get_pages(),
            'chosen' => true,
            'placeholder' => __( 'Select a page', 'userswp' ),
            'class' => 'uwp_label_block',
        ),
    );
    return $fields;
}

/**
 * Returns merged login and logout tab setting fields
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      array       Setting fields
 */
function uwp_settings_general_loginout_fields() {
    $login = uwp_settings_general_login_fields();
    $wp_login = uwp_settings_general_wp_login_fields();
    $logout = uwp_settings_general_logout_fields();

    $fields = array_merge($login, $wp_login, $logout);
    return $fields;
}