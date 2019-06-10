<?php do_action('uwp_template_before', 'users'); ?>
<div class="uwp-content-wrap">
    <div class="uwp-users-list">

        <?php do_action('uwp_users_search'); ?>

        <ul class="uwp-users-list-wrap <?php echo apply_filters('uwp_users_list_ul_extra_class', 'list'); ?>" id="uwp_user_items_layout">
            <?php
            $users = get_uwp_users_list();

            if($users){
                do_action( 'uwp_before_user_list_item' );

                foreach ($users as $user){
                    uwp_locate_template( 'users-list' );
                }

                do_action( 'uwp_after_user_list_item' );
            } else {
                uwp_no_users_found();
            }
            ?>
        </ul>
    </div>
</div>
<?php do_action('uwp_template_after', 'users'); ?>