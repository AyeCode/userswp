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
	return apply_filters(
		'sd_pagenow_exclude',
		array(
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
		)
	);
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
	global $aui_bs5;
	$options = array(
		''     => __( 'None', 'ayecode-connect' ),
		'auto' => __( 'auto', 'ayecode-connect' ),
		'0'    => '0',
		'1'    => '1',
		'2'    => '2',
		'3'    => '3',
		'4'    => '4',
		'5'    => '5',
		'6'    => '6',
		'7'    => '7',
		'8'    => '8',
		'9'    => '9',
		'10'   => '10',
		'11'   => '11',
		'12'   => '12',
	);

	if ( $include_negatives ) {
		$options['n1']  = '-1';
		$options['n2']  = '-2';
		$options['n3']  = '-3';
		$options['n4']  = '-4';
		$options['n5']  = '-5';
		$options['n6']  = '-6';
		$options['n7']  = '-7';
		$options['n8']  = '-8';
		$options['n9']  = '-9';
		$options['n10'] = '-10';
		$options['n11'] = '-11';
		$options['n12'] = '-12';
	}

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Margin top', 'ayecode-connect' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Wrapper Styles', 'ayecode-connect' ),
	);

	// title
	if ( $type == 'mt' ) {
		$defaults['title'] = __( 'Margin top', 'ayecode-connect' );
		$defaults['icon']  = 'box-top';
		$defaults['row']   = array(
			'title' => __( 'Margins', 'ayecode-connect' ),
			'key'   => 'wrapper-margins',
			'open'  => true,
			'class' => 'text-center',
		);
	} elseif ( $type == 'mr' ) {
		$defaults['title'] = __( 'Margin right', 'ayecode-connect' );
		$defaults['icon']  = 'box-right';
		$defaults['row']   = array(
			'key' => 'wrapper-margins',
		);
	} elseif ( $type == 'mb' ) {
		$defaults['title'] = __( 'Margin bottom', 'ayecode-connect' );
		$defaults['icon']  = 'box-bottom';
		$defaults['row']   = array(
			'key' => 'wrapper-margins',
		);
	} elseif ( $type == 'ml' ) {
		$defaults['title'] = __( 'Margin left', 'ayecode-connect' );
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
		''   => __( 'None', 'ayecode-connect' ),
		'0'  => '0',
		'1'  => '1',
		'2'  => '2',
		'3'  => '3',
		'4'  => '4',
		'5'  => '5',
		'6'  => '6',
		'7'  => '7',
		'8'  => '8',
		'9'  => '9',
		'10' => '10',
		'11' => '11',
		'12' => '12',
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Padding top', 'ayecode-connect' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Wrapper Styles', 'ayecode-connect' ),
	);

	// title
	if ( $type == 'pt' ) {
		$defaults['title'] = __( 'Padding top', 'ayecode-connect' );
		$defaults['icon']  = 'box-top';
		$defaults['row']   = array(
			'title' => __( 'Padding', 'ayecode-connect' ),
			'key'   => 'wrapper-padding',
			'open'  => true,
			'class' => 'text-center',
		);
	} elseif ( $type == 'pr' ) {
		$defaults['title'] = __( 'Padding right', 'ayecode-connect' );
		$defaults['icon']  = 'box-right';
		$defaults['row']   = array(
			'key' => 'wrapper-padding',
		);
	} elseif ( $type == 'pb' ) {
		$defaults['title'] = __( 'Padding bottom', 'ayecode-connect' );
		$defaults['icon']  = 'box-bottom';
		$defaults['row']   = array(
			'key' => 'wrapper-padding',
		);
	} elseif ( $type == 'pl' ) {
		$defaults['title'] = __( 'Padding left', 'ayecode-connect' );
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
	global $aui_bs5;

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Border', 'ayecode-connect' ),
		'options'  => array(),
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Wrapper Styles', 'ayecode-connect' ),
	);

	// title
	if ( 'rounded' === $type ) {
		$defaults['title']           = __( 'Border radius type', 'ayecode-connect' );
		$defaults['options']         = array(
			''               => __( 'Default', 'ayecode-connect' ),
			'rounded'        => 'rounded',
			'rounded-top'    => 'rounded-top',
			'rounded-right'  => 'rounded-right',
			'rounded-bottom' => 'rounded-bottom',
			'rounded-left'   => 'rounded-left',
		);
		$defaults['element_require'] = '([%border%]&&[%border%]!="0")';
	} elseif ( 'rounded_size' === $type ) {
		$defaults['title'] = __( 'Border radius size', 'ayecode-connect' );

		if ( $aui_bs5 ) {
			$defaults['options'] = array(
				''       => __( 'Default', 'ayecode-connect' ),
				'0'      => '0',
				'1'      => '1',
				'2'      => '2',
				'3'      => '3',
				'4'      => '4',
				'circle' => 'circle',
				'pill'   => 'pill',
			);
		} else {
			$defaults['options'] = array(
				''   => __( 'Default', 'ayecode-connect' ),
				'sm' => __( 'Small', 'ayecode-connect' ),
				'lg' => __( 'Large', 'ayecode-connect' ),
			);
		}
		$defaults['element_require'] = '([%border%]&&[%border%]!="0")';
	} elseif ( 'width' === $type ) { // BS%
		$defaults['title']           = __( 'Border width', 'ayecode-connect' );
		$defaults['options']         = array(
			''         => __( 'Default', 'ayecode-connect' ),
			'border-2' => '2',
			'border-3' => '3',
			'border-4' => '4',
			'border-5' => '5',
		);
		$defaults['element_require'] = $aui_bs5 ? '([%border%]&&[%border%]!="0")' : '1==2';
	} elseif ( 'opacity' === $type ) { // BS%
		$defaults['title']           = __( 'Border opacity', 'ayecode-connect' );
		$defaults['options']         = array(
			''                  => __( 'Default', 'ayecode-connect' ),
			'border-opacity-75' => '75%',
			'border-opacity-50' => '50%',
			'border-opacity-25' => '25%',
			'border-opacity-10' => '10%',
		);
		$defaults['element_require'] = $aui_bs5 ? '([%border%]&&[%border%]!="0")' : '1==2';
	} elseif ( 'type' === $type ) {
		$defaults['title']           = __( 'Border show', 'ayecode-connect' );
		$defaults['options']         = array(
			'border'          => __( 'Full (set color to show)', 'ayecode-connect' ),
			'border-top'      => __( 'Top', 'ayecode-connect' ),
			'border-bottom'   => __( 'Bottom', 'ayecode-connect' ),
			'border-left'     => __( 'Left', 'ayecode-connect' ),
			'border-right'    => __( 'Right', 'ayecode-connect' ),
			'border-top-0'    => __( '-Top', 'ayecode-connect' ),
			'border-bottom-0' => __( '-Bottom', 'ayecode-connect' ),
			'border-left-0'   => __( '-Left', 'ayecode-connect' ),
			'border-right-0'  => __( '-Right', 'ayecode-connect' ),
		);
		$defaults['element_require'] = '([%border%]&&[%border%]!="0")';
	} else {
		$defaults['title']   = __( 'Border color', 'ayecode-connect' );
		$defaults['options'] = array(
			''  => __( 'Default', 'ayecode-connect' ),
			'0' => __( 'None', 'ayecode-connect' )
		) + sd_aui_colors( false, false, false, false, true );
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
		''          => __( 'None', 'ayecode-connect' ),
		'shadow-sm' => __( 'Small', 'ayecode-connect' ),
		'shadow'    => __( 'Regular', 'ayecode-connect' ),
		'shadow-lg' => __( 'Large', 'ayecode-connect' ),
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Shadow', 'ayecode-connect' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Wrapper Styles', 'ayecode-connect' ),
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
		           ''            => __( 'None', 'ayecode-connect' ),
		           'transparent' => __( 'Transparent', 'ayecode-connect' ),
	           ) + sd_aui_colors(false,false,false,false,true);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Background color', 'ayecode-connect' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Wrapper Styles', 'ayecode-connect' ),
	);

	$input = wp_parse_args( $overwrite, $defaults );

	return $input;
}

/**
 * A function to get th opacity options.
 *
 * @param $type
 * @param $overwrite
 *
 * @return array
 */
