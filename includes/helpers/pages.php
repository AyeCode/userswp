<?php
function is_uwp_page($type = false) {
    $page = new Users_WP_Pages();
    return $page->is_page($type);
}

function is_uwp_register_page() {
    $page = new Users_WP_Pages();
    return $page->is_register_page();
}

function is_uwp_login_page() {
    $page = new Users_WP_Pages();
    return $page->is_login_page();
}

function is_uwp_forgot_page() {
    $page = new Users_WP_Pages();
    return $page->is_forgot_page();
}

function is_uwp_change_page() {
    $page = new Users_WP_Pages();
    return $page->is_change_page();
}

function is_uwp_reset_page() {
    $page = new Users_WP_Pages();
    return $page->is_reset_page();
}

function is_uwp_account_page() {
    $page = new Users_WP_Pages();
    return $page->is_account_page();
}

function is_uwp_profile_page() {
    $page = new Users_WP_Pages();
    return $page->is_profile_page();
}

function is_uwp_users_page() {
    $page = new Users_WP_Pages();
    return $page->is_users_page();
}

function is_uwp_multi_register_page() {
    $page = new Users_WP_Pages();
    return $page->is_multi_register_page();
}

function is_uwp_current_user_profile_page() {
    $page = new Users_WP_Pages();
    return $page->is_current_user_profile_page();
}

function uwp_get_pages() {
    $page = new Users_WP_Pages();
    return $page->get_pages();
}

function uwp_get_page_slug($page_type = 'register_page') {
    $page = new Users_WP_Pages();
    return $page->get_page_slug($page_type);
}

function uwp_create_page($slug, $option, $page_title = '', $page_content = '', $post_parent = 0, $status = 'publish') {
    $page = new Users_WP_Pages();
    $page->create_page($slug, $option, $page_title, $page_content, $post_parent, $status);
}

function uwp_generate_default_pages() {
    $page = new Users_WP_Pages();
    $page->generate_default_pages();
}