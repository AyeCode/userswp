<?php do_action('uwp_template_before', 'login'); ?>
<div class="uwp-content-wrap">
    <div class="uwp-login">
        <div class="uwp-lf-icon"><i class="fa fa-user fa-fw"></i></div>
        <?php do_action('uwp_template_form_title_before', 'login'); ?>
        <h2>Sign In</h2>
        <?php do_action('uwp_template_form_title_after', 'login'); ?>
        <form class="uwp-login-form" method="post">
            <input name="username" placeholder="Username" required=" " type="text"><br>
            <input name="password" class="password" placeholder="Password" required=" " type="password"><br>
            <input name="uwp_login_submit" value="login" type="submit"><br>
            <div class="uwp-forgetmenot">
                <label for="rememberme"><input name="rememberme" id="rememberme" value="forever" type="checkbox"> Remember Me</label>
            </div>
        </form>
        <div class="uwp-forgotpsw"><a href="<?php echo uwp_get_page_link('forgot'); ?>">Forgot password?</a></div>
        <div class="clfx"></div>
        <div class="uwp-register-now">Not a Member? <a rel="nofollow" href="<?php echo uwp_get_page_link('register'); ?>">Create Account</a></div>
    </div>
</div>
<?php do_action('uwp_template_after', 'login'); ?>