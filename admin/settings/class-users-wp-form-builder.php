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
        $form_type = (isset($_REQUEST['subtab']) && $_REQUEST['subtab'] != '') ? sanitize_text_field($_REQUEST['subtab']) : 'register';
        ?>
        <div class="uwp-panel-heading">
            <h3><?php echo apply_filters('uwp_form_builder_panel_head', '');?></h3>
        </div>

        <div id="uwp_form_builder_container" class="clearfix">
            <div class="uwp-form-builder-frame">
                <div class="uwp-side-sortables" id="uwp-available-fields">
                    <h3 class="hndle">
                    <span>
                        <?php echo apply_filters('uwp_form_builder_available_fields_head', __('Add new form field', 'uwp')); ?>
                    </span>
                    </h3>

                    <p>
                        <?php
                        $note = sprintf(__('Click on any box below to add a field of that type on '.$form_type.' form. You must be use a fieldset to group your fields.', 'uwp'));
                        echo apply_filters('uwp_form_builder_available_fields_note', $note);
                        ?>
                    </p>

                    <h3>
                        <?php _e('Setup New Field' ,'uwp'); ?>
                    </h3>

                    <div class="inside">
                        <div id="uwp-form-builder-tab" class="uwp-tabs-panel">
                            <?php do_action('uwp_manage_available_fields'); ?>
                        </div>
                    </div>

                    <h3>
                        <?php _e('Predefined Fields' ,'uwp'); ?>
                    </h3>

                    <div class="inside">
                        <div id="uwp-form-builder-tab-predefined" class="uwp-tabs-panel">
                            <?php do_action('uwp_manage_available_fields_predefined'); ?>
                        </div>
                    </div>