function sd_get_opacity_input( $type = 'opacity', $overwrite = array() ) {
	$options = array(
		''            => __( 'Default', 'ayecode-connect' ),
		'opacity-10'  => '10%',
		'opacity-15'  => '15%',
		'opacity-25'  => '25%',
		'opacity-35'  => '35%',
		'opacity-40'  => '40%',
		'opacity-50'  => '50%',
		'opacity-60'  => '60%',
		'opacity-65'  => '65%',
		'opacity-70'  => '70%',
		'opacity-75'  => '75%',
		'opacity-80'  => '80%',
		'opacity-90'  => '90%',
		'opacity-100' => '100%',
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Opacity', 'ayecode-connect' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Wrapper Styles', 'ayecode-connect' ),
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
function sd_get_background_inputs( $type = 'bg', $overwrite = array(), $overwrite_color = array(), $overwrite_gradient = array(), $overwrite_image = array(), $include_button_colors = false ) {

	$color_options = $include_button_colors ? sd_aui_colors( false, true, true, true, true ) : sd_aui_colors(false, false, false, false, true );

	$options = array(
		           ''            => __( 'None', 'ayecode-connect' ),
		           'transparent' => __( 'Transparent', 'ayecode-connect' ),
	           ) + $color_options;

	if ( false !== $overwrite_color ) {
		$options['custom-color'] = __( 'Custom Color', 'ayecode-connect' );
	}

	if ( false !== $overwrite_gradient ) {
		$options['custom-gradient'] = __( 'Custom Gradient', 'ayecode-connect' );
	}

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Background Color', 'ayecode-connect' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Background', 'ayecode-connect' ),
	);

	if ( $overwrite !== false ) {
		$input[ $type ] = wp_parse_args( $overwrite, $defaults );
	}

	if ( $overwrite_color !== false ) {
		$input[ $type . '_color' ] = wp_parse_args(
			$overwrite_color,
			array(
				'type'            => 'color',
				'title'           => __( 'Custom color', 'ayecode-connect' ),
				'placeholder'     => '',
				'default'         => '#0073aa',
				'desc_tip'        => true,
				'group'           => __( 'Background', 'ayecode-connect' ),
				'element_require' => '[%' . $type . '%]=="custom-color"',
			)
		);
	}

	if ( $overwrite_gradient !== false ) {
		$input[ $type . '_gradient' ] = wp_parse_args(
			$overwrite_gradient,
			array(
				'type'            => 'gradient',
				'title'           => __( 'Custom gradient', 'ayecode-connect' ),
				'placeholder'     => '',
				'default'         => 'linear-gradient(135deg,rgba(6,147,227,1) 0%,rgb(155,81,224) 100%)',
				'desc_tip'        => true,
				'group'           => __( 'Background', 'ayecode-connect' ),
				'element_require' => '[%' . $type . '%]=="custom-gradient"',
			)
		);
	}

	if ( $overwrite_image !== false ) {

		$input[ $type . '_image_fixed' ] = array(
			'type'            => 'checkbox',
			'title'           => __( 'Fixed background', 'ayecode-connect' ),
			'default'         => '',
			'desc_tip'        => true,
			'group'           => ! empty( $overwrite_image['group'] ) ? $overwrite_image['group'] : __( 'Background', 'ayecode-connect' ),
			'element_require' => '( [%' . $type . '%]=="" || [%' . $type . '%]=="custom-color" || [%' . $type . '%]=="custom-gradient" || [%' . $type . '%]=="transparent" )',

		);

		$input[ $type . '_image_use_featured' ] = array(
			'type'            => 'checkbox',
			'title'           => __( 'Use featured image', 'ayecode-connect' ),
			'default'         => '',
			'desc_tip'        => true,
			'group'           => ! empty( $overwrite_image['group'] ) ? $overwrite_image['group'] : __( 'Background', 'ayecode-connect' ),
			'element_require' => '( [%' . $type . '%]=="" || [%' . $type . '%]=="custom-color" || [%' . $type . '%]=="custom-gradient" || [%' . $type . '%]=="transparent" )',

		);

		$input[ $type . '_image' ] = wp_parse_args(
			$overwrite_image,
			array(
				'type'        => 'image',
				'title'       => __( 'Custom image', 'ayecode-connect' ),
				'placeholder' => '',
				'default'     => '',
				'desc_tip'    => true,
				'group'       => __( 'Background', 'ayecode-connect' ),
				//          'element_require' => ' ![%' . $type . '_image_use_featured%] '
			)
		);

		$input[ $type . '_image_id' ] = wp_parse_args(
			$overwrite_image,
			array(
				'type'        => 'hidden',
				'hidden_type' => 'number',
				'title'       => '',
				'placeholder' => '',
				'default'     => '',
				'group'       => __( 'Background', 'ayecode-connect' ),
			)
		);

		$input[ $type . '_image_xy' ] = wp_parse_args(
			$overwrite_image,
			array(
				'type'        => 'image_xy',
				'title'       => '',
				'placeholder' => '',
				'default'     => '',
				'group'       => __( 'Background', 'ayecode-connect' ),
			)
		);
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
		''                      => __( 'None', 'ayecode-connect' ),
		'mountains'             => __( 'Mountains', 'ayecode-connect' ),
		'drops'                 => __( 'Drops', 'ayecode-connect' ),
		'clouds'                => __( 'Clouds', 'ayecode-connect' ),
		'zigzag'                => __( 'Zigzag', 'ayecode-connect' ),
		'pyramids'              => __( 'Pyramids', 'ayecode-connect' ),
		'triangle'              => __( 'Triangle', 'ayecode-connect' ),
		'triangle-asymmetrical' => __( 'Triangle Asymmetrical', 'ayecode-connect' ),
		'tilt'                  => __( 'Tilt', 'ayecode-connect' ),
		'opacity-tilt'          => __( 'Opacity Tilt', 'ayecode-connect' ),
		'opacity-fan'           => __( 'Opacity Fan', 'ayecode-connect' ),
		'curve'                 => __( 'Curve', 'ayecode-connect' ),
		'curve-asymmetrical'    => __( 'Curve Asymmetrical', 'ayecode-connect' ),
		'waves'                 => __( 'Waves', 'ayecode-connect' ),
		'wave-brush'            => __( 'Wave Brush', 'ayecode-connect' ),
		'waves-pattern'         => __( 'Waves Pattern', 'ayecode-connect' ),
		'arrow'                 => __( 'Arrow', 'ayecode-connect' ),
		'split'                 => __( 'Split', 'ayecode-connect' ),
		'book'                  => __( 'Book', 'ayecode-connect' ),
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Type', 'ayecode-connect' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Shape Divider', 'ayecode-connect' ),
	);

	$input[ $type ] = wp_parse_args( $overwrite, $defaults );

	$input[ $type . '_notice' ] = array(
		'type'            => 'notice',
		'desc'            => __( 'Parent element must be position `relative`', 'ayecode-connect' ),
		'status'          => 'warning',
		'group'           => __( 'Shape Divider', 'ayecode-connect' ),
		'element_require' => '[%' . $type . '%]!=""',
	);

	$input[ $type . '_position' ] = wp_parse_args(
		$overwrite_color,
		array(
			'type'            => 'select',
			'title'           => __( 'Position', 'ayecode-connect' ),
			'options'         => array(
				'top'    => __( 'Top', 'ayecode-connect' ),
				'bottom' => __( 'Bottom', 'ayecode-connect' ),
			),
			'desc_tip'        => true,
			'group'           => __( 'Shape Divider', 'ayecode-connect' ),
			'element_require' => '[%' . $type . '%]!=""',
		)
	);

	$options = array(
		           ''            => __( 'None', 'ayecode-connect' ),
		           'transparent' => __( 'Transparent', 'ayecode-connect' ),
	           ) + sd_aui_colors(false,false,false,false,true )
	           + array(
		           'custom-color' => __( 'Custom Color', 'ayecode-connect' ),
	           );

	$input[ $type . '_color' ] = wp_parse_args(
		$overwrite_color,
		array(
			'type'            => 'select',
			'title'           => __( 'Color', 'ayecode-connect' ),
			'options'         => $options,
			'desc_tip'        => true,
			'group'           => __( 'Shape Divider', 'ayecode-connect' ),
			'element_require' => '[%' . $type . '%]!=""',
		)
	);

	$input[ $type . '_custom_color' ] = wp_parse_args(
		$overwrite_color,
		array(
			'type'            => 'color',
			'title'           => __( 'Custom color', 'ayecode-connect' ),
			'placeholder'     => '',
			'default'         => '#0073aa',
			'desc_tip'        => true,
			'group'           => __( 'Shape Divider', 'ayecode-connect' ),
			'element_require' => '[%' . $type . '_color%]=="custom-color" && [%' . $type . '%]!=""',
		)
	);

	$input[ $type . '_width' ] = wp_parse_args(
		$overwrite_gradient,
		array(
			'type'              => 'range',
			'title'             => __( 'Width', 'ayecode-connect' ),
			'placeholder'       => '',
			'default'           => '200',
			'desc_tip'          => true,
			'custom_attributes' => array(
				'min' => 100,
				'max' => 300,
			),
			'group'             => __( 'Shape Divider', 'ayecode-connect' ),
			'element_require'   => '[%' . $type . '%]!=""',
		)
	);

	$input[ $type . '_height' ] = array(
		'type'              => 'range',
		'title'             => __( 'Height', 'ayecode-connect' ),
		'default'           => '100',
		'desc_tip'          => true,
		'custom_attributes' => array(
			'min' => 0,
			'max' => 500,
		),
		'group'             => __( 'Shape Divider', 'ayecode-connect' ),
		'element_require'   => '[%' . $type . '%]!=""',
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
		'title'           => __( 'Flip', 'ayecode-connect' ),
		'default'         => '',
		'desc_tip'        => true,
		'group'           => __( 'Shape Divider', 'ayecode-connect' ),
		'element_require' => sd_get_element_require_string( $requires, 'flip', 'sd' ),
	);

	$input[ $type . '_invert' ] = array(
		'type'            => 'checkbox',
		'title'           => __( 'Invert', 'ayecode-connect' ),
		'default'         => '',
		'desc_tip'        => true,
		'group'           => __( 'Shape Divider', 'ayecode-connect' ),
		'element_require' => sd_get_element_require_string( $requires, 'invert', 'sd' ),
	);

	$input[ $type . '_btf' ] = array(
		'type'            => 'checkbox',
		'title'           => __( 'Bring to front', 'ayecode-connect' ),
		'default'         => '',
		'desc_tip'        => true,
		'group'           => __( 'Shape Divider', 'ayecode-connect' ),
		'element_require' => '[%' . $type . '%]!=""',

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
function sd_get_text_color_input( $type = 'text_color', $overwrite = array(), $has_custom = false, $emphasis = true ) {
	$options = array(
		           '' => __( 'None', 'ayecode-connect' ),
	           ) + sd_aui_colors(false,false,false,false,false, true);

	if ( $has_custom ) {
		$options['custom'] = __( 'Custom color', 'ayecode-connect' );
	}

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Text color', 'ayecode-connect' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Typography', 'ayecode-connect' ),
	);

	$input = wp_parse_args( $overwrite, $defaults );

	return $input;
}

function sd_get_text_color_input_group( $type = 'text_color', $overwrite = array(), $overwrite_custom = array() ) {
	$inputs = array();

	if ( $overwrite !== false ) {
		$inputs[ $type ] = sd_get_text_color_input( $type, $overwrite, true );
	}

	if ( $overwrite_custom !== false ) {
		$custom            = $type . '_custom';
		$inputs[ $custom ] = sd_get_custom_color_input( $custom, $overwrite_custom, $type );
	}

	return $inputs;
}

/**
 * A helper function for custom color.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function sd_get_custom_color_input( $type = 'color_custom', $overwrite = array(), $parent_type = '' ) {

	$defaults = array(
		'type'        => 'color',
		'title'       => __( 'Custom color', 'ayecode-connect' ),
		'default'     => '',
		'placeholder' => '',
		'desc_tip'    => true,
		'group'       => __( 'Typography', 'ayecode-connect' ),
	);

	if ( $parent_type ) {
		$defaults['element_require'] = '[%' . $parent_type . '%]=="custom"';
	}

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
		''     => __( 'Default', 'ayecode-connect' ),
		'auto' => __( 'auto', 'ayecode-connect' ),
		'1'    => '1/12',
		'2'    => '2/12',
		'3'    => '3/12',
		'4'    => '4/12',
		'5'    => '5/12',
		'6'    => '6/12',
		'7'    => '7/12',
		'8'    => '8/12',
		'9'    => '9/12',
		'10'   => '10/12',
		'11'   => '11/12',
		'12'   => '12/12',
	);

	$defaults = array(
		'type'            => 'select',
		'title'           => __( 'Column width', 'ayecode-connect' ),
		'options'         => $options,
		'default'         => '',
		'desc_tip'        => true,
		'group'           => __( 'Container', 'ayecode-connect' ),
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
		''  => __( 'auto', 'ayecode-connect' ),
		'1' => '1',
		'2' => '2',
		'3' => '3',
		'4' => '4',
		'5' => '5',
		'6' => '6',
	);

	$defaults = array(
		'type'            => 'select',
		'title'           => __( 'Row columns', 'ayecode-connect' ),
		'options'         => $options,
		'default'         => '',
		'desc_tip'        => true,
		'group'           => __( 'Container', 'ayecode-connect' ),
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
		''                                => __( 'Default', 'ayecode-connect' ),
		'text' . $device_size . '-left'   => __( 'Left', 'ayecode-connect' ),
		'text' . $device_size . '-right'  => __( 'Right', 'ayecode-connect' ),
		'text' . $device_size . '-center' => __( 'Center', 'ayecode-connect' ),
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Text align', 'ayecode-connect' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Typography', 'ayecode-connect' ),
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
		''                                   => __( 'Default', 'ayecode-connect' ),
		'd' . $device_size . '-none'         => 'none',
		'd' . $device_size . '-inline'       => 'inline',
		'd' . $device_size . '-inline-block' => 'inline-block',
		'd' . $device_size . '-block'        => 'block',
		'd' . $device_size . '-table'        => 'table',
		'd' . $device_size . '-table-cell'   => 'table-cell',
		'd' . $device_size . '-table-row'    => 'table-row',
		'd' . $device_size . '-flex'         => 'flex',
		'd' . $device_size . '-inline-flex'  => 'inline-flex',
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Display', 'ayecode-connect' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Wrapper Styles', 'ayecode-connect' ),
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
		'title'    => __( 'Text justify', 'ayecode-connect' ),
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Typography', 'ayecode-connect' ),
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
function sd_aui_colors( $include_branding = false, $include_outlines = false, $outline_button_only_text = false, $include_translucent = false, $include_subtle = false, $include_emphasis = false ) {
	$theme_colors = array();

	$theme_colors['primary']   = __( 'Primary', 'ayecode-connect' );
	$theme_colors['secondary'] = __( 'Secondary', 'ayecode-connect' );
	$theme_colors['success']   = __( 'Success', 'ayecode-connect' );
	$theme_colors['danger']    = __( 'Danger', 'ayecode-connect' );
	$theme_colors['warning']   = __( 'Warning', 'ayecode-connect' );
	$theme_colors['info']      = __( 'Info', 'ayecode-connect' );
	$theme_colors['light']     = __( 'Light', 'ayecode-connect' );
	$theme_colors['dark']      = __( 'Dark', 'ayecode-connect' );
	$theme_colors['black']     = __( 'Black', 'ayecode-connect' );
	$theme_colors['white']     = __( 'White', 'ayecode-connect' );
	$theme_colors['purple']    = __( 'Purple', 'ayecode-connect' );
	$theme_colors['salmon']    = __( 'Salmon', 'ayecode-connect' );
	$theme_colors['cyan']      = __( 'Cyan', 'ayecode-connect' );
	$theme_colors['gray']      = __( 'Gray', 'ayecode-connect' );
	$theme_colors['muted']     = __( 'Muted', 'ayecode-connect' );
	$theme_colors['gray-dark'] = __( 'Gray dark', 'ayecode-connect' );
	$theme_colors['indigo']    = __( 'Indigo', 'ayecode-connect' );
	$theme_colors['orange']    = __( 'Orange', 'ayecode-connect' );
	$theme_colors['body']      = __( 'Body', 'ayecode-connect' );


	// for bg and borders
	if ( $include_subtle ) {
		$theme_colors['primary-subtle']   = __( 'Primary Subtle', 'ayecode-connect' );
		$theme_colors['primary-subtle']   = __( 'Primary Subtle', 'ayecode-connect' );
		$theme_colors['secondary-subtle'] = __( 'Secondary Subtle', 'ayecode-connect' );
		$theme_colors['success-subtle']   = __( 'Success Subtle', 'ayecode-connect' );
		$theme_colors['danger-subtle']    = __( 'Danger Subtle', 'ayecode-connect' );
		$theme_colors['warning-subtle']   = __( 'Warning Subtle', 'ayecode-connect' );
		$theme_colors['info-subtle']      = __( 'Info Subtle', 'ayecode-connect' );
		$theme_colors['light-subtle']     = __( 'Light Subtle', 'ayecode-connect' );
		$theme_colors['dark-subtle']      = __( 'Dark Subtle', 'ayecode-connect' );
		$theme_colors['purple-subtle']    = __( 'Purple Subtle', 'ayecode-connect' );
		$theme_colors['salmon-subtle']    = __( 'Salmon Subtle', 'ayecode-connect' );
		$theme_colors['cyan-subtle']      = __( 'Cyan Subtle', 'ayecode-connect' );
		$theme_colors['gray-subtle']      = __( 'Gray Subtle', 'ayecode-connect' );
		$theme_colors['gray-dark-subtle'] = __( 'Gray dark Subtle', 'ayecode-connect' );
		$theme_colors['indigo-subtle']    = __( 'Indigo Subtle', 'ayecode-connect' );
		$theme_colors['orange-subtle']    = __( 'Orange Subtle', 'ayecode-connect' );
	}

	// for texts
	if ($include_emphasis) {
		$theme_colors['primary-emphasis']   = __( 'Primary Emphasis', 'ayecode-connect' );
		$theme_colors['secondary-emphasis'] = __( 'Secondary Emphasis', 'ayecode-connect' );
		$theme_colors['success-emphasis']   = __( 'Success Emphasis', 'ayecode-connect' );
		$theme_colors['danger-emphasis']    = __( 'Danger Emphasis', 'ayecode-connect' );
		$theme_colors['warning-emphasis']   = __( 'Warning Emphasis', 'ayecode-connect' );
		$theme_colors['info-emphasis']      = __( 'Info Emphasis', 'ayecode-connect' );
		$theme_colors['light-emphasis']     = __( 'Light Emphasis', 'ayecode-connect' );
		$theme_colors['dark-emphasis']      = __( 'Dark Emphasis', 'ayecode-connect' );
		$theme_colors['purple-emphasis']    = __( 'Purple Emphasis', 'ayecode-connect' );
		$theme_colors['salmon-emphasis']    = __( 'Salmon Emphasis', 'ayecode-connect' );
		$theme_colors['cyan-emphasis']      = __( 'Cyan Emphasis', 'ayecode-connect' );
		$theme_colors['gray-emphasis']      = __( 'Gray Emphasis', 'ayecode-connect' );
		$theme_colors['muted-emphasis']     = __( 'Muted Emphasis', 'ayecode-connect' );
		$theme_colors['gray-dark-emphasis'] = __( 'Gray dark Emphasis', 'ayecode-connect' );
		$theme_colors['indigo-emphasis']    = __( 'Indigo Emphasis', 'ayecode-connect' );
		$theme_colors['orange-emphasis']    = __( 'Orange Emphasis', 'ayecode-connect' );
	}

	if ( $include_outlines ) {
		$button_only                       = $outline_button_only_text ? ' ' . __( '(button only)', 'ayecode-connect' ) : '';
		$theme_colors['outline-primary']   = __( 'Primary outline', 'ayecode-connect' ) . $button_only;
		$theme_colors['outline-secondary'] = __( 'Secondary outline', 'ayecode-connect' ) . $button_only;
		$theme_colors['outline-success']   = __( 'Success outline', 'ayecode-connect' ) . $button_only;
		$theme_colors['outline-danger']    = __( 'Danger outline', 'ayecode-connect' ) . $button_only;
		$theme_colors['outline-warning']   = __( 'Warning outline', 'ayecode-connect' ) . $button_only;
		$theme_colors['outline-info']      = __( 'Info outline', 'ayecode-connect' ) . $button_only;
		$theme_colors['outline-light']     = __( 'Light outline', 'ayecode-connect' ) . $button_only;
		$theme_colors['outline-dark']      = __( 'Dark outline', 'ayecode-connect' ) . $button_only;
		$theme_colors['outline-white']     = __( 'White outline', 'ayecode-connect' ) . $button_only;
		$theme_colors['outline-purple']    = __( 'Purple outline', 'ayecode-connect' ) . $button_only;
		$theme_colors['outline-salmon']    = __( 'Salmon outline', 'ayecode-connect' ) . $button_only;
		$theme_colors['outline-cyan']      = __( 'Cyan outline', 'ayecode-connect' ) . $button_only;
		$theme_colors['outline-gray']      = __( 'Gray outline', 'ayecode-connect' ) . $button_only;
		$theme_colors['outline-gray-dark'] = __( 'Gray dark outline', 'ayecode-connect' ) . $button_only;
		$theme_colors['outline-indigo']    = __( 'Indigo outline', 'ayecode-connect' ) . $button_only;
		$theme_colors['outline-orange']    = __( 'Orange outline', 'ayecode-connect' ) . $button_only;
	}

	if ( $include_branding ) {
		$theme_colors = $theme_colors + sd_aui_branding_colors();
	}

	if ( $include_translucent ) {
		$button_only                           = $outline_button_only_text ? ' ' . __( '(button only)', 'ayecode-connect' ) : '';
		$theme_colors['translucent-primary']   = __( 'Primary translucent', 'ayecode-connect' ) . $button_only;
		$theme_colors['translucent-secondary'] = __( 'Secondary translucent', 'ayecode-connect' ) . $button_only;
		$theme_colors['translucent-success']   = __( 'Success translucent', 'ayecode-connect' ) . $button_only;
		$theme_colors['translucent-danger']    = __( 'Danger translucent', 'ayecode-connect' ) . $button_only;
		$theme_colors['translucent-warning']   = __( 'Warning translucent', 'ayecode-connect' ) . $button_only;
		$theme_colors['translucent-info']      = __( 'Info translucent', 'ayecode-connect' ) . $button_only;
		$theme_colors['translucent-light']     = __( 'Light translucent', 'ayecode-connect' ) . $button_only;
		$theme_colors['translucent-dark']      = __( 'Dark translucent', 'ayecode-connect' ) . $button_only;
		$theme_colors['translucent-white']     = __( 'White translucent', 'ayecode-connect' ) . $button_only;
		$theme_colors['translucent-purple']    = __( 'Purple translucent', 'ayecode-connect' ) . $button_only;
		$theme_colors['translucent-salmon']    = __( 'Salmon translucent', 'ayecode-connect' ) . $button_only;
		$theme_colors['translucent-cyan']      = __( 'Cyan translucent', 'ayecode-connect' ) . $button_only;
		$theme_colors['translucent-gray']      = __( 'Gray translucent', 'ayecode-connect' ) . $button_only;
		$theme_colors['translucent-gray-dark'] = __( 'Gray dark translucent', 'ayecode-connect' ) . $button_only;
		$theme_colors['translucent-indigo']    = __( 'Indigo translucent', 'ayecode-connect' ) . $button_only;
		$theme_colors['translucent-orange']    = __( 'Orange translucent', 'ayecode-connect' ) . $button_only;
	}

	return apply_filters( 'sd_aui_colors', $theme_colors, $include_outlines, $include_branding );
}

/**
 * Get the AUI branding colors.
 *
 * @return array
 */
