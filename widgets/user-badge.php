<?php

/**
 * UsersWP Badge widget.
 */
class UWP_User_Badge_Widget extends WP_Super_Duper {

	public $arguments;

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {

		$options = array(
			'textdomain'     => 'userswp',
			'block-icon'     => 'admin-site',
			'block-category' => 'widgets',
			'block-keywords' => "['badge','userswp']",
			'class_name'     => __CLASS__,
			'base_id'        => 'uwp_user_badge',
			'name'           => __( 'UWP > User Badge', 'userswp' ),
			'no_wrap'       => true,
			'widget_ops'     => array(
				'classname'     => 'uwp-user-badge',
				'description'   => esc_html__( 'Displays the user badge.', 'userswp' ),	// widget description
				'userswp'  => true,
			)
		);

		parent::__construct( $options );
	}

	/**
	 * Set widget arguments.
	 *
	 */
	public function set_arguments() {
		$arguments = array(
			'user_id'  	=> array(
				'type' => 'number',
				'title' => __('User ID:', 'userswp'),
				'desc' => __('Leave blank to use current user id.', 'userswp'),
				'placeholder' => 'Leave blank to use current user id.',
				'default' => '',
				'desc_tip' => true,
				'advanced' => false
			),
			'key'  => array(
				'type' => 'select',
				'title' => __('Field Key:', 'userswp'),
				'desc' => __('This is the custom field key.', 'userswp'),
				'placeholder' => '',
				'options' => $this->get_custom_field_keys(),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false
			),
			'condition'  => array(
				'type' => 'select',
				'title' => __('Field condition:', 'userswp'),
				'desc' => __('Select the custom field condition.', 'userswp'),
				'placeholder' => '',
				'options' => $this->get_badge_conditions(),
				'default' => 'is_equal',
				'desc_tip' => true,
				'advanced' => false
			),
			'search'  => array(
				'type' => 'text',
				'title' => __('Value to match:', 'userswp'),
				'desc' => __('Match this text with field value to display user badge.', 'userswp'),
				'placeholder' => '',
				'default' => '',
				'desc_tip' => true,
				'advanced' => false,
				'element_require' => '[%condition%]!="is_empty" && [%condition%]!="is_not_empty"'
			),
			'icon_class'  => array(
				'type' => 'text',
				'title' => __('Icon class:', 'userswp'),
				'desc' => __('You can show a font-awesome icon here by entering the icon class.', 'userswp'),
				'placeholder' => 'fas fa-award',
				'default' => '',
				'desc_tip' => true,
				'advanced' => true
			),
			'badge'  => array(
				'type' => 'text',
				'title' => __('Badge:', 'userswp'),
				'desc' => __('Badge text. Leave blank to show field title as a badge, or use %%input%% to use the input value of the field or %%profile_url%% for the user profile url, or the field key for any other info %%email%%.', 'userswp'),
				'placeholder' => '',
				'default' => '',
				'desc_tip' => true,
				'advanced' => false
			),
			'link'  => array(
				'type' => 'text',
				'title' => __('Link url:', 'userswp'),
				'desc' => __('Badge link url. You can use this to make the button link to something, %%input%% can be used here if a link or %%profile_url%% for the user profile url.', 'userswp'),
				'placeholder' => '',
				'default' => '',
				'desc_tip' => true,
				'advanced' => true
			),
			'new_window'  => array(
				'title' => __('Open link in new window:', 'userswp'),
				'desc' => __('This will open the link in a new window.', 'userswp'),
				'type' => 'checkbox',
				'desc_tip' => true,
				'value'  => '1',
				'default'  => 0,
				'advanced' => true
			),
			'bg_color'  => array(
				'type' => 'color',
				'title' => __('Badge background color:', 'userswp'),
				'desc' => __('Color for the badge background.', 'userswp'),
				'placeholder' => '',
				'default' => '#0073aa',
				'desc_tip' => true,
				'advanced' => true
			),
			'txt_color'  => array(
				'type' => 'color',
				'title' => __('Badge text color:', 'userswp'),
				'desc' => __('Color for the badge text.', 'userswp'),
				'placeholder' => '',
				'desc_tip' => true,
				'default'  => '#ffffff',
				'advanced' => true
			),
			'size'  => array(
				'type' => 'select',
				'title' => __('Badge size:', 'userswp'),
				'desc' => __('Size of the badge.', 'userswp'),
				'options' =>  array(
					"small" => __('Small', 'userswp'),
					 "" => __('Normal', 'userswp'),
					"medium" => __('Medium', 'userswp'),
					"large" => __('Large', 'userswp'),
					"extra-large" => __('Extra Large', 'userswp'),
				),
				'default' => '',
				'desc_tip' => true,
				'advanced' => true
			),
			'alignment'  => array(
				'type' => 'select',
				'title' => __('Alignment:', 'userswp'),
				'desc' => __('How the item should be positioned on the page.', 'userswp'),
				'options'   =>  array(
					"" => __('None', 'userswp'),
					"left" => __('Left', 'userswp'),
					"center" => __('Center', 'userswp'),
					"right" => __('Right', 'userswp'),
				),
				'desc_tip' => true,
				'advanced' => true
			),
			'list_hide'  => array(
				'title' => __('Hide item on view:', 'userswp'),
				'desc' => __('You can set at what view the item will become hidden.', 'userswp'),
				'type' => 'select',
				'options'   =>  array(
					"" => __('None', 'userswp'),
					"2" => __('Grid view 2', 'userswp'),
					"3" => __('Grid view 3', 'userswp'),
					"4" => __('Grid view 4', 'userswp'),
					"5" => __('Grid view 5', 'userswp'),
				),
				'desc_tip' => true,
				'advanced' => true
			),
			'list_hide_secondary'  => array(
				'title' => __('Hide secondary info on view', 'userswp'),
				'desc' => __('You can set at what view the secondary info such as label will become hidden.', 'userswp'),
				'type' => 'select',
				'options'   =>  array(
					"" => __('None', 'userswp'),
					"2" => __('Grid view 2', 'userswp'),
					"3" => __('Grid view 3', 'userswp'),
					"4" => __('Grid view 4', 'userswp'),
					"5" => __('Grid view 5', 'userswp'),
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
		);

		return $arguments;
	}


	/**
	 * Outputs the user badge on the front-end.
	 *
	 * @param array $args
	 * @param array $widget_args
	 * @param string $content
	 *
	 * @return string
	 */
	public function output( $args = array(), $widget_args = array(), $content = '' ) {
		global $post;

		if('post_author' == $args['user_id'] && $post instanceof WP_Post){
			$user = get_userdata($post->post_author);
			$args['user_id'] = $post->post_author;
		} else if(isset($args['user_id']) && (int)$args['user_id'] > 0){
			$user = get_userdata($args['user_id']);
		} else {
			$user = uwp_get_displayed_user();
		}

		if(empty($args['user_id']) && !empty($user->ID)){
			$args['user_id'] = $user->ID;
		}

		if(!$user){
			return '';
		}

		$output = uwp_get_user_badge( $args );

		return $output;
	}

	/**
	 * Gets an array of custom field keys for user badge.
	 *
	 * @return array
	 */
	public function get_custom_field_keys(){
		global $wpdb;
		$table_name = uwp_get_table_prefix() . 'uwp_form_fields';

		$fields = $wpdb->get_results("SELECT htmlvar_name, site_title FROM " . $table_name . " WHERE form_type = 'account'");

		$keys = array();
		if ( !empty( $fields ) ) {
			foreach( $fields as $field ) {
				if ( apply_filters( 'uwp_badge_field_skip_key', false, $field ) ) {
					continue;
				}
				$keys[ $field->htmlvar_name ] = $field->htmlvar_name . ' ( ' . __( $field->site_title, 'userswp' ) . ' )';
			}
		}

		return apply_filters( 'uwp_badge_field_keys', $keys );
	}
	
	/**
	 * Gets an array of badge field conditions.
	 *
	 * @return array
	 */
	public function get_badge_conditions(){
		$conditions = array(
			'is_equal' => __( 'is equal', 'userswp' ),
			'is_not_equal' => __( 'is not equal', 'userswp' ),
			'is_greater_than' => __( 'is greater than', 'userswp' ),
			'is_less_than' => __( 'is less than', 'userswp' ),
			'is_empty' => __( 'is empty', 'userswp' ),
			'is_not_empty' => __( 'is not empty', 'userswp' ),
		);

		return apply_filters( 'uwp_badge_conditions', $conditions );
	}
	
}

