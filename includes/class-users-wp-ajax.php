<?php
class Users_WP_Ajax {

    private $form_builder;

    public function __construct($form_builder) {
        $this->form_builder = $form_builder;
    }

    public function handler()
    {
        if ((isset($_REQUEST['uwp_ajax']) && $_REQUEST['uwp_ajax'] == 'admin_ajax') || isset($_REQUEST['create_field']) || isset($_REQUEST['sort_create_field'])) {
            if (current_user_can('manage_options')) {
                if (isset($_REQUEST['create_field'])) {
                    $this->create_field($_REQUEST);
                    $this->uwp_die();
                }
            } else {
                $login_page = esc_attr( get_option('uwp_login_page', false));
                if ($login_page) {
                    wp_redirect(get_permalink($login_page));
                } else {
                    wp_redirect(home_url('/'));
                }
                $this->uwp_die();
            }
        }
    }

    public function uwp_die( $message = '', $title = '', $status = 400 ) {
        add_filter( 'wp_die_ajax_handler', '_uwp_die_handler', 10, 3 );
        add_filter( 'wp_die_handler', '_uwp_die_handler', 10, 3 );
        wp_die( $message, $title, array( 'response' => $status ));
    }

    public function create_field($data) {

        $form_type = isset($data['form_type']) ? sanitize_text_field($data['form_type']) : '';
        $field_type = isset($data['field_type']) ? sanitize_text_field($data['field_type']) : '';
        $field_type_key = isset($data['field_type_key']) ? sanitize_text_field($data['field_type_key']) : '';
        $field_action = isset($data['field_ins_upd']) ? sanitize_text_field($data['field_ins_upd']) : '';
        $field_id = isset($data['field_id']) ? sanitize_text_field($data['field_id']) : '';

        $field_id = $field_id != '' ? trim($field_id, '_') : $field_id;

        $field_ids = array();
        if (!empty($data['licontainer']) && is_array($data['licontainer'])) {
            foreach ($data['licontainer'] as $lic_id) {
                $field_ids[] = sanitize_text_field($lic_id);
            }
        }

        /* ------- check nonce field ------- */
        if (isset($data['update']) && $data['update'] == "update" && isset($data['create_field']) && isset($data['manage_field_type']) && $data['manage_field_type'] == 'custom_fields') {
            echo $this->form_builder->uwp_set_field_order($field_ids);
        }

        /* ---- Show field form in admin ---- */
        if ($field_type != '' && $field_id != '' && $field_action == 'new' && isset($data['create_field']) && isset($data['manage_field_type']) && $data['manage_field_type'] == 'custom_fields') {
            $this->form_builder->uwp_custom_field_adminhtml($field_type, $field_id, $field_action,$field_type_key, $form_type);
        }


        /* ---- Delete field ---- */
        if ($field_id != '' && $field_action == 'delete' && isset($data['_wpnonce']) && isset($data['create_field']) && isset($data['manage_field_type']) && $data['manage_field_type'] == 'custom_fields') {
            if (!wp_verify_nonce($data['_wpnonce'], 'custom_fields_' . $field_id))
                return;

            echo $this->form_builder->uwp_custom_field_delete($field_id);
        }

        /* ---- Save field  ---- */
        if ($field_id != '' && $field_action == 'submit' && isset($data['_wpnonce']) && isset($data['create_field']) && isset($data['manage_field_type']) && $data['manage_field_type'] == 'custom_fields') {
            if (!wp_verify_nonce($data['_wpnonce'], 'custom_fields_' . $field_id))
                return;

            foreach ($data as $pkey => $pval) {
                if (is_array($data[$pkey])) {
                    $tags = 'skip_field';
                } else {
                    $tags = '';
                }

                if ($tags != 'skip_field') {
                    $data[$pkey] = strip_tags($data[$pkey], $tags);
                }
            }

            $return = $this->form_builder->uwp_custom_field_save($data);

            if (is_int($return)) {
                $lastid = $return;
                $this->form_builder->uwp_custom_field_adminhtml($field_type, $lastid, 'submit',$field_type_key, $form_type);
            } else {
                echo $return;
            }
        }

    }
}