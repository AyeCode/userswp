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

    <tr valign="top">
        <th scope="row"><?php echo __( 'Output Location Shortcode', 'userswp' ); ?></th>
        <td>
            <span class="short_code">[uwp_output_location location=""]</span>
            <span class="description"><?php echo __( 'This is the shortcode for displaying user data at location selected while creating field via form builder. Location can be users, profile_side, more_info', 'userswp' ); ?></span>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row"><?php echo __( 'Profile Header Shortcode', 'userswp' ); ?></th>
        <td>
            <span class="short_code">[uwp_profile_header hide_cover="" hide_avatar="" allow_change=""]</span>
            <span class="description"><?php echo __( 'This is the shortcode for showing displayed user\'s profile picture and cover image . You can hide cover image, avatar with parameters and also disallow changing profile cover image and avatar with value 1. ', 'userswp' ); ?></span>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row"><?php echo __( 'Profile Section Shortcode', 'userswp' ); ?></th>
        <td>
            <span class="short_code">[uwp_profile_section type="" position=""]</span>
            <span class="description"><?php echo __( 'This is the shortcode for wrapping the things left and right on profile page. Type can be open or close. Position can be left or right. Open section requires close section else it can break the layout.', 'userswp' ); ?></span>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row"><?php echo __( 'User Title Shortcode', 'userswp' ); ?></th>
        <td>
            <span class="short_code">[uwp_user_title tag="" user_id=""]</span>
            <span class="description"><?php echo __( 'This is the shortcode for showing displayed user\'s name. You can pass heading tag from h1 to h6 and user_id if you want to display for specific user ID. ', 'userswp' ); ?></span>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row"><?php echo __( 'User Meta Shortcode', 'userswp' ); ?></th>
        <td>
            <span class="short_code">[uwp_user_meta key="" user_id="" show="" css_class=""]</span>
            <span class="description"><?php echo __( 'This is the shortcode for showing displayed user\'s meta value based on key provided. You can pass icon-value, label-value, label, value, value-strip for how to show value. You can user_id if you want to display for specific user ID. ', 'userswp' ); ?></span>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row"><?php echo __( 'Profile tabs Shortcode', 'userswp' ); ?></th>
        <td>
            <span class="short_code">[uwp_profile_tabs]</span>
            <span class="description"><?php echo __( 'This is the shortcode for displaying profile tabs for user\'s profile page. ', 'userswp' ); ?></span>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row"><?php echo __( 'Profile Social Fields Shortcode', 'userswp' ); ?></th>
        <td>
            <span class="short_code">[uwp_profile_social exclude=""]</span>
            <span class="description"><?php echo __( 'This is the shortcode for displaying user\'s social fields which are selected by adding uwp_social class while creating field via form builder. You can pass comma separated keys to exclude from displaying.', 'userswp' ); ?></span>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row"><?php echo __( 'User Actions Shortcode', 'userswp' ); ?></th>
        <td>
            <span class="short_code">[uwp_user_actions]</span>
            <span class="description"><?php echo __( 'This is the shortcode for displaying user\'s actions like add friend, follow etc. ', 'userswp' ); ?></span>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row"><?php echo __( 'Users Search Shortcode', 'userswp' ); ?></th>
        <td>
            <span class="short_code">[uwp_users_search]</span>
            <span class="description"><?php echo __( 'This is the shortcode for displaying search form for searching users.', 'userswp' ); ?></span>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row"><?php echo __( 'Users Loop Actions Shortcode', 'userswp' ); ?></th>
        <td>
            <span class="short_code">[uwp_users_loop_actions]</span>
            <span class="description"><?php echo __( 'This is the shortcode for displaying users loop actions like views and filters.', 'userswp' ); ?></span>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row"><?php echo __( 'Users Loop Shortcode', 'userswp' ); ?></th>
        <td>
            <span class="short_code">[uwp_users_loop]</span>
            <span class="description"><?php echo __( 'This is the shortcode for displaying users list.', 'userswp' ); ?></span>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row"><?php echo __( 'Author box', 'userswp' ); ?></th>
        <td>
            <span class="short_code">[uwp_author_box]</span>
            <span class="description"><?php echo __( 'This is the shortcode for displaying author box.', 'userswp' ); ?></span>
        </td>
    </tr>

    <?php do_action('uwp_after_general_shortcodes_content'); ?>

</table>