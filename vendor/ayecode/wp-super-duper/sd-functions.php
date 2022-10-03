<?php
/**
 * A file for common functions.
 */

/**
 * Return an array of global $pagenow page names that should be used to exclude register_widgets.
 *
 * Used to block the loading of widgets on certain wp-admin pages to save on memory.
 *
 * @return mixed|void
 */
function sd_pagenow_exclude() {
	return apply_filters( 'sd_pagenow_exclude', array(
		'upload.php',
		'edit-comments.php',
		'edit-tags.php',
		'index.php',
		'media-new.php',
		'options-discussion.php',
		'options-writing.php',
		'edit.php',
		'themes.php',
		'users.php',
	) );
}


/**
 * Return an array of widget class names that should be excluded.
 *
 * Used to conditionally load widgets code.
 *
 * @return mixed|void
 */
function sd_widget_exclude() {
	return apply_filters( 'sd_widget_exclude', array() );
}


/**
 * A helper function for margin inputs.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function sd_get_margin_input( $type = 'mt', $overwrite = array(), $include_negatives = true ) {
	$options = array(
		""     => __( 'None' ),
		"auto" => __( 'auto' ),
		"0"    => "0",
		"1"    => "1",
		"2"    => "2",
		"3"    => "3",
		"4"    => "4",
		"5"    => "5",
	);

	if ( $include_negatives ) {
		$options['n1'] = '-1';
		$options['n2'] = '-2';
		$options['n3'] = '-3';
		$options['n4'] = '-4';
		$options['n5'] = '-5';
	}

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Margin top' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( "Wrapper Styles", "geodirectory" ),
//		'device_type' => 'Desktop',
	);

	// title
	if ( $type == 'mt' ) {
		$defaults['title'] = __( 'Margin top' );
		$defaults['icon']  = 'box-top';
		$defaults['row']   = array(
			'title' => __( 'Margins' ),
			'key'   => 'wrapper-margins',
			'open'  => true,
			'class' => 'text-center',
		);
	} elseif ( $type == 'mr' ) {
		$defaults['title'] = __( 'Margin right' );
		$defaults['icon']  = 'box-right';
		$defaults['row']   = array(
			'key' => 'wrapper-margins',
		);
	} elseif ( $type == 'mb' ) {
		$defaults['title'] = __( 'Margin bottom' );
		$defaults['icon']  = 'box-bottom';
		$defaults['row']   = array(
			'key' => 'wrapper-margins',
		);
	} elseif ( $type == 'ml' ) {
		$defaults['title'] = __( 'Margin left' );
		$defaults['icon']  = 'box-left';
		$defaults['row']   = array(
			'key'   => 'wrapper-margins',
			'close' => true,
		);
	}

	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}

/**
 * A helper function for padding inputs.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function sd_get_padding_input( $type = 'pt', $overwrite = array() ) {
	$options = array(
		""  => __( 'None' ),
		"0" => "0",
		"1" => "1",
		"2" => "2",
		"3" => "3",
		"4" => "4",
		"5" => "5",
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Padding top' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( "Wrapper Styles", "geodirectory" )
	);

	// title
	if ( $type == 'pt' ) {
		$defaults['title'] = __( 'Padding top' );
		$defaults['icon']  = 'box-top';
		$defaults['row']   = array(
			'title' => __( 'Padding' ),
			'key'   => 'wrapper-padding',
			'open'  => true,
			'class' => 'text-center',
		);
	} elseif ( $type == 'pr' ) {
		$defaults['title'] = __( 'Padding right' );
		$defaults['icon']  = 'box-right';
		$defaults['row']   = array(
			'key' => 'wrapper-padding',
		);
	} elseif ( $type == 'pb' ) {
		$defaults['title'] = __( 'Padding bottom' );
		$defaults['icon']  = 'box-bottom';
		$defaults['row']   = array(
			'key' => 'wrapper-padding',
		);
	} elseif ( $type == 'pl' ) {
		$defaults['title'] = __( 'Padding left' );
		$defaults['icon']  = 'box-left';
		$defaults['row']   = array(
			'key'   => 'wrapper-padding',
			'close' => true,

		);
	}

	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}

/**
 * A helper function for border inputs.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function sd_get_border_input( $type = 'border', $overwrite = array() ) {

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Border' ),
		'options'  => array(),
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( "Wrapper Styles", "geodirectory" )
	);

	// title
	if ( $type == 'rounded' ) {
		$defaults['title']   = __( 'Border radius type' );
		$defaults['options'] = array(
			''               => __( "Default", "geodirectory" ),
			'rounded'        => 'rounded',
			'rounded-top'    => 'rounded-top',
			'rounded-right'  => 'rounded-right',
			'rounded-bottom' => 'rounded-bottom',
			'rounded-left'   => 'rounded-left',
			'rounded-circle' => 'rounded-circle',
			'rounded-pill'   => 'rounded-pill',
			'rounded-0'      => 'rounded-0',
		);
	} elseif ( $type == 'rounded_size' ) {
		$defaults['title']   = __( 'Border radius size' );
		$defaults['options'] = array(
			''   => __( "Default", "geodirectory" ),
			'sm' => __( "Small", "geodirectory" ),
			'lg' => __( "Large", "geodirectory" ),
		);
	} elseif ( $type == 'type' ) {
		$defaults['title']   = __( 'Border type' );
		$defaults['options'] = array(
			''              => __( "None", "geodirectory" ),
			'border'        => __( "Full", "geodirectory" ),
			'border-top'    => __( "Top", "geodirectory" ),
			'border-bottom' => __( "Bottom", "geodirectory" ),
			'border-left'   => __( "Left", "geodirectory" ),
			'border-right'  => __( "Right", "geodirectory" ),
		);
	} else {
		$defaults['title']   = __( 'Border color' );
		$defaults['options'] = array(
			                       ''  => __( "Default", "geodirectory" ),
			                       '0' => __( "None", "geodirectory" ),
		                       ) + sd_aui_colors();
	}

	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}

/**
 * A helper function for shadow inputs.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function sd_get_shadow_input( $type = 'shadow', $overwrite = array() ) {
	$options = array(
		""          => __( 'None' ),
		"shadow-sm" => __( 'Small' ),
		"shadow"    => __( 'Regular' ),
		"shadow-lg" => __( 'Large' ),
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Shadow' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( "Wrapper Styles", "geodirectory" )
	);


	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}

/**
 * A helper function for background inputs.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function sd_get_background_input( $type = 'bg', $overwrite = array() ) {
	$options = array(
		           ''            => __( "None", "geodirectory" ),
		           'transparent' => __( "Transparent", "geodirectory" ),
	           ) + sd_aui_colors();

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Background color' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( "Wrapper Styles", "geodirectory" )
	);


	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}

/**
 * A helper function for a set of background inputs.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function sd_get_background_inputs( $type = 'bg', $overwrite = array(), $overwrite_color = array(), $overwrite_gradient = array(), $overwrite_image = array() ) {
	$options = array(
		           ''            => __( "None", "geodirectory" ),
		           'transparent' => __( "Transparent", "geodirectory" ),
	           ) + sd_aui_colors()
	           + array(
		           'custom-color'    => __( "Custom Color", "geodirectory" ),
		           'custom-gradient' => __( "Custom Gradient", "geodirectory" ),
//		           'custom-image' => __("Custom Image","geodirectory"),
	           );

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Background Color' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( "Background", "geodirectory" )
	);


	if ( $overwrite !== false ) {
		$input[ $type ] = wp_parse_args( $overwrite, $defaults );
	}


	if ( $overwrite_color !== false ) {
		$input[ $type . '_color' ] = wp_parse_args( $overwrite_color, array(
			'type'            => 'color',
			'title'           => __( 'Custom color' ),
			'placeholder'     => '',
			'default'         => '#0073aa',
			'desc_tip'        => true,
			'group'           => __( "Background" ),
			'element_require' => '[%' . $type . '%]=="custom-color"'
		) );
	}


	if ( $overwrite_gradient !== false ) {
		$input[ $type . '_gradient' ] = wp_parse_args( $overwrite_gradient, array(
			'type'            => 'gradient',
			'title'           => __( 'Custom gradient' ),
			'placeholder'     => '',
			'default'         => 'linear-gradient(135deg,rgba(6,147,227,1) 0%,rgb(155,81,224) 100%)',
			'desc_tip'        => true,
			'group'           => __( "Background" ),
			'element_require' => '[%' . $type . '%]=="custom-gradient"'
		) );
	}

	if ( $overwrite_image !== false ) {

		$input[ $type . '_image_fixed' ] = array(
			'type'            => 'checkbox',
			'title'           => __( 'Fixed background' ),
			'default'         => '',
			'desc_tip'        => true,
			'group'           => __( "Background" ),
			'element_require' => '( [%' . $type . '%]=="" || [%' . $type . '%]=="custom-color" || [%' . $type . '%]=="custom-gradient" || [%' . $type . '%]=="transparent" )'

		);

		$input[ $type . '_image_use_featured' ] = array(
			'type'            => 'checkbox',
			'title'           => __( 'Use featured image' ),
			'default'         => '',
			'desc_tip'        => true,
			'group'           => __( "Background" ),
			'element_require' => '( [%' . $type . '%]=="" || [%' . $type . '%]=="custom-color" || [%' . $type . '%]=="custom-gradient" || [%' . $type . '%]=="transparent" )'

		);


		$input[ $type . '_image' ] = wp_parse_args( $overwrite_image, array(
			'type'        => 'image',
			'title'       => __( 'Custom image' ),
			'placeholder' => '',
			'default'     => '',
			'desc_tip'    => true,
			'group'       => __( "Background" ),
//			'element_require' => ' ![%' . $type . '_image_use_featured%] '
		) );

		$input[ $type . '_image_id' ] = wp_parse_args( $overwrite_image, array(
			'type'        => 'hidden',
			'hidden_type' => 'number',
			'title'       => '',
			'placeholder' => '',
			'default'     => '',
			'group'       => __( "Background" ),
		) );

		$input[ $type . '_image_xy' ] = wp_parse_args( $overwrite_image, array(
			'type'        => 'image_xy',
			'title'       => '',
			'placeholder' => '',
			'default'     => '',
			'group'       => __( "Background" ),
		) );
	}

	return $input;
}

/**
 * A helper function for a set of inputs for the shape divider.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function sd_get_shape_divider_inputs( $type = 'sd', $overwrite = array(), $overwrite_color = array(), $overwrite_gradient = array(), $overwrite_image = array() ) {

	$options = array(
		''                      => __( "None" ),
		'mountains'             => __( "Mountains" ),
		'drops'                 => __( "Drops" ),
		'clouds'                => __( "Clouds" ),
		'zigzag'                => __( "Zigzag" ),
		'pyramids'              => __( "Pyramids" ),
		'triangle'              => __( "Triangle" ),
		'triangle-asymmetrical' => __( "Triangle Asymmetrical" ),
		'tilt'                  => __( "Tilt" ),
		'opacity-tilt'          => __( "Opacity Tilt" ),
		'opacity-fan'           => __( "Opacity Fan" ),
		'curve'                 => __( "Curve" ),
		'curve-asymmetrical'    => __( "Curve Asymmetrical" ),
		'waves'                 => __( "Waves" ),
		'wave-brush'            => __( "Wave Brush" ),
		'waves-pattern'         => __( "Waves Pattern" ),
		'arrow'                 => __( "Arrow" ),
		'split'                 => __( "Split" ),
		'book'                  => __( "Book" ),
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Type' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( "Shape Divider" ),
	);


	$input[ $type ] = wp_parse_args( $overwrite, $defaults );


	$input[ $type . '_notice' ] = array(
		'type'            => 'notice',
		'desc'            => __( 'Parent element must be position `relative`' ),
		'status'          => 'warning',
		'group'           => __( "Shape Divider" ),
		'element_require' => '[%' . $type . '%]!=""',
	);


	$input[ $type . '_position' ] = wp_parse_args( $overwrite_color, array(
		'type'            => 'select',
		'title'           => __( 'Position' ),
		'options'         => array(
			'top'    => __( 'Top' ),
			'bottom' => __( 'Bottom' ),
			//'left'   => __('Left'),
			//'right'   => __('Right'),
		),
		'desc_tip'        => true,
		'group'           => __( "Shape Divider" ),
		'element_require' => '[%' . $type . '%]!=""'
	) );

	$options = array(
		           ''            => __( "None" ),
		           'transparent' => __( "Transparent" ),
	           ) + sd_aui_colors()
	           + array(
		           'custom-color' => __( "Custom Color" ),
	           );

	$input[ $type . '_color' ] = wp_parse_args( $overwrite_color, array(
		'type'            => 'select',
		'title'           => __( 'Color' ),
		'options'         => $options,
		'desc_tip'        => true,
		'group'           => __( "Shape Divider" ),
		'element_require' => '[%' . $type . '%]!=""'
	) );

	$input[ $type . '_custom_color' ] = wp_parse_args( $overwrite_color, array(
		'type'            => 'color',
		'title'           => __( 'Custom color' ),
		'placeholder'     => '',
		'default'         => '#0073aa',
		'desc_tip'        => true,
		'group'           => __( "Shape Divider" ),
		'element_require' => '[%' . $type . '_color%]=="custom-color" && [%' . $type . '%]!=""'
	) );

	$input[ $type . '_width' ] = wp_parse_args( $overwrite_gradient, array(
		'type'              => 'range',
		'title'             => __( 'Width' ),
		'placeholder'       => '',
		'default'           => '200',
		'desc_tip'          => true,
		'custom_attributes' => array(
			'min' => 100,
			'max' => 300,
		),
		'group'             => __( "Shape Divider" ),
		'element_require'   => '[%' . $type . '%]!=""'
	) );

	$input[ $type . '_height' ] = array(
		'type'              => 'range',
		'title'             => __( 'Height' ),
		'default'           => '100',
		'desc_tip'          => true,
		'custom_attributes' => array(
			'min' => 0,
			'max' => 500,
		),
		'group'             => __( "Shape Divider" ),
		'element_require'   => '[%' . $type . '%]!=""'
	);

	$requires = array(
		'mountains'             => array( 'flip' ),
		'drops'                 => array( 'flip', 'invert' ),
		'clouds'                => array( 'flip', 'invert' ),
		'zigzag'                => array(),
		'pyramids'              => array( 'flip', 'invert' ),
		'triangle'              => array( 'invert' ),
		'triangle-asymmetrical' => array( 'flip', 'invert' ),
		'tilt'                  => array( 'flip' ),
		'opacity-tilt'          => array( 'flip' ),
		'opacity-fan'           => array(),
		'curve'                 => array( 'invert' ),
		'curve-asymmetrical'    => array( 'flip', 'invert' ),
		'waves'                 => array( 'flip', 'invert' ),
		'wave-brush'            => array( 'flip' ),
		'waves-pattern'         => array( 'flip' ),
		'arrow'                 => array( 'invert' ),
		'split'                 => array( 'invert' ),
		'book'                  => array( 'invert' ),
	);

	$input[ $type . '_flip' ] = array(
		'type'            => 'checkbox',
		'title'           => __( 'Flip' ),
		'default'         => '',
		'desc_tip'        => true,
		'group'           => __( "Shape Divider" ),
		'element_require' => sd_get_element_require_string( $requires, 'flip', 'sd' )
	);

	$input[ $type . '_invert' ] = array(
		'type'            => 'checkbox',
		'title'           => __( 'Invert' ),
		'default'         => '',
		'desc_tip'        => true,
		'group'           => __( "Shape Divider" ),
		'element_require' => sd_get_element_require_string( $requires, 'invert', 'sd' )
	);

	$input[ $type . '_btf' ] = array(
		'type'            => 'checkbox',
		'title'           => __( 'Bring to front' ),
		'default'         => '',
		'desc_tip'        => true,
		'group'           => __( "Shape Divider" ),
		'element_require' => '[%' . $type . '%]!=""'

	);


	return $input;
}

/**
 * Get the element require sting.
 *
 * @param $args
 * @param $key
 * @param $type
 *
 * @return string
 */
