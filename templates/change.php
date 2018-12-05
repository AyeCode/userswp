<?php do_action('uwp_template_before', 'change'); ?>
    <div class="uwp-content-wrap">
        <div class="uwp-login">
            <div class="uwp-lf-icon"><i class="fas fa-sync fa-fw"></i></div>
            <?php do_action('uwp_template_form_title_before', 'change'); ?>
            <h2><?php echo apply_filters('uwp_template_form_title', get_the_title(), 'change'); ?></h2>
            <?php do_action('uwp_template_display_notices', 'change'); ?>
            <form class="uwp-change-form uwp_form" method="post">
                <?php do_action('uwp_template_fields', 'change'); ?>
                <input name="uwp_change_submit" value="<?php echo __( 'Submit', 'userswp' ); ?>" type="submit"><br>
            </form>
            <div class="uwp-changepsw"><a href="<?php echo uwp_get_page_link('account'); ?>"><?php echo __( 'Account', 'userswp' ); ?></a> <a style="float: right" href="<?php echo uwp_get_page_link('profile'); ?>"><?php echo __( 'Profile', 'userswp' ); ?></a></div>
        </div>
    </div>
<?php do_action('uwp_template_after', 'change'); ?>