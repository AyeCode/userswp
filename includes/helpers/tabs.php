<?php
/**
 * Checks the current page is a UsersWP profile tab page.
 *
 * @since       1.0.0
 * @package     UsersWP
 * @param       string|bool        $tab     Tab slug.
 * @return      bool                        True when success. False when failure.
 */
function is_uwp_profile_tab($tab = false) {
    global $wp_query;
    if (is_uwp_profile_page()) {
        if (isset($wp_query->query_vars['uwp_tab']) && !empty($wp_query->query_vars['uwp_tab'])) {
            if ($wp_query->query_vars['uwp_tab'] == $tab) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 * Checks the current page is a UsersWP profile subtab page.
 *
 * @since       1.0.0
 * @package     UsersWP
 * @param       string|bool        $subtab     Subtab slug.
 * @return      bool                           True when success. False when failure.
 */
function is_uwp_profile_subtab($subtab = false) {
    global $wp_query;
    if (is_uwp_profile_page()) {
        if (isset($wp_query->query_vars['uwp_subtab']) && !empty($wp_query->query_vars['uwp_subtab'])) {
            if ($wp_query->query_vars['uwp_subtab'] == $subtab) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } else {
        return false;
    }
}