<?php do_action('uwp_template_before', 'register'); ?>
<div class="uwp-content-wrap">
    <div class="uwp-registration">
        <div class="uwp-rf-icon"><i class="fa fa-pencil fa-fw"></i></div>
        <?php do_action('uwp_template_form_title_before', 'register'); ?>
        <h2>Create Account</h2>
        <?php do_action('uwp_template_form_title_after', 'register'); ?>
        <form class="uwp-registration-form" method="post">
            <input name="username" placeholder="Username" required="" type="text"><br>
            <input name="first_name" class="uwp-half uwp-half-left" placeholder="First Name" required="" type="text">
            <input name="last_name" class="uwp-half uwp-half-right" placeholder="Last Name" required="" type="text"><br>
            <input name="email" placeholder="Email Address" required="" type="text"><br>
            <input name="password" class="password" placeholder="Password" required="" type="password"><br>
            <input name="confirm_password" class="password" placeholder="Confirm Password" required="" type="password"><br>
            <input name="uwp_register_submit" value="create account" type="submit"><br>
        </form>
        <div class="uwp-login-now">Already a Member? <a rel="nofollow" href="<?php echo uwp_get_page_link('login'); ?>">Login Here</a></div>
    </div>
</div>
<?php do_action('uwp_template_after', 'register'); ?>