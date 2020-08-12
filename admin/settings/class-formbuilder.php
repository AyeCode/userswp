<?php
/**
 * The form builder functionality of the plugin.
 *
 * @link       http://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    userswp
 * @subpackage userswp/admin/settings
 */

/**
 * The form builder functionality of the plugin.
 *
 * @package    userswp
 * @subpackage userswp/admin/settings
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UsersWP_Form_Builder {

    public static function output($tab = '') {

        global $current_tab;

        do_action( 'uwp_form_builder_start' );

        // Get current tab/section
        if($tab){
            $current_tab = sanitize_title( $tab);
        }else{
            $current_tab = empty( $_GET['tab'] ) ? 'account' : sanitize_title( $_GET['tab'] );
        }

        // Get tabs for the form builder page
        $tabs = apply_filters( 'uwp_form_builder_tabs_array', array(
            'account' => __( 'Account', 'userswp' ),
            'register' => __( 'Register', 'userswp' ),
        ) );

        ?>
        <div class="wrap">
            <nav class="nav-tab-wrapper uwp-nav-tab-wrapper">
                <?php
                foreach ( $tabs as $name => $label ) {
                    echo '<a href="' . admin_url( 'admin.php?page=uwp_form_builder&tab=' . $name ) . '" id="uwp-form-builder-'. $name . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';
                }
                do_action( 'uwp_form_builder_tabs' );
                ?>
            </nav>
            <h1 class="screen-reader-text"><?php echo esc_html( $tabs[ $current_tab ] ); ?></h1>
            <?php
                do_action( 'uwp_form_builder_tabs_content', $current_tab, $tabs);
                do_action( 'uwp_form_builder_tabs_' . $current_tab, $tabs );
                do_action('uwp_extra_form_builder_content', $current_tab, $tabs);
            ?>
        </div>
        <?php
    }
    
    public function uwp_form_builder($default_tab = 'account')
    {
        ob_start();
        $form_type = (isset($_REQUEST['tab']) && $_REQUEST['tab'] != '') ? sanitize_text_field($_REQUEST['tab']) : $default_tab;
        ?>
        <div class="uwp-panel-heading">
            <h3><?php echo apply_filters('uwp_form_builder_panel_head', ''); ?></h3>
        </div>

        <div id="uwp_form_builder_container" class="clearfix">
            <div class="uwp-form-builder-frame">
                <div class="uwp-side-sortables" id="uwp-available-fields">
                    <h3>
                    <span>
                        <?php echo apply_filters('uwp_form_builder_available_fields_head', __('Add new form field', 'userswp'), $form_type); ?>
                    </span>
                    </h3>

                    <p>
                        <?php
                        $note = sprintf(__('Click on any box below to add a field of that type on %s form. You must use a fieldset to group your fields.', 'userswp'), $form_type);
                        echo apply_filters('uwp_form_builder_available_fields_note', $note, $form_type);
                        ?>
                    </p>

                    <h3>
                        <?php _e('Standard Field', 'userswp'); ?>
                    </h3>

                    <div class="inside">
                        <div id="uwp-form-builder-tab" class="uwp-tabs-panel">
                            <?php do_action('uwp_manage_available_fields', $form_type); ?>
                        </div>
                    </div>
                    <?php
                    $predefined_fields = apply_filters('uwp_predefined_fields_tabs', array('account', 'profile-tabs'));
                    if(in_array($form_type, $predefined_fields)){
                    ?>
                    <h3>
                        <?php _e('Predefined Fields', 'userswp'); ?>
                    </h3>

                    <div class="inside">
                        <div id="uwp-form-builder-tab-predefined" class="uwp-tabs-panel">
                            <?php do_action('uwp_manage_available_fields_predefined', $form_type); ?>
                        </div>
                    </div>
                    <?php }
                    $custom_fields = apply_filters('uwp_custom_fields_tabs', array('account', 'profile-tabs'));
                    if(in_array($form_type, $custom_fields)){ ?>
                    <h3>
		                <?php _e('Custom Fields', 'userswp'); ?>
                    </h3>

                    <div class="inside">
                        <div id="uwp-form-builder-tab-custom" class="uwp-tabs-panel">
			                <?php do_action('uwp_manage_available_fields_custom', $form_type); ?>
                        </div>
                    </div>
                    <?php } ?>
                </div>


                <div class="uwp-side-sortables" id="uwp-selected-fields">

                    <h3>
                        <span>
                            <?php
                            $title = __('List of fields those will appear in the account form', 'userswp');
                            echo apply_filters('uwp_form_builder_selected_fields_head', $title, $form_type); ?>
                        </span>
                    </h3>

                    <p>
                        <?php
                        $note = sprintf(__('Click to expand and view field related settings. You may drag and drop to arrange fields order on %s form too.', 'userswp'), $form_type);
                        echo apply_filters('uwp_form_builder_selected_fields_note', $note, $form_type); ?>
                    </p>

                    <div class="inside">
                        <div id="uwp-form-builder-tab-selected" class="uwp-tabs-panel">
                            <div class="field_row_main">
                                <?php do_action('uwp_manage_selected_fields', $form_type); ?>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <?php
        $output = ob_get_clean();
        
        echo $output;
    }

    public function custom_available_fields($type = '', $form_type)
    {
        ?>
        <input type="hidden" name="form_type" id="form_type" value="<?php echo $form_type; ?>"/>
        <input type="hidden" name="manage_field_type" class="manage_field_type" value="custom_fields">
        <?php
        if ($type == 'predefined') {
            $fields = $this->form_fields_predefined($form_type);
        }elseif ($type == 'custom') {
            $fields = $this->form_fields_custom($form_type);
        } else {
            $fields = $this->form_fields($form_type);
            ?>
            <ul class="full">
                <li class="uwp-tooltip-wrap">
                    <a id="uwp-fieldset"
                       class="uwp-draggable-form-items uwp-fieldset"
                       href="javascript:void(0);"
                       data-field-custom-type=""
                       data-field-type="fieldset"
                       data-field-type-key="fieldset">
                        <i class="fas fa-long-arrow-alt-left " aria-hidden="true"></i>
                        <i class="fas fa-long-arrow-alt-right " aria-hidden="true"></i>
                        <?php _e('Fieldset (section separator)', 'userswp');
                        echo uwp_help_tip(__('This adds a section separator with a title.', 'userswp'));
                        ?>
                    </a>
                </li>
            </ul>

            <?php
        }

        if (!empty($fields)) {
            ?>
            <ul>
            <?php
            foreach ($fields as $id => $field) {
                ?>
                <li class="uwp-tooltip-wrap">
                    <a id="uwp-<?php echo $id; ?>"
                       data-field-custom-type="<?php echo $type; ?>"
                       data-field-type-key="<?php echo $id; ?>"
                       data-field-type="<?php echo $field['field_type']; ?>"
                       class="uwp-draggable-form-items"
                       href="javascript:void(0);">

                        <?php if (isset($field['icon']) && strpos($field['icon'], ' fa-') !== false) {
                            echo '<i class="' . $field['icon'] . '" aria-hidden="true"></i>';
                        } elseif (isset($field['icon']) && $field['icon']) {
                            echo '<b style="background-image: url("' . $field['icon'] . '")"></b>';
                        } else {
                            echo '<i class="fas fa-cog" aria-hidden="true"></i>';
                        }

                        echo ' '.$field['name'];

                        if (isset($field['description']) && $field['description']) {
	                        echo uwp_help_tip($field['description']);
                        } ?>
                    </a>
                </li>
                <?php
            }
        } else {
            _e('There are no custom fields here yet.', 'userswp');
        }
        ?>
        </ul>

        <?php

    }

    public function form_fields_predefined($type = '') {
        $custom_fields = array();

        // Country
        $custom_fields['uwp_country'] = array( // The key value should be unique and not contain any spaces.
            'field_type'  =>  'select',
            'class'       =>  'uwp-country',
            'icon'        =>  'fas fa-map-marker-alt',
            'name'        =>  __('Country', 'userswp'),
            'description' =>  __('Adds a input for Country field.', 'userswp'),
            'defaults'    => array(
                'admin_title'         =>  'Country',
                'site_title'          =>  'Country',
                'htmlvar_name'        =>  'uwp_country',
                'is_active'           =>  1,
                'default_value'       =>  '',
                'is_required'         =>  0,
                'option_values'       =>  '',
                'required_msg'        =>  '',
                'field_icon'          =>  'fas fa-map-marker-alt',
                'css_class'           =>  ''
            )
        );

        // Gender
        $custom_fields['gender'] = array( // The key value should be unique and not contain any spaces.
            'field_type'  =>  'select',
            'class'       =>  'uwp-gender',
            'icon'        =>  'fas fa-user',
            'name'        =>  __('Gender', 'userswp'),
            'description' =>  __('Adds a input for Gender field.', 'userswp'),
            'defaults'    => array(
                'admin_title'         =>  'Gender',
                'site_title'          =>  'Gender',
                'htmlvar_name'        =>  'gender',
                'is_active'           =>  1,
                'default_value'       =>  '',
                'is_required'         =>  0,
                'option_values'       =>  __('Select Gender/,Male,Female,Other', 'userswp'),
                'required_msg'        =>  '',
                'field_icon'          =>  'fas fa-user',
                'css_class'           =>  ''
            )
        );

        $custom_fields['dob'] = array( // The key value should be unique and not contain any spaces.
            'field_type'  => 'datepicker',
            'class'       => 'uwp-dob',
            'icon'        => 'fas fa-birthday-cake',
            'name'        => __( 'Date of birth', 'userswp' ),
            'description' => __( 'Adds a date input for users to enter their date of birth.', 'userswp' ),
            'defaults'    => array(
                'data_type'          => 'DATE',
                'admin_title'        => __( 'Date of birth', 'userswp' ),
                'site_title'     => __( 'Date of birth', 'userswp' ),
                'form_label'      => __( 'Enter your date of birth.', 'userswp' ),
                'htmlvar_name'       => 'dob',
                'is_active'          => true,
                'for_admin_use'      => false,
                'default_value'      => '',
                'is_required'        => false,
                'validation_pattern' => '',
                'validation_msg'     => '',
                'required_msg'       => '',
                'field_icon'         => 'fas fa-birthday-cake',
                'css_class'          => '',
                'cat_sort'           => true,
                'cat_filter'         => true,
                'extra_fields'       => array(
                    'date_range'        => 'c-100:c+0'
                )
            )
        );

        // Mobile
        $custom_fields['mobile'] = array( // The key value should be unique and not contain any spaces.
            'field_type'  =>  'phone',
            'class'       =>  'uwp-mobile',
            'icon'        =>  'fas fa-mobile-alt',
            'name'        =>  __('Mobile', 'userswp'),
            'description' =>  __('Adds a input for Mobile field.', 'userswp'),
            'defaults'    => array(
                'admin_title'         =>  'Mobile',
                'site_title'          =>  'Mobile',
                'htmlvar_name'        =>  'mobile',
                'is_active'           =>  1,
                'default_value'       =>  '',
                'is_required'         =>  0,
                'required_msg'        =>  '',
                'field_icon'          =>  'fas fa-mobile-alt',
                'css_class'           =>  ''
            )
        );

	    $custom_fields['register_gdpr'] = array(
		    'field_type'  =>  'checkbox',
		    'class'       =>  'uwp-register-gdpr',
		    'icon'        =>  'fas fa-file',
		    'name'        =>  __('GDPR Policy Page', 'userswp'),
		    'description' =>  __('Adds Register GDPR page.', 'userswp'),
		    'defaults'    => array(
			    'admin_title'         =>  'GDPR Policy',
			    'site_title'          =>  'GDPR Policy',
			    'form_label'          => __( 'GDPR Policy', 'userswp' ),
			    'htmlvar_name'        =>  'register_gdpr',
			    'is_active'           =>  1,
			    'default_value'       =>  '',
			    'is_required'         =>  1,
			    'required_msg'        =>  '',
			    'field_icon'          =>  'fas fa-file',
			    'css_class'           =>  'btn-register-gdpr'
		    )
	    );

	    $custom_fields['register_tos'] = array(
		    'field_type'  =>  'checkbox',
		    'class'       =>  'uwp-register-tos',
		    'icon'        =>  'fas fa-file',
		    'name'        =>  __('Terms & Conditions', 'userswp'),
		    'description' =>  __('Adds Register TOS page.', 'userswp'),
		    'defaults'    => array(
			    'admin_title'         =>  'Terms & Conditions',
			    'site_title'          =>  'Terms & Conditions',
			    'form_label'          => __( 'Terms & Conditions', 'userswp' ),
			    'htmlvar_name'        =>  'register_tos',
			    'is_active'           =>  1,
			    'default_value'       =>  '',
			    'is_required'         =>  1,
			    'required_msg'        =>  '',
			    'field_icon'          =>  'fas fa-file',
			    'css_class'           =>  'btn-register-tos'
		    )
	    );

        // Website
	    $custom_fields['website'] = array( // The key value should be unique and not contain any spaces.
		    'field_type'  =>  'url',
		    'class'       =>  'uwp-website',
		    'icon'        =>  'fas fa-link',
		    'name'        =>  __('Website', 'userswp'),
		    'description' =>  __('Let users enter their website url.', 'userswp'),
		    'defaults'    => array(
			    'admin_title'         =>  'Website',
			    'site_title'          =>  'Website',
			    'form_label'          => __( 'Website', 'userswp' ),
			    'htmlvar_name'        =>  'user_url',
			    'is_active'           =>  1,
			    'default_value'       =>  '',
			    'is_required'         =>  0,
			    'required_msg'        =>  '',
			    'field_icon'          =>  'fas fa-link',
			    'css_class'           =>  'btn-website'
		    )
	    );

        // Facebook
        $custom_fields['facebook'] = array( // The key value should be unique and not contain any spaces.
            'field_type'  =>  'url',
            'class'       =>  'uwp-facebook',
            'icon'        =>  'fab fa-facebook-square',
            'name'        =>  __('Facebook', 'userswp'),
            'description' =>  __('Let users enter their facebook url.', 'userswp'),
            'defaults'    => array(
                'admin_title'         =>  'Facebook',
                'site_title'          =>  'Facebook',
                'form_label'          => __( 'Facebook url', 'userswp' ),
                'htmlvar_name'        =>  'facebook',
                'is_active'           =>  1,
                'default_value'       =>  '',
                'is_required'         =>  0,
                'required_msg'        =>  '',
                'field_icon'          =>  'fab fa-facebook-f',
                'css_class'           =>  'btn-facebook'
            )
        );

        // Twitter
        $custom_fields['twitter'] = array( // The key value should be unique and not contain any spaces.
            'field_type'  =>  'url',
            'class'       =>  'uwp-twitter',
            'icon'        =>  'fab fa-twitter-square',
            'name'        =>  __('Twitter', 'userswp'),
            'description' =>  __('Let users enter their twitter url.', 'userswp'),
            'defaults'    => array(
                'admin_title'         =>  'Twitter',
                'site_title'          =>  'Twitter',
                'form_label'          => __( 'Twitter url', 'userswp' ),
                'htmlvar_name'        =>  'twitter',
                'is_active'           =>  1,
                'default_value'       =>  '',
                'is_required'         =>  0,
                'required_msg'        =>  '',
                'field_icon'          =>  'fab fa-twitter',
                'css_class'           =>  'btn-twitter'
            )
        );

        // Instagram
        $custom_fields['instagram'] = array( // The key value should be unique and not contain any spaces.
            'field_type'  =>  'url',
            'class'       =>  'uwp-instagram',
            'icon'        =>  'fab fa-instagram',
            'name'        =>  __('Instagram', 'userswp'),
            'description' =>  __('Let users enter their instagram url.', 'userswp'),
            'defaults'    => array(
                'admin_title'         =>  'Instagram',
                'site_title'          =>  'Instagram',
                'form_label'          => __( 'Instagram url', 'userswp' ),
                'htmlvar_name'        =>  'instagram',
                'is_active'           =>  1,
                'default_value'       =>  '',
                'is_required'         =>  0,
                'required_msg'        =>  '',
                'field_icon'          =>  'fab fa-instagram',
                'css_class'           =>  'btn-instagram'
            )
        );

        // Linkedin
        $custom_fields['linkedin'] = array( // The key value should be unique and not contain any spaces.
            'field_type'  =>  'url',
            'class'       =>  'uwp-linkedin',
            'icon'        =>  'fab fa-linkedin',
            'name'        =>  __('Linkedin', 'userswp'),
            'description' =>  __('Let users enter their linkedin url.', 'userswp'),
            'defaults'    => array(
                'admin_title'         =>  'Linkedin',
                'site_title'          =>  'Linkedin',
                'form_label'          => __( 'Linkedin url', 'userswp' ),
                'htmlvar_name'        =>  'linkedin',
                'is_active'           =>  1,
                'default_value'       =>  '',
                'is_required'         =>  0,
                'required_msg'        =>  '',
                'field_icon'          =>  'fab fa-linkedin-in',
                'css_class'           =>  'btn-linkedin'
            )
        );


        // Flickr
        $custom_fields['flickr'] = array( // The key value should be unique and not contain any spaces.
            'field_type'  =>  'url',
            'class'       =>  'uwp-flickr',
            'icon'        =>  'fab fa-flickr',
            'name'        =>  __('Flickr', 'userswp'),
            'description' =>  __('Let users enter their Flickr url.', 'userswp'),
            'defaults'    => array(
                'admin_title'         =>  'Flickr',
                'site_title'          =>  'Flickr',
                'form_label'          => __( 'Flickr url', 'userswp' ),
                'htmlvar_name'        =>  'flickr',
                'is_active'           =>  1,
                'default_value'       =>  '',
                'is_required'         =>  0,
                'required_msg'        =>  '',
                'field_icon'          =>  'fab fa-flickr',
                'css_class'           =>  'btn-flickr'
            )
        );

        // GitHub
        $custom_fields['github'] = array( // The key value should be unique and not contain any spaces.
            'field_type'  =>  'url',
            'class'       =>  'uwp-github',
            'icon'        =>  'fab fa-github-square',
            'name'        =>  __('GitHub', 'userswp'),
            'description' =>  __('Let users enter their GitHub url.', 'userswp'),
            'defaults'    => array(
                'admin_title'         =>  'GitHub',
                'site_title'          =>  'GitHub',
                'form_label'          => __( 'GitHub url', 'userswp' ),
                'htmlvar_name'        =>  'github',
                'is_active'           =>  1,
                'default_value'       =>  '',
                'is_required'         =>  0,
                'required_msg'        =>  '',
                'field_icon'          =>  'fab fa-github-alt',
                'css_class'           =>  'btn-github'
            )
        );

        // YouTube
        $custom_fields['youtube'] = array( // The key value should be unique and not contain any spaces.
            'field_type'  =>  'url',
            'class'       =>  'uwp-youtube',
            'icon'        =>  'fab fa-youtube-square',
            'name'        =>  __('YouTube', 'userswp'),
            'description' =>  __('Let users enter their YouTube url.', 'userswp'),
            'defaults'    => array(
                'admin_title'         =>  'YouTube',
                'site_title'          =>  'YouTube',
                'form_label'          => __( 'YouTube url', 'userswp' ),
                'htmlvar_name'        =>  'youtube',
                'is_active'           =>  1,
                'default_value'       =>  '',
                'is_required'         =>  0,
                'required_msg'        =>  '',
                'field_icon'          =>  'fab fa-youtube',
                'css_class'           =>  'btn-youtube'
            )
        );

        // WordPress
        $custom_fields['wordpress'] = array( // The key value should be unique and not contain any spaces.
            'field_type'  =>  'url',
            'class'       =>  'uwp-wordpress',
            'icon'        =>  'fab fa-wordpress-simple',
            'name'        =>  __('WordPress', 'userswp'),
            'description' =>  __('Let users enter their WordPress profile url.', 'userswp'),
            'defaults'    => array(
                'admin_title'         =>  'WordPress',
                'site_title'          =>  'WordPress',
                'form_label'          => __( 'WordPress url', 'userswp' ),
                'htmlvar_name'        =>  'wordpress',
                'is_active'           =>  1,
                'default_value'       =>  '',
                'is_required'         =>  0,
                'required_msg'        =>  '',
                'field_icon'          =>  'fab fa-wordpress-simple',
                'css_class'           =>  'btn-wordpress'
            )
        );


        return apply_filters('uwp_form_fields_predefined', $custom_fields, $type);
    }

    public function form_fields_custom($type = '') {
        $custom_fields = array();
        return apply_filters('uwp_form_fields_custom', $custom_fields, $type);
    }

    public function form_fields($type = '') {

        $custom_fields = array(
            'text' => array(
                'field_type'  =>  'text',
                'class' =>  'uwp-text',
                'icon'  =>  'fas fa-minus',
                'name'  =>  __('Text', 'userswp'),
                'description' =>  __('Add any sort of text field, text or numbers', 'userswp')
            ),
            'datepicker' => array(
                'field_type'  =>  'datepicker',
                'class' =>  'uwp-datepicker',
                'icon'  =>  'fas fa-calendar-alt',
                'name'  =>  __('Date', 'userswp'),
                'description' =>  __('Adds a date picker.', 'userswp')
            ),
            'textarea' => array(
                'field_type'  =>  'textarea',
                'class' =>  'uwp-textarea',
                'icon'  =>  'fas fa-bars',
                'name'  =>  __('Textarea', 'userswp'),
                'description' =>  __('Adds a textarea', 'userswp')
            ),
            'time' => array(
                'field_type'  =>  'time',
                'class' =>  'uwp-time',
                'icon' =>  'far fa-clock',
                'name'  =>  __('Time', 'userswp'),
                'description' =>  __('Adds a time picker', 'userswp')
            ),
            'checkbox' => array(
                'field_type'  =>  'checkbox',
                'class' =>  'uwp-checkbox',
                'icon' =>  'far fa-check-square',
                'name'  =>  __('Checkbox', 'userswp'),
                'description' =>  __('Adds a checkbox', 'userswp')
            ),
            'phone' => array(
                'field_type'  =>  'phone',
                'class' =>  'uwp-phone',
                'icon' =>  'fas fa-phone',
                'name'  =>  __('Phone', 'userswp'),
                'description' =>  __('Adds a phone input', 'userswp')
            ),
            'radio' => array(
                'field_type'  =>  'radio',
                'class' =>  'uwp-radio',
                'icon' =>  'far fa-dot-circle',
                'name'  =>  __('Radio', 'userswp'),
                'description' =>  __('Adds a radio input', 'userswp')
            ),
            'email' => array(
                'field_type'  =>  'email',
                'class' =>  'uwp-email',
                'icon' =>  'far fa-envelope',
                'name'  =>  __('Email', 'userswp'),
                'description' =>  __('Adds a email input', 'userswp')
            ),
            'select' => array(
                'field_type'  =>  'select',
                'icon' =>  'far fa-caret-square-down',
                'name'  =>  __('Select', 'userswp'),
                'description' =>  __('Adds a select input', 'userswp')
            ),
            'multiselect' => array(
                'field_type'  =>  'multiselect',
                'class' =>  'uwp-multiselect',
                'icon' =>  'far fa-caret-square-down',
                'name'  =>  __('Multi Select', 'userswp'),
                'description' =>  __('Adds a multiselect input', 'userswp')
            ),
            'url' => array(
                'field_type'  =>  'url',
                'class' =>  'uwp-url',
                'icon' =>  'fas fa-link',
                'name'  =>  __('URL', 'userswp'),
                'description' =>  __('Adds a url input', 'userswp')
            ),
            'editor' => array(
	            'field_type'  =>  'editor',
	            'class' =>  'uwp-html',
	            'icon' =>  'fas fa-code',
	            'name'  =>  __('HTML', 'userswp'),
	            'description' =>  __('Adds a wysiwyg editor input', 'userswp')
            ),
            'file' => array(
                'field_type'  =>  'file',
                'class' =>  'uwp-file',
                'icon' =>  'fas fa-file',
                'name'  =>  __('File Upload', 'userswp'),
                'description' =>  __('Adds a file input', 'userswp')
            )
        );

        return apply_filters('uwp_form_fields', $custom_fields, $type);
    }


    public function manage_available_fields_predefined($form_type) {
        switch ($form_type) {
            case 'account':
                $this->custom_available_fields('predefined', $form_type);
                break;
        }
    }

    public function manage_available_fields_custom($form_type) {
        switch ($form_type) {
            case 'account':
                $this->custom_available_fields('custom', $form_type);
                break;
        }
    }

    public function manage_available_fields($form_type) {
        switch ($form_type) {
            case 'account':
                $this->custom_available_fields('', $form_type);
                break;
            case 'register':
                $this->register_available_fields($form_type);
                break;
        }
    }

    public function manage_selected_fields($form_type) {
        switch ($form_type) {
            case 'account':
                $this->custom_selected_fields($form_type);
                break;
            case 'register':
                $this->register_selected_fields($form_type);
                break;
        }
    }

    public function custom_selected_fields($form_type)
    {

        global $wpdb;
        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
        ?>
        <input type="hidden" name="form_type" id="form_type" value="<?php echo $form_type; ?>"/>
        <input type="hidden" name="manage_field_type" class="manage_field_type" value="custom_fields">
        <ul class="core uwp-tabs-selected uwp_form_extras">
            <?php
            $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s ORDER BY sort_order ASC", array($form_type)));

            if (!empty($fields)) {
                foreach ($fields as $field) {
                    $result_str = $field;
                    $field_type = $field->field_type;
                    $field_type_key = $field->field_type_key;
                    $field_ins_upd = 'display';

                    $this->form_field_adminhtml($field_type, $result_str, $field_ins_upd, $field_type_key);
                }
            }
            ?></ul>
        <?php

    }

    /**
     * @param $field_info
     * @param $field_type
     * @param string $field_type_key
     * @param string $field_ins_upd
     * @param $result_str
     * @param bool $form_type
     */
    public function admin_form_field_html($field_info, $field_type, $field_type_key, $field_ins_upd, $result_str, $form_type = false) {

        if (!$form_type) {
            if (!isset($field_info->form_type)) {
                $form_type = sanitize_text_field($_REQUEST['tab']);
            } else {
                $form_type = $field_info->form_type;
            }
        }

        $cf_arr1 = $this->form_fields($form_type);
        $cf_arr2 = $this->form_fields_predefined($form_type);
        $cf_arr3 = $this->form_fields_custom($form_type);

        $cf_arr = $cf_arr1 + $cf_arr2 + $cf_arr3; // this way defaults can't be overwritten

        $cf = (isset($cf_arr[$field_type_key])) ? $cf_arr[$field_type_key] : '';

        $field_info = stripslashes_deep($field_info); // strip slashes from labels

        $field_site_title = '';
        if (isset($field_info->site_title))
            $field_site_title = $field_info->site_title;

        $field_display = $field_type == 'address' && $field_info->htmlvar_name == 'post' ? 'style="display:none"' : '';

        if (isset($cf['icon']) && strpos($cf['icon'], ' fa-') !== false) {
            $field_icon = '<i class="' . $cf['icon'] . '" aria-hidden="true"></i>';
        }elseif (isset($cf['icon']) && $cf['icon']) {
            $field_icon = '<b style="background-image: url("' . $cf['icon'] . '")"></b>';
        } else {
            $field_icon = '<i class="fas fa-cog" aria-hidden="true"></i>';
        }

        if (isset($cf['name']) && $cf['name']) {
            $field_type_name = $cf['name'];
        } else {
            $field_type_name = $field_type;
        }

        ?>
        <li class="text li-settings" id="licontainer_<?php echo $result_str; ?>">
            <i class="fas fa-caret-down toggle-arrow" aria-hidden="true" onclick="uwp_show_hide(this);"></i>
            <div class="title title<?php echo $result_str; ?> uwp-fieldset">
                <?php
                $nonce = wp_create_nonce('custom_fields_' . $result_str);
                if ($field_type == 'fieldset') {
                    ?>
                    <i class="fas fa-long-arrow-alt-left " aria-hidden="true"></i>
                    <i class="fas fa-long-arrow-alt-right " aria-hidden="true"></i>
                    <b><?php echo uwp_ucwords(__('Fieldset:', 'userswp')); ?></b>
                    <span class="field-type"><?php echo ' ('.uwp_ucwords($field_site_title).')';?></span>
                    <?php
                } else {echo $field_icon;
                    ?>
                    <b><?php echo uwp_ucwords(' ' . $field_site_title ); ?></b>
                    <span class="field-type"><?php echo ' ('.uwp_ucwords($field_type_name).')';?></span>
                    <?php
                }
                ?>
            </div>

            <form>
                <div id="field_frm<?php echo $result_str; ?>" class="field_frm"
                     style="display:<?php if ($field_ins_upd == 'submit') {
                            echo 'block;';
                        } else {
                            echo 'none;';
                        } ?>">
                    <input type="hidden" name="_wpnonce" value="<?php echo esc_attr($nonce); ?>"/>
                    <input type="hidden" name="form_type" id="form_type" value="<?php echo $form_type; ?>"/>
                    <input type="hidden" name="field_type" id="field_type" value="<?php echo $field_type; ?>"/>
                    <input type="hidden" name="field_type_key" id="field_type_key" value="<?php echo $field_type_key; ?>"/>
                    <input type="hidden" name="field_id" id="field_id" value="<?php echo esc_attr($result_str); ?>"/>
                    <input type="hidden" name="is_active" id="is_active" value="1"/>

                    <input type="hidden" name="is_default" value="<?php echo isset($field_info->is_default) ? $field_info->is_default : ''; ?>" /><?php // show in sidebar value?>

                    <ul class="widefat post fixed" style="width:100%;">

                        <?php
                        // data_type
                        if (has_filter("uwp_builder_data_type_{$field_type}")) {

                            echo apply_filters("uwp_builder_data_type_{$field_type}", '', $result_str, $cf, $field_info);

                        } else {
                            $value = '';
                            if (isset($field_info->data_type)) {
                                $value = esc_attr($field_info->data_type);
                            }elseif (isset($cf['defaults']['data_type']) && $cf['defaults']['data_type']) {
                                $value = $cf['defaults']['data_type'];
                            }
                            ?>
                            <input type="hidden" name="data_type" id="data_type" value="<?php echo $value; ?>"/>
                            <?php
                        }
                        
                        // site_title
                        if (has_filter("uwp_builder_site_title_{$field_type}")) {

                            echo apply_filters("uwp_builder_site_title_{$field_type}", '', $result_str, $cf, $field_info);

                        } else {
                            $value = '';
                            if (isset($field_info->site_title)) {
                                $value = esc_attr($field_info->site_title);
                            }elseif (isset($cf['defaults']['site_title']) && $cf['defaults']['site_title']) {
                                $value = $cf['defaults']['site_title'];
                            }
                            ?>
                            <li class="uwp-setting-name">
                                <label for="site_title" class="uwp-tooltip-wrap">
                                    <?php
                                    echo uwp_help_tip(__('This will be the label for the field.', 'userswp'));
                                    _e('Field Label:', 'userswp'); ?>
                                </label>
                                <div class="uwp-input-wrap">
                                    <input type="text" name="site_title" id="site_title"
                                           value="<?php echo $value; ?>"/>
                                </div>
                            </li>
                            <?php
                        }

                        // Input Label
                        if (has_filter("uwp_builder_form_label_{$field_type}")) {

                            echo apply_filters("uwp_builder_form_label_{$field_type}", '', $result_str, $cf, $field_info);

                        } else {
                            $value = '';
                            if (isset($field_info->form_label)) {
                                $value = esc_attr($field_info->form_label);
                            }elseif (isset($cf['defaults']['form_label']) && $cf['defaults']['form_label']) {
                                $value = $cf['defaults']['form_label'];
                            }
                            ?>
                            <li class="uwp-setting-name uwp-advanced-setting">
                                <label for="form_label" class="uwp-tooltip-wrap">
                                    <?php
                                    echo uwp_help_tip(__('If your form label is different, then you can fill this field. Ex: You would like to display "What is your age?" in Form Field but would like to display "DOB" in site. In such cases "What is your age?" should be entered here and "DOB" should be entered in previous field. Note: If this field not filled, then the previous field will be used in Form.', 'userswp'));
                                    _e('Form Label: (Optional)', 'userswp'); ?>
                                </label>
                                <div class="uwp-input-wrap">
                                    <input type="text" name="form_label" id="form_label"
                                           value="<?php echo $value; ?>"/>
                                </div>
                            </li>
                            <?php
                        }

                        // Input Description
                        if (has_filter("uwp_builder_field_description_{$field_type}")) {

	                        echo apply_filters("uwp_builder_field_description_{$field_type}", '', $result_str, $cf, $field_info);

                        } else {
	                        $value = '';
	                        if (isset($field_info->help_text)) {
		                        $value = esc_attr($field_info->help_text);
	                        }elseif (isset($cf['defaults']['help_text']) && $cf['defaults']['help_text']) {
		                        $value = $cf['defaults']['help_text'];
	                        }
	                        ?>
                            <li class="uwp-setting-name">
                                <label for="help_text" class="uwp-tooltip-wrap">
			                        <?php
			                        echo uwp_help_tip(__('This will be displayed below the field in the form.', 'userswp'));
			                        _e('Field Description:', 'userswp'); ?>
                                </label>
                                <div class="uwp-input-wrap">
                                    <input type="text" name="help_text" id="help_text"
                                           value="<?php echo $value; ?>"/>
                                </div>
                            </li>
	                        <?php
                        }


                        // htmlvar_name
                        if(has_filter("uwp_builder_htmlvar_name_{$field_type}")){

                            echo apply_filters("uwp_builder_htmlvar_name_{$field_type}",'',$result_str,$cf,$field_info);

                        }else{
                            $value = '';
                            if (isset($field_info->htmlvar_name)) {
                                $value = esc_attr($field_info->htmlvar_name);
                            }elseif(isset($cf['defaults']['htmlvar_name']) && $cf['defaults']['htmlvar_name']){
                                $value = $cf['defaults']['htmlvar_name'];
                            }
                            ?>
                            <li class="uwp-setting-name uwp-advanced-setting">
                                <label for="htmlvar_name" class="uwp-tooltip-wrap">
                                    <?php
                                    echo uwp_help_tip(__('This is a unique identifier used in the HTML, it MUST NOT contain spaces or special characters.', 'userswp'));
                                    _e('Field Key :', 'userswp');?>
                                </label>
                                <div class="uwp-input-wrap">
                                    <input type="text" name="htmlvar_name" id="htmlvar_name" pattern="[a-zA-Z0-9]+" title="<?php _e('Must not contain spaces or special characters', 'userswp');?>"
                                           value="<?php if ($value) {
                                                echo preg_replace('/uwp_'.$form_type.'_/', '', $value, 1);
                                            }?>" <?php if ( ! empty( $value ) && $value != '' ) { echo 'readonly="readonly"'; } ?> />
                                </div>
                            </li>
                            <?php
                        }

                        // Placeholder text
                        if(has_filter("uwp_builder_placeholder_value_{$field_type}")){

	                        echo apply_filters("uwp_builder_placeholder_value_{$field_type}",'',$result_str,$cf,$field_info);

                        }else{
	                        $value = '';
	                        if (isset($field_info->placeholder_value)) {
		                        $value = esc_attr($field_info->placeholder_value);
	                        }elseif (isset($cf['defaults']['placeholder_value']) && $cf['defaults']['placeholder_value']) {
		                        $value = $cf['defaults']['placeholder_value'];
	                        }
	                        ?>
                            <li class="uwp-setting-name uwp-advanced-setting">
                                <label for="placeholder_value" class="uwp-tooltip-wrap">
                                    <?php
			                        echo uwp_help_tip(__('Display placeholder text for this field.', 'userswp'));
                                    _e('Placeholder :', 'userswp');?>
                                </label>
                                <div class="uwp-input-wrap">
                                    <input type="text" name="placeholder_value" id="placeholder_value_<?php echo $result_str;?>" title="<?php _e('Enter placeholder text for this field.', 'userswp');?>" value="<?php echo $value; ?>" />
                                </div>
                            </li>
	                        <?php
                        }


                        // is_active
                        if (has_filter("uwp_builder_is_active_{$field_type}")) {

                            echo apply_filters("uwp_builder_is_active_{$field_type}", '', $result_str, $cf, $field_info);

                        } else {
                            $value = '';
                            if (isset($field_info->is_active)) {
                                $value = esc_attr($field_info->is_active);
                            }elseif (isset($cf['defaults']['is_active']) && $cf['defaults']['is_active']) {
                                $value = $cf['defaults']['is_active'];
                            }
                            ?>
                            <li <?php echo $field_display; ?>  class="uwp-setting-name">
                                <label for="is_active" class="uwp-tooltip-wrap">
                                    <?php
                                    echo uwp_help_tip(__('If no is selected then the field will not be displayed anywhere.', 'userswp'));
                                    _e('Is active :', 'userswp'); ?>
                                </label>
                                <div class="uwp-input-wrap">
                                    <input type="hidden" name="is_active" value="0" />
                                    <input type="checkbox" name="is_active" value="1" <?php checked( $value, 1, true );?> />
                                </div>
                            </li>
                            <?php
                        }

                        // for_admin_use
                        if (has_filter("uwp_builder_for_admin_use_{$field_type}")) {

                            echo apply_filters("uwp_builder_for_admin_use_{$field_type}", '', $result_str, $cf, $field_info);

                        } else {
                            $value = '';
                            if (isset($field_info->for_admin_use)) {
                                $value = esc_attr($field_info->for_admin_use);
                            }elseif (isset($cf['defaults']['for_admin_use']) && $cf['defaults']['for_admin_use']) {
                                $value = $cf['defaults']['for_admin_use'];
                            }
                            ?>
                            <li <?php echo $field_display; ?> class="uwp-setting-name uwp-advanced-setting">
                                <label for="for_admin_use" class="uwp-tooltip-wrap">
                                    <?php
                                    echo uwp_help_tip(__('If yes is selected then only site admin can see and edit this field.', 'userswp'));
                                    _e('For admin use only? :', 'userswp'); ?>
                                </label>
                                <div class="uwp-input-wrap">
                                    <input type="hidden" name="for_admin_use" value="0" />
                                    <input type="checkbox" name="for_admin_use" value="1" <?php checked( $value, 1, true );?> />
                                </div>
                            </li>
                            <?php
                        }

                        // is_public
                        if ( has_filter( "uwp_builder_is_public_{$field_type}" ) ) {

                            echo apply_filters( "uwp_builder_is_public_{$field_type}", '', $result_str, $cf, $field_info );

                        } else {
                            $value = '';
                            if ( isset( $field_info->is_public ) ) {
                                $value = esc_attr( $field_info->is_public );
                            } elseif ( isset( $cf['defaults']['is_public'] ) && $cf['defaults']['is_public'] ) {
                                $value = $cf['defaults']['is_public'];
                            }
                            ?>
                            <li <?php echo $field_display; ?> class="uwp-setting-name uwp-advanced-setting">
                                <label for="is_public" class="uwp-tooltip-wrap">
                                    <?php
                                    echo uwp_help_tip(__('If no is selected then the field will not be visible to other users.', 'userswp'));
                                    _e( 'Is Public :', 'userswp' ); ?>
                                </label>
                                <div class="uwp-input-wrap">
                                    <?php
                                    if ( ! $value ) {
                                        $value = "1";
                                    }
                                    ?>
                                    <select name="is_public" class="aui-select2">
                                        <option value="1" <?php selected( $value, "1" ); ?>><?php echo __( "Yes", "userswp" ) ?></option>
                                        <option value="0" <?php selected( $value, "0" ); ?>><?php echo __( "No", "userswp" ) ?></option>
                                        <option value="2" <?php selected( $value, "2" ); ?>><?php echo __( "Let User Decide", "userswp" ) ?></option>
                                    </select>

                                </div>
                            </li>
                            <?php
                        }


                        // default_value
                        if (has_filter("uwp_builder_default_value_{$field_type}")) {

                            echo apply_filters("uwp_builder_default_value_{$field_type}", '', $result_str, $cf, $field_info);

                        } else {
                            $value = '';
                            if (isset($field_info->default_value)) {
                                $value = esc_attr($field_info->default_value);
                            }elseif (isset($cf['defaults']['default_value']) && $cf['defaults']['default_value']) {
                                $value = $cf['defaults']['default_value'];
                            }
                            ?>
                            <li class="uwp-setting-name uwp-advanced-setting">
                                <label for="default_value" class="uwp-tooltip-wrap">
                                    <?php
                                    if ($field_type == 'checkbox') {
                                        $tip = __('Should the checkbox be checked by default?', 'userswp');
                                    } else if ($field_type == 'email') {
                                        $tip = __('A default value for the field, usually blank. Ex: info@mysite.com', 'userswp');
                                    } else {
                                        $tip = __('A default value for the field, usually blank. (for links this will be used as the link text)', 'userswp');
                                    }
                                    ?>
                                    <?php
                                    echo uwp_help_tip($tip);
                                    _e('Default value :', 'userswp'); ?>
                                </label>
                                <div class="uwp-input-wrap">
                                    <?php if ($field_type == 'checkbox') { ?>
                                        <select name="default_value" id="default_value" class="aui-select2">
                                            <option value=""><?php _e('Unchecked', 'userswp'); ?></option>
                                            <option value="1" <?php selected(true, (int) $value === 1); ?>><?php _e('Checked', 'userswp'); ?></option>
                                        </select>
                                    <?php } else if ($field_type == 'email') { ?>
                                        <input type="email" name="default_value" placeholder="<?php _e('info@mysite.com', 'userswp'); ?>" id="default_value" value="<?php echo esc_attr($value); ?>" /><br/>
                                    <?php } else { ?>
                                        <input type="text" name="default_value" id="default_value" value="<?php echo esc_attr($value); ?>" /><br/>
                                    <?php } ?>
                                </div>
                            </li>
                            <?php
                        }

                        // advanced_editor
                        if (has_filter("uwp_builder_advanced_editor_{$field_type}")) {

                            echo apply_filters("uwp_builder_advanced_editor_{$field_type}", '', $result_str, $cf, $field_info);

                        }
                        ?>
                        <input type="hidden" readonly="readonly" name="sort_order" id="sort_order" value="<?php if (isset($field_info->sort_order)) { echo esc_attr($field_info->sort_order); } ?>"/>
                        <?php

                        // is_required
                        if (has_filter("uwp_builder_is_required_{$field_type}")) {

                            echo apply_filters("uwp_builder_is_required_{$field_type}", '', $result_str, $cf, $field_info);

                        } else {
                            $value = '';
                            if (isset($field_info->is_required)) {
                                $value = esc_attr($field_info->is_required);
                            }elseif (isset($cf['defaults']['is_required']) && $cf['defaults']['is_required']) {
                                $value = $cf['defaults']['is_required'];
                            }
                            ?>
                            <li class="uwp-setting-name">
                                <label for="is_required" class="uwp-tooltip-wrap">
                                    <?php
                                    echo uwp_help_tip(__('Select yes to set field as required', 'userswp'));
                                    _e('Is required :', 'userswp'); ?>
                                </label>

                                <div class="uwp-input-wrap">
                                    <input type="hidden" name="is_required" value="0" />
                                    <input type="checkbox" name="is_required" value="1" <?php checked( $value, 1, true );?> />
                                </div>

                            </li>

                            <?php
                        }

                        // required_msg
                        if (has_filter("uwp_builder_required_msg_{$field_type}")) {

                            echo apply_filters("uwp_builder_required_msg_{$field_type}", '', $result_str, $cf, $field_info);

                        } else {
                            $value = '';
                            if (isset($field_info->required_msg)) {
                                $value = esc_attr($field_info->required_msg);
                            }elseif (isset($cf['defaults']['required_msg']) && $cf['defaults']['required_msg']) {
                                $value = $cf['defaults']['required_msg'];
                            }
                            ?>
                            <li class="cf-is-required-msg uwp-setting-name uwp-advanced-setting" <?php if ((isset($field_info->is_required) && $field_info->is_required == '0') || !isset($field_info->is_required)) {echo "style='display:none;'"; }?>>
                                <label for="required_msg" class="uwp-tooltip-wrap">
                                    <?php
                                    echo uwp_help_tip(__('Enter text for the error message if the field is required and has not fulfilled the requirements.', 'userswp'));
                                    _e('Required message:', 'userswp'); ?>
                                </label>
                                <div class="uwp-input-wrap">
                                    <input type="text" name="required_msg" id="required_msg"
                                           value="<?php echo esc_attr($value); ?>"/>
                                </div>
                            </li>
                            <?php
                        }

                        // required_msg
                        if (has_filter("uwp_builder_validation_pattern_{$field_type}")) {

                            echo apply_filters("uwp_builder_validation_pattern_{$field_type}", '', $result_str, $cf, $field_info);

                        }

                        // extra_fields
                        if (has_filter("uwp_builder_extra_fields_{$field_type}")) {

                            echo apply_filters("uwp_builder_extra_fields_{$field_type}", '', $result_str, $cf, $field_info);

                        }

                        // field_icon
                        if (has_filter("uwp_builder_field_icon_{$field_type}")) {

                            echo apply_filters("uwp_builder_field_icon_{$field_type}", '', $result_str, $cf, $field_info);

                        } else {
                            $value = '';
                            if (isset($field_info->field_icon)) {
                                $value = esc_attr($field_info->field_icon);
                            }elseif (isset($cf['defaults']['field_icon']) && $cf['defaults']['field_icon']) {
                                $value = $cf['defaults']['field_icon'];
                            }
                            ?>
                            <li class="uwp-setting-name uwp-advanced-setting">

                                <label for="field_icon" class="uwp-tooltip-wrap">
                                    <?php
                                    echo uwp_help_tip(sprintf(__('Upload icon using media and enter its url path, or enter %sfont awesome%s class eg:"fas fa-home"', 'userswp'), '<a href="http://fortawesome.github.io/Font-Awesome/icons/" target="_blank" >', '</a>'));
                                    _e('Upload icon :', 'userswp'); ?>
                                </label>
                                <div class="uwp-input-wrap">
                                    <input type="text" name="field_icon" id="field_icon"
                                           value="<?php echo $value; ?>"/>
                                </div>

                            </li>
                            <?php
                        }


                        // css_class
                        if (has_filter("uwp_builder_css_class_{$field_type}")) {

                            echo apply_filters("uwp_builder_css_class_{$field_type}", '', $result_str, $cf, $field_info);

                        } else {
                            ?>
                            <li class="uwp-setting-name uwp-advanced-setting">

                                <?php
                                $value = '';
                                if (isset($field_info->css_class)) {
                                    $value = esc_attr($field_info->css_class);
                                }elseif (isset($cf['defaults']['css_class']) && $cf['defaults']['css_class']) {
                                    $value = $cf['defaults']['css_class'];
                                }
                                $tip = __('Enter custom css class for field custom style.', 'userswp');
                                if ($field_type == 'multiselect') { $tip .= __('(Enter class `uwp-comma-list` to show list as comma separated)', 'userswp'); }
                                ?>

                                <label for="css_class" class="uwp-tooltip-wrap">
                                    <?php
                                    echo uwp_help_tip($tip);
                                    _e('CSS class :', 'userswp'); ?>
                                </label>
                                <div class="uwp-input-wrap">
                                    <input type="text" name="css_class" id="css_class"
                                           value="<?php echo $value;?>"/>
                                </div>

                            </li>
                            <?php
                        }

                        // show_in
                        if (has_filter("uwp_builder_show_in_{$field_type}")) {

                            echo apply_filters("uwp_builder_show_in_{$field_type}", '', $result_str, $cf, $field_info);

                        } else {
                            $value = '';
                            if (isset($field_info->show_in)) {
                                $value = esc_attr($field_info->show_in);
                            }elseif (isset($cf['defaults']['show_in']) && $cf['defaults']['show_in']) {
                                $value = esc_attr($cf['defaults']['show_in']);
                            }
                            ?>
                            <li class="uwp-setting-name">
                                <label for="show_in" class="uwp-tooltip-wrap">
                                    <?php
                                    echo uwp_help_tip(__('Select in what locations you want to display this field.', 'userswp'));
                                    _e('Show in what locations?:', 'userswp'); ?>
                                </label>
                                <div class="uwp-input-wrap">

                                    <?php

                                    $show_in_locations = uwp_get_show_in_locations();

                                    if ($field_type == 'fieldset') {
                                        unset($show_in_locations['[fieldset]']);
                                    }
                                    
                                    if (!in_array($field_type, array('text', 'datepicker', 'textarea', 'time', 'phone', 'email', 'select', 'multiselect', 'url', 'html', 'fieldset', 'radio', 'checkbox', 'file'))) {
                                        unset($show_in_locations['[own_tab]']);
                                    }
                                    ?>

                                    <select multiple="multiple" name="show_in[]"
                                            style="min-width:300px;"
                                            class="aui-select2"
                                            data-placeholder="<?php _e('Select locations', 'userswp'); ?>">
                                        <?php

                                        $show_in_values = explode(',', $value);

                                        foreach ($show_in_locations as $key => $val) {
                                            $selected = '';

                                            if (is_array($show_in_values) && in_array($key, $show_in_values)) {
                                                $selected = 'selected';
                                            }

                                            ?>
                                            <option  value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $val; ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </li>
                            <?php
                        }

                        do_action('uwp_admin_extra_custom_fields', $field_info, $cf);?>

                        <li>

                            <label for="save" class="uwp-tooltip-wrap">
                            </label>
                            <div class="uwp-input-wrap uwp-tab-actions" data-setting="save_button">
                                <input type="button" class="button button-primary" name="save" id="save" value="<?php echo esc_attr(__('Save', 'userswp')); ?>"
                                       onclick="save_field('<?php echo esc_attr($result_str); ?>')"/>
                                <?php
                                $default_fields = array('email', 'first_name', 'last_name', 'username', 'password');
                                $default_fields = apply_filters('uwp_is_default_field', $default_fields, $field_info);

                                if(isset($field_info->htmlvar_name) && !in_array($field_info->htmlvar_name, $default_fields)): ?>
                                    <a class="item-delete submitdelete deletion" id="delete-16" href="javascript:void(0);" onclick="delete_field('<?php echo esc_attr($result_str); ?>', '<?php echo $nonce; ?>')"><?php _e("Remove","userswp");?></a>
                                <?php endif; ?>
                                <?php UsersWP_Settings_Page::toggle_advanced_button();?>
                            </div>
                        </li>
                    </ul>
                </div>
            </form>
        </li>
    <?php
    }

    public function form_field_adminhtml($field_type, $result_str, $field_ins_upd = '', $field_type_key = '', $form_type = false)
    {
        global $wpdb;
        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
        $cf = $result_str;
        if (!is_object($cf)) {

            $field_info = $wpdb->get_row($wpdb->prepare("select * from " . $table_name . " where id= %d", array($cf)));

        } else {
            $field_info = $cf;
            $result_str = $cf->id;
        }

        $this->admin_form_field_html($field_info, $field_type, $field_type_key, $field_ins_upd, $result_str, $form_type);

    }

    public function admin_form_field_save($request_field = array())
    {

        global $wpdb;

        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';

        $meta_table = get_usermeta_table_prefix() . 'uwp_usermeta';

        $old_html_variable = '';

        $result_str = isset($request_field['field_id']) ? trim($request_field['field_id']) : '';

        $user_meta_info = null;

        // some servers fail if a POST value is VARCHAR so we change it.
        if (isset($request_field['data_type']) && $request_field['data_type'] == 'XVARCHAR') {
            $request_field['data_type'] = 'VARCHAR';
        }
        
        $cf = trim($result_str, '_');

        $cehhtmlvar_name = isset($request_field['htmlvar_name']) ? $request_field['htmlvar_name'] : '';
        $form_type = $request_field['form_type'];

        $old_html_variable_name = 'uwp_account_' . $cehhtmlvar_name;
        $check_old_html_variable = $wpdb->get_var(
            $wpdb->prepare(
                "select htmlvar_name from " . $table_name . " where id <> %d and htmlvar_name = %s and form_type = %s ",
                array($cf,$old_html_variable_name , $form_type)
            )
        );

        $check_html_variable = $wpdb->get_var(
            $wpdb->prepare(
                "select htmlvar_name from " . $table_name . " where id <> %d and htmlvar_name = %s and form_type = %s ",
                array($cf, $cehhtmlvar_name, $form_type)
            )
        );


        if ((!$check_old_html_variable && !$check_html_variable) || $request_field['field_type'] == 'fieldset') {

            if ($cf != '') {

                $user_meta_info = $wpdb->get_row(
                    $wpdb->prepare(
                        "select * from " . $table_name . " where id = %d",
                        array($cf)
                    )
                );

            }

            if (!empty($user_meta_info)) {
                $old_html_variable = $user_meta_info->htmlvar_name;

            }

            $site_title = sanitize_text_field($request_field['site_title']);
            $form_label = isset($request_field['form_label']) ? sanitize_text_field($request_field['form_label']) : '';
            $help_text = isset($request_field['help_text']) ? sanitize_text_field($request_field['help_text']) : '';
            $field_type = sanitize_text_field($request_field['field_type']);
            $data_type = sanitize_text_field($request_field['data_type']);
            $field_type_key = isset($request_field['field_type_key']) ? sanitize_text_field($request_field['field_type_key']) : $field_type;
	        $htmlvar_name = isset( $request_field['htmlvar_name'] ) ? str_replace(array('-',' ','"',"'"), array('_','','',''), sanitize_title_with_dashes( $request_field['htmlvar_name'] ) ) : null;
            $default_value = isset($request_field['default_value']) ? sanitize_text_field($request_field['default_value']) : '';
            $sort_order = isset($request_field['sort_order']) ? absint($request_field['sort_order']) : '';
            $is_active = isset($request_field['is_active']) ? absint($request_field['is_active']) : 1;
            $placeholder_value = isset($request_field['placeholder_value']) ? $request_field['placeholder_value'] : '';
            $for_admin_use = isset($request_field['for_admin_use']) ? absint($request_field['for_admin_use']) : 0;
            $is_required = isset($request_field['is_required']) ? absint($request_field['is_required']) : 0;
            $is_dummy = isset($request_field['is_dummy']) ? absint($request_field['is_dummy']) : 0;
            $is_public = isset($request_field['is_public']) ? absint($request_field['is_public']) : 0;
	        $is_default = isset($request_field['is_default']) ? absint($request_field['is_default']) : 0;
            $is_register_field = isset($request_field['is_register_field']) ? absint($request_field['is_register_field']) : 0;
            $is_search_field = isset($request_field['is_search_field']) ? absint($request_field['is_search_field']) : 0;
            $is_register_only_field = isset($request_field['is_register_only_field']) ? absint($request_field['is_register_only_field']) : 0;
            $required_msg = isset($request_field['required_msg']) ? sanitize_text_field($request_field['required_msg']) : '';
            $css_class = isset($request_field['css_class']) ? sanitize_text_field($request_field['css_class']) : '';
            $field_icon = isset($request_field['field_icon']) ? sanitize_text_field($request_field['field_icon']) : '';
            $show_in = isset($request_field['show_in']) ? $request_field['show_in'] : '';
            $user_roles = isset($request_field['user_roles']) ? $request_field['user_roles'] : '';
            $decimal_point = isset($request_field['decimal_point']) ? absint($request_field['decimal_point']) : ''; // decimal point for DECIMAL data type
            $decimal_point = $decimal_point > 0 ? ($decimal_point > 10 ? 10 : $decimal_point) : '';
            $validation_pattern = isset($request_field['validation_pattern']) ? sanitize_text_field($request_field['validation_pattern']) : '';
            $validation_msg = isset($request_field['validation_msg']) ? sanitize_text_field($request_field['validation_msg']) : '';

	        if ( empty( $htmlvar_name ) && $field_type == 'fieldset') {
		        $htmlvar_name = $field_type_key;
	        }

	        if ( empty( $htmlvar_name ) ) {
		        $htmlvar_name = sanitize_key( str_replace( array( '-', ' ', '"', "'" ), array( '_', '_', '', '' ), $request_field['site_title'] ) );
		        if ( str_replace( '_', '', $htmlvar_name ) != '' ) {
			        $htmlvar_name = substr( $htmlvar_name, 0, 50 );
		        } else {
			        $htmlvar_name = time();
		        }
	        }

            if (is_array($show_in)) {
                $show_in = implode(",", $request_field['show_in']);
                $show_in = sanitize_text_field($show_in);
            }

            if (is_array($user_roles)) {
                $user_roles = implode(",", $request_field['user_roles']);
	            $user_roles = sanitize_text_field($user_roles);
            }

            $option_values = '';
            if (isset($request_field['option_values']))
                $option_values = $request_field['option_values'];

            if (isset($request_field['extra']) && !empty($request_field['extra']))
                $extra_fields = $request_field['extra'];

            if ($sort_order == '') {

                $last_order = $wpdb->get_var("SELECT MAX(sort_order) as last_order FROM " . $table_name);

                $sort_order = (int) $last_order + 1;
            }
            
            if (!empty($user_meta_info)) {

                $excluded = uwp_get_excluded_fields();
                
                if (!in_array($htmlvar_name, $excluded)) {
                    // Create custom columns
                    switch ($field_type):

                        case 'checkbox':
                        case 'multiselect':
                        case 'select':

                            $op_size = '500';

                            // only make the field as big as it needs to be.
                            if (isset($option_values) && $option_values && $field_type == 'select') {
                                $option_values_arr = explode(',', $option_values);
                                if (is_array($option_values_arr)) {
                                    $op_max = 0;
                                    foreach ($option_values_arr as $op_val) {
                                        if (strlen($op_val) && strlen($op_val) > $op_max) {$op_max = strlen($op_val); }
                                    }
                                    if ($op_max) {$op_size = $op_max; }
                                }
                            }elseif (isset($option_values) && $option_values && $field_type == 'multiselect') {
                                if (strlen($option_values)) {
                                    $op_size = strlen($option_values);
                                }
                            }

                            $meta_field_add = "ALTER TABLE " . $meta_table . " CHANGE `" . $old_html_variable . "` `" . $htmlvar_name . "`VARCHAR( $op_size ) NULL";

                            if ($default_value != '') {
                                $meta_field_add .= " DEFAULT '" . $default_value . "'";
                            }

                            $alter_result = $wpdb->query($meta_field_add);
                            if ($alter_result === false) {
                                return __('Column change failed, you may have too many columns.', 'userswp');
                            }

                            if (isset($request_field['cat_display_type']))
                                $extra_fields = $request_field['cat_display_type'];

                            if (isset($request_field['multi_display_type']))
                                $extra_fields = $request_field['multi_display_type'];


                            break;

                        case 'textarea':
                        case 'html':
                        case 'url':
                        case 'file':

                            $alter_result = $wpdb->query("ALTER TABLE " . $meta_table . " CHANGE `" . $old_html_variable . "` `" . $htmlvar_name . "` TEXT NULL");
                            if ($alter_result === false) {
                                return __('Column change failed, you may have too many columns.', 'userswp');
                            }
                            if (isset($request_field['advanced_editor']))
                                $extra_fields = $request_field['advanced_editor'];

                            break;

                        case 'fieldset':
                            // Nothing happened for fieldset
                            break;

                        default:
                            if ($data_type != 'VARCHAR' && $data_type != '') {
                                if ($data_type == 'FLOAT' && $decimal_point > 0) {
                                    $default_value_add = "ALTER TABLE " . $meta_table . " CHANGE `" . $old_html_variable . "` `" . $htmlvar_name . "` DECIMAL(11, " . (int) $decimal_point . ") NULL";
                                } else {
                                    $default_value_add = "ALTER TABLE " . $meta_table . " CHANGE `" . $old_html_variable . "` `" . $htmlvar_name . "` " . $data_type . " NULL";
                                }

                                if (is_numeric($default_value) && $default_value != '') {
                                    $default_value_add .= " DEFAULT '" . $default_value . "'";
                                }
                            } else {
                                $default_value_add = "ALTER TABLE " . $meta_table . " CHANGE `" . $old_html_variable . "` `" . $htmlvar_name . "` VARCHAR( 254 ) NULL";
                                if ($default_value != '') {
                                    $default_value_add .= " DEFAULT '" . $default_value . "'";
                                }
                            }

                            $alter_result = $wpdb->query($default_value_add);
                            if ($alter_result === false) {
                                return __('Column change failed, you may have too many columns.', 'userswp');
                            }
                            break;
                    endswitch;
                }

                $extra_field_query = '';
                if (!empty($extra_fields)) {
                    $extra_field_query = serialize($extra_fields);
                }

                $wpdb->query(

                    $wpdb->prepare(

                        "update " . $table_name . " set
                            form_type = %s,
                            site_title = %s,
                            form_label = %s,
                            help_text = %s,
                            field_type = %s,
                            data_type = %s,
                            decimal_point = %s,
                            field_type_key = %s,
                            htmlvar_name = %s,
                            default_value = %s,
                            sort_order = %s,
                            is_active = %s,
                            placeholder_value = %s,
                            for_admin_use = %s,
                            is_default  = %s,
                            is_required = %s,
                            is_dummy = %s,
                            is_public = %s,
                            is_register_field = %s,
                            is_search_field = %s,
                            is_register_only_field = %s,
                            required_msg = %s,
                            css_class = %s,
                            field_icon = %s,
                            show_in = %s,
                            user_roles = %s,
                            option_values = %s,
                            extra_fields = %s,
                            validation_pattern = %s,
                            validation_msg = %s
                            where id = %d",

                        array(
                            $form_type,
                            $site_title,
                            $form_label,
	                        $help_text,
                            $field_type,
                            $data_type,
                            $decimal_point,
                            $field_type_key,
                            $htmlvar_name,
                            $default_value,
                            $sort_order,
                            $is_active,
	                        $placeholder_value,
                            $for_admin_use,
                            $is_default,
                            $is_required,
                            $is_dummy,
                            $is_public,
                            $is_register_field,
                            $is_search_field,
                            $is_register_only_field,
                            $required_msg,
                            $css_class,
                            $field_icon,
                            $show_in,
                            $user_roles,
                            $option_values,
                            $extra_field_query,
                            $validation_pattern,
                            $validation_msg,
                            $cf
                        )
                    )

                );

                $lastid = trim($cf);

                do_action('uwp_after_custom_fields_updated', $lastid);

            } else {

                switch ($field_type):

                    case 'checkbox':
                        $data_type = 'TINYINT';

                        $meta_field_add = $data_type . "( 1 ) NOT NULL ";
                        if ((int) $default_value === 1) {
                            $meta_field_add .= " DEFAULT '1'";
                        }

                        $add_result = uwp_add_column_if_not_exist($meta_table, $htmlvar_name, $meta_field_add);
                        if ($add_result === false) {
                            return __('Column creation failed, you may have too many columns or the default value does not match with field data type.', 'userswp');
                        }
                        break;
                    case 'multiselect':
                    case 'select':
                        $data_type = 'VARCHAR';
                        $op_size = '500';

                        // only make the field as big as it needs to be.
                        if (isset($option_values) && $option_values && $field_type == 'select') {
                            $option_values_arr = explode(',', $option_values);

                            if (is_array($option_values_arr)) {
                                $op_max = 0;

                                foreach ($option_values_arr as $op_val) {
                                    if (strlen($op_val) && strlen($op_val) > $op_max) {
                                        $op_max = strlen($op_val);
                                    }
                                }

                                if ($op_max) {
                                    $op_size = $op_max;
                                }
                            }
                        } elseif (isset($option_values) && $option_values && $field_type == 'multiselect') {
                            if (strlen($option_values)) {
                                $op_size = strlen($option_values);
                            }

                            if (isset($request_field['multi_display_type'])) {
                                $extra_fields = $request_field['multi_display_type'];
                            }
                        }

                        $meta_field_add = $data_type . "( $op_size ) NULL ";
                        if ($default_value != '') {
                            $meta_field_add .= " DEFAULT '" . $default_value . "'";
                        }

                        $add_result = uwp_add_column_if_not_exist($meta_table, $htmlvar_name, $meta_field_add);
                        if ($add_result === false) {
                            return __('Column creation failed, you may have too many columns or the default value does not match with field data type.', 'userswp');
                        }
                        break;
                    case 'textarea':
                    case 'html':
                    case 'url':
                    case 'file':

                        $data_type = 'TEXT';

                        $meta_field_add = $data_type . " NULL ";

                        $add_result = uwp_add_column_if_not_exist($meta_table, $htmlvar_name, $meta_field_add);
                        if ($add_result === false) {
                            return __('Column creation failed, you may have too many columns or the default value does not match with field data type.', 'userswp');
                        }

                        break;

                    case 'datepicker':

                        $data_type = 'DATE';

                        $meta_field_add = $data_type . " NULL ";

                        $add_result = uwp_add_column_if_not_exist($meta_table, $htmlvar_name, $meta_field_add);
                        if ($add_result === false) {
                            return __('Column creation failed, you may have too many columns or the default value must have in valid date format.', 'userswp');
                        }

                        break;

                    case 'time':

                        $data_type = 'TIME';

                        $meta_field_add = $data_type . " NULL ";

                        $add_result = uwp_add_column_if_not_exist($meta_table, $htmlvar_name, $meta_field_add);
                        if ($add_result === false) {
                            return __('Column creation failed, you may have too many columns or the default value must have in valid time format.', 'userswp');
                        }

                        break;

                    default:

                        if ($data_type != 'VARCHAR' && $data_type != '') {
                            $meta_field_add = $data_type . " NULL ";

                            if ($data_type == 'FLOAT' && $decimal_point > 0) {
                                $meta_field_add = "DECIMAL(11, " . (int) $decimal_point . ") NULL ";
                            }

                            if (is_numeric($default_value) && $default_value != '') {
                                $meta_field_add .= " DEFAULT '" . $default_value . "'";
                            }
                        } else {
                            $meta_field_add = " VARCHAR( 254 ) NULL ";

                            if ($default_value != '') {
                                $meta_field_add .= " DEFAULT '" . $default_value . "'";
                            }
                        }

                        $add_result = uwp_add_column_if_not_exist($meta_table, $htmlvar_name, $meta_field_add);
                        if ($add_result === false) {
                            return __('Column creation failed, you may have too many columns or the default value does not match with field data type.', 'userswp');
                        }
                        break;
                endswitch;

                $extra_field_query = '';
                if (!empty($extra_fields)) {
                    $extra_field_query = serialize($extra_fields);
                }

                $wpdb->query(

                    $wpdb->prepare(

                        "insert into " . $table_name . " set
                            form_type = %s,
                            site_title = %s,
                            form_label = %s,
                            help_text = %s,
                            field_type = %s,
                            data_type = %s,
                            decimal_point = %s,
                            field_type_key = %s,
                            htmlvar_name = %s,
                            default_value = %s,
                            sort_order = %d,
                            is_active = %s,
                            placeholder_value = %s,
                            for_admin_use = %s,
                            is_default  = %s,
                            is_required = %s,
                            is_dummy = %s,
                            is_public = %s,
                            is_register_field = %s,
                            is_search_field = %s,
                            is_register_only_field = %s,
                            required_msg = %s,
                            css_class = %s,
                            field_icon = %s,
                            show_in = %s,
                            user_roles = %s,
                            option_values = %s,
                            extra_fields = %s,
                            validation_pattern = %s,
                            validation_msg = %s ",

                        array(
                            $form_type,
                            $site_title,
                            $form_label,
	                        $help_text,
                            $field_type,
                            $data_type,
                            $decimal_point,
                            $field_type_key,
                            $htmlvar_name,
                            $default_value,
                            $sort_order,
                            $is_active,
	                        $placeholder_value,
                            $for_admin_use,
                            $is_default,
                            $is_required,
                            $is_dummy,
                            $is_public,
                            $is_register_field,
                            $is_search_field,
                            $is_register_only_field,
                            $required_msg,
                            $css_class,
                            $field_icon,
                            $show_in,
                            $user_roles,
                            $option_values,
                            $extra_field_query,
                            $validation_pattern,
                            $validation_msg
                        )

                    )

                );

                $lastid = $wpdb->insert_id;

                $lastid = trim($lastid);

            }

            return (int) $lastid;


        } else {
            return 'invalid_key';
        }

    }

    public function set_field_order($field_ids = array())
    {

        global $wpdb;

        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';

        $count = 0;
        if (!empty($field_ids)):
            $user_meta_info = false;
            foreach ($field_ids as $id) {

                $cf = trim($id, '_');

                $user_meta_info = $wpdb->query(
                    $wpdb->prepare(
                        "update " . $table_name . " set
															sort_order=%d
															where id= %d",
                        array($count, $cf)
                    )
                );
                $count++;
            }

            return $user_meta_info;
        else:
            return false;
        endif;
    }

    public function admin_form_field_delete($field_id = '') {
        global $wpdb;

        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
        $extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';
        $meta_table = get_usermeta_table_prefix() . 'uwp_usermeta';

        if ($field_id != '') {
            $cf = trim($field_id, '_');

            if ($field = $wpdb->get_row($wpdb->prepare("select * from " . $table_name . " where id= %d", array($cf)))) {

                $wpdb->query($wpdb->prepare("delete from " . $table_name . " where id= %d ", array($cf)));

                $form_type = $field->form_type;

                // Also delete register form field
                $wpdb->query($wpdb->prepare("delete from " . $extras_table_name . " where site_htmlvar_name= %s ", array($field->htmlvar_name)));

                // delete the meta column
                $col_name  = sanitize_sql_orderby($field->htmlvar_name);
                $wpdb->query("ALTER TABLE `{$meta_table}` DROP COLUMN $col_name");

                do_action('uwp_after_custom_field_deleted', $cf, $field->htmlvar_name, $form_type);

                return $field_id;
            } else
                return 0;
        } else
            return 0;
    }

    public function builder_extra_fields_smr($output, $result_str, $cf, $field_info) {

        ob_start();

        $value = '';
        if (isset($field_info->option_values)) {
            $value = esc_attr($field_info->option_values);
        }elseif (isset($cf['defaults']['option_values']) && $cf['defaults']['option_values']) {
            $value = esc_attr($cf['defaults']['option_values']);
        }

        $field_type = isset($field_info->field_type) ? $field_info->field_type : $cf['field_type'];
        $field_type_key = isset($field_info->field_type_key) ? $field_info->field_type_key : '';
        if(!$field_type_key){
            $field_type_key = isset($_REQUEST['field_type_key']) ? esc_html($_REQUEST['field_type_key']) : '';
        }
        ?>
        <li class="uwp-setting-name">
            <label for="option_values" class="uwp-tooltip-wrap">
                <?php
                $tip = __('Option Values should be separated by comma.', 'userswp');
                if($field_type != 'multiselect'){
                    $tip .= '<br/><small>'.__('If using for a tick filter place a / and then either a 1 for true or 0 for false', 'userswp');
                    $tip .= '<br/>'.__('eg: No Dogs Allowed/0,Dogs Allowed/1', 'userswp').'</small>';
                }
                if ($field_type == 'multiselect' || $field_type == 'select') {
                    $tip .= '<br/><small>'.__('Like: Apple,Bannana,Pear,Peach', 'userswp');
                    $tip .= '<br/>'.__('Or you can show Selection/Value shown: Pets Allowed/Yes,Pets not Allowed/No', 'userswp');
                    $tip .= '<br/>'.__('- If using OPTGROUP tag to grouping options, use {optgroup}OPTGROUP-LABEL|OPTION-1,OPTION-2{/optgroup}', 'userswp');
                    $tip .= '<br/>'.__('eg: {optgroup}Pets Allowed|No Dogs Allowed/0,Dogs Allowed/1{/optgroup},{optgroup}Sports|Cricket/Cricket,Football/Football,Hockey{/optgroup}', 'userswp').'</small>';
                }
                ?>
                <?php
                echo uwp_help_tip($tip);
                _e('Option Values :', 'userswp'); ?>
            </label>
            <div class="uwp-input-wrap">

                <?php if(isset($field_type_key) && $field_type_key == 'uwp_country' ){

                    // @todo here we should show a multiselect to either include or exclude countries
                    _e('A full country list will be shown','userswp');
                    ?>

                <?php }else{?>
                    <input type="text" name="option_values" id="option_values" value="<?php echo $value; ?>"/>
                <?php }?>

                <br/>

            </div>
        </li>
        <?php

        $html = ob_get_clean();
        return $output . $html;
    }

    public function builder_extra_fields_datepicker($output, $result_str, $cf, $field_info) {
        ob_start();
        $extra = array();
        if (isset($field_info->extra_fields) && $field_info->extra_fields != '') {
            $extra = unserialize($field_info->extra_fields);
        }
        ?>
        <li class="uwp-setting-name uwp-advanced-setting">
            <label for="date_format" class="uwp-tooltip-wrap">
                <?php
                echo uwp_help_tip(__('Select the date format.', 'userswp'));
                _e('Date Format :', 'userswp'); ?>
            </label>
            <div class="uwp-input-wrap" style="overflow:inherit;">
                <?php
                $date_formats = array(
                    'm/d/Y',
                    'd/m/Y',
                    'Y/m/d',
                    'm-d-Y',
                    'd-m-Y',
                    'Y-m-d',
                    'F j, Y',
                );
                
                $date_formats = apply_filters('uwp_date_formats', $date_formats);
                ?>
                <select name="extra[date_format]" id="date_format" class="aui-select2">
                    <?php
                    foreach ($date_formats as $format) {
                        $selected = '';
                        if (!empty($extra) && esc_attr($extra['date_format']) == $format) {
                            $selected = "selected='selected'";
                        }
                        echo "<option $selected value='$format'>$format       (" . date_i18n($format, time()) . ")</option>";
                    }
                    ?>
                </select>

            </div>
        </li>
        <?php

        $html = ob_get_clean();
        return $output . $html;
    }

    public function builder_extra_fields_password($output, $result_str, $cf, $field_info) {
        ob_start();
        
        //confirm password field
        $extra = array();
        if (isset($field_info->extra_fields) && $field_info->extra_fields != '') {
            $extra = unserialize($field_info->extra_fields);
        }
        $value = isset($extra['confirm_password']) ? $extra['confirm_password'] : '1';
        if (isset($field_info->htmlvar_name) && $field_info->htmlvar_name == 'password') {
            ?>
            <li class="uwp-setting-name uwp-advanced-setting">
                <label for="cat_sort" class="uwp-tooltip-wrap">
                    <?php
                    echo uwp_help_tip(__('Lets you display confirm password form field.', 'userswp'));
                    _e('Display confirm password field?:', 'userswp'); ?>
                </label>

                <div class="uwp-input-wrap">
                    <input type="hidden" name="extra[confirm_password]" value="0" />
                    <input type="checkbox" name="extra[confirm_password]" value="1" <?php checked( $value, 1, true );?> onclick="" />
                </div>
            </li>
            <?php
        }
        $html = ob_get_clean();
        return $output . $html;
    }

    public function builder_extra_fields_email($output, $result_str, $cf, $field_info) {
        ob_start();
        //confirm email field
        $extra = array();
        if (isset($field_info->extra_fields) && $field_info->extra_fields != '') {
            $extra = unserialize($field_info->extra_fields);
        }
        $value = isset($extra['confirm_email']) ? $extra['confirm_email'] : '0';

        if (isset($field_info->htmlvar_name) && $field_info->htmlvar_name == 'email') {
            ?>
            <li class="uwp-setting-name uwp-advanced-setting">
                <label for="cat_sort" class="uwp-tooltip-wrap">
                    <?php
                    echo uwp_help_tip(__('Lets you display confirm email form field.', 'userswp'));
                    _e('Display confirm email field?:', 'userswp'); ?>
                </label>

                <div class="uwp-input-wrap">
                    <input type="hidden" name="extra[confirm_email]" value="0" />
                    <input type="checkbox" name="extra[confirm_email]" value="1" <?php checked( $value, 1, true );?> onclick="" />
                </div>
            </li>
            <?php
        }
        $html = ob_get_clean();
        return $output . $html;
    }

    public function builder_extra_fields_file($output, $result_str, $cf, $field_info) {
        ob_start();

        $file_obj = new UsersWP_Files();
        $allowed_file_types = $file_obj->allowed_mime_types();

        $extra_fields = isset($field_info->extra_fields) && $field_info->extra_fields != '' ? maybe_unserialize($field_info->extra_fields) : '';
        $uwp_file_types = !empty($extra_fields) && !empty($extra_fields['uwp_file_types']) ? $extra_fields['uwp_file_types'] : array('*');
        ?>
        <li class="uwp-setting-name uwp-advanced-setting">
            <label for="uwp_file_types" class="uwp-tooltip-wrap">
                <?php
                echo uwp_help_tip(__('Select file types to allowed for file uploading. (Select multiple file types by holding down "Ctrl" key.)', 'userswp'));
                _e('Allowed file types :', 'userswp'); ?>
            </label>
            <div class="uwp-input-wrap">
                <select name="extra[uwp_file_types][]" id="uwp_file_types" multiple="multiple" class="aui-select2" style="height:100px;width:90%;">
                    <option value="*" <?php selected(true, in_array('*', $uwp_file_types)); ?>><?php _e('All types', 'userswp'); ?></option>
                    <?php foreach ($allowed_file_types as $format => $types) { ?>
                        <optgroup label="<?php echo esc_attr(wp_sprintf(__('%s formats', 'userswp'), __($format, 'userswp'))); ?>">
                            <?php foreach ($types as $ext => $type) { ?>
                                <option value="<?php echo esc_attr($ext); ?>" <?php selected(true, in_array($ext, $uwp_file_types)); ?>><?php echo '.' . $ext; ?></option>
                            <?php } ?>
                        </optgroup>
                    <?php } ?>
                </select>
            </div>
        </li>
        <?php

        $html = ob_get_clean();
        return $output . $html;
    }
    
    public function builder_data_type_text($output, $result_str, $cf, $field_info) {
        ob_start();

        $dt_value = '';
        if (isset($field_info->data_type)) {
            $dt_value  = esc_attr($field_info->data_type);
        }elseif (isset($cf['defaults']['data_type']) && $cf['defaults']['data_type']) {
            $dt_value  = $cf['defaults']['data_type'];
        }
        ?>
        <li class="uwp-setting-name uwp-advanced-setting">
            <label for="data_type" class="uwp-tooltip-wrap">
                <?php
                echo uwp_help_tip(__('Select Custom Field type', 'userswp'));
                _e('Field Data Type:', 'userswp'); ?>
            </label>
            <div class="uwp-input-wrap">

                <select name="data_type" id="data_type" class="aui-select2"
                        onchange="uwp_data_type_changed(this, '<?php echo $result_str; ?>');">
                    <option
                        value="XVARCHAR" <?php if ($dt_value == 'VARCHAR') {
                        echo 'selected="selected"';
                    } ?>><?php _e('Text Field', 'userswp'); ?></option>
                    <option
                        value="INT" <?php if ($dt_value == 'INT') {
                        echo 'selected="selected"';
                    } ?>><?php _e('Number Field', 'userswp'); ?></option>
                    <option
                        value="FLOAT" <?php if ($dt_value == 'FLOAT') {
                        echo 'selected="selected"';
                    } ?>><?php _e('Decimal Field', 'userswp'); ?></option>
                </select>

            </div>
        </li>

        <?php
        $value = '';
        if (isset($field_info->decimal_point)) {
            $value = esc_attr($field_info->decimal_point);
        }elseif (isset($cf['defaults']['decimal_point']) && $cf['defaults']['decimal_point']) {
            $value = $cf['defaults']['decimal_point'];
        }
        ?>

        <li class="decimal-point-wrapper uwp-setting-name uwp-advanced-setting"
            style="<?php echo ($dt_value == 'FLOAT') ? '' : 'display:none' ?>">
            <label for="decimal_point" class="uwp-tooltip-wrap">
                <?php
                echo uwp_help_tip(__('Decimal places to display after point', 'userswp'));
                _e('Select decimal precision:', 'userswp'); ?>
            </label>
            <div class="uwp-input-wrap">
                <select name="decimal_point" id="decimal_point" class="aui-select2">
                    <option value=""><?php _e('Select', 'userswp'); ?></option>
                    <?php for ($i = 1; $i <= 10; $i++) {
                        $selected = $i == $value ? 'selected="selected"' : ''; ?>
                        <option value="<?php echo $i; ?>" <?php echo $selected; ?>><?php echo $i; ?></option>
                    <?php } ?>
                </select>
            </div>
        </li>
        <?php

        $output = ob_get_clean();
        return $output;
    }

    public function advance_admin_custom_fields($field_info, $cf) {
        $hide_register_field = (isset($cf['defaults']['is_register_field']) && $cf['defaults']['is_register_field'] === false) ? "style='display:none;'" : '';
        $hide_register_field = (isset($field_info->for_admin_use) && $field_info->for_admin_use == '1') ? "style='display:none;'" : $hide_register_field;

        $value = 0;
        if (isset($field_info->is_register_field)) {
            $value = (int) $field_info->is_register_field;
        } else if (isset($cf['defaults']['is_register_field']) && $cf['defaults']['is_register_field']) {
            $value = ($cf['defaults']['is_register_field']) ? 1 : 0;
        }

        //register only field
        $hide_register_only_field = (isset($cf['defaults']['is_register_only_field']) && $cf['defaults']['is_register_only_field'] === false) ? "style='display:none;'" : '';
        $hide_register_only_field = (isset($field_info->for_admin_use) && $field_info->for_admin_use == '1') ? "style='display:none;'" : $hide_register_only_field;
        $register_only_value = 0;
        if (isset($field_info->is_register_only_field)) {
            $register_only_value = (int) $field_info->is_register_only_field;
        } else if (isset($cf['defaults']['is_register_only_field']) && $cf['defaults']['is_register_only_field']) {
            $register_only_value = ($cf['defaults']['is_register_only_field']) ? 1 : 0;
        }

        ?>
        <li <?php echo $hide_register_field; ?> class="cf-incin-reg-form uwp-setting-name uwp-advanced-setting">
            <label for="cat_sort" class="uwp-tooltip-wrap">
                <?php
                echo uwp_help_tip(__('Lets you use this field as register form field, set from register tab above.', 'userswp'));
                _e('Include this field in register form:', 'userswp'); ?>
            </label>

            <?php
            $reg_only_fields = array('username', 'register_gdpr', 'register_tos');
            $reg_only_fields = apply_filters('uwp_register_mandatory_fields', $reg_only_fields);
            if (isset($field_info->htmlvar_name) && in_array($field_info->htmlvar_name, $reg_only_fields)) {
                ?>
                <div>
                    <input type="hidden" name="is_register_field" value="1" />
                    <p><?php _e('This is mandatory register form field.', 'userswp'); ?></p>
                </div>
                <?php
            } else {
            ?>
                <div class="uwp-input-wrap">
                    <input type="hidden" name="is_register_field" value="0" />
                    <input type="checkbox" name="is_register_field" value="1" <?php checked( $value, 1, true );?> />
                </div>
            <?php } ?>
        </li>

        <li <?php echo $hide_register_only_field; ?> class="cf-inconlyin-reg-form uwp-setting-name uwp-advanced-setting">
            <label for="cat_sort" class="uwp-tooltip-wrap">
                <?php
                echo uwp_help_tip(__('Lets you use this field as register ONLY form field.', 'userswp'));
                _e('Include this field ONLY in register form:', 'userswp'); ?>
            </label>

        <?php
        $reg_only_fields = array('username', 'register_gdpr', 'register_tos');
        $reg_only_fields = apply_filters('uwp_register_only_fields', $reg_only_fields);
        if (isset($field_info->htmlvar_name) && in_array($field_info->htmlvar_name, $reg_only_fields)) {
            ?>
            <div>
                <input type="hidden" name="is_register_only_field" value="1" />
                <p><?php _e('This field is applicable only for register form.', 'userswp'); ?></p>
            </div>
            <?php
        } else {
            ?>
            <div class="uwp-input-wrap">
                <input type="hidden" name="is_register_only_field" value="0" />
                <input type="checkbox" name="is_register_only_field" value="1" <?php checked( $register_only_value, 1, true );?> />
            </div>
        <?php } ?>
        </li>
        <?php
    }

    public function return_empty_string() {
        return "";
    }

    public function register_available_fields_head($heading, $form_type)
    {
        switch ($form_type)
        {
            case 'register':
                $heading = __('Available register form fields.', 'userswp');
                break;
        }
        return $heading;
    }


    public function register_available_fields_note($note, $form_type)
    {
        switch ($form_type)
        {
            case 'register':
                $note = __("Click on any box below to make it appear in register form. To make a field available here, go to account tab and expand any field from selected fields panel and tick the checkbox saying 'Include this field in register form'.", 'userswp');
                break;
        }
        return $note;
    }


    public function register_selected_fields_head($heading, $form_type)
    {
        switch ($form_type)
        {
            case 'register':
                $heading = __('List of fields those will appear in register form.', 'userswp');
                break;

        }
        return $heading;
    }


    public function register_selected_fields_note($note, $form_type)
    {
        switch ($form_type)
        {
            case 'register':
                $note = __('Click to expand and view field related settings. You may drag and drop to arrange fields order in register form.', 'userswp');
                break;

        }
        return $note;
    }

    public function register_available_fields($form_type)
    {
        global $wpdb;

        $extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';

        $existing_fields = $wpdb->get_results("select site_htmlvar_name from " . $extras_table_name . "     where form_type ='" . $form_type . "'");

        $existing_field_ids = array();
        if (!empty($existing_fields)) {
            foreach ($existing_fields as $existing_field) {
                $existing_field_ids[] = $existing_field->site_htmlvar_name;
            }
        }
        ?>
        <input type="hidden" name="form_type" id="form_type" value="<?php echo $form_type; ?>"/>
        <input type="hidden" name="manage_field_type" class="manage_field_type" value="register">
        <ul>
        <?php

            $fields = $this->register_fields($form_type);

            if (!empty($fields)) {
                foreach ($fields as $field) {
                    $field = stripslashes_deep($field); // strip slashes


                    $fieldset_width = '';
                    if ($field['field_type'] == 'fieldset') {
                        $fieldset_width = 'width:100%;';
                    }

                    $display = '';
                    if (in_array($field['htmlvar_name'], $existing_field_ids))
                        $display = 'display:none;';

                    $style = 'style="' . $display . $fieldset_width . '"';
                    ?>
                    <li <?php echo $style; ?> >

                        <a id="uwp-<?php echo $field['htmlvar_name']; ?>"
                           class="uwp-draggable-form-items uwp-<?php echo $field['field_type']; ?>"
                           href="javascript:void(0);" data-field-type="<?php echo $field['field_type']; ?>">

                            <?php if ($icon = uwp_get_field_icon($field['field_icon'])) {
                                echo $icon;
                            } else {
                                echo '<i class="fas fa-cog" aria-hidden="true"></i>';
                            }?>

                            <?php echo $field['site_title']; ?>

                        </a>
                    </li>


                    <?php
                }
            }
            ?>
        </ul>
        <?php
    }

    public function register_fields($form_type)
    {

        global $wpdb;

        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
        $fields = $wpdb->get_results($wpdb->prepare("select field_type, site_title, htmlvar_name, field_icon from " . $table_name . " where form_type = %s and is_register_field=%s order by sort_order asc", array('account', '1')), ARRAY_A);

        return apply_filters('uwp_register_fields', $fields, $form_type);
    }

    public function register_selected_fields($form_type)
    {
        global $wpdb;
        $extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';
        ?>
        <input type="hidden" name="manage_field_type" class="manage_field_type" value="register">
        <ul class="core uwp_form_extras uwp-tabs-selected"><?php

            $fields = $wpdb->get_results(
                $wpdb->prepare(
                    "select * from  " . $extras_table_name . " where form_type = %s order by sort_order asc",
                    array($form_type)
                )
            );

            if (!empty($fields)) {
                foreach ($fields as $field) {
                    $result_str = $field;
                    $field_ins_upd = 'display';

                    $default = false;

                    $this->register_field_adminhtml($result_str, $field_ins_upd, $default);
                }
            }?>
        </ul>
        <?php
    }

    public function register_field_adminhtml($result_str, $field_ins_upd = '', $default = false, $request = array())
    {
        global $wpdb;

        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
        $extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';

        $cf = $result_str;
        if (!is_object($cf) && (is_int($cf) || ctype_digit($cf))) {
            $field_info = $wpdb->get_row($wpdb->prepare("select * from " . $extras_table_name . " where id= %d", array($cf)));
        } elseif (is_object($cf)) {
            //$field_info = $cf;
            $result_str = $cf->id;
            $field_info = $wpdb->get_row($wpdb->prepare("select * from " . $extras_table_name . " where id= %d", array((int) $cf->id)));
        } else {
            $field_info = false;
        }

        if (isset($request['field_type']) && $request['field_type'] != '')
            $field_type = esc_attr($request['field_type']);
        else
            $field_type = $field_info->field_type;


        $field_site_name = '';
        if (isset($request['site_title'])) {
            $field_site_name = $request['site_title'];
        }

        if ($field_info) {
            $account_field_info = $wpdb->get_row($wpdb->prepare("select * from " . $table_name . " where htmlvar_name= %s", array($field_info->site_htmlvar_name)));
            if (isset($account_field_info->site_title)) {
                if ($account_field_info->field_type == 'fieldset') {
                    $field_site_name = __('Fieldset:', 'userswp') . ' ' . $account_field_info->site_title;
                } else {
                    $field_site_name = $account_field_info->site_title;
                }
            }
            $field_info = stripslashes_deep($field_info); // strip slashes
        }
        $field_site_name = sanitize_title($field_site_name);

        if (isset($request['form_type'])) {
            $form_type = esc_attr($request['form_type']);
        } else {
            $form_type = $field_info->form_type;
        }

        if (isset($request['htmlvar_name']) && $request['htmlvar_name'] != '') {
            $htmlvar_name = esc_attr($request['htmlvar_name']);
        } else {
            $htmlvar_name = $field_info->site_htmlvar_name;
        }

        if (isset($htmlvar_name)) {
            if (!is_object($field_info)) {$field_info = new stdClass(); }
            $field_info->field_icon = $wpdb->get_var(
                $wpdb->prepare("SELECT field_icon FROM " . $table_name . " WHERE htmlvar_name = %s", array($htmlvar_name))
            );
        }

	    $icon = isset($field_info->field_icon) ? $field_info->field_icon : '';
	    if ( uwp_is_fa_icon( $icon ) ) {
		    $field_icon = '<i class="' . esc_attr( $icon ) . '" aria-hidden="true"></i>';
	    } elseif ( uwp_is_icon_url( $icon ) ) {
		    $field_icon = '<b style="background-image: url("' . $icon . '")"></b>';
	    } elseif (isset($field_info->field_type) && $field_info->field_type == 'fieldset') {
		    $field_icon = '<i class="fas fa-arrows-alt-h" aria-hidden="true"></i>';
	    } else {
		    $field_icon = '<i class="fas fa-cog" aria-hidden="true"></i>';
	    }
        ?>
        <li class="text li-settings" id="licontainer_<?php echo $result_str; ?>">
            <i class="fas fa-caret-down toggle-arrow" aria-hidden="true" onclick="uwp_show_hide(this);"></i>
            <form>
                <div class="title title<?php echo $result_str; ?> uwp-fieldset">
                    <?php
                    $nonce = wp_create_nonce('uwp_form_extras_nonce' . $result_str);
                    echo $field_icon;
                    ?>
                    <b><?php echo uwp_ucwords(' ' . $field_site_name); ?></b>

                </div>

                <div id="field_frm<?php echo $result_str; ?>" class="field_frm"
                     style="display:<?php if ($field_ins_upd == 'submit') {
                            echo 'block;';
                        } else {
                            echo 'none;';
                        } ?>">
                    <input type="hidden" name="_wpnonce" value="<?php echo $nonce; ?>"/>
                    <input type="hidden" name="field_id" id="field_id" value="<?php echo esc_attr($result_str); ?>"/>
                    <input type="hidden" name="form_type" id="form_type" value="<?php echo $form_type; ?>"/>
                    <input type="hidden" name="field_type" id="field_type" value="<?php echo $field_type; ?>"/>
                    <input type="hidden" name="is_active" id="is_active" value="1"/>
                    <ul class="widefat post fixed" style="width:100%;">

                        <input type="hidden" name="site_htmlvar_name" value="<?php echo $htmlvar_name ?>"/>

                        <li>
                            <div class="uwp-input-wrap">
                                <p><?php _e('No options available', 'userswp'); ?></p>
                            </div>
                        </li>

                        <li>
                            <div class="uwp-input-wrap">
                                <?php
                                $no_actions = array('username', 'email');
                                $no_actions = apply_filters('uwp_register_fields_without_actions', $no_actions);
                                if (!in_array($field_info->site_htmlvar_name, $no_actions)) { ?>
                                <input type="button" class="button button-primary" name="save" id="save"
                                       value="<?php esc_attr_e('Save', 'userswp'); ?>"
                                       onclick="save_field('<?php echo $result_str; ?>', 'register')"/>
                                <input type="button" name="delete" value="<?php esc_attr_e('Delete', 'userswp'); ?>"
                                       onclick="delete_field('<?php echo $result_str; ?>', '<?php echo $nonce; ?>','<?php echo $htmlvar_name ?>', 'register')"
                                       class="button"/>
                                <?php } ?>

                            </div>
                        </li>
                    </ul>

                </div>
            </form>
        </li>
    <?php
    }

	/**
	 * Handles the create custom field ajax request.
	 *
	 * @since       1.0.0
	 * @package     userswp
	 * @return      void
	 */
	public function create_field() {

		$form_type = isset($_REQUEST['form_type']) ? sanitize_text_field($_REQUEST['form_type']) : '';
		$field_type = isset($_REQUEST['field_type']) ? sanitize_text_field($_REQUEST['field_type']) : '';
		$field_type_key = isset($_REQUEST['field_type_key']) ? sanitize_text_field($_REQUEST['field_type_key']) : '';
		$field_action = isset($_REQUEST['field_ins_upd']) ? sanitize_text_field($_REQUEST['field_ins_upd']) : '';
		$field_id = isset($_REQUEST['field_id']) ? sanitize_text_field($_REQUEST['field_id']) : '';

		$field_id = $field_id != '' ? trim($field_id, '_') : $field_id;

		$field_ids = array();
		if (!empty($_REQUEST['licontainer']) && is_array($_REQUEST['licontainer'])) {
			foreach ($_REQUEST['licontainer'] as $lic_id) {
				$field_ids[] = sanitize_text_field($lic_id);
			}
		}

		/* ------- check nonce field ------- */
		if (isset($_REQUEST['update']) && $_REQUEST['update'] == "update" && isset($_REQUEST['create_field']) && isset($_REQUEST['manage_field_type']) && $_REQUEST['manage_field_type'] == 'custom_fields') {
			echo $this->set_field_order($field_ids);
		}

		/* ---- Show field form in admin ---- */
		if ($field_type != '' && $field_id != '' && $field_action == 'new' && isset($_REQUEST['create_field']) && isset($_REQUEST['manage_field_type']) && $_REQUEST['manage_field_type'] == 'custom_fields') {
			$this->form_field_adminhtml($field_type, $field_id, $field_action,$field_type_key, $form_type);
		}


		/* ---- Delete field ---- */
		if ($field_id != '' && $field_action == 'delete' && isset($_REQUEST['_wpnonce']) && isset($_REQUEST['create_field']) && isset($_REQUEST['manage_field_type']) && $_REQUEST['manage_field_type'] == 'custom_fields') {
			if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'custom_fields_' . $field_id))
				return;

			echo $this->admin_form_field_delete($field_id);
		}

		/* ---- Save field  ---- */
		if ($field_id != '' && $field_action == 'submit' && isset($_REQUEST['_wpnonce']) && isset($_REQUEST['create_field']) && isset($_REQUEST['manage_field_type']) && $_REQUEST['manage_field_type'] == 'custom_fields') {
			if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'custom_fields_' . $field_id))
				return;

			foreach ($_REQUEST as $pkey => $pval) {
				if (is_array($_REQUEST[$pkey])) {
					$tags = 'skip_field';
				} else {
					$tags = '';
				}

				if ($tags != 'skip_field') {
					$_REQUEST[$pkey] = strip_tags($_REQUEST[$pkey], $tags);
				}
			}

			$return = $this->admin_form_field_save($_REQUEST);

			if (is_int($return)) {
				$lastid = $return;
				$this->form_field_adminhtml($field_type, $lastid, 'submit',$field_type_key, $form_type);
			} else {
				echo $return;
			}
		}

		wp_die();

	}

    public function register_ajax_handler()
    {
        if (isset($_REQUEST['create_field'])) {
            $field_id = isset($_REQUEST['field_id']) ? trim(sanitize_text_field($_REQUEST['field_id']), '_') : '';
            $field_action = isset($_REQUEST['field_ins_upd']) ? sanitize_text_field($_REQUEST['field_ins_upd']) : '';

            /* ------- check nonce field ------- */
            if (isset($_REQUEST['update']) && $_REQUEST['update'] == 'update') {
                $field_ids = array();
                if (!empty($_REQUEST['licontainer']) && is_array($_REQUEST['licontainer'])) {
                    foreach ($_REQUEST['licontainer'] as $lic_id) {
                        $field_ids[] = sanitize_text_field($lic_id);
                    }
                }

                $return = uwp_form_extras_field_order($field_ids, "register");

                if (is_array($return)) {
                    $return = json_encode($return);
                }

                echo $return;
            }

            /* ---- Show field form in admin ---- */
            if ($field_action == 'new') {
                $form_type = isset($_REQUEST['form_type']) ? sanitize_text_field($_REQUEST['form_type']) : '';
                $fields = $this->register_fields($form_type);


                $_REQUEST['site_field_id'] = isset($_REQUEST['field_id']) ? sanitize_text_field($_REQUEST['field_id']) : '';
                $_REQUEST['is_default'] = '0';

                if (!empty($fields)) {
                    foreach ($fields as $val) {
                        $val = stripslashes_deep($val);

                        if ($val['htmlvar_name'] == $_REQUEST['htmlvar_name']) {
                            $_REQUEST['field_type'] = $val['field_type'];
                            $_REQUEST['site_title'] = $val['site_title'];
                        }
                    }
                }


                $htmlvar_name = isset($_REQUEST['htmlvar_name']) ? sanitize_text_field($_REQUEST['htmlvar_name']) : '';

                $this->register_field_adminhtml($htmlvar_name, $field_action, false, $_REQUEST);
            }

            /* ---- Delete field ---- */
            if ($field_id != '' && $field_action == 'delete' && isset($_REQUEST['_wpnonce'])) {
                if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'uwp_form_extras_nonce' . $field_id))
                    return;

                echo $this->register_field_delete($field_id);
            }

            /* ---- Save field  ---- */
            if ($field_id != '' && $field_action == 'submit' && isset($_REQUEST['_wpnonce'])) {
                if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'uwp_form_extras_nonce' . $field_id))
                    return;

                foreach ($_REQUEST as $pkey => $pval) {
                    $tags = is_array($_REQUEST[$pkey]) ? 'skip_field' : '';

                    if ($tags != 'skip_field') {
                        $_REQUEST[$pkey] = strip_tags(sanitize_text_field($_REQUEST[$pkey]), $tags);
                    }
                }


                $return = $this->register_field_save($_REQUEST);

                if (is_int($return)) {
                    $lastid = $return;

                    $this->register_field_adminhtml($lastid, 'submit');
                } else {
                    echo $return;
                }
            }
        }
        die();
    }

    public function register_field_save($request_field = array())
    {
        global $wpdb;
        $extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';

        $result_str = isset($request_field['field_id']) ? trim($request_field['field_id']) : '';

        $cf = trim($result_str, '_');

        /*-------- check duplicate validation --------*/

        $site_htmlvar_name = isset($request_field['site_htmlvar_name']) ? $request_field['site_htmlvar_name'] : $request_field['htmlvar_name'];
        $form_type = $request_field['form_type'];
        $field_type = $request_field['field_type'];

        $check_html_variable = $wpdb->get_var($wpdb->prepare("select site_htmlvar_name from " . $extras_table_name . " where id <> %d and site_htmlvar_name = %s and form_type = %s ",
            array($cf, $site_htmlvar_name, $form_type)));


        if (!$check_html_variable) {

            if ($cf != '') {

                $user_meta_info = $wpdb->get_row(
                    $wpdb->prepare(
                        "select * from " . $extras_table_name . " where id = %d",
                        array($cf)
                    )
                );

            }

            if ($form_type == '') $form_type = 'register';

            $site_htmlvar_name = $request_field['site_htmlvar_name'];
            $field_id = (isset($request_field['field_id']) && $request_field['field_id']) ? str_replace('new', '', $request_field['field_id']) : '';

            if (!empty($user_meta_info)) {

                $wpdb->query(
                    $wpdb->prepare(
                        "update " . $extras_table_name . " set
					form_type = %s,
					field_type = %s,
					site_htmlvar_name = %s,
					sort_order = %s
					where id = %d",
                        array(
                            $form_type,
                            $field_type,
                            $site_htmlvar_name,
                            $field_id,
                            $cf
                            )

                    )

                );

                $lastid = trim($cf);


            } else {


                $wpdb->query(
                    $wpdb->prepare(

                        "insert into " . $extras_table_name . " set
					form_type = %s,
					field_type = %s,
					site_htmlvar_name = %s,
					sort_order = %s",
                        array($form_type,
                            $field_type,
                            $site_htmlvar_name,
                            $field_id
                        )
                    )
                );
                $lastid = $wpdb->insert_id;
                $lastid = trim($lastid);
            }

            return (int) $lastid;


        } else {
            return 'invalid_key';
        }
    }

    public function register_field_delete($field_id = '')
    {

        global $wpdb;
        $extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';

        if ($field_id != '') {
            $cf = trim($field_id, '_');

            $wpdb->query($wpdb->prepare("delete from " . $extras_table_name . " where id= %d ", array($cf)));

            return $field_id;

        } else
            return 0;
    }
    
}