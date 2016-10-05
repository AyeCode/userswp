<?php do_action('uwp_template_before', 'forgot'); ?>
<div class="uwp-content-wrap">
    <div class="uwp-content-wrap">
        <div class="uwp-login">
            <div class="uwp-lf-icon"><i class="fa fa-user fa-fw"></i></div>
            <?php do_action('uwp_template_form_title_before', 'forgot'); ?>
            <h2>Forgot Password?</h2>
            <?php do_action('uwp_template_form_title_after', 'forgot'); ?>
            <form class="uwp-login-form" method="post">
                <input name="email" placeholder="Email" required="" type="text"><br>
                <input name="uwp_forgot_submit" value="Submit" type="submit"><br>
            </form>
            <div class="uwp-forgotpsw"><a href="<?php echo uwp_get_page_link('login'); ?>">Login?</a></div>
            <div class="clfx"></div>
            <div class="uwp-register-now">Not a Member? <a rel="nofollow" href="<?php echo uwp_get_page_link('register'); ?>">Create Account</a></div>
        </div>
    </div>
</div>
<?php do_action('uwp_template_after', 'forgot'); ?>