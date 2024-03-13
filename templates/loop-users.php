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
    <div class="uwp-content-wrap">

        <?php

        do_action('uwp_before_users_list');

        if ($users) {
            ?>

            <ul class="uwp-users-list-wrap <?php echo esc_attr( apply_filters('uwp_users_list_ul_extra_class', '') ); ?>"
                id="uwp_user_items_layout">
                <?php

                global $uwp_user;
                $original_user = $uwp_user;
                do_action( 'uwp_before_user_list_items', $users );

                foreach ($users as $uwp_user){

	                uwp_get_template( 'users-item.php', $args );
                }
                $uwp_user = $original_user;
                do_action( 'uwp_after_user_list_items', $users );

                ?>
            </ul>

            <?php
        } else {
            uwp_no_users_found();
        }

        do_action('uwp_after_users_list');

        ?>
    </div>

<?php do_action('uwp_template_after', 'users'); $uwp_in_user_loop = false; ?>
