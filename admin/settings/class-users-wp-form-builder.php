<?php
/**
 * The form builder functionality of the plugin.
 *
 * @link       http://wpuwpectory.com
 * @since      1.0.0
 *
 * @package    Users_WP
 * @subpackage Users_WP/admin/settings
 */

/**
 * The form builder functionality of the plugin.
 *
 * @package    Users_WP
 * @subpackage Users_WP/admin/settings
 * @author     GeoDirectory Team <info@wpuwpectory.com>
 */
class Users_WP_Form_Builder {

    protected $loader;

    public function __construct() {

    }

    public function uwp_form_builder()
    {
        ?>
        <div class="uwp-panel-heading">
            <h3><?php echo apply_filters('uwp_form_builder_panel_head', '');?></h3>
        </div>

        <div id="uwp_form_builder_container" class="clearfix">
            <div class="uwp-form-builder-frame">
                <div class="uwp-side-sortables" id="uwp-available-fields">
                    <h3 class="hndle">
                    <span>
                        <?php echo apply_filters('uwp_form_builder_available_fields_head', __('Add new form field', 'users-wp')); ?>
                    </span>
                    </h3>

                    <p>
                        <?php
                        $note = sprintf(__('Click on any box below to add a field of that type on add listing form. You must be use a fieldset to group your fields.', 'users-wp'));
                        echo apply_filters('uwp_form_builder_available_fields_note', $note);
                        ?>
                    </p>

                    <h3>
                        <?php _e('Setup New Field','users-wp'); ?>
                    </h3>

                    <div class="inside">
                        <div id="uwp-form-builder-tab" class="uwp-tabs-panel">
                            <?php do_action('uwp_manage_available_fields'); ?>
                        </div>
                    </div>

                    <h3>
                        <?php _e('Predefined Fields','users-wp'); ?>
                    </h3>

                    <div class="inside">
                        <div id="uwp-form-builder-tab" class="uwp-tabs-panel">
                            <?php do_action('uwp_manage_available_fields_predefined'); ?>
                        </div>
                    </div>

                    <h3>
                        <?php _e('Custom Fields','users-wp'); ?>
                    </h3>

                    <div class="inside">
                        <div id="uwp-form-builder-tab" class="uwp-tabs-panel">
                            <?php do_action('uwp_manage_available_fields_custom'); ?>
                        </div>
                    </div>

                </div>


                <div class="uwp-side-sortables" id="uwp-selected-fields">

                    <h3 class="hndle">
                        <span>
                            <?php
                            $title = __('List of fields those will appear on add new listing form', 'users-wp');
                            echo apply_filters('uwp_form_builder_selected_fields_head', $title); ?>
                        </span>
                    </h3>

                    <p>
                        <?php
                        $note = __('Click to expand and view field related settings. You may drag and drop to arrange fields order on add listing form too.', 'users-wp');
                        echo apply_filters('uwp_form_builder_selected_fields_note', $note); ?>
                    </p>

                    <div class="inside">
                        <div id="uwp-form-builder-tab" class="uwp-tabs-panel">
                            <div class="field_row_main">
                                <?php do_action('uwp_manage_selected_fields'); ?>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <?php
    }

    public function uwp_custom_available_fields($type='')
    {
        $form_type = (isset($_REQUEST['subtab']) && $_REQUEST['subtab'] != '') ? sanitize_text_field($_REQUEST['subtab']) : 'register';
        ?>
        <input type="hidden" name="form_type" id="form_type" value="<?php echo $form_type;?>"/>

        <?php
        if($type=='predefined'){
            $fields = $this->uwp_custom_fields_predefined($form_type);
        }elseif($type=='custom'){
            $fields = $this->uwp_custom_fields_custom($form_type);
        }else{
            $fields = $this->uwp_custom_fields($form_type);
            ?>
            <ul class="full gd-cf-tooltip-wrap">
                <li>
                    <div class="uwp-tooltip">
                        <?php _e('This adds a section separator with a title.', 'users-wp');?>
                    </div>
                    <a id="uwp-fieldset"
                       class="uwp-draggable-form-items uwp-fieldset"
                       href="javascript:void(0);"
                       data-field-custom-type=""
                       data-field-type="fieldset"
                       data-field-type-key="fieldset">

                        <i class="fa fa-long-arrow-left " aria-hidden="true"></i>
                        <i class="fa fa-long-arrow-right " aria-hidden="true"></i>
                        <?php _e('Fieldset (section separator)', 'users-wp');?>
                    </a>
                </li>
            </ul>

            <?php
        }

        if(!empty($fields)) {

            foreach ( $fields as $id => $field ) {
                ?>
                <ul>
                <li class="uwp-tooltip-wrap">
                    <?php
                    if ( isset( $field['description'] ) && $field['description'] ) {
                        echo '<div class="uwp-tooltip">' . $field['description'] . '</div>';
                    } ?>

                    <a id="uwp-<?php echo $id; ?>"
                       data-field-custom-type="<?php echo $type; ?>"
                       data-field-type-key="<?php echo $id; ?>"
                       data-field-type="<?php echo $field['field_type']; ?>"
                       class="uwp-draggable-form-items <?php echo $field['class']; ?>"
                       href="javascript:void(0);">

                        <?php if ( isset( $field['icon'] ) && strpos( $field['icon'], 'fa fa-' ) !== false ) {
                            echo '<i class="' . $field['icon'] . '" aria-hidden="true"></i>';
                        } elseif ( isset( $field['icon'] ) && $field['icon'] ) {
                            echo '<b style="background-image: url("' . $field['icon'] . '")"></b>';
                        } else {
                            echo '<i class="fa fa-cog" aria-hidden="true"></i>';
                        } ?>
                        <?php echo $field['name']; ?>
                    </a>
                </li>
                <?php
            }
        }else{
            _e('There are no custom fields here yet.', 'users-wp');
        }
        ?>
        </ul>

        <?php

    }

