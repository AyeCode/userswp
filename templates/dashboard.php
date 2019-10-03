<?php do_action('uwp_template_before', 'dashboard');
global $uwp_widget_args;
$user_id = get_current_user_id();
$user_info = get_userdata($user_id);
$display_name = $user_info->data->display_name;
$profile_link = uwp_build_profile_tab_url($user_id);
$hello_text = !empty($uwp_widget_args['dashboard_text']) ? esc_attr__($uwp_widget_args['dashboard_text'],'userswp') : __( 'Hello, %s', 'userswp' );
$display_name = "<a href='$profile_link' >".esc_attr($display_name)."</a>";
$hello_text = sprintf($hello_text,$display_name);
$dashboard_links = !empty($uwp_widget_args['template_args']['dashboard_links']) ? $uwp_widget_args['template_args']['dashboard_links'] : '';
?>
    <div class="uwp-content-wrap">
        <div class="uwp-account" <?php if(!empty($uwp_widget_args['form_padding'])){echo "style='padding:".absint($uwp_widget_args['form_padding'])."px'";}?>>
            <div class="uwp-account-avatar"><a href="<?php echo $profile_link;?>"><?php echo get_avatar( get_current_user_id(), 100 ); ?></a></div>
            <?php do_action('uwp_template_form_title_before', 'dashboard'); ?>
            <h2><?php
                echo apply_filters('uwp_template_form_title',  $hello_text, 'dashboard');
                ?></h2>
            <?php do_action('uwp_template_form_title_after', 'dashboard'); ?>
            <?php do_action('uwp_template_display_notices', 'dashboard'); ?>
            <div class="uwp-dashboard-links">
                <?php
                do_action('uwp_dashboard_links_before',$uwp_widget_args);

                global $userswp;
                $userswp->forms->output_dashboard_links( $dashboard_links );
                
                do_action('uwp_dashboard_links_after',$uwp_widget_args);
                ?>
            </div>
            <div class="uwp-login-links">
                <div class="uwp-logout-link">
                    <a href="<?php echo wp_logout_url();?>"><?php _e("Logout","userswp");?></a>
                </div>
            </div>

        </div>
    </div>
<?php do_action('uwp_template_after', 'dashboard'); ?>