function sd_aui_branding_colors() {
	return array(
		'facebook'  => __( 'Facebook', 'ayecode-connect' ),
		'twitter'   => __( 'Twitter', 'ayecode-connect' ),
		'instagram' => __( 'Instagram', 'ayecode-connect' ),
		'linkedin'  => __( 'Linkedin', 'ayecode-connect' ),
		'flickr'    => __( 'Flickr', 'ayecode-connect' ),
		'github'    => __( 'GitHub', 'ayecode-connect' ),
		'youtube'   => __( 'YouTube', 'ayecode-connect' ),
		'wordpress' => __( 'WordPress', 'ayecode-connect' ),
		'google'    => __( 'Google', 'ayecode-connect' ),
		'yahoo'     => __( 'Yahoo', 'ayecode-connect' ),
		'vkontakte' => __( 'Vkontakte', 'ayecode-connect' ),
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
		'container'       => __( 'container (default)', 'ayecode-connect' ),
		'container-sm'    => 'container-sm',
		'container-md'    => 'container-md',
		'container-lg'    => 'container-lg',
		'container-xl'    => 'container-xl',
		'container-xxl'   => 'container-xxl',
		'container-fluid' => 'container-fluid',
		'row'             => 'row',
		'col'             => 'col',
		'card'            => 'card',
		'card-header'     => 'card-header',
		'card-img-top'    => 'card-img-top',
		'card-body'       => 'card-body',
		'card-footer'     => 'card-footer',
		'list-group'      => 'list-group',
		'list-group-item' => 'list-group-item',
		''                => __( 'no container class', 'ayecode-connect' ),
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Type', 'ayecode-connect' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Container', 'ayecode-connect' ),
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
		''                  => __( 'Default', 'ayecode-connect' ),
		'position-static'   => 'static',
		'position-relative' => 'relative',
		'position-absolute' => 'absolute',
		'position-fixed'    => 'fixed',
		'position-sticky'   => 'sticky',
		'fixed-top'         => 'fixed-top',
		'fixed-bottom'      => 'fixed-bottom',
		'sticky-top'        => 'sticky-top',
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Position', 'ayecode-connect' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Wrapper Styles', 'ayecode-connect' ),
	);

	$input = wp_parse_args( $overwrite, $defaults );

	return $input;
}

/**
 * @param $type
 * @param $overwrite
 *
 * @return array
 */