    public function uwp_custom_fields_predefined($type='') {
        $custom_fields = array();

        $countries = array(
            "Afghanistan",
            "Albania",
            "Algeria",
            "American Samoa",
            "Andorra",
            "Angola",
            "Anguilla",
            "Antarctica",
            "Antigua and Barbuda",
            "Argentina",
            "Armenia",
            "Aruba",
            "Australia",
            "Austria",
            "Azerbaijan",
            "Bahamas",
            "Bahrain",
            "Bangladesh",
            "Barbados",
            "Belarus",
            "Belgium",
            "Belize",
            "Benin",
            "Bermuda",
            "Bhutan",
            "Bolivia",
            "Bosnia and Herzegowina",
            "Botswana",
            "Bouvet Island",
            "Brazil",
            "British Indian Ocean Territory",
            "Brunei Darussalam",
            "Bulgaria",
            "Burkina Faso",
            "Burundi",
            "Cambodia",
            "Cameroon",
            "Canada",
            "Cape Verde",
            "Cayman Islands",
            "Central African Republic",
            "Chad",
            "Chile",
            "China",
            "Christmas Island",
            "Cocos (Keeling) Islands",
            "Colombia",
            "Comoros",
            "Congo",
            "Congo,
             the Democratic Republic of the",
            "Cook Islands",
            "Costa Rica",
            "Cote d'Ivoire",
            "Croatia (Hrvatska)",
            "Cuba",
            "Cyprus",
            "Czech Republic",
            "Denmark",
            "Djibouti",
            "Dominica",
            "Dominican Republic",
            "East Timor",
            "Ecuador",
            "Egypt",
            "El Salvador",
            "Equatorial Guinea",
            "Eritrea",
            "Estonia",
            "Ethiopia",
            "Falkland Islands (Malvinas)",
            "Faroe Islands",
            "Fiji",
            "Finland",
            "France",
            "France Metropolitan",
            "French Guiana",
            "French Polynesia",
            "French Southern Territories",
            "Gabon",
            "Gambia",
            "Georgia",
            "Germany",
            "Ghana",
            "Gibraltar",
            "Greece",
            "Greenland",
            "Grenada",
            "Guadeloupe",
            "Guam",
            "Guatemala",
            "Guinea",
            "Guinea-Bissau",
            "Guyana",
            "Haiti",
            "Heard and Mc Donald Islands",
            "Holy See (Vatican City State)",
            "Honduras",
            "Hong Kong",
            "Hungary",
            "Iceland",
            "India",
            "Indonesia",
            "Iran (Islamic Republic of)",
            "Iraq",
            "Ireland",
            "Israel",
            "Italy",
            "Jamaica",
            "Japan",
            "Jordan",
            "Kazakhstan",
            "Kenya",
            "Kiribati",
            "Korea, Democratic People's Republic of",
            "Korea, Republic of",
            "Kuwait",
            "Kyrgyzstan",
            "Lao, People's Democratic Republic",
            "Latvia",
            "Lebanon",
            "Lesotho",
            "Liberia",
            "Libyan Arab Jamahiriya",
            "Liechtenstein",
            "Lithuania",
            "Luxembourg",
            "Macau",
            "Macedonia, The Former Yugoslav Republic of",
            "Madagascar",
            "Malawi",
            "Malaysia",
            "Maldives",
            "Mali",
            "Malta",
            "Marshall Islands",
            "Martinique",
            "Mauritania",
            "Mauritius",
            "Mayotte",
            "Mexico",
            "Micronesia, Federated States of",
            "Moldova, Republic of",
            "Monaco",
            "Mongolia",
            "Montserrat",
            "Morocco",
            "Mozambique",
            "Myanmar",
            "Namibia",
            "Nauru",
            "Nepal",
            "Netherlands",
            "Netherlands Antilles",
            "New Caledonia",
            "New Zealand",
            "Nicaragua",
            "Niger",
            "Nigeria",
            "Niue",
            "Norfolk Island",
            "Northern Mariana Islands",
            "Norway",
            "Oman",
            "Pakistan",
            "Palau",
            "Panama",
            "Papua New Guinea",
            "Paraguay",
            "Peru",
            "Philippines",
            "Pitcairn",
            "Poland",
            "Portugal",
            "Puerto Rico",
            "Qatar",
            "Reunion",
            "Romania",
            "Russian Federation",
            "Rwanda",
            "Saint Kitts and Nevis",
            "Saint Lucia",
            "Saint Vincent and the Grenadines",
            "Samoa",
            "San Marino",
            "Sao Tome and Principe",
            "Saudi Arabia",
            "Senegal",
            "Seychelles",
            "Sierra Leone",
            "Singapore",
            "Slovakia (Slovak Republic)",
            "Slovenia",
            "Solomon Islands",
            "Somalia",
            "South Africa",
            "South Georgia and the South Sandwich Islands",
            "Spain",
            "Sri Lanka",
            "St. Helena",
            "St. Pierre and Miquelon",
            "Sudan",
            "Suriname",
            "Svalbard and Jan Mayen Islands",
            "Swaziland",
            "Sweden",
            "Switzerland",
            "Syrian Arab Republic",
            "Taiwan, Province of China",
            "Tajikistan",
            "Tanzania, United Republic of",
            "Thailand",
            "Togo",
            "Tokelau",
            "Tonga",
            "Trinidad and Tobago",
            "Tunisia",
            "Turkey",
            "Turkmenistan",
            "Turks and Caicos Islands",
            "Tuvalu",
            "Uganda",
            "Ukraine",
            "United Arab Emirates",
            "United Kingdom",
            "United States",
            "United States Minor Outlying Islands",
            "Uruguay",
            "Uzbekistan",
            "Vanuatu",
            "Venezuela",
            "Vietnam",
            "Virgin Islands (British)",
            "Virgin Islands (U.S.)",
            "Wallis and Futuna Islands",
            "Western Sahara",
            "Yemen",
            "Yugoslavia",
            "Zambia",
            "Zimbabwe"
        );

        $countries_string = implode(',', $countries);

        // Country
        $custom_fields['country'] = array( // The key value should be unique and not contain any spaces.
            'field_type'  =>  'text',
            'class'       =>  'uwp-country',
            'icon'        =>  'fa fa-map-marker',
            'name'        =>  __('Country', 'users-wp'),
            'description' =>  __('Adds a input for Country field.', 'users-wp'),
            'defaults'    => array(
                'data_type'           =>  'VARCHAR',
                'admin_title'         =>  'Country',
                'site_title'          =>  'Country',
                'htmlvar_name'        =>  'country',
                'is_active'           =>  1,
                'default_value'       =>  '',
                'is_required'         =>  0,
                'option_values'       =>  $countries_string,
                'required_msg'        =>  '',
                'field_icon'          =>  'fa fa-map-marker',
                'css_class'           =>  ''
            )
        );

        // Gender
        $custom_fields['gender'] = array( // The key value should be unique and not contain any spaces.
            'field_type'  =>  'text',
            'class'       =>  'uwp-gender',
            'icon'        =>  'fa fa-user',
            'name'        =>  __('Gender', 'users-wp'),
            'description' =>  __('Adds a input for Gender field.', 'users-wp'),
            'defaults'    => array(
                'data_type'           =>  'VARCHAR',
                'admin_title'         =>  'Gender',
                'site_title'          =>  'Gender',
                'htmlvar_name'        =>  'gender',
                'is_active'           =>  1,
                'default_value'       =>  '',
                'is_required'         =>  0,
                'option_values'       =>  __('Select Gender/,Male,Female,Other','users-wp'),
                'required_msg'        =>  '',
                'field_icon'          =>  'fa fa-user',
                'css_class'           =>  ''
            )
        );

        // Mobile
        $custom_fields['mobile'] = array( // The key value should be unique and not contain any spaces.
            'field_type'  =>  'phone',
            'class'       =>  'uwp-mobile',
            'icon'        =>  'fa fa-mobile',
            'name'        =>  __('Mobile', 'users-wp'),
            'description' =>  __('Adds a input for Mobile field.', 'users-wp'),
            'defaults'    => array(
                'data_type'           =>  'VARCHAR',
                'admin_title'         =>  'Mobile',
                'site_title'          =>  'Mobile',
                'htmlvar_name'        =>  'mobile',
                'is_active'           =>  1,
                'default_value'       =>  '',
                'is_required'         =>  0,
                'required_msg'        =>  '',
                'field_icon'          =>  'fa fa-mobile',
                'css_class'           =>  ''
            )
        );

        // Facebook
        $custom_fields['facebook'] = array( // The key value should be unique and not contain any spaces.
            'field_type'  =>  'text',
            'class'       =>  'uwp-facebook',
            'icon'        =>  'fa fa-facebook',
            'name'        =>  __('Facebook', 'users-wp'),
            'description' =>  __('Adds a input for Facebook field.', 'users-wp'),
            'defaults'    => array(
                'data_type'           =>  'VARCHAR',
                'admin_title'         =>  'Facebook',
                'site_title'          =>  'Facebook',
                'htmlvar_name'        =>  'facebook',
                'is_active'           =>  1,
                'default_value'       =>  '',
                'is_required'         =>  0,
                'required_msg'        =>  '',
                'field_icon'          =>  'fa fa-facebook',
                'css_class'           =>  ''
            )
        );

        // Twitter
        $custom_fields['twitter'] = array( // The key value should be unique and not contain any spaces.
            'field_type'  =>  'text',
            'class'       =>  'uwp-twitter',
            'icon'        =>  'fa fa-twitter',
            'name'        =>  __('Twitter', 'users-wp'),
            'description' =>  __('Adds a input for Twitter field.', 'users-wp'),
            'defaults'    => array(
                'data_type'           =>  'VARCHAR',
                'admin_title'         =>  'Twitter',
                'site_title'          =>  'Twitter',
                'htmlvar_name'        =>  'twitter',
                'is_active'           =>  1,
                'default_value'       =>  '',
                'is_required'         =>  0,
                'required_msg'        =>  '',
                'field_icon'          =>  'fa fa-twitter',
                'css_class'           =>  ''
            )
        );

        // Youtube
        $custom_fields['youtube'] = array( // The key value should be unique and not contain any spaces.
            'field_type'  =>  'text',
            'class'       =>  'uwp-youtube',
            'icon'        =>  'fa fa-youtube',
            'name'        =>  __('Youtube', 'users-wp'),
            'description' =>  __('Adds a input for Youtube field.', 'users-wp'),
            'defaults'    => array(
                'data_type'           =>  'VARCHAR',
                'admin_title'         =>  'Youtube',
                'site_title'          =>  'Youtube',
                'htmlvar_name'        =>  'youtube',
                'is_active'           =>  1,
                'default_value'       =>  '',
                'is_required'         =>  0,
                'required_msg'        =>  '',
                'field_icon'          =>  'fa fa-youtube',
                'css_class'           =>  ''
            )
        );

        // Instagram
        $custom_fields['instagram'] = array( // The key value should be unique and not contain any spaces.
            'field_type'  =>  'text',
            'class'       =>  'uwp-instagram',
            'icon'        =>  'fa fa-instagram',
            'name'        =>  __('Instagram', 'users-wp'),
            'description' =>  __('Adds a input for Instagram field.', 'users-wp'),
            'defaults'    => array(
                'data_type'           =>  'VARCHAR',
                'admin_title'         =>  'Instagram',
                'site_title'          =>  'Instagram',
                'htmlvar_name'        =>  'instagram',
                'is_active'           =>  1,
                'default_value'       =>  '',
                'is_required'         =>  0,
                'required_msg'        =>  '',
                'field_icon'          =>  'fa fa-instagram',
                'css_class'           =>  ''
            )
        );

        // Soundcloud
        $custom_fields['soundcloud'] = array( // The key value should be unique and not contain any spaces.
            'field_type'  =>  'text',
            'class'       =>  'uwp-soundcloud',
            'icon'        =>  'fa fa-soundcloud',
            'name'        =>  __('Soundcloud', 'users-wp'),
            'description' =>  __('Adds a input for Soundcloud field.', 'users-wp'),
            'defaults'    => array(
                'data_type'           =>  'VARCHAR',
                'admin_title'         =>  'Soundcloud',
                'site_title'          =>  'Soundcloud',
                'htmlvar_name'        =>  'soundcloud',
                'is_active'           =>  1,
                'default_value'       =>  '',
                'is_required'         =>  0,
                'required_msg'        =>  '',
                'field_icon'          =>  'fa fa-soundcloud',
                'css_class'           =>  ''
            )
        );

        // Reddit
        $custom_fields['reddit'] = array( // The key value should be unique and not contain any spaces.
            'field_type'  =>  'text',
            'class'       =>  'uwp-reddit',
            'icon'        =>  'fa fa-reddit',
            'name'        =>  __('Reddit', 'users-wp'),
            'description' =>  __('Adds a input for Reddit field.', 'users-wp'),
            'defaults'    => array(
                'data_type'           =>  'VARCHAR',
                'admin_title'         =>  'Reddit',
                'site_title'          =>  'Reddit',
                'htmlvar_name'        =>  'reddit',
                'is_active'           =>  1,
                'default_value'       =>  '',
                'is_required'         =>  0,
                'required_msg'        =>  '',
                'field_icon'          =>  'fa fa-reddit',
                'css_class'           =>  ''
            )
        );

        // Skype
        $custom_fields['skype'] = array( // The key value should be unique and not contain any spaces.
            'field_type'  =>  'text',
            'class'       =>  'uwp-skype',
            'icon'        =>  'fa fa-skype',
            'name'        =>  __('Skype', 'users-wp'),
            'description' =>  __('Adds a input for Skype field.', 'users-wp'),
            'defaults'    => array(
                'data_type'           =>  'VARCHAR',
                'admin_title'         =>  'Skype',
                'site_title'          =>  'Skype',
                'htmlvar_name'        =>  'skype',
                'is_active'           =>  1,
                'default_value'       =>  '',
                'is_required'         =>  0,
                'required_msg'        =>  '',
                'field_icon'          =>  'fa fa-skype',
                'css_class'           =>  ''
            )
        );

        return apply_filters('uwp_custom_fields_predefined',$custom_fields,$type);
    }

