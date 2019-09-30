<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $uwp_widget_args,$uwp_in_user_loop;
$uwp_in_user_loop = true;
$the_query = isset( $uwp_widget_args['template_args']['the_query'] ) ? $uwp_widget_args['template_args']['the_query'] : '';
$maximum_pages = isset( $uwp_widget_args['template_args']['maximum_pages'] ) ? $uwp_widget_args['template_args']['maximum_pages'] : '';
$users = isset( $uwp_widget_args['template_args']['users'] ) ? $uwp_widget_args['template_args']['users'] : '';
$total_users = isset( $uwp_widget_args['template_args']['total_users'] ) ? $uwp_widget_args['template_args']['total_users'] : '';
?>
<div class="uwp-users-loop">
    <?php


    // The Loop
    if ( $users ) {

        $design_style = ! empty( $uwp_widget_args['design_style'] ) ? esc_attr( $uwp_widget_args['design_style'] ) : uwp_get_option( "design_style", 'bootstrap' );
        $template     = $design_style ? $design_style . "/users-item" : "users-item";

        echo '<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3">';

        global $uwp_user;
        $original_user = $uwp_user;
        foreach ($users as $uwp_user){

            uwp_locate_template( $template );

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
$uwp_in_user_loop = true;
?>
