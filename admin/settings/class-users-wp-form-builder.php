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

    private $users_wp;

    protected $loader;

    public function __construct() {

    }

    public function uwp_form_builder()
    {
        ?>
        <div class="uwp-panel-heading">
            <h3><?php echo apply_filters('uwp_form_builder_panel_head', '');?></h3>
        </div>

        <div id="uwp_form_builder_container" class="clearfix">
            <div class="uwp-form-builder-frame">
                <div class="uwp-side-sortables" id="uwp-available-fields">
                    <h3 class="hndle">
                    <span>
                        <?php echo apply_filters('uwp_form_builder_available_fields_head', __('Add new form field', 'users-wp')); ?>
                    </span>
                    </h3>

                    <p>
                        <?php
                        $note = sprintf(__('Click on any box below to add a field of that type on add listing form. You must be use a fieldset to group your fields.', 'users-wp'));
                        echo apply_filters('uwp_form_builder_available_fields_note', $note);
                        ?>
                    </p>

                    <h3>
                        <?php _e('Setup New Field','users-wp'); ?>
                    </h3>

                    <div class="inside">
                        <div id="uwp-form-builder-tab" class="uwp-tabs-panel">
                            <?php do_action('uwp_manage_available_fields'); ?>
                        </div>
                    </div>

                    <h3>
                        <?php _e('Predefined Fields','users-wp'); ?>
                    </h3>

                    <div class="inside">
                        <div id="uwp-form-builder-tab" class="uwp-tabs-panel">
                            <?php do_action('uwp_manage_available_fields_predefined'); ?>
                        </div>
                    </div>

                    <h3>
                        <?php _e('Custom Fields','users-wp'); ?>
                    </h3>

                    <div class="inside">
                        <div id="uwp-form-builder-tab" class="uwp-tabs-panel">
                            <?php do_action('uwp_manage_available_fields_custom'); ?>
                        </div>
                    </div>

                </div>


                <div class="uwp-side-sortables" id="uwp-selected-fields">

                    <h3 class="hndle">
                        <span>
                            <?php
                            $title = __('List of fields those will appear on add new listing form', 'users-wp');
                            echo apply_filters('uwp_form_builder_selected_fields_head', $title); ?>
                        </span>
                    </h3>

                    <p>
                        <?php
                        $note = __('Click to expand and view field related settings. You may drag and drop to arrange fields order on add listing form too.', 'users-wp');
                        echo apply_filters('uwp_form_builder_selected_fields_note', $note); ?>
                    </p>

                    <div class="inside">
                        <div id="uwp-form-builder-tab" class="uwp-tabs-panel">
                            <?php do_action('uwp_manage_selected_fields'); ?>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <?php
    }

    public function uwp_custom_available_fields($type='')
    {
        $form_type = (isset($_REQUEST['form_type']) && $_REQUEST['form_type'] != '') ? sanitize_text_field($_REQUEST['form_type']) : 'register';
        ?>
        <input type="hidden" name="form_type" id="form_type" value="<?php echo $form_type;?>"/>

        <?php
        if($type=='predefined'){
            $fields = $this->uwp_custom_fields_predefined($form_type);
        }elseif($type=='custom'){
            $fields = $this->uwp_custom_fields_custom($form_type);
        }else{
            $fields = $this->uwp_custom_fields($form_type);
            ?>
            <ul class="full gd-cf-tooltip-wrap">
                <li>
                    <div class="uwp-tooltip">
                        <?php _e('This adds a section separator with a title.', 'users-wp');?>
                    </div>
                    <a id="uwp-fieldset"
                       class="uwp-draggable-form-items uwp-fieldset"
                       href="javascript:void(0);"
                       data-field-custom-type=""
                       data-field-type="fieldset"
                       data-field-type-key="fieldset">

                        <i class="fa fa-long-arrow-left " aria-hidden="true"></i>
                        <i class="fa fa-long-arrow-right " aria-hidden="true"></i>
                        <?php _e('Fieldset (section separator)', 'users-wp');?>
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
            _e('There are no custom fields here yet.', 'users-wp');
        }
        ?>
        </ul>

        <?php

    }

    public function uwp_custom_fields_predefined($type='') {
        $custom_fields = array();

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
                'class' =>  'gd-text',
                'icon'  =>  'fa fa-minus',
                'name'  =>  __('Text', 'geodirectory'),
                'description' =>  __('Add any sort of text field, text or numbers', 'geodirectory')
            ),
            'datepicker' => array(
                'field_type'  =>  'datepicker',
                'class' =>  'gd-datepicker',
                'icon'  =>  'fa fa-calendar',
                'name'  =>  __('Date', 'geodirectory'),
                'description' =>  __('Adds a date picker.', 'geodirectory')
            ),
            'textarea' => array(
                'field_type'  =>  'textarea',
                'class' =>  'gd-textarea',
                'icon'  =>  'fa fa-bars',
                'name'  =>  __('Textarea', 'geodirectory'),
                'description' =>  __('Adds a textarea', 'geodirectory')
            ),
            'time' => array(
                'field_type'  =>  'time',
                'class' =>  'gd-time',
                'icon' =>  'fa fa-clock-o',
                'name'  =>  __('Time', 'geodirectory'),
                'description' =>  __('Adds a time picker', 'geodirectory')
            ),
            'checkbox' => array(
                'field_type'  =>  'checkbox',
                'class' =>  'gd-checkbox',
                'icon' =>  'fa fa-check-square-o',
                'name'  =>  __('Checkbox', 'geodirectory'),
                'description' =>  __('Adds a checkbox', 'geodirectory')
            ),
            'phone' => array(
                'field_type'  =>  'phone',
                'class' =>  'gd-phone',
                'icon' =>  'fa fa-phone',
                'name'  =>  __('Phone', 'geodirectory'),
                'description' =>  __('Adds a phone input', 'geodirectory')
            ),
            'radio' => array(
                'field_type'  =>  'radio',
                'class' =>  'gd-radio',
                'icon' =>  'fa fa-dot-circle-o',
                'name'  =>  __('Radio', 'geodirectory'),
                'description' =>  __('Adds a radio input', 'geodirectory')
            ),
            'email' => array(
                'field_type'  =>  'email',
                'class' =>  'gd-email',
                'icon' =>  'fa fa-envelope-o',
                'name'  =>  __('Email', 'geodirectory'),
                'description' =>  __('Adds a email input', 'geodirectory')
            ),
            'select' => array(
                'field_type'  =>  'select',
                'class' =>  'gd-select',
                'icon' =>  'fa fa-caret-square-o-down',
                'name'  =>  __('Select', 'geodirectory'),
                'description' =>  __('Adds a select input', 'geodirectory')
            ),
            'multiselect' => array(
                'field_type'  =>  'multiselect',
                'class' =>  'gd-multiselect',
                'icon' =>  'fa fa-caret-square-o-down',
                'name'  =>  __('Multi Select', 'geodirectory'),
                'description' =>  __('Adds a multiselect input', 'geodirectory')
            ),
            'url' => array(
                'field_type'  =>  'url',
                'class' =>  'gd-url',
                'icon' =>  'fa fa-link',
                'name'  =>  __('URL', 'geodirectory'),
                'description' =>  __('Adds a url input', 'geodirectory')
            ),
            'html' => array(
                'field_type'  =>  'html',
                'class' =>  'gd-html',
                'icon' =>  'fa fa-code',
                'name'  =>  __('HTML', 'geodirectory'),
                'description' =>  __('Adds a html input textarea', 'geodirectory')
            ),
            'file' => array(
                'field_type'  =>  'file',
                'class' =>  'gd-file',
                'icon' =>  'fa fa-file',
                'name'  =>  __('File Upload', 'geodirectory'),
                'description' =>  __('Adds a file input', 'geodirectory')
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
}