    public function uwp_custom_fields_custom($type='') {
        $custom_fields = array();
        return apply_filters('uwp_custom_fields_custom',$custom_fields,$type);
    }

    public function uwp_custom_fields($type='') {

        $custom_fields = array(
            'text' => array(
                'field_type'  =>  'text',
                'class' =>  'uwp-text',
                'icon'  =>  'fa fa-minus',
                'name'  =>  __('Text', 'users-wp'),
                'description' =>  __('Add any sort of text field, text or numbers', 'users-wp')
            ),
            'datepicker' => array(
                'field_type'  =>  'datepicker',
                'class' =>  'uwp-datepicker',
                'icon'  =>  'fa fa-calendar',
                'name'  =>  __('Date', 'users-wp'),
                'description' =>  __('Adds a date picker.', 'users-wp')
            ),
            'textarea' => array(
                'field_type'  =>  'textarea',
                'class' =>  'uwp-textarea',
                'icon'  =>  'fa fa-bars',
                'name'  =>  __('Textarea', 'users-wp'),
                'description' =>  __('Adds a textarea', 'users-wp')
            ),
            'time' => array(
                'field_type'  =>  'time',
                'class' =>  'uwp-time',
                'icon' =>  'fa fa-clock-o',
                'name'  =>  __('Time', 'users-wp'),
                'description' =>  __('Adds a time picker', 'users-wp')
            ),
            'checkbox' => array(
                'field_type'  =>  'checkbox',
                'class' =>  'uwp-checkbox',
                'icon' =>  'fa fa-check-square-o',
                'name'  =>  __('Checkbox', 'users-wp'),
                'description' =>  __('Adds a checkbox', 'users-wp')
            ),
            'phone' => array(
                'field_type'  =>  'phone',
                'class' =>  'uwp-phone',
                'icon' =>  'fa fa-phone',
                'name'  =>  __('Phone', 'users-wp'),
                'description' =>  __('Adds a phone input', 'users-wp')
            ),
            'radio' => array(
                'field_type'  =>  'radio',
                'class' =>  'uwp-radio',
                'icon' =>  'fa fa-dot-circle-o',
                'name'  =>  __('Radio', 'users-wp'),
                'description' =>  __('Adds a radio input', 'users-wp')
            ),
            'email' => array(
                'field_type'  =>  'email',
                'class' =>  'uwp-email',
                'icon' =>  'fa fa-envelope-o',
                'name'  =>  __('Email', 'users-wp'),
                'description' =>  __('Adds a email input', 'users-wp')
            ),
            'select' => array(
                'field_type'  =>  'select',
                'class' =>  'uwp-select',
                'icon' =>  'fa fa-caret-square-o-down',
                'name'  =>  __('Select', 'users-wp'),
                'description' =>  __('Adds a select input', 'users-wp')
            ),
            'multiselect' => array(
                'field_type'  =>  'multiselect',
                'class' =>  'uwp-multiselect',
                'icon' =>  'fa fa-caret-square-o-down',
                'name'  =>  __('Multi Select', 'users-wp'),
                'description' =>  __('Adds a multiselect input', 'users-wp')
            ),
            'url' => array(
                'field_type'  =>  'url',
                'class' =>  'uwp-url',
                'icon' =>  'fa fa-link',
                'name'  =>  __('URL', 'users-wp'),
                'description' =>  __('Adds a url input', 'users-wp')
            ),
            'html' => array(
                'field_type'  =>  'html',
                'class' =>  'uwp-html',
                'icon' =>  'fa fa-code',
                'name'  =>  __('HTML', 'users-wp'),
                'description' =>  __('Adds a html input textarea', 'users-wp')
            ),
            'file' => array(
                'field_type'  =>  'file',
                'class' =>  'uwp-file',
                'icon' =>  'fa fa-file',
                'name'  =>  __('File Upload', 'users-wp'),
                'description' =>  __('Adds a file input', 'users-wp')
            )
        );

        return apply_filters('uwp_custom_fields', $custom_fields, $type);
    }


