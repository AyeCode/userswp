<?php do_action('uwp_template_before', 'account'); ?>
<div class="uwp-content-wrap">
    <div class="uwp-registration">
        <div class="uwp-rf-icon"><i class="fa fa-pencil fa-fw"></i></div>
        <?php do_action('uwp_template_form_title_before', 'account'); ?>
        <h2><?php echo __( 'Edit Account', 'users-wp' ); ?></h2>
        <?php do_action('uwp_template_form_title_after', 'account'); ?>
        <form class="uwp-registration-form" method="post">
            <?php do_action('uwp_template_fields', 'account'); ?>
            <input type="hidden" name="uwp_account_nonce" value="<?php echo wp_create_nonce( 'uwp-account-nonce' ); ?>" />
            <input name="uwp_account_submit" value="<?php echo __( 'Update Account', 'users-wp' ); ?>" type="submit">
        </form>
    </div>
</div>
<?php do_action('uwp_template_after', 'account'); ?>