<?php
/**
 * Returns the country array.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @return      array       Country array.
 */
function uwp_get_country_data(){
    $countries = new Users_WP_Countries();
    return $countries->get_country_data();
}

/**
 * Outputs country html.
 *
 * @since       1.0.0
 * @package     UsersWP
 *
 * @param       string      $value      Country code.
 *
 * @return      string                  Html string.
 */
function uwp_output_country_html($value){
    $countries = new Users_WP_Countries();
    return $countries->output_country_html($value);
}