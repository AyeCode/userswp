<?php do_action('uwp_template_before', 'register'); ?>
    <div class="row">
        <div class="mx-auto mw-100" style="width: 20rem;">
            <div class="card card-signin rounded border p-2">
                <div class="card-body">

                    <?php do_action( 'uwp_template_form_title_before', 'register' ); ?>

                    <h3 class="card-title text-center">
                        <?php
                        global $uwp_register_widget_args;
                        $form_title = ! empty( $uwp_register_widget_args['form_title'] ) ? esc_attr__( $uwp_register_widget_args['form_title'], 'userswp' ) : __( 'Register', 'userswp' );
                        echo apply_filters( 'uwp_template_form_title', $form_title, 'register' );
                        ?>
                    </h3>

                    <?php do_action( 'uwp_template_display_notices', 'register' ); ?>

                    <form class="uwp-registration-form uwp_form" method="post" enctype="multipart/form-data">
                        <?php do_action( 'uwp_template_fields', 'register' ); ?>

                        <div class="form-group">
                            <?php do_action( 'uwp_social_fields', 'login' ); ?>
                        </div>

                        <input name="uwp_register_submit" class="btn btn-lg btn-primary btn-block text-uppercase"
                               value="<?php echo __( 'Create Account', 'userswp' ); ?>" type="submit">
                    </form>

                    <div class="uwp-footer-links">
                        <div class="uwp-footer-link"><a rel="nofollow"
                                                        href="<?php echo uwp_get_login_page_url(); ?>"
                                                        class="d-block text-center mt-2 small"><?php _e( 'Login', 'userswp' ); ?></a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
<?php do_action('uwp_template_after', 'register'); ?>