function sd_get_element_require_string( $args, $key, $type ) {
	$output   = '';
	$requires = array();

	if ( ! empty( $args ) ) {
		foreach ( $args as $t => $k ) {
			if ( in_array( $key, $k ) ) {
				$requires[] = '[%' . $type . '%]=="' . $t . '"';
			}
		}

		if ( ! empty( $requires ) ) {
			$output = '(' . implode( ' || ', $requires ) . ')';
		}
	}


	return $output;
}

/**
 * A helper function for text color inputs.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function sd_get_text_color_input( $type = 'text_color', $overwrite = array() ) {
	$options = array(
		           '' => __( "None" ),
	           ) + sd_aui_colors();

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Text color' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( "Typography" )
	);


	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}

/**
 * A helper function for column inputs.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function sd_get_col_input( $type = 'col', $overwrite = array() ) {

	$device_size = '';
	if ( ! empty( $overwrite['device_type'] ) ) {
		if ( $overwrite['device_type'] == 'Tablet' ) {
			$device_size = '-md';
		} elseif ( $overwrite['device_type'] == 'Desktop' ) {
			$device_size = '-lg';
		}
	}
	$options = array(
		''   => __( 'auto' ),
		'1'  => '1/12',
		'2'  => '2/12',
		'3'  => '3/12',
		'4'  => '4/12',
		'5'  => '5/12',
		'6'  => '6/12',
		'7'  => '7/12',
		'8'  => '8/12',
		'9'  => '9/12',
		'10' => '10/12',
		'11' => '11/12',
		'12' => '12/12',
	);

	$defaults = array(
		'type'            => 'select',
		'title'           => __( 'Column width' ),
		'options'         => $options,
		'default'         => '',
		'desc_tip'        => true,
		'group'           => __( "Container" ),
		'element_require' => '[%container%]=="col"',
	);


	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}

/**
 * A helper function for row columns inputs.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function sd_get_row_cols_input( $type = 'row_cols', $overwrite = array() ) {

	$device_size = '';
	if ( ! empty( $overwrite['device_type'] ) ) {
		if ( $overwrite['device_type'] == 'Tablet' ) {
			$device_size = '-md';
		} elseif ( $overwrite['device_type'] == 'Desktop' ) {
			$device_size = '-lg';
		}
	}
	$options = array(
		''  => __( 'auto' ),
		'1' => '1',
		'2' => '2',
		'3' => '3',
		'4' => '4',
		'5' => '5',
		'6' => '6',
	);

	$defaults = array(
		'type'            => 'select',
		'title'           => __( 'Row columns' ),
		'options'         => $options,
		'default'         => '',
		'desc_tip'        => true,
		'group'           => __( "Container" ),
		'element_require' => '[%container%]=="row"',
	);


	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}

/**
 * A helper function for text align inputs.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function sd_get_text_align_input( $type = 'text_align', $overwrite = array() ) {

	$device_size = '';
	if ( ! empty( $overwrite['device_type'] ) ) {
		if ( $overwrite['device_type'] == 'Tablet' ) {
			$device_size = '-md';
		} elseif ( $overwrite['device_type'] == 'Desktop' ) {
			$device_size = '-lg';
		}
	}
	$options = array(
		''                                => __( "Default" ),
		'text' . $device_size . '-left'   => __( "Left" ),
		'text' . $device_size . '-right'  => __( "Right" ),
		'text' . $device_size . '-center' => __( "Center" ),
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Text align' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( "Typography" )
	);


	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}

/**
 * A helper function for display inputs.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function sd_get_display_input( $type = 'display', $overwrite = array() ) {

	$device_size = '';
	if ( ! empty( $overwrite['device_type'] ) ) {
		if ( $overwrite['device_type'] == 'Tablet' ) {
			$device_size = '-md';
		} elseif ( $overwrite['device_type'] == 'Desktop' ) {
			$device_size = '-lg';
		}
	}
	$options = array(
		''                                   => __( "Default" ),
		'd' . $device_size . '-none'         => "none",
		'd' . $device_size . '-inline'       => "inline",
		'd' . $device_size . '-inline-block' => "inline-block",
		'd' . $device_size . '-block'        => "block",
		'd' . $device_size . '-table'        => "table",
		'd' . $device_size . '-table-cell'   => "table-cell",
		'd' . $device_size . '-table-row'    => "table-row",
		'd' . $device_size . '-flex'         => "flex",
		'd' . $device_size . '-inline-flex'  => "inline-flex",
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Display' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( "Wrapper Styles" )
	);


	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}

/**
 * A helper function for text justify inputs.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function sd_get_text_justify_input( $type = 'text_justify', $overwrite = array() ) {

	$defaults = array(
		'type'     => 'checkbox',
		'title'    => __( 'Text justify' ),
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( "Typography" )
	);


	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}

/**
 * Get the AUI colors.
 *
 * @param $include_branding
 * @param $include_outlines
 * @param $outline_button_only_text
 *
 * @return array
 */
