<?php
/**
 * Returns the country array.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @return      array       Country array.
 */
function uwp_get_country_data(){
    $countries = new UsersWP_Countries();
    return $countries->get_country_data();
}

/**
 * Outputs country html.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       string      $value      Country code.
 *
 * @return      string                  Html string.
 */
function uwp_output_country_html($value){
    $countries = new UsersWP_Countries();
    return $countries->output_country_html($value);
}