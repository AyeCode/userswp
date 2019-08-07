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

if ( ! class_exists( 'UsersWP_Settings_Profile_Tabs', false ) ) :

    /**
     * UsersWP_Settings_Email.
     */
    class UsersWP_Settings_Profile_Tabs {

        public function __construct() {

            add_filter( 'uwp_form_builder_tabs_array', array( $this, 'uwp_form_builder_tab_items' ), 99 );
	        add_filter('uwp_form_builder_available_fields_head', array( $this,  'uwp_tabs_available_fields_head' ), 10, 2);
	        add_filter('uwp_form_builder_available_fields_note', array( $this,  'uwp_tabs_available_fields_note' ), 10, 2);
	        add_filter('uwp_form_builder_selected_fields_head', array( $this,  'uwp_tabs_selected_fields_head' ), 10, 2);
	        add_filter('uwp_form_builder_selected_fields_note', array( $this,  'uwp_tabs_selected_fields_note' ), 10, 2);
	        add_action('uwp_manage_available_fields', array( $this,  'uwp_manage_tabs_available_fields' ), 10, 1);
	        add_action('uwp_manage_available_fields_predefined', array( $this,  'uwp_manage_tabs_predefined_fields' ), 10, 1);
	        add_action('uwp_manage_selected_fields', array( $this,  'uwp_manage_tabs_selected_fields' ), 10, 1);
	        add_filter('uwp_tabs_fields', array( $this,  'uwp_tabs_extra_fields' ), 10, 2);
	        add_action('wp_ajax_uwp_ajax_profile_tabs_action', array( $this,  'uwp_tabs_ajax_handler'));

        }

	    public function uwp_form_builder_tab_items($tabs)
	    {
		    $tabs['profile-tabs'] = __('Profile Tabs', 'userswp');
		    return $tabs;

	    }

	    public function uwp_tabs_available_fields_head($heading, $form_type)
	    {
		    switch ($form_type) {
			    case 'profile-tabs':
				    $heading = __('Available profile tabs.', 'userswp');
				    break;
		    }

		    return $heading;
	    }


	    public function uwp_tabs_available_fields_note($note, $form_type)
	    {
		    switch ($form_type) {
			    case 'profile-tabs':
				    $note = __("Fields that can be added to the profile tabs.", 'userswp');
				    break;
		    }

		    return $note;
	    }


	    public function uwp_tabs_selected_fields_head($heading, $form_type)
	    {
		    switch ($form_type) {
			    case 'profile-tabs':
				    $heading = __('Profile Tabs', 'userswp');
				    break;

		    }

		    return $heading;
	    }


	    public function uwp_tabs_selected_fields_note($note, $form_type)
	    {
		    switch ($form_type) {
			    case 'profile-tabs':
				    $note = __('Choose the items from left panel to create the profile tabs.', 'userswp');
				    break;

		    }
		    return $note;
	    }


	    public function uwp_manage_tabs_available_fields($form_type)
	    {
		    switch ($form_type) {
			    case 'profile-tabs':
				    $this->uwp_tabs_available_fields($form_type);
				    break;
		    }
	    }

	    public function uwp_manage_tabs_predefined_fields($form_type)
	    {
		    switch ($form_type) {
			    case 'profile-tabs':
				    $this->uwp_tabs_predefined_fields($form_type);
				    break;
		    }
	    }

	    public function uwp_manage_tabs_selected_fields($form_type)
	    {
		    switch ($form_type) {
			    case 'profile-tabs':
				    $this->uwp_tabs_selected_fields($form_type);
				    break;
		    }
	    }

	    public function uwp_tabs_available_fields($form_type){
		    global $wpdb;

		    $table_name = uwp_get_table_prefix() . 'uwp_profile_tabs';

		    $existing_fields = $wpdb->get_results("select tab_key from " . $table_name . " where form_type ='" . $form_type . "'");

		    $existing_field_ids = array();
		    if (!empty($existing_fields)) {
			    foreach ($existing_fields as $existing_field) {
				    $existing_field_ids[] = $existing_field->tab_key;
			    }
		    }

		    ?>
		    <input type="hidden" name="form_type" id="form_type" value="<?php echo $form_type; ?>"/>
		    <input type="hidden" name="manage_field_type" class="manage_field_type" value="profile_tabs">
            <input type="hidden" name="uwp_new_field_nonce" id="uwp_new_field_nonce" value="<?php echo wp_create_nonce( 'uwp_new_field_nonce' );?>"/>
		    <ul>
			    <?php

			    $fields = $this->uwp_tabs_fields($form_type);

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
                            $tab_icon = '<b style="background-image: url("' . $icon . '")"></b>';
                        } else {
                            $tab_icon = '<i class="fas fa-cog" aria-hidden="true"></i>';
                        }
					    ?>
					    <li <?php echo $style; ?> >
						    <a id="uwp-<?php echo $field['tab_key']; ?>"
                                data-field-custom-type="custom"
                               class="uwp-draggable-form-items"
                               data-tab_layout="profile"
                               data-tab_type="<?php echo isset($field['tab_type']) ? esc_attr($field['tab_type']) : ''; ?>"
                               data-tab_name="<?php echo isset($field['tab_name']) ? esc_attr($field['tab_name']) : ''; ?>"
                               data-tab_icon="<?php echo isset($field['tab_icon']) ? esc_attr($field['tab_icon']) : ''; ?>"
                               data-tab_key="<?php echo isset($field['tab_key']) ? esc_attr($field['tab_key']) : ''; ?>"
                               data-tab_login_only="<?php echo isset($field['tab_login_only']) ? esc_attr($field['tab_login_only']) : 0; ?>"
                               data-tab_content="<?php echo isset($field['tab_content']) ? esc_attr($field['tab_content']) : ''; ?>"
                               href="javascript:void(0);">
                                <?php echo $tab_icon; ?>
							    <?php echo $field['tab_name']; ?>
						    </a>
					    </li>
					    <?php
				    }
			    }
			    ?>
		    </ul>
		    <?php
	    }

	    public function uwp_tabs_predefined_fields($form_type){
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

		    $fields = apply_filters('uwp_profile_tabs_predefined_fields', $fields, $form_type);

		    global $wpdb;

		    $table_name = uwp_get_table_prefix() . 'uwp_profile_tabs';

		    $existing_fields = $wpdb->get_results("select tab_key from " . $table_name . " where form_type ='" . $form_type . "'");

		    $existing_field_ids = array();
		    if (!empty($existing_fields)) {
			    foreach ($existing_fields as $existing_field) {
				    $existing_field_ids[] = $existing_field->tab_key;
			    }
		    }

		    ?>
            <input type="hidden" name="form_type" id="form_type" value="<?php echo $form_type; ?>"/>
            <input type="hidden" name="manage_field_type" class="manage_field_type" value="profile_tabs">
            <?php
            if (!empty($fields)) {
            ?>
                <ul>
                <?php
                foreach ($fields as $id => $field) {

                    $display = '';
					    if (in_array($field['tab_key'], $existing_field_ids))
						    $display = 'display:none;';

					    $style = 'style="' . $display . '"';

                    ?>
                    <li <?php echo $style; ?>>
                        <a id="uwp-<?php echo $field['tab_key']; ?>"
                           data-field-custom-type="predefined"
                           class="uwp-draggable-form-items"
                           data-tab_layout="profile"
                           data-field-type-key="uwp-<?php echo $field['tab_key']; ?>"
						   data-tab_type="<?php echo isset($field['tab_type']) ? esc_attr($field['tab_type']) : ''; ?>"
						   data-tab_name="<?php echo isset($field['tab_name']) ? esc_attr($field['tab_name']) : ''; ?>"
						   data-tab_icon="<?php echo isset($field['tab_icon']) ? esc_attr($field['tab_icon']) : ''; ?>"
						   data-tab_key="<?php echo isset($field['tab_key']) ? esc_attr($field['tab_key']) : ''; ?>"
						   data-tab_login_only="<?php echo isset($field['tab_login_only']) ? esc_attr($field['tab_login_only']) : 0; ?>"
						   data-tab_content="<?php echo isset($field['tab_content']) ? esc_attr($field['tab_content']) : ''; ?>"
                           href="javascript:void(0);">

                            <?php
                            $icon = $field['tab_icon'];
                            if ( uwp_is_fa_icon( $icon ) ) {
                                $tab_icon = '<i class="' . esc_attr( $icon ) . '" aria-hidden="true"></i>';
                            } elseif ( uwp_is_icon_url( $icon ) ) {
                                $tab_icon = '<b style="background-image: url("' . $icon . '")"></b>';
                            } else {
                                $tab_icon = '<i class="fas fa-cog" aria-hidden="true"></i>';
                            }
                            ?>
                            <?php echo $tab_icon; ?>
                            <?php echo $field['tab_name']; ?>
                        </a>
                    </li>
                    <?php
                }
            }
        }

	    public function uwp_tabs_selected_fields($form_type)
	    {
		    global $wpdb;
		    $table_name = uwp_get_table_prefix() . 'uwp_profile_tabs';
		    ?>
		    <input type="hidden" name="form_type" id="form_type" value="<?php echo $form_type; ?>"/>
		    <input type="hidden" name="manage_field_type" class="manage_field_type" value="profile_tabs">
		    <ul class="core uwp_form_extras">
			    <?php
			    $fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM  " . $table_name . " where form_type = %s order by sort_order asc", array($form_type)));
			    if (!empty($fields)) {
				    foreach ($fields as $field) {
					    $field_ins_upd = 'display';
					    $this->uwp_tabs_field_adminhtml($field, $field_ins_upd, false);
				    }
			    } ?>
		    </ul>
		    <?php
	    }

	    public function uwp_tabs_fields($form_type)
	    {
		    $fields = array();

		    return apply_filters('uwp_tabs_fields', $fields, $form_type);
	    }

	    public function uwp_tabs_extra_fields($fields, $form_type)
	    {
		    global $wpdb;

		    $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
		    $cfs = $wpdb->get_results("SELECT htmlvar_name, site_title, field_type, field_icon FROM " . $table_name . " WHERE form_type = 'account' AND is_public != '0' AND show_in LIKE '%[own_tab]%' ORDER BY sort_order ASC");

		    if(!empty($cfs)){
                foreach ($cfs as $row) {
                    $fields[] = array(
                        'tab_type'   => 'meta',
                        'tab_name'   => esc_attr($row->site_title),
                        'tab_icon'   => isset($row->field_icon) && $row->field_icon ? $row->field_icon : "fas fa-cog",
                        'tab_key'    => esc_attr($row->htmlvar_name),
                        'tab_login_only' => 0,
                        'tab_content'=> ''
                    );
                }
		    }

		    return $fields;
	    }

	    public function uwp_tabs_field_adminhtml($result_str, $field_ins_upd = '', $default = false, $request = array()){
		    global $wpdb;

		    $tabs_table_name = uwp_get_table_prefix() . 'uwp_profile_tabs';

		    $cf = $result_str;
		    if (!is_object($cf) && (is_int($cf) || ctype_digit($cf))) {
			    $field_info = $wpdb->get_row($wpdb->prepare("select * from " . $tabs_table_name . " where id= %d", array($cf)));
		    } elseif (is_object($cf)) {
			    $result_str = $cf->id;
			    $field_info = $wpdb->get_row($wpdb->prepare("select * from " . $tabs_table_name . " where id= %d", array((int)$cf->id)));
		    } else {
			    $field_info = $wpdb->get_row($wpdb->prepare("select * from " . $tabs_table_name . " where tab_key= %d", array($cf)));
		    }

		    if (isset($request['tab_type']) && $request['tab_type'] != ''){
			    $tab_type = esc_attr($request['tab_type']);
            } elseif($field_info) {
			    $tab_type = $field_info->tab_type;
            }else {
                $tab_type = '';
            }

		    if (isset($request['tab_name'])) {
			    $field_site_name = $request['tab_name'];
		    } elseif($field_info) {
			    $field_site_name = $field_info->tab_name;
            } else {
                $field_site_name = '';
            }

		    if (isset($request['tab_key']) && $request['tab_key'] != '') {
			    $tab_key = esc_attr($request['tab_key']);
		    } elseif($field_info) {
			    $tab_key = $field_info->tab_key;
		    } else {
		        $tab_key = '';
		    }

		    if (isset($request['tab_content']) && $request['tab_content'] != '') {
			    $tab_content = esc_attr($request['tab_content']);
		    } elseif($field_info) {
			    $tab_content = $field_info->tab_content;
		    } else {
		        $tab_content = '';
		    }

		    if (isset($request['tab_login_only']) && $request['tab_login_only'] != '') {
			    $tab_login_only = esc_attr($request['tab_login_only']);
		    } elseif($field_info) {
			    $tab_login_only = $field_info->tab_login_only;
		    } else {
		        $tab_login_only = 0;
		    }

		    if (isset($request['tab_icon']) && $request['tab_icon'] != '') {
			    $icon = esc_attr($request['tab_icon']);
		    } elseif($field_info) {
			    $icon = $field_info->tab_icon;
		    } else {
		        $icon = 'fas fa-cog';
		    }

            if ( uwp_is_fa_icon( $icon ) ) {
                $field_icon = '<i class="' . esc_attr( $icon ) . '" aria-hidden="true"></i>';
            } elseif ( uwp_is_icon_url( $icon ) ) {
                $field_icon = '<b style="background-image: url("' . $icon . '")"></b>';
            } elseif (isset($field_info->tab_type) && $field_info->tab_type == 'fieldset') {
			    $field_icon = '<i class="fas fa-arrows-h" aria-hidden="true"></i>';
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

                        <input type="hidden" name="_wpnonce" id="uwp_form_extras_nonce" value="<?php echo $nonce; ?>"/>
                        <input type="hidden" name="field_id" id="field_id" value="<?php echo esc_attr($result_str); ?>"/>
                        <input type="hidden" name="form_type" id="form_type" value="profile-tabs"/>
                        <input type="hidden" name="tab_type" id="tab_type" value="<?php echo $tab_type; ?>"/>
                        <input type="hidden" name="tab_key" value="<?php echo $tab_key ?>"/>

                        <ul class="widefat post fixed" style="width:100%;">

                            <li class="uwp-setting-name">
                                <label for="tab_name" class="uwp-tooltip-wrap">
                                    <?php _e('Tab Name:', 'userswp'); ?>
                                </label>
                                <div class="uwp-input-wrap">
                                    <input type="text" name="tab_name" id="tab_name"
                                           value="<?php echo $field_site_name; ?>"/>
                                </div>
                            </li>

                            <li class="uwp-setting-name">

                                <label for="tab_icon" class="uwp-tooltip-wrap">
                                    <?php _e('Upload icon :', 'userswp'); ?>
                                </label>
                                <div class="uwp-input-wrap">
                                    <input type="text" name="tab_icon" id="tab_icon"
                                           value="<?php echo $icon; ?>"/>
                                </div>

                            </li>

                            <li class="uwp-setting-name">

                                <label for="tab_login_only" class="uwp-tooltip-wrap">
                                    <?php _e('For logged in users? :', 'userswp'); ?>
                                </label>
                                <div class="uwp-input-wrap">
                                    <input type="hidden" name="tab_login_only" value="0" />
                                    <input type="checkbox" name="tab_login_only" value="1" <?php checked( $tab_login_only, 1, true );?> />
                                </div>

                            </li>

                            <?php

                            if($tab_type == 'shortcode'){
                            ?>
                            <li class="uwp-setting-name">
                                <label for="tab_content">
                                    <?php _e('Tab content:','userswp');
                                    if($tab_type == 'shortcode'){
                                        echo WP_Super_Duper::shortcode_button("'tab_content_".$result_str."'");
                                    }
                                    ?><br>
                                    <textarea name="tab_content" id="tab_content" placeholder="<?php _e('Add shortcode here.','userswp');?>"><?php echo stripslashes($tab_content);?></textarea>
                                </label>
                            </li>
                            <?php
                            }else{
                                echo '<input type="hidden" name="tab_content" value=\''.stripslashes($tab_content).'\'>';
                            }
                            ?>

                            <li>
                                <div class="uwp-input-wrap uwp-tab-actions" data-setting="save_button">

                                    <input type="button" class="button button-primary" name="save" id="save"
                                           value="<?php esc_attr_e('Save', 'userswp'); ?>"
                                           onclick="save_register_field('<?php echo $result_str; ?>', 'profile_tab')"/>
                                           <a class="item-delete submitdelete deletion" id="delete-16" href="javascript:void(0);" onclick="delete_register_field('<?php echo esc_attr($result_str); ?>', '<?php echo $nonce; ?>', '<?php echo $tab_key ?>', 'profile_tab')"><?php _e("Remove","userswp");?></a>

                                </div>
                            </li>
                        </ul>

                    </div>
                </form>
            </li>
		    <?php
        }

	    public function uwp_tabs_ajax_handler(){
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

				    $return = $this->update_field_order($field_ids);

				    if (is_array($return)) {
					    $return = json_encode($return);
				    }

				    echo $return;
			    }

			    /* ---- Show field form in admin ---- */
			    if ($field_action == 'new') {
				    $form_type = isset($_REQUEST['form_type']) ? sanitize_text_field($_REQUEST['form_type']) : '';
				    //$fields = $this->uwp_tabs_fields($form_type);


				    //$_REQUEST['field_id'] = isset($_REQUEST['field_id']) ? sanitize_text_field($_REQUEST['field_id']) : '';
				    //$_REQUEST['is_default'] = '0';

				    /*if (!empty($fields)) {
					    foreach ($fields as $val) {
						    $val = stripslashes_deep($val);
                            $data_type = $val['tab_type'];
						    if ($val['tab_key'] == $_REQUEST['tab_key']) {
							    $_REQUEST['field_type'] = $val['tab_type'];
							    $_REQUEST['tab_name'] = $val['tab_name'];
							    $_REQUEST['tab_login_only'] = $val['tab_login_only'];
						    }
					    }
				    }*/

				    $htmlvar_name = isset($_REQUEST['htmlvar_name']) ? sanitize_text_field($_REQUEST['htmlvar_name']) : sanitize_text_field($_REQUEST['tab_key']);

				    $this->uwp_tabs_field_adminhtml($htmlvar_name, $field_action, false, $_REQUEST);
			    }

			    /* ---- Delete field ---- */
			    if ($field_id != '' && $field_action == 'delete' && isset($_REQUEST['_wpnonce'])) {
				    if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'uwp_form_extras_nonce' . $field_id))
					    return;

				    echo $this->uwp_tabs_field_delete($field_id);
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

				    $lastid = $this->uwp_tabs_field_save($_REQUEST);

				    if (is_int($lastid) && $lastid > 0) {
					    $this->uwp_tabs_field_adminhtml($lastid, 'submit');
				    } else {
					    echo $lastid;
				    }
			    }
		    }
		    die();
        }

	    public function uwp_tabs_field_save($request_field = array())
	    {
		    global $wpdb;
		    $table_name = uwp_get_table_prefix() . 'uwp_profile_tabs';

		    $result_str = isset($request_field['field_id']) ? trim($request_field['field_id']) : '';

		    $cf = trim($result_str, '_');

		    /*-------- check duplicate validation --------*/

		    $tab_key = isset($request_field['tab_key']) ? sanitize_text_field($request_field['tab_key']) : '';
		    $tab_type= isset($request_field['tab_type']) ? sanitize_text_field($request_field['tab_type']) : '';
		    $tab_name= isset($request_field['tab_name']) ? sanitize_text_field($request_field['tab_name']) : '';
		    $tab_icon= isset($request_field['tab_icon']) ? sanitize_text_field($request_field['tab_icon']) : '';
		    $tab_login_only= isset($request_field['tab_login_only']) ? (int)$request_field['tab_login_only'] : 0;
		    $form_type = $request_field['form_type'];

		    $data = array(
                    'tab_layout'    => 'profile',
                    'form_type'     => 'profile-tabs',
                    'tab_type'      => $tab_type,
                    'tab_name'      => $tab_name,
                    'tab_icon'      => $tab_icon,
                    'tab_key'       => sanitize_text_field($tab_key),
                    'tab_login_only'=> $tab_login_only,
                    'tab_content'   => sanitize_textarea_field($request_field['tab_content']),
                );

            $format = array_fill( 0, count( $data ), '%s' );

		    $check_html_variable = $wpdb->get_var($wpdb->prepare("select COUNT(*) from " . $table_name . " where tab_type = %s AND tab_name LIKE %s AND tab_key = %s and form_type = %s ",
			    array($tab_type, $tab_key, $tab_name, $form_type)));


		    if ((int)$check_html_variable > 0) {
                return 'invalid_key';
		    }

		    $user_meta_info = $wpdb->get_row(
                $wpdb->prepare(
                    "select id from " . $table_name . " where tab_type = %s AND tab_name LIKE %s AND tab_key = %s and form_type = %s",
                    array($tab_type, $tab_key, $tab_name, $form_type)
                )
            );

		    if ( !empty($user_meta_info) ) {// update // update

                $result = $wpdb->update(
                    $table_name,
                    $data,
                    array( 'id' => $user_meta_info->id ),
                    $format
                );

                $lastid = $user_meta_info->id;

            } else { // insert
                $result = $wpdb->insert(
                    $table_name,
                    $data,
                    $format
                );

                $lastid = $wpdb->insert_id;
            }

            return (int) $lastid;
	    }

	    public function uwp_tabs_field_delete($field_id = '')
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
         * Updates extras fields sort order.
         *
         * @param       array       $field_ids      Form extras field ids.
         *
         * @return      array|bool                  Sorted field ids.
         */
        function update_field_order($field_ids = array())
        {
            global $wpdb;
            $table_name = uwp_get_table_prefix() . 'uwp_profile_tabs';

            $count = 0;
            if (!empty($field_ids)):
                foreach ($field_ids as $id) {

                    $cf = trim($id, '_');

                    $wpdb->query(
                        $wpdb->prepare(
                            "update " . $table_name . " set
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

    }

endif;


return new UsersWP_Settings_Profile_Tabs();