function sd_aui_colors( $include_branding = false, $include_outlines = false, $outline_button_only_text = false ) {
	$theme_colors = array();

	$theme_colors["primary"]   = __( 'Primary' );
	$theme_colors["secondary"] = __( 'Secondary' );
	$theme_colors["success"]   = __( 'Success' );
	$theme_colors["danger"]    = __( 'Danger' );
	$theme_colors["warning"]   = __( 'Warning' );
	$theme_colors["info"]      = __( 'Info' );
	$theme_colors["light"]     = __( 'Light' );
	$theme_colors["dark"]      = __( 'Dark' );
	$theme_colors["white"]     = __( 'White' );
	$theme_colors["purple"]    = __( 'Purple' );
	$theme_colors["salmon"]    = __( 'Salmon' );
	$theme_colors["cyan"]      = __( 'Cyan' );
	$theme_colors["gray"]      = __( 'Gray' );
	$theme_colors["gray-dark"]      = __( 'Gray dark' );
	$theme_colors["indigo"]    = __( 'Indigo' );
	$theme_colors["orange"]    = __( 'Orange' );

	if ( $include_outlines ) {
		$button_only                       = $outline_button_only_text ? " " . __( "(button only)" ) : '';
		$theme_colors["outline-primary"]   = __( 'Primary outline' ) . $button_only;
		$theme_colors["outline-secondary"] = __( 'Secondary outline' ) . $button_only;
		$theme_colors["outline-success"]   = __( 'Success outline' ) . $button_only;
		$theme_colors["outline-danger"]    = __( 'Danger outline' ) . $button_only;
		$theme_colors["outline-warning"]   = __( 'Warning outline' ) . $button_only;
		$theme_colors["outline-info"]      = __( 'Info outline' ) . $button_only;
		$theme_colors["outline-light"]     = __( 'Light outline' ) . $button_only;
		$theme_colors["outline-dark"]      = __( 'Dark outline' ) . $button_only;
		$theme_colors["outline-white"]     = __( 'White outline' ) . $button_only;
		$theme_colors["outline-purple"]    = __( 'Purple outline' ) . $button_only;
		$theme_colors["outline-salmon"]    = __( 'Salmon outline' ) . $button_only;
		$theme_colors["outline-cyan"]      = __( 'Cyan outline' ) . $button_only;
		$theme_colors["outline-gray"]      = __( 'Gray outline' ) . $button_only;
		$theme_colors["outline-gray-dark"]      = __( 'Gray dark outline' ) . $button_only;
		$theme_colors["outline-indigo"]    = __( 'Indigo outline' ) . $button_only;
		$theme_colors["outline-orange"]    = __( 'Orange outline' ) . $button_only;
	}


	if ( $include_branding ) {
		$theme_colors = $theme_colors + sd_aui_branding_colors();
	}

	return apply_filters( 'sd_aui_colors', $theme_colors, $include_outlines, $include_branding );
}

