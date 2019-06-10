<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP user meta widget.
 *
 * @since 1.1.2
 */
class UWP_User_Meta_Widget extends WP_Super_Duper {

    /**
     * Register the profile user title widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','usermeta']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_user_meta',
            'name'          => __('UWP > User Meta','userswp'),
            'no_wrap'       => true,
            'widget_ops'    => array(
                'classname'   => 'uwp-user-meta',
                'description' => esc_html__('Displays user meta.','userswp'),
            ),
            'arguments'     => array(
                'key'  => array(
                    'title' => __('Key:', 'userswp'),
                    'desc' => __('This is the custom field key.', 'userswp'),
                    'type' => 'select',
                    'options'   => $this->get_custom_field_keys(),
                    'desc_tip' => true,
                    'default'  => '',
                    'advanced' => false
                ),
                'user_id'  => array(
                    'title' => __('User ID:', 'userswp'),
                    'desc' => __('Leave blank to use current user ID. For profile page it will take displayed user ID', 'userswp'),
                    'type' => 'number',
                    'desc_tip' => true,
                    'default'  => '',
                    'advanced' => true
                ),
                'show'  => array(
                    'title' => __('Show:', 'userswp'),
                    'desc' => __('What part of the post meta to show.', 'userswp'),
                    'type' => 'select',
                    'options'   =>  array(
                        "" => __('Icon + Label + Value', 'userswp'),
                        "icon-value" => __('Icon + Value', 'userswp'),
                        "label-value" => __('Label + Value', 'userswp'),
                        "label" => __('Label', 'userswp'),
                        "value" => __('Value', 'userswp'),
                        "value-strip" => __('Value (strip_tags)', 'userswp'),
                    ),
                    'desc_tip' => true,
                    'advanced' => true
                ),
                'css_class'  => array(
                    'type' => 'text',
                    'title' => __('Extra class:', 'userswp'),
                    'desc' => __('Give the wrapper an extra class so you can style things as you want.', 'userswp'),
                    'placeholder' => '',
                    'default' => '',
                    'desc_tip' => true,
                    'advanced' => true,
                ),
            )

        );


        parent::__construct( $options );
    }

    /**
     * Gets an array of custom field keys.
     *
     * @return array
     */
    public function get_custom_field_keys(){
        global $wpdb;
        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
        $fields = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE form_type = 'account' ORDER BY sort_order ASC");

        $keys = array();
        $keys[] = __('Select Key','userswp');
        if(!empty($fields)){
            foreach($fields as $field){
                $key = str_replace('uwp_account_', '', $field->htmlvar_name);
                $keys[$key] = $key;
            }
        }

        return $keys;

    }

    public function output( $args = array(), $widget_args = array(), $content = '' ) {

        global $wpdb;
        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';

        $user = uwp_get_displayed_user();

        $defaults = array(
            'user_id'  => '',
            'key'      => '',
            'show'     => '',
            'css_class'     => '',
        );

        $args = wp_parse_args( $args, $defaults );

        $args = apply_filters( 'uwp_widget_user_meta_args', $args, $widget_args, $this );

        if(empty($args['user_id']) && !empty($user->ID)){
            $args['user_id'] = $user->ID;
        }

        if(empty($args['key']) || empty($args['user_id'])){
            return '';
        }

        $fields = $wpdb->get_results("SELECT site_title,field_icon,htmlvar_name,field_type FROM " . $table_name . " WHERE form_type = 'account' AND htmlvar_name = 'uwp_account_".$args['key']."'");

        if(!$fields){
            return '';
        }

        $field = $fields[0];
        $value = $output = $label = '';

        if ($field->field_icon != '') {
            $icon = uwp_get_field_icon($field->field_icon);
        } else {
            $field_icon = uwp_field_type_to_fa_icon($field->field_type);
            if ($field_icon) {
                $icon = '<i class="'.$field_icon.'"></i>';
            } else {
                $icon = '<i class="fas fa-user"></i>';
            }
        }

        if(!empty($args['css_class']) && isset($field->css_class)){
            $css_class = $field->css_class ." ". $args['css_class'];
        } else {
            $css_class = $args['css_class'];
        }

        $privacy = uwp_get_usermeta($args['user_id'], '_privacy');

        if (isset($privacy) && 'no' == $privacy) {
            return $value;
        }

        $obj = new UsersWP_Profile();
        $value = $obj->uwp_get_field_value($field, $user);

        switch ($args['show']){
            case 'icon-value':
                $output = $icon.$value;
                break;
            case 'label-value':
                $output = '<div class="uwp-profile-extra-key">'. $field->site_title . '<span class="uwp-profile-extra-sep">:</span></div><div class="uwp-profile-extra-value">'.$value.'</div>';
                break;
            case 'label':
                $output = '<div class="uwp-profile-extra-key">'. $field->site_title . '<span class="uwp-profile-extra-sep">:</span></div>';
                break;
            case 'value':
                $output = '<div class="uwp-profile-extra-value">'.$value.'</div>';
                break;
            case 'value-strip':
                $output = '<div class="uwp-profile-extra-value">'.strip_tags($value).'</div>';
                break;
            default:
                $output = '<div class="uwp-profile-extra-key">'. $icon . $field->site_title . '<span class="uwp-profile-extra-sep">:</span></div><div class="uwp-profile-extra-value">'. $value. '</div>';
        }

        //wrap output in a div
        $output = '<div class="uwp-profile-extra-wrap '.$css_class.'">'.$output.'</div>';

        return $output;

    }

}