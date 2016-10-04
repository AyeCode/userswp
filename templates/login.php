<div class="uwp-content-wrap">
    <div class="uwp-login">
        <div class="uwp-lf-icon"><i class="fa fa-user fa-fw"></i></div>
        <h2>Sign In</h2>
        <form class="uwp-login-form">
            <input placeholder="Username" required=" " type="text"><br>
            <input class="password" placeholder="Password" required=" " type="password"><br>
            <input value="login" type="submit"><br>
        </form>
        <div class="uwp-forgetmenot"><label for="rememberme"><input name="rememberme" id="rememberme" value="forever" type="checkbox"> Remember Me</label></div>
        <div class="uwp-forgotpsw"><a href="<?php echo uwp_get_page_link('forgot'); ?>">Forgot password?</a></div>
        <div class="clfx"></div>
        <div class="uwp-register-now">Not a Member? <a rel="nofollow" href="<?php echo uwp_get_page_link('register'); ?>">Create Account</a></div>
    </div>
</div>