/**
 * Get the AUI brangin colors.
 *
 * @return array
 */
function sd_aui_branding_colors() {
	return array(
		"facebook"  => __( 'Facebook' ),
		"twitter"   => __( 'Twitter' ),
		"instagram" => __( 'Instagram' ),
		"linkedin"  => __( 'Linkedin' ),
		"flickr"    => __( 'Flickr' ),
		"github"    => __( 'GitHub' ),
		"youtube"   => __( 'YouTube' ),
		"wordpress" => __( 'WordPress' ),
		"google"    => __( 'Google' ),
		"yahoo"     => __( 'Yahoo' ),
		"vkontakte" => __( 'Vkontakte' ),
	);
}


/**
 * A helper function for container class.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function sd_get_container_class_input( $type = 'container', $overwrite = array() ) {


	$options = array(
		"container"       => __( 'container (default)' ),
		"container-sm"    => 'container-sm',
		"container-md"    => 'container-md',
		"container-lg"    => 'container-lg',
		"container-xl"    => 'container-xl',
		"container-xxl"   => 'container-xxl',
		"container-fluid" => 'container-fluid',
		"row"             => 'row',
		"col"             => 'col',
		"card"            => 'card',
		"card-header"     => 'card-header',
		"card-body"       => 'card-body',
		"card-footer"     => 'card-footer',
		"list-group"      => 'list-group',
		"list-group-item" => 'list-group-item',
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Type' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( "Container" )
	);


	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}

/**
 * A helper function for position class.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function sd_get_position_class_input( $type = 'position', $overwrite = array() ) {


	$options = array(
		""                  => __( 'Default' ),
		"position-static"   => 'static',
		"position-relative" => 'relative',
		"position-absolute" => 'absolute',
		"position-fixed"    => 'fixed',
		"position-sticky"   => 'sticky',
		"fixed-top"         => 'fixed-top',
		"fixed-bottom"      => 'fixed-bottom',
		"sticky-top"        => 'sticky-top',
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Position' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( "Wrapper Styles" )
	);


	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}

/**
 * A helper function for sticky offset input.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function sd_get_sticky_offset_input( $type = 'top', $overwrite = array() ) {

	$defaults = array(
		'type'            => 'number',
		'title'           => __( 'Sticky offset' ),
		//'desc' =>  __('Sticky offset'),
		'default'         => '',
		'desc_tip'        => true,
		'group'           => __( "Wrapper Styles" ),
		'element_require' => '[%position%]=="sticky" || [%position%]=="sticky-top"'
	);

	// title
	if ( $type == 'top' ) {
		$defaults['title'] = __( 'Top offset' );
		$defaults['icon']  = 'box-top';
		$defaults['row']   = array(
			'title' => __( 'Sticky offset' ),
			'key'   => 'sticky-offset',
			'open'  => true,
			'class' => 'text-center',
		);
	} elseif ( $type == 'bottom' ) {
		$defaults['title'] = __( 'Bottom offset' );
		$defaults['icon']  = 'box-bottom';
		$defaults['row']   = array(
			'key'   => 'sticky-offset',
			'close' => true,
		);
	}


	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}

/**
 * A helper function for font size
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function sd_get_font_size_input( $type = 'font_size', $overwrite = array(), $has_custom = false ) {


	$options = array(
		""          => __( 'Inherit from parent' ),
		"h6"        => 'h6',
		"h5"        => 'h5',
		"h4"        => 'h4',
		"h3"        => 'h3',
		"h2"        => 'h2',
		"h1"        => 'h1',
		"display-1" => "display-1",
		"display-2" => "display-2",
		"display-3" => "display-3",
		"display-4" => "display-4",
	);

	if ( $has_custom ) {
		$options['custom'] = __( 'Custom size' );
	}

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Font size' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( "Typography" )
	);


	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}

/**
 * A helper function for custom font size.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function sd_get_font_custom_size_input( $type = 'font_size_custom', $overwrite = array(), $parent_type = '' ) {


	$defaults = array(
		'type'              => 'number',
		'title'             => __( 'Font size (rem)' ),
		'default'           => '',
		'placeholder'       => '1.25',
		'custom_attributes' => array(
			'step' => '0.1',
			'min'  => '0',
			'max'  => '100',
		),
		'desc_tip'          => true,
		'group'             => __( "Typography" )
	);

	if ( $parent_type ) {
		$defaults['element_require'] = '[%' . $parent_type . '%]=="custom"';
	}


	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}

/**
 * A helper function for font size inputs.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function sd_get_font_size_input_group( $type = 'font_size', $overwrite = array(), $overwrite_custom = array() ) {


	$inputs = array();

	if ( $overwrite !== false ) {
		$inputs[ $type ] = sd_get_font_size_input( $type, $overwrite, true );
	}

	if ( $overwrite_custom !== false ) {
		$custom            = $type . "_custom";
		$inputs[ $custom ] = sd_get_font_custom_size_input( $custom, $overwrite_custom, $type );
	}


	return $inputs;
}

/**
 * A helper function for font weight.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function sd_get_font_weight_input( $type = 'font_weight', $overwrite = array() ) {


	$options = array(
		""                                => __( 'Inherit' ),
		"font-weight-bold"                => 'bold',
		"font-weight-bolder"              => 'bolder',
		"font-weight-normal"              => 'normal',
		"font-weight-light"               => 'light',
		"font-weight-lighter"             => 'lighter',
		"font-italic"                     => 'italic',
		"font-weight-bold font-italic"    => 'bold italic',
		"font-weight-bolder font-italic"  => 'bolder italic',
		"font-weight-normal font-italic"  => 'normal italic',
		"font-weight-light font-italic"   => 'light italic',
		"font-weight-lighter font-italic" => 'lighter italic',
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Appearance' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( "Typography" )
	);


	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}

/**
 * A helper function for font case class.
 *
 * @param $type
 * @param $overwrite
 *
 * @return array
 */