    public function uwp_manage_available_fields_predefined(){
        $this->uwp_custom_available_fields('predefined');
    }

    public function uwp_manage_available_fields_custom(){
        $this->uwp_custom_available_fields('custom');
    }

    public function uwp_manage_available_fields() {
        $this->uwp_custom_available_fields();
    }

    public function uwp_manage_selected_fields()
    {
        $this->uwp_custom_selected_fields();
    }

    function uwp_custom_selected_fields()
    {

        global $wpdb;
        $table_name = $wpdb->prefix . 'uwp_custom_fields';
        $form_type = (isset($_REQUEST['subtab']) && $_REQUEST['subtab'] != '') ? sanitize_text_field($_REQUEST['subtab']) : 'register';
        ?>
        <ul class="core">
            <?php
            $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE form_type = %s ORDER BY sort_order ASC", array($form_type)));

            if (!empty($fields)) {
                foreach ($fields as $field) {
                    //$result_str = $field->id;
                    $result_str = $field;
                    $field_type = $field->field_type;
                    $field_type_key = $field->field_type_key;
                    $field_ins_upd = 'display';

                    $this->uwp_custom_field_adminhtml($field_type, $result_str, $field_ins_upd,$field_type_key);
                }
            }
            ?></ul>
        <?php

    }

