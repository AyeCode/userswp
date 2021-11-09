<?php do_action('uwp_template_before', 'change'); ?>
    <div class="uwp-content-wrap">
        <div class="uwp-login">
            <div class="uwp-lf-icon"><i class="fas fa-sync fa-fw"></i></div>
            <?php do_action('uwp_template_form_title_before', 'change'); ?>
            <h2><?php
                $form_title = !empty($args['form_title']) ? esc_attr__($args['form_title'], 'userswp') : __('Change', 'userswp');
                echo apply_filters('uwp_template_form_title', $form_title, 'change');
                ?></h2>
            <?php do_action('uwp_template_display_notices', 'change'); ?>
            <form class="uwp-change-form uwp_form" method="post">
                <?php do_action('uwp_template_fields', 'change'); ?>
                <input name="uwp_change_submit" value="<?php echo __( 'Submit', 'userswp' ); ?>" type="submit"><br>
            </form>
            <div class="uwp-footer-link uwp-changepsw"><a rel="nofollow" href="<?php echo uwp_get_page_link('account'); ?>"><?php echo uwp_get_option("account_link_title") ? uwp_get_option("account_link_title") : __( 'Account', 'userswp' ); ?></a> <a style="float: right" href="<?php echo uwp_get_page_link('profile'); ?>"><?php echo uwp_get_option("profile_link_title") ? uwp_get_option("profile_link_title") : __( 'Profile', 'userswp' ); ?></a></div>
        </div>
    </div>
<?php do_action('uwp_template_after', 'change'); ?>