function sd_get_font_case_input( $type = 'font_weight', $overwrite = array() ) {


	$options = array(
		""                => __( 'Default' ),
		"text-lowercase"  => __( 'lowercase' ),
		"text-uppercase"  => __( 'UPPERCASE' ),
		"text-capitalize" => __( 'Capitalize' ),
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Letter case' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( "Typography" )
	);


	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}

/**
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 * @todo remove this as now included above.
 * A helper function for font size
 *
 */
function sd_get_font_italic_input( $type = 'font_italic', $overwrite = array() ) {


	$options = array(
		""            => __( 'No' ),
		"font-italic" => __( 'Yes' )
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Font italic' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( "Typography" )
	);

	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}

/**
 * A helper function for the anchor input.
 *
 * @param $type
 * @param $overwrite
 *
 * @return array
 */
function sd_get_anchor_input( $type = 'anchor', $overwrite = array() ) {


	$defaults = array(
		'type'     => 'text',
		'title'    => __( 'HTML anchor' ),
		'desc'     => __( 'Enter a word or two — without spaces — to make a unique web address just for this block, called an “anchor.” Then, you’ll be able to link directly to this section of your page.' ),
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( "Advanced" )
	);

	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}

/**
 * A helper function for the class input.
 *
 * @param $type
 * @param $overwrite
 *
 * @return array
 */
