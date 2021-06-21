<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $uwp_in_user_loop;
$uwp_in_user_loop = true;
$the_query = isset( $args['template_args']['the_query'] ) ? $args['template_args']['the_query'] : '';
$maximum_pages = isset( $args['template_args']['maximum_pages'] ) ? $args['template_args']['maximum_pages'] : '';
$users = isset( $args['template_args']['users'] ) ? $args['template_args']['users'] : '';
$total_users = isset( $args['template_args']['total_users'] ) ? $args['template_args']['total_users'] : '';
?>
<div class="uwp-users-loop">
    <?php

    // The Loop
    if ( $users ) {

        echo '<div class="row row-cols-1 row-cols-sm-2 '.apply_filters('uwp_users_list_ul_extra_class', '').'">';

        global $uwp_user;
        $original_user = $uwp_user;
        foreach ($users as $uwp_user){

	        uwp_get_template( 'bootstrap/users-item.php', $args );

        }

        $uwp_user = $original_user;
        echo '</div>';

        /* Restore original Post Data */
        wp_reset_postdata();
    } else {
        // no users found
        uwp_no_users_found();
    }
    do_action('uwp_after_users_list');
    ?>
</div><!-- .uwp-users-loop -->

<?php
$uwp_in_user_loop = false;
?>
