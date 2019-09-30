<?php

global $uwp_widget_args;
$users = isset( $uwp_widget_args['template_args']['users'] ) ? $uwp_widget_args['template_args']['users'] : '';
$total_users = isset( $uwp_widget_args['template_args']['total_users'] ) ? $uwp_widget_args['template_args']['total_users'] : '';
?>
    <div class="uwp-content-wrap">
        <div class="uwp-users-list">

            <?php

            do_action('uwp_before_users_list');

            if ($users) {
                ?>

                <ul class="uwp-users-list-wrap <?php echo apply_filters('uwp_users_list_ul_extra_class', 'list'); ?>"
                    id="uwp_user_items_layout">
                    <?php

                    global $uwp_user;

                    do_action( 'uwp_before_user_list_items', $users );

                    foreach ($users as $uwp_user){

                        uwp_locate_template( 'users-item' );
                    }

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
    </div>

<?php do_action('uwp_template_after', 'users'); ?>