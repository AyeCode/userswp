<?php
/**
 * Setting fields callback functions
 *
 * This class defines all code necessary to display setting field input.
 *
 * @link       http://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    userswp
 */
class UsersWP_Callback {

    public function uwp_missing_callback($args) {
        printf(
            __( 'The callback function used for the %s setting is missing.', 'userswp' ),
            '<strong>' . $args['id'] . '</strong>'
        );
    }

    public function uwp_select_callback($args) {

        global $uwp_options;

        $global = isset( $args['global'] ) ? $args['global'] : true;
        if ($global) {
            if ( isset( $uwp_options[ $args['id'] ] ) ) {
                $value = $uwp_options[ $args['id'] ];
            } else {
                $value = isset( $args['std'] ) ? $args['std'] : '';
            }
        } else {
            if ( isset( $args['value'] ) ) {
                $value = $args['value'];
            } else {
                $value = isset( $args['std'] ) ? $args['std'] : '';
            }
        }


        if ( isset( $args['placeholder'] ) ) {
            $placeholder = $args['placeholder'];
        } else {
            $placeholder = '';
        }

        if ( isset( $args['chosen'] ) ) {
            $chosen = ($args['multiple'] ? '[]" multiple="multiple" class="uwp_chosen_select" style="height:auto"' : "'");
        } else {
            $chosen = '';
        }

        $html = '<select id="uwp_settings[' . $args['id'] . ']" name="uwp_settings[' . $args['id'] . ']' . $chosen . ' data-placeholder="' . $placeholder . '" />';

        foreach ( $args['options'] as $option => $name ) {
            if (is_array($value)) {
                if (in_array($option, $value)) {
                    $selected = 'selected="selected"';
                } else {
                    $selected = '';
                }
            } else {
                $selected = selected( $option, $value, false );
            }
            $html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
        }

        $html .= '</select>';
        $html .= '<label for="uwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

        echo $html;
    }

    public function uwp_select_order_callback($args) {
        
        global $uwp_options;

        $global = isset( $args['global'] ) ? $args['global'] : true;
        if ($global) {
            if ( isset( $uwp_options[ $args['id'] ] ) ) {
                $value = $uwp_options[ $args['id'] ];
            } else {
                $value = isset( $args['std'] ) ? $args['std'] : '';
            }
        } else {
            if ( isset( $args['value'] ) ) {
                $value = $args['value'];
            } else {
                $value = isset( $args['std'] ) ? $args['std'] : '';
            }
        }


        if ( isset( $args['placeholder'] ) ) {
            $placeholder = $args['placeholder'];
        } else {
            $placeholder = '';
        }

        if ( isset( $args['chosen'] ) ) {
            $chosen = ($args['multiple'] ? '[]" multiple="multiple" class="uwp_chosen_select" style="height:auto"' : "'");
        } else {
            $chosen = '';
        }

        //print_r($args);

        $html = '<select id="uwp_dummy_' . $args['id'] . '" name="uwp_dummy_settings[' . $args['id'] . ']' . $chosen . ' data-placeholder="' . $placeholder . '" />';

        foreach ( $args['options'] as $option => $name ) {
            if (is_array($value)) {
                if (in_array($option, $value)) {
                    $selected = 'selected="selected"';
                } else {
                    $selected = '';
                }
            } else {
                $selected = selected( $option, $value, false );
            }
            $html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
        }

        $html .= '</select>';
        $html .= '<label for="uwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

        // The hidden select with correct orders
        $html .= '<select style="visibility: hidden;
    height: 0px;
    padding: 0;
    margin: 0;
    float: left;" id="' . $args['id'] . '" name="uwp_settings[' . $args['id'] . '][]" multiple="multiple" data-placeholder="' . $placeholder . '" />';

        if (is_array($value)) {
            foreach ( $value as $option ) {
                if ( in_array( $option,array_keys($args['options']) ) ) {
                    $selected = 'selected="selected"';
                    $html .= '<option value="' . $option . '" ' . $selected . '>' . $args['options'][$option] . '</option>';
                }
            }
        }

        $html .= '</select>';

        echo $html;


        ?>
        <script>
            jQuery(document).ready(function() {
                setTimeout(function(){

                    // Set the current order on load
                    var current_order = jQuery('#<?php echo $args['id'];?>').val();

                    if(current_order){
                        current_order = jQuery.unique( current_order );
                        ChosenOrder.setSelectionOrder(jQuery("#uwp_dummy_<?php echo $args['id'];?>"), current_order);
                    }


                    // on order change update the hidden real values
                    jQuery("#uwp_dummy_<?php echo $args['id'];?>").chosen().change(function () {
                        console.log('changed');

                        setTimeout(function(){ // trigers before the item is removed so we add a slight delay
                            var selection = ChosenOrder.getSelectionOrder(jQuery("#uwp_dummy_<?php echo $args['id'];?>"));
                            console.log(selection);
                            jQuery('#<?php echo $args['id'];?>').find('option').remove().end();

                            jQuery.each( selection, function( key, value ) {
                                jQuery('#<?php echo $args['id'];?>').append('<option value="'+value+'" selected>'+value+'</option>')
                            });

                        }, 50);

                    });

                }, 100);
            });
        </script>

        <?php
    }

