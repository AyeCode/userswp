<?php
function get_uwp_page_permalink($type) {
    $page = new Users_WP_Pages();
    return $page->get_page_permalink($type);
}

function get_uwp_register_permalink() {
    $page = new Users_WP_Pages();
    return $page->get_register_permalink();
}

function get_uwp_login_permalink() {
    $page = new Users_WP_Pages();
    return $page->get_login_permalink();
}

function get_uwp_forgot_permalink() {
    $page = new Users_WP_Pages();
    return $page->get_forgot_permalink();
}

function get_uwp_reset_permalink() {
    $page = new Users_WP_Pages();
    return $page->get_reset_permalink();
}

function get_uwp_account_permalink() {
    $page = new Users_WP_Pages();
    return $page->get_account_permalink();
}

function get_uwp_profile_permalink() {
    $page = new Users_WP_Pages();
    return $page->get_profile_permalink();
}

function get_uwp_users_permalink() {
    $page = new Users_WP_Pages();
    return $page->get_users_permalink();
}

function uwp_get_page_link($page_type) {
    $page = new Users_WP_Pages();
    return $page->get_page_link($page_type);
}