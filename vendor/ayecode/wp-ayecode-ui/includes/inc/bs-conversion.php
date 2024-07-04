<?php
/**
 * Functionality to convert BS4 to BS5.
 */

/**
 * Convert BS4 HTML to BS5 HTML from Super Duper output.
 *
 * @param $output
 * @param $instance
 * @param $args
 * @param $sd
 *
 * @return array|mixed|string|string[]
 */
function aui_bs_convert_sd_output( $output, $instance = '', $args = '', $sd = '' ) {
	global $aui_bs5;

	if ( $aui_bs5 ) {
		$convert = array(
			'"ml-' => '"ms-',
			'"mr-' => '"me-',
			'"pl-' => '"ps-',
			'"pr-' => '"pe-',
			"'ml-" => "'ms-",
			"'mr-" => "'me-",
			"'pl-" => "'ps-",
			"'pr-" => "'pe-",
			' ml-' => ' ms-',
			' mr-' => ' me-',
			' pl-' => ' ps-',
			' pr-' => ' pe-',
			'.ml-' => '.ms-',
			'.mr-' => '.me-',
			'.pl-' => '.ps-',
			'.pr-' => '.pe-',
			' form-row' => ' row',
			' embed-responsive-item' => '',
			' embed-responsive' => ' ratio',
			'-1by1'    => '-1x1',
			'-4by3'    => '-4x3',
			'-16by9'    => '-16x9',
			'-21by9'    => '-21x9',
			'geodir-lightbox-image' => 'aui-lightbox-image',
			'geodir-lightbox-iframe' => 'aui-lightbox-iframe',
			' badge-'   => ' text-bg-',
			'form-group'   => 'mb-3',
			'custom-select'   => 'form-select',
			'float-left'   => 'float-start',
			'float-right'   => 'float-end',
			'text-left'    => 'text-start',
			'text-sm-left'    => 'text-sm-start',
			'text-md-left'    => 'text-md-start',
			'text-lg-left'    => 'text-lg-start',
			'text-right'    => 'text-end',
			'text-sm-right'    => 'text-sm-end',
			'text-md-right'    => 'text-md-end',
			'text-lg-right'    => 'text-lg-end',
			'border-right'    => 'border-end',
			'border-left'    => 'border-start',
			'font-weight-'  => 'fw-',
			'btn-block'     => 'w-100',
			'rounded-left'  => 'rounded-start',
			'rounded-right'  => 'rounded-end',
			'font-italic' => 'fst-italic',

//			'custom-control custom-checkbox'    => 'form-check',
			// data
			' data-toggle=' => ' data-bs-toggle=',
			'data-ride=' => 'data-bs-ride=',
			'data-controlnav=' => 'data-bs-controlnav=',
			'data-slide='   => 'data-bs-slide=',
			'data-slide-to=' => 'data-bs-slide-to=',
			'data-target='  => 'data-bs-target=',
			'data-dismiss="modal"'  => 'data-bs-dismiss="modal"',
			'class="close"' => 'class="btn-close"',
			'<span aria-hidden="true">&times;</span>' => '',
		);
		$output  = str_replace(
			array_keys( $convert ),
			array_values( $convert ),
			$output
		);
	}

	return $output;
}

add_filter( 'wp_super_duper_widget_output', 'aui_bs_convert_sd_output', 10, 4 ); //$output, $instance, $args, $this
