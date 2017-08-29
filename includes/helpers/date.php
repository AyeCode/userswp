<?php
/**
 * Converts PHP Date format to jQuery UI date format.
 *
 * @since       1.0.0
 * @package     userswp
 * @param       string      $php_format     Date Format.
 * @return      string                      Formatted Date.
 */
function uwp_date_format_php_to_jqueryui( $php_format ) {
    $date = new UsersWP_Date();
    return $date->date_format_php_to_jqueryui($php_format);
}

/**
 * Converts date from one format to another.
 *
 * @since       1.0.0
 * @package     userswp
 * @param       string      $date_input         Date string to convert.
 * @param       string      $date_to            Date format to convert to.
 * @param       string      $date_from          Date format to convert from.
 * @return      string                          Converted date.
 */
function uwp_date($date_input, $date_to, $date_from = '') {
    $date = new UsersWP_Date();
    return $date->date($date_input, $date_to, $date_from);
}

/**
 * Converts non english date months to english date months.
 *
 * @since       1.0.0
 * @package     userswp
 * @param       string      $non_english_date   Date String.
 * @return      string                          Converted Date.
 */
function uwp_maybe_untranslate_date($non_english_date){
    $date = new UsersWP_Date();
    return $date->maybe_untranslate_date($non_english_date);
}

/**
 * Gets random date.
 *
 * @since       1.0.0
 * @package     userswp
 *
 * @param       int $days_from      Random days from.
 * @param       int $days_to        Random days to.
 *
 * @return      string              Formatted date string.
 */
function uwp_get_random_date( $days_from = 30, $days_to = 0 ) {
    $date = new UsersWP_Date();
    return $date->get_random_date($days_from, $days_to);
}