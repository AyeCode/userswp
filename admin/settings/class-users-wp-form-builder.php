<?php
/**
 * The form builder functionality of the plugin.
 *
 * @link       http://wpgeodirectory.com
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
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class Users_WP_Form_Builder {

    protected $loader;

    public function __construct() {

    }

    public function uwp_form_builder($default_tab = 'account')
    {
        $form_type = (isset($_REQUEST['subtab']) && $_REQUEST['subtab'] != '') ? sanitize_text_field($_REQUEST['subtab']) : $default_tab;
        ?>
        <div class="uwp-panel-heading">
            <h3><?php echo apply_filters('uwp_form_builder_panel_head', '');?></h3>
        </div>

        <div id="uwp_form_builder_container" class="clearfix">
            <div class="uwp-form-builder-frame">
                <div class="uwp-side-sortables" id="uwp-available-fields">
                    <h3 class="hndle">
                    <span>
                        <?php echo apply_filters('uwp_form_builder_available_fields_head', __('Add new form field', 'uwp'), $form_type); ?>
                    </span>
                    </h3>

                    <p>
                        <?php
                        $note = sprintf(__('Click on any box below to add a field of that type on '.$form_type.' form. You must be use a fieldset to group your fields.', 'uwp'));
                        echo apply_filters('uwp_form_builder_available_fields_note', $note, $form_type);
                        ?>
                    </p>

                    <h3>
                        <?php _e('Setup New Field' ,'uwp'); ?>
                    </h3>

                    <div class="inside">
                        <div id="uwp-form-builder-tab" class="uwp-tabs-panel">
                            <?php do_action('uwp_manage_available_fields', $form_type); ?>
                        </div>
                    </div>

                    <?php if ($form_type=='account') { ?>
                        <h3>
                            <?php _e('Predefined Fields' ,'uwp'); ?>
                        </h3>

                        <div class="inside">
                            <div id="uwp-form-builder-tab-predefined" class="uwp-tabs-panel">
                                <?php do_action('uwp_manage_available_fields_predefined', $form_type); ?>
                            </div>
                        </div>
                    <?php } ?>
                    
                </div>


                <div class="uwp-side-sortables" id="uwp-selected-fields">

                    <h3 class="hndle">
                        <span>
                            <?php
                            $title = __('List of fields those will appear on add new listing form', 'uwp');
                            echo apply_filters('uwp_form_builder_selected_fields_head', $title, $form_type); ?>
                        </span>
                    </h3>

                    <p>
                        <?php
                        $note = __('Click to expand and view field related settings. You may drag and drop to arrange fields order on '.$form_type.' form too.', 'uwp');
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
    }

    public function uwp_custom_available_fields($type='', $form_type)
    {
        ?>
        <input type="hidden" name="form_type" id="form_type" value="<?php echo $form_type;?>"/>
        <input type="hidden" name="manage_field_type" class="manage_field_type" value="custom_fields">
        <?php
        if($type=='predefined'){
            $fields = $this->uwp_form_fields_predefined($form_type);
        }elseif($type=='custom'){
            $fields = $this->uwp_form_fields_custom($form_type);
        }else{
            $fields = $this->uwp_form_fields($form_type);
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
            ?>
            <ul>
            <?php
            foreach ( $fields as $id => $field ) {
                ?>
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

    public function uwp_form_fields_predefined($type='') {
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
        

        return apply_filters('uwp_form_fields_predefined',$custom_fields,$type);
    }

    public function uwp_form_fields_custom($type='') {
        $custom_fields = array();
        return apply_filters('uwp_form_fields_custom',$custom_fields,$type);
    }

    public function uwp_form_fields($type='') {

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
                'class' =>  'uwp-file',
                'icon' =>  'fa fa-file',
                'name'  =>  __('File Upload', 'uwp'),
                'description' =>  __('Adds a file input', 'uwp')
            )
        );

        return apply_filters('uwp_form_fields', $custom_fields, $type);
    }


    public function uwp_manage_available_fields_predefined($form_type){
        switch ($form_type) {
            case 'account':
                $this->uwp_custom_available_fields('predefined', $form_type);
                break;
        }
    }

    public function uwp_manage_available_fields_custom($form_type){
        switch ($form_type) {
            case 'account':
                $this->uwp_custom_available_fields('custom', $form_type);
                break;
        }
    }

    public function uwp_manage_available_fields($form_type) {
        switch ($form_type) {
            case 'account':
                $this->uwp_custom_available_fields('', $form_type);
                break;
        }
    }

    public function uwp_manage_selected_fields($form_type) {
        switch ($form_type) {
            case 'account':
                $this->uwp_custom_selected_fields($form_type);
                break;
        }
    }

    function uwp_custom_selected_fields($form_type)
    {

        global $wpdb;
        $table_name = $wpdb->prefix . 'uwp_form_fields';
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

                    $this->uwp_form_field_adminhtml($field_type, $result_str, $field_ins_upd,$field_type_key);
                }
            }
            ?></ul>
        <?php

    }

    public function uwp_admin_form_field_html($field_info, $field_type, $field_type_key, $field_ins_upd, $result_str, $form_type = false) {

        if (!$form_type) {
            if (!isset($field_info->form_type)) {
                $form_type = sanitize_text_field($_REQUEST['subtab']);
            } else {
                $form_type = $field_info->form_type;
            }
        }

        $cf_arr1 = $this->uwp_form_fields($form_type);
        $cf_arr2 = $this->uwp_form_fields_predefined($form_type);
        $cf_arr3 = $this->uwp_form_fields_custom($form_type);

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
                    <div title="<?php _e('Default field, should not be removed.', 'uwp'); ?>" class="handlediv move uwp-default-remove"><i class="fa fa-times" aria-hidden="true"></i></div>
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
                        if(has_filter("uwp_builder_site_title_{$field_type}")){

                            echo apply_filters("uwp_builder_site_title_{$field_type}",'',$result_str,$cf,$field_info);

                        }else{
                            $value = '';
                            if (isset($field_info->site_title)) {
                                $value = esc_attr($field_info->site_title);
                            }elseif(isset($cf['defaults']['site_title']) && $cf['defaults']['site_title']){
                                $value = $cf['defaults']['site_title'];
                            }
                            ?>
                            <li>
                                <label for="site_title" class="uwp-tooltip-wrap"> <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Label :', 'uwp'); ?>
                                    <div class="uwp-tooltip">
                                        <?php _e('This will be the label for the field.', 'uwp'); ?>
                                    </div>
                                </label>
                                <div class="uwp-input-wrap">
                                    <input type="text" name="site_title" id="site_title"
                                           value="<?php echo $value; ?>"/>
                                </div>
                            </li>
                            <?php
                        }

                        // Input Label
                        if(has_filter("uwp_builder_form_label_{$field_type}")){

                            echo apply_filters("uwp_builder_form_label_{$field_type}",'',$result_str,$cf,$field_info);

                        }else{
                            $value = '';
                            if (isset($field_info->form_label)) {
                                $value = esc_attr($field_info->form_label);
                            }elseif(isset($cf['defaults']['form_label']) && $cf['defaults']['form_label']){
                                $value = $cf['defaults']['form_label'];
                            }
                            ?>
                            <li>
                                <label for="form_label" class="uwp-tooltip-wrap"> <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Form Label: (Optional)', 'uwp'); ?>
                                    <div class="uwp-tooltip">
                                        <?php _e('If your form label is different, then you can fill this field. Ex: You would like to display "What is your age?" in Form Field but would like to display "DOB" in site. In such cases "What is your age?" should be entered here and "DOB" should be entered in previous field. Note: If this field not field, then the previous field will be used in Form. ', 'uwp'); ?>
                                    </div>
                                </label>
                                <div class="uwp-input-wrap">
                                    <input type="text" name="form_label" id="form_label"
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
                        if(has_filter("uwp_builder_is_active_{$field_type}")){

                            echo apply_filters("uwp_builder_is_active_{$field_type}",'',$result_str,$cf,$field_info);

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
                                <div class="uwp-input-wrap uwp-switch">

                                    <input type="radio" id="is_active_yes<?php echo $radio_id;?>" name="is_active" class="uwp-ri-enabled"  value="1"
                                        <?php if ($value == '1') {
                                            echo 'checked';
                                        } ?>/>
                                    <label for="is_active_yes<?php echo $radio_id;?>" class="uwp-cb-enable"><span><?php _e('Yes', 'uwp'); ?></span></label>

                                    <input type="radio" id="is_active_no<?php echo $radio_id;?>" name="is_active" class="uwp-ri-disabled" value="0"
                                        <?php if ($value == '0' || !$value) {
                                            echo 'checked';
                                        } ?>/>
                                    <label for="is_active_no<?php echo $radio_id;?>" class="uwp-cb-disable"><span><?php _e('No', 'uwp'); ?></span></label>

                                </div>
                            </li>
                            <?php
                        }


                        // is_public
                        if(has_filter("uwp_builder_is_public_{$field_type}")){

                            echo apply_filters("uwp_builder_is_public_{$field_type}",'',$result_str,$cf,$field_info);

                        }else{
                            $value = '';
                            if (isset($field_info->is_public)) {
                                $value = esc_attr($field_info->is_public);
                            }elseif(isset($cf['defaults']['is_public']) && $cf['defaults']['is_public']){
                                $value = $cf['defaults']['is_public'];
                            }
                            ?>
                            <li <?php echo $field_display; ?>>
                                <label for="is_public" class="uwp-tooltip-wrap"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Is Public :', 'uwp'); ?>
                                    <div class="uwp-tooltip">
                                        <?php _e('If no is selected then the field will not be visible to other users.', 'uwp'); ?>
                                    </div>
                                </label>
                                <div class="uwp-input-wrap uwp-switch">

                                    <input type="radio" id="is_public_yes<?php echo $radio_id;?>" name="is_public" class="uwp-ri-enabled"  value="1"
                                        <?php if ($value == '1') {
                                            echo 'checked';
                                        } ?>/>
                                    <label for="is_public_yes<?php echo $radio_id;?>" class="uwp-cb-enable"><span><?php _e('Yes', 'uwp'); ?></span></label>

                                    <input type="radio" id="is_public_no<?php echo $radio_id;?>" name="is_public" class="uwp-ri-disabled" value="0"
                                        <?php if ($value == '0' || !$value) {
                                            echo 'checked';
                                        } ?>/>
                                    <label for="is_public_no<?php echo $radio_id;?>" class="uwp-cb-disable"><span><?php _e('No', 'uwp'); ?></span></label>

                                </div>
                            </li>
                            <?php
                        }


                        // default_value
                        if(has_filter("uwp_builder_default_value_{$field_type}")){

                            echo apply_filters("uwp_builder_default_value_{$field_type}",'',$result_str,$cf,$field_info);

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
                        if(has_filter("uwp_builder_advanced_editor_{$field_type}")){

                            echo apply_filters("uwp_builder_advanced_editor_{$field_type}",'',$result_str,$cf,$field_info);

                        }


                        ?>

                        <?php // we dont need to show the sort order ?>
                        <input type="hidden" readonly="readonly" name="sort_order" id="sort_order" value="<?php if (isset($field_info->sort_order)) { echo esc_attr($field_info->sort_order);} ?>"/>



                        <?php

                        // is_required
                        if(has_filter("uwp_builder_is_required_{$field_type}")){

                            echo apply_filters("uwp_builder_is_required_{$field_type}",'',$result_str,$cf,$field_info);

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

                                <div class="uwp-input-wrap uwp-switch">

                                    <input type="radio" id="is_required_yes<?php echo $radio_id;?>" name="is_required" class="uwp-ri-enabled"  value="1"
                                        <?php if ($value == '1') {
                                            echo 'checked';
                                        } ?>/>
                                    <label onclick="show_hide_radio(this,'show','cf-is-required-msg');" for="is_required_yes<?php echo $radio_id;?>" class="uwp-cb-enable"><span><?php _e('Yes', 'uwp'); ?></span></label>

                                    <input type="radio" id="is_required_no<?php echo $radio_id;?>" name="is_required" class="uwp-ri-disabled" value="0"
                                        <?php if ($value == '0' || !$value) {
                                            echo 'checked';
                                        } ?>/>
                                    <label onclick="show_hide_radio(this,'hide','cf-is-required-msg');" for="is_required_no<?php echo $radio_id;?>" class="uwp-cb-disable"><span><?php _e('No', 'uwp'); ?></span></label>

                                </div>

                            </li>

                            <?php
                        }

                        // required_msg
                        if(has_filter("uwp_builder_required_msg_{$field_type}")){

                            echo apply_filters("uwp_builder_required_msg_{$field_type}",'',$result_str,$cf,$field_info);

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
                        if(has_filter("uwp_builder_validation_pattern_{$field_type}")){

                            echo apply_filters("uwp_builder_validation_pattern_{$field_type}",'',$result_str,$cf,$field_info);

                        }


                        // extra_fields
                        if(has_filter("uwp_builder_extra_fields_{$field_type}")){

                            echo apply_filters("uwp_builder_extra_fields_{$field_type}",'',$result_str,$cf,$field_info);

                        }


                        // field_icon
                        if(has_filter("uwp_builder_field_icon_{$field_type}")){

                            echo apply_filters("uwp_builder_field_icon_{$field_type}",'',$result_str,$cf,$field_info);

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
                        if(has_filter("uwp_builder_css_class_{$field_type}")){

                            echo apply_filters("uwp_builder_css_class_{$field_type}",'',$result_str,$cf,$field_info);

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
                                        <?php if($field_type=='multiselect'){_e('(Enter class `uwp-comma-list` to show list as comma separated)', 'uwp');}?>
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

                        // show_in
                        if(has_filter("uwp_builder_show_in_{$field_type}")){

                            echo apply_filters("uwp_builder_show_in_{$field_type}",'',$result_str,$cf,$field_info);

                        }else{
                            $value = '';
                            if (isset($field_info->show_in)) {
                                $value = esc_attr($field_info->show_in);
                            }elseif(isset($cf['defaults']['show_in']) && $cf['defaults']['show_in']){
                                $value = esc_attr($cf['defaults']['show_in']);
                            }
                            ?>
                            <li>
                                <label for="show_in" class="uwp-tooltip-wrap"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Show in what locations?:', 'uwp'); ?>
                                    <div class="uwp-tooltip">
                                        <?php _e('Select in what locations you want to display this field.', 'uwp'); ?>
                                    </div>
                                </label>
                                <div class="uwp-input-wrap">

                                    <?php

                                    $show_in_locations = array(
                                        "[users]" => __("Users Page", 'uwp'),
                                        "[more_info]" => __("More info tab", 'uwp'),
                                        "[own_tab]" => __("Profile page own tab", 'uwp'),
                                        "[profile_side]" => __("Profile Side", 'uwp'),
                                    );

                                    $show_in_locations = apply_filters('uwp_show_in_locations',$show_in_locations,$field_info,$field_type);

                                    
                                    if (in_array($field_type, array('text', 'datepicker', 'textarea', 'time', 'phone', 'email', 'select', 'multiselect', 'url', 'html', 'fieldset', 'radio', 'checkbox', 'file'))) {
                                    }else{
                                        unset($show_in_locations['[own_tab]']);
                                    }
                                    
                                    ?>

                                    <select multiple="multiple" name="show_in[]"
                                            id="show_in"
                                            style="min-width:300px;"
                                            class="uwp_chosen_select"
                                            data-placeholder="<?php _e('Select locations', 'uwp'); ?>">
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



                        switch ($field_type):
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

    public function uwp_form_field_adminhtml($field_type, $result_str, $field_ins_upd = '', $field_type_key ='', $form_type = false)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'uwp_form_fields';
        $cf = $result_str;
        if (!is_object($cf)) {

            $field_info = $wpdb->get_row($wpdb->prepare("select * from " . $table_name . " where id= %d", array($cf)));

        } else {
            $field_info = $cf;
            $result_str = $cf->id;
        }

        $this->uwp_admin_form_field_html($field_info, $field_type, $field_type_key, $field_ins_upd, $result_str, $form_type);

    }

    public function uwp_ucwords($string, $charset='UTF-8') {
        if (function_exists('mb_convert_case')) {
            return mb_convert_case($string, MB_CASE_TITLE, $charset);
        } else {
            return ucwords($string);
        }
    }

    public function uwp_admin_form_field_save($request_field = array(), $default = false)
    {

        global $wpdb;

        $table_name = $wpdb->prefix . 'uwp_form_fields';

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
            $form_label = isset($request_field['form_label']) ? $request_field['form_label'] : '';
            $field_type = $request_field['field_type'];
            $field_type_key = isset($request_field['field_type_key']) ? $request_field['field_type_key'] : $field_type;
            $htmlvar_name = isset($request_field['htmlvar_name']) ? $request_field['htmlvar_name'] : '';
            $default_value = isset($request_field['default_value']) ? $request_field['default_value'] : '';
            $sort_order = isset($request_field['sort_order']) ? $request_field['sort_order'] : '';
            $is_active = isset($request_field['is_active']) ? $request_field['is_active'] : '';
            $is_required = isset($request_field['is_required']) ? $request_field['is_required'] : '';
            $is_dummy = isset($request_field['is_dummy']) ? $request_field['is_dummy'] : '';
            $is_public = isset($request_field['is_public']) ? $request_field['is_public'] : '';
            $is_register_field = isset($request_field['is_register_field']) ? $request_field['is_register_field'] : '';
            $is_register_only_field = isset($request_field['is_register_only_field']) ? $request_field['is_register_only_field'] : '';
            $required_msg = isset($request_field['required_msg']) ? $request_field['required_msg'] : '';
            $css_class = isset($request_field['css_class']) ? $request_field['css_class'] : '';
            $field_icon = isset($request_field['field_icon']) ? $request_field['field_icon'] : '';
            $show_in = isset($request_field['show_in']) ? $request_field['show_in'] : '';
            $validation_pattern = isset($request_field['validation_pattern']) ? $request_field['validation_pattern'] : '';
            $validation_msg = isset($request_field['validation_msg']) ? $request_field['validation_msg'] : '';


            if(is_array($show_in)){
                $show_in = implode(",", $request_field['show_in']);
            }

            // fieldset need htmlvar_name for register tab
            //if ($field_type != 'fieldset') {
                $htmlvar_name = 'uwp_' .$form_type. '_' . $htmlvar_name;
            //}

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
            if ($is_dummy == '') $is_dummy = 0;
            if ($is_public == '') $is_public = 0;
            if ($is_register_field == '') $is_register_field = 0;
            if ($is_register_only_field == '') $is_register_only_field = 0;


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
                            form_label = %s,
                            field_type = %s,
                            field_type_key = %s,
                            htmlvar_name = %s,
                            default_value = %s,
                            sort_order = %s,
                            is_active = %s,
                            is_default  = %s,
                            is_required = %s,
                            is_dummy = %s,
                            is_public = %s,
                            is_register_field = %s,
                            is_register_only_field = %s,
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
                            $form_label,
                            $field_type,
                            $field_type_key,
                            $htmlvar_name,
                            $default_value,
                            $sort_order,
                            $is_active,
                            $is_default,
                            $is_required,
                            $is_dummy,
                            $is_public,
                            $is_register_field,
                            $is_register_only_field,
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


                do_action('uwp_after_custom_fields_updated', $lastid);

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
                            form_label = %s,
                            field_type = %s,
                            field_type_key = %s,
                            htmlvar_name = %s,
                            default_value = %s,
                            sort_order = %d,
                            is_active = %s,
                            is_default  = %s,
                            is_required = %s,
                            is_dummy = %s,
                            is_public = %s,
                            is_register_field = %s,
                            is_register_only_field = %s,
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
                            $form_label,
                            $field_type,
                            $field_type_key,
                            $htmlvar_name,
                            $default_value,
                            $sort_order,
                            $is_active,
                            $is_default,
                            $is_required,
                            $is_dummy,
                            $is_public,
                            $is_register_field,
                            $is_register_only_field,
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
            return __('HTML Variable Name should be a unique name', 'uwp');
        }

    }

    public function uwp_set_field_order($field_ids = array())
    {

        global $wpdb;

        $table_name = $wpdb->prefix . 'uwp_form_fields';

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

    public function uwp_admin_form_field_delete($field_id = '') {
        global $wpdb;

        $table_name = $wpdb->prefix . 'uwp_form_fields';
        $extras_table_name = $wpdb->prefix . 'uwp_form_extras';

        if ($field_id != '') {
            $cf = trim($field_id, '_');

            if ($field = $wpdb->get_row($wpdb->prepare("select * from " . $table_name . " where id= %d", array($cf)))) {

                $wpdb->query($wpdb->prepare("delete from " . $table_name . " where id= %d ", array($cf)));

                $form_type = $field->form_type;

                // Also delete register form field
                $wpdb->query($wpdb->prepare("delete from " . $extras_table_name . " where site_htmlvar_name= %s ", array($field->htmlvar_name)));

                do_action('uwp_after_custom_field_deleted', $cf, $field->htmlvar_name, $form_type);

                return $field_id;
            } else
                return 0;
        } else
            return 0;
    }

    public function uwp_builder_extra_fields_smr($output,$result_str,$cf,$field_info){

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

    public function uwp_builder_extra_fields_datepicker($output,$result_str,$cf,$field_info){
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

    public function uwp_builder_extra_fields_file($output,$result_str,$cf,$field_info){
        ob_start();
        $allowed_file_types = uwp_allowed_mime_types();

        $extra_fields = isset($field_info->extra_fields) && $field_info->extra_fields != '' ? maybe_unserialize($field_info->extra_fields) : '';
        $uwp_file_types = !empty($extra_fields) && !empty($extra_fields['uwp_file_types']) ? $extra_fields['uwp_file_types'] : array('*');
        ?>
        <li>
            <label for="uwp_file_types" class="uwp-tooltip-wrap">
                <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Allowed file types :', 'uwp'); ?>
                <div class="uwp-tooltip">
                    <?php _e('Select file types to allowed for file uploading. (Select multiple file types by holding down "Ctrl" key.)', 'uwp');?>
                </div>
            </label>
            <div class="uwp-input-wrap">
                <select name="extra[uwp_file_types][]" id="uwp_file_types" multiple="multiple" style="height:100px;width:90%;">
                    <option value="*" <?php selected(true, in_array('*', $uwp_file_types));?>><?php _e('All types', 'uwp') ;?></option>
                    <?php foreach ( $allowed_file_types as $format => $types ) { ?>
                        <optgroup label="<?php echo esc_attr( wp_sprintf(__('%s formats', 'uwp'), __($format, 'uwp') ) ) ;?>">
                            <?php foreach ( $types as $ext => $type ) { ?>
                                <option value="<?php echo esc_attr($ext) ;?>" <?php selected(true, in_array($ext, $uwp_file_types));?>><?php echo '.' . $ext ;?></option>
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

    public function uwp_advance_admin_custom_fields($field_info, $cf) {
        $radio_id = (isset($field_info->htmlvar_name)) ? $field_info->htmlvar_name : rand(5, 500);
        $hide_register_field = (isset($cf['defaults']['is_register_field']) && $cf['defaults']['is_register_field']===false) ? "style='display:none;'" : '';

        $value = 0;
        if (isset($field_info->is_register_field)) {
            $value = (int)$field_info->is_register_field;
        } else if(isset($cf['defaults']['is_register_field']) && $cf['defaults']['is_register_field']) {
            $value = ($cf['defaults']['is_register_field']) ? 1 : 0;
        }

        //register only field
        $hide_register_only_field = (isset($cf['defaults']['is_register_only_field']) && $cf['defaults']['is_register_only_field']===false) ? "style='display:none;'" : '';
        $register_only_value = 0;
        if (isset($field_info->is_register_only_field)) {
            $register_only_value = (int)$field_info->is_register_only_field;
        } else if(isset($cf['defaults']['is_register_only_field']) && $cf['defaults']['is_register_only_field']) {
            $register_only_value = ($cf['defaults']['is_register_only_field']) ? 1 : 0;
        }
        ?>
        <li <?php echo $hide_register_field ;?>>
            <label for="cat_sort" class="uwp-tooltip-wrap">
                <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Include this field in register form:', 'uwp');?>
                <div class="uwp-tooltip">
                    <?php _e('Lets you use this field as register form field, set from register tab above.', 'uwp');?>
                </div>
            </label>

            <div class="uwp-input-wrap uwp-switch">
                <input type="radio" id="is_register_field_yes<?php echo $radio_id;?>" name="is_register_field" class="uwp-ri-enabled"  value="1" <?php checked(1, $value);?> />
                <label for="is_register_field_yes<?php echo $radio_id;?>" class="uwp-cb-enable"><span><?php _e('Yes', 'uwp'); ?></span></label>

                <input type="radio" id="is_register_field_no<?php echo $radio_id;?>" name="is_register_field" class="uwp-ri-disabled" value="0" <?php checked(0, $value);?> />
                <label for="is_register_field_no<?php echo $radio_id;?>" class="uwp-cb-disable"><span><?php _e('No', 'uwp'); ?></span></label>
            </div>
        </li>

        <li <?php echo $hide_register_only_field ;?>>
            <label for="cat_sort" class="uwp-tooltip-wrap">
                <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Include this field ONLY in register form:', 'uwp');?>
                <div class="uwp-tooltip">
                    <?php _e('Lets you use this field as register ONLY form field.', 'uwp');?>
                </div>
            </label>

            <div class="uwp-input-wrap uwp-switch">
                <input type="radio" id="is_register_only_field_yes<?php echo $radio_id;?>" name="is_register_only_field" class="uwp-ri-enabled"  value="1" <?php checked(1, $register_only_value);?> />
                <label for="is_register_only_field_yes<?php echo $radio_id;?>" class="uwp-cb-enable"><span><?php _e('Yes', 'uwp'); ?></span></label>

                <input type="radio" id="is_register_only_field_no<?php echo $radio_id;?>" name="is_register_only_field" class="uwp-ri-disabled" value="0" <?php checked(0, $register_only_value);?> />
                <label for="is_register_only_field_no<?php echo $radio_id;?>" class="uwp-cb-disable"><span><?php _e('No', 'uwp'); ?></span></label>
            </div>
        </li>
        <?php
    }

    public function return_empty_string() {
        return "";
    }

    public function uwp_register_available_fields_head($heading , $form_type)
    {
        switch($form_type)
        {
            case 'register':
                $heading =	__('Available regsiter form fields.', 'uwp');
                break;
        }
        return $heading;
    }


    public function uwp_register_available_fields_note($note , $form_type)
    {
        switch($form_type)
        {
            case 'register':
                $note =	__("Click on any box below to make it appear in register form. To make a field available here, go to account tab and expand any field from selected fields panel and tick the checkbox saying 'Include this field in register form'." , 'uwp');
                break;
        }
        return $note;
    }


    public function uwp_register_selected_fields_head($heading , $form_type)
    {
        switch($form_type)
        {
            case 'register':
                $heading = __('List of fields those will appear in register form.' , 'uwp');
                break;

        }
        return $heading;
    }


    public function uwp_register_selected_fields_note($note , $form_type)
    {
        switch($form_type)
        {
            case 'register':
                $note =	__('Click to expand and view field related settings. You may drag and drop to arrange fields order in register form.' , 'uwp');
                break;

        }
        return $note;
    }


    public function uwp_manage_register_available_fields($form_type)
    {
        switch ($form_type) {
            case 'register':
                $this->uwp_register_available_fields($form_type);
                break;
        }
    }

    public function uwp_manage_register_selected_fields($form_type)
    {
        switch ($form_type) {
            case 'register':
                $this->uwp_register_selected_fields($form_type);
                break;
        }
    }

    public function uwp_register_available_fields($form_type)
    {
        global $wpdb;

        $extras_table_name = $wpdb->prefix . 'uwp_form_extras';

        $existing_fields = $wpdb->get_results("select site_htmlvar_name from " . $extras_table_name . "     where form_type ='" . $form_type . "'");

        $existing_field_ids = array();
        if (!empty($existing_fields)) {
            foreach ($existing_fields as $existing_field) {
                $existing_field_ids[] = $existing_field->site_htmlvar_name;
            }
        }
        ?>
        <input type="hidden" name="form_type" id="form_type" value="<?php echo $form_type;?>"/>
        <input type="hidden" name="manage_field_type" class="manage_field_type" value="register">
        <ul>
        <?php

            $fields = $this->uwp_register_fields($form_type);

            if (!empty($fields)) {
                foreach ($fields as $field) {
                    $field = stripslashes_deep($field); // strip slashes


                    $fieldset_width = '';
                    if($field['field_type']=='fieldset') {
                        $fieldset_width = 'width:100%;';
                    }

                    $display = '';
                    if (in_array($field['htmlvar_name'], $existing_field_ids))
                        $display = 'display:none;';

                    $style = 'style="'.$display .$fieldset_width.'"';
                    ?>
                    <li <?php echo $style; ?> >

                        <a id="uwp-<?php echo $field['htmlvar_name'];?>"
                           class="uwp-draggable-form-items uwp-<?php echo $field['field_type'];?>"
                           href="javascript:void(0);" data-type="<?php echo $field['field_type'];?>">

                            <?php if (isset($field['field_icon']) && strpos($field['field_icon'], 'fa fa-') !== false) {
                                echo '<i class="'.$field['field_icon'].'" aria-hidden="true"></i>';
                            }elseif(isset($field['field_icon']) && $field['field_icon']){
                                echo '<b style="background-image: url("'.$field['field_icon'].'")"></b>';
                            }else{
                                echo '<i class="fa fa-cog" aria-hidden="true"></i>';
                            }?>

                            <?php echo $field['site_title'];?>


                        </a>
                    </li>


                    <?php
                }
            }
            ?>
        </ul>
        <?php
    }

    public function uwp_register_fields($form_type)
    {
        $fields = array();

        return apply_filters('uwp_register_fields', $fields, $form_type);
    }
    
    public function uwp_register_extra_fields($fields, $form_type)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'uwp_form_fields';
        $records = $wpdb->get_results($wpdb->prepare("select * from " . $table_name . " where form_type = %s and is_register_field=%s order by sort_order asc", array('account', '1')));

        foreach ($records as $row) {
            $field_type = $row->field_type;
            $fields[] = array(
                'field_type' => $field_type,
                'site_title' => $row->site_title,
                'htmlvar_name' => $row->htmlvar_name,
                'field_icon' => $row->field_icon
            );
        }
        return $fields;
    }

    public function uwp_register_selected_fields($form_type)
    {
        global $wpdb;
        $extras_table_name = $wpdb->prefix . 'uwp_form_extras';
        ?>
        <input type="hidden" name="manage_field_type" class="manage_field_type" value="register">
        <ul class="uwp_form_extras"><?php

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

                    $this->uwp_register_field_adminhtml($result_str, $field_ins_upd, $default);
                }
            }?>
        </ul>
        <?php
    }

    public function uwp_register_field_adminhtml($result_str, $field_ins_upd = '', $default = false, $request = array())
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'uwp_form_fields';
        $extras_table_name = $wpdb->prefix . 'uwp_form_extras';

        $cf = $result_str;
        if (!is_object($cf) && (is_int($cf) || ctype_digit($cf))) {
            $field_info = $wpdb->get_row($wpdb->prepare("select * from " . $extras_table_name . " where id= %d", array($cf)));
        } elseif(is_object($cf)) {
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
                    $field_site_name = __('Fieldset:', 'uwp') . ' ' . $account_field_info->site_title;
                } else {
                    $field_site_name = $account_field_info->site_title;
                }
            }
            $field_info = stripslashes_deep($field_info); // strip slashes
        }

        if (isset($request['form_type'])) {
            $form_type = esc_attr($request['form_type']);
        } else {
            $form_type = $field_info->form_type;
        }

        if (isset($request['is_default']) && $request['is_default'] != '') {
            $default = esc_attr($request['is_default']);
        } else {
            $default = $field_info->is_default;
        }

        if (isset($request['htmlvar_name']) && $request['htmlvar_name'] != '') {
            $htmlvar_name = esc_attr($request['htmlvar_name']);
        } else {
            $htmlvar_name = $field_info->site_htmlvar_name;
        }

        if(isset($htmlvar_name)){
            if(!is_object($field_info)){$field_info = new stdClass();}
            $field_info->field_icon = $wpdb->get_var(
                $wpdb->prepare("SELECT field_icon FROM " . $table_name . " WHERE htmlvar_name = %s", array($htmlvar_name))
            );
        }

        if (isset($field_info->field_icon) && strpos($field_info->field_icon, 'fa fa-') !== false) {
            $field_icon = '<i class="'.$field_info->field_icon.'" aria-hidden="true"></i>';
        }elseif(isset($field_info->field_icon) && $field_info->field_icon){
            $field_icon = '<b style="background-image: url("'.$field_info->field_icon.'")"></b>';
        }
        elseif(isset($field_info->field_type) && $field_info->field_type=='fieldset'){
            $field_icon = '<i class="fa fa-arrows-h" aria-hidden="true"></i>';
        }else{
            $field_icon = '<i class="fa fa-cog" aria-hidden="true"></i>';
        }

        ?>
        <li class="text" id="licontainer_<?php echo $result_str; ?>">
            <form><!-- we need to wrap in a fom so we can use radio buttons with same name -->
                <div class="title title<?php echo $result_str; ?> gt-fieldset"
                     title="<?php _e('Double Click to toggle and drag-drop to sort', 'uwp'); ?>"
                     ondblclick="show_hide_register('field_frm<?php echo $result_str; ?>')">
                    <?php

                    $nonce = wp_create_nonce('uwp_form_extras_nonce' . $result_str);
                    ?>

                    <?php if ($default): ?>
                        <div title="<?php _e('Default field, should not be removed.', 'uwp'); ?>" class="handlediv move uwp-default-remove"><i class="fa fa-times" aria-hidden="true"></i></div>
                    <?php else: ?>
                        <div title="<?php _e('Click to remove field', 'uwp'); ?>"
                             onclick="delete_register_field('<?php echo $result_str; ?>', '<?php echo $nonce; ?>','<?php echo $htmlvar_name; ?>')"
                             class="handlediv close"><i class="fa fa-times" aria-hidden="true"></i></div>
                    <?php endif;
                    echo $field_icon;
                    ?>
                    <b style="cursor:pointer;"
                       onclick="show_hide_register('field_frm<?php echo $result_str; ?>')"><?php echo $this->uwp_ucwords( ' ' . $field_site_name); ?></b>

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
                                <p>No options available</p>
                            </div>
                        </li>

                        <li>
                            <div class="uwp-input-wrap">

                                <input type="button" class="button button-primary" name="save" id="save"
                                       value="<?php esc_attr_e('Save', 'uwp'); ?>"
                                       onclick="save_register_field('<?php echo $result_str; ?>')"/>
                                <input type="button" name="delete" value="<?php esc_attr_e('Delete', 'uwp'); ?>"
                                       onclick="delete_register_field('<?php echo $result_str; ?>', '<?php echo $nonce; ?>','<?php echo $htmlvar_name ?>')"
                                       class="button"/>

                            </div>
                        </li>
                    </ul>

                </div>
            </form>
        </li>
    <?php
    }

    public function uwp_register_ajax_handler()
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

                $return = $this->uwp_form_extras_field_order($field_ids);

                if (is_array($return)) {
                    $return = json_encode($return);
                }

                echo $return;
            }

            /* ---- Show field form in admin ---- */
            if ($field_action == 'new') {
                $form_type = isset($_REQUEST['form_type']) ? sanitize_text_field($_REQUEST['form_type']) : '';
                $fields = $this->uwp_register_fields($form_type);


                $_REQUEST['site_field_id'] = isset($_REQUEST['field_id']) ? sanitize_text_field($_REQUEST['field_id']) : '';
                $_REQUEST['is_default'] = '0';

                if (!empty($fields)){
                    foreach ($fields as $val) {
                        $val = stripslashes_deep($val);

                        if ($val['htmlvar_name'] == $_REQUEST['htmlvar_name']) {
                            $_REQUEST['field_type'] = $val['field_type'];
                            $_REQUEST['site_title'] = $val['site_title'];
                        }
                    }
                }


                $htmlvar_name = isset($_REQUEST['htmlvar_name']) ? sanitize_text_field($_REQUEST['htmlvar_name']) : '';

                $this->uwp_register_field_adminhtml($htmlvar_name, $field_action, false, $_REQUEST);
            }

            /* ---- Delete field ---- */
            if ($field_id != '' && $field_action == 'delete' && isset($_REQUEST['_wpnonce'])) {
                if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'uwp_form_extras_nonce' . $field_id))
                    return;

                echo $this->uwp_register_field_delete($field_id);
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


                $return = $this->uwp_register_field_save($_REQUEST);

                if (is_int($return)) {
                    $lastid = $return;

                    $this->uwp_register_field_adminhtml($lastid, 'submit');
                } else {
                    echo $return;
                }
            }
        }
        die();
    }

    public function uwp_register_field_save($request_field = array())
    {
        global $wpdb;
        $extras_table_name = $wpdb->prefix . 'uwp_form_extras';

        $result_str = isset($request_field['field_id']) ? trim($request_field['field_id']) : '';

        $cf = trim($result_str, '_');

        /*-------- check duplicate validation --------*/

        $site_htmlvar_name = isset($request_field['htmlvar_name']) ? $request_field['htmlvar_name'] : '';
        $form_type = $request_field['form_type'];
        $field_type = $request_field['field_type'];

        $check_html_variable = $wpdb->get_var($wpdb->prepare("select site_htmlvar_name from " . $extras_table_name . " where id <> %d and site_htmlvar_name = %s and form_type = %s ",
            array($cf, $site_htmlvar_name, $form_type)));


        if (!$check_html_variable) {

            if ($cf != '') {

                $post_meta_info = $wpdb->get_row(
                    $wpdb->prepare(
                        "select * from " . $extras_table_name . " where id = %d",
                        array($cf)
                    )
                );

            }

            if ($form_type == '') $form_type = 'register';


            $site_htmlvar_name = $request_field['site_htmlvar_name'];
            $field_id = (isset($request_field['field_id']) && $request_field['field_id']) ? str_replace('new', '', $request_field['field_id']) : '';
            

            if (!empty($post_meta_info)) {

                $wpdb->query(
                    $wpdb->prepare(
                        "update " . $extras_table_name . " set
					form_type = %s,
					field_type = %s,
					site_htmlvar_name = %s,
					sort_order = %s,
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

            return (int)$lastid;


        } else {
            return 'HTML Variable Name should be a unique name';
        }
    }

    public function uwp_register_field_delete($field_id = '')
    {

        global $wpdb;
        $extras_table_name = $wpdb->prefix . 'uwp_form_extras';

        if ($field_id != '') {
            $cf = trim($field_id, '_');

            $wpdb->query($wpdb->prepare("delete from " . $extras_table_name . " where id= %d ", array($cf)));

            return $field_id;

        } else
            return 0;


    }

    public function uwp_form_extras_field_order($field_ids = array())
    {
        global $wpdb;
        $extras_table_name = $wpdb->prefix . 'uwp_form_extras';

        $count = 0;
        if (!empty($field_ids)):
            foreach ($field_ids as $id) {

                $cf = trim($id, '_');

                $wpdb->query(
                    $wpdb->prepare(
                        "update " . $extras_table_name . " set
															sort_order=%d
															where id= %d",
                        array($count, $cf)
                    )
                );
                $count++;
            }

            return $field_ids;
        else:
            return false;
        endif;
    }
    
    
    public function uwp_form_builder_dummy_fields() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'uwp_form_fields';
        $extras_table_name = $wpdb->prefix . 'uwp_form_extras';
        // This function is intended for testing purpose
        if (isset($_GET['uwp_dummy'])
            && is_admin()
            && is_user_logged_in()
            && current_user_can('manage_options')) {

            if ($_GET['uwp_dummy'] == 'create') {
                // Account
                $fields = $this->uwp_dummy_custom_fields();

                foreach ($fields as $field_index => $field) {
                    $this->uwp_admin_form_field_save($field);
                }

                // Register
                foreach ($fields as $field) {
                    $last_order = $wpdb->get_var("SELECT MAX(sort_order) as last_order FROM " . $extras_table_name);
                    $sort_order = (int)$last_order + 1;
                    $wpdb->query(
                        $wpdb->prepare(

                            "insert into " . $extras_table_name . " set
                        form_type = %s,
                        field_type = %s,
                        is_dummy = %s,
                        site_htmlvar_name = %s,
                        sort_order = %s",
                            array(
                                'register',
                                $field['field_type'],
                                $field['is_dummy'],
                                'uwp_account_'.$field['htmlvar_name'],
                                $sort_order
                            )
                        )
                    );
                }

                wp_redirect(admin_url('admin.php?page=uwp_form_builder'));
                exit;
            }


            if ($_GET['uwp_dummy'] == 'delete') {

                $wpdb->query($wpdb->prepare("delete from " . $table_name . " where is_dummy= %s ", array('1')));
                $wpdb->query($wpdb->prepare("delete from " . $extras_table_name . " where is_dummy= %s ", array('1')));
                wp_redirect(admin_url('admin.php?page=uwp_form_builder'));
                exit;
            }

        }


    }

    public function uwp_dummy_custom_fields(){

        $fields = array();

        //Fieldset
        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'fieldset',
            'site_title' => __('Dummy Fieldset', 'uwp'),
            'htmlvar_name' => 'dummy_fieldset',
            'default_value' => '',
            'option_values' => '',
            'is_dummy' => '1',
            'is_public' => '1',
            'is_active' => '1',
            'is_register_field' => '1',
        );

        //Text
        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'text',
            'site_title' => __('Dummy Text', 'uwp'),
            'htmlvar_name' => 'dummy_text',
            'default_value' => '',
            'option_values' => '',
            'is_dummy' => '1',
            'is_public' => '1',
            'is_active' => '1',
            'is_register_field' => '1',
        );

        //Textarea
        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'textarea',
            'site_title' => __('Dummy Textarea', 'uwp'),
            'htmlvar_name' => 'dummy_textarea',
            'default_value' => '',
            'option_values' => '',
            'is_dummy' => '1',
            'is_public' => '1',
            'is_active' => '1',
            'is_register_field' => '1',
        );

        //Checkbox
        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'checkbox',
            'site_title' => __('Dummy Checkbox', 'uwp'),
            'htmlvar_name' => 'dummy_checkbox',
            'default_value' => '',
            'option_values' => '',
            'is_dummy' => '1',
            'is_public' => '1',
            'is_active' => '1',
            'is_register_field' => '1',
        );

        //Radio
        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'radio',
            'site_title' => __('Dummy Radio', 'uwp'),
            'htmlvar_name' => 'dummy_radio',
            'default_value' => '',
            'option_values' => __('Value1,Value2,Value3' ,'uwp'),
            'is_dummy' => '1',
            'is_public' => '1',
            'is_active' => '1',
            'is_register_field' => '1',
        );

        //Select
        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'select',
            'site_title' => __('Dummy Select', 'uwp'),
            'htmlvar_name' => 'dummy_select',
            'default_value' => '',
            'option_values' => __('Select Option/,Value1,Value2,Value3' ,'uwp'),
            'is_dummy' => '1',
            'is_public' => '1',
            'is_active' => '1',
            'is_register_field' => '1',
        );

        //URL
        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'url',
            'site_title' => __('Dummy URL', 'uwp'),
            'htmlvar_name' => 'dummy_url',
            'default_value' => '',
            'option_values' => '',
            'is_dummy' => '1',
            'is_public' => '1',
            'is_active' => '1',
            'is_register_field' => '1',
        );

        //Date
        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'datepicker',
            'site_title' => __('Dummy Date', 'uwp'),
            'htmlvar_name' => 'dummy_datepicker',
            'default_value' => '',
            'option_values' => '',
            'is_dummy' => '1',
            'is_public' => '1',
            'is_active' => '1',
            'is_register_field' => '1',
            'extra' => array(
                'date_format' => 'F j, Y'
            )
        );

        //Time
        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'time',
            'site_title' => __('Dummy Time', 'uwp'),
            'htmlvar_name' => 'dummy_time',
            'default_value' => '',
            'option_values' => '',
            'is_dummy' => '1',
            'is_public' => '1',
            'is_active' => '1',
            'is_register_field' => '1',
        );

        //Phone
        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'phone',
            'site_title' => __('Dummy Phone', 'uwp'),
            'htmlvar_name' => 'dummy_phone',
            'default_value' => '',
            'option_values' => '',
            'is_dummy' => '1',
            'is_public' => '1',
            'is_active' => '1',
            'is_register_field' => '1',
        );

        //Email
        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'email',
            'site_title' => __('Dummy Email', 'uwp'),
            'htmlvar_name' => 'dummy_email',
            'default_value' => '',
            'option_values' => '',
            'is_dummy' => '1',
            'is_public' => '1',
            'is_active' => '1',
            'is_register_field' => '1',
        );

        //Multiselect
        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'multiselect',
            'site_title' => __('Dummy Multiselect', 'uwp'),
            'htmlvar_name' => 'dummy_multiselect',
            'default_value' => '',
            'option_values' => __('Select Option/,Value1,Value2,Value3' ,'uwp'),
            'is_dummy' => '1',
            'is_public' => '1',
            'is_active' => '1',
            'is_register_field' => '1',
        );

        //File Upload
        $fields[] = array(
            'form_type' => 'account',
            'field_type' => 'file',
            'site_title' => __('Dummy File', 'uwp'),
            'htmlvar_name' => 'dummy_file',
            'default_value' => '',
            'option_values' => '',
            'is_dummy' => '1',
            'is_public' => '1',
            'is_active' => '1',
            'is_register_field' => '1',
        );

        $fields = apply_filters('uwp_dummy_custom_fields', $fields);

        return  $fields;
    }

}