<?php
/**
 * Contains the shortcode class.
 *
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 *
 * The widget super duper class.
 *
 *
 * @since 2.0.0
 * @version 2.0.0
 */
class WP_Super_Duper_Widget extends WP_Widget {

	/**
	 * @var WP_Super_Duper
	 */
	protected $sd;

	/**
	 * Class constructor.
	 *
	 * @param WP_Super_Duper $super_duper
	 */
	public function __construct( $super_duper ) {
		$this->sd = $super_duper;

		// Register widget.
		$widget_ops = $super_duper->options['widget_ops'];

		// Only overwrite if not set already.
		if ( ! isset( $widget_ops['show_instance_in_rest'] ) ) {
			$widget_ops['show_instance_in_rest'] = true;
		}

		parent::__construct( $super_duper->options['base_id'], $super_duper->options['name'], $widget_ops );

		if ( did_action( 'widgets_init' ) || doing_action( 'widgets_init' ) ) {
			$this->register_widget();
		} else {
			add_action( 'widgets_init', array( $this, 'register_widget' ) );
		}

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( __CLASS__, 'elementor_editor_styles' ) );
		add_filter( 'widget_types_to_hide_from_legacy_widget_block', array( $this, 'hide_widget' ) );

	}

	/**
	 * Registers the widget.
	 */
	public function register_widget() {
		register_widget( $this );
	}

	/**
	 * Enqeues scripts.
	 *
	 * @param WP_Super_Duper $super_duper
	 */
	public static function enqueue_scripts() {
		wp_add_inline_script( 'admin-widgets', WP_Super_Duper::widget_js() );
		wp_add_inline_script( 'customize-controls', WP_Super_Duper::widget_js() );
		wp_add_inline_style( 'widgets', WP_Super_Duper::widget_css() );
	}

	/**
	 * Add our widget CSS to elementor editor.
	 */
	public static function elementor_editor_styles() {
		wp_add_inline_style( 'elementor-editor', WP_Super_Duper::widget_css( false ) );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		// Prepare output args.
		$argument_values = $this->sd->argument_values( $instance );
		$argument_values = $this->sd->string_to_bool( $argument_values );
		$output          = $this->sd->output( $argument_values, $args );
		$no_wrap         = ! empty( $argument_values['no_wrap'] );

		ob_start();
		if ( $output && ! $no_wrap ) {

			$class_original = $this->sd->options['widget_ops']['classname'];
			$class          = $this->sd->options['widget_ops']['classname'] . ' sdel-' . $this->sd->get_instance_hash();

			// Before widget
			$before_widget = $args['before_widget'];
			$before_widget = str_replace( $class_original, $class, $before_widget );
			$before_widget = apply_filters( 'wp_super_duper_before_widget', $before_widget, $args, $instance, $this );
			$before_widget = apply_filters( 'wp_super_duper_before_widget_' . $this->sd->base_id, $before_widget, $args, $instance, $this );

			// After widget
			$after_widget = $args['after_widget'];
			$after_widget = apply_filters( 'wp_super_duper_after_widget', $after_widget, $args, $instance, $this );
			$after_widget = apply_filters( 'wp_super_duper_after_widget_' . $this->sd->base_id, $after_widget, $args, $instance, $this );

			echo $before_widget;

			// elementor strips the widget wrapping div so we check for and add it back if needed
			if ( $this->is_elementor_widget_output() ) {
				// Filter class & attrs for elementor widget output.
				$class = apply_filters( 'wp_super_duper_div_classname', $class, $args, $this->sd, $this );
				$class = apply_filters( 'wp_super_duper_div_classname_' . $this->sd->base_id, $class, $args, $this->sd, $this );

				$attrs = apply_filters( 'wp_super_duper_div_attrs', '', $args, $this->sd, $this );
				$attrs = apply_filters( 'wp_super_duper_div_attrs_' . $this->sd->base_id, $attrs, $args, $this->sd, $this );

				echo "<span class='" . esc_attr( $class ) . "' " . $attrs . ">";
			}

			echo $this->sd->output_title( $args, $instance );
			echo $output;
			if ( $this->is_elementor_widget_output() ) {
				echo "</span>";
			}

			echo $after_widget;
		} elseif ( $this->sd->is_preview() && $output == '' ) {// if preview show a placeholder if empty
			$output = $this->sd->preview_placeholder_text( "{{" . $this->base_id . "}}" );
			echo $output;
		} elseif ( $output && $no_wrap ) {
			echo $output;
		}
		$output = ob_get_clean();

		$output = apply_filters( 'wp_super_duper_widget_output', $output, $instance, $args, $this );

		echo $output;
	}

	/**
	 * Tests if the current output is inside a elementor container.
	 *
	 * @since 1.0.4
	 * @return bool
	 */
	public function is_elementor_widget_output() {
		return defined( 'ELEMENTOR_VERSION' ) && isset( $this->number ) && $this->number == 'REPLACE_TO_ID';
	}

	/**
	 * Outputs the options form inputs for the widget.
	 *
	 * @param array $instance The widget options.
	 */
	public function form( $instance ) {
		$this->sd->form( $instance );
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 *
	 * @return array
	 * @todo we should add some sanitation here.
	 */
	public function update( $new_instance, $old_instance ) {

		// Save the widget.
		$instance = array_merge( (array) $old_instance, (array) $new_instance );

		// set widget instance
		$this->sd->instance = $instance;

		if ( empty( $this->arguments ) ) {
			$this->sd->get_arguments();
		}

		// check for checkboxes
		if ( ! empty( $this->sd->arguments ) ) {
			foreach ( $this->sd->arguments as $argument ) {
				if ( isset( $argument['type'] ) && $argument['type'] == 'checkbox' && ! isset( $new_instance[ $argument['name'] ] ) ) {
					$instance[ $argument['name'] ] = '0';
				}
			}
		}

		return $instance;
	}

	/**
	 * Hides this widget from the block widgets inserter function.
	 *
	 * @param array $widget_types List of hidden widgets.
	 *
	 * @return array
	 */
	public function hide_widget( $widget_types ) {
		$widget_types[] = $this->id_base;

		return $widget_types;
	}

}
