<?php

class SD_Map extends WP_Super_Duper {


	public $arguments;

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {

		$options = array(
			'textdomain'     => 'super-duper',
			// textdomain of the plugin/theme (used to prefix the Gutenberg block)
			'block-icon'     => 'admin-site',
			// Dash icon name for the block: https://developer.wordpress.org/resource/dashicons/#arrow-right
			'block-category' => 'widgets',
			// the category for the block, 'common', 'formatting', 'layout', 'widgets', 'embed'.
			'block-keywords' => "['map','super','google']",
			// used in the block search, MAX 3
			'block-output'   => array( // the block visual output elements as an array
				array(
					'element' => 'p',
					'content' => __('A Google API key is required to use this block, we recommend installing our plugin which makes it easy and sets it globally, or you can set a key in the block settings sidebar: ','super-duper'),
					//'element_require' => '"1"=='.get_option( 'rgmk_google_map_api_key', '"0"') ? '"0"' : '"1"',
					'element_require' => get_option( 'rgmk_google_map_api_key', false) ? '1==0' : '1==1 && [%api_key%]==""',
				),
				array(
					'element' => 'a',
					'content' => __('API KEY for Google Maps','super-duper'),
					'element_require' => get_option( 'rgmk_google_map_api_key', false) ? '1==0' : '1==1 && [%api_key%]==""',
					'href' => 'https://wordpress.org/plugins/api-key-for-google-maps/',
				),
				array(
					'element' => 'img',
					'class'   => '[%className%]',
					//'content' => 'Hello: [%after_text%]' // block properties can be added by wrapping them in [%name%]
					'element_require' => '[%type%]=="image"',
					'src'     => get_option( 'rgmk_google_map_api_key', false) ? "https://maps.googleapis.com/maps/api/staticmap?center=[%location%]&maptype=[%maptype%]&zoom=[%zoom%]&size=[%static_width%]x[%static_height%]&key=".get_option( 'rgmk_google_map_api_key') : "https://maps.googleapis.com/maps/api/staticmap?center=[%location%]&maptype=[%maptype%]&zoom=[%zoom%]&size=[%static_width%]x[%static_height%]&key=[%api_key%]"
				),
				array(
					'element' => 'div',
					'class'   => 'sd-map-iframe-cover',
					'style'   => '{overflow:"hidden", position:"relative"}',
					array(
						'element' => 'iframe',
						'title'   => __( 'Placeholderx', 'super-duper' ),
						'class'   => '[%className%]',
						'width'   => '[%width%]',
						'height'  => '[%height%]',
						'frameborder' => '0',
						'allowfullscreen' => 'true',
						'style' => '{border:0}',
						'element_require' => '[%type%]!="image"',
						'src'     => get_option( 'rgmk_google_map_api_key', false) ? "https://www.google.com/maps/embed/v1/[%type%]?q=[%location%]&maptype=[%maptype%]&zoom=[%zoom%]&key=".get_option( 'rgmk_google_map_api_key') : "https://www.google.com/maps/embed/v1/[%type%]?q=[%location%]&maptype=[%maptype%]&zoom=[%zoom%]&key=[%api_key%]"
					),
				),
				array(
					'element' => 'style',
					'content' => '.sd-map-iframe-cover:hover:before {background: #4a4a4a88; content: "'.__("Click here, Settings are in the block settings sidebar","super-duper").'";} .sd-map-iframe-cover:before{cursor: pointer; content: ""; width: 100%; height: 100%; position: absolute; top: 0; bottom: 0;padding-top: 33%; text-align: center;  color: #fff; font-size: 20px; font-weight: bold;}',
					'element_require' => '[%type%]!="image"',
				),
			),
			'class_name'     => __CLASS__,
			// The calling class name
			'base_id'        => 'sd_map',
			// this is used as the widget id and the shortcode id.
			'name'           => __( 'Map', 'super-duper' ),
			// the name of the widget/block
			'widget_ops'     => array(
				'classname'   => 'sd-map-class',
				// widget class
				'description' => esc_html__( 'This is an example that will take a text parameter and output it after `Hello:`.', 'hello-world' ),
				// widget description
			),
			'arguments'      => array( // these are the arguments that will be used in the widget, shortcode and block settings.
				'type'  => array(
					'title' => __('Map Type:', 'geodirectory'),
					'desc' => __('Select the map type to use.', 'geodirectory'),
					'type' => 'select',
					'options'   =>  array(
						"image" => __('Static Image', 'geodirectory'),
						"place" => __('Place', 'geodirectory'),
//						"directions" => __('Directions', 'geodirectory'),
//						"search" => __('Search', 'geodirectory'),
//						"view" => __('View', 'geodirectory'),
//						"streetview" => __('Streetview', 'geodirectory'),
					),
					'default'  => 'image',
					'desc_tip' => true,
					'advanced' => false
				),
				'location'            => array(
					'type'        => 'text',
					'title'       => __( 'Location:', 'geodirectory' ),
					'desc'        => __( 'Enter the location to show on the map, place, city, zip code or GPS.', 'geodirectory' ),
					'placeholder' => 'Place, city, zip code or GPS',
					'desc_tip'    => true,
					'default'     => 'Ireland',
					'advanced'    => false
				),
				'static_width'            => array(
					'type'        => 'number',
					'title'       => __( 'Width:', 'geodirectory' ),
					'desc'        => __( 'This is the width of the map, for static maps you can only use px values.', 'geodirectory' ),
					'placeholder' => '600',
					'desc_tip'    => true,
					'default'     => '600',
					'custom_attributes' => array(
						'max'         => '2000',
						'min'         => '100',
					),
					'element_require' => '[%type%]=="image"',
					'advanced'    => false
				),
				'static_height'           => array(
					'type'        => 'number',
					'title'       => __( 'Height:', 'geodirectory' ),
					'desc'        => __( 'This is the height of the map, for static maps you can only use px values.', 'geodirectory' ),
					'placeholder' => '400',
					'desc_tip'    => true,
					'default'     => '400',
					'custom_attributes' => array(
						'max'         => '2000',
						'min'         => '100',
						'required'         => 'required',
					),
					'element_require' => '[%type%]=="image"',
					'advanced'    => false
				),
				'width'            => array(
					'type'        => 'text',
					'title'       => __( 'Width:', 'geodirectory' ),
					'desc'        => __( 'This is the width of the map, you can use % or px here.', 'geodirectory' ),
					'placeholder' => '100%',
					'desc_tip'    => true,
					'default'     => '100%',
					'element_require' => '[%type%]!="image"',
					'advanced'    => false
				),
				'height'           => array(
					'type'        => 'text',
					'title'       => __( 'Height:', 'geodirectory' ),
					'desc'        => __( 'This is the height of the map, you can use %, px or vh here.', 'geodirectory' ),
					'placeholder' => '425px',
					'desc_tip'    => true,
					'default'     => '425px',
					'element_require' => '[%type%]!="image"',
					'advanced'    => false
				),
				'maptype'          => array(
					'type'     => 'select',
					'title'    => __( 'Mapview:', 'geodirectory' ),
					'desc'     => __( 'This is the type of map view that will be used by default.', 'geodirectory' ),
					'options'  => array(
						"roadmap"   => __( 'Road Map', 'geodirectory' ),
						"satellite" => __( 'Satellite Map', 'geodirectory' ),
//						"hybrid"    => __( 'Hybrid Map', 'geodirectory' ),
//						"terrain"   => __( 'Terrain Map', 'geodirectory' ),
					),
					'desc_tip' => true,
					'default'  => 'roadmap',
					'advanced' => true
				),
				'zoom'             => array(
					'type'        => 'select',
					'title'       => __( 'Zoom level:', 'geodirectory' ),
					'desc'        => __( 'This is the zoom level of the map, `auto` is recommended.', 'geodirectory' ),
					'options'     => range( 1, 19 ),
					'placeholder' => '',
					'desc_tip'    => true,
					'default'     => '7',
					'advanced'    => true
				),
				'api_key'           => array(
					'type'        => 'text',
					'title'       => __( 'Api Key:', 'geodirectory' ),
					'desc'        => __( 'This is the height of the map, you can use %, px or vh here.', 'geodirectory' ),
					'placeholder' => '',
					'desc_tip'    => true,
					'default'     => '',
					'element_require' => get_option( 'rgmk_google_map_api_key', false) ? '1==0' : '1==1',
					'advanced'    => false
				),
			)
		);

		parent::__construct( $options );
	}