function sd_get_absolute_position_input( $type = 'absolute_position', $overwrite = array() ) {

	$options = array(
		''              => __( 'Default', 'ayecode-connect' ),
		'top-left'      => 'top-left',
		'top-center'    => 'top-center',
		'top-right'     => 'top-right',
		'center-left'   => 'middle-left',
		'center'        => 'center',
		'center-right'  => 'middle-right',
		'bottom-left'   => 'bottom-left',
		'bottom-center' => 'bottom-center',
		'bottom-right'  => 'bottom-right',
	);

	$defaults = array(
		'type'            => 'select',
		'title'           => __( 'Absolute Position', 'ayecode-connect' ),
		'options'         => $options,
		'default'         => '',
		'desc_tip'        => true,
		'group'           => __( 'Wrapper Styles', 'ayecode-connect' ),
		'element_require' => '[%position%]=="position-absolute"',
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
		'title'           => __( 'Sticky offset', 'ayecode-connect' ),
		//'desc' =>  __( 'Sticky offset', 'ayecode-connect' ),
		'default'         => '',
		'desc_tip'        => true,
		'group'           => __( 'Wrapper Styles', 'ayecode-connect' ),
		'element_require' => '[%position%]=="sticky" || [%position%]=="sticky-top"',
	);

	// title
	if ( $type == 'top' ) {
		$defaults['title'] = __( 'Top offset', 'ayecode-connect' );
		$defaults['icon']  = 'box-top';
		$defaults['row']   = array(
			'title' => __( 'Sticky offset', 'ayecode-connect' ),
			'key'   => 'sticky-offset',
			'open'  => true,
			'class' => 'text-center',
		);
	} elseif ( $type == 'bottom' ) {
		$defaults['title'] = __( 'Bottom offset', 'ayecode-connect' );
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
	global $aui_bs5;

	$options[] = __( 'Inherit from parent', 'ayecode-connect' );
	if ( $aui_bs5 ) {
		// responsive font sizes
		$options['fs-base'] = 'fs-base (body default)';
		$options['fs-6']    = 'fs-6';
		$options['fs-5']    = 'fs-5';
		$options['fs-4']    = 'fs-4';
		$options['fs-3']    = 'fs-3';
		$options['fs-2']    = 'fs-2';
		$options['fs-1']    = 'fs-1';

		// custom
		$options['fs-lg']  = 'fs-lg';
		$options['fs-sm']  = 'fs-sm';
		$options['fs-xs']  = 'fs-xs';
		$options['fs-xxs'] = 'fs-xxs';

	}

	$options = $options + array(
			'h6'        => 'h6',
			'h5'        => 'h5',
			'h4'        => 'h4',
			'h3'        => 'h3',
			'h2'        => 'h2',
			'h1'        => 'h1',
			'display-1' => 'display-1',
			'display-2' => 'display-2',
			'display-3' => 'display-3',
			'display-4' => 'display-4',
		);

	if ( $aui_bs5 ) {
		$options['display-5'] = 'display-5';
		$options['display-6'] = 'display-6';
	}

	if ( $has_custom ) {
		$options['custom'] = __( 'Custom size', 'ayecode-connect' );
	}

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Font size', 'ayecode-connect' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Typography', 'ayecode-connect' ),
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
		'title'             => __( 'Font size (rem)', 'ayecode-connect' ),
		'default'           => '',
		'placeholder'       => '1.25',
		'custom_attributes' => array(
			'step' => '0.1',
			'min'  => '0',
			'max'  => '100',
		),
		'desc_tip'          => true,
		'group'             => __( 'Typography', 'ayecode-connect' ),
	);

	if ( $parent_type ) {
		$defaults['element_require'] = '[%' . $parent_type . '%]=="custom"';
	}

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
function sd_get_font_line_height_input( $type = 'font_line_height', $overwrite = array() ) {

	$defaults = array(
		'type'              => 'number',
		'title'             => __( 'Font Line Height', 'ayecode-connect' ),
		'default'           => '',
		'placeholder'       => '1.75',
		'custom_attributes' => array(
			'step' => '0.1',
			'min'  => '0',
			'max'  => '100',
		),
		'desc_tip'          => true,
		'group'             => __( 'Typography', 'ayecode-connect' ),
	);

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
		$custom            = $type . '_custom';
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
		''                                => __( 'Inherit', 'ayecode-connect' ),
		'font-weight-bold'                => 'bold',
		'font-weight-bolder'              => 'bolder',
		'font-weight-normal'              => 'normal',
		'font-weight-light'               => 'light',
		'font-weight-lighter'             => 'lighter',
		'font-italic'                     => 'italic',
		'font-weight-bold font-italic'    => 'bold italic',
		'font-weight-bolder font-italic'  => 'bolder italic',
		'font-weight-normal font-italic'  => 'normal italic',
		'font-weight-light font-italic'   => 'light italic',
		'font-weight-lighter font-italic' => 'lighter italic',
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Appearance', 'ayecode-connect' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Typography', 'ayecode-connect' ),
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
		''                => __( 'Default', 'ayecode-connect' ),
		'text-lowercase'  => __( 'lowercase', 'ayecode-connect' ),
		'text-uppercase'  => __( 'UPPERCASE', 'ayecode-connect' ),
		'text-capitalize' => __( 'Capitalize', 'ayecode-connect' ),
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Letter case', 'ayecode-connect' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Typography', 'ayecode-connect' ),
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
		''            => __( 'No', 'ayecode-connect' ),
		'font-italic' => __( 'Yes', 'ayecode-connect' ),
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Font italic', 'ayecode-connect' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Typography', 'ayecode-connect' ),
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
		'title'    => __( 'HTML anchor', 'ayecode-connect' ),
		'desc'     => __( 'Enter a word or two — without spaces — to make a unique web address just for this block, called an “anchor.” Then, you’ll be able to link directly to this section of your page.', 'ayecode-connect' ),
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Advanced', 'ayecode-connect' ),
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
		'title'    => __( 'Additional CSS class(es)', 'ayecode-connect' ),
		'desc'     => __( 'Separate multiple classes with spaces.', 'ayecode-connect' ),
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Advanced', 'ayecode-connect' ),
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
function sd_get_custom_name_input( $type = 'metadata_name', $overwrite = array() ) {

	$defaults = array(
		'type'     => 'text',
		'title'    => __( 'Block Name', 'ayecode-connect' ),
		'desc'     => __( 'Set a custom name for this block', 'ayecode-connect' ),
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Advanced', 'ayecode-connect' ),
	);

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
function sd_get_hover_animations_input( $type = 'hover_animations', $overwrite = array() ) {

	$options = array(
		''                 => __( 'none', 'ayecode-connect' ),
		'hover-zoom'       => __( 'Zoom', 'ayecode-connect' ),
		'hover-shadow'     => __( 'Shadow', 'ayecode-connect' ),
		'hover-move-up'    => __( 'Move up', 'ayecode-connect' ),
		'hover-move-down'  => __( 'Move down', 'ayecode-connect' ),
		'hover-move-left'  => __( 'Move left', 'ayecode-connect' ),
		'hover-move-right' => __( 'Move right', 'ayecode-connect' ),
	);

	$defaults = array(
		'type'     => 'select',
		'multiple' => true,
		'title'    => __( 'Hover Animations', 'ayecode-connect' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Hover Animations', 'ayecode-connect' ),
	);

	$input = wp_parse_args( $overwrite, $defaults );

	return $input;
}


function sd_get_flex_align_items_input( $type = 'align-items', $overwrite = array() ) {
	$device_size = '';
	if ( ! empty( $overwrite['device_type'] ) ) {
		if ( $overwrite['device_type'] == 'Tablet' ) {
			$device_size = '-md';
		} elseif ( $overwrite['device_type'] == 'Desktop' ) {
			$device_size = '-lg';
		}
	}
	$options = array(
		''                                         => __( 'Default', 'ayecode-connect' ),
		'align-items' . $device_size . '-start'    => 'align-items-start',
		'align-items' . $device_size . '-end'      => 'align-items-end',
		'align-items' . $device_size . '-center'   => 'align-items-center',
		'align-items' . $device_size . '-baseline' => 'align-items-baseline',
		'align-items' . $device_size . '-stretch'  => 'align-items-stretch',
	);

	$defaults = array(
		'type'            => 'select',
		'title'           => __( 'Vertical Align Items', 'ayecode-connect' ),
		'options'         => $options,
		'default'         => '',
		'desc_tip'        => true,
		'group'           => __( 'Wrapper Styles', 'ayecode-connect' ),
		'element_require' => ' ( ( [%container%]=="row" ) || ( [%display%]=="d-flex" || [%display_md%]=="d-md-flex" || [%display_lg%]=="d-lg-flex" ) ) ',

	);

	$input = wp_parse_args( $overwrite, $defaults );

	return $input;
}

function sd_get_flex_align_items_input_group( $type = 'flex_align_items', $overwrite = array() ) {
	$inputs = array();
	$sizes  = array(
		''    => 'Mobile',
		'_md' => 'Tablet',
		'_lg' => 'Desktop',
	);

	if ( $overwrite !== false ) {

		foreach ( $sizes as $ds => $dt ) {
			$overwrite['device_type'] = $dt;
			$inputs[ $type . $ds ]    = sd_get_flex_align_items_input( $type, $overwrite );
		}
	}

	return $inputs;
}

function sd_get_flex_justify_content_input( $type = 'flex_justify_content', $overwrite = array() ) {
	$device_size = '';
	if ( ! empty( $overwrite['device_type'] ) ) {
		if ( $overwrite['device_type'] == 'Tablet' ) {
			$device_size = '-md';
		} elseif ( $overwrite['device_type'] == 'Desktop' ) {
			$device_size = '-lg';
		}
	}
	$options = array(
		''                                            => __( 'Default', 'ayecode-connect' ),
		'justify-content' . $device_size . '-start'   => 'justify-content-start',
		'justify-content' . $device_size . '-end'     => 'justify-content-end',
		'justify-content' . $device_size . '-center'  => 'justify-content-center',
		'justify-content' . $device_size . '-between' => 'justify-content-between',
		'justify-content' . $device_size . '-stretch' => 'justify-content-around',
	);

	$defaults = array(
		'type'            => 'select',
		'title'           => __( 'Justify content', 'ayecode-connect' ),
		'options'         => $options,
		'default'         => '',
		'desc_tip'        => true,
		'group'           => __( 'Wrapper Styles', 'ayecode-connect' ),
		'element_require' => '( ( [%container%]=="row" ) || ( [%display%]=="d-flex" || [%display_md%]=="d-md-flex" || [%display_lg%]=="d-lg-flex" ) ) ',

	);

	$input = wp_parse_args( $overwrite, $defaults );

	return $input;
}

function sd_get_flex_justify_content_input_group( $type = 'flex_justify_content', $overwrite = array() ) {
	$inputs = array();
	$sizes  = array(
		''    => 'Mobile',
		'_md' => 'Tablet',
		'_lg' => 'Desktop',
	);

	if ( $overwrite !== false ) {

		foreach ( $sizes as $ds => $dt ) {
			$overwrite['device_type'] = $dt;
			$inputs[ $type . $ds ]    = sd_get_flex_justify_content_input( $type, $overwrite );
		}
	}

	return $inputs;
}


function sd_get_flex_align_self_input( $type = 'flex_align_self', $overwrite = array() ) {
	$device_size = '';
	if ( ! empty( $overwrite['device_type'] ) ) {
		if ( $overwrite['device_type'] == 'Tablet' ) {
			$device_size = '-md';
		} elseif ( $overwrite['device_type'] == 'Desktop' ) {
			$device_size = '-lg';
		}
	}
	$options = array(
		''                                         => __( 'Default', 'ayecode-connect' ),
		'align-items' . $device_size . '-start'    => 'align-items-start',
		'align-items' . $device_size . '-end'      => 'align-items-end',
		'align-items' . $device_size . '-center'   => 'align-items-center',
		'align-items' . $device_size . '-baseline' => 'align-items-baseline',
		'align-items' . $device_size . '-stretch'  => 'align-items-stretch',
	);

	$defaults = array(
		'type'            => 'select',
		'title'           => __( 'Align Self', 'ayecode-connect' ),
		'options'         => $options,
		'default'         => '',
		'desc_tip'        => true,
		'group'           => __( 'Wrapper Styles', 'ayecode-connect' ),
		'element_require' => ' [%container%]=="col" ',

	);

	$input = wp_parse_args( $overwrite, $defaults );

	return $input;
}

function sd_get_flex_align_self_input_group( $type = 'flex_align_self', $overwrite = array() ) {
	$inputs = array();
	$sizes  = array(
		''    => 'Mobile',
		'_md' => 'Tablet',
		'_lg' => 'Desktop',
	);

	if ( $overwrite !== false ) {

		foreach ( $sizes as $ds => $dt ) {
			$overwrite['device_type'] = $dt;
			$inputs[ $type . $ds ]    = sd_get_flex_align_self_input( $type, $overwrite );
		}
	}

	return $inputs;
}

function sd_get_flex_order_input( $type = 'flex_order', $overwrite = array() ) {
	$device_size = '';
	if ( ! empty( $overwrite['device_type'] ) ) {
		if ( $overwrite['device_type'] == 'Tablet' ) {
			$device_size = '-md';
		} elseif ( $overwrite['device_type'] == 'Desktop' ) {
			$device_size = '-lg';
		}
	}
	$options = array(
		'' => __( 'Default', 'ayecode-connect' ),
	);

	$i = 0;
	while ( $i <= 5 ) {
		$options[ 'order' . $device_size . '-' . $i ] = $i;
		$i++;
	}

	$defaults = array(
		'type'            => 'select',
		'title'           => __( 'Flex Order', 'ayecode-connect' ),
		'options'         => $options,
		'default'         => '',
		'desc_tip'        => true,
		'group'           => __( 'Wrapper Styles', 'ayecode-connect' ),
		'element_require' => ' [%container%]=="col" ',

	);

	$input = wp_parse_args( $overwrite, $defaults );

	return $input;
}

function sd_get_flex_order_input_group( $type = 'flex_order', $overwrite = array() ) {
	$inputs = array();
	$sizes  = array(
		''    => 'Mobile',
		'_md' => 'Tablet',
		'_lg' => 'Desktop',
	);

	if ( $overwrite !== false ) {

		foreach ( $sizes as $ds => $dt ) {
			$overwrite['device_type'] = $dt;
			$inputs[ $type . $ds ]    = sd_get_flex_order_input( $type, $overwrite );
		}
	}

	return $inputs;
}

function sd_get_flex_wrap_group( $type = 'flex_wrap', $overwrite = array() ) {
	$inputs = array();
	$sizes  = array(
		''    => 'Mobile',
		'_md' => 'Tablet',
		'_lg' => 'Desktop',
	);

	if ( $overwrite !== false ) {

		foreach ( $sizes as $ds => $dt ) {
			$overwrite['device_type'] = $dt;
			$inputs[ $type . $ds ]    = sd_get_flex_wrap_input( $type, $overwrite );
		}
	}

	return $inputs;
}

function sd_get_flex_wrap_input( $type = 'flex_wrap', $overwrite = array() ) {
	$device_size = '';
	if ( ! empty( $overwrite['device_type'] ) ) {
		if ( $overwrite['device_type'] == 'Tablet' ) {
			$device_size = '-md';
		} elseif ( $overwrite['device_type'] == 'Desktop' ) {
			$device_size = '-lg';
		}
	}
	$options = array(
		''                                      => __( 'Default', 'ayecode-connect' ),
		'flex' . $device_size . '-nowrap'       => 'nowrap',
		'flex' . $device_size . '-wrap'         => 'wrap',
		'flex' . $device_size . '-wrap-reverse' => 'wrap-reverse',
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Flex wrap', 'ayecode-connect' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Wrapper Styles', 'ayecode-connect' ),
	);

	$input = wp_parse_args( $overwrite, $defaults );

	return $input;
}

function sd_get_float_group( $type = 'float', $overwrite = array() ) {
	$inputs = array();
	$sizes  = array(
		''    => 'Mobile',
		'_md' => 'Tablet',
		'_lg' => 'Desktop',
	);

	if ( $overwrite !== false ) {

		foreach ( $sizes as $ds => $dt ) {
			$overwrite['device_type'] = $dt;
			$inputs[ $type . $ds ]    = sd_get_float_input( $type, $overwrite );
		}
	}

	return $inputs;
}
function sd_get_float_input( $type = 'float', $overwrite = array() ) {
	$device_size = '';
	if ( ! empty( $overwrite['device_type'] ) ) {
		if ( $overwrite['device_type'] == 'Tablet' ) {
			$device_size = '-md';
		} elseif ( $overwrite['device_type'] == 'Desktop' ) {
			$device_size = '-lg';
		}
	}
	$options = array(
		''                                      => __( 'Default', 'ayecode-connect' ),
		'float' . $device_size . '-start'       => 'left',
		'float' . $device_size . '-end'         => 'right',
		'float' . $device_size . '-none' => 'none',
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Float', 'ayecode-connect' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Wrapper Styles', 'ayecode-connect' ),
	);

	$input = wp_parse_args( $overwrite, $defaults );

	return $input;
}

/**
 * @param $type
 * @param $overwrite
 *
 * @return array
 */
function sd_get_zindex_input( $type = 'zindex', $overwrite = array() ) {

	$options = array(
		''          => __( 'Default', 'ayecode-connect' ),
		'zindex-0'  => '0',
		'zindex-1'  => '1',
		'zindex-5'  => '5',
		'zindex-10' => '10',
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Z-index', 'ayecode-connect' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Wrapper Styles', 'ayecode-connect' ),
	);

	$input = wp_parse_args( $overwrite, $defaults );

	return $input;
}

/**
 * @param $type
 * @param $overwrite
 *
 * @return array
 */
function sd_get_overflow_input( $type = 'overflow', $overwrite = array() ) {

	$options = array(
		''                 => __( 'Default', 'ayecode-connect' ),
		'overflow-auto'    => __( 'Auto', 'ayecode-connect' ),
		'overflow-hidden'  => __( 'Hidden', 'ayecode-connect' ),
		'overflow-visible' => __( 'Visible', 'ayecode-connect' ),
		'overflow-scroll'  => __( 'Scroll', 'ayecode-connect' ),
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Overflow', 'ayecode-connect' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Wrapper Styles', 'ayecode-connect' ),
	);

	$input = wp_parse_args( $overwrite, $defaults );

	return $input;
}

/**
 * @param $type
 * @param $overwrite
 *
 * @return array
 */
function sd_get_max_height_input( $type = 'max_height', $overwrite = array() ) {

	$defaults = array(
		'type'        => 'text',
		'title'       => __( 'Max height', 'ayecode-connect' ),
		'value'       => '',
		'default'     => '',
		'placeholder' => '',
		'desc_tip'    => true,
		'group'       => __( 'Wrapper Styles', 'ayecode-connect' ),
	);

	$input = wp_parse_args( $overwrite, $defaults );

	return $input;
}

/**
 * @param $type
 * @param $overwrite
 *
 * @return array
 */
function sd_get_scrollbars_input( $type = 'scrollbars', $overwrite = array() ) {

	$options = array(
		''               => __( 'Default', 'ayecode-connect' ),
		'scrollbars-ios' => __( 'IOS Style', 'ayecode-connect' ),
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Scrollbars', 'ayecode-connect' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Wrapper Styles', 'ayecode-connect' ),
	);

	$input = wp_parse_args( $overwrite, $defaults );

	return $input;
}

/**
 * @param $type
 * @param $overwrite
 *
 * @return array
 */
