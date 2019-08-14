<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP Button_Group widget.
 *
 * @since 1.2.0
 */
class UWP_Button_Group_Widget extends WP_Super_Duper {

    /**
     * Register the profile social widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','button']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_button_group',
            'name'          => __('UWP > Button Group','userswp'),
            'widget_ops'    => array(
                'classname'   => 'uwp-button-group bsui',
                'description' => esc_html__('Displays a group of buttons from the custom fields.','userswp'),
            ),
            'arguments'     => array(
                'fields'  => array(
                    'title'       => __( 'Fields', 'userswp' ),
                    'desc'        => __( 'Enter a comma separated list of field keys. (leave empty for default social fields)', 'userswp' ),
                    'placeholder' => 'facebook,twitter,instagram,linkedin,flickr,github,youtube,wordpress',
                    'type'        => 'text',
                    'desc_tip'    => true,
                    'default'     => '',
                    'advanced'    => false
                ),
            )

        );


        parent::__construct( $options );
    }

    public function output( $args = array(), $widget_args = array(), $content = '' ) {

        global $wpdb;
        $user = uwp_get_displayed_user();

        if(empty($user)){
            return;
        }

        $defaults = array(
            'fields' => 'facebook,twitter,instagram,linkedin,flickr,github,youtube,wordpress',
        );

        $args = wp_parse_args( $args, $defaults );

        if(empty($args['fields'])){$args['fields'] = $defaults['fields'];}

//        $args = apply_filters( 'uwp_widget_profile_social_args', $args, $widget_args, $this );

        $fields = explode(",",$args['fields']);
        $fields = array_map('trim',$fields);
        $prepare_fields = implode(",",array_fill(0, count($fields), '%s'));

        $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
        $db_fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE ( form_type = 'register' OR form_type = 'account' ) AND is_public = '1' AND field_type = 'url' AND htmlvar_name IN($prepare_fields)",$fields));
        $field_info = array();
        if(!empty($db_fields)){
            foreach($db_fields as $db_field){
                $field_info[$db_field->htmlvar_name] = $db_field;
            }
        }
//        print_r( $fields );
//echo $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE ( form_type = 'register' OR form_type = 'account' ) AND field_type = 'url' AND htmlvar_name IN($prepare_fields)",$fields);
//        print_r($form_fields);exit;

        $user_meta = uwp_get_usermeta_row($user->ID);
        $buttons = array();
        foreach($fields as $field){
            if(!empty($user_meta->{$field}) && isset($field_info[$field])){
                $buttons[$field] = $field_info[$field];
                $buttons[$field]->url = esc_url($user_meta->{$field});
            }
        }

        if(empty($buttons)){
            return;
        }else{
            $args['buttons'] = $buttons;
        }

//        print_r($fields);
//        print_r($field_info);
//        print_r($user_meta);
//        print_r($valid_fields);
//        exit;

        global $uwp_widget_args;
        $uwp_widget_args = $args;

        $design_style = !empty($args['design_style']) ? esc_attr($args['design_style']) : uwp_get_option("design_style",'bootstrap');
        $template = $design_style ? $design_style."/button-group" : "";





        ob_start();

        if($template){
            uwp_locate_template($template);
        }


        $output = ob_get_clean();

        return $output;

    }

}