function sd_get_class_input( $type = 'css_class', $overwrite = array() ) {

	$defaults = array(
		'type'     => 'text',
		'title'    => __( 'Additional CSS class(es)' ),
		'desc'     => __( 'Separate multiple classes with spaces.' ),
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( "Advanced" )
	);

	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}


/**
 * Build AUI classes from settings.
 *
 * @param $args
 *
 * @return string
 * @todo find best way to use px- py- or general p-
 */
function sd_build_aui_class( $args ) {

	$classes = array();

	// margins.
	if ( isset( $args['mt'] ) && $args['mt'] !== '' ) {
		$classes[] = "mt-" . sanitize_html_class( $args['mt'] );
		$mt        = $args['mt'];
	} else {
		$mt = null;
	}
	if ( isset( $args['mr'] ) && $args['mr'] !== '' ) {
		$classes[] = "mr-" . sanitize_html_class( $args['mr'] );
		$mr        = $args['mr'];
	} else {
		$mr = null;
	}
	if ( isset( $args['mb'] ) && $args['mb'] !== '' ) {
		$classes[] = "mb-" . sanitize_html_class( $args['mb'] );
		$mb        = $args['mb'];
	} else {
		$mb = null;
	}
	if ( isset( $args['ml'] ) && $args['ml'] !== '' ) {
		$classes[] = "ml-" . sanitize_html_class( $args['ml'] );
		$ml        = $args['ml'];
	} else {
		$ml = null;
	}

	// margins tablet.
	if ( isset( $args['mt_md'] ) && $args['mt_md'] !== '' ) {
		$classes[] = "mt-md-" . sanitize_html_class( $args['mt_md'] );
		$mt_md     = $args['mt_md'];
	} else {
		$mt_md = null;
	}
	if ( isset( $args['mr_md'] ) && $args['mr_md'] !== '' ) {
		$classes[] = "mr-md-" . sanitize_html_class( $args['mr_md'] );
		$mt_md     = $args['mr_md'];
	} else {
		$mr_md = null;
	}
	if ( isset( $args['mb_md'] ) && $args['mb_md'] !== '' ) {
		$classes[] = "mb-md-" . sanitize_html_class( $args['mb_md'] );
		$mt_md     = $args['mb_md'];
	} else {
		$mb_md = null;
	}
	if ( isset( $args['ml_md'] ) && $args['ml_md'] !== '' ) {
		$classes[] = "ml-md-" . sanitize_html_class( $args['ml_md'] );
		$mt_md     = $args['ml_md'];
	} else {
		$ml_md = null;
	}

	// margins desktop.
	if ( isset( $args['mt_lg'] ) && $args['mt_lg'] !== '' ) {
		if ( $mt == null && $mt_md == null ) {
			$classes[] = "mt-" . sanitize_html_class( $args['mt_lg'] );
		} else {
			$classes[] = "mt-lg-" . sanitize_html_class( $args['mt_lg'] );
		}
	}
	if ( isset( $args['mr_lg'] ) && $args['mr_lg'] !== '' ) {
		if ( $mr == null && $mr_md == null ) {
			$classes[] = "mr-" . sanitize_html_class( $args['mr_lg'] );
		} else {
			$classes[] = "mr-lg-" . sanitize_html_class( $args['mr_lg'] );
		}
	}
	if ( isset( $args['mb_lg'] ) && $args['mb_lg'] !== '' ) {
		if ( $mb == null && $mb_md == null ) {
			$classes[] = "mb-" . sanitize_html_class( $args['mb_lg'] );
		} else {
			$classes[] = "mb-lg-" . sanitize_html_class( $args['mb_lg'] );
		}
	}
	if ( isset( $args['ml_lg'] ) && $args['ml_lg'] !== '' ) {
		if ( $ml == null && $ml_md == null ) {
			$classes[] = "ml-" . sanitize_html_class( $args['ml_lg'] );
		} else {
			$classes[] = "ml-lg-" . sanitize_html_class( $args['ml_lg'] );
		}
	}


	// padding.
	if ( isset( $args['pt'] ) && $args['pt'] !== '' ) {
		$classes[] = "pt-" . sanitize_html_class( $args['pt'] );
		$pt        = $args['pt'];
	} else {
		$pt = null;
	}
	if ( isset( $args['pr'] ) && $args['pr'] !== '' ) {
		$classes[] = "pr-" . sanitize_html_class( $args['pr'] );
		$pr        = $args['pr'];
	} else {
		$pr = null;
	}
	if ( isset( $args['pb'] ) && $args['pb'] !== '' ) {
		$classes[] = "pb-" . sanitize_html_class( $args['pb'] );
		$pb        = $args['pb'];
	} else {
		$pb = null;
	}
	if ( isset( $args['pl'] ) && $args['pl'] !== '' ) {
		$classes[] = "pl-" . sanitize_html_class( $args['pl'] );
		$pl        = $args['pl'];
	} else {
		$pl = null;
	}

	// padding tablet.
	if ( isset( $args['pt_md'] ) && $args['pt_md'] !== '' ) {
		$classes[] = "pt-md-" . sanitize_html_class( $args['pt_md'] );
		$pt_md     = $args['pt_md'];
	} else {
		$pt_md = null;
	}
	if ( isset( $args['pr_md'] ) && $args['pr_md'] !== '' ) {
		$classes[] = "pr-md-" . sanitize_html_class( $args['pr_md'] );
		$pt_md     = $args['pr_md'];
	} else {
		$pr_md = null;
	}
	if ( isset( $args['pb_md'] ) && $args['pb_md'] !== '' ) {
		$classes[] = "pb-md-" . sanitize_html_class( $args['pb_md'] );
		$pt_md     = $args['pb_md'];
	} else {
		$pb_md = null;
	}
	if ( isset( $args['pl_md'] ) && $args['pl_md'] !== '' ) {
		$classes[] = "pl-md-" . sanitize_html_class( $args['pl_md'] );
		$pt_md     = $args['pl_md'];
	} else {
		$pl_md = null;
	}

	// padding desktop.
	if ( isset( $args['pt_lg'] ) && $args['pt_lg'] !== '' ) {
		if ( $pt == null && $pt_md == null ) {
			$classes[] = "pt-" . sanitize_html_class( $args['pt_lg'] );
		} else {
			$classes[] = "pt-lg-" . sanitize_html_class( $args['pt_lg'] );
		}
	}
	if ( isset( $args['pr_lg'] ) && $args['pr_lg'] !== '' ) {
		if ( $pr == null && $pr_md == null ) {
			$classes[] = "pr-" . sanitize_html_class( $args['pr_lg'] );
		} else {
			$classes[] = "pr-lg-" . sanitize_html_class( $args['pr_lg'] );
		}
	}
	if ( isset( $args['pb_lg'] ) && $args['pb_lg'] !== '' ) {
		if ( $pb == null && $pb_md == null ) {
			$classes[] = "pb-" . sanitize_html_class( $args['pb_lg'] );
		} else {
			$classes[] = "pb-lg-" . sanitize_html_class( $args['pb_lg'] );
		}
	}
	if ( isset( $args['pl_lg'] ) && $args['pl_lg'] !== '' ) {
		if ( $pl == null && $pl_md == null ) {
			$classes[] = "pl-" . sanitize_html_class( $args['pl_lg'] );
		} else {
			$classes[] = "pl-lg-" . sanitize_html_class( $args['pl_lg'] );
		}
	}

	// row cols, mobile, tablet, desktop
	if ( ! empty( $args['row_cols'] ) && $args['row_cols'] !== '' ) {
		$classes[] = sanitize_html_class( "row-cols-" . $args['row_cols'] );
		$row_cols  = $args['row_cols'];
	} else {
		$row_cols = null;
	}
	if ( ! empty( $args['row_cols_md'] ) && $args['row_cols_md'] !== '' ) {
		$classes[]   = sanitize_html_class( "row-cols-md-" . $args['row_cols_md'] );
		$row_cols_md = $args['row_cols_md'];
	} else {
		$row_cols_md = null;
	}
	if ( ! empty( $args['row_cols_lg'] ) && $args['row_cols_lg'] !== '' ) {
		if ( $row_cols == null && $row_cols_md == null ) {
			$classes[] = sanitize_html_class( "row-cols-" . $args['row_cols_lg'] );
		} else {
			$classes[] = sanitize_html_class( "row-cols-lg-" . $args['row_cols_lg'] );
		}
	}

	// columns , mobile, tablet, desktop
	if ( ! empty( $args['col'] ) && $args['col'] !== '' ) {
		$classes[] = sanitize_html_class( "col-" . $args['col'] );
		$col       = $args['col'];
	} else {
		$col = null;
	}
	if ( ! empty( $args['col_md'] ) && $args['col_md'] !== '' ) {
		$classes[] = sanitize_html_class( "col-md-" . $args['col_md'] );
		$col_md    = $args['col_md'];
	} else {
		$col_md = null;
	}
	if ( ! empty( $args['col_lg'] ) && $args['col_lg'] !== '' ) {
		if ( $col == null && $col_md == null ) {
			$classes[] = sanitize_html_class( "col-" . $args['col_lg'] );
		} else {
			$classes[] = sanitize_html_class( "col-lg-" . $args['col_lg'] );
		}
	}


	// border
	if ( ! empty( $args['border'] ) && ( $args['border'] == 'none' || $args['border'] === '0' ) ) {
		$classes[] = "border-0";
	} elseif ( ! empty( $args['border'] ) ) {
		$classes[] = "border border-" . sanitize_html_class( $args['border'] );
	}

	// border radius type
	if ( ! empty( $args['rounded'] ) ) {
		$classes[] = sanitize_html_class( $args['rounded'] );
	}

	// border radius size
	if ( ! empty( $args['rounded_size'] ) ) {
		$classes[] = "rounded-" . sanitize_html_class( $args['rounded_size'] );
		// if we set a size then we need to remove "rounded" if set
		if ( ( $key = array_search( "rounded", $classes ) ) !== false ) {
			unset( $classes[ $key ] );
		}
	}

	// shadow
	//if ( !empty( $args['shadow'] ) ) { $classes[] = sanitize_html_class($args['shadow']); }

	// background
	if ( ! empty( $args['bg'] ) ) {
		$classes[] = "bg-" . sanitize_html_class( $args['bg'] );
	}

	// text_color
	if ( ! empty( $args['text_color'] ) ) {
		$classes[] = "text-" . sanitize_html_class( $args['text_color'] );
	}

	// text_align
	if ( ! empty( $args['text_justify'] ) ) {
		$classes[] = 'text-justify';
	} else {
		if ( ! empty( $args['text_align'] ) ) {
			$classes[]  = sanitize_html_class( $args['text_align'] );
			$text_align = $args['text_align'];
		} else {
			$text_align = null;
		}
		if ( ! empty( $args['text_align_md'] ) && $args['text_align_md'] !== '' ) {
			$classes[]     = sanitize_html_class( $args['text_align_md'] );
			$text_align_md = $args['text_align_md'];
		} else {
			$text_align_md = null;
		}
		if ( ! empty( $args['text_align_lg'] ) && $args['text_align_lg'] !== '' ) {
			if ( $text_align == null && $text_align_md == null ) {
				$classes[] = sanitize_html_class( str_replace( "-lg", "", $args['text_align_lg'] ) );
			} else {
				$classes[] = sanitize_html_class( $args['text_align_lg'] );
			}
		}
	}

	// display
	if ( ! empty( $args['display'] ) ) {
		$classes[] = sanitize_html_class( $args['display'] );
		$display   = $args['display'];
	} else {
		$display = null;
	}
	if ( ! empty( $args['display_md'] ) && $args['display_md'] !== '' ) {
		$classes[]  = sanitize_html_class( $args['display_md'] );
		$display_md = $args['display_md'];
	} else {
		$display_md = null;
	}
	if ( ! empty( $args['display_lg'] ) && $args['display_lg'] !== '' ) {
		if ( $display == null && $display_md == null ) {
			$classes[] = sanitize_html_class( str_replace( "-lg", "", $args['display_lg'] ) );
		} else {
			$classes[] = sanitize_html_class( $args['display_lg'] );
		}
	}


	// bgtus - background transparent until scroll
	if ( ! empty( $args['bgtus'] ) ) {
		$classes[] = sanitize_html_class( "bg-transparent-until-scroll" );
	}


	// build classes from build keys
	$build_keys = sd_get_class_build_keys();
	if ( ! empty( $build_keys ) ) {
		foreach ( $build_keys as $key ) {
			if ( $key == 'font_size' && ! empty( $args[ $key ] ) && $args[ $key ] == 'custom' ) {
				continue;
			}
			if ( ! empty( $args[ $key ] ) ) {
				$classes[] = sd_sanitize_html_classes( $args[ $key ] );
			}
		}
	}


	return implode( " ", $classes );
}