function sd_get_new_window_input( $type = 'target', $overwrite = array() ) {

	$defaults = array(
		'type'     => 'checkbox',
		'title'    => __( 'Open in new window', 'ayecode-connect' ),
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Link', 'ayecode-connect' ),
	);

	$input = wp_parse_args( $overwrite, $defaults );

	return $input;
}

/**
 * @param $type
 * @param $overwrite
 *
 * @return array
 */
function sd_get_nofollow_input( $type = 'nofollow', $overwrite = array() ) {

	$defaults = array(
		'type'     => 'checkbox',
		'title'    => __( 'Add nofollow', 'ayecode-connect' ),
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Link', 'ayecode-connect' ),
	);

	$input = wp_parse_args( $overwrite, $defaults );

	return $input;
}

/**
 * A helper function for width inputs.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function sd_get_width_input( $type = 'width', $overwrite = array() ) {

	$device_size = '';
	if ( ! empty( $overwrite['device_type'] ) ) {
		if ( $overwrite['device_type'] == 'Tablet' ) {
			$device_size = '-md';
		} elseif ( $overwrite['device_type'] == 'Desktop' ) {
			$device_size = '-lg';
		}
	}
	$options = array(
		'' => __('Default', 'ayecode-connect'),
		'w' . $device_size . '-25' => '25%',
		'w' . $device_size . '-50' => '50%',
		'w' . $device_size . '-75' => '75%',
		'w' . $device_size . '-100' => '100%',
		'w' . $device_size . '-auto' => 'auto',
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Width', 'ayecode-connect' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Wrapper Styles', 'ayecode-connect' ),
	);

	$input = wp_parse_args( $overwrite, $defaults );

	return $input;
}

/**
 * A helper function for height inputs.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function sd_get_height_input( $type = 'height', $overwrite = array() ) {

	$device_size = '';
	if ( ! empty( $overwrite['device_type'] ) ) {
		if ( $overwrite['device_type'] == 'Tablet' ) {
			$device_size = '-md';
		} elseif ( $overwrite['device_type'] == 'Desktop' ) {
			$device_size = '-lg';
		}
	}
	$options = array(
		'' => __('Default', 'ayecode-connect'),
		'h' . $device_size . '-25' => '25%',
		'h' . $device_size . '-50' => '50%',
		'h' . $device_size . '-75' => '75%',
		'h' . $device_size . '-100' => '100%',
		'h' . $device_size . '-auto' => 'auto',
	);

	$defaults = array(
		'type'     => 'select',
		'title'    => __( 'Height', 'ayecode-connect' ),
		'options'  => $options,
		'default'  => '',
		'desc_tip' => true,
		'group'    => __( 'Wrapper Styles', 'ayecode-connect' ),
	);

	$input = wp_parse_args( $overwrite, $defaults );

	return $input;
}

/**
 * @param $type
 * @param $overwrite
 *
 * @return array
 */
function sd_get_attributes_input( $type = 'attributes', $overwrite = array() ) {

	$defaults = array(
		'type'        => 'text',
		'title'       => __( 'Custom Attributes', 'ayecode-connect' ),
		'value'       => '',
		'default'     => '',
		'placeholder' => 'key|value,key2|value2',
		'desc_tip'    => true,
		'group'       => __( 'Link', 'ayecode-connect' ),
	);

	$input = wp_parse_args( $overwrite, $defaults );

	return $input;
}

/**
 * @param $args
 *
 * @return string
 */
function sd_build_attributes_string_escaped( $args ) {
	global $aui_bs5;

	$attributes = array();
	$string_escaped = '';

	if ( ! empty( $args['custom'] ) ) {
		$attributes = sd_parse_custom_attributes($args['custom']);
	}

	// new window
	if ( ! empty( $args['new_window'] ) ) {
		$attributes['target'] = '_blank';
	}

	// nofollow
	if ( ! empty( $args['nofollow'] ) ) {
		$attributes['rel'] = isset($attributes['rel']) ? $attributes['rel'] . ' nofollow' : 'nofollow';
	}

	if(!empty($attributes )){
		foreach ( $attributes as $key => $val ) {
			$string_escaped .= esc_attr($key) . '="' . esc_attr($val) . '" ';
		}
	}

	return $string_escaped;
}

/**
 * @info borrowed from elementor
 *
 * @param $attributes_string
 * @param $delimiter
 *
 * @return array
 */
