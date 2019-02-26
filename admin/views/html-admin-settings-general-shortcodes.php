<?php

/**
 * Admin View: General Settings
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<h3><?php echo __( 'Flexible, Lightweight and Fast', 'userswp' ); ?></h3>
<p><?php echo __( 'UsersWP allows you to add a customizable register and login form to your website.
        It also adds an extended profile page that override the default author page.
        UsersWP has been built to be lightweight and fast', 'userswp' ); ?></p>

<h3><?php echo __( 'Less options, more hooks', 'userswp' ); ?></h3>
<p><?php echo __( 'We cut down the options to the bare minimum and you will not find any fancy
        styling options in this plugin as we believe they belong in your theme.
        This doesn\'t mean that you cannot customize the plugin behaviour.
        To do this we provided a long list of Filters and Actions for any developer
        to extend UsersWP to fit their needs.', 'userswp' ); ?></p>

<h3><?php echo __( 'Override Templates', 'userswp' ); ?></h3>
<p><?php echo sprintf(__( 'If you need to change the look and feel of any UsersWP templates,
        simply create a folder named userswp inside your active child theme
        and copy the template you wish to modify in it. You can now modify the template.
        The plugin will use your modified version and you don\'t have to worry about plugin or theme updates.
        %s Click here for examples %s', 'userswp' ), '<a href="https://userswp.io/docs/override-templates/">', '</a>'); ?></p>

<h3><?php echo __( 'Add-ons', 'userswp' ); ?></h3>
<p><?php echo sprintf(__( 'We have a long list of free and premium add-ons that will help you extend users management on your website.
        %s Click here for our official free and premium add-ons %s', 'userswp' ), '<a href="https://userswp.io/downloads/category/addons/">', '</a>'); ?></p>

<h3><?php echo __( 'Available Shortcodes', 'userswp' ); ?></h3>
<table class="uwp-form-table">

    <?php do_action('uwp_before_general_shortcodes_content'); ?>

    <tr valign="top">
        <th scope="row"><?php echo __( 'User Profile Shortcode', 'userswp' ); ?></th>
        <td>
            <span class="short_code">[uwp_profile]</span>
            <span class="description"><?php echo __( 'This is the shortcode for the front end user\'s profile.', 'userswp' ); ?></span>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row"><?php echo __( 'Register Form Shortcode', 'userswp' ); ?></th>
        <td>
            <span class="short_code">[uwp_register]</span>
            <span class="description"><?php echo __( 'This is the shortcode for the front end register form.', 'userswp' ); ?></span>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row"><?php echo __( 'Login Form Shortcode', 'userswp' ); ?></th>
        <td>
            <span class="short_code">[uwp_login]</span>
            <span class="description"><?php echo __( 'This is the shortcode for the front end login form.', 'userswp' ); ?></span>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row"><?php echo __( 'Account Form Shortcode', 'userswp' ); ?></th>
        <td>
            <span class="short_code">[uwp_account]</span>
            <span class="description"><?php echo __( 'This is the shortcode for the front end account form.', 'userswp' ); ?></span>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row"><?php echo __( 'Forgot Password Form Shortcode', 'userswp' ); ?></th>
        <td>
            <span class="short_code">[uwp_forgot]</span>
            <span class="description"><?php echo __( 'This is the shortcode for the front end forgot password form.', 'userswp' ); ?></span>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row"><?php echo __( 'Change Password Form Shortcode', 'userswp' ); ?></th>
        <td>
            <span class="short_code">[uwp_change]</span>
            <span class="description"><?php echo __( 'This is the shortcode for the front end change password form.', 'userswp' ); ?></span>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row"><?php echo __( 'Reset Password Form Shortcode', 'userswp' ); ?></th>
        <td>
            <span class="short_code">[uwp_reset]</span>
            <span class="description"><?php echo __( 'This is the shortcode for the front end reset password form.', 'userswp' ); ?></span>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row"><?php echo __( 'Users List Shortcode', 'userswp' ); ?></th>
        <td>
            <span class="short_code">[uwp_users]</span>
            <span class="description"><?php echo __( 'This is the shortcode for the front end users list.', 'userswp' ); ?></span>
        </td>
    </tr>

    <?php do_action('uwp_after_general_shortcodes_content'); ?>

</table>