<?php do_action('uwp_template_before', 'account'); ?>
    <div class="uwp-content-wrap">
        <div class="uwp-account">
            <?php
            if (isset($_GET['type'])) {
                $type = strip_tags(esc_sql($_GET['type']));
            } else {
                $type = 'account';
            }
            ?>
            <div class="uwp-account-avatar"><?php echo get_avatar(get_current_user_id(), 100); ?></div>
            <?php do_action('uwp_template_form_title_before', 'account'); ?>
            <h2><?php echo apply_filters('uwp_account_page_title', __('Edit Account', 'userswp'), $type); ?></h2>
            <?php do_action('uwp_template_form_title_after', 'account'); ?>
            <?php do_action('uwp_template_display_notices', 'account'); ?>
            <?php do_action('uwp_account_menu_display'); ?>
            <?php do_action('uwp_account_form_display', $type); ?>
        </div>
    </div>
<?php do_action('uwp_template_after', 'account'); ?>