<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP author box widget.
 *
 * @since 1.0.22
 */
class UWP_Author_Box_Widget extends WP_Super_Duper {
    public $arguments;

    /**
     * Register the profile widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','author','authorbox']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_author_box',
            'name'          => __('UWP > Author Box','userswp'),
            'widget_ops'    => array(
                'classname'   => 'uwp_widgets uwp_widget_author_box bsui',
                'description' => esc_html__('Displays author box.','userswp'),
            ),
        );


        parent::__construct( $options );
    }

    /**
     * Set widget arguments.
     *
     */
    public function set_arguments()
    {
        $arguments = array(
            'title'  => array(
                'title'       => __('Widget title', 'userswp'),
                'desc'        => __('Enter widget title.', 'userswp'),
                'type'        => 'text',
                'desc_tip'    => true,
                'default'     => '',
                'advanced'    => false
            )
        );

        // margins mobile
        $arguments['mt'] = sd_get_margin_input('mt', array('device_type' => 'Mobile'));
        $arguments['mr'] = sd_get_margin_input('mr', array('device_type' => 'Mobile'));
        $arguments['mb'] = sd_get_margin_input('mb', array('device_type' => 'Mobile'));
        $arguments['ml'] = sd_get_margin_input('ml', array('device_type' => 'Mobile'));

        // margins tablet
        $arguments['mt_md'] = sd_get_margin_input('mt', array('device_type' => 'Tablet'));
        $arguments['mr_md'] = sd_get_margin_input('mr', array('device_type' => 'Tablet'));
        $arguments['mb_md'] = sd_get_margin_input('mb', array('device_type' => 'Tablet'));
        $arguments['ml_md'] = sd_get_margin_input('ml', array('device_type' => 'Tablet'));

        // margins desktop
        $arguments['mt_lg'] = sd_get_margin_input('mt', array('device_type' => 'Desktop'));
        $arguments['mr_lg'] = sd_get_margin_input('mr', array('device_type' => 'Desktop'));
        $arguments['mb_lg'] = sd_get_margin_input('mb', array('device_type' => 'Desktop'));
        $arguments['ml_lg'] = sd_get_margin_input('ml', array('device_type' => 'Desktop'));

        // padding
        $arguments['pt'] = sd_get_padding_input('pt', array('device_type' => 'Mobile'));
        $arguments['pr'] = sd_get_padding_input('pr', array('device_type' => 'Mobile'));
        $arguments['pb'] = sd_get_padding_input('pb', array('device_type' => 'Mobile'));
        $arguments['pl'] = sd_get_padding_input('pl', array('device_type' => 'Mobile'));

        // padding tablet
        $arguments['pt_md'] = sd_get_padding_input('pt', array('device_type' => 'Tablet'));
        $arguments['pr_md'] = sd_get_padding_input('pr', array('device_type' => 'Tablet'));
        $arguments['pb_md'] = sd_get_padding_input('pb', array('device_type' => 'Tablet'));
        $arguments['pl_md'] = sd_get_padding_input('pl', array('device_type' => 'Tablet'));

        // padding desktop
        $arguments['pt_lg'] = sd_get_padding_input('pt', array('device_type' => 'Desktop'));
        $arguments['pr_lg'] = sd_get_padding_input('pr', array('device_type' => 'Desktop'));
        $arguments['pb_lg'] = sd_get_padding_input('pb', array('device_type' => 'Desktop'));
        $arguments['pl_lg'] = sd_get_padding_input('pl', array('device_type' => 'Desktop'));

        // border
        $arguments['border']       = sd_get_border_input('border');
        $arguments['rounded']      = sd_get_border_input('rounded');
        $arguments['rounded_size'] = sd_get_border_input('rounded_size');

        // shadow
        $arguments['shadow'] = sd_get_shadow_input('shadow');

        return apply_filters('uwp_author_box_widget_arguments', $arguments);
    }