/**
 * Build Style output from arguments.
 *
 * @param $args
 *
 * @return array
 */
function sd_build_aui_styles( $args ) {

	$styles = array();

	// background color
	if ( ! empty( $args['bg'] ) && $args['bg'] !== '' ) {
		if ( $args['bg'] == 'custom-color' ) {
			$styles['background-color'] = $args['bg_color'];
		} else if ( $args['bg'] == 'custom-gradient' ) {
			$styles['background-image'] = $args['bg_gradient'];

			// use background on text.
			if ( ! empty( $args['bg_on_text'] ) && $args['bg_on_text'] ) {
				$styles['background-clip']         = "text";
				$styles['-webkit-background-clip'] = "text";
				$styles['text-fill-color']         = "transparent";
				$styles['-webkit-text-fill-color'] = "transparent";
			}
		}
	}

	if ( ! empty( $args['bg_image'] ) && $args['bg_image'] !== '' ) {
		$hasImage = true;
		if ( $styles['background-color'] !== undefined && $args['bg'] == 'custom-color' ) {
			$styles['background-image']      = "url(" . $args['bg_image'] . ")";
			$styles['background-blend-mode'] = "overlay";
		} else if ( $styles['background-image'] !== undefined && $args['bg'] == 'custom-gradient' ) {
			$styles['background-image'] .= ",url(" . $args['bg_image'] . ")";
		} else if ( ! empty( $args['bg'] ) && $args['bg'] != '' && $args['bg'] != 'transparent' ) {
			// do nothing as we alreay have a preset
			$hasImage = false;
		} else {
			$styles['background-image'] = "url(" . $args['bg_image'] . ")";
		}

		if ( $hasImage ) {
			$styles['background-size'] = "cover";

			if ( ! empty( $args['bg_image_fixed'] ) && $args['bg_image_fixed'] ) {
				$styles['background-attachment'] = "fixed";
			}
		}

		if ( $hasImage && ! empty( $args['bg_image_xy'] ) && $args['bg_image_xy'] . x . length ) {
			$styles['background-position'] = ( $args['bg_image_xy'] . x * 100 ) . "% " . ( $args['bg_image_xy'] . y * 100 ) . "%";
		}
	}

	// sticky offset top
	if ( ! empty( $args['sticky_offset_top'] ) && $args['sticky_offset_top'] !== '' ) {
		$styles['top'] = absint( $args['sticky_offset_top'] );
	}

	// sticky offset bottom
	if ( ! empty( $args['sticky_offset_bottom'] ) && $args['sticky_offset_bottom'] !== '' ) {
		$styles['bottom'] = absint( $args['sticky_offset_bottom'] );
	}

	// font size
	if ( ! empty( $args['font_size_custom'] ) && $args['font_size_custom'] !== '' ) {
		$styles['font-size'] = (float) $args['font_size_custom'] . "rem";

	}

	$style_string = '';
	if ( ! empty( $styles ) ) {
		foreach ( $styles as $key => $val ) {
			$style_string .= esc_attr( $key ) . ':' . esc_attr( $val ) . ';';
		}
	}

	return $style_string;

}

/**
 * Sanitize single or multiple HTML classes.
 *
 * @param $classes
 * @param $sep
 *
 * @return string
 */
function sd_sanitize_html_classes( $classes, $sep = " " ) {
	$return = "";

	if ( ! is_array( $classes ) ) {
		$classes = explode( $sep, $classes );
	}

	if ( ! empty( $classes ) ) {
		foreach ( $classes as $class ) {
			$return .= sanitize_html_class( $class ) . " ";
		}
	}

	return $return;
}


/**
 * Keys that are used for the class builder.
 *
 * @return void
 */
function sd_get_class_build_keys() {
	$keys = array(
		'container',
		'position',
		'flex_direction',
		'shadow',
		'rounded',
		'nav_style',
		'horizontal_alignment',
		'nav_fill',
		'width',
		'font_weight',
		'font_size',
		'font_case',
		'css_class',
	);

	return apply_filters( "sd_class_build_keys", $keys );
}