	/**
	 * This is the output function for the widget, shortcode and block (front end).
	 *
	 * @param array $args The arguments values.
	 * @param array $widget_args The widget arguments when used.
	 * @param string $content The shortcode content argument
	 *
	 * @return string
	 */
	public function output( $args = array(), $widget_args = array(), $content = '' ) {

		// options
		$defaults = array(
			'type'      => 'image', // image, place
			'location' => 'Ireland',
			'static_width' => '600',
			'static_height' => '400',
			'width'=> '100%',
			'height'=> '425px',
			'maptype'     => 'roadmap',
			'zoom'     => '7',
			'api_key'     => 'AIzaSyBK3ZcmK0ljxl5agNyJNQh_G24Thq1btuE',
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args = wp_parse_args($args, $defaults );

		$output = '';


		// check if we have a global API key
		$args['api_key'] = get_option( 'rgmk_google_map_api_key', false ) ? get_option( 'rgmk_google_map_api_key' ) : $args['api_key'];

		if($args['type']=='image'){
			$output .= "<img src='https://maps.googleapis.com/maps/api/staticmap?center=".esc_attr($args['location'])."&maptype=".esc_attr($args['maptype'])."&zoom=".esc_attr($args['zoom'])."&size=".esc_attr($args['static_width'])."x".esc_attr($args['static_height'])."&key=".esc_attr($args['api_key'])."' />";
		}else{
			$output .= "<iframe width='".esc_attr($args['width'])."' height='".esc_attr($args['height'])."' frameborder='0' allowfullscreen style='border:0;' src='https://www.google.com/maps/embed/v1/".esc_attr($args['type'])."?q=".esc_attr($args['location'])."&maptype=".esc_attr($args['maptype'])."&zoom=".esc_attr($args['zoom'])."&key=".esc_attr($args['api_key'])."' ></iframe> ";
		}

		return $output;

	}

}

// register it.
add_action( 'widgets_init', function () {
	register_widget( 'SD_Map' );
} );
