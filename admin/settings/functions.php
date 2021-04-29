<?php
/**
 * Gets UsersWP setting value using key.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string          $key        Setting Key.
 * @param       bool|string     $default    Default value.
 * @param       bool            $cache      Use cache to retrieve the value?.
 *
 * @return      string                      Setting Value.
 */
function uwp_get_option( $key = '', $default = false, $cache = true ) {

    if ($cache) {
        global $uwp_options;
    } else {
        $uwp_options = get_option( 'uwp_settings' );
    }

    $value = isset( $uwp_options[ $key ] ) ? $uwp_options[ $key ] : $default;
    $value = apply_filters( 'uwp_get_option', $value, $key, $default );
    return apply_filters( 'uwp_get_option_' . $key, $value, $key, $default );

}

/**
 * Updates UsersWP setting value using key.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string|bool     $key        Setting Key.
 * @param       string          $value      Setting Value.
 *
 * @return      bool                        Update success or not?.
 */
function uwp_update_option( $key = false, $value = '') {

    if (!$key ) {
        return false;
    }

    $settings = get_option( 'uwp_settings', array());

    if( !is_array( $settings ) ) {
        $settings = array();
    }

    $settings[ $key ] = $value;

    $settings = apply_filters( 'uwp_update_option', $settings, $key, $value );
    $settings =  apply_filters( 'uwp_update_option_' . $key, $settings, $key, $value );

    $updated = update_option( 'uwp_settings', $settings );

    if ( $updated ) {
        global $uwp_options;
        $uwp_options[ $key ] = $value;
    }

    return $updated;

}

/**
 * Deletes UsersWP setting value using key.
 *
 * @package     userswp
 *
 * @param       string|bool     $key        Setting Key.
 *
 * @return      bool                        delete success or not?.
 */
function uwp_delete_option( $key = '' ) {

    if ( empty( $key ) ) {
        return false;
    }

    $options = get_option( 'uwp_settings' );
    if ( empty( $options ) ) {
        $options = array();
    }

    if ( isset( $options[ $key ] ) ) {
        unset( $options[ $key ] );
    }

    $updated = update_option( 'uwp_settings', $options );

    if ( $updated ) {
        global $uwp_options;
        $uwp_options = $options;
    }

    return $updated;
}

/**
 * Get UWP Settings.
 *
 * Retrieves all plugin settings.
 *
 * @return array UWP settings
 */
function uwp_get_settings() {
    $settings = get_option( 'uwp_settings' );

    if ( empty( $settings ) ) {
        // Update old settings with new single option.
        $settings = array();

        update_option( 'uwp_settings', $settings );
    }

    return apply_filters( 'uwp_get_settings', $settings );
}

function uwp_get_register_only_fields(){
	$reg_only_fields = array('username', 'register_gdpr', 'register_tos', 'subscribe');
	return apply_filters('uwp_register_mandatory_fields', $reg_only_fields);
}