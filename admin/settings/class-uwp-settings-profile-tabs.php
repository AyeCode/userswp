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
				    $heading = __('Available tabs.', 'userswp');
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
		    <input type="hidden" name="manage_field_type" class="manage_field_type" value="profile_tabs">
		    <ul>
			    <?php

			    $fields = $this->uwp_tabs_fields($form_type);

			    if (!empty($fields)) {
				    foreach ($fields as $field) {
					    $field = stripslashes_deep($field); // strip slashes

					    $fieldset_width = '';
					    if ($field['field_type'] == 'fieldset') {
						    $fieldset_width = 'width:100%;';
					    }

					    $form_field_info = uwp_get_custom_field_info($field['htmlvar_name']);

					    $display = '';
					    if (in_array($field['htmlvar_name'], $existing_field_ids))
						    $display = 'display:none;';

					    $style = 'style="' . $display . $fieldset_width . '"';

					    if($form_field_info){
						    $data_type = $form_field_info->data_type;
                        } else {
						    $data_type = $field['field_type'];
                        }

					    if ($data_type == 'VARCHAR') {
						    $data_type = 'XVARCHAR';
					    }
					    ?>
					    <li <?php echo $style; ?> >

						    <a id="uwp-<?php echo $field['htmlvar_name']; ?>"
						       class="uwp-draggable-form-items uwp-<?php echo $field['field_type']; ?>"
						       href="javascript:void(0);" data-type="<?php echo $field['field_type']; ?>" data-data_type="<?php echo $data_type; ?>">
							    <?php if (isset($field['field_icon']) && (strpos($field['field_icon'], 'fa fa-') !== false || strpos($field['field_icon'], 'fas fa-') !== false)) {
								    echo '<i class="' . $field['field_icon'] . '" aria-hidden="true"></i>';
							    } elseif (isset($field['field_icon']) && $field['field_icon']) {
								    echo '<b style="background-image: url("' . $field['field_icon'] . '")"></b>';
							    } else {
								    echo '<i class="fas fa-cog" aria-hidden="true"></i>';
							    } ?>

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

	    public function uwp_tabs_selected_fields($form_type)
	    {
		    global $wpdb;
		    $extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';
		    ?>
		    <input type="hidden" name="form_type" id="form_type" value="<?php echo $form_type; ?>"/>
		    <input type="hidden" name="manage_field_type" class="manage_field_type" value="profile_tabs">
		    <ul class="core uwp_form_extras">
			    <?php
			    $fields = $wpdb->get_results($wpdb->prepare("select * from  " . $extras_table_name . " where form_type = %s order by sort_order asc",array($form_type)));

			    if (!empty($fields)) {
				    foreach ($fields as $field) {
					    $result_str = $field;
					    $field_ins_upd = 'display';

					    $this->uwp_tabs_field_adminhtml($result_str, $field_ins_upd, false);
				    }
			    } ?>
		    </ul>
		    <?php
	    }

	    public function uwp_tabs_fields($form_type)
	    {
		    $fields = array();

		    $fields[] = array(
			    'field_type'   => 'standard',
			    'site_title'   => __('More Info','userswp'),
			    'field_icon'   => 'fas fa-info-circle',
			    'htmlvar_name'    => 'more_info',
		    );

		    $fields[] = array(
			    'field_type'   => 'standard',
			    'site_title'   => __('Posts','userswp'),
			    'field_icon'   => '',
			    'htmlvar_name'    => 'posts',
		    );

		    $fields[] = array(
			    'field_type'   => 'standard',
			    'site_title'   => __('Comments','userswp'),
			    'field_icon'   => 'fas fa-comments',
			    'htmlvar_name'    => 'comments',
		    );

		    return apply_filters('uwp_tabs_fields', $fields, $form_type);
	    }

	    public function uwp_tabs_extra_fields($fields, $form_type)
	    {
		    global $wpdb;

		    $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
		    $records = $wpdb->get_results("SELECT htmlvar_name, site_title, field_type, field_icon FROM " . $table_name . " WHERE form_type = 'account' AND is_public != '0' AND show_in LIKE '%[own_tab]%' ORDER BY sort_order ASC");

		    foreach ($records as $row) {
			    $fields[] = array(
				    'field_type' => $row->field_type,
				    'site_title' => $row->site_title,
				    'htmlvar_name' => $row->htmlvar_name,
				    'field_icon' => $row->field_icon
			    );
		    }
		    return $fields;
	    }

	    public function uwp_tabs_field_adminhtml($result_str, $field_ins_upd = '', $default = false, $request = array()){
		    global $wpdb;

		    $table_name = uwp_get_table_prefix() . 'uwp_form_fields';
		    $extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';

		    $cf = $result_str;
		    if (!is_object($cf) && (is_int($cf) || ctype_digit($cf))) {
			    $field_info = $wpdb->get_row($wpdb->prepare("select * from " . $extras_table_name . " where id= %d", array($cf)));
		    } elseif (is_object($cf)) {
			    //$field_info = $cf;
			    $result_str = $cf->id;
			    $field_info = $wpdb->get_row($wpdb->prepare("select * from " . $extras_table_name . " where id= %d", array((int)$cf->id)));
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
			    $account_field_info =  uwp_get_custom_field_info($field_info->site_htmlvar_name);
			    if (isset($account_field_info->site_title)) {
				    if ($account_field_info->field_type == 'fieldset') {
					    $field_site_name = __('Fieldset:', 'userswp') . ' ' . $account_field_info->site_title;
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

		    if (isset($request['htmlvar_name']) && $request['htmlvar_name'] != '') {
			    $htmlvar_name = esc_attr($request['htmlvar_name']);
		    } else {
			    $htmlvar_name = $field_info->site_htmlvar_name;
		    }

		    if (isset($htmlvar_name)) {
			    if (!is_object($field_info)) {
				    $field_info = new stdClass();
			    }
			    $field_info->field_icon = $wpdb->get_var(
				    $wpdb->prepare("SELECT field_icon FROM " . $table_name . " WHERE htmlvar_name = %s", array($htmlvar_name))
			    );
		    }

		    if (isset($field_info->field_icon) && (strpos($field_info->field_icon, 'fa fa-') !== false || strpos($field_info->field_icon, 'fas fa-') !== false)) {
			    $field_icon = '<i class="' . $field_info->field_icon . '" aria-hidden="true"></i>';
		    } elseif (isset($field_info->field_icon) && $field_info->field_icon) {
			    $field_icon = '<b style="background-image: url("' . $field_info->field_icon . '")"></b>';
		    } elseif (isset($field_info->field_type) && $field_info->field_type == 'fieldset') {
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

                                    <input type="button" class="button button-primary" name="save" id="save"
                                           value="<?php esc_attr_e('Save', 'userswp'); ?>"
                                           onclick="save_register_field('<?php echo $result_str; ?>', 'profile_tab')"/>
                                    <input type="button" name="delete" value="<?php esc_attr_e('Delete', 'userswp'); ?>"
                                           onclick="delete_register_field('<?php echo $result_str; ?>', '<?php echo $nonce; ?>','<?php echo $htmlvar_name ?>', 'profile_tab')"
                                           class="button"/>

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

				    $return = uwp_form_extras_field_order($field_ids, "profile_tabs");

				    if (is_array($return)) {
					    $return = json_encode($return);
				    }

				    echo $return;
			    }

			    /* ---- Show field form in admin ---- */
			    if ($field_action == 'new') {
				    $form_type = isset($_REQUEST['form_type']) ? sanitize_text_field($_REQUEST['form_type']) : '';
				    $fields = $this->uwp_tabs_fields($form_type);


				    $_REQUEST['site_field_id'] = isset($_REQUEST['field_id']) ? sanitize_text_field($_REQUEST['field_id']) : '';
				    $_REQUEST['is_default'] = '0';

				    if (!empty($fields)) {
					    foreach ($fields as $val) {
						    $val = stripslashes_deep($val);

						    $form_field_info = uwp_get_custom_field_info($val['htmlvar_name']);
						    if($form_field_info && !empty($form_field_info->data_type)){
							    $data_type = $form_field_info->data_type;
						    } else {
							    $data_type = $val['field_type'];
						    }
						    if ($val['htmlvar_name'] == $_REQUEST['htmlvar_name']) {
							    $_REQUEST['field_type'] = $val['field_type'];
							    $_REQUEST['site_title'] = $val['site_title'];
							    $_REQUEST['field_data_type'] = $data_type;
						    }
					    }
				    }

				    $htmlvar_name = isset($_REQUEST['htmlvar_name']) ? sanitize_text_field($_REQUEST['htmlvar_name']) : '';

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

				    $return = $this->uwp_tabs_field_save($_REQUEST);

				    if (is_int($return)) {
					    $lastid = $return;

					    $this->uwp_tabs_field_adminhtml($lastid, 'submit');
				    } else {
					    echo $return;
				    }
			    }
		    }
		    die();
        }

	    public function uwp_tabs_field_save($request_field = array())
	    {
		    global $wpdb;
		    $extras_table_name = uwp_get_table_prefix() . 'uwp_form_extras';

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

				    $user_meta_info = $wpdb->get_row(
					    $wpdb->prepare(
						    "select * from " . $extras_table_name . " where id = %d",
						    array($cf)
					    )
				    );

			    }

			    if ($form_type == '') $form_type = 'profile_tabs';


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

	    public function uwp_tabs_field_delete($field_id = '')
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

endif;


//return new UsersWP_Settings_Profile_Tabs();