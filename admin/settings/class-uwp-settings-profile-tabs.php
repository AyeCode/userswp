<?php
/**
 * UsersWP Tabs in form builder
 *
 * @author      AyeCode
 * @category    Admin
 * @package     userswp/Admin
 * @version     1.0.24
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'UsersWP_Settings_Profile_Tabs', false ) ) {

    /**
     * UsersWP_Settings_Email.
     */
    class UsersWP_Settings_Profile_Tabs {

        public function __construct() {

            add_filter( 'uwp_form_builder_tabs_array', array( $this, 'form_builder_tab_items' ), 99 );
	        add_filter('uwp_form_builder_available_fields_head', array( $this,  'tabs_available_fields_head' ), 10, 2);
	        add_filter('uwp_form_builder_available_fields_note', array( $this,  'tabs_available_fields_note' ), 10, 2);
	        add_filter('uwp_form_builder_selected_fields_head', array( $this,  'tabs_selected_fields_head' ), 10, 2);
	        add_filter('uwp_form_builder_selected_fields_note', array( $this,  'tabs_selected_fields_note' ), 10, 2);
	        add_action('uwp_manage_available_fields', array( $this,  'manage_tabs_available_fields' ), 10, 1);
	        add_action('uwp_manage_available_fields_predefined', array( $this,  'manage_tabs_predefined_fields' ), 10, 1);
	        add_action('uwp_manage_available_fields_custom', array( $this,  'manage_available_fields_custom' ), 10, 1);
	        add_action('uwp_manage_selected_fields', array( $this,  'manage_tabs_selected_fields' ), 10, 1);
	        add_action('wp_ajax_uwp_ajax_profile_tabs_action', array( $this,  'tabs_ajax_handler'));

        }

        /**
         * Add a tab to form builder
         *
         * @since       1.0.0
         * @package     userswp
         *
         * @param       array   $tabs   Tabs
         *
         * @return      array   $tabs
         */
	    public function form_builder_tab_items($tabs)
	    {
		    $tabs['profile-tabs'] = __('Profile Tabs', 'userswp');
		    return $tabs;

	    }

	    /**
         * Add a tab to form builder
         *
         * @since       2.0.0
         * @package     userswp
         *
         * @param       string   $heading   Heading
         * @param       string   $form_type   Form type
         *
         * @return      string
         */
	    public function tabs_available_fields_head($heading, $form_type)
	    {
		    switch ($form_type) {
			    case 'profile-tabs':
				    $heading = __('Available profile tabs.', 'userswp');
				    break;
		    }

		    return $heading;
	    }

	    /**
         * Add a note above available fields.
         *
         * @since       2.0.0
         * @package     userswp
         *
         * @param       string   $note   Note to display
         * @param       string   $form_type   Form type
         *
         * @return      string
         */
	    public function tabs_available_fields_note($note, $form_type)
	    {
		    switch ($form_type) {
			    case 'profile-tabs':
				    $note = __("Fields that can be added to the profile tabs.", 'userswp');
				    break;
		    }

		    return $note;
	    }

        /**
         * Heading for the selected fields
         *
         * @since       2.0.0
         * @package     userswp
         *
         * @param       string   $heading   Heading to display
         * @param       string   $form_type   Form type
         *
         * @return      string
         */
	    public function tabs_selected_fields_head($heading, $form_type)
	    {
		    switch ($form_type) {
			    case 'profile-tabs':
				    $heading = __('Profile Tabs', 'userswp');
				    break;
		    }

		    return $heading;
	    }

	    /**
         * Add a note above selected fields.
         *
         * @since       2.0.0
         * @package     userswp
         *
         * @param       string   $note   Note to display
         * @param       string   $form_type   Form type
         *
         * @return      string
         */
	    public function tabs_selected_fields_note($note, $form_type)
	    {
		    switch ($form_type) {
			    case 'profile-tabs':
				    $note = __('Choose the items from left panel to create the profile tabs.', 'userswp');
				    break;
		    }
		    return $note;
	    }

	    /**
         * Display available fields
         *
         * @since       2.0.0
         * @package     userswp
         *
         * @param       string   $form_type   Form type
         *
         */
	    public function manage_tabs_available_fields($form_type)
	    {
	        if('profile-tabs' == $form_type){
                $this->tabs_available_fields($form_type);
	        }
	    }

	    /**
         * Display predefined fields
         *
         * @since       2.0.0
         * @package     userswp
         *
         * @param       string   $form_type   Form type
         *
         */
	    public function manage_tabs_predefined_fields($form_type)
	    {
		    if('profile-tabs' == $form_type){
                $this->tabs_predefined_fields($form_type);
		    }
	    }

	    /**
         * Display custom fields
         *
         * @since       2.0.0
         * @package     userswp
         *
         * @param       string   $form_type   Form type
         *
         */
	    public function manage_available_fields_custom($form_type){
            if('profile-tabs' == $form_type){
                $this->tabs_custom_fields($form_type);
		    }
	    }

	    /**
         * Display selected fields
         *
         * @since       2.0.0
         * @package     userswp
         *
         * @param       string   $form_type   Form type
         *
         */
	    public function manage_tabs_selected_fields($form_type)
	    {
		    if('profile-tabs' == $form_type){
                $this->tabs_selected_fields($form_type);
		    }
	    }

	    /**
         * Display custom fields
         *
         * @since       2.0.0
         * @package     userswp
         *
         * @param       string   $form_type   Form type
         *
         */
	    public function tabs_custom_fields($form_type){
            // insert the required code for the SD button.
			$js_insert_function = $this->insert_shortcode_function();
			WP_Super_Duper::shortcode_insert_button('',$js_insert_function);

			$fields = array();
			$fields[] = array(
				'tab_type'   => 'shortcode',
				'tab_name'   => __('Shortcode','userswp'),
				'tab_icon'   => 'fas fa-cubes',
				'tab_key'    => '',
				'tab_content'=> ''

			);

			?>
			<input type="hidden" name="form_type" id="form_type" value="<?php echo esc_attr($form_type); ?>"/>
		    <input type="hidden" name="manage_field_type" class="manage_field_type" value="profile_tabs">
		    <ul>
			<?php

			foreach ($fields as $id => $field) {

                ?>
                <li>
                    <a id="uwp-<?php echo esc_attr($field['tab_key']); ?>"
                       data-field-custom-type="predefined"
                       class="uwp-draggable-form-items"
                       data-tab_layout="profile"
                       data-field-type-key="uwp-<?php echo esc_attr($field['tab_key']); ?>"
                       data-tab_type="<?php echo isset($field['tab_type']) ? esc_attr($field['tab_type']) : ''; ?>"
                       data-tab_name="<?php echo isset($field['tab_name']) ? esc_attr($field['tab_name']) : ''; ?>"
                       data-tab_icon="<?php echo isset($field['tab_icon']) ? esc_attr($field['tab_icon']) : ''; ?>"
                       data-tab_key="<?php echo isset($field['tab_key']) ? esc_attr($field['tab_key']) : ''; ?>"
                       data-tab_level="<?php echo isset($field['tab_level']) ? esc_attr($field['tab_level']) : 0; ?>"
                       data-tab_parent="<?php echo isset($field['tab_parent']) ? esc_attr($field['tab_parent']) : ''; ?>"
                       data-tab_content="<?php echo isset($field['tab_content']) ? esc_attr($field['tab_content']) : ''; ?>"
                       data-tab_privacy="<?php echo isset($field['tab_privacy']) ? esc_attr($field['tab_privacy']) : 0; ?>"
                       data-user_decided="<?php echo isset($field['user_decided']) ? esc_attr($field['user_decided']) : 0; ?>"
                       href="javascript:void(0);">

                        <?php
                        $icon = $field['tab_icon'];
                        if ( uwp_is_fa_icon( $icon ) ) {
                            $tab_icon = '<i class="' . esc_attr( $icon ) . '" aria-hidden="true"></i>';
                        } elseif ( uwp_is_icon_url( $icon ) ) {
                            $tab_icon = '<b style="background-image: url("' . esc_url($icon) . '")"></b>';
                        } else {
                            $tab_icon = '<i class="fas fa-cog" aria-hidden="true"></i>';
                        }
                        ?>
                        <?php echo $tab_icon; ?>
                        <?php echo esc_attr($field['tab_name']); ?>
                    </a>
                </li>
                <?php
            }
            ?>
            </ul>
            <?php
	    }

	    /**
         * Insert the shortcode function.
         *
         * @return string
         */
		public function insert_shortcode_function(){
			ob_start();
			?>
			function sd_insert_shortcode(){
				$shortcode = jQuery('#sd-shortcode-output').val();
				if($shortcode){
					jQuery('.uwp-tab-settings-open textarea').val($shortcode);
					tb_remove();
				}
			}
			<?php
			return ob_get_clean();

		}

		/**
         * Display available tabs fields
         *
         * @since       2.0.0
         * @package     userswp
         *
         * @param       string   $form_type   Form type
         *
         */
	    public function tabs_available_fields($form_type){
		    global $wpdb;

		    $form_id = ! empty( $_REQUEST['form'] ) ? (int) $_REQUEST['form'] : 1;

		    $table_name = uwp_get_table_prefix() . 'uwp_profile_tabs';

		    $existing_fields = $wpdb->get_results("select tab_key from " . $table_name . " where form_type ='" . $form_type . "'");

		    $existing_field_ids = array();
		    if (!empty($existing_fields)) {
			    foreach ($existing_fields as $existing_field) {
				    $existing_field_ids[] = $existing_field->tab_key;
			    }
		    }

		    ?>
		    <input type="hidden" name="form_type" id="form_type" value="<?php echo esc_attr($form_type); ?>"/>
		    <input type="hidden" name="manage_field_type" class="manage_field_type" value="profile_tabs">
		    <ul>
			    <?php

			    $fields = $this->tabs_fields($form_type, $form_id);

			    if (!empty($fields)) {
				    foreach ($fields as $field) {
					    $field = stripslashes_deep($field); // strip slashes

					    $fieldset_width = '';
					    if ($field['tab_type'] == 'fieldset') {
						    $fieldset_width = 'width:100%;';
					    }

					    $display = '';
					    if (in_array($field['tab_key'], $existing_field_ids))
						    $display = 'display:none;';

					    $style = 'style="' . $display . $fieldset_width . '"';

					    $icon = $field['tab_icon'];
					    if ( uwp_is_fa_icon( $icon ) ) {
                            $tab_icon = '<i class="' . esc_attr( $icon ) . '" aria-hidden="true"></i>';
                        } elseif ( uwp_is_icon_url( $icon ) ) {
                            $tab_icon = '<b style="background-image: url("' . esc_url($icon) . '")"></b>';
                        } else {
                            $tab_icon = '<i class="fas fa-cog" aria-hidden="true"></i>';
                        }
					    ?>
					    <li <?php echo esc_attr($style); ?> >
						    <a id="uwp-<?php echo esc_attr($field['tab_key']); ?>"
                                data-field-custom-type="custom"
                               class="uwp-draggable-form-items"
                               data-tab_layout="profile"
                               data-tab_type="<?php echo isset($field['tab_type']) ? esc_attr($field['tab_type']) : ''; ?>"
                               data-tab_name="<?php echo isset($field['tab_name']) ? esc_attr($field['tab_name']) : ''; ?>"
                               data-tab_icon="<?php echo isset($field['tab_icon']) ? esc_attr($field['tab_icon']) : ''; ?>"
                               data-tab_key="<?php echo isset($field['tab_key']) ? esc_attr($field['tab_key']) : ''; ?>"
                               data-tab_level="<?php echo isset($field['tab_level']) ? esc_attr($field['tab_level']) : 0; ?>"
                               data-tab_parent="<?php echo isset($field['tab_parent']) ? esc_attr($field['tab_parent']) : ''; ?>"
                               data-tab_content="<?php echo isset($field['tab_content']) ? esc_attr($field['tab_content']) : ''; ?>"
                               data-tab_privacy="<?php echo isset($field['tab_privacy']) ? esc_attr($field['tab_privacy']) : 0; ?>"
                               data-user_decided="<?php echo isset($field['user_decided']) ? esc_attr($field['user_decided']) : 0; ?>"
                               href="javascript:void(0);">
                                <?php echo $tab_icon; ?>
							    <?php echo esc_attr($field['tab_name']); ?>
						    </a>
					    </li>
					    <?php
				    }
			    }
			    ?>
		    </ul>
		    <?php
	    }

	    /**
         * Display predefined tabs fields
         *
         * @since       2.0.0
         * @package     userswp
         *
         * @param       string   $form_type   Form type
         *
         */
	    public function tabs_predefined_fields($form_type){
		    $fields = array();

			$fields[] = array(
				'tab_type'   => 'standard',
				'tab_name'   => __('More Info','userswp'),
				'tab_icon'   => 'fas fa-info-circle',
				'tab_key'    => 'more_info',
				'tab_content'=> ''
			);

			$fields[] = array(
				'tab_type'   => 'standard',
				'tab_name'   => __('Posts','userswp'),
				'tab_icon'   => 'fas fa-info-circle',
				'tab_key'    => 'posts',
				'tab_content'=> ''
			);

			$fields[] = array(
				'tab_type'   => 'standard',
				'tab_name'   => __('Comments','userswp'),
				'tab_icon'   => 'fas fa-comments',
				'tab_key'    => 'comments',
				'tab_content'=> ''
			);

			/*$fields[] = array(
				'tab_type'   => 'standard',
				'tab_name'   => __('User Comments','userswp'),
				'tab_icon'   => 'fas fa-comments',
				'tab_key'    => 'user-comments',
				'tab_content'=> ''
			);*/

		    $fields = apply_filters('uwp_profile_tabs_predefined_fields', $fields, $form_type);

		    ?>
            <input type="hidden" name="form_type" id="form_type" value="<?php echo esc_attr($form_type); ?>"/>
            <input type="hidden" name="manage_field_type" class="manage_field_type" value="profile_tabs">
            <?php
            if (!empty($fields)) {
            ?>
                <ul>
                <?php
                foreach ($fields as $id => $field) {

                    ?>
                    <li>
                        <a id="uwp-<?php echo esc_attr($field['tab_key']); ?>"
                           data-field-custom-type="predefined"
                           class="uwp-draggable-form-items"
                           data-tab_layout="profile"
                           data-field-type-key="uwp-<?php echo esc_attr($field['tab_key']); ?>"
						   data-tab_type="<?php echo isset($field['tab_type']) ? esc_attr($field['tab_type']) : ''; ?>"
						   data-tab_name="<?php echo isset($field['tab_name']) ? esc_attr($field['tab_name']) : ''; ?>"
						   data-tab_icon="<?php echo isset($field['tab_icon']) ? esc_attr($field['tab_icon']) : ''; ?>"
						   data-tab_key="<?php echo isset($field['tab_key']) ? esc_attr($field['tab_key']) : ''; ?>"
						   data-tab_level="<?php echo isset($field['tab_level']) ? esc_attr($field['tab_level']) : 0; ?>"
						   data-tab_parent="<?php echo isset($field['tab_parent']) ? esc_attr($field['tab_parent']) : ''; ?>"
						   data-tab_content="<?php echo isset($field['tab_content']) ? esc_attr($field['tab_content']) : ''; ?>"
						   data-tab_privacy="<?php echo isset($field['tab_privacy']) ? esc_attr($field['tab_privacy']) : 0; ?>"
						   data-user_decided="<?php echo isset($field['user_decided']) ? esc_attr($field['user_decided']) : 0; ?>"
                           href="javascript:void(0);">

                            <?php
                            $icon = $field['tab_icon'];
                            if ( uwp_is_fa_icon( $icon ) ) {
                                $tab_icon = '<i class="' . esc_attr( $icon ) . '" aria-hidden="true"></i>';
                            } elseif ( uwp_is_icon_url( $icon ) ) {
                                $tab_icon = '<b style="background-image: url("' . esc_url($icon) . '")"></b>';
                            } else {
                                $tab_icon = '<i class="fas fa-cog" aria-hidden="true"></i>';
                            }
                            ?>
                            <?php echo $tab_icon; ?>
                            <?php echo esc_attr($field['tab_name']); ?>
                        </a>
                    </li>
                    <?php
                }
            }
        }

        /**
         * Display selected tabs fields
         *
         * @since       2.0.0
         * @package     userswp
         *
         * @param       string   $form_type   Form type
         *
         */
	    public function tabs_selected_fields($form_type)
	    {
		    global $wpdb;
		    $form_id = ! empty( $_REQUEST['form'] ) ? absint($_REQUEST['form']) : 1;
		    $table_name = uwp_get_table_prefix() . 'uwp_profile_tabs';
		    ?>
		    <input type="hidden" name="form_type" id="form_type" value="<?php echo esc_attr($form_type); ?>"/>
		    <input type="hidden" name="manage_field_type" class="manage_field_type" value="profile_tabs">
		    <ul class="uwp-profile-tabs-selected core uwp_form_extras">
			    <?php
			    $tabs = $wpdb->get_results($wpdb->prepare("SELECT * FROM  " . $table_name . " where form_type = %s AND form_id = %s order by sort_order asc", array($form_type, $form_id)));
			    if (!empty($tabs)) {
				    foreach ($tabs as $key => $tab) {
					    $field_ins_upd = 'display';

					    if($tab->tab_level=='1' ){continue;}

					    ob_start();
					    $this->tabs_field_adminhtml($tab, $field_ins_upd);
					    $tab_rendered = ob_get_clean();

                        $tab_rendered = str_replace("</li>","",$tab_rendered);
                        $child_tabs = '';
                        foreach($tabs as $child_tab){
                            if($child_tab->tab_parent==$tab->id){
                                ob_start();
                                $this->tabs_field_adminhtml($child_tab, $field_ins_upd);
                                $child_tabs .= ob_get_clean();
                            }
                        }

                        if($child_tabs){
                            $tab_rendered .= "<ul>";
                            $tab_rendered .= $child_tabs;
                            $tab_rendered .= "</ul>";
                        }

                        echo $tab_rendered;
                        echo "</li>";

                        unset($tabs[$key]);
				    }
			    } ?>
		    </ul>
		    <?php
	    }

	    /**
         * Returns tabs fields
         *
         * @since       2.0.0
         * @package     userswp
         *
         * @param       string   $form_type   Form type
         *
         * @return array
         *
         */
	    public function tabs_fields($form_type, $form_id = 1)
	    {
		    $fields = array();

		    return apply_filters('uwp_tabs_fields', $fields, $form_type, $form_id);
	    }

	    /**
         * Displays tab field HTML
         *
         * @since       2.0.0
         * @package     userswp
         *
         * @param       mixed   $result_str   Field
         * @param       string  $field_ins_upd   Field action
         * @param       array   $request   Request data
         *
         */
	    public function tabs_field_adminhtml($result_str, $field_ins_upd = '', $request = array()){
		    global $wpdb;

		    $tabs_table_name = uwp_get_table_prefix() . 'uwp_profile_tabs';

		    $cf = $result_str;
		    if (!is_object($cf) && (is_int($cf) || ctype_digit($cf))) {
			    $field_info = $wpdb->get_row($wpdb->prepare("select * from " . $tabs_table_name . " where id= %d", array($cf)));
		    } elseif (is_object($cf)) {
			    $result_str = $cf->id;
			    $field_info = $wpdb->get_row($wpdb->prepare("select * from " . $tabs_table_name . " where id= %d", array((int)$cf->id)));
		    } elseif(isset($cf) && !empty($cf)) {
			    $field_info = $wpdb->get_row($wpdb->prepare("select * from " . $tabs_table_name . " where tab_key= %s", array($cf)));
		    } else {
		        $field_info = array();
		    }


		    if (isset($request['tab_type']) && $request['tab_type'] != ''){
			    $tab_type = esc_attr($request['tab_type']);
            } elseif($field_info && isset( $field_info->tab_type )) {
			    $tab_type = $field_info->tab_type;
            }else {
                $tab_type = '';
            }

		    if (isset($request['tab_name'])) {
			    $field_site_name = esc_attr($request['tab_name']);
		    } elseif($field_info && isset( $field_info->tab_name )) {
			    $field_site_name = $field_info->tab_name;
            } else {
                $field_site_name = '';
            }

		    if (isset($request['tab_key']) && $request['tab_key'] != '') {
			    $tab_key = esc_attr($request['tab_key']);
		    } elseif($field_info && isset( $field_info->tab_key )) {
			    $tab_key = $field_info->tab_key;
		    } else {
		        $tab_key = '';
		    }

		    if (isset($request['tab_content']) && $request['tab_content'] != '') {
			    $tab_content = esc_attr($request['tab_content']);
		    } elseif($field_info && isset( $field_info->tab_content )) {
			    $tab_content = $field_info->tab_content;
		    } else {
		        $tab_content = '';
		    }

		    if (isset($request['tab_icon']) && $request['tab_icon'] != '') {
			    $icon = esc_attr($request['tab_icon']);
		    } elseif( $field_info && isset($field_info->tab_icon)) {
			    $icon = $field_info->tab_icon;
		    } else {
		        $icon = 'fas fa-cog';
		    }

            if ( uwp_is_fa_icon( $icon ) ) {
                $field_icon = '<i class="' . esc_attr( $icon ) . '" aria-hidden="true"></i>';
            } elseif ( uwp_is_icon_url( $icon ) ) {
                $field_icon = '<b style="background-image: url("' . esc_url($icon) . '")"></b>';
            } elseif (isset($field_info->tab_type) && $field_info->tab_type == 'fieldset') {
			    $field_icon = '<i class="fas fa-arrows-h" aria-hidden="true"></i>';
		    } else {
                $field_icon = '<i class="fas fa-cog" aria-hidden="true"></i>';
            }

            if ( isset($request['tab_privacy']) && $request['tab_privacy'] != '' ) {
			    $privacy = absint( $request['tab_privacy'] );
		    } elseif( $field_info && isset($field_info->tab_privacy) ) {
			    $privacy = $field_info->tab_privacy;
		    } else {
		        $privacy = 1;
		    }

		    if ( isset($request['user_decided']) && $request['user_decided'] != '' ) {
			    $user_decided = esc_attr( $request['user_decided'] );
		    } elseif( $field_info && isset( $field_info->user_decided )) {
			    $user_decided = $field_info->user_decided;
		    } else {
		        $user_decided = 0;
		    }

		    if (isset($request['tab_parent']) && $request['tab_parent'] != ''){
			    $tab_parent = esc_attr($request['tab_parent']);
            } elseif($field_info && isset( $field_info->tab_parent )) {
			    $tab_parent = $field_info->tab_parent;
            }else {
                $tab_parent = 0;
            }

            if (isset($request['tab_level']) && $request['tab_level'] != ''){
			    $tab_level = esc_attr($request['tab_level']);
            } elseif($field_info && isset( $field_info->tab_level )) {
			    $tab_level = $field_info->tab_level;
            }else {
                $tab_level = 0;
            }

		    $exclude_privacy_tab = apply_filters('uwp_exclude_privacy_settings_tabs',array());
		    $exclude_privacy_option = false;
		    if(!empty($exclude_privacy_tab) && in_array($tab_key,$exclude_privacy_tab)) {
		        $exclude_privacy_option = true;
		    }

		    ?>
            <li class="text li-settings" id="licontainer_<?php echo $result_str; ?>">
                <i class="fas fa-caret-down toggle-arrow" aria-hidden="true" onclick="uwp_show_hide(this);"></i>
                <form>
                    <div class="title title<?php echo $result_str; ?> uwp-fieldset">
					    <?php
					    $nonce = wp_create_nonce('uwp_tabs_extras_nonce_' . $result_str);
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

                        <input type="hidden" name="_wpnonce" id="uwp_tabs_extras_nonce" value="<?php echo esc_attr($nonce); ?>"/>
                        <input type="hidden" name="field_id" id="field_id" value="<?php echo esc_attr($result_str); ?>"/>
                        <input type="hidden" name="form_type" id="form_type" value="profile-tabs"/>
                        <input type="hidden" name="tab_type" id="tab_type" value="<?php echo esc_attr($tab_type); ?>"/>
                        <input type="hidden" name="tab_parent" value="<?php echo esc_attr($tab_parent); ?>"/>
                        <input type="hidden" name="tab_level" value="<?php echo esc_attr($tab_level); ?>"/>

                        <ul class="widefat post fixed" style="width:100%;">

                            <li class="uwp-setting-name">
                                <label for="tab_name" class="uwp-tooltip-wrap">
                                    <?php
                                    echo uwp_help_tip(__('This will be the name of profile tab.', 'userswp'));
                                    _e('Tab Name:', 'userswp'); ?>
                                </label>
                                <div class="uwp-input-wrap">
                                    <input type="text" name="tab_name" id="tab_name"
                                           value="<?php echo esc_attr($field_site_name); ?>"/>
                                </div>
                            </li>

                            <li class="uwp-setting-name">

                                <label for="tab_key" class="uwp-tooltip-wrap">
                                    <?php
                                    echo uwp_help_tip(__('Key and URL slug for the profile tab.', 'userswp'));
                                    _e('Tab key:', 'userswp'); ?>
                                </label>
                                <div class="uwp-input-wrap">
                                    <input type="text" name="tab_key" id="tab_key"
                                           value="<?php echo esc_attr($tab_key); ?>" <?php if ( ! empty( $tab_key ) && $tab_key != '' ) { echo 'readonly="readonly"'; } ?>/>
                                </div>

                            </li>

                            <li class="uwp-setting-name">

                                <label for="tab_icon" class="uwp-tooltip-wrap">
                                    <?php
                                    echo uwp_help_tip(__('This will be the icon for profile tab.', 'userswp'));
                                    _e('Tab icon:', 'userswp'); ?>
                                </label>
                                <div class="uwp-input-wrap">
                                    <input type="text" name="tab_icon" id="tab_icon"
                                           value="<?php echo esc_attr($icon); ?>"/>
                                </div>

                            </li>

                            <li class="uwp-setting-name">
                                <label for="privacy" class="uwp-tooltip-wrap">
                                    <?php
                                    echo uwp_help_tip(__('Select privacy for displaying profile tab and content.', 'userswp'));
                                    _e('Privacy:', 'userswp'); ?>
                                </label>
                                <div class="uwp-input-wrap">
                                    <?php
                                        $privacy_options = array(
                                            0 => __("Anyone", "userswp"),
                                            1 => __("Logged in", "userswp"),
                                            2 => __("Author only", "userswp"),
                                        );

                                        $privacy_options = apply_filters('uwp_tab_privacy_options', $privacy_options, $result_str, $request);

                                        if($exclude_privacy_option) { ?>
                                            <input type="hidden" name="tab_privacy" value="<?php echo $privacy; ?>">
                                            <p>
                                                <?php
                                                $privacy_option_val =  !empty($privacy_options[$privacy]) ? $privacy_options[$privacy] : '';
                                                echo sprintf(__('%s (Not Changeable)','userswp'),$privacy_option_val);
                                                ?>
                                            </p>
                                        <?php } else{ ?>
                                            <select name="tab_privacy" class="aui-select2">
                                                <?php
                                                    foreach ($privacy_options as $key => $val){
                                                        echo '<option value="'.esc_attr($key).'"'. selected($privacy, $key, false).'>'.$val.'</option>';
                                                    }
                                                ?>
                                            </select>
                                        <?php } ?>
                                </div>
                            </li>

                            <li class="uwp-setting-name">
                                <label for="user_decided" class="uwp-tooltip-wrap">
                                    <?php
                                    echo uwp_help_tip(__('Enable to allow user to decide privacy for displaying profile tab and content.', 'userswp'));
                                    _e('Let user decide :', 'userswp'); ?>
                                    <?php echo ($exclude_privacy_option) ? __('N/A','userswp'): ''; ?>
                                </label>
                                <div class="uwp-input-wrap">
                                    <?php if(!$exclude_privacy_option) { ?>
                                        <input type="hidden" name="user_decided" value="0" />
                                        <input type="checkbox" name="user_decided" value="1" <?php checked( $user_decided, 1, true );?> />
                                    <?php } else { ?>
                                        <input type="hidden" name="user_decided" value="<?php echo esc_attr($user_decided); ?>" />
                                    <?php } ?>
                                </div>
                            </li>

                            <?php

                            do_action('uwp_profile_tab_custom_fields', $result_str);

                            if($tab_type == 'shortcode'){
                            ?>
                            <li class="uwp-setting-name">
                                <label for="tab_content" style="width:100%;">
                                    <?php
                                    echo uwp_help_tip(__('Content to display in profile tab. Shortcode allowed.', 'userswp'));
                                    _e('Tab content:','userswp');
                                    if($tab_type == 'shortcode'){
                                        echo WP_Super_Duper::shortcode_button("'tab_content_".$result_str."'");
                                    }
                                    ?><br>
                                    <textarea name="tab_content" id="tab_content" placeholder="<?php _e('Add shortcode here.','userswp');?>"  style="width:100%;"><?php echo stripslashes($tab_content);?></textarea>
                                </label>
                            </li>
                            <?php
                            }else{
                                echo '<input type="hidden" name="tab_content" value="'.esc_attr($tab_content).'">';
                            }
                            ?>

                            <li>
                                <div class="uwp-input-wrap uwp-tab-actions" data-setting="save_button">

                                    <input type="button" class="button button-primary" name="save" id="save"
                                           value="<?php esc_attr_e('Save', 'userswp'); ?>"
                                           onclick="save_field('<?php echo esc_attr($result_str); ?>', 'profile_tab')"/>
                                           <a class="item-delete submitdelete deletion" id="delete-<?php echo esc_attr($result_str); ?>" href="javascript:void(0);" onclick="delete_field('<?php echo esc_attr($result_str); ?>', '<?php echo wp_create_nonce('uwp_form_tab_delete_nonce_' . $result_str); ?>', '<?php echo esc_attr($tab_key); ?>', 'profile_tab')"><?php _e("Remove","userswp");?></a>

                                </div>
                            </li>
                        </ul>

                    </div>
                </form>
            </li>
		    <?php
        }

        /**
         * Handles tabs fields AJAX
         *
         * @since       2.0.0
         * @package     userswp
         *
         * @return mixed|void
         *
         */
	    public function tabs_ajax_handler(){

		    if (isset($_REQUEST['create_field'])) {

		        if ( ! current_user_can( 'manage_options' ) ) {
                    wp_die( -1 );
                }

			    $field_id = isset($_REQUEST['field_id']) ? trim(sanitize_text_field($_REQUEST['field_id']), '_') : '';
			    $field_action = isset($_REQUEST['field_ins_upd']) ? sanitize_text_field($_REQUEST['field_ins_upd']) : '';
			    $form_id = isset($_REQUEST['form_id']) ? absint($_REQUEST['form_id']) : 1;

			    /* ------- check nonce field ------- */
			    if (isset($_REQUEST['update']) && $_REQUEST['update'] == 'update') {

				    if (!empty($_REQUEST['tabs']) && is_array($_REQUEST['tabs'])) {

					    $tabs = $_REQUEST['tabs'];
					    $this->update_field_order($tabs, $form_id);

				    }
			    }

			    /* ---- Show field form in admin ---- */
			    if ($field_action == 'new') {
				    $htmlvar_name = isset($_REQUEST['htmlvar_name']) ? sanitize_text_field($_REQUEST['htmlvar_name']) : sanitize_text_field($_REQUEST['tab_key']);

				    $this->tabs_field_adminhtml($htmlvar_name, $field_action, $_REQUEST);
			    }

			    /* ---- Delete field ---- */
			    if ($field_id != '' && $field_action == 'delete' && isset($_REQUEST['_wpnonce'])) {
				    if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'uwp_form_tab_delete_nonce_' . $field_id))
					    return;

				    $this->tabs_field_delete($field_id);
			    }

			    /* ---- Save field  ---- */
			    if ($field_action == 'submit' && isset($_REQUEST['_wpnonce'])) {
				    if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'uwp_tabs_extras_nonce_' . $field_id))
					    return;

				    foreach ($_REQUEST as $pkey => $pval) {
					    $tags = is_array($_REQUEST[$pkey]) ? 'skip_field' : '';

					    if ($tags != 'skip_field' && $pkey != 'tab_content') {
						    $_REQUEST[$pkey] = strip_tags(sanitize_text_field($_REQUEST[$pkey]), $tags);
					    }
				    }

				    $lastid = $this->tabs_field_save($_REQUEST);

				    if (is_int($lastid) && $lastid > 0) {
					    $this->tabs_field_adminhtml($lastid, 'submit');
				    } else {
					    echo $lastid;
				    }
			    }
		    }
		    die();
        }

        /**
         * Save tabs fields
         *
         * @since       2.0.0
         * @package     userswp
         *
         * @param       array   $request_field   Request data
         *
         * @return string|int
         *
         */
	    public function tabs_field_save($request_field = array())
	    {
		    global $wpdb;
		    $table_name = uwp_get_table_prefix() . 'uwp_profile_tabs';

		    $tab_id = isset($request_field['field_id']) && $request_field['field_id'] ? absint($request_field['field_id']) : '';
		    $tab_name= isset($request_field['tab_name']) ? sanitize_text_field($request_field['tab_name']) : '';
		    $tab_icon= isset($request_field['tab_icon']) ? sanitize_text_field($request_field['tab_icon']) : '';
		    $tab_key = !empty($request_field['tab_key']) ? sanitize_title($request_field['tab_key']) : sanitize_title($tab_name, 'uwp-tab-'.$tab_name);
		    $tab_level = !empty($request_field['tab_level']) ? sanitize_text_field($request_field['tab_level']) : 0;
		    $tab_parent = !empty($request_field['tab_parent']) ? sanitize_text_field($request_field['tab_parent']) : 0;
		    $tab_type = isset($request_field['tab_type']) ? sanitize_text_field($request_field['tab_type']) : 'standard';
		    $form_type = isset($request_field['form_type']) ? $request_field['form_type'] : $tab_type;
		    $tab_privacy = isset($request_field['tab_privacy']) ? (int)$request_field['tab_privacy'] : 0;
		    $user_decided = isset($request_field['user_decided']) ? (int)$request_field['user_decided'] : 0;
            $form_id = isset($request_field['form_id']) ? (int)$request_field['form_id'] : 1;

		    $total_tabs = $wpdb->get_var("SELECT COUNT(id) FROM {$table_name}");

		    $data = array(
                    'form_type'     => 'profile-tabs',
                    'sort_order'    => $total_tabs + 1,
                    'tab_layout'    => 'profile',
                    'tab_type'      => $tab_type,
                    'tab_level'     => sanitize_text_field($tab_level),
                    'tab_parent'    => sanitize_text_field($tab_parent),
                    'tab_name'      => $tab_name,
                    'tab_icon'      => $tab_icon,
                    'tab_key'       => sanitize_text_field($tab_key),
                    'tab_content'   => wp_kses_post($request_field['tab_content']),
                    'tab_privacy'   => $tab_privacy,
                    'user_decided'  => $user_decided,
                    'form_id'       => $form_id,
                );

            $format = array_fill( 0, count( $data ), '%s' );

		    $check_html_variable = $wpdb->get_var($wpdb->prepare("select COUNT(*) from " . $table_name . " where tab_type = %s AND tab_name LIKE %s AND tab_key = %s AND form_type = %s AND form_id = %s",
			    array($tab_type, $tab_name, $tab_key, $form_type, $form_id)));


		    if ( !$tab_id && (int)$check_html_variable > 0) {
                return 'invalid_key';
		    }

		    if ( $tab_id ) { // update

                $wpdb->update(
                    $table_name,
                    $data,
                    array( 'id' => $tab_id ),
                    $format
                );

                $lastid = $tab_id;

            } else { // insert
                $wpdb->insert(
                    $table_name,
                    $data,
                    $format
                );

                $lastid = $wpdb->insert_id;
            }

            return (int) $lastid;
	    }

	    /**
         * Save tabs fields
         *
         * @since       2.0.0
         * @package     userswp
         *
         * @param       string   $field_id   Request data
         *
         * @return int
         *
         */
	    public function tabs_field_delete($field_id = '')
	    {
		    global $wpdb;
		    $table_name = uwp_get_table_prefix() . 'uwp_profile_tabs';

		    if ($field_id != '') {
			    $cf = trim($field_id, '_');
			    $wpdb->query($wpdb->prepare("delete from " . $table_name . " where id= %d ", array($cf)));
			    return $field_id;
		    } else
			    return 0;
	    }

	    /**
         * Updates profile tabs sort order.
         *
         * @param       array       $tabs      Tabs array.
         *
         * @return      object|bool       Sorted tabs.
         */
        public function update_field_order($tabs = array(), $form_id = 1)
        {
            global $wpdb;
            $table_name = uwp_get_table_prefix() . 'uwp_profile_tabs';

            $count = 0;
			if (!empty($tabs)) {
				$result = false;
				foreach ( $tabs as $index => $tab ) {
					$result = $wpdb->update(
						$table_name,
						array('sort_order' => $index, 'tab_level' => (int)$tab['tab_level'], 'tab_parent' => (int)$tab['tab_parent']),
						array('id' => absint($tab['id']), 'form_id' => $form_id),
						array('%d','%d')
					);
					$count ++;
				}
				if($result !== false){
					return true;
				}else{
					return new WP_Error( 'failed', __( "Failed to sort tab items.", "userswp" ) );
				}
			} else{
				return new WP_Error( 'failed', __( "Failed to sort tab items.", "userswp" ) );
			}
        }

    }

}


new UsersWP_Settings_Profile_Tabs();