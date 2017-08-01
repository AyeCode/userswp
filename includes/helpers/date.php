<?php
function uwp_date_format_php_to_jqueryui( $php_format ) {
    $date = new Users_WP_Date();
    return $date->date_format_php_to_jqueryui($php_format);
}

function uwp_date($date_input, $date_to, $date_from = '') {
    $date = new Users_WP_Date();
    return $date->date($date_input, $date_to, $date_from);
}

function uwp_maybe_untranslate_date($old_date){
    $date = new Users_WP_Date();
    return $date->maybe_untranslate_date($old_date);
}