<?php do_action('uwp_template_before', 'users'); ?>
<div class="uwp-content-wrap">
    <div class="uwp-users-list">
        <?php do_action('uwp_users_search'); ?>
        <?php do_action('uwp_users_list'); ?>
    </div>
</div>
<?php do_action('uwp_template_after', 'users'); ?>