    public function uwp_custom_field_html($field_info, $field_type, $field_type_key, $field_ins_upd, $result_str) {
        global $form_type;

        if (!isset($field_info->form_type)) {
            $form_type = sanitize_text_field($_REQUEST['subtab']);
        } else
            $form_type = $field_info->form_type;

        $cf_arr1 = $this->uwp_custom_fields($form_type);
        $cf_arr2 = $this->uwp_custom_fields_predefined($form_type);
        $cf_arr3 = $this->uwp_custom_fields_custom($form_type);

        $cf_arr = $cf_arr1 + $cf_arr2 + $cf_arr3; // this way defaults can't be overwritten

        $cf = (isset($cf_arr[$field_type_key])) ? $cf_arr[$field_type_key] : '';

        $field_info = stripslashes_deep($field_info); // strip slashes from labels

        $field_site_title = '';
        if (isset($field_info->site_title))
            $field_site_title = $field_info->site_title;

        $default = isset($field_info->is_admin) ? $field_info->is_admin : '';

        $display_on_listing = true;
        // Remove Send Enquiry | Send To Friend from listings page
        $htmlvar_name = isset($field_info->htmlvar_name) && $field_info->htmlvar_name != '' ? $field_info->htmlvar_name : '';
        if ($htmlvar_name == 'uwp_email') {
            $field_info->show_on_listing = 0;
            $display_on_listing = false;
        }

        $field_display = $field_type == 'address' && $field_info->htmlvar_name == 'post' ? 'style="display:none"' : '';

        $radio_id = (isset($field_info->htmlvar_name)) ? $field_info->htmlvar_name : rand(5, 500);

        //print_r($field_info);

        if (isset($cf['icon']) && strpos($cf['icon'], 'fa fa-') !== false) {
            $field_icon = '<i class="'.$cf['icon'].'" aria-hidden="true"></i>';
        }elseif(isset($cf['icon']) && $cf['icon']){
            $field_icon = '<b style="background-image: url("'.$cf['icon'].'")"></b>';
        }else{
            $field_icon = '<i class="fa fa-cog" aria-hidden="true"></i>';
        }

        if(isset($cf['name']) && $cf['name']){
            $field_type_name = $cf['name'];
        }else{
            $field_type_name = $field_type;
        }

        ?>
        <li class="text" id="licontainer_<?php echo $result_str; ?>">
            <div class="title title<?php echo $result_str; ?> uwp-fieldset"
                 title="<?php _e('Double Click to toggle and drag-drop to sort', 'users-wp'); ?>"
                 ondblclick="show_hide('field_frm<?php echo $result_str; ?>')">
                <?php

                $nonce = wp_create_nonce('custom_fields_' . $result_str);
                ?>

                <?php if ($default): ?>
                    <div title="<?php _e('Default field, should not be removed.', 'users-wp'); ?>" class="handlediv move gd-default-remove"><i class="fa fa-times" aria-hidden="true"></i></div>
                <?php else: ?>
                    <div title="<?php _e('Click to remove field', 'users-wp'); ?>"
                         onclick="delete_field('<?php echo $result_str; ?>', '<?php echo $nonce; ?>')"
                         class="handlediv close"><i class="fa fa-times" aria-hidden="true"></i></div>
                <?php endif;
                if ($field_type == 'fieldset') {
                    ?>
                    <i class="fa fa-long-arrow-left " aria-hidden="true"></i>
                    <i class="fa fa-long-arrow-right " aria-hidden="true"></i>
                    <b style="cursor:pointer;"
                       onclick="show_hide('field_frm<?php echo $result_str;?>')"><?php echo $this->uwp_ucwords(__('Fieldset:', 'users-wp') . ' ' . $field_site_title);?></b>
                    <?php
                } else {echo $field_icon;
                    ?>
                    <b style="cursor:pointer;"
                       onclick="show_hide('field_frm<?php echo $result_str;?>')"><?php echo $this->uwp_ucwords(' ' . $field_site_title . ' (' . $field_type_name . ')');?></b>
                    <?php
                }
                ?>
            </div>

            <form><!-- we need to wrap in a fom so we can use radio buttons with same name -->
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
                    <input type="hidden" name="data_type" id="data_type" value="<?php if (isset($field_info->data_type)) {
                        echo $field_info->data_type;
                    } ?>"/>
                    <input type="hidden" name="is_active" id="is_active" value="1"/>

                    <input type="hidden" name="is_default" value="<?php echo isset($field_info->is_default) ? $field_info->is_default : '';?>" /><?php // show in sidebar value?>
                    <input type="hidden" name="show_on_listing" value="<?php echo isset($field_info->show_on_listing) ? $field_info->show_on_listing : '';?>" />
                    <input type="hidden" name="show_on_detail" value="<?php echo isset($field_info->show_on_listing) ? $field_info->show_on_listing : '';?>" />
                    <input type="hidden" name="show_as_tab" value="<?php echo isset($field_info->show_as_tab) ? $field_info->show_as_tab : '';?>" />

                    <ul class="widefat post fixed" style="width:100%;">

                        <?php

                        // data_type
                        if(has_filter("uwp_cfa_data_type_{$field_type}")){

                            echo apply_filters("uwp_cfa_data_type_{$field_type}",'',$result_str,$cf,$field_info);

                        }else{
                            $value = '';
                            if (isset($field_info->data_type)) {
                                $value = esc_attr($field_info->data_type);
                            }elseif(isset($cf['defaults']['data_type']) && $cf['defaults']['data_type']){
                                $value = $cf['defaults']['data_type'];
                            }
                            ?>
                            <input type="hidden" name="data_type" id="data_type" value="<?php echo $value;?>"/>
                            <?php
                        }


                        // site_title
                        if(has_filter("uwp_cfa_site_title_{$field_type}")){

                            echo apply_filters("uwp_cfa_site_title_{$field_type}",'',$result_str,$cf,$field_info);

                        }else{
                            $value = '';
                            if (isset($field_info->site_title)) {
                                $value = esc_attr($field_info->site_title);
                            }elseif(isset($cf['defaults']['site_title']) && $cf['defaults']['site_title']){
                                $value = $cf['defaults']['site_title'];
                            }
                            ?>
                            <li>
                                <label for="site_title" class="gd-cf-tooltip-wrap"> <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Frontend title :', 'users-wp'); ?>
                                    <div class="gdcf-tooltip">
                                        <?php _e('This will be the title for the field on the frontend.', 'users-wp'); ?>
                                    </div>
                                </label>
                                <div class="gd-cf-input-wrap">
                                    <input type="text" name="site_title" id="site_title"
                                           value="<?php echo $value; ?>"/>
                                </div>
                            </li>
                            <?php
                        }