function sd_parse_custom_attributes( $attributes_string, $delimiter = ',' ) {
	$attributes = explode( $delimiter, $attributes_string );
	$result = [];

	foreach ( $attributes as $attribute ) {
		$attr_key_value = explode( '|', $attribute );

		$attr_key = mb_strtolower( $attr_key_value[0] );

		// Remove any not allowed characters.
		preg_match( '/[-_a-z0-9]+/', $attr_key, $attr_key_matches );

		if ( empty( $attr_key_matches[0] ) ) {
			continue;
		}

		$attr_key = $attr_key_matches[0];

		// Avoid Javascript events and unescaped href.
		if ( 'href' === $attr_key || 'on' === substr( $attr_key, 0, 2 ) ) {
			continue;
		}

		if ( isset( $attr_key_value[1] ) ) {
			$attr_value = trim( $attr_key_value[1] );
		} else {
			$attr_value = '';
		}

		$result[ $attr_key ] = $attr_value;
	}

	return $result;
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
	global $aui_bs5;

	$classes = array();

	if ( $aui_bs5 ) {
		$p_ml = 'ms-';
		$p_mr = 'me-';

		$p_pl = 'ps-';
		$p_pr = 'pe-';
	} else {
		$p_ml = 'ml-';
		$p_mr = 'mr-';

		$p_pl = 'pl-';
		$p_pr = 'pr-';
	}

	// margins.
	if ( isset( $args['mt'] ) && $args['mt'] !== '' ) {
		$classes[] = 'mt-' . sanitize_html_class( $args['mt'] );
		$mt        = $args['mt'];
	} else {
		$mt = null;
	}
	if ( isset( $args['mr'] ) && $args['mr'] !== '' ) {
		$classes[] = $p_mr . sanitize_html_class( $args['mr'] );
		$mr        = $args['mr'];
	} else {
		$mr = null;
	}
	if ( isset( $args['mb'] ) && $args['mb'] !== '' ) {
		$classes[] = 'mb-' . sanitize_html_class( $args['mb'] );
		$mb        = $args['mb'];
	} else {
		$mb = null;
	}
	if ( isset( $args['ml'] ) && $args['ml'] !== '' ) {
		$classes[] = $p_ml . sanitize_html_class( $args['ml'] );
		$ml        = $args['ml'];
	} else {
		$ml = null;
	}

	// margins tablet.
	if ( isset( $args['mt_md'] ) && $args['mt_md'] !== '' ) {
		$classes[] = 'mt-md-' . sanitize_html_class( $args['mt_md'] );
		$mt_md     = $args['mt_md'];
	} else {
		$mt_md = null;
	}
	if ( isset( $args['mr_md'] ) && $args['mr_md'] !== '' ) {
		$classes[] = $p_mr . 'md-' . sanitize_html_class( $args['mr_md'] );
		$mt_md     = $args['mr_md'];
	} else {
		$mr_md = null;
	}
	if ( isset( $args['mb_md'] ) && $args['mb_md'] !== '' ) {
		$classes[] = 'mb-md-' . sanitize_html_class( $args['mb_md'] );
		$mt_md     = $args['mb_md'];
	} else {
		$mb_md = null;
	}
	if ( isset( $args['ml_md'] ) && $args['ml_md'] !== '' ) {
		$classes[] = $p_ml . 'md-' . sanitize_html_class( $args['ml_md'] );
		$mt_md     = $args['ml_md'];
	} else {
		$ml_md = null;
	}

	// margins desktop.
	if ( isset( $args['mt_lg'] ) && $args['mt_lg'] !== '' ) {
		if ( $mt == null && $mt_md == null ) {
			$classes[] = 'mt-' . sanitize_html_class( $args['mt_lg'] );
		} else {
			$classes[] = 'mt-lg-' . sanitize_html_class( $args['mt_lg'] );
		}
	}
	if ( isset( $args['mr_lg'] ) && $args['mr_lg'] !== '' ) {
		if ( $mr == null && $mr_md == null ) {
			$classes[] = $p_mr . sanitize_html_class( $args['mr_lg'] );
		} else {
			$classes[] = $p_mr . 'lg-' . sanitize_html_class( $args['mr_lg'] );
		}
	}
	if ( isset( $args['mb_lg'] ) && $args['mb_lg'] !== '' ) {
		if ( $mb == null && $mb_md == null ) {
			$classes[] = 'mb-' . sanitize_html_class( $args['mb_lg'] );
		} else {
			$classes[] = 'mb-lg-' . sanitize_html_class( $args['mb_lg'] );
		}
	}
	if ( isset( $args['ml_lg'] ) && $args['ml_lg'] !== '' ) {
		if ( $ml == null && $ml_md == null ) {
			$classes[] = $p_ml . sanitize_html_class( $args['ml_lg'] );
		} else {
			$classes[] = $p_ml . 'lg-' . sanitize_html_class( $args['ml_lg'] );
		}
	}

	// padding.
	if ( isset( $args['pt'] ) && $args['pt'] !== '' ) {
		$classes[] = 'pt-' . sanitize_html_class( $args['pt'] );
		$pt        = $args['pt'];
	} else {
		$pt = null;
	}
	if ( isset( $args['pr'] ) && $args['pr'] !== '' ) {
		$classes[] = $p_pr . sanitize_html_class( $args['pr'] );
		$pr        = $args['pr'];
	} else {
		$pr = null;
	}
	if ( isset( $args['pb'] ) && $args['pb'] !== '' ) {
		$classes[] = 'pb-' . sanitize_html_class( $args['pb'] );
		$pb        = $args['pb'];
	} else {
		$pb = null;
	}
	if ( isset( $args['pl'] ) && $args['pl'] !== '' ) {
		$classes[] = $p_pl . sanitize_html_class( $args['pl'] );
		$pl        = $args['pl'];
	} else {
		$pl = null;
	}

	// padding tablet.
	if ( isset( $args['pt_md'] ) && $args['pt_md'] !== '' ) {
		$classes[] = 'pt-md-' . sanitize_html_class( $args['pt_md'] );
		$pt_md     = $args['pt_md'];
	} else {
		$pt_md = null;
	}
	if ( isset( $args['pr_md'] ) && $args['pr_md'] !== '' ) {
		$classes[] = $p_pr . 'md-' . sanitize_html_class( $args['pr_md'] );
		$pr_md     = $args['pr_md'];
	} else {
		$pr_md = null;
	}
	if ( isset( $args['pb_md'] ) && $args['pb_md'] !== '' ) {
		$classes[] = 'pb-md-' . sanitize_html_class( $args['pb_md'] );
		$pb_md     = $args['pb_md'];
	} else {
		$pb_md = null;
	}
	if ( isset( $args['pl_md'] ) && $args['pl_md'] !== '' ) {
		$classes[] = $p_pl . 'md-' . sanitize_html_class( $args['pl_md'] );
		$pl_md     = $args['pl_md'];
	} else {
		$pl_md = null;
	}

	// padding desktop.
	if ( isset( $args['pt_lg'] ) && $args['pt_lg'] !== '' ) {
		if ( $pt == null && $pt_md == null ) {
			$classes[] = 'pt-' . sanitize_html_class( $args['pt_lg'] );
		} else {
			$classes[] = 'pt-lg-' . sanitize_html_class( $args['pt_lg'] );
		}
	}
	if ( isset( $args['pr_lg'] ) && $args['pr_lg'] !== '' ) {
		if ( $pr == null && $pr_md == null ) {
			$classes[] = $p_pr . sanitize_html_class( $args['pr_lg'] );
		} else {
			$classes[] = $p_pr . 'lg-' . sanitize_html_class( $args['pr_lg'] );
		}
	}
	if ( isset( $args['pb_lg'] ) && $args['pb_lg'] !== '' ) {
		if ( $pb == null && $pb_md == null ) {
			$classes[] = 'pb-' . sanitize_html_class( $args['pb_lg'] );
		} else {
			$classes[] = 'pb-lg-' . sanitize_html_class( $args['pb_lg'] );
		}
	}
	if ( isset( $args['pl_lg'] ) && $args['pl_lg'] !== '' ) {
		if ( $pl == null && $pl_md == null ) {
			$classes[] = $p_pl . sanitize_html_class( $args['pl_lg'] );
		} else {
			$classes[] = $p_pl . 'lg-' . sanitize_html_class( $args['pl_lg'] );
		}
	}

	// row cols, mobile, tablet, desktop
	if ( ! empty( $args['row_cols'] ) && $args['row_cols'] !== '' ) {
		$classes[] = sanitize_html_class( 'row-cols-' . $args['row_cols'] );
		$row_cols  = $args['row_cols'];
	} else {
		$row_cols = null;
	}
	if ( ! empty( $args['row_cols_md'] ) && $args['row_cols_md'] !== '' ) {
		$classes[]   = sanitize_html_class( 'row-cols-md-' . $args['row_cols_md'] );
		$row_cols_md = $args['row_cols_md'];
	} else {
		$row_cols_md = null;
	}
	if ( ! empty( $args['row_cols_lg'] ) && $args['row_cols_lg'] !== '' ) {
		if ( $row_cols == null && $row_cols_md == null ) {
			$classes[] = sanitize_html_class( 'row-cols-' . $args['row_cols_lg'] );
		} else {
			$classes[] = sanitize_html_class( 'row-cols-lg-' . $args['row_cols_lg'] );
		}
	}

	// columns , mobile, tablet, desktop
	if ( ! empty( $args['col'] ) && $args['col'] !== '' ) {
		$classes[] = sanitize_html_class( 'col-' . $args['col'] );
		$col       = $args['col'];
	} else {
		$col = null;
	}
	if ( ! empty( $args['col_md'] ) && $args['col_md'] !== '' ) {
		$classes[] = sanitize_html_class( 'col-md-' . $args['col_md'] );
		$col_md    = $args['col_md'];
	} else {
		$col_md = null;
	}
	if ( ! empty( $args['col_lg'] ) && $args['col_lg'] !== '' ) {
		if ( $col == null && $col_md == null ) {
			$classes[] = sanitize_html_class( 'col-' . $args['col_lg'] );
		} else {
			$classes[] = sanitize_html_class( 'col-lg-' . $args['col_lg'] );
		}
	}

	// border
	if ( isset( $args['border'] ) && ( $args['border'] == 'none' || $args['border'] === '0' || $args['border'] === 0 ) ) {
		$classes[] = 'border-0';
	} elseif ( ! empty( $args['border'] ) ) {
		$border_class = 'border';
		if ( ! empty( $args['border_type'] ) && strpos( $args['border_type'], '-0' ) === false ) {
			$border_class = '';
		}
		$classes[] = $border_class . ' border-' . sanitize_html_class( $args['border'] );
	}

	// border radius type
	if ( ! empty( $args['rounded'] ) ) {
		$classes[] = sanitize_html_class( $args['rounded'] );
	}

	// border radius size BS4
	if ( isset( $args['rounded_size'] ) && in_array( $args['rounded_size'], array( 'sm', 'lg' ) ) ) {
		$classes[] = 'rounded-' . sanitize_html_class( $args['rounded_size'] );
		// if we set a size then we need to remove "rounded" if set
		if ( ( $key = array_search( 'rounded', $classes ) ) !== false ) {
			unset( $classes[ $key ] );
		}
	} else {

		// border radius size , mobile, tablet, desktop
		if ( isset( $args['rounded_size'] ) && $args['rounded_size'] !== '' ) {
			$classes[]    = sanitize_html_class( 'rounded-' . $args['rounded_size'] );
			$rounded_size = $args['rounded_size'];
		} else {
			$rounded_size = null;
		}
		if ( isset( $args['rounded_size_md'] ) && $args['rounded_size_md'] !== '' ) {
			$classes[]       = sanitize_html_class( 'rounded-md-' . $args['rounded_size_md'] );
			$rounded_size_md = $args['rounded_size_md'];
		} else {
			$rounded_size_md = null;
		}
		if ( isset( $args['rounded_size_lg'] ) && $args['rounded_size_lg'] !== '' ) {
			if ( $rounded_size == null && $rounded_size_md == null ) {
				$classes[] = sanitize_html_class( 'rounded-' . $args['rounded_size_lg'] );
			} else {
				$classes[] = sanitize_html_class( 'rounded-lg-' . $args['rounded_size_lg'] );
			}
		}
	}

	// shadow
	//if ( !empty( $args['shadow'] ) ) { $classes[] = sanitize_html_class($args['shadow']); }

	// background
	if ( ! empty( $args['bg'] ) ) {
		$classes[] = 'bg-' . sanitize_html_class( $args['bg'] );
	}

	// background image fixed bg_image_fixed this helps fix a iOS bug
	if ( ! empty( $args['bg_image_fixed'] ) ) {
		$classes[] = 'bg-image-fixed';
	}

	// text_color
	if ( ! empty( $args['text_color'] ) ) {
		$classes[] = 'text-' . sanitize_html_class( $args['text_color'] );
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
				$classes[] = sanitize_html_class( str_replace( '-lg', '', $args['text_align_lg'] ) );
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
			$classes[] = sanitize_html_class( str_replace( '-lg', '', $args['display_lg'] ) );
		} else {
			$classes[] = sanitize_html_class( $args['display_lg'] );
		}
	}

	// bgtus - background transparent until scroll
	if ( ! empty( $args['bgtus'] ) ) {
		$classes[] = sanitize_html_class( 'bg-transparent-until-scroll' );
	}

	// cscos - change color scheme on scroll
	if ( ! empty( $args['bgtus'] ) && ! empty( $args['cscos'] ) ) {
		$classes[] = sanitize_html_class( 'color-scheme-flip-on-scroll' );
	}

	// hover animations
	if ( ! empty( $args['hover_animations'] ) ) {
		$classes[] = sd_sanitize_html_classes( str_replace( ',', ' ', $args['hover_animations'] ) );
	}

	// absolute_position
	if ( ! empty( $args['absolute_position'] ) ) {
		if ( 'top-left' === $args['absolute_position'] ) {
			$classes[] = 'start-0 top-0';
		} elseif ( 'top-center' === $args['absolute_position'] ) {
			$classes[] = 'start-50 top-0 translate-middle';
		} elseif ( 'top-right' === $args['absolute_position'] ) {
			$classes[] = 'end-0 top-0';
		} elseif ( 'center-left' === $args['absolute_position'] ) {
			$classes[] = 'start-0 top-50';
		} elseif ( 'center' === $args['absolute_position'] ) {
			$classes[] = 'start-50 top-50 translate-middle';
		} elseif ( 'center-right' === $args['absolute_position'] ) {
			$classes[] = 'end-0 top-50';
		} elseif ( 'bottom-left' === $args['absolute_position'] ) {
			$classes[] = 'start-0 bottom-0';
		} elseif ( 'bottom-center' === $args['absolute_position'] ) {
			$classes[] = 'start-50 bottom-0 translate-middle';
		} elseif ( 'bottom-right' === $args['absolute_position'] ) {
			$classes[] = 'end-0 bottom-0';
		}
	}

	// build classes from build keys
	$build_keys = sd_get_class_build_keys();
	if ( ! empty( $build_keys ) ) {
		foreach ( $build_keys as $key ) {

			if ( substr( $key, -4 ) == '-MTD' ) {

				$k = str_replace( '-MTD', '', $key );

				// Mobile, Tablet, Desktop
				if ( ! empty( $args[ $k ] ) && $args[ $k ] !== '' ) {
					$classes[] = sanitize_html_class( $args[ $k ] );
					$v         = $args[ $k ];
				} else {
					$v = null;
				}
				if ( ! empty( $args[ $k . '_md' ] ) && $args[ $k . '_md' ] !== '' ) {
					$classes[] = sanitize_html_class( $args[ $k . '_md' ] );
					$v_md      = $args[ $k . '_md' ];
				} else {
					$v_md = null;
				}
				if ( ! empty( $args[ $k . '_lg' ] ) && $args[ $k . '_lg' ] !== '' ) {
					if ( $v == null && $v_md == null ) {
						$classes[] = sanitize_html_class( str_replace( '-lg', '', $args[ $k . '_lg' ] ) );
					} else {
						$classes[] = sanitize_html_class( $args[ $k . '_lg' ] );
					}
				}
			} else {
				if ( $key == 'font_size' && ! empty( $args[ $key ] ) && $args[ $key ] == 'custom' ) {
					continue;
				}
				if ( ! empty( $args[ $key ] ) ) {
					$classes[] = sd_sanitize_html_classes( $args[ $key ] );
				}
			}
		}
	}

	if ( ! empty( $classes ) ) {
		$classes = array_unique( array_filter( array_map( 'trim', $classes ) ) );
	}

	return implode( ' ', $classes );
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
		} elseif ( $args['bg'] == 'custom-gradient' ) {
			$styles['background-image'] = $args['bg_gradient'];

			// use background on text.
			if ( ! empty( $args['bg_on_text'] ) && $args['bg_on_text'] ) {
				$styles['background-clip']         = 'text';
				$styles['-webkit-background-clip'] = 'text';
				$styles['text-fill-color']         = 'transparent';
				$styles['-webkit-text-fill-color'] = 'transparent';
			}
		}
	}

	if ( ! empty( $args['bg_image'] ) && $args['bg_image'] !== '' ) {
		$hasImage = true;
		if ( ! empty( $styles['background-color'] ) && $args['bg'] == 'custom-color' ) {
			$styles['background-image']      = 'url(' . $args['bg_image'] . ')';
			$styles['background-blend-mode'] = 'overlay';
		} elseif ( ! empty( $styles['background-image'] ) && $args['bg'] == 'custom-gradient' ) {
			$styles['background-image'] .= ',url(' . $args['bg_image'] . ')';
		} elseif ( ! empty( $args['bg'] ) && $args['bg'] != '' && $args['bg'] != 'transparent' ) {
			// do nothing as we alreay have a preset
			$hasImage = false;
		} else {
			$styles['background-image'] = 'url(' . $args['bg_image'] . ')';
		}

		if ( $hasImage ) {
			$styles['background-size'] = 'cover';

			if ( ! empty( $args['bg_image_fixed'] ) && $args['bg_image_fixed'] ) {
				$styles['background-attachment'] = 'fixed';
			}
		}

		if ( $hasImage && ! empty( $args['bg_image_xy'] ) && ! empty( $args['bg_image_xy']['x'] ) ) {
			$styles['background-position'] = ( $args['bg_image_xy']['x'] * 100 ) . '% ' . ( $args['bg_image_xy']['y'] * 100 ) . '%';
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
		$styles['font-size'] = (float) $args['font_size_custom'] . 'rem';
	}

	// font color
	if ( ! empty( $args['text_color_custom'] ) && $args['text_color_custom'] !== '' ) {
		$styles['color'] = esc_attr( $args['text_color_custom'] );
	}

	// font line height
	if ( ! empty( $args['font_line_height'] ) && $args['font_line_height'] !== '' ) {
		$styles['line-height'] = esc_attr( $args['font_line_height'] );
	}

	// max height
	if ( ! empty( $args['max_height'] ) && $args['max_height'] !== '' ) {
		$styles['max-height'] = esc_attr( $args['max_height'] );
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
 * Build the hover styles from args.
 *
 * @param $args
 * @param $is_preview
 *
 * @return string
 */
function sd_build_hover_styles( $args, $is_preview = false ) {
	$rules = '';
	// text color
	if ( ! empty( $args['styleid'] ) ) {
		$styleid = $is_preview ? 'html .editor-styles-wrapper .' . esc_attr( $args['styleid'] ) : 'html .' . esc_attr( $args['styleid'] );

		// text
		if ( ! empty( $args['text_color_hover'] ) ) {
			$key    = 'custom' === $args['text_color_hover'] && ! empty( $args['text_color_hover_custom'] ) ? 'text_color_hover_custom' : 'text_color_hover';
			$color  = sd_get_color_from_var( $args[ $key ] );
			$rules .= $styleid . ':hover {color: ' . $color . ' !important;} ';
		}

		// bg
		if ( ! empty( $args['bg_hover'] ) ) {
			if ( 'custom-gradient' === $args['bg_hover'] ) {
				$color  = $args['bg_hover_gradient'];
				$rules .= $styleid . ':hover {background-image: ' . $color . ' !important;} ';
				$rules .= $styleid . '.btn:hover {border-color: transparent !important;} ';
			} else {
				$key    = 'custom-color' === $args['bg_hover'] ? 'bg_hover_color' : 'bg_hover';
				$color  = sd_get_color_from_var( $args[ $key ] );
				$rules .= $styleid . ':hover {background: ' . $color . ' !important;} ';
				$rules .= $styleid . '.btn:hover {border-color: ' . $color . ' !important;} ';
			}
		}
	}

	return $rules ? '<style>' . $rules . '</style>' : '';
}

/**
 * Try to get a CSS color variable for a given value.
 *
 * @param $var
 *
 * @return mixed|string
 */
function sd_get_color_from_var( $var ) {

	//sanitize_hex_color() @todo this does not cover transparency
	if ( strpos( $var, '#' ) === false ) {
		$var = defined( 'BLOCKSTRAP_BLOCKS_VERSION' ) ? 'var(--wp--preset--color--' . esc_attr( $var ) . ')' : 'var(--' . esc_attr( $var ) . ')';
	}

	return $var;
}

/**
 * Sanitize single or multiple HTML classes.
 *
 * @param $classes
 * @param $sep
 *
 * @return string
 */
function sd_sanitize_html_classes( $classes, $sep = ' ' ) {
	$return = '';

	if ( ! is_array( $classes ) ) {
		$classes = explode( $sep, $classes );
	}

	if ( ! empty( $classes ) ) {
		foreach ( $classes as $class ) {
			$return .= sanitize_html_class( $class ) . ' ';
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
		'flex_align_items-MTD',
		'flex_justify_content-MTD',
		'flex_align_self-MTD',
		'flex_order-MTD',
		'styleid',
		'border_opacity',
		'border_width',
		'border_type',
		'opacity',
		'zindex',
		'flex_wrap-MTD',
		'h100',
		'overflow',
		'scrollbars',
		'float-MTD',
		'height-MTD',
		'width-MTD'
	);

	return apply_filters( 'sd_class_build_keys', $keys );
}

/**
 * This is a placeholder function for the visibility conditions input.
 *
 * @param $type
 * @param $overwrite
 *
 * @return array
 */
function sd_get_visibility_conditions_input( $type = 'visibility_conditions', $overwrite = array() ) {
	$defaults = array(
		'type'         => 'visibility_conditions',
		'title'        => __( 'Block Visibility', 'ayecode-connect' ),
		'button_title' => __( 'Set Block Visibility', 'ayecode-connect' ),
		'default'      => '',
		'desc_tip'     => true,
		'group'        => __( 'Visibility Conditions', 'ayecode-connect' ),
	);

	$input = wp_parse_args( $overwrite, $defaults );

	return $input;
}

/**
 * Get a array of user roles.
 *
 *
 *
 * @param array $exclude An array of roles to exclude from the return array.
 * @return array An array of roles.
 */
function sd_user_roles_options( $exclude = array() ) {
	$user_roles = array();

	if ( !function_exists('get_editable_roles') ) {
		require_once( ABSPATH . '/wp-admin/includes/user.php' );
	}

	$roles = get_editable_roles();

	foreach ( $roles as $role => $data ) {
		if ( ! ( ! empty( $exclude ) && in_array( $role, $exclude ) ) ) {
			$user_roles[ esc_attr( $role ) ] = translate_user_role( $data['name'] );
		}
	}

	return apply_filters( 'sd_user_roles_options', $user_roles );
}

/**
 * Get visibility conditions rule options.
 *
 *
 *
 * @return array Rule options.
 */
function sd_visibility_rules_options() {
	$options = array(
		'logged_in'  => __( 'Logged In', 'ayecode-connect' ),
		'logged_out' => __( 'Logged Out', 'ayecode-connect' ),
		'post_author'  => __( 'Post Author', 'ayecode-connect' ),
		'user_roles' => __( 'Specific User Roles', 'ayecode-connect' )
	);

	if ( class_exists( 'GeoDirectory' ) ) {
		$options['gd_field'] = __( 'GD Field', 'ayecode-connect' );
	}

	return apply_filters( 'sd_visibility_rules_options', $options );
}

/**
 * Get visibility GD field options.
 *
 * @return array
 */
function sd_visibility_gd_field_options() {
	$fields = geodir_post_custom_fields( '', 'all', 'all', 'none' );

	$keys = array();
	if ( ! empty( $fields ) ) {
		foreach( $fields as $field ) {
			if ( apply_filters( 'geodir_badge_field_skip_key', false, $field ) ) {
				continue;
			}

			$keys[ $field['htmlvar_name'] ] = $field['htmlvar_name'] . ' ( ' . __( $field['admin_title'], 'geodirectory' ) . ' )';

			// Extra address fields
			if ( $field['htmlvar_name'] == 'address' && ( $address_fields = geodir_post_meta_address_fields( '' ) ) ) {
				foreach ( $address_fields as $_field => $args ) {
					if ( $_field != 'map_directions' && $_field != 'street' ) {
						$keys[ $_field ] = $_field . ' ( ' . $args['frontend_title'] . ' )';
					}
				}
			}
		}
	}

	$standard_fields = sd_visibility_gd_standard_field_options();

	if ( ! empty( $standard_fields ) ) {
		foreach ( $standard_fields as $key => $option ) {
			$keys[ $key ] = $option;
		}
	}

	$options = apply_filters( 'geodir_badge_field_keys', $keys );

	return apply_filters( 'sd_visibility_gd_field_options', $options );
}

/**
 * Get visibility GD post standard field options.
 *
 * @return array
 */
function sd_visibility_gd_standard_field_options( $post_type = '' ) {
	$fields = sd_visibility_gd_standard_fields( $post_type );

	$options = array();

	foreach ( $fields as $key => $field ) {
		if ( ! empty( $field['frontend_title'] ) ) {
			$options[ $key ] = $key . ' ( ' . $field['frontend_title'] . ' )';
		}
	}

	return apply_filters( 'sd_visibility_gd_standard_field_options', $options, $fields );
}

/**
 * Get visibility GD post standard fields.
 *
 * @return array
 */
function sd_visibility_gd_standard_fields( $post_type = '' ) {
	$standard_fields = geodir_post_meta_standard_fields( $post_type );

	$fields = array();

	foreach ( $standard_fields as $key => $field ) {
		if ( $key != 'post_link' && strpos( $key, 'event' ) === false && ! empty( $field['frontend_title'] ) ) {
			$fields[ $key ] = $field;
		}
	}

	return apply_filters( 'sd_visibility_gd_standard_fields', $fields );
}

/**
 * Get visibility field conditions options.
 *
 * @return array
 */
function sd_visibility_field_condition_options(){
	$options = array(
		'is_empty' => __( 'is empty', 'ayecode-connect' ),
		'is_not_empty' => __( 'is not empty', 'ayecode-connect' ),
		'is_equal' => __( 'is equal', 'ayecode-connect' ),
		'is_not_equal' => __( 'is not equal', 'ayecode-connect' ),
		'is_greater_than' => __( 'is greater than', 'ayecode-connect' ),
		'is_less_than' => __( 'is less than', 'ayecode-connect' ),
		'is_contains' => __( 'is contains', 'ayecode-connect' ),
		'is_not_contains' => __( 'is not contains', 'ayecode-connect' ),
	);

	return apply_filters( 'sd_visibility_field_condition_options', $options );
}

/**
 * Get visibility conditions output options.
 *
 *
 *
 * @return array Template type options.
 */
function sd_visibility_output_options() {
	$options = array(
		'hide'          => __( 'Hide Block', 'ayecode-connect' ),
		'message'       => __( 'Show Custom Message', 'ayecode-connect' ),
		'page'          => __( 'Show Page Content', 'ayecode-connect' ),
		'template_part' => __( 'Show Template Part', 'ayecode-connect' ),
	);

	return apply_filters( 'sd_visibility_output_options', $options );
}

/**
 * Get the template page options.
 *
 *
 *
 * @param array $args Array of arguments.
 * @return array Template page options.
 */
function sd_template_page_options( $args = array() ) {
	global $wpdb, $sd_tmpl_page_options;

	$defaults = array(
		'nocache' => false,
		'with_slug' => false,
		'default_label' => __( 'Select Page...', 'ayecode-connect' )
	);

	$args = wp_parse_args( $args, $defaults );

	if ( ! empty( $sd_tmpl_page_options ) && empty( $args['nocache'] ) ) {
		return $sd_tmpl_page_options;
	}

	$exclude_pages = array();
	if ( $page_on_front = get_option( 'page_on_front' ) ) {
		$exclude_pages[] = $page_on_front;
	}

	if ( $page_for_posts = get_option( 'page_for_posts' ) ) {
		$exclude_pages[] = $page_for_posts;
	}

	$exclude_pages_placeholders = '';
	if ( ! empty( $exclude_pages ) ) {
		// Sanitize the array of excluded pages and implode it for the SQL query.
		$exclude_pages_placeholders = implode( ',', array_fill( 0, count( $exclude_pages ), '%d' ) );
	}

	// Prepare the base SQL query.
	$sql = "SELECT ID, post_title, post_name FROM " . $wpdb->posts . " WHERE post_type = 'page' AND post_status = 'publish'";

	// Add the exclusion if there are pages to exclude
	if ( ! empty( $exclude_pages ) ) {
		$sql .= " AND ID NOT IN ($exclude_pages_placeholders)";
	}

	// Add sorting.
	$sql .= " ORDER BY post_title ASC";

	// Add a limit.
	$limit = (int) apply_filters( 'sd_template_page_options_limit', 500, $args );

	if ( $limit > 0 ) {
		$sql .= " LIMIT " . (int) $limit;
	}

	// Prepare the SQL query to include the excluded pages only if we have placeholders.
	$pages = $exclude_pages_placeholders ? $wpdb->get_results( $wpdb->prepare( $sql, ...$exclude_pages ) ) : $wpdb->get_results( $sql );

	if ( ! empty( $args['default_label'] ) ) {
		$options = array( '' => $args['default_label'] );
	} else {
		$options = array();
	}

	if ( ! empty( $pages ) ) {
		foreach ( $pages as $page ) {
			$title = ! empty( $args['with_slug'] ) ? $page->post_title . ' (' . $page->post_name . ')' : ( $page->post_title . ' (#' . $page->ID . ')' );

			$options[ $page->ID ] = $title;
		}
	}

	$sd_tmpl_page_options = $options;

	return apply_filters( 'sd_template_page_options', $options, $args );
}

/**
 * Get the template part options.
 *
 *
 *
 * @param array $args Array of arguments.
 * @return array Template part options.
 */
function sd_template_part_options( $args = array() ) {
	global $sd_tmpl_part_options;

	if ( ! empty( $sd_tmpl_part_options ) ) {
		return $sd_tmpl_part_options;
	}

	$options = array( '' => __( 'Select Template Part...', 'ayecode-connect' ) );

	$parts = get_block_templates( array(), 'wp_template_part' );

	if ( ! empty( $parts ) ) {
		foreach ( $parts as $part ) {
			$options[ $part->slug ] = $part->title . ' (#' . $part->slug . ')';
		}
	}

	$sd_tmpl_part_options = $options;

	return apply_filters( 'sd_template_part_options', $options, $args );
}

/**
 * Get the template part by slug.
 *
 *
 *
 * @param string $slug Template slug.
 * @return array Template part object.
 */
function sd_get_template_part_by_slug( $slug ) {
	global $bs_tmpl_part_by_slug;

	if ( empty( $bs_tmpl_part_by_slug ) ) {
		$bs_tmpl_part_by_slug = array();
	}

	if ( isset( $bs_tmpl_part_by_slug[ $slug ] ) ) {
		return $bs_tmpl_part_by_slug[ $slug ];
	}

	$template_query = get_block_templates( array( 'slug__in' => array( $slug ) ), 'wp_template_part' );

	$query_post = ! empty( $template_query ) ? $template_query[0] : array();

	$template_part = ! empty( $query_post ) && $query_post->status == 'publish' ? $query_post : array();

	$bs_tmpl_part_by_slug[ $slug ] = $template_part;

	return apply_filters( 'sd_get_template_part_by_slug', $template_part, $slug );
}

/**
 * Filters the content of a single block.
 *
 *
 *
 * @param string   $block_content The block content.
 * @param array    $block         The full block, including name and attributes.
 * @param WP_Block $instance      The block instance.
 */
function sd_render_block( $block_content, $block, $instance = '' ) {
	// No block visibility conditions set.
	if ( empty( $block['attrs']['visibility_conditions'] ) ) {
		return $block_content;
	}

	$attributes = json_decode( $block['attrs']['visibility_conditions'], true );
	$rules = ! empty( $attributes ) ? sd_block_parse_rules( $attributes ) : array();

	// No rules set.
	if ( empty( $rules ) ) {
		return $block_content;
	}

	$_block_content = $block_content;

	if ( ! empty( $rules ) && sd_block_check_rules( $rules ) ) {
		if ( ! empty( $attributes['output']['type'] ) ) {
			switch ( $attributes['output']['type'] ) {
				case 'hide':
					$valid_type = true;
					$content = '';

					break;
				case 'message':
					$valid_type = true;

					if ( isset( $attributes['output']['message'] ) ) {
						$content = $attributes['output']['message'] != '' ? __( stripslashes( $attributes['output']['message'] ), 'ayecode-connect' ) : $attributes['output']['message'];

						if ( ! empty( $attributes['output']['message_type'] ) ) {
							$content = aui()->alert( array(
									'type'=> $attributes['output']['message_type'],
									'content'=> $content
								)
							);
						}
					}

					break;
				case 'page':
					$valid_type = true;

					$page_id = ! empty( $attributes['output']['page'] ) ? absint( $attributes['output']['page'] ) : 0;
					$content = sd_get_page_content( $page_id );

					break;
				case 'template_part':
					$valid_type = true;

					$template_part = ! empty( $attributes['output']['template_part'] ) ? $attributes['output']['template_part'] : '';
					$content = sd_get_template_part_content( $template_part );

					break;
				default:
					$valid_type = false;
					break;
			}

			if ( $valid_type ) {
				$block_content = '<div class="' . esc_attr( wp_get_block_default_classname( $instance->name ) ) . ' sd-block-has-rule">' . $content . '</div>';
			}
		}
	}

	return apply_filters( 'sd_render_block_visibility_content', $block_content, $_block_content, $attributes, $block, $instance );
}
add_filter( 'render_block', 'sd_render_block', 9, 3 );

function sd_get_page_content( $page_id ) {
	$content = $page_id > 0 ? get_post_field( 'post_content', (int) $page_id ) : '';

	// Maybe bypass content
	$bypass_content = apply_filters( 'sd_bypass_page_content', '', $content, $page_id );
	if ( $bypass_content ) {
		return $bypass_content;
	}

	// Run the shortcodes on the content.
	$content = do_shortcode( $content );

	// Run block content if its available.
	if ( function_exists( 'do_blocks' ) ) {
		$content = do_blocks( $content );
	}

	return apply_filters( 'sd_get_page_content', $content, $page_id );
}

function sd_get_template_part_content( $template_part ) {
	$template_part_post = $template_part ? sd_get_template_part_by_slug( $template_part ) : array();
	$content = ! empty( $template_part_post ) ? $template_part_post->content : '';

	// Maybe bypass content
	$bypass_content = apply_filters( 'sd_bypass_template_part_content', '', $content, $template_part );
	if ( $bypass_content ) {
		return $bypass_content;
	}

	// Run the shortcodes on the content.
	$content = do_shortcode( $content );

	// Run block content if its available.
	if ( function_exists( 'do_blocks' ) ) {
		$content = do_blocks( $content );
	}

	return apply_filters( 'sd_get_template_part_content', $content, $template_part );
}

function sd_block_parse_rules( $attrs ) {
	$rules = array();

	if ( ! empty( $attrs ) && is_array( $attrs ) ) {
		$attrs_keys = array_keys( $attrs );

		for ( $i = 1; $i <= count( $attrs_keys ); $i++ ) {
			if ( ! empty( $attrs[ 'rule' . $i ] ) && is_array( $attrs[ 'rule' . $i ] ) ) {
				$rules[] = $attrs[ 'rule' . $i ];
			}
		}
	}

	return apply_filters( 'sd_block_parse_rules', $rules, $attrs );
}

function sd_block_check_rules( $rules ) {
	if ( ! ( is_array( $rules ) && ! empty( $rules ) ) ) {
		return true;
	}

	foreach ( $rules as $key => $rule ) {
		$match = apply_filters( 'sd_block_check_rule', true, $rule );

		if ( ! $match ) {
			break;
		}
	}

	return apply_filters( 'sd_block_check_rules', $match, $rules );
}

function sd_block_check_rule( $match, $rule ) {
	global $post;

	if ( $match && ! empty( $rule['type'] ) ) {
		switch ( $rule['type'] ) {
			case 'logged_in':
				$match = (bool) is_user_logged_in();

				break;
			case 'logged_out':
				$match = ! is_user_logged_in();

				break;
			case 'post_author':
				if ( ! empty( $post ) && $post->post_type != 'page' && ! empty( $post->post_author ) && is_user_logged_in() ) {
					$match = (int) $post->post_author === (int) get_current_user_id() ? true : false;
				} else {
					$match = false;
				}

				break;
			case 'user_roles':
				$match = false;

				if ( ! empty( $rule['user_roles'] ) ) {
					$user_roles = is_scalar( $rule['user_roles'] ) ? explode( ",", $rule['user_roles'] ) : $rule['user_roles'];

					if ( is_array( $user_roles ) ) {
						$user_roles = array_filter( array_map( 'trim', $user_roles ) );
					}

					if ( ! empty( $user_roles ) && is_array( $user_roles ) && is_user_logged_in() && ( $current_user = wp_get_current_user() ) ) {
						$current_user_roles = $current_user->roles;

						foreach ( $user_roles as $role ) {
							if ( in_array( $role, $current_user_roles ) ) {
								$match = true;
							}
						}
					}
				}

				break;
			case 'gd_field':
				$match = sd_block_check_rule_gd_field( $rule );

				break;
		}
	}

	return $match;
}
add_filter( 'sd_block_check_rule', 'sd_block_check_rule', 10, 2 );

function sd_block_check_rule_gd_field( $rule ) {
	global $gd_post;

	$match_found = false;

	if ( class_exists( 'GeoDirectory' ) && ! empty( $gd_post->ID ) && ! empty( $rule['field'] ) && ! empty( $rule['condition'] ) ) {
		$args['block_visibility'] = true;
		$args['key'] = $rule['field'];
		$args['condition'] = $rule['condition'];
		$args['search'] = isset( $rule['search'] ) ? $rule['search'] : '';

		if ( $args['key'] == 'street' ) {
			$args['key'] = 'address';
		}

		$match_field = $_match_field = $args['key'];

		if ( $match_field == 'address' ) {
			$match_field = 'street';
		} elseif ( $match_field == 'post_images' ) {
			$match_field = 'featured_image';
		}

		$find_post = $gd_post;
		$find_post_keys = ! empty( $find_post ) ? array_keys( (array) $find_post ) : array();

		if ( ! empty( $find_post->ID ) && ! in_array( 'post_category', $find_post_keys ) ) {
			$find_post = geodir_get_post_info( (int) $find_post->ID );
			$find_post_keys = ! empty( $find_post ) ? array_keys( (array) $find_post ) : array();
		}

		if ( $match_field === '' || ( ! empty( $find_post_keys ) && ( in_array( $match_field, $find_post_keys ) || in_array( $_match_field, $find_post_keys ) ) ) ) {
			$address_fields = array( 'street2', 'neighbourhood', 'city', 'region', 'country', 'zip', 'latitude', 'longitude' ); // Address fields
			$field = array();
			$empty_field = false;

			$standard_fields = sd_visibility_gd_standard_fields();

			if ( $match_field && ! in_array( $match_field, array_keys( $standard_fields ) ) && ! in_array( $match_field, $address_fields ) ) {
				$package_id = geodir_get_post_package_id( $find_post->ID, $find_post->post_type );
				$fields = geodir_post_custom_fields( $package_id, 'all', $find_post->post_type, 'none' );

				foreach ( $fields as $field_info ) {
					if ( $match_field == $field_info['htmlvar_name'] ) {
						$field = $field_info;
						break;
					} elseif( $_match_field == $field_info['htmlvar_name'] ) {
						$field = $field_info;
						break;
					}
				}

				if ( empty( $field ) ) {
					$empty_field = true;
				}
			}

			// Address fields.
			if ( in_array( $match_field, $address_fields ) && ( $address_fields = geodir_post_meta_address_fields( '' ) ) ) {
				if ( ! empty( $address_fields[ $match_field ] ) ) {
					$field = $address_fields[ $match_field ];
				}
			} else if ( in_array( $match_field, array_keys( $standard_fields ) ) ) {
				if ( ! empty( $standard_fields[ $match_field ] ) ) {
					$field = $standard_fields[ $match_field ];
				}
			}

			// Parse search.
			$search = sd_gd_field_rule_search( $args['search'], $find_post->post_type, $rule, $field, $find_post );

			$is_date = ( ! empty( $field['type'] ) && $field['type'] == 'datepicker' ) || in_array( $match_field, array( 'post_date', 'post_modified' ) ) ? true : false;
			$is_date = apply_filters( 'geodir_post_badge_is_date', $is_date, $match_field, $field, $args, $find_post );

			$match_value = isset( $find_post->{$match_field} ) && empty( $empty_field ) ? esc_attr( trim( $find_post->{$match_field} ) ) : '';
			$match_found = $match_field === '' ? true : false;

			if ( ! $match_found ) {
				if ( ( $match_field == 'post_date' || $match_field == 'post_modified' ) && ( empty( $args['condition'] ) || $args['condition'] == 'is_greater_than' || $args['condition'] == 'is_less_than' ) ) {
					if ( strpos( $search, '+' ) === false && strpos( $search, '-' ) === false ) {
						$search = '+' . $search;
					}
					$the_time = $match_field == 'post_modified' ? get_the_modified_date( 'Y-m-d', $find_post ) : get_the_time( 'Y-m-d', $find_post );
					$until_time = strtotime( $the_time . ' ' . $search . ' days' );
					$now_time   = strtotime( date_i18n( 'Y-m-d', current_time( 'timestamp' ) ) );
					if ( ( empty( $args['condition'] ) || $args['condition'] == 'is_less_than' ) && $until_time > $now_time ) {
						$match_found = true;
					} elseif ( $args['condition'] == 'is_greater_than' && $until_time < $now_time ) {
						$match_found = true;
					}
				} else {
					switch ( $args['condition'] ) {
						case 'is_equal':
							$match_found = (bool) ( $search != '' && $match_value == $search );
							break;
						case 'is_not_equal':
							$match_found = (bool) ( $search != '' && $match_value != $search );
							break;
						case 'is_greater_than':
							$match_found = (bool) ( $search != '' && ( is_float( $search ) || is_numeric( $search ) ) && ( is_float( $match_value ) || is_numeric( $match_value ) ) && $match_value > $search );
							break;
						case 'is_less_than':
							$match_found = (bool) ( $search != '' && ( is_float( $search ) || is_numeric( $search ) ) && ( is_float( $match_value ) || is_numeric( $match_value ) ) && $match_value < $search );
							break;
						case 'is_empty':
							$match_found = (bool) ( $match_value === '' || $match_value === false || $match_value === '0' || is_null( $match_value ) );
							break;
						case 'is_not_empty':
							$match_found = (bool) ( $match_value !== '' && $match_value !== false && $match_value !== '0' && ! is_null( $match_value ) );
							break;
						case 'is_contains':
							$match_found = (bool) ( $search != '' && stripos( $match_value, $search ) !== false );
							break;
						case 'is_not_contains':
							$match_found = (bool) ( $search != '' && stripos( $match_value, $search ) === false );
							break;
					}
				}
			}

			$match_found = apply_filters( 'geodir_post_badge_check_match_found', $match_found, $args, $find_post );
		} else {
			$field = array();

			// Parse search.
			$search = sd_gd_field_rule_search( $args['search'], $find_post->post_type, $rule, $field, $find_post );

			$match_value = '';
			$match_found = $match_field === '' ? true : false;

			if ( ! $match_found ) {
				switch ( $args['condition'] ) {
					case 'is_equal':
						$match_found = (bool) ( $search != '' && $match_value == $search );
						break;
					case 'is_not_equal':
						$match_found = (bool) ( $search != '' && $match_value != $search );
						break;
					case 'is_greater_than':
						$match_found = false;
						break;
					case 'is_less_than':
						$match_found = false;
						break;
					case 'is_empty':
						$match_found = true;
						break;
					case 'is_not_empty':
						$match_found = false;
						break;
					case 'is_contains':
						$match_found = false;
						break;
					case 'is_not_contains':
						$match_found = false;
						break;
				}
			}

			$match_found = apply_filters( 'geodir_post_badge_check_match_found_empty', $match_found, $args, $find_post );
		}
	}

	return $match_found;
}

function sd_gd_field_rule_search( $search, $post_type, $rule, $field = array(), $gd_post = array() ) {
	global $post;

	if ( ! $search ) {
		return $search;
	}

	$orig_search = $search;
	$_search = strtolower( $search );

	if ( ! empty( $rule['field'] ) && $rule['field'] == 'post_author' ) {
		if ( $search == 'current_user' ) {
			$search = is_user_logged_in() ? (int) get_current_user_id() : - 1;
		} else if ( $search == 'current_author' ) {
			$search = ( ! empty( $post ) && $post->post_type != 'page' && isset( $post->post_author ) ) ? absint( $post->post_author ) : - 1;
		}
	} else if ( $_search == 'date_today' ) {
		$search = date( 'Y-m-d' );
	} else if ( $_search == 'date_tomorrow' ) {
		$search = date( 'Y-m-d', strtotime( "+1 day" ) );
	} else if ( $_search == 'date_yesterday' ) {
		$search = date( 'Y-m-d', strtotime( "-1 day" ) );
	} else if ( $_search == 'time_his' ) {
		$search = date( 'H:i:s' );
	} else if ( $_search == 'time_hi' ) {
		$search = date( 'H:i' );
	} else if ( $_search == 'datetime_now' ) {
		$search = date( 'Y-m-d H:i:s' );
	} else if ( strpos( $_search, 'datetime_after_' ) === 0 ) {
		$_searches = explode( 'datetime_after_', $_search, 2 );

		if ( ! empty( $_searches[1] ) ) {
			$search = date( 'Y-m-d H:i:s', strtotime( "+ " . str_replace( "_", " ", $_searches[1] ) ) );
		} else {
			$search = date( 'Y-m-d H:i:s' );
		}
	} else if ( strpos( $_search, 'datetime_before_' ) === 0 ) {
		$_searches = explode( 'datetime_before_', $_search, 2 );

		if ( ! empty( $_searches[1] ) ) {
			$search = date( 'Y-m-d H:i:s', strtotime( "- " . str_replace( "_", " ", $_searches[1] ) ) );
		} else {
			$search = date( 'Y-m-d H:i:s' );
		}
	} else if ( strpos( $_search, 'date_after_' ) === 0 ) {
		$_searches = explode( 'date_after_', $_search, 2 );

		if ( ! empty( $_searches[1] ) ) {
			$search = date( 'Y-m-d', strtotime( "+ " . str_replace( "_", " ", $_searches[1] ) ) );
		} else {
			$search = date( 'Y-m-d' );
		}
	} else if ( strpos( $_search, 'date_before_' ) === 0 ) {
		$_searches = explode( 'date_before_', $_search, 2 );

		if ( ! empty( $_searches[1] ) ) {
			$search = date( 'Y-m-d', strtotime( "- " . str_replace( "_", " ", $_searches[1] ) ) );
		} else {
			$search = date( 'Y-m-d' );
		}
	}

	return apply_filters( 'sd_gd_field_rule_search', $search, $post_type, $rule, $orig_search );
}


if(!function_exists('sd_blocks_render_blocks')){
	/**
	 * Add the shortcodes to the block content if set as an attribute.
	 *
	 * We have moved the shortcodes from the block content to a block argument to help prevent broken blocks.
	 *
	 * @param $block_content
	 * @param $parsed_block
	 * @param $thiss
	 * @return mixed|string
	 */
	function sd_blocks_render_blocks($block_content, $parsed_block, $thiss = array() ){

		// Check if ita a nested block that needs to be wrapped
		if(! empty($parsed_block['attrs']['sd_shortcode_close'])){
			$content = isset($parsed_block['attrs']['html']) ? $parsed_block['attrs']['html'] : $block_content;
			$block_content = $parsed_block['attrs']['sd_shortcode'].$content.$parsed_block['attrs']['sd_shortcode_close'];

			$block_content = do_shortcode($block_content);

		}elseif(! empty($parsed_block['attrs']['sd_shortcode'])){
			$has_warp = false;
			if($block_content && strpos(trim($block_content), '<div class="wp-block-') === 0 ){
				$parts = explode('></', $block_content);
				if(count($parts) === 2){
					$block_content = $parts[0].'>'.$parsed_block['attrs']['sd_shortcode'].'</'.$parts[1];
					$has_warp = true;
				}
			}
			if (!$has_warp) {
				// Add the shortcode if its not a wrapped block
				$block_content .= $parsed_block['attrs']['sd_shortcode'];
			}

			$block_content = do_shortcode($block_content);
		}
		return  $block_content;
	}
}

add_filter('render_block', 'sd_blocks_render_blocks',10,3);