	/**
	 * The Super block output function.
	 *
	 * @param array $args
	 * @param array $widget_args
	 * @param string $content
	 *
	 * @return mixed|string|bool
	 */
    public function output( $args = array(), $widget_args = array(), $content = '' ) {

        global $post, $wpdb;

        // Ensure we have an array.
		if ( ! is_array( $args ) ) {
			$args = array();
		}

        if(empty($post->ID)){
            return '';
        }

        ob_start();

        if(!wp_style_is('uwp-authorbox')){
            wp_enqueue_style( 'uwp-authorbox' );
        }

        $design_style = uwp_get_option("design_style",'bootstrap');
        if( $design_style =='bootstrap'){
            $output = uwp_get_option('author_box_content_bootstrap');
            if(!$output){$output = UsersWP_Defaults::author_box_content_bootstrap( $args );}
        }else{
            $output = uwp_get_option('author_box_content');
            if(!$output){$output = UsersWP_Defaults::author_box_content();}
        }

        $output = apply_filters('uwp_author_box_pre_output', $output, $args);

        $output = do_shortcode($output );

        $author_id = $post->post_author;
        $author_link = uwp_build_profile_tab_url($author_id);
        $user = get_user_by('id', $author_id);
        $author_name = esc_attr( $user->display_name );
        $author_display_name = uwp_get_username($user->ID);
        $author_bio = get_user_meta($author_id, 'description', true);
	    $limit_words = apply_filters('uwp_author_bio_content_limit', uwp_get_option('author_box_bio_limit', 200));
	    $author_bio = wp_trim_words( $author_bio, $limit_words, '...' );
        $meta_table = get_usermeta_table_prefix() . 'uwp_usermeta';
        $user_meta = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$meta_table} WHERE user_id = %d", $author_id), ARRAY_A);

        $user_meta = apply_filters('uwp_author_box_user_meta_fields', $user_meta, $args);
        $avatar_size = apply_filters('uwp_author_box_avatar_size', 100, $args);

        $replace_array = array(
            '[#post_id#]' => absint( $post->ID ),
            '[#post_modified#]' => esc_attr( $post->post_modified ),
            '[#post_date#]' => esc_attr( $post->post_date ),
            '[#author_id#]' => absint( $post->post_author ),
            '[#author_name#]' => esc_attr( $author_name ),
            '[#author_display_name#]' => $author_display_name,
            '[#author_link#]' => esc_url( $author_link ),
            '[#author_bio#]' => esc_textarea( $author_bio ),
            '[#author_image#]' => get_avatar($post->post_author, $avatar_size),
            '[#author_image_url#]' => get_avatar_url($post->post_author, $avatar_size),
            '[#author_nicename#]' => esc_attr( $user->user_nicename ),
            '[#author_registered#]' => esc_attr( $user->user_registered ),
            '[#author_website#]' => esc_url( $user->user_url ),
        );

        if( !empty( $user_meta ) && '' !== $user_meta ) {
            foreach ( $user_meta as $meta_key => $meta_val ) {

                if(in_array($meta_key, array('avatar_thumb', 'banner_thumb')) && !empty($meta_val)){
                    $uploads = wp_upload_dir();
                    $upload_url = $uploads['baseurl'];
                    $meta_val = esc_url( $upload_url.$meta_val );
                }

                if( in_array($meta_key, array('user_privacy')) ) {
                    continue;
                }

                $replace_array['[#'.$meta_key.'#]'] = esc_attr( $meta_val );
            }
        }

        $replace_array = apply_filters('uwp_author_box_replace_array', $replace_array);

        foreach ( $replace_array as $key => $value ) {
            $value = apply_filters('uwp_author_box_'.$key.'_value', $value, $post);
            $output = str_replace( $key, $value, $output );
        }

        echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        $output = ob_get_clean();

        return apply_filters('uwp_author_box_output', $output, $args);
    }

}