<!--                    <h3>-->
<!--                        --><?php //_e('Custom Fields' ,'uwp'); ?>
<!--                    </h3>-->
<!---->
<!--                    <div class="inside">-->
<!--                        <div id="uwp-form-builder-tab-custom" class="uwp-tabs-panel">-->
<!--                            --><?php //do_action('uwp_manage_available_fields_custom'); ?>
<!--                        </div>-->
<!--                    </div>-->

                </div>


                <div class="uwp-side-sortables" id="uwp-selected-fields">

                    <h3 class="hndle">
                        <span>
                            <?php
                            $title = __('List of fields those will appear on add new listing form', 'uwp');
                            echo apply_filters('uwp_form_builder_selected_fields_head', $title); ?>
                        </span>
                    </h3>

                    <p>
                        <?php
                        $note = __('Click to expand and view field related settings. You may drag and drop to arrange fields order on '.$form_type.' form too.', 'uwp');
                        echo apply_filters('uwp_form_builder_selected_fields_note', $note); ?>
                    </p>

                    <div class="inside">
                        <div id="uwp-form-builder-tab-selected" class="uwp-tabs-panel">
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
        <input type="hidden" name="manage_field_type" class="manage_field_type" value="custom_fields">
        <?php
        if($type=='predefined'){
            $fields = $this->uwp_custom_fields_predefined($form_type);
        }elseif($type=='custom'){
            $fields = $this->uwp_custom_fields_custom($form_type);
        }else{
            $fields = $this->uwp_custom_fields($form_type);
            ?>
            <ul class="full uwp-tooltip-wrap">
                <li>
                    <div class="uwp-tooltip">
                        <?php _e('This adds a section separator with a title.', 'uwp');?>
                    </div>
                    <a id="uwp-fieldset"
                       class="uwp-draggable-form-items uwp-fieldset"
                       href="javascript:void(0);"
                       data-field-custom-type=""
                       data-field-type="fieldset"
                       data-field-type-key="fieldset">

                        <i class="fa fa-long-arrow-left " aria-hidden="true"></i>
                        <i class="fa fa-long-arrow-right " aria-hidden="true"></i>
                        <?php _e('Fieldset (section separator)', 'uwp');?>
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
            _e('There are no custom fields here yet.', 'uwp');
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
            'field_type'  =>  'select',
            'class'       =>  'uwp-country',
            'icon'        =>  'fa fa-map-marker',
            'name'        =>  __('Country', 'uwp'),
            'description' =>  __('Adds a input for Country field.', 'uwp'),
            'defaults'    => array(
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
            'field_type'  =>  'select',
            'class'       =>  'uwp-gender',
            'icon'        =>  'fa fa-user',
            'name'        =>  __('Gender', 'uwp'),
            'description' =>  __('Adds a input for Gender field.', 'uwp'),
            'defaults'    => array(
                'admin_title'         =>  'Gender',
                'site_title'          =>  'Gender',
                'htmlvar_name'        =>  'gender',
                'is_active'           =>  1,
                'default_value'       =>  '',
                'is_required'         =>  0,
                'option_values'       =>  __('Select Gender/,Male,Female,Other' ,'uwp'),
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
            'name'        =>  __('Mobile', 'uwp'),
            'description' =>  __('Adds a input for Mobile field.', 'uwp'),
            'defaults'    => array(
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
            'name'        =>  __('Facebook', 'uwp'),
            'description' =>  __('Adds a input for Facebook field.', 'uwp'),
            'defaults'    => array(
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
            'name'        =>  __('Twitter', 'uwp'),
            'description' =>  __('Adds a input for Twitter field.', 'uwp'),
            'defaults'    => array(
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
            'name'        =>  __('Youtube', 'uwp'),
            'description' =>  __('Adds a input for Youtube field.', 'uwp'),
            'defaults'    => array(
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
            'name'        =>  __('Instagram', 'uwp'),
            'description' =>  __('Adds a input for Instagram field.', 'uwp'),
            'defaults'    => array(
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
            'name'        =>  __('Soundcloud', 'uwp'),
            'description' =>  __('Adds a input for Soundcloud field.', 'uwp'),
            'defaults'    => array(
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
            'name'        =>  __('Reddit', 'uwp'),
            'description' =>  __('Adds a input for Reddit field.', 'uwp'),
            'defaults'    => array(
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
            'name'        =>  __('Skype', 'uwp'),
            'description' =>  __('Adds a input for Skype field.', 'uwp'),
            'defaults'    => array(
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
                'name'  =>  __('Text', 'uwp'),
                'description' =>  __('Add any sort of text field, text or numbers', 'uwp')
            ),
            'datepicker' => array(
                'field_type'  =>  'datepicker',
                'class' =>  'uwp-datepicker',
                'icon'  =>  'fa fa-calendar',
                'name'  =>  __('Date', 'uwp'),
                'description' =>  __('Adds a date picker.', 'uwp')
            ),
            'textarea' => array(
                'field_type'  =>  'textarea',
                'class' =>  'uwp-textarea',
                'icon'  =>  'fa fa-bars',
                'name'  =>  __('Textarea', 'uwp'),
                'description' =>  __('Adds a textarea', 'uwp')
            ),
            'time' => array(
                'field_type'  =>  'time',
                'class' =>  'uwp-time',
                'icon' =>  'fa fa-clock-o',
                'name'  =>  __('Time', 'uwp'),
                'description' =>  __('Adds a time picker', 'uwp')
            ),
            'checkbox' => array(
                'field_type'  =>  'checkbox',
                'class' =>  'uwp-checkbox',
                'icon' =>  'fa fa-check-square-o',
                'name'  =>  __('Checkbox', 'uwp'),
                'description' =>  __('Adds a checkbox', 'uwp')
            ),
            'phone' => array(
                'field_type'  =>  'phone',
                'class' =>  'uwp-phone',
                'icon' =>  'fa fa-phone',
                'name'  =>  __('Phone', 'uwp'),
                'description' =>  __('Adds a phone input', 'uwp')
            ),
            'radio' => array(
                'field_type'  =>  'radio',
                'class' =>  'uwp-radio',
                'icon' =>  'fa fa-dot-circle-o',
                'name'  =>  __('Radio', 'uwp'),
                'description' =>  __('Adds a radio input', 'uwp')
            ),
            'email' => array(
                'field_type'  =>  'email',
                'class' =>  'uwp-email',
                'icon' =>  'fa fa-envelope-o',
                'name'  =>  __('Email', 'uwp'),
                'description' =>  __('Adds a email input', 'uwp')
            ),
            'select' => array(
                'field_type'  =>  'select',
                'class' =>  'uwp-select',
                'icon' =>  'fa fa-caret-square-o-down',
                'name'  =>  __('Select', 'uwp'),
                'description' =>  __('Adds a select input', 'uwp')
            ),
            'multiselect' => array(
                'field_type'  =>  'multiselect',
                'class' =>  'uwp-multiselect',
                'icon' =>  'fa fa-caret-square-o-down',
                'name'  =>  __('Multi Select', 'uwp'),
                'description' =>  __('Adds a multiselect input', 'uwp')
            ),
            'url' => array(
                'field_type'  =>  'url',
                'class' =>  'uwp-url',
                'icon' =>  'fa fa-link',
                'name'  =>  __('URL', 'uwp'),
                'description' =>  __('Adds a url input', 'uwp')
            ),
            'file' => array(
                'field_type'  =>  'file',
                'class' =>  'gd-file',
                'icon' =>  'fa fa-file',
                'name'  =>  __('File Upload', 'uwp'),
                'description' =>  __('Adds a file input', 'uwp')
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
        <input type="hidden" name="form_type" id="form_type" value="<?php echo $form_type;?>"/>
        <input type="hidden" name="manage_field_type" class="manage_field_type" value="custom_fields">
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

    public function uwp_custom_field_html($field_info, $field_type, $field_type_key, $field_ins_upd, $result_str, $form_type = false) {

        if (!$form_type) {
            if (!isset($field_info->form_type)) {
                $form_type = sanitize_text_field($_REQUEST['subtab']);
            } else {
                $form_type = $field_info->form_type;
            }
        }

        $cf_arr1 = $this->uwp_custom_fields($form_type);
        $cf_arr2 = $this->uwp_custom_fields_predefined($form_type);
        $cf_arr3 = $this->uwp_custom_fields_custom($form_type);

        $cf_arr = $cf_arr1 + $cf_arr2 + $cf_arr3; // this way defaults can't be overwritten

        $cf = (isset($cf_arr[$field_type_key])) ? $cf_arr[$field_type_key] : '';

        $field_info = stripslashes_deep($field_info); // strip slashes from labels

        $field_site_title = '';
        if (isset($field_info->site_title))
            $field_site_title = $field_info->site_title;

        $default = isset($field_info->is_default) ? $field_info->is_default : '';

        $field_display = $field_type == 'address' && $field_info->htmlvar_name == 'post' ? 'style="display:none"' : '';

        $radio_id = (isset($field_info->htmlvar_name)) ? $field_info->htmlvar_name : rand(5, 500);


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
                 title="<?php _e('Double Click to toggle and drag-drop to sort', 'uwp'); ?>"
                 ondblclick="show_hide('field_frm<?php echo $result_str; ?>')">
                <?php

                $nonce = wp_create_nonce('custom_fields_' . $result_str);
                ?>

                <?php if ($default): ?>
                    <div title="<?php _e('Default field, should not be removed.', 'uwp'); ?>" class="handlediv move gd-default-remove"><i class="fa fa-times" aria-hidden="true"></i></div>
                <?php else: ?>
                    <div title="<?php _e('Click to remove field', 'uwp'); ?>"
                         onclick="delete_field('<?php echo $result_str; ?>', '<?php echo $nonce; ?>')"
                         class="handlediv close"><i class="fa fa-times" aria-hidden="true"></i></div>
                <?php endif;
                if ($field_type == 'fieldset') {
                    ?>
                    <i class="fa fa-long-arrow-left " aria-hidden="true"></i>
                    <i class="fa fa-long-arrow-right " aria-hidden="true"></i>
                    <b style="cursor:pointer;"
                       onclick="show_hide('field_frm<?php echo $result_str;?>')"><?php echo $this->uwp_ucwords(__('Fieldset:', 'uwp') . ' ' . $field_site_title);?></b>
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
                    <input type="hidden" name="is_active" id="is_active" value="1"/>

                    <input type="hidden" name="is_default" value="<?php echo isset($field_info->is_default) ? $field_info->is_default : '';?>" /><?php // show in sidebar value?>

                    <ul class="widefat post fixed" style="width:100%;">

                        <?php
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
                                <label for="site_title" class="uwp-tooltip-wrap"> <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Form Label :', 'uwp'); ?>
                                    <div class="uwp-tooltip">
                                        <?php _e('This will be the label for the field on the form.', 'uwp'); ?>
                                    </div>
                                </label>
                                <div class="uwp-input-wrap">
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
                                <label for="htmlvar_name" class="uwp-tooltip-wrap"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('HTML variable name :', 'uwp');?>
                                    <div class="uwp-tooltip">
                                        <?php _e('This is a unique identifier used in the HTML, it MUST NOT contain spaces or special characters.', 'uwp'); ?>
                                    </div>
                                </label>
                                <div class="uwp-input-wrap">
                                    <input type="text" name="htmlvar_name" id="htmlvar_name" pattern="[a-zA-Z0-9]+" title="<?php _e('Must not contain spaces or special characters', 'uwp');?>"
                                           value="<?php if ($value) {
                                               echo preg_replace('/uwp_'.$form_type.'_/', '', $value, 1);
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
                                <label for="is_active" class="uwp-tooltip-wrap"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Is active :', 'uwp'); ?>
                                    <div class="uwp-tooltip">
                                        <?php _e('If no is selected then the field will not be displayed anywhere.', 'uwp'); ?>
                                    </div>
                                </label>
                                <div class="uwp-input-wrap gd-switch">

                                    <input type="radio" id="is_active_yes<?php echo $radio_id;?>" name="is_active" class="gdri-enabled"  value="1"
                                        <?php if ($value == '1') {
                                            echo 'checked';
                                        } ?>/>
                                    <label for="is_active_yes<?php echo $radio_id;?>" class="gdcb-enable"><span><?php _e('Yes', 'uwp'); ?></span></label>

                                    <input type="radio" id="is_active_no<?php echo $radio_id;?>" name="is_active" class="gdri-disabled" value="0"
                                        <?php if ($value == '0' || !$value) {
                                            echo 'checked';
                                        } ?>/>
                                    <label for="is_active_no<?php echo $radio_id;?>" class="gdcb-disable"><span><?php _e('No', 'uwp'); ?></span></label>

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
                                <label for="default_value" class="uwp-tooltip-wrap"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Default value :', 'uwp');?>
                                    <div class="uwp-tooltip">
                                        <?php
                                        if ($field_type == 'checkbox') {
                                            _e('Should the checkbox be checked by default?', 'uwp');
                                        } else if ($field_type == 'email') {
                                            _e('A default value for the field, usually blank. Ex: info@mysite.com', 'uwp');
                                        } else {
                                            _e('A default value for the field, usually blank. (for "link" this will be used as the link text)', 'uwp');
                                        }
                                        ?>
                                    </div>
                                </label>
                                <div class="uwp-input-wrap">
                                    <?php if ($field_type == 'checkbox') { ?>
                                        <select name="default_value" id="default_value">
                                            <option value=""><?php _e('Unchecked', 'uwp'); ?></option>
                                            <option value="1" <?php selected(true, (int)$value === 1);?>><?php _e('Checked', 'uwp'); ?></option>
                                        </select>
                                    <?php } else if ($field_type == 'email') { ?>
                                        <input type="email" name="default_value" placeholder="<?php _e('info@mysite.com', 'uwp') ;?>" id="default_value" value="<?php echo esc_attr($value);?>" /><br/>
                                    <?php } else { ?>
                                        <input type="text" name="default_value" id="default_value" value="<?php echo esc_attr($value);?>" /><br/>
                                    <?php } ?>
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
                                <label for="is_required" class="uwp-tooltip-wrap"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Is required :', 'uwp'); ?>
                                    <div class="uwp-tooltip">
                                        <?php _e('Select yes to set field as required', 'uwp'); ?>
                                    </div>
                                </label>

                                <div class="uwp-input-wrap gd-switch">

                                    <input type="radio" id="is_required_yes<?php echo $radio_id;?>" name="is_required" class="gdri-enabled"  value="1"
                                        <?php if ($value == '1') {
                                            echo 'checked';
                                        } ?>/>
                                    <label onclick="show_hide_radio(this,'show','cf-is-required-msg');" for="is_required_yes<?php echo $radio_id;?>" class="gdcb-enable"><span><?php _e('Yes', 'uwp'); ?></span></label>

                                    <input type="radio" id="is_required_no<?php echo $radio_id;?>" name="is_required" class="gdri-disabled" value="0"
                                        <?php if ($value == '0' || !$value) {
                                            echo 'checked';
                                        } ?>/>
                                    <label onclick="show_hide_radio(this,'hide','cf-is-required-msg');" for="is_required_no<?php echo $radio_id;?>" class="gdcb-disable"><span><?php _e('No', 'uwp'); ?></span></label>

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
                                <label for="required_msg" class="uwp-tooltip-wrap">
                                    <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Required message:', 'uwp'); ?>
                                    <div class="uwp-tooltip">
                                        <?php _e('Enter text for the error message if the field is required and has not fulfilled the requirements.', 'uwp'); ?>
                                    </div>
                                </label>
                                <div class="uwp-input-wrap">
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

                                <label for="field_icon" class="uwp-tooltip-wrap">
                                    <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Upload icon :', 'uwp'); ?>
                                    <div class="uwp-tooltip">
                                        <?php _e('Upload icon using media and enter its url path, or enter <a href="http://fortawesome.github.io/Font-Awesome/icons/" target="_blank" >font awesome </a>class eg:"fa fa-home"', 'uwp');?>
                                    </div>
                                </label>
                                <div class="uwp-input-wrap">
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

                                <label for="css_class" class="uwp-tooltip-wrap">
                                    <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Css class :', 'uwp'); ?>
                                    <div class="uwp-tooltip">
                                        <?php _e('Enter custom css class for field custom style.', 'uwp');?>
                                        <?php if($field_type=='multiselect'){_e('(Enter class `gd-comma-list` to show list as comma separated)', 'uwp');}?>
                                    </div>
                                </label>
                                <div class="uwp-input-wrap">
                                    <input type="text" name="css_class" id="css_class"
                                           value="<?php if (isset($field_info->css_class)) {
                                               echo esc_attr($field_info->css_class);
                                           }?>"/>
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

                            <label for="save" class="uwp-tooltip-wrap">
                            </label>
                            <div class="uwp-input-wrap">
                                <input type="button" class="button button-primary" name="save" id="save" value="<?php echo esc_attr(__('Save' ,'uwp'));?>"
                                       onclick="save_field('<?php echo esc_attr($result_str); ?>')"/>
                                <?php if (!$default): ?>
                                    <a href="javascript:void(0)"><input type="button" name="delete" value="<?php echo esc_attr(__('Delete' ,'uwp'));?>"
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

    public function uwp_custom_field_adminhtml($field_type, $result_str, $field_ins_upd = '', $field_type_key ='', $form_type = false)
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

        $this->uwp_custom_field_html($field_info, $field_type, $field_type_key, $field_ins_upd, $result_str, $form_type);

    }

    public function uwp_ucwords($string, $charset='UTF-8') {
        if (function_exists('mb_convert_case')) {
            return mb_convert_case($string, MB_CASE_TITLE, $charset);
        } else {
            return ucwords($string);
        }
    }

    public function uwp_custom_field_save($request_field = array(), $default = false)
    {

        global $wpdb;

        $table_name = $wpdb->prefix . 'uwp_custom_fields';

        $result_str = isset($request_field['field_id']) ? trim($request_field['field_id']) : '';

        $post_meta_info = null;
        $cf = trim($result_str, '_');


        $cehhtmlvar_name = isset($request_field['htmlvar_name']) ? $request_field['htmlvar_name'] : '';
        $form_type = $request_field['form_type'];

        if ($request_field['field_type'] != 'fieldset') {
            $cehhtmlvar_name = 'uwp_' .$form_type. '_' . $cehhtmlvar_name;
        }

        $check_html_variable = $wpdb->get_var(
            $wpdb->prepare(
                "select htmlvar_name from " . $table_name . " where id <> %d and htmlvar_name = %s and form_type = %s ",
                array($cf, $cehhtmlvar_name, $form_type)
            )
        );


        if (!$check_html_variable || $request_field['field_type'] == 'fieldset') {

            if ($cf != '') {

                $post_meta_info = $wpdb->get_row(
                    $wpdb->prepare(
                        "select * from " . $table_name . " where id = %d",
                        array($cf)
                    )
                );

            }


            $site_title = $request_field['site_title'];
            $field_type = $request_field['field_type'];
            $field_type_key = isset($request_field['field_type_key']) ? $request_field['field_type_key'] : $field_type;
            $htmlvar_name = isset($request_field['htmlvar_name']) ? $request_field['htmlvar_name'] : '';
            $default_value = isset($request_field['default_value']) ? $request_field['default_value'] : '';
            $sort_order = isset($request_field['sort_order']) ? $request_field['sort_order'] : '';
            $is_active = isset($request_field['is_active']) ? $request_field['is_active'] : '';
            $is_required = isset($request_field['is_required']) ? $request_field['is_required'] : '';
            $required_msg = isset($request_field['required_msg']) ? $request_field['required_msg'] : '';
            $css_class = isset($request_field['css_class']) ? $request_field['css_class'] : '';
            $field_icon = isset($request_field['field_icon']) ? $request_field['field_icon'] : '';
            $show_in = isset($request_field['show_in']) ? $request_field['show_in'] : '';
            $validation_pattern = isset($request_field['validation_pattern']) ? $request_field['validation_pattern'] : '';
            $validation_msg = isset($request_field['validation_msg']) ? $request_field['validation_msg'] : '';


            if(is_array($show_in)){
                $show_in = implode(",", $request_field['show_in']);
            }

            if ($field_type != 'fieldset') {
                $htmlvar_name = 'uwp_' .$form_type. '_' . $htmlvar_name;
            }

            $option_values = '';
            if (isset($request_field['option_values']))
                $option_values = $request_field['option_values'];

            if (isset($request_field['extra']) && !empty($request_field['extra']))
                $extra_fields = $request_field['extra'];

            if (isset($request_field['is_default']) && $request_field['is_default'] != '')
                $is_default = $request_field['is_default'];
            else
                $is_default = '0';

            if ($is_active == '') $is_active = 1;
            if ($is_required == '') $is_required = 0;


            if ($sort_order == '') {

                $last_order = $wpdb->get_var("SELECT MAX(sort_order) as last_order FROM " . $table_name);

                $sort_order = (int)$last_order + 1;
            }


            if (!empty($post_meta_info)) {

                $extra_field_query = '';
                if (!empty($extra_fields)) {
                    $extra_field_query = serialize($extra_fields);
                }

                $wpdb->query(

                    $wpdb->prepare(

                        "update " . $table_name . " set
                            form_type = %s,
                            site_title = %s,
                            field_type = %s,
                            field_type_key = %s,
                            htmlvar_name = %s,
                            default_value = %s,
                            sort_order = %s,
                            is_active = %s,
                            is_default  = %s,
                            is_required = %s,
                            required_msg = %s,
                            css_class = %s,
                            field_icon = %s,
                            field_icon = %s,
                            show_in = %s,
                            option_values = %s,
                            extra_fields = %s,
                            validation_pattern = %s,
                            validation_msg = %s
                            where id = %d",

                        array(
                            $form_type,
                            $site_title,
                            $field_type,
                            $field_type_key,
                            $htmlvar_name,
                            $default_value,
                            $sort_order,
                            $is_active,
                            $is_default,
                            $is_required,
                            $required_msg,
                            $css_class,
                            $field_icon,
                            $field_icon,
                            $show_in,
                            $option_values,
                            $extra_field_query,
                            $validation_pattern,
                            $validation_msg,
                            $cf
                        )
                    )

                );

                $lastid = trim($cf);


                do_action('geodir_after_custom_fields_updated', $lastid);

            } else {

                $extra_field_query = '';
                if (!empty($extra_fields)) {
                    $extra_field_query = serialize($extra_fields);
                }

                $wpdb->query(

                    $wpdb->prepare(

                        "insert into " . $table_name . " set
                            form_type = %s,
                            site_title = %s,
                            field_type = %s,
                            field_type_key = %s,
                            htmlvar_name = %s,
                            default_value = %s,
                            sort_order = %d,
                            is_active = %s,
                            is_default  = %s,
                            is_required = %s,
                            required_msg = %s,
                            css_class = %s,
                            field_icon = %s,
                            show_in = %s,
                            option_values = %s,
                            extra_fields = %s,
                            validation_pattern = %s,
                            validation_msg = %s ",

                        array(
                            $form_type,
                            $site_title,
                            $field_type,
                            $field_type_key,
                            $htmlvar_name,
                            $default_value,
                            $sort_order,
                            $is_active,
                            $is_default,
                            $is_required,
                            $required_msg,
                            $css_class,
                            $field_icon,
                            $show_in,
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

            return (int)$lastid;


        } else {
            return 'HTML Variable Name should be a unique name';
        }

    }

    public function uwp_set_field_order($field_ids = array())
    {

        global $wpdb;

        $table_name = $wpdb->prefix . 'uwp_custom_fields';

        $count = 0;
        if (!empty($field_ids)):
            $post_meta_info = false;
            foreach ($field_ids as $id) {

                $cf = trim($id, '_');

                $post_meta_info = $wpdb->query(
                    $wpdb->prepare(
                        "update " . $table_name . " set
															sort_order=%d
															where id= %d",
                        array($count, $cf)
                    )
                );
                $count++;
            }

            return $post_meta_info;
        else:
            return false;
        endif;
    }

    public function uwp_custom_field_delete($field_id = '') {
        global $wpdb;

        $table_name = $wpdb->prefix . 'uwp_custom_fields';

        if ($field_id != '') {
            $cf = trim($field_id, '_');

            if ($field = $wpdb->get_row($wpdb->prepare("select htmlvar_name from " . $table_name . " where id= %d", array($cf)))) {

                $wpdb->query($wpdb->prepare("delete from " . $table_name . " where id= %d ", array($cf)));

                $form_type = $field->form_type;

                //todo: add option to delete data
                do_action('uwp_after_custom_field_deleted', $cf, $field->htmlvar_name, $form_type);

                return $field_id;
            } else
                return 0;
        } else
            return 0;
    }

    public function uwp_cfa_extra_fields_smr($output,$result_str,$cf,$field_info){

        ob_start();

        $value = '';
        if (isset($field_info->option_values)) {
            $value = esc_attr($field_info->option_values);
        }elseif(isset($cf['defaults']['option_values']) && $cf['defaults']['option_values']){
            $value = esc_attr($cf['defaults']['option_values']);
        }

        $field_type = isset($field_info->field_type) ? $field_info->field_type : '';
        ?>
        <li>
            <label for="option_values" class="uwp-tooltip-wrap">
                <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Option Values :', 'uwp'); ?>
                <div class="uwp-tooltip">
                    <span><?php _e('Option Values should be separated by comma.', 'uwp');?></span>
                    <br/>
                    <small><span><?php _e('If using for a "tick filter" place a / and then either a 1 for true or 0 for false', 'uwp');?></span>
                        <br/>
                        <span><?php _e('eg: "No Dogs Allowed/0,Dogs Allowed/1" (Select only, not multiselect)', 'uwp');?></span>
                        <?php if ($field_type == 'multiselect' || $field_type == 'select') { ?>
                            <br/>
                            <span><?php _e('- If using OPTGROUP tag to grouping options, use "{optgroup}OPTGROUP-LABEL|OPTION-1,OPTION-2{/optgroup}"', 'uwp'); ?></span>
                            <br/>
                            <span><?php _e('eg: "{optgroup}Pets Allowed|No Dogs Allowed/0,Dogs Allowed/1{/optgroup},{optgroup}Sports|Cricket/Cricket,Football/Football,Hockey{/optgroup}"', 'uwp'); ?></span>
                        <?php } ?></small>
                </div>
            </label>
            <div class="uwp-input-wrap">
                <input type="text" name="option_values" id="option_values"
                       value="<?php echo $value;?>"/>
                <br/>

            </div>
        </li>
        <?php

        $html = ob_get_clean();
        return $output.$html;
    }

    public function uwp_cfa_extra_fields_datepicker($output,$result_str,$cf,$field_info){
        ob_start();
        $extra = array();
        if (isset($field_info->extra_fields) && $field_info->extra_fields != '') {
            $extra = unserialize($field_info->extra_fields);
        }
        ?>
        <li>
            <label for="date_format" class="uwp-tooltip-wrap">
                <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Date Format :', 'uwp'); ?>
                <div class="uwp-tooltip">
                    <?php _e('Select the date format.', 'uwp');?>
                </div>
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
                
                $date_formats = apply_filters('uwp_date_formats',$date_formats);
                ?>
                <select name="extra[date_format]" id="date_format">
                    <?php
                    foreach($date_formats as $format){
                        $selected = '';
                        if(!empty($extra) && esc_attr($extra['date_format'])==$format){
                            $selected = "selected='selected'";
                        }
                        echo "<option $selected value='$format'>$format       (".date_i18n( $format, time()).")</option>";
                    }
                    ?>
                </select>

            </div>
        </li>
        <?php

        $html = ob_get_clean();
        return $output.$html;
    }

    public function uwp_cfa_extra_fields_file($output,$result_str,$cf,$field_info){
        ob_start();
        $allowed_file_types = uwp_allowed_mime_types();

        $extra_fields = isset($field_info->extra_fields) && $field_info->extra_fields != '' ? maybe_unserialize($field_info->extra_fields) : '';
        $gd_file_types = !empty($extra_fields) && !empty($extra_fields['uwp_file_types']) ? $extra_fields['uwp_file_types'] : array('*');
        ?>
        <li>
            <label for="gd_file_types" class="uwp-tooltip-wrap">
                <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Allowed file types :', 'uwp'); ?>
                <div class="uwp-tooltip">
                    <?php _e('Select file types to allowed for file uploading. (Select multiple file types by holding down "Ctrl" key.)', 'uwp');?>
                </div>
            </label>
            <div class="uwp-input-wrap">
                <select name="extra[uwp_file_types][]" id="uwp_file_types" multiple="multiple" style="height:100px;width:90%;">
                    <option value="*" <?php selected(true, in_array('*', $gd_file_types));?>><?php _e('All types', 'uwp') ;?></option>
                    <?php foreach ( $allowed_file_types as $format => $types ) { ?>
                        <optgroup label="<?php echo esc_attr( wp_sprintf(__('%s formats', 'uwp'), __($format, 'uwp') ) ) ;?>">
                            <?php foreach ( $types as $ext => $type ) { ?>
                                <option value="<?php echo esc_attr($ext) ;?>" <?php selected(true, in_array($ext, $gd_file_types));?>><?php echo '.' . $ext ;?></option>
                            <?php } ?>
                        </optgroup>
                    <?php } ?>
                </select>
            </div>
        </li>
        <?php

        $html = ob_get_clean();
        return $output.$html;
    }

    public function return_empty_string() {
        return "";
    }

}