    public function uwp_text_callback( $args ) {
        global $uwp_options;

        $global = isset( $args['global'] ) ? $args['global'] : true;
        if ($global) {
            if ( isset( $uwp_options[ $args['id'] ] ) ) {
                $value = $uwp_options[ $args['id'] ];
            } else {
                $value = isset( $args['std'] ) ? $args['std'] : '';
            }
        } else {
            if ( isset( $args['value'] ) ) {
                $value = $args['value'];
            } else {
                $value = isset( $args['std'] ) ? $args['std'] : '';
            }
        }

        if ( isset( $args['faux'] ) && true === $args['faux'] ) {
            $args['readonly'] = true;
            $value = isset( $args['std'] ) ? $args['std'] : '';
            $name  = '';
        } else {
            $name = 'name="uwp_settings[' . $args['id'] . ']"';
        }

        $readonly = $args['readonly'] === true ? ' readonly="readonly"' : '';
        $size     = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
        $html     = '<input type="text" class="' . $size . '-text" id="uwp_settings[' . $args['id'] . ']"' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"' . $readonly . '/>';
        $html    .= '<label for="uwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

        echo $html;
    }

    public function uwp_textarea_callback( $args ) {
        global $uwp_options;

        $global = isset( $args['global'] ) ? $args['global'] : true;
        if ($global) {
            if ( isset( $uwp_options[ $args['id'] ] ) ) {
                $value = $uwp_options[ $args['id'] ];
            } else {
                $value = isset( $args['std'] ) ? $args['std'] : '';
            }
        } else {
            if ( isset( $args['value'] ) ) {
                $value = $args['value'];
            } else {
                $value = isset( $args['std'] ) ? $args['std'] : '';
            }
        }

        $html = '<textarea class="large-text" cols="50" rows="5" id="uwp_settings[' . $args['id'] . ']" name="uwp_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
        $html .= '<label for="uwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

        echo $html;
    }

    public function uwp_checkbox_callback( $args ) {
        global $uwp_options;

        if ( isset( $args['faux'] ) && true === $args['faux'] ) {
            $name = '';
        } else {
            $name = 'name="uwp_settings[' . $args['id'] . ']"';
        }

        $checked = isset( $uwp_options[ $args['id'] ] ) ? checked( 1, $uwp_options[ $args['id'] ], false ) : '';
        $html = '<input type="checkbox" id="uwp_settings[' . $args['id'] . ']"' . $name . ' value="1" ' . $checked . '/>';
        $html .= '<label for="uwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

        echo $html;
    }

    public function uwp_number_callback( $args ) {
        global $uwp_options;

        $global = isset( $args['global'] ) ? $args['global'] : true;
        if ($global) {
            if ( isset( $uwp_options[ $args['id'] ] ) ) {
                $value = $uwp_options[ $args['id'] ];
            } else {
                $value = isset( $args['std'] ) ? $args['std'] : '';
            }
        } else {
            if ( isset( $args['value'] ) ) {
                $value = $args['value'];
            } else {
                $value = isset( $args['std'] ) ? $args['std'] : '';
            }
        }

        if ( isset( $args['faux'] ) && true === $args['faux'] ) {
            $args['readonly'] = true;
            $value = isset( $args['std'] ) ? $args['std'] : '';
            $name  = '';
        } else {
            $name = 'name="uwp_settings[' . $args['id'] . ']"';
        }

        $max  = isset( $args['max'] ) ? $args['max'] : 999999;
        $min  = isset( $args['min'] ) ? $args['min'] : 0;
        $step = isset( $args['step'] ) ? $args['step'] : 1;

        $size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
        $html = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . $size . '-text" id="uwp_settings[' . $args['id'] . ']" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"/>';
        $html .= '<label for="uwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

        echo $html;
    }

    public function uwp_info_callback( $args ) {
        echo $args['desc'];
    }

    public function uwp_media_callback( $args ) {
        global $uwp_options;

        $global = isset( $args['global'] ) ? $args['global'] : true;
        if ($global) {
            if ( isset( $uwp_options[ $args['id'] ] ) ) {
                $value = $uwp_options[ $args['id'] ];
            } else {
                $value = isset( $args['std'] ) ? $args['std'] : '';
            }
        } else {
            if ( isset( $args['value'] ) ) {
                $value = $args['value'];
            } else {
                $value = isset( $args['std'] ) ? $args['std'] : '';
            }
        }
        wp_enqueue_media();
        if(isset($value) && $value > 0){
            $image = wp_get_attachment_url($value);
            $is_default = false;
        } else {
            $image = USERSWP_PLUGIN_URL."/public/assets/images/no_thumb.png";
            $is_default = true;
        }
        ?>
        <div class="uwp_media_input">
            <input type="hidden" id="uwp_settings[<?php echo $args['id']; ?>]" class="uwp_img_url" name="uwp_settings[<?php echo $args['id']; ?>]" value="<?php echo $value; ?>" />
            <input id="uwp_upload_btn" type="button" class="button uwp_upload_btn" value="<?php _e( 'Upload', 'wptuts' ); ?>" /><br><br>
            <input id="uwp_remove_btn" type="button" class="button uwp_remove_btn" value="<?php _e( 'Remove', 'wptuts' ); ?>" style="<?php if($is_default){ echo "display:none;"; } ?>" />
        </div>
        <div class="uwp_media_preview">
            <img data-src="<?php echo USERSWP_PLUGIN_URL."public/assets/images/banner.png"; ?>" src="<?php echo $image; ?>" width="100px" height="100px" />
        </div>
        <label for="uwp_settings[<?php echo $args['id']; ?>]"><?php echo $args['desc']; ?></label>
        <?php
    }

}