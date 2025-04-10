<?php


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class Super_Duper_Bricks_Element extends \Bricks\Element {

	public $widget;

	public function __construct( $element = null ) {


		$block_icon = !empty($this->widget->options['block-icon']) ? $this->widget->options['block-icon'] : '';


		$this->category = !empty($this->widget->options['textdomain']) ? esc_attr( $this->widget->options['textdomain'] ) : 'Super Duper';
		$this->name     = $this->widget->id_base;
		$this->icon     = (strpos($block_icon, 'fa') === 0) ? esc_attr($this->widget->options['block-icon']) : 'fas fa-globe-americas';

		parent::__construct($element);
	}

	/**
	 * Set the element name.
	 *
	 * @return array|string|string[]|null
	 */
	public function get_label() {
		$escaped_text = esc_attr( $this->widget->name );
		return str_replace( ' &gt; ', ' > ', $escaped_text ); // keep our > but have it safe
	}

	/**
	 * Bricks function to set the controls
	 *
	 * @return void
	 */
	public function set_controls() {
		$args = $this->sd_convert_arguments($this->widget);

		if (!empty($args)) {
			$this->controls = $this->controls + $args;
		}

	}

	/**
	 * Set the bricks control groups from the GD ones.
	 *
	 * @return void
	 */
	public function set_control_groups() {
		$args = $this->sd_get_arguments();

		$groups = array();
		if(!empty($args)) {
			foreach ($args as $k => $v) {
				$g_slug = !empty($v['group']) ? sanitize_title( $v['group'] ) : '';
				if($g_slug && empty($groups[$g_slug])) {
					$groups[$g_slug] = array(
						'title' => esc_html( $v['group'] ),
						'tab' => 'content',
					);
				}
			}
		}

		if(!empty($groups)) {
			$this->control_groups = $this->control_groups + $groups;
		}

	}

	/**
	 * Get the setting input arguments.
	 *
	 * @return mixed
	 */
	public function sd_get_arguments() {
		$args = $this->widget->set_arguments();

		$widget_options = ! empty( $this->widget->options ) ? $this->widget->options : array();
		$widget_instance = ! empty( $this->widget->instance ) ? $this->widget->instance : array();

		$args = apply_filters( 'wp_super_duper_arguments', $args, $widget_options, $widget_instance );

		$arg_keys_subtract = $this->sd_remove_arguments();

		if ( ! empty( $arg_keys_subtract ) ) {
			foreach($arg_keys_subtract as $key ){
				unset($args[$key]);
			}
		}

		return $args;
	}


	/**
	 * Simply use our own render function for the output.
	 *
	 * @return void
	 */
	public function render() {
		$settings = $this->sd_maybe_convert_values( $this->settings );

		// Set the AyeCode UI calss on the wrapper
		$this->set_attribute( '_root', 'class', 'bsui' );

		// We might need to add a placeholder here for previews.

		do_action( 'super_duper_before_render_bricks_element', $settings, $this->widget, $this );

		// Add the bricks attributes to wrapper
		echo "<div {$this->render_attributes( '_root' )}>";
		echo $this->widget->output( $settings );
		echo '</div>';
	}

	/**
	 * Values can never be arrays so convert if bricks setting make it an array.
	 *
	 * @param $settings
	 * @return mixed
	 */
	public function sd_maybe_convert_values( $settings ) {


		if (!empty($settings)) {
			foreach( $settings as $k => $v ) {
				if(is_array($v)) {
					$value = '';
					// is color
					if (isset($v['hex'])) {
						$value = $v['hex'];
					} elseif (isset($v['icon'])) {
						$value = $v['icon'];
					}


					// set the value
					$settings[$k] = $value;
				}

			}
		}

		return $settings;
	}

	/**
	 * Convert SD arguments to Bricks arguments.
	 *
	 * @param $widget
	 *
	 * @return array
	 */
	public function sd_convert_arguments() {
		$bricks_args = array();

		$args = $this->sd_get_arguments();

		if ( ! empty( $args ) ) {
			foreach ( $args as $key => $arg ) {
				// convert title
				if ( ! empty( $arg['title'] ) ) {
					$arg['label'] = $arg['title'];
					unset( $arg['title'] );
				}

				// set fields not to use dynamic data
				$arg['hasDynamicData'] = false;

				if ( ! empty( $arg['group'] ) ) {
					$arg['group'] =  sanitize_title( $arg['group'] );
				}

				$arg['rerender'] = true;

				// required
				if( ! empty( $arg['element_require'] ) ) {
					$arg['required'] = $this->sd_convert_required( $arg['element_require'] );
					unset( $arg['element_require'] );
				}

				// icons
				if ( 'icon' === $key ) {
					$arg['type'] = 'icon';
				}

				// Bricks don't render dropdown when first option key is 0.
				if ( in_array( $key, array( 'zoom', 'mapzoom' ) ) && ! empty( $arg['options'] ) && is_array( $arg['options'] ) && ( $option_keys = array_keys( $arg['options'] ) ) ) {
					// Move first element to last.
					if ( $option_keys[0] === 0 || $option_keys[0] === '0' ) {
						$options = $arg['options'];
						unset( $arg['options'][0] );
						$arg['options'][0] = $options[0];
					}
				}

				$bricks_args[$key] = $arg;
			}
		}

		return $bricks_args;
	}

	/**
	 * Convert the SD element_required to the Bricks required syntax.
	 *
	 * @param $element_require
	 * @return array
	 */
	public function sd_convert_required($element_require) {
		$bricks_required = [];

		// Handle logical OR (||) for multiple values
		if (strpos($element_require, '||') !== false) {
			preg_match('/\[%(.+?)%\] *== *"(.*?)"/', $element_require, $matches);
			if ($matches) {
				$control_id = $matches[1];
				preg_match_all('/\[%.*?%\] *== *"(.*?)"/', $element_require, $value_matches);
				$values = $value_matches[1];
				$bricks_required[] = [$control_id, '=', $values];
			}
			return $bricks_required;
		}

		// Match individual conditions
		preg_match_all('/(!)?\[%(.*?)%\](?:\s*([!=<>]=?)\s*(".*?"|\'.*?\'|\d+))?/', $element_require, $matches, PREG_SET_ORDER);

		foreach ($matches as $match) {
			$is_negation = isset($match[1]) && $match[1] === '!';
			$control_id = $match[2];
			$operator = isset($match[3]) ? str_replace('==', '=', $match[3]) : ($is_negation ? '=' : '!=');
			$value = isset($match[4]) ? trim($match[4], '"\'') : ($is_negation ? '' : '');

			// Adjust for negation without explicit operator
			if ($is_negation && !isset($match[3])) {
				$operator = '=';
				$value = '';
			}

			$bricks_required[] = [$control_id, $operator, $value];
		}

		return $bricks_required;
	}


	/**
	 * A way to remove some settings by keys.
	 *
	 * @return array
	 */
	public function sd_remove_arguments()
	{
		return array();
	}

}


/**
 * This implements the desktop, tablet and mobile breakpoints views with our fields that are hidden on these types and adda the icon after the label to show which it applies to.
 */
add_action( 'wp_enqueue_scripts', function() {

	// Check if we're in the Bricks Editor
	if ( isset( $_GET['bricks'] ) && $_GET['bricks'] && bricks_is_builder_main() ) {
		// Add inline script to the 'bricks-builder' script
		wp_add_inline_script(
			'bricks-builder',
			"

(function () {
    // Function to get the current breakpoint from the #bricks-preview class
    function getCurrentBreakpoint() {
        const bricksPreview = document.getElementById('bricks-preview');
        if (!bricksPreview) return null;

        // Check which breakpoint class is active
        if (bricksPreview.classList.contains('desktop')) {
            return 'desktop';
        } else if (bricksPreview.classList.contains('tablet_portrait')) {
            return 'tablet';
        } else if (bricksPreview.classList.contains('mobile_landscape') || bricksPreview.classList.contains('mobile_portrait')) {
            return 'phone';
        }
        return null;
    }

    // Function to group fields by base key
    function groupFields() {
        const controls = document.querySelectorAll('[data-controlkey]');
        const grouped = {};

        controls.forEach((control) => {
            const controlKey = control.getAttribute('data-controlkey');
            const baseKey = controlKey.replace(/(_sm|_md|_lg)$/, ''); // Remove _sm, _md, or _lg suffix

            if (!grouped[baseKey]) {
                grouped[baseKey] = { base: null, md: null, lg: null };
            }

            if (controlKey.endsWith('_lg')) {
                grouped[baseKey].lg = control;
            } else if (controlKey.endsWith('_md')) {
                grouped[baseKey].md = control;
            } else {
                grouped[baseKey].base = control; // No suffix is treated as base (sm)
            }
        });

        return grouped;
    }

    // Function to update visibility of controls
    function updateControlVisibility() {
        const breakpoint = getCurrentBreakpoint();
        const groupedFields = groupFields();

        Object.keys(groupedFields).forEach((baseKey) => {
            const { base, md, lg } = groupedFields[baseKey];

            // Skip fields that have no `_md` or `_lg` counterparts
            if (!md && !lg) {
                if (base) base.style.display = ''; // Ensure base field is always visible
                return;
            }

            // Apply hide/show logic based on the breakpoint
            if (breakpoint === 'desktop') {
                if (base) base.style.display = 'none';
                if (md) md.style.display = 'none';
                if (lg) lg.style.display = ''; // Show _lg
            } else if (breakpoint === 'tablet') {
                if (base) base.style.display = 'none';
                if (md) md.style.display = ''; // Show _md
                if (lg) lg.style.display = 'none';
            } else if (breakpoint === 'phone') {
                if (base) base.style.display = ''; // Show base (sm)
                if (md) md.style.display = 'none';
                if (lg) lg.style.display = 'none';
            }
        });
    }

    // Observe changes in the #bricks-panel-element content
    const panelElementObserver = new MutationObserver(() => {
        updateControlVisibility();
    });

    const bricksPanelElement = document.getElementById('bricks-panel-element');
    if (bricksPanelElement) {
        panelElementObserver.observe(bricksPanelElement, { childList: true, subtree: true });
    }

    // Also observe changes to the #bricks-preview element for breakpoint changes
    const bricksPreviewObserver = new MutationObserver(() => {
        updateControlVisibility();
    });

    const bricksPreview = document.getElementById('bricks-preview');
    if (bricksPreview) {
        bricksPreviewObserver.observe(bricksPreview, { attributes: true, attributeFilter: ['class'] });
    }

    // Run on initial load
    updateControlVisibility();
})();


(function () {
    // Function to get the current breakpoint from the #bricks-preview class
    function getCurrentBreakpoint() {
        const bricksPreview = document.getElementById('bricks-preview');
        if (!bricksPreview) return null;

        if (bricksPreview.classList.contains('desktop')) {
            return 'desktop';
        } else if (bricksPreview.classList.contains('tablet_portrait')) {
            return 'tablet';
        } else if (bricksPreview.classList.contains('mobile_landscape') || bricksPreview.classList.contains('mobile_portrait')) {
            return 'phone';
        }
        return null;
    }

    // SVG icons
   const icons = {
    lg: `
        <span class=\"bricks-svg-wrapper\" data-name=\"laptop\" style=\"padding-top:3px;\" title=\"Desktop\">
            <svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 14 14\" class=\"bricks-svg\">
                <g id=\"laptop--device-laptop-electronics-computer-notebook\">
                    <path id=\"Vector\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M3.08 1.61H10.9136C11.4549 1.61 11.8936 2.0488 11.8936 2.59V7.98H2.1V2.59C2.1 2.002 2.492 1.61 3.08 1.61Z\" stroke-width=\"1\"></path>
                    <path id=\"Vector 3945\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M0.6957 11.2566L2.1 7.98H11.9L13.3042 11.2566C13.3477 11.3578 13.37 11.4667 13.37 11.5769C13.37 12.0259 13.0059 12.39 12.5569 12.39H1.4431C0.994 12.39 0.63 12.0259 0.63 11.5769C0.63 11.4667 0.6524 11.3578 0.6957 11.2566Z\" stroke-width=\"1\"></path>
                </g>
            </svg>
        </span>
    `,
    md: `
        <span class=\"bricks-svg-wrapper\" data-name=\"tablet-portrait\" style=\"padding-top:3px;\" title=\"Tablet\"><svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 14 14\" class=\"bricks-svg\"><g id=\"one-handed-holding-tablet-handheld\"><path id=\"Rectangle 2038\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M9.64593 1.23658C9.47168 1.089 9.24623 1 9 1H2c-0.55228 0 -1 0.44771 -1 1v9.0938c0 0.5522 0.44772 1 1 1h3.75\" stroke-width=\"1\"></path><path id=\"vector 296\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"m12.3106 13 0.6383 -3.15223c0.0742 -0.36672 0.0675 -0.7452 -0.0197 -1.10906l-0.9088 -3.79119c-0.1682 -0.70134 -0.7013 -1.25797 -1.3954 -1.45681l-0.6221 -0.17821 -0.0002 5.23879c0 0.35407 -0.35839 0.59595 -0.68734 0.46392l-1.6994 -0.68209c-0.3105 -0.12463 -0.66467 -0.06608 -0.91839 0.15183 -0.3824 0.32842 -0.41818 0.90721 -0.07914 1.28012l1.24302 1.36723L8.89958 13\" stroke-width=\"1\"></path></g></svg></span>
    `,
    sm: `
        <span class=\"bricks-svg-wrapper\" data-name=\"phone-portrait\" style=\"padding-top:3px;\" title=\"Mobile\">
            <svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 14 14\" class=\"bricks-svg\">
                <g id=\"phone-mobile-phone--android-phone-mobile-device-smartphone-iphone\">
                    <path id=\"Vector\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M10.5 0.5h-7c-0.55228 0 -1 0.447715 -1 1v11c0 0.5523 0.44772 1 1 1h7c0.5523 0 1 -0.4477 1 -1v-11c0 -0.552285 -0.4477 -1 -1 -1Z\" stroke-width=\"1\"></path>
                    <path id=\"Vector_2\" stroke=\"currentColor\" stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M6.5 11h1\" stroke-width=\"1\"></path>
                </g>
            </svg>
        </span>
    `,
};


    // Function to group fields by base key
    function groupFields() {
        const controls = document.querySelectorAll('[data-controlkey]');
        const grouped = {};

        controls.forEach((control) => {
            const controlKey = control.getAttribute('data-controlkey');
            const baseKey = controlKey.replace(/(_sm|_md|_lg)$/, ''); // Remove _sm, _md, or _lg suffix

            if (!grouped[baseKey]) {
                grouped[baseKey] = { base: null, md: null, lg: null };
            }

            if (controlKey.endsWith('_lg')) {
                grouped[baseKey].lg = control;
            } else if (controlKey.endsWith('_md')) {
                grouped[baseKey].md = control;
            } else {
                grouped[baseKey].base = control; // No suffix is treated as base (sm)
            }
        });

        return grouped;
    }

    // Function to add icons to labels
    function addIconsToLabels() {
        const groupedFields = groupFields();

        Object.keys(groupedFields).forEach((baseKey) => {
            const { base, md, lg } = groupedFields[baseKey];

            // Skip fields that do not have `_md` or `_lg` counterparts
            if (!md && !lg) return;

            if (base) appendIcon(base, 'sm');
            if (md) appendIcon(md, 'md');
            if (lg) appendIcon(lg, 'lg');
        });
    }

    // Append an icon to the label of a control
    function appendIcon(control, type) {
        const label = control.querySelector('label');
        if (label && !label.querySelector('.bricks-svg-wrapper')) {
            label.insertAdjacentHTML('beforeend', icons[type]);
        }
    }

    // Observe changes in the #bricks-panel-element content
    const panelElementObserver = new MutationObserver(() => {
        addIconsToLabels(); // Ensure icons are added when the panel updates
    });

    const bricksPanelElement = document.getElementById('bricks-panel-element');
    if (bricksPanelElement) {
        panelElementObserver.observe(bricksPanelElement, { childList: true, subtree: true });
    }

    // Initial run to add icons
    addIconsToLabels();
})();
"
		);
	}
});