                        // htmlvar_name
                        if(has_filter("uwp_cfa_htmlvar_name_{$field_type}")){

                            echo apply_filters("uwp_cfa_htmlvar_name_{$field_type}",'',$result_str,$cf,$field_info);

                        }else{
                            $value = '';
                            if (isset($field_info->htmlvar_name)) {
                                $value = esc_attr($field_info->htmlvar_name);
                            }elseif(isset($cf['defaults']['htmlvar_name']) && $cf['defaults']['htmlvar_name']){
                                $value = $cf['defaults']['htmlvar_name'];
                            }
                            ?>
                            <li>
                                <label for="htmlvar_name" class="gd-cf-tooltip-wrap"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('HTML variable name :', 'users-wp');?>
                                    <div class="gdcf-tooltip">
                                        <?php _e('This is a unique identifier used in the HTML, it MUST NOT contain spaces or special characters.', 'users-wp'); ?>
                                    </div>
                                </label>
                                <div class="gd-cf-input-wrap">
                                    <input type="text" name="htmlvar_name" id="htmlvar_name" pattern="[a-zA-Z0-9]+" title="<?php _e('Must not contain spaces or special characters', 'users-wp');?>"
                                           value="<?php if ($value) {
                                               echo preg_replace('/uwp_/', '', $value, 1);
                                           }?>" <?php if ($default) {
                                        echo 'readonly="readonly"';
                                    }?> />
                                </div>
                            </li>
                            <?php
                        }


                        // is_active
                        if(has_filter("uwp_cfa_is_active_{$field_type}")){

                            echo apply_filters("uwp_cfa_is_active_{$field_type}",'',$result_str,$cf,$field_info);

                        }else{
                            $value = '';
                            if (isset($field_info->is_active)) {
                                $value = esc_attr($field_info->is_active);
                            }elseif(isset($cf['defaults']['is_active']) && $cf['defaults']['is_active']){
                                $value = $cf['defaults']['is_active'];
                            }
                            ?>
                            <li <?php echo $field_display; ?>>
                                <label for="is_active" class="gd-cf-tooltip-wrap"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Is active :', 'users-wp'); ?>
                                    <div class="gdcf-tooltip">
                                        <?php _e('If no is selected then the field will not be displayed anywhere.', 'users-wp'); ?>
                                    </div>
                                </label>
                                <div class="gd-cf-input-wrap gd-switch">

                                    <input type="radio" id="is_active_yes<?php echo $radio_id;?>" name="is_active" class="gdri-enabled"  value="1"
                                        <?php if ($value == '1') {
                                            echo 'checked';
                                        } ?>/>
                                    <label for="is_active_yes<?php echo $radio_id;?>" class="gdcb-enable"><span><?php _e('Yes', 'users-wp'); ?></span></label>

                                    <input type="radio" id="is_active_no<?php echo $radio_id;?>" name="is_active" class="gdri-disabled" value="0"
                                        <?php if ($value == '0' || !$value) {
                                            echo 'checked';
                                        } ?>/>
                                    <label for="is_active_no<?php echo $radio_id;?>" class="gdcb-disable"><span><?php _e('No', 'users-wp'); ?></span></label>

                                </div>
                            </li>
                            <?php
                        }


                        // for_admin_use
                        if(has_filter("uwp_cfa_for_admin_use_{$field_type}")){

                            echo apply_filters("uwp_cfa_for_admin_use_{$field_type}",'',$result_str,$cf,$field_info);

                        }else{
                            $value = '';
                            if (isset($field_info->for_admin_use)) {
                                $value = esc_attr($field_info->for_admin_use);
                            }elseif(isset($cf['defaults']['for_admin_use']) && $cf['defaults']['for_admin_use']){
                                $value = $cf['defaults']['for_admin_use'];
                            }
                            ?>
                            <li>
                                <label for="for_admin_use" class="gd-cf-tooltip-wrap"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('For admin use only? :', 'users-wp'); ?>
                                    <div class="gdcf-tooltip">
                                        <?php _e('If yes is selected then only site admin can see and edit this field.', 'users-wp'); ?>
                                    </div>
                                </label>
                                <div class="gd-cf-input-wrap gd-switch">

                                    <input type="radio" id="for_admin_use_yes<?php echo $radio_id;?>" name="for_admin_use" class="gdri-enabled"  value="1"
                                        <?php if ($value == '1') {
                                            echo 'checked';
                                        } ?>/>
                                    <label for="for_admin_use_yes<?php echo $radio_id;?>" class="gdcb-enable"><span><?php _e('Yes', 'users-wp'); ?></span></label>

                                    <input type="radio" id="for_admin_use_no<?php echo $radio_id;?>" name="for_admin_use" class="gdri-disabled" value="0"
                                        <?php if ($value == '0' || !$value) {
                                            echo 'checked';
                                        } ?>/>
                                    <label for="for_admin_use_no<?php echo $radio_id;?>" class="gdcb-disable"><span><?php _e('No', 'users-wp'); ?></span></label>

                                </div>
                            </li>
                            <?php
                        }


                        // default_value
                        if(has_filter("uwp_cfa_default_value_{$field_type}")){

                            echo apply_filters("uwp_cfa_default_value_{$field_type}",'',$result_str,$cf,$field_info);

                        }else{
                            $value = '';
                            if (isset($field_info->default_value)) {
                                $value = esc_attr($field_info->default_value);
                            }elseif(isset($cf['defaults']['default_value']) && $cf['defaults']['default_value']){
                                $value = $cf['defaults']['default_value'];
                            }
                            ?>
                            <li>
                                <label for="default_value" class="gd-cf-tooltip-wrap"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Default value :', 'users-wp');?>
                                    <div class="gdcf-tooltip">
                                        <?php
                                        if ($field_type == 'checkbox') {
                                            _e('Should the checkbox be checked by default?', 'users-wp');
                                        } else if ($field_type == 'email') {
                                            _e('A default value for the field, usually blank. Ex: info@mysite.com', 'users-wp');
                                        } else {
                                            _e('A default value for the field, usually blank. (for "link" this will be used as the link text)', 'users-wp');
                                        }
                                        ?>
                                    </div>
                                </label>
                                <div class="gd-cf-input-wrap">
                                    <?php if ($field_type == 'checkbox') { ?>
                                        <select name="default_value" id="default_value">
                                            <option value=""><?php _e('Unchecked', 'users-wp'); ?></option>
                                            <option value="1" <?php selected(true, (int)$value === 1);?>><?php _e('Checked', 'users-wp'); ?></option>
                                        </select>
                                    <?php } else if ($field_type == 'email') { ?>
                                        <input type="email" name="default_value" placeholder="<?php _e('info@mysite.com', 'users-wp') ;?>" id="default_value" value="<?php echo esc_attr($value);?>" /><br/>
                                    <?php } else { ?>
                                        <input type="text" name="default_value" id="default_value" value="<?php echo esc_attr($value);?>" /><br/>
                                    <?php } ?>
                                </div>
                            </li>
                            <?php
                        }


                        // show_in
                        if(has_filter("uwp_cfa_show_in_{$field_type}")){

                            echo apply_filters("uwp_cfa_show_in_{$field_type}",'',$result_str,$cf,$field_info);

                        }else{
                            $value = '';
                            if (isset($field_info->show_in)) {
                                $value = esc_attr($field_info->show_in);
                            }elseif(isset($cf['defaults']['show_in']) && $cf['defaults']['show_in']){
                                $value = esc_attr($cf['defaults']['show_in']);
                            }
                            ?>
                            <li>
                                <label for="show_in" class="gd-cf-tooltip-wrap"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Show in what locations?:', 'users-wp'); ?>
                                    <div class="gdcf-tooltip">
                                        <?php _e('Select in what locations you want to display this field.', 'users-wp'); ?>
                                    </div>
                                </label>
                                <div class="gd-cf-input-wrap">

                                    <?php

                                    /*
                                     * We wrap the key values in [] so we can search the DB easier with a LIKE query.
                                     */
                                    $show_in_locations = array(
                                        "[detail]" => __("Details page sidebar", 'users-wp'),
                                        "[moreinfo]" => __("More info tab", 'users-wp'),
                                        "[listing]" => __("Listings page", 'users-wp'),
                                        "[owntab]" => __("Details page own tab", 'users-wp'),
                                        "[mapbubble]" => __("Map bubble", 'users-wp'),
                                    );

                                    $show_in_locations = apply_filters('uwp_show_in_locations',$show_in_locations,$field_info,$field_type);


                                    // remove some locations for some field types

                                    // don't show new tab option for some types

                                    if (in_array($field_type, array('text', 'datepicker', 'textarea', 'time', 'phone', 'email', 'select', 'multiselect', 'url', 'html', 'fieldset', 'radio', 'checkbox', 'file'))) {
                                    }else{
                                        unset($show_in_locations['[owntab]']);
                                    }

                                    if(!$display_on_listing){
                                        unset($show_in_locations['[listings]']);
                                    }

                                    ?>

                                    <select multiple="multiple" name="show_in[]"
                                            id="show_in"
                                            style="min-width:300px;"
                                            class="chosen_select"
                                            data-placeholder="<?php _e('Select locations', 'users-wp'); ?>"
                                            option-ajaxchosen="false">
                                        <?php

                                        $show_in_values = explode(',',$value);

                                        foreach( $show_in_locations as $key => $val){
                                            $selected = '';

                                            if(is_array($show_in_values) && in_array($key,$show_in_values ) ){
                                                $selected = 'selected';
                                            }

                                            ?>
                                            <option  value="<?php echo $key;?>" <?php echo $selected;?>><?php echo $val;?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </li>
                            <?php
                        }


                        // advanced_editor
                        if(has_filter("uwp_cfa_advanced_editor_{$field_type}")){

                            echo apply_filters("uwp_cfa_advanced_editor_{$field_type}",'',$result_str,$cf,$field_info);

                        }




                        ?>

                        <?php // we dont need to show the sort order ?>
                        <input type="hidden" readonly="readonly" name="sort_order" id="sort_order" value="<?php if (isset($field_info->sort_order)) { echo esc_attr($field_info->sort_order);} ?>"/>



                        <?php

                        // is_required
                        if(has_filter("uwp_cfa_is_required_{$field_type}")){

                            echo apply_filters("uwp_cfa_is_required_{$field_type}",'',$result_str,$cf,$field_info);

                        }else{
                            $value = '';
                            if (isset($field_info->is_required)) {
                                $value = esc_attr($field_info->is_required);
                            }elseif(isset($cf['defaults']['is_required']) && $cf['defaults']['is_required']){
                                $value = $cf['defaults']['is_required'];
                            }
                            ?>
                            <li>
                                <label for="is_required" class="gd-cf-tooltip-wrap"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Is required :', 'users-wp'); ?>
                                    <div class="gdcf-tooltip">
                                        <?php _e('Select yes to set field as required', 'users-wp'); ?>
                                    </div>
                                </label>

                                <div class="gd-cf-input-wrap gd-switch">

                                    <input type="radio" id="is_required_yes<?php echo $radio_id;?>" name="is_required" class="gdri-enabled"  value="1"
                                        <?php if ($value == '1') {
                                            echo 'checked';
                                        } ?>/>
                                    <label onclick="show_hide_radio(this,'show','cf-is-required-msg');" for="is_required_yes<?php echo $radio_id;?>" class="gdcb-enable"><span><?php _e('Yes', 'users-wp'); ?></span></label>

                                    <input type="radio" id="is_required_no<?php echo $radio_id;?>" name="is_required" class="gdri-disabled" value="0"
                                        <?php if ($value == '0' || !$value) {
                                            echo 'checked';
                                        } ?>/>
                                    <label onclick="show_hide_radio(this,'hide','cf-is-required-msg');" for="is_required_no<?php echo $radio_id;?>" class="gdcb-disable"><span><?php _e('No', 'users-wp'); ?></span></label>

                                </div>

                            </li>

                            <?php
                        }

                        // required_msg
                        if(has_filter("uwp_cfa_required_msg_{$field_type}")){

                            echo apply_filters("uwp_cfa_required_msg_{$field_type}",'',$result_str,$cf,$field_info);

                        }else{
                            $value = '';
                            if (isset($field_info->required_msg)) {
                                $value = esc_attr($field_info->required_msg);
                            }elseif(isset($cf['defaults']['required_msg']) && $cf['defaults']['required_msg']){
                                $value = $cf['defaults']['required_msg'];
                            }
                            ?>
                            <li class="cf-is-required-msg" <?php if ((isset($field_info->is_required) && $field_info->is_required == '0') || !isset($field_info->is_required)) {echo "style='display:none;'";}?>>
                                <label for="required_msg" class="gd-cf-tooltip-wrap">
                                    <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Required message:', 'users-wp'); ?>
                                    <div class="gdcf-tooltip">
                                        <?php _e('Enter text for the error message if the field is required and has not fulfilled the requirements.', 'users-wp'); ?>
                                    </div>
                                </label>
                                <div class="gd-cf-input-wrap">
                                    <input type="text" name="required_msg" id="required_msg"
                                           value="<?php echo esc_attr($value); ?>"/>
                                </div>
                            </li>
                            <?php
                        }


                        // required_msg
                        if(has_filter("uwp_cfa_validation_pattern_{$field_type}")){

                            echo apply_filters("uwp_cfa_validation_pattern_{$field_type}",'',$result_str,$cf,$field_info);

                        }


                        // extra_fields
                        if(has_filter("uwp_cfa_extra_fields_{$field_type}")){

                            echo apply_filters("uwp_cfa_extra_fields_{$field_type}",'',$result_str,$cf,$field_info);

                        }


                        // field_icon
                        if(has_filter("uwp_cfa_field_icon_{$field_type}")){

                            echo apply_filters("uwp_cfa_field_icon_{$field_type}",'',$result_str,$cf,$field_info);

                        }else{
                            $value = '';
                            if (isset($field_info->field_icon)) {
                                $value = esc_attr($field_info->field_icon);
                            }elseif(isset($cf['defaults']['field_icon']) && $cf['defaults']['field_icon']){
                                $value = $cf['defaults']['field_icon'];
                            }
                            ?>
                            <li>
                                <h3><?php echo __('Custom css', 'users-wp'); ?></h3>


                                <label for="field_icon" class="gd-cf-tooltip-wrap">
                                    <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Upload icon :', 'users-wp'); ?>
                                    <div class="gdcf-tooltip">
                                        <?php _e('Upload icon using media and enter its url path, or enter <a href="http://fortawesome.github.io/Font-Awesome/icons/" target="_blank" >font awesome </a>class eg:"fa fa-home"', 'users-wp');?>
                                    </div>
                                </label>
                                <div class="gd-cf-input-wrap">
                                    <input type="text" name="field_icon" id="field_icon"
                                           value="<?php echo $value;?>"/>
                                </div>

                            </li>
                            <?php
                        }


                        // css_class
                        if(has_filter("uwp_cfa_css_class_{$field_type}")){

                            echo apply_filters("uwp_cfa_css_class_{$field_type}",'',$result_str,$cf,$field_info);

                        }else{
                            $value = '';
                            if (isset($field_info->css_class)) {
                                $value = esc_attr($field_info->css_class);
                            }elseif(isset($cf['defaults']['css_class']) && $cf['defaults']['css_class']){
                                $value = $cf['defaults']['css_class'];
                            }
                            ?>
                            <li>

                                <label for="css_class" class="gd-cf-tooltip-wrap">
                                    <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Css class :', 'users-wp'); ?>
                                    <div class="gdcf-tooltip">
                                        <?php _e('Enter custom css class for field custom style.', 'users-wp');?>
                                        <?php if($field_type=='multiselect'){_e('(Enter class `gd-comma-list` to show list as comma separated)', 'users-wp');}?>
                                    </div>
                                </label>
                                <div class="gd-cf-input-wrap">
                                    <input type="text" name="css_class" id="css_class"
                                           value="<?php if (isset($field_info->css_class)) {
                                               echo esc_attr($field_info->css_class);
                                           }?>"/>
                                </div>
                            </li>
                            <?php
                        }


                        // cat_sort
                        if(has_filter("uwp_cfa_cat_sort_{$field_type}")){

                            echo apply_filters("uwp_cfa_cat_sort_{$field_type}",'',$result_str,$cf,$field_info);

                        }else{
                            $value = '';
                            $hide_cat_sort  ='';
                            if (isset($field_info->cat_sort)) {
                                $value = esc_attr($field_info->cat_sort);
                            }elseif(isset($cf['defaults']['cat_sort']) && $cf['defaults']['cat_sort']){
                                $value = $cf['defaults']['cat_sort'];
                                $hide_cat_sort = ($value===false) ? "style='display:none;'" : '';
                            }

                            $hide_cat_sort = (isset($cf['defaults']['cat_sort']) && $cf['defaults']['cat_sort']===false) ? "style='display:none;'" : '';
                            ?>
                            <li <?php echo $hide_cat_sort ;?>>
                                <h3><?php
                                    echo apply_filters('uwp_advance_custom_fields_heading', __('Posts sort options', 'users-wp'), $field_type);

                                    ?></h3>
                                <label for="cat_sort" class="gd-cf-tooltip-wrap">
                                    <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Include this field in sorting options :', 'users-wp'); ?>
                                    <div class="gdcf-tooltip">
                                        <?php _e('Lets you use this filed as a sorting option, set from sorting options above.', 'users-wp');?>
                                    </div>
                                </label>

                                <div class="gd-cf-input-wrap gd-switch">

                                    <input type="radio" id="cat_sort_yes<?php echo $radio_id;?>" name="cat_sort" class="gdri-enabled"  value="1"
                                        <?php if ($value == '1') {
                                            echo 'checked';
                                        } ?>/>
                                    <label for="cat_sort_yes<?php echo $radio_id;?>" class="gdcb-enable"><span><?php _e('Yes', 'users-wp'); ?></span></label>

                                    <input type="radio" id="cat_sort_no<?php echo $radio_id;?>" name="cat_sort" class="gdri-disabled" value="0"
                                        <?php if (!$value) {
                                            echo 'checked';
                                        } ?>/>
                                    <label for="cat_sort_no<?php echo $radio_id;?>" class="gdcb-disable"><span><?php _e('No', 'users-wp'); ?></span></label>

                                </div>
                            </li>
                            <?php
                        }



                        switch ($field_type):
                            case 'html':
                            case 'file':
                            case 'url':
                            case 'fieldset':
                                break;
                            default:
                                do_action('uwp_advance_custom_fields', $field_info,$cf);?>


                            <?php endswitch; ?>


                        <li>

                            <label for="save" class="gd-cf-tooltip-wrap">
                                <h3></h3>
                            </label>
                            <div class="gd-cf-input-wrap">
                                <input type="button" class="button button-primary" name="save" id="save" value="<?php echo esc_attr(__('Save','users-wp'));?>"
                                       onclick="save_field('<?php echo esc_attr($result_str); ?>')"/>
                                <?php if (!$default): ?>
                                    <a href="javascript:void(0)"><input type="button" name="delete" value="<?php echo esc_attr(__('Delete','users-wp'));?>"
                                                                        onclick="delete_field('<?php echo esc_attr($result_str); ?>', '<?php echo $nonce; ?>')"
                                                                        class="button"/></a>
                                <?php endif; ?>
                            </div>
                        </li>
                    </ul>
                </div>
            </form>
        </li>
    <?php
    }

    public function uwp_custom_field_adminhtml($field_type, $result_str, $field_ins_upd = '', $field_type_key ='')
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'uwp_custom_fields';
        $cf = $result_str;
        if (!is_object($cf)) {

            $field_info = $wpdb->get_row($wpdb->prepare("select * from " . $table_name . " where id= %d", array($cf)));

        } else {
            $field_info = $cf;
            $result_str = $cf->id;
        }

        $this->uwp_custom_field_html($field_info, $field_type, $field_type_key, $field_ins_upd, $result_str);

    }

    public function uwp_ucwords($string, $charset='UTF-8') {
        if (function_exists('mb_convert_case')) {
            return mb_convert_case($string, MB_CASE_TITLE, $charset);
        } else {
            return ucwords($string);
        }
    }
}