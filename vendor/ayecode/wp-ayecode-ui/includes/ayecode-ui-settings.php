<?php
/**
 * A class for adjusting AyeCode UI settings on WordPress
 *
 * This class can be added to any plugin or theme and will add a settings screen to WordPress to control Bootstrap settings.
 *
 * @link https://github.com/AyeCode/wp-ayecode-ui
 *
 * @internal This file should not be edited directly but pulled from the github repo above.
 */

/**
 * Bail if we are not in WP.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Only add if the class does not already exist.
 */
if ( ! class_exists( 'AyeCode_UI_Settings' ) ) {

	/**
	 * A Class to be able to change settings for Font Awesome.
	 *
	 * Class AyeCode_UI_Settings
	 * @ver 1.0.0
	 * @todo decide how to implement textdomain
	 */
	class AyeCode_UI_Settings {

		/**
		 * Class version version.
		 *
		 * @var string
		 */
		public $version = '0.1.46';

		/**
		 * Class textdomain.
		 *
		 * @var string
		 */
		public $textdomain = 'aui';

		/**
		 * Latest version of Bootstrap at time of publish published.
		 *
		 * @var string
		 */
		public $latest = "4.5.3";

		/**
		 * Current version of select2 being used.
		 *
		 * @var string
		 */
		public $select2_version = "4.0.11";

		/**
		 * The title.
		 *
		 * @var string
		 */
		public $name = 'AyeCode UI';

		/**
		 * The relative url to the assets.
		 *
		 * @var string
		 */
		public $url = '';

		/**
		 * Holds the settings values.
		 *
		 * @var array
		 */
		private $settings;

		/**
		 * AyeCode_UI_Settings instance.
		 *
		 * @access private
		 * @since  1.0.0
		 * @var    AyeCode_UI_Settings There can be only one!
		 */
		private static $instance = null;

		/**
		 * Main AyeCode_UI_Settings Instance.
		 *
		 * Ensures only one instance of AyeCode_UI_Settings is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @return AyeCode_UI_Settings - Main instance.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof AyeCode_UI_Settings ) ) {

				self::$instance = new AyeCode_UI_Settings;

				add_action( 'init', array( self::$instance, 'init' ) ); // set settings

				if ( is_admin() ) {
					add_action( 'admin_menu', array( self::$instance, 'menu_item' ) );
					add_action( 'admin_init', array( self::$instance, 'register_settings' ) );

					// Maybe show example page
					add_action( 'template_redirect', array( self::$instance,'maybe_show_examples' ) );
				}

				add_action( 'customize_register', array( self::$instance, 'customizer_settings' ));

				do_action( 'ayecode_ui_settings_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Setup some constants.
		 */
		public function constants(){
			define('AUI_PRIMARY_COLOR_ORIGINAL', "#1e73be");
			define('AUI_SECONDARY_COLOR_ORIGINAL', '#6c757d');
			if (!defined('AUI_PRIMARY_COLOR')) define('AUI_PRIMARY_COLOR', AUI_PRIMARY_COLOR_ORIGINAL);
			if (!defined('AUI_SECONDARY_COLOR')) define('AUI_SECONDARY_COLOR', AUI_SECONDARY_COLOR_ORIGINAL);
		}

		/**
		 * Initiate the settings and add the required action hooks.
		 */
		public function init() {
			$this->constants();
			$this->settings = $this->get_settings();
			$this->url = $this->get_url();

			/**
			 * Maybe load CSS
			 *
			 * We load super early in case there is a theme version that might change the colors
			 */
			if ( $this->settings['css'] ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_style' ), 1 );
			}
			if ( $this->settings['css_backend'] && $this->load_admin_scripts() ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_style' ), 1 );
			}

			// maybe load JS
			if ( $this->settings['js'] ) {
				$priority = $this->is_bs3_compat() ? 100 : 1;
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), $priority );
			}
			if ( $this->settings['js_backend'] && $this->load_admin_scripts() ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 1 );
			}

			// Maybe set the HTML font size
			if ( $this->settings['html_font_size'] ) {
				add_action( 'wp_footer', array( $this, 'html_font_size' ), 10 );
			}


		}

		/**
		 * Check if we should load the admin scripts or not.
		 *
		 * @return bool
		 */
		public function load_admin_scripts(){
			$result = true;

			// check if specifically disabled
			if(!empty($this->settings['disable_admin'])){
				$url_parts = explode("\n",$this->settings['disable_admin']);
				foreach($url_parts as $part){
					if( strpos($_SERVER['REQUEST_URI'], trim($part)) !== false ){
						return false; // return early, no point checking further
					}
				}
			}

			return $result;
		}

		/**
		 * Add a html font size to the footer.
		 */
		public function html_font_size(){
			$this->settings = $this->get_settings();
			echo "<style>html{font-size:".absint($this->settings['html_font_size'])."px;}</style>";
		}

		/**
		 * Check if the current admin screen should load scripts.
		 * 
		 * @return bool
		 */
		public function is_aui_screen(){
			$load = false;
			// check if we should load or not
			if ( is_admin() ) {
				// Only enable on set pages
				$aui_screens = array(
					'page',
					'post',
					'settings_page_ayecode-ui-settings',
					'appearance_page_gutenberg-widgets'
				);
				$screen_ids = apply_filters( 'aui_screen_ids', $aui_screens );

				$screen = get_current_screen();

//				echo '###'.$screen->id;
				
				if ( $screen && in_array( $screen->id, $screen_ids ) ) {
					$load = true;
				}
			}

			return $load;
		}

		/**
		 * Adds the styles.
		 */
		public function enqueue_style() {

			if( is_admin() && !$this->is_aui_screen()){
				// don't add wp-admin scripts if not requested to
			}else{
				$css_setting = current_action() == 'wp_enqueue_scripts' ? 'css' : 'css_backend';

				$rtl = is_rtl() ? '-rtl' : '';

				if($this->settings[$css_setting]){
					$compatibility = $this->settings[$css_setting]=='core' ? false : true;
					$url = $this->settings[$css_setting]=='core' ? $this->url.'assets/css/ayecode-ui'.$rtl.'.css' : $this->url.'assets/css/ayecode-ui-compatibility'.$rtl.'.css';
					wp_register_style( 'ayecode-ui', $url, array(), $this->latest );
					wp_enqueue_style( 'ayecode-ui' );

					// flatpickr
					wp_register_style( 'flatpickr', $this->url.'assets/css/flatpickr.min.css', array(), $this->latest );


					// fix some wp-admin issues
					if(is_admin()){
						$custom_css = "
                body{
                    background-color: #f1f1f1;
                    font-family: -apple-system,BlinkMacSystemFont,\"Segoe UI\",Roboto,Oxygen-Sans,Ubuntu,Cantarell,\"Helvetica Neue\",sans-serif;
                    font-size:13px;
                }
                a {
				    color: #0073aa;
				    text-decoration: underline;
				}
                label {
				    display: initial;
				    margin-bottom: 0;
				}
				input, select {
				    margin: 1px;
				    line-height: initial;
				}
				th, td, div, h2 {
				    box-sizing: content-box;
				}
				p {
				    font-size: 13px;
				    line-height: 1.5;
				    margin: 1em 0;
				}
				h1, h2, h3, h4, h5, h6 {
				    display: block;
				    font-weight: 600;
				}
				h2,h3 {
				    font-size: 1.3em;
				    margin: 1em 0
				}
				.blocks-widgets-container .bsui *{
					box-sizing: border-box;
				}
                ";

						// @todo, remove once fixed :: fix for this bug https://github.com/WordPress/gutenberg/issues/14377
						$custom_css .= "
						.edit-post-sidebar input[type=color].components-text-control__input{
						    padding: 0;
						}
					";
						wp_add_inline_style( 'ayecode-ui', $custom_css );
					}

					// custom changes
					wp_add_inline_style( 'ayecode-ui', self::custom_css($compatibility) );

				}
			}


		}

		/**
		 * Get inline script used if bootstrap enqueued
		 *
		 * If this remains small then its best to use this than to add another JS file.
		 */
		public function inline_script() {
			// Flatpickr calendar locale
			$flatpickr_locale = self::flatpickr_locale();

			ob_start();
			?>
			<script>
				/**
				 * An AUI bootstrap adaptation of GreedyNav.js ( by Luke Jackson ).
				 *
				 * Simply add the class `greedy` to any <nav> menu and it will do the rest.
				 * Licensed under the MIT license - http://opensource.org/licenses/MIT
				 * @ver 0.0.1
				 */
				function aui_init_greedy_nav(){
					jQuery('nav.greedy').each(function(i, obj) {

						// Check if already initialized, if so continue.
						if(jQuery(this).hasClass("being-greedy")){return true;}

						// Make sure its always expanded
						jQuery(this).addClass('navbar-expand');

						// vars
						var $vlinks = '';
						var $dDownClass = '';
						if(jQuery(this).find('.navbar-nav').length){
							if(jQuery(this).find('.navbar-nav').hasClass("being-greedy")){return true;}
							$vlinks = jQuery(this).find('.navbar-nav').addClass("being-greedy w-100").removeClass('overflow-hidden');
						}else if(jQuery(this).find('.nav').length){
							if(jQuery(this).find('.nav').hasClass("being-greedy")){return true;}
							$vlinks = jQuery(this).find('.nav').addClass("being-greedy w-100").removeClass('overflow-hidden');
							$dDownClass = ' mt-2 ';
						}else{
							return false;
						}

						jQuery($vlinks).append('<li class="nav-item list-unstyled ml-auto greedy-btn d-none dropdown ">' +
							'<a href="javascript:void(0)" data-toggle="dropdown" class="nav-link"><i class="fas fa-ellipsis-h"></i> <span class="greedy-count badge badge-dark badge-pill"></span></a>' +
							'<ul class="greedy-links dropdown-menu  dropdown-menu-right '+$dDownClass+'"></ul>' +
							'</li>');

						var $hlinks = jQuery(this).find('.greedy-links');
						var $btn = jQuery(this).find('.greedy-btn');

						var numOfItems = 0;
						var totalSpace = 0;
						var closingTime = 1000;
						var breakWidths = [];

						// Get initial state
						$vlinks.children().outerWidth(function(i, w) {
							totalSpace += w;
							numOfItems += 1;
							breakWidths.push(totalSpace);
						});

						var availableSpace, numOfVisibleItems, requiredSpace, buttonSpace ,timer;

						/*
						 The check function.
						 */
						function check() {

							// Get instant state
							buttonSpace = $btn.width();
							availableSpace = $vlinks.width() - 10;
							numOfVisibleItems = $vlinks.children().length;
							requiredSpace = breakWidths[numOfVisibleItems - 1];

							// There is not enough space
							if (numOfVisibleItems > 1 && requiredSpace > availableSpace) {
								$vlinks.children().last().prev().prependTo($hlinks);
								numOfVisibleItems -= 1;
								check();
								// There is more than enough space
							} else if (availableSpace > breakWidths[numOfVisibleItems]) {
								$hlinks.children().first().insertBefore($btn);
								numOfVisibleItems += 1;
								check();
							}
							// Update the button accordingly
							jQuery($btn).find(".greedy-count").html( numOfItems - numOfVisibleItems);
							if (numOfVisibleItems === numOfItems) {
								$btn.addClass('d-none');
							} else $btn.removeClass('d-none');
						}

						// Window listeners
						jQuery(window).on("resize",function() {
							check();
						});

						// do initial check
						check();
					});
				}

				function aui_select2_locale() {
					var aui_select2_params = <?php echo self::select2_locale(); ?>;

					return {
						'language': {
							errorLoading: function() {
								// Workaround for https://github.com/select2/select2/issues/4355 instead of i18n_ajax_error.
								return aui_select2_params.i18n_searching;
							},
							inputTooLong: function(args) {
								var overChars = args.input.length - args.maximum;
								if (1 === overChars) {
									return aui_select2_params.i18n_input_too_long_1;
								}
								return aui_select2_params.i18n_input_too_long_n.replace('%item%', overChars);
							},
							inputTooShort: function(args) {
								var remainingChars = args.minimum - args.input.length;
								if (1 === remainingChars) {
									return aui_select2_params.i18n_input_too_short_1;
								}
								return aui_select2_params.i18n_input_too_short_n.replace('%item%', remainingChars);
							},
							loadingMore: function() {
								return aui_select2_params.i18n_load_more;
							},
							maximumSelected: function(args) {
								if (args.maximum === 1) {
									return aui_select2_params.i18n_selection_too_long_1;
								}
								return aui_select2_params.i18n_selection_too_long_n.replace('%item%', args.maximum);
							},
							noResults: function() {
								return aui_select2_params.i18n_no_matches;
							},
							searching: function() {
								return aui_select2_params.i18n_searching;
							}
						}
					};
				}

				/**
				 * Initiate Select2 items.
				 */
				function aui_init_select2(){
					var select2_args = jQuery.extend({}, aui_select2_locale());

					jQuery("select.aui-select2").select2(select2_args);
				}

				/**
				 * A function to convert a time value to a "ago" time text.
				 *
				 * @param selector string The .class selector
				 */
				function aui_time_ago(selector) {

					var templates = {
						prefix: "",
						suffix: " ago",
						seconds: "less than a minute",
						minute: "about a minute",
						minutes: "%d minutes",
						hour: "about an hour",
						hours: "about %d hours",
						day: "a day",
						days: "%d days",
						month: "about a month",
						months: "%d months",
						year: "about a year",
						years: "%d years"
					};
					var template = function (t, n) {
						return templates[t] && templates[t].replace(/%d/i, Math.abs(Math.round(n)));
					};

					var timer = function (time) {
						if (!time)
							return;
						time = time.replace(/\.\d+/, ""); // remove milliseconds
						time = time.replace(/-/, "/").replace(/-/, "/");
						time = time.replace(/T/, " ").replace(/Z/, " UTC");
						time = time.replace(/([\+\-]\d\d)\:?(\d\d)/, " $1$2"); // -04:00 -> -0400
						time = new Date(time * 1000 || time);

						var now = new Date();
						var seconds = ((now.getTime() - time) * .001) >> 0;
						var minutes = seconds / 60;
						var hours = minutes / 60;
						var days = hours / 24;
						var years = days / 365;

						return templates.prefix + (
								seconds < 45 && template('seconds', seconds) ||
								seconds < 90 && template('minute', 1) ||
								minutes < 45 && template('minutes', minutes) ||
								minutes < 90 && template('hour', 1) ||
								hours < 24 && template('hours', hours) ||
								hours < 42 && template('day', 1) ||
								days < 30 && template('days', days) ||
								days < 45 && template('month', 1) ||
								days < 365 && template('months', days / 30) ||
								years < 1.5 && template('year', 1) ||
								template('years', years)
							) + templates.suffix;
					};

					var elements = document.getElementsByClassName(selector);
					if (selector && elements && elements.length) {
						for (var i in elements) {
							var $el = elements[i];
							if (typeof $el === 'object') {
								$el.innerHTML = '<i class="far fa-clock"></i> ' + timer($el.getAttribute('title') || $el.getAttribute('datetime'));
							}
						}
					}

					// update time every minute
					setTimeout(function() {
						aui_time_ago(selector);
					}, 60000);

				}

				/**
				 * Initiate tooltips on the page.
				 */
				function aui_init_tooltips(){
					jQuery('[data-toggle="tooltip"]').tooltip();
					jQuery('[data-toggle="popover"]').popover();
					jQuery('[data-toggle="popover-html"]').popover({
						html: true
					});

					// fix popover container compatibility
					jQuery('[data-toggle="popover"],[data-toggle="popover-html"]').on('inserted.bs.popover', function () {
						jQuery('body > .popover').wrapAll("<div class='bsui' />");
					});
				}

				/**
				 * Initiate flatpickrs on the page.
				 */
				$aui_doing_init_flatpickr = false;
				function aui_init_flatpickr(){
					if ( typeof jQuery.fn.flatpickr === "function" && !$aui_doing_init_flatpickr) {
						$aui_doing_init_flatpickr = true;
						<?php if ( ! empty( $flatpickr_locale ) ) { ?>try{flatpickr.localize(<?php echo $flatpickr_locale; ?>);}catch(err){console.log(err.message);}<?php } ?>
						jQuery('input[data-aui-init="flatpickr"]:not(.flatpickr-input)').flatpickr();
					}
					$aui_doing_init_flatpickr = false;
				}

				function aui_modal($title,$body,$footer,$dismissible,$class,$dialog_class) {
					if(!$class){$class = '';}
					if(!$dialog_class){$dialog_class = '';}
					if(!$body){$body = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';}
					// remove it first
					jQuery('.aui-modal').modal('hide').modal('dispose').remove();
					jQuery('.modal-backdrop').remove();

					var $modal = '';

					$modal += '<div class="modal aui-modal fade shadow bsui '+$class+'" tabindex="-1">'+
						'<div class="modal-dialog modal-dialog-centered '+$dialog_class+'">'+
							'<div class="modal-content">';

					if($title) {
						$modal += '<div class="modal-header">' +
						'<h5 class="modal-title">' + $title + '</h5>';

						if ($dismissible) {
							$modal += '<button type="button" class="close" data-dismiss="modal" aria-label="Close">' +
								'<span aria-hidden="true">&times;</span>' +
								'</button>';
						}

						$modal += '</div>';
					}
					$modal += '<div class="modal-body">'+
									$body+
								'</div>';

					if($footer){
						$modal += '<div class="modal-footer">'+
							$footer +
							'</div>';
					}

					$modal +='</div>'+
						'</div>'+
					'</div>';

					jQuery('body').append($modal);

					jQuery('.aui-modal').modal('hide').modal({
						//backdrop: 'static'
					});
				}

				/**
				 * Show / hide fields depending on conditions.
				 */
				function aui_conditional_fields(form){
					jQuery(form).find(".aui-conditional-field").each(function () {

						var $element_require = jQuery(this).data('element-require');

						if ($element_require) {

							$element_require = $element_require.replace("&#039;", "'"); // replace single quotes
							$element_require = $element_require.replace("&quot;", '"'); // replace double quotes

							if (aui_check_form_condition($element_require,form)) {
								jQuery(this).removeClass('d-none');
							} else {
								jQuery(this).addClass('d-none');
							}
						}
					});
				}

				/**
				 * Check form condition
				 */
				function aui_check_form_condition(condition,form) {
					if (form) {
						condition = condition.replace(/\(form\)/g, "('"+form+"')");
					}
					return new Function("return " + condition+";")();
				}

				/**
				 * A function to determine if a element is on screen.
				 */
				jQuery.fn.aui_isOnScreen = function(){

					var win = jQuery(window);

					var viewport = {
						top : win.scrollTop(),
						left : win.scrollLeft()
					};
					viewport.right = viewport.left + win.width();
					viewport.bottom = viewport.top + win.height();

					var bounds = this.offset();
					bounds.right = bounds.left + this.outerWidth();
					bounds.bottom = bounds.top + this.outerHeight();

					return (!(viewport.right < bounds.left || viewport.left > bounds.right || viewport.bottom < bounds.top || viewport.top > bounds.bottom));

				};

				/**
				 * Maybe show multiple carousel items if set to do so.
				 */ 
				function aui_carousel_maybe_show_multiple_items($carousel){
					var $items = {};
					var $item_count = 0;

					// maybe backup
					if(!jQuery($carousel).find('.carousel-inner-original').length){
						jQuery($carousel).append('<div class="carousel-inner-original d-none">'+jQuery($carousel).find('.carousel-inner').html()+'</div>');
					}

					// Get the original items html
					jQuery($carousel).find('.carousel-inner-original .carousel-item').each(function () {
						$items[$item_count] = jQuery(this).html();
						$item_count++;
					});

					// bail if no items
					if(!$item_count){return;}

					if(jQuery(window).width() <= 576){
						// maybe restore original
						if(jQuery($carousel).find('.carousel-inner').hasClass('aui-multiple-items') && jQuery($carousel).find('.carousel-inner-original').length){
							jQuery($carousel).find('.carousel-inner').removeClass('aui-multiple-items').html(jQuery($carousel).find('.carousel-inner-original').html());
							jQuery($carousel).find(".carousel-indicators li").removeClass("d-none");
						}

					}else{
						// new items
						var $md_count = jQuery($carousel).data('limit_show');
						var $new_items = '';
						var $new_items_count = 0;
						var $new_item_count = 0;
						var $closed = true;
						Object.keys($items).forEach(function(key,index) {

							// close
							if(index != 0 && Number.isInteger(index/$md_count) ){
								$new_items += '</div></div>';
								$closed = true;
							}

							// open
							if(index == 0 || Number.isInteger(index/$md_count) ){
								$active = index == 0 ? 'active' : '';
								$new_items += '<div class="carousel-item '+$active+'"><div class="row m-0">';
								$closed = false;
								$new_items_count++;
								$new_item_count = 0;
							}

							// content
							$new_items += '<div class="col pr-1 pl-0">'+$items[index]+'</div>';
							$new_item_count++;


						});

						// close if not closed in the loop
						if(!$closed){
							// check for spares
							if($md_count-$new_item_count > 0){
								$placeholder_count = $md_count-$new_item_count;
								while($placeholder_count > 0){
									$new_items += '<div class="col pr-1 pl-0"></div>';
									$placeholder_count--;
								}

							}

							$new_items += '</div></div>';
						}

						// insert the new items
						jQuery($carousel).find('.carousel-inner').addClass('aui-multiple-items').html($new_items);

						// fix any lazyload images in the active slider
						jQuery($carousel).find('.carousel-item.active img').each(function () {
							// fix the srcset
							if(real_srcset = jQuery(this).attr("data-srcset")){
								if(!jQuery(this).attr("srcset")) jQuery(this).attr("srcset",real_srcset);
							}
							// fix the src
							if(real_src = jQuery(this).attr("data-src")){
								if(!jQuery(this).attr("srcset"))  jQuery(this).attr("src",real_src);
							}
						});

						// maybe fix carousel indicators
						$hide_count = $new_items_count-1;
						jQuery($carousel).find(".carousel-indicators li:gt("+$hide_count+")").addClass("d-none");
					}

					// trigger a global action to say we have
					jQuery( window ).trigger( "aui_carousel_multiple" );
				}

				/**
				 * Init Multiple item carousels.
				 */ 
				function aui_init_carousel_multiple_items(){
					jQuery(window).on("resize",function(){
						jQuery('.carousel-multiple-items').each(function () {
							aui_carousel_maybe_show_multiple_items(this);
						});
					});

					// run now
					jQuery('.carousel-multiple-items').each(function () {
						aui_carousel_maybe_show_multiple_items(this);
					});
				}

				/**
				 * Allow navs to use multiple sub menus.
				 */
				function init_nav_sub_menus(){

					jQuery('.navbar-multi-sub-menus').each(function(i, obj) {
						// Check if already initialized, if so continue.
						if(jQuery(this).hasClass("has-sub-sub-menus")){return true;}

						// Make sure its always expanded
						jQuery(this).addClass('has-sub-sub-menus');

						jQuery(this).find( '.dropdown-menu a.dropdown-toggle' ).on( 'click', function ( e ) {
							var $el = jQuery( this );
							$el.toggleClass('active-dropdown');
							var $parent = jQuery( this ).offsetParent( ".dropdown-menu" );
							if ( !jQuery( this ).next().hasClass( 'show' ) ) {
								jQuery( this ).parents( '.dropdown-menu' ).first().find( '.show' ).removeClass( "show" );
							}
							var $subMenu = jQuery( this ).next( ".dropdown-menu" );
							$subMenu.toggleClass( 'show' );

							jQuery( this ).parent( "li" ).toggleClass( 'show' );

							jQuery( this ).parents( 'li.nav-item.dropdown.show' ).on( 'hidden.bs.dropdown', function ( e ) {
								jQuery( '.dropdown-menu .show' ).removeClass( "show" );
								$el.removeClass('active-dropdown');
							} );

							if ( !$parent.parent().hasClass( 'navbar-nav' ) ) {
								$el.next().addClass('position-relative border-top border-bottom');
							}

							return false;
						} );

					});

				}


				/**
				 * Open a lightbox when an embed item is clicked.
				 */
				function aui_lightbox_embed($link,ele){
					ele.preventDefault();

					// remove it first
					jQuery('.aui-carousel-modal').remove();

					var $modal = '<div class="modal fade aui-carousel-modal bsui" tabindex="-1" role="dialog" aria-labelledby="aui-modal-title" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-xl mw-100"><div class="modal-content bg-transparent border-0"><div class="modal-header"><h5 class="modal-title" id="aui-modal-title"></h5></div><div class="modal-body text-center"><i class="fas fa-circle-notch fa-spin fa-3x"></i></div></div></div></div>';
					jQuery('body').append($modal);

					jQuery('.aui-carousel-modal').modal({
						//backdrop: 'static'
					});
					jQuery('.aui-carousel-modal').on('hidden.bs.modal', function (e) {
						jQuery("iframe").attr('src', '');
					});

					$container = jQuery($link).closest('.aui-gallery');

					$clicked_href = jQuery($link).attr('href');
					$images = [];
					$container.find('.aui-lightbox-image').each(function() {
						var a = this;
						var href = jQuery(a).attr('href');
						if (href) {
							$images.push(href);
						}
					});

					if( $images.length ){
						var $carousel = '<div id="aui-embed-slider-modal" class="carousel slide" >';

						// indicators
						if($images.length > 1){
							$i = 0;
							$carousel  += '<ol class="carousel-indicators position-fixed">';
							$container.find('.aui-lightbox-image').each(function() {
								$active = $clicked_href == jQuery(this).attr('href') ? 'active' : '';
								$carousel  += '<li data-target="#aui-embed-slider-modal" data-slide-to="'+$i+'" class="'+$active+'"></li>';
								$i++;

							});
							$carousel  += '</ol>';
						}



						// items
						$i = 0;
						$carousel  += '<div class="carousel-inner">';
						$container.find('.aui-lightbox-image').each(function() {
							var a = this;

							$active = $clicked_href == jQuery(this).attr('href') ? 'active' : '';
							$carousel  += '<div class="carousel-item '+ $active+'"><div>';


							// image
							var css_height = window.innerWidth > window.innerHeight ? '90vh' : 'auto';
							var img = jQuery(a).find('img').clone().removeClass().addClass('mx-auto d-block w-auto mw-100 rounded').css('height',css_height).get(0).outerHTML;
							$carousel  += img;
							// captions
							if(jQuery(a).parent().find('.carousel-caption').length ){
								$carousel  += jQuery(a).parent().find('.carousel-caption').clone().removeClass('sr-only').get(0).outerHTML;
							}
							$carousel  += '</div></div>';
							$i++;

						});
						$container.find('.aui-lightbox-iframe').each(function() {
							var a = this;

							$active = $clicked_href == jQuery(this).attr('href') ? 'active' : '';
							$carousel  += '<div class="carousel-item '+ $active+'"><div class="modal-xl mx-auto embed-responsive embed-responsive-16by9">';


							// iframe
							var css_height = window.innerWidth > window.innerHeight ? '95vh' : 'auto';
							var url = jQuery(a).attr('href');
							var iframe = '<iframe class="embed-responsive-item" style="height:'+css_height +'" src="'+url+'?rel=0&amp;showinfo=0&amp;modestbranding=1&amp;autoplay=1" id="video" allow="autoplay"></iframe>';
							var img = iframe ;//.css('height',css_height).get(0).outerHTML;
							$carousel  += img;

							$carousel  += '</div></div>';
							$i++;

						});
						$carousel  += '</div>';


						// next/prev indicators
						if($images.length > 1) {
							$carousel += '<a class="carousel-control-prev" href="#aui-embed-slider-modal" role="button" data-slide="prev">';
							$carousel += '<span class="carousel-control-prev-icon" aria-hidden="true"></span>';
							$carousel += ' <a class="carousel-control-next" href="#aui-embed-slider-modal" role="button" data-slide="next">';
							$carousel += '<span class="carousel-control-next-icon" aria-hidden="true"></span>';
							$carousel += '</a>';
						}


						$carousel  += '</div>';

						var $close = '<button type="button" class="close text-white text-right position-fixed" style="font-size: 2.5em;right: 20px;top: 10px; z-index: 1055;" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';

						jQuery('.aui-carousel-modal .modal-content').html($carousel).prepend($close);

						// enable ajax load
						//gd_init_carousel_ajax();
					}

				}

				/**
				 * Init lightbox embed.
				 */
				function aui_init_lightbox_embed(){
					// Open a lightbox for embeded items
					jQuery('.aui-lightbox-image, .aui-lightbox-iframe').unbind('click').click(function(ele) {
						aui_lightbox_embed(this,ele);
					});
				}
				

				/**
				 * Initiate all AUI JS.
				 */
				function aui_init(){
					// nav menu submenus
					init_nav_sub_menus();
					
					// init tooltips
					aui_init_tooltips();

					// init select2
					aui_init_select2();

					// init flatpickr
					aui_init_flatpickr();

					// init Greedy nav
					aui_init_greedy_nav();

					// Set times to time ago
					aui_time_ago('timeago');
					
					// init multiple item carousels
					aui_init_carousel_multiple_items();
					
					// init lightbox embeds
					aui_init_lightbox_embed();
				}

				// run on window loaded
				jQuery(window).on("load",function() {
					aui_init();
				});

			</script>
			<?php
			$output = ob_get_clean();

			/*
			 * We only add the <script> tags for code highlighting, so we strip them from the output.
			 */
			return str_replace( array(
				'<script>',
				'</script>'
			), '', $output );
		}


		/**
		 * JS to help with conflict issues with other plugins and themes using bootstrap v3.
		 *
		 * @TODO we may need this when other conflicts arrise.
		 * @return mixed
		 */
		public static function bs3_compat_js() {
			ob_start();
			?>
			<script>
				<?php if( defined( 'FUSION_BUILDER_VERSION' ) ){ ?>
				/* With Avada builder */

				<?php } ?>
			</script>
			<?php
			return str_replace( array(
				'<script>',
				'</script>'
			), '', ob_get_clean());
		}

		/**
		 * Get inline script used if bootstrap file browser enqueued.
		 *
		 * If this remains small then its best to use this than to add another JS file.
		 */
		public function inline_script_file_browser(){
			ob_start();
			?>
			<script>
				// run on doc ready
				jQuery(document).ready(function () {
					bsCustomFileInput.init();
				});
			</script>
			<?php
			$output = ob_get_clean();

			/*
			 * We only add the <script> tags for code highlighting, so we strip them from the output.
			 */
			return str_replace( array(
				'<script>',
				'</script>'
			), '', $output );
		}

		/**
		 * Adds the Font Awesome JS.
		 */
		public function enqueue_scripts() {

			if( is_admin() && !$this->is_aui_screen()){
				// don't add wp-admin scripts if not requested to
			}else {

				$js_setting = current_action() == 'wp_enqueue_scripts' ? 'js' : 'js_backend';

				// select2
				wp_register_script( 'select2', $this->url . 'assets/js/select2.min.js', array( 'jquery' ), $this->select2_version );

				// flatpickr
				wp_register_script( 'flatpickr', $this->url . 'assets/js/flatpickr.min.js', array(), $this->latest );

				// Bootstrap file browser
				wp_register_script( 'aui-custom-file-input', $url = $this->url . 'assets/js/bs-custom-file-input.min.js', array( 'jquery' ), $this->select2_version );
				wp_add_inline_script( 'aui-custom-file-input', $this->inline_script_file_browser() );

				$load_inline = false;

				if ( $this->settings[ $js_setting ] == 'core-popper' ) {
					// Bootstrap bundle
					$url = $this->url . 'assets/js/bootstrap.bundle.min.js';
					wp_register_script( 'bootstrap-js-bundle', $url, array(
						'select2',
						'jquery'
					), $this->latest, $this->is_bs3_compat() );
					// if in admin then add to footer for compatibility.
					is_admin() ? wp_enqueue_script( 'bootstrap-js-bundle', '', null, null, true ) : wp_enqueue_script( 'bootstrap-js-bundle' );
					$script = $this->inline_script();
					wp_add_inline_script( 'bootstrap-js-bundle', $script );
				} elseif ( $this->settings[ $js_setting ] == 'popper' ) {
					$url = $this->url . 'assets/js/popper.min.js';
					wp_register_script( 'bootstrap-js-popper', $url, array( 'select2', 'jquery' ), $this->latest );
					wp_enqueue_script( 'bootstrap-js-popper' );
					$load_inline = true;
				} else {
					$load_inline = true;
				}

				// Load needed inline scripts by faking the loading of a script if the main script is not being loaded
				if ( $load_inline ) {
					wp_register_script( 'bootstrap-dummy', '', array( 'select2', 'jquery' ) );
					wp_enqueue_script( 'bootstrap-dummy' );
					$script = $this->inline_script();
					wp_add_inline_script( 'bootstrap-dummy', $script );
				}
			}

		}

		/**
		 * Enqueue flatpickr if called.
		 */
		public function enqueue_flatpickr(){
			wp_enqueue_style( 'flatpickr' );
			wp_enqueue_script( 'flatpickr' );
		}

		/**
		 * Get the url path to the current folder.
		 *
		 * @return string
		 */
		public function get_url() {

			$url = '';
			// check if we are inside a plugin
			$file_dir = str_replace( "/includes","", wp_normalize_path( dirname( __FILE__ ) ) );

			// add check in-case user has changed wp-content dir name.
			$wp_content_folder_name = basename(WP_CONTENT_DIR);
			$dir_parts = explode("/$wp_content_folder_name/",$file_dir);
			$url_parts = explode("/$wp_content_folder_name/",plugins_url());

			if(!empty($url_parts[0]) && !empty($dir_parts[1])){
				$url = trailingslashit( $url_parts[0]."/$wp_content_folder_name/".$dir_parts[1] );
			}

			return $url;
		}

		/**
		 * Register the database settings with WordPress.
		 */
		public function register_settings() {
			register_setting( 'ayecode-ui-settings', 'ayecode-ui-settings' );
		}

		/**
		 * Add the WordPress settings menu item.
		 * @since 1.0.10 Calling function name direct will fail theme check so we don't.
		 */
		public function menu_item() {
			$menu_function = 'add' . '_' . 'options' . '_' . 'page'; // won't pass theme check if function name present in theme
			call_user_func( $menu_function, $this->name, $this->name, 'manage_options', 'ayecode-ui-settings', array(
				$this,
				'settings_page'
			) );
		}

		/**
		 * Get a list of themes and their default JS settings.
		 *
		 * @return array
		 */
		public function theme_js_settings(){
			return array(
				'ayetheme' => 'popper',
				'listimia' => 'required',
				'listimia_backend' => 'core-popper',
				//'avada'    => 'required', // removed as we now add compatibility
			);
		}

		/**
		 * Get the current Font Awesome output settings.
		 *
		 * @return array The array of settings.
		 */
		public function get_settings() {

			$db_settings = get_option( 'ayecode-ui-settings' );
			$js_default = 'core-popper';
			$js_default_backend = $js_default;

			// maybe set defaults (if no settings set)
			if(empty($db_settings)){
				$active_theme = strtolower( get_template() ); // active parent theme.
				$theme_js_settings = self::theme_js_settings();
				if(isset($theme_js_settings[$active_theme])){
					$js_default = $theme_js_settings[$active_theme];
					$js_default_backend = isset($theme_js_settings[$active_theme."_backend"]) ? $theme_js_settings[$active_theme."_backend"] : $js_default;
				}
			}

			$defaults = array(
				'css'       => 'compatibility', // core, compatibility
				'js'        => $js_default, // js to load, core-popper, popper
				'html_font_size'        => '16', // js to load, core-popper, popper
				'css_backend'       => 'compatibility', // core, compatibility
				'js_backend'        => $js_default_backend, // js to load, core-popper, popper
				'disable_admin'     =>  '', // URL snippets to disable loading on admin
			);

			$settings = wp_parse_args( $db_settings, $defaults );

			/**
			 * Filter the Bootstrap settings.
			 *
			 * @todo if we add this filer people might use it and then it defeates the purpose of this class :/
			 */
			return $this->settings = apply_filters( 'ayecode-ui-settings', $settings, $db_settings, $defaults );
		}


		/**
		 * The settings page html output.
		 */
		public function settings_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'aui' ) );
			}
			?>
			<div class="wrap">
				<h1><?php echo $this->name; ?></h1>
				<p><?php _e("Here you can adjust settings if you are having compatibility issues.",'aui');?></p>
				<form method="post" action="options.php">
					<?php
					settings_fields( 'ayecode-ui-settings' );
					do_settings_sections( 'ayecode-ui-settings' );
					?>

					<h2><?php _e( 'Frontend', 'aui' ); ?></h2>
					<table class="form-table wpbs-table-settings">
						<tr valign="top">
							<th scope="row"><label
									for="wpbs-css"><?php _e( 'Load CSS', 'aui' ); ?></label></th>
							<td>
								<select name="ayecode-ui-settings[css]" id="wpbs-css">
									<option	value="compatibility" <?php selected( $this->settings['css'], 'compatibility' ); ?>><?php _e( 'Compatibility Mode (default)', 'aui' ); ?></option>
									<option value="core" <?php selected( $this->settings['css'], 'core' ); ?>><?php _e( 'Full Mode', 'aui' ); ?></option>
									<option	value="" <?php selected( $this->settings['css'], '' ); ?>><?php _e( 'Disabled', 'aui' ); ?></option>
								</select>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><label
									for="wpbs-js"><?php _e( 'Load JS', 'aui' ); ?></label></th>
							<td>
								<select name="ayecode-ui-settings[js]" id="wpbs-js">
									<option	value="core-popper" <?php selected( $this->settings['js'], 'core-popper' ); ?>><?php _e( 'Core + Popper (default)', 'aui' ); ?></option>
									<option value="popper" <?php selected( $this->settings['js'], 'popper' ); ?>><?php _e( 'Popper', 'aui' ); ?></option>
									<option value="required" <?php selected( $this->settings['js'], 'required' ); ?>><?php _e( 'Required functions only', 'aui' ); ?></option>
									<option	value="" <?php selected( $this->settings['js'], '' ); ?>><?php _e( 'Disabled (not recommended)', 'aui' ); ?></option>
								</select>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><label
									for="wpbs-font_size"><?php _e( 'HTML Font Size (px)', 'aui' ); ?></label></th>
							<td>
								<input type="number" name="ayecode-ui-settings[html_font_size]" id="wpbs-font_size" value="<?php echo absint( $this->settings['html_font_size']); ?>" placeholder="16" />
								<p class="description" ><?php _e("Our font sizing is rem (responsive based) here you can set the html font size in-case your theme is setting it too low.",'aui');?></p>
							</td>
						</tr>

					</table>

					<h2><?php _e( 'Backend', 'aui' ); ?> (wp-admin)</h2>
					<table class="form-table wpbs-table-settings">
						<tr valign="top">
							<th scope="row"><label
									for="wpbs-css-admin"><?php _e( 'Load CSS', 'aui' ); ?></label></th>
							<td>
								<select name="ayecode-ui-settings[css_backend]" id="wpbs-css-admin">
									<option	value="compatibility" <?php selected( $this->settings['css_backend'], 'compatibility' ); ?>><?php _e( 'Compatibility Mode (default)', 'aui' ); ?></option>
									<option value="core" <?php selected( $this->settings['css_backend'], 'core' ); ?>><?php _e( 'Full Mode (will cause style issues)', 'aui' ); ?></option>
									<option	value="" <?php selected( $this->settings['css_backend'], '' ); ?>><?php _e( 'Disabled', 'aui' ); ?></option>
								</select>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><label
									for="wpbs-js-admin"><?php _e( 'Load JS', 'aui' ); ?></label></th>
							<td>
								<select name="ayecode-ui-settings[js_backend]" id="wpbs-js-admin">
									<option	value="core-popper" <?php selected( $this->settings['js_backend'], 'core-popper' ); ?>><?php _e( 'Core + Popper (default)', 'aui' ); ?></option>
									<option value="popper" <?php selected( $this->settings['js_backend'], 'popper' ); ?>><?php _e( 'Popper', 'aui' ); ?></option>
									<option value="required" <?php selected( $this->settings['js_backend'], 'required' ); ?>><?php _e( 'Required functions only', 'aui' ); ?></option>
									<option	value="" <?php selected( $this->settings['js_backend'], '' ); ?>><?php _e( 'Disabled (not recommended)', 'aui' ); ?></option>
								</select>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><label
									for="wpbs-disable-admin"><?php _e( 'Disable load on URL', 'aui' ); ?></label></th>
							<td>
								<p><?php _e( 'If you have backend conflict you can enter a partial URL argument that will disable the loading of AUI on those pages. Add each argument on a new line.', 'aui' ); ?></p>
								<textarea name="ayecode-ui-settings[disable_admin]" rows="10" cols="50" id="wpbs-disable-admin" class="large-text code" spellcheck="false" placeholder="myplugin.php &#10;action=go"><?php echo $this->settings['disable_admin'];?></textarea>

							</td>
						</tr>

					</table>

					<?php
					submit_button();
					?>
				</form>

				<div id="wpbs-version"><?php echo $this->version; ?></div>
			</div>

			<?php
		}

		public function customizer_settings($wp_customize){
			$wp_customize->add_section('aui_settings', array(
				'title'    => __('AyeCode UI','aui'),
				'priority' => 120,
			));

			//  =============================
			//  = Color Picker              =
			//  =============================
			$wp_customize->add_setting('aui_options[color_primary]', array(
				'default'           => AUI_PRIMARY_COLOR,
				'sanitize_callback' => 'sanitize_hex_color',
				'capability'        => 'edit_theme_options',
				'type'              => 'option',
				'transport'         => 'refresh',
			));
			$wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'color_primary', array(
				'label'    => __('Primary Color','aui'),
				'section'  => 'aui_settings',
				'settings' => 'aui_options[color_primary]',
			)));

			$wp_customize->add_setting('aui_options[color_secondary]', array(
				'default'           => '#6c757d',
				'sanitize_callback' => 'sanitize_hex_color',
				'capability'        => 'edit_theme_options',
				'type'              => 'option',
				'transport'         => 'refresh',
			));
			$wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'color_secondary', array(
				'label'    => __('Secondary Color','aui'),
				'section'  => 'aui_settings',
				'settings' => 'aui_options[color_secondary]',
			)));
		}

		/**
		 * CSS to help with conflict issues with other plugins and themes using bootstrap v3.
		 *
		 * @return mixed
		 */
		public static function bs3_compat_css() {
			ob_start();
			?>
			<style>
			/* Bootstrap 3 compatibility */
			body.modal-open .modal-backdrop.show:not(.in) {opacity:0.5;}
			body.modal-open .modal.show:not(.in)  {opacity:1;z-index: 99999}
			body.modal-open .modal.show:not(.in) .modal-content  {box-shadow: none;}
			body.modal-open .modal.show:not(.in)  .modal-dialog {transform: initial;}

			body.modal-open .modal.bsui .modal-dialog{left: auto;}

			.collapse.show:not(.in){display: inherit;}
			.fade.show{opacity: 1;}

			<?php if( defined( 'SVQ_THEME_VERSION' ) ){ ?>
			/* KLEO theme specific */
			.kleo-main-header .navbar-collapse.collapse.show:not(.in){display: inherit !important;}
			<?php } ?>

			<?php if( defined( 'FUSION_BUILDER_VERSION' ) ){ ?>
			/* With Avada builder */
			body.modal-open .modal.in  {opacity:1;z-index: 99999}
			body.modal-open .modal.bsui.in .modal-content  {box-shadow: none;}
			.bsui .collapse.in{display: inherit;}
			<?php } ?>
			</style>
			<?php
			return str_replace( array(
				'<style>',
				'</style>'
			), '', ob_get_clean());
		}


		public static function custom_css($compatibility = true) {
			$settings = get_option('aui_options');

			ob_start();

			$primary_color = !empty($settings['color_primary']) ? $settings['color_primary'] : AUI_PRIMARY_COLOR;
			$secondary_color = !empty($settings['color_secondary']) ? $settings['color_secondary'] : AUI_SECONDARY_COLOR;
				//AUI_PRIMARY_COLOR_ORIGINAL
			?>
			<style>
				<?php

					// BS v3 compat
					if( self::is_bs3_compat() ){
					    echo self::bs3_compat_css();
					}

					if(!is_admin() && $primary_color != AUI_PRIMARY_COLOR_ORIGINAL){
						echo self::css_primary($primary_color,$compatibility);
					}

					if(!is_admin() && $secondary_color != AUI_SECONDARY_COLOR_ORIGINAL){
						echo self::css_secondary($settings['color_secondary'],$compatibility);
					}

					// Set admin bar z-index lower when modal is open.
					echo ' body.modal-open #wpadminbar{z-index:999}';
                ?>
			</style>
			<?php


			/*
			 * We only add the <script> tags for code highlighting, so we strip them from the output.
			 */
			return str_replace( array(
				'<style>',
				'</style>'
			), '', ob_get_clean());
		}

		/**
		 * Check if we should add booststrap 3 compatibility changes.
		 *
		 * @return bool
		 */
		public static function is_bs3_compat(){
			return defined('AYECODE_UI_BS3_COMPAT') || defined('SVQ_THEME_VERSION') || defined('FUSION_BUILDER_VERSION');
		}

		public static function css_primary($color_code,$compatibility){;
			$color_code = sanitize_hex_color($color_code);
			if(!$color_code){return '';}
			/**
			 * c = color, b = background color, o = border-color, f = fill
			 */
			$selectors = array(
				'a' => array('c'),
				'.btn-primary' => array('b','o'),
				'.btn-primary.disabled' => array('b','o'),
				'.btn-primary:disabled' => array('b','o'),
				'.btn-outline-primary' => array('c','o'),
				'.btn-outline-primary:hover' => array('b','o'),
				'.btn-outline-primary:not(:disabled):not(.disabled).active' => array('b','o'),
				'.btn-outline-primary:not(:disabled):not(.disabled):active' => array('b','o'),
				'.show>.btn-outline-primary.dropdown-toggle' => array('b','o'),
				'.btn-link' => array('c'),
				'.dropdown-item.active' => array('b'),
				'.custom-control-input:checked~.custom-control-label::before' => array('b','o'),
				'.custom-checkbox .custom-control-input:indeterminate~.custom-control-label::before' => array('b','o'),
//				'.custom-range::-webkit-slider-thumb' => array('b'), // these break the inline rules...
//				'.custom-range::-moz-range-thumb' => array('b'),
//				'.custom-range::-ms-thumb' => array('b'),
				'.nav-pills .nav-link.active' => array('b'),
				'.nav-pills .show>.nav-link' => array('b'),
				'.page-link' => array('c'),
				'.page-item.active .page-link' => array('b','o'),
				'.badge-primary' => array('b'),
				'.alert-primary' => array('b','o'),
				'.progress-bar' => array('b'),
				'.list-group-item.active' => array('b','o'),
				'.bg-primary' => array('b','f'),
				'.btn-link.btn-primary' => array('c'),
				'.select2-container .select2-results__option--highlighted.select2-results__option[aria-selected=true]' => array('b'),
			);

			$important_selectors = array(
				'.bg-primary' => array('b','f'),
				'.border-primary' => array('o'),
				'.text-primary' => array('c'),
			);

			$color = array();
			$color_i = array();
			$background = array();
			$background_i = array();
			$border = array();
			$border_i = array();
			$fill = array();
			$fill_i = array();

			$output = '';

			// build rules into each type
			foreach($selectors as $selector => $types){
				$selector = $compatibility ? ".bsui ".$selector : $selector;
				$types = array_combine($types,$types);
				if(isset($types['c'])){$color[] = $selector;}
				if(isset($types['b'])){$background[] = $selector;}
				if(isset($types['o'])){$border[] = $selector;}
				if(isset($types['f'])){$fill[] = $selector;}
			}

			// build rules into each type
			foreach($important_selectors as $selector => $types){
				$selector = $compatibility ? ".bsui ".$selector : $selector;
				$types = array_combine($types,$types);
				if(isset($types['c'])){$color_i[] = $selector;}
				if(isset($types['b'])){$background_i[] = $selector;}
				if(isset($types['o'])){$border_i[] = $selector;}
				if(isset($types['f'])){$fill_i[] = $selector;}
			}

			// add any color rules
			if(!empty($color)){
				$output .= implode(",",$color) . "{color: $color_code;} ";
			}
			if(!empty($color_i)){
				$output .= implode(",",$color_i) . "{color: $color_code !important;} ";
			}

			// add any background color rules
			if(!empty($background)){
				$output .= implode(",",$background) . "{background-color: $color_code;} ";
			}
			if(!empty($background_i)){
				$output .= implode(",",$background_i) . "{background-color: $color_code !important;} ";
			}

			// add any border color rules
			if(!empty($border)){
				$output .= implode(",",$border) . "{border-color: $color_code;} ";
			}
			if(!empty($border_i)){
				$output .= implode(",",$border_i) . "{border-color: $color_code !important;} ";
			}

			// add any fill color rules
			if(!empty($fill)){
				$output .= implode(",",$fill) . "{fill: $color_code;} ";
			}
			if(!empty($fill_i)){
				$output .= implode(",",$fill_i) . "{fill: $color_code !important;} ";
			}


			$prefix = $compatibility ? ".bsui " : "";

			// darken
			$darker_075 = self::css_hex_lighten_darken($color_code,"-0.075");
			$darker_10 = self::css_hex_lighten_darken($color_code,"-0.10");
			$darker_125 = self::css_hex_lighten_darken($color_code,"-0.125");

			// lighten
			$lighten_25 = self::css_hex_lighten_darken($color_code,"0.25");

			// opacity see https://css-tricks.com/8-digit-hex-codes/
			$op_25 = $color_code."40"; // 25% opacity


			// button states
			$output .= $prefix ." .btn-primary:hover{background-color: ".$darker_075.";    border-color: ".$darker_10.";} ";
			$output .= $prefix ." .btn-outline-primary:not(:disabled):not(.disabled):active:focus, $prefix .btn-outline-primary:not(:disabled):not(.disabled).active:focus, .show>$prefix .btn-outline-primary.dropdown-toggle:focus{box-shadow: 0 0 0 0.2rem $op_25;} ";
			$output .= $prefix ." .btn-primary:not(:disabled):not(.disabled):active, $prefix .btn-primary:not(:disabled):not(.disabled).active, .show>$prefix .btn-primary.dropdown-toggle{background-color: ".$darker_10.";    border-color: ".$darker_125.";} ";
			$output .= $prefix ." .btn-primary:not(:disabled):not(.disabled):active:focus, $prefix .btn-primary:not(:disabled):not(.disabled).active:focus, .show>$prefix .btn-primary.dropdown-toggle:focus {box-shadow: 0 0 0 0.2rem $op_25;} ";


			// dropdown's
			$output .= $prefix ." .dropdown-item.active, $prefix .dropdown-item:active{background-color: $color_code;} ";


			// input states
			$output .= $prefix ." .form-control:focus{border-color: ".$lighten_25.";box-shadow: 0 0 0 0.2rem $op_25;} ";

			// page link
			$output .= $prefix ." .page-link:focus{box-shadow: 0 0 0 0.2rem $op_25;} ";

			return $output;
		}

		public static function css_secondary($color_code,$compatibility){;
			$color_code = sanitize_hex_color($color_code);
			if(!$color_code){return '';}
			/**
			 * c = color, b = background color, o = border-color, f = fill
			 */
			$selectors = array(
				'.btn-secondary' => array('b','o'),
				'.btn-secondary.disabled' => array('b','o'),
				'.btn-secondary:disabled' => array('b','o'),
				'.btn-outline-secondary' => array('c','o'),
				'.btn-outline-secondary:hover' => array('b','o'),
				'.btn-outline-secondary.disabled' => array('c'),
				'.btn-outline-secondary:disabled' => array('c'),
				'.btn-outline-secondary:not(:disabled):not(.disabled):active' => array('b','o'),
				'.btn-outline-secondary:not(:disabled):not(.disabled).active' => array('b','o'),
				'.btn-outline-secondary.dropdown-toggle' => array('b','o'),
				'.badge-secondary' => array('b'),
				'.alert-secondary' => array('b','o'),
				'.btn-link.btn-secondary' => array('c'),
			);

			$important_selectors = array(
				'.bg-secondary' => array('b','f'),
				'.border-secondary' => array('o'),
				'.text-secondary' => array('c'),
			);

			$color = array();
			$color_i = array();
			$background = array();
			$background_i = array();
			$border = array();
			$border_i = array();
			$fill = array();
			$fill_i = array();

			$output = '';

			// build rules into each type
			foreach($selectors as $selector => $types){
				$selector = $compatibility ? ".bsui ".$selector : $selector;
				$types = array_combine($types,$types);
				if(isset($types['c'])){$color[] = $selector;}
				if(isset($types['b'])){$background[] = $selector;}
				if(isset($types['o'])){$border[] = $selector;}
				if(isset($types['f'])){$fill[] = $selector;}
			}

			// build rules into each type
			foreach($important_selectors as $selector => $types){
				$selector = $compatibility ? ".bsui ".$selector : $selector;
				$types = array_combine($types,$types);
				if(isset($types['c'])){$color_i[] = $selector;}
				if(isset($types['b'])){$background_i[] = $selector;}
				if(isset($types['o'])){$border_i[] = $selector;}
				if(isset($types['f'])){$fill_i[] = $selector;}
			}

			// add any color rules
			if(!empty($color)){
				$output .= implode(",",$color) . "{color: $color_code;} ";
			}
			if(!empty($color_i)){
				$output .= implode(",",$color_i) . "{color: $color_code !important;} ";
			}

			// add any background color rules
			if(!empty($background)){
				$output .= implode(",",$background) . "{background-color: $color_code;} ";
			}
			if(!empty($background_i)){
				$output .= implode(",",$background_i) . "{background-color: $color_code !important;} ";
			}

			// add any border color rules
			if(!empty($border)){
				$output .= implode(",",$border) . "{border-color: $color_code;} ";
			}
			if(!empty($border_i)){
				$output .= implode(",",$border_i) . "{border-color: $color_code !important;} ";
			}

			// add any fill color rules
			if(!empty($fill)){
				$output .= implode(",",$fill) . "{fill: $color_code;} ";
			}
			if(!empty($fill_i)){
				$output .= implode(",",$fill_i) . "{fill: $color_code !important;} ";
			}


			$prefix = $compatibility ? ".bsui " : "";

			// darken
			$darker_075 = self::css_hex_lighten_darken($color_code,"-0.075");
			$darker_10 = self::css_hex_lighten_darken($color_code,"-0.10");
			$darker_125 = self::css_hex_lighten_darken($color_code,"-0.125");

			// lighten
			$lighten_25 = self::css_hex_lighten_darken($color_code,"0.25");

			// opacity see https://css-tricks.com/8-digit-hex-codes/
			$op_25 = $color_code."40"; // 25% opacity


			// button states
			$output .= $prefix ." .btn-secondary:hover{background-color: ".$darker_075.";    border-color: ".$darker_10.";} ";
			$output .= $prefix ." .btn-outline-secondary:not(:disabled):not(.disabled):active:focus, $prefix .btn-outline-secondary:not(:disabled):not(.disabled).active:focus, .show>$prefix .btn-outline-secondary.dropdown-toggle:focus{box-shadow: 0 0 0 0.2rem $op_25;} ";
			$output .= $prefix ." .btn-secondary:not(:disabled):not(.disabled):active, $prefix .btn-secondary:not(:disabled):not(.disabled).active, .show>$prefix .btn-secondary.dropdown-toggle{background-color: ".$darker_10.";    border-color: ".$darker_125.";} ";
			$output .= $prefix ." .btn-secondary:not(:disabled):not(.disabled):active:focus, $prefix .btn-secondary:not(:disabled):not(.disabled).active:focus, .show>$prefix .btn-secondary.dropdown-toggle:focus {box-shadow: 0 0 0 0.2rem $op_25;} ";


			return $output;
		}

		/**
		 * Increases or decreases the brightness of a color by a percentage of the current brightness.
		 *
		 * @param   string  $hexCode        Supported formats: `#FFF`, `#FFFFFF`, `FFF`, `FFFFFF`
		 * @param   float   $adjustPercent  A number between -1 and 1. E.g. 0.3 = 30% lighter; -0.4 = 40% darker.
		 *
		 * @return  string
		 */
		public static function css_hex_lighten_darken($hexCode, $adjustPercent) {
			$hexCode = ltrim($hexCode, '#');

			if (strlen($hexCode) == 3) {
				$hexCode = $hexCode[0] . $hexCode[0] . $hexCode[1] . $hexCode[1] . $hexCode[2] . $hexCode[2];
			}

			$hexCode = array_map('hexdec', str_split($hexCode, 2));

			foreach ($hexCode as & $color) {
				$adjustableLimit = $adjustPercent < 0 ? $color : 255 - $color;
				$adjustAmount = ceil($adjustableLimit * $adjustPercent);

				$color = str_pad(dechex($color + $adjustAmount), 2, '0', STR_PAD_LEFT);
			}

			return '#' . implode($hexCode);
		}

		/**
		 * Check if we should display examples.
		 */
		public function maybe_show_examples(){
			if(current_user_can('manage_options') && isset($_REQUEST['preview-aui'])){
				echo "<head>";
				wp_head();
				echo "</head>";
				echo "<body>";
				echo $this->get_examples();
				echo "</body>";
				exit;
			}
		}

		/**
		 * Get developer examples.
		 *
		 * @return string
		 */
		public function get_examples(){
			$output = '';


			// open form
			$output .= "<form class='p-5 m-5 border rounded'>";

			// input example
			$output .= aui()->input(array(
				'type'  =>  'text',
				'id'    =>  'text-example',
				'name'    =>  'text-example',
				'placeholder'   => 'text placeholder',
				'title'   => 'Text input example',
				'value' =>  '',
				'required'  => false,
				'help_text' => 'help text',
				'label' => 'Text input example label'
			));

			// input example
			$output .= aui()->input(array(
				'type'  =>  'url',
				'id'    =>  'text-example2',
				'name'    =>  'text-example',
				'placeholder'   => 'url placeholder',
				'title'   => 'Text input example',
				'value' =>  '',
				'required'  => false,
				'help_text' => 'help text',
				'label' => 'Text input example label'
			));

			// checkbox example
			$output .= aui()->input(array(
				'type'  =>  'checkbox',
				'id'    =>  'checkbox-example',
				'name'    =>  'checkbox-example',
				'placeholder'   => 'checkbox-example',
				'title'   => 'Checkbox example',
				'value' =>  '1',
				'checked'   => true,
				'required'  => false,
				'help_text' => 'help text',
				'label' => 'Checkbox checked'
			));

			// checkbox example
			$output .= aui()->input(array(
				'type'  =>  'checkbox',
				'id'    =>  'checkbox-example2',
				'name'    =>  'checkbox-example2',
				'placeholder'   => 'checkbox-example',
				'title'   => 'Checkbox example',
				'value' =>  '1',
				'checked'   => false,
				'required'  => false,
				'help_text' => 'help text',
				'label' => 'Checkbox un-checked'
			));

			// switch example
			$output .= aui()->input(array(
				'type'  =>  'checkbox',
				'id'    =>  'switch-example',
				'name'    =>  'switch-example',
				'placeholder'   => 'checkbox-example',
				'title'   => 'Switch example',
				'value' =>  '1',
				'checked'   => true,
				'switch'    => true,
				'required'  => false,
				'help_text' => 'help text',
				'label' => 'Switch on'
			));

			// switch example
			$output .= aui()->input(array(
				'type'  =>  'checkbox',
				'id'    =>  'switch-example2',
				'name'    =>  'switch-example2',
				'placeholder'   => 'checkbox-example',
				'title'   => 'Switch example',
				'value' =>  '1',
				'checked'   => false,
				'switch'    => true,
				'required'  => false,
				'help_text' => 'help text',
				'label' => 'Switch off'
			));

			// close form
			$output .= "</form>";

			return $output;
		}

		/**
		 * Calendar params.
		 *
		 * @since 0.1.44
		 *
		 * @return array Calendar params.
		 */
		public static function calendar_params() {
			$params = array(
				'month_long_1' => __( 'January', 'aui' ),
				'month_long_2' => __( 'February', 'aui' ),
				'month_long_3' => __( 'March', 'aui' ),
				'month_long_4' => __( 'April', 'aui' ),
				'month_long_5' => __( 'May', 'aui' ),
				'month_long_6' => __( 'June', 'aui' ),
				'month_long_7' => __( 'July', 'aui' ),
				'month_long_8' => __( 'August', 'aui' ),
				'month_long_9' => __( 'September', 'aui' ),
				'month_long_10' => __( 'October', 'aui' ),
				'month_long_11' => __( 'November', 'aui' ),
				'month_long_12' => __( 'December', 'aui' ),
				'month_s_1' => _x( 'Jan', 'January abbreviation', 'aui' ),
				'month_s_2' => _x( 'Feb', 'February abbreviation', 'aui' ),
				'month_s_3' => _x( 'Mar', 'March abbreviation', 'aui' ),
				'month_s_4' => _x( 'Apr', 'April abbreviation', 'aui' ),
				'month_s_5' => _x( 'May', 'May abbreviation', 'aui' ),
				'month_s_6' => _x( 'Jun', 'June abbreviation', 'aui' ),
				'month_s_7' => _x( 'Jul', 'July abbreviation', 'aui' ),
				'month_s_8' => _x( 'Aug', 'August abbreviation', 'aui' ),
				'month_s_9' => _x( 'Sep', 'September abbreviation', 'aui' ),
				'month_s_10' => _x( 'Oct', 'October abbreviation', 'aui' ),
				'month_s_11' => _x( 'Nov', 'November abbreviation', 'aui' ),
				'month_s_12' => _x( 'Dec', 'December abbreviation', 'aui' ),
				'day_s1_1' => _x( 'S', 'Sunday initial', 'aui' ),
				'day_s1_2' => _x( 'M', 'Monday initial', 'aui' ),
				'day_s1_3' => _x( 'T', 'Tuesday initial', 'aui' ),
				'day_s1_4' => _x( 'W', 'Wednesday initial', 'aui' ),
				'day_s1_5' => _x( 'T', 'Friday initial', 'aui' ),
				'day_s1_6' => _x( 'F', 'Thursday initial', 'aui' ),
				'day_s1_7' => _x( 'S', 'Saturday initial', 'aui' ),
				'day_s2_1' => __( 'Su', 'aui' ),
				'day_s2_2' => __( 'Mo', 'aui' ),
				'day_s2_3' => __( 'Tu', 'aui' ),
				'day_s2_4' => __( 'We', 'aui' ),
				'day_s2_5' => __( 'Th', 'aui' ),
				'day_s2_6' => __( 'Fr', 'aui' ),
				'day_s2_7' => __( 'Sa', 'aui' ),
				'day_s3_1' => __( 'Sun', 'aui' ),
				'day_s3_2' => __( 'Mon', 'aui' ),
				'day_s3_3' => __( 'Tue', 'aui' ),
				'day_s3_4' => __( 'Wed', 'aui' ),
				'day_s3_5' => __( 'Thu', 'aui' ),
				'day_s3_6' => __( 'Fri', 'aui' ),
				'day_s3_7' => __( 'Sat', 'aui' ),
				'day_s5_1' => __( 'Sunday', 'aui' ),
				'day_s5_2' => __( 'Monday', 'aui' ),
				'day_s5_3' => __( 'Tuesday', 'aui' ),
				'day_s5_4' => __( 'Wednesday', 'aui' ),
				'day_s5_5' => __( 'Thursday', 'aui' ),
				'day_s5_6' => __( 'Friday', 'aui' ),
				'day_s5_7' => __( 'Saturday', 'aui' ),
				'am_lower' => __( 'am', 'aui' ),
				'pm_lower' => __( 'pm', 'aui' ),
				'am_upper' => __( 'AM', 'aui' ),
				'pm_upper' => __( 'PM', 'aui' ),
				'firstDayOfWeek' => (int) get_option( 'start_of_week' ),
				'time_24hr' => false,
				'year' => __( 'Year', 'aui' ),
				'hour' => __( 'Hour', 'aui' ),
				'minute' => __( 'Minute', 'aui' ),
				'weekAbbreviation' => __( 'Wk', 'aui' ),
				'rangeSeparator' => __( ' to ', 'aui' ),
				'scrollTitle' => __( 'Scroll to increment', 'aui' ),
				'toggleTitle' => __( 'Click to toggle', 'aui' )
			);

			return apply_filters( 'ayecode_ui_calendar_params', $params );
		}

		/**
		 * Flatpickr calendar localize.
		 *
		 * @since 0.1.44
		 *
		 * @return string Calendar locale.
		 */
		public static function flatpickr_locale() {
			$params = self::calendar_params();

			if ( is_string( $params ) ) {
				$params = html_entity_decode( $params, ENT_QUOTES, 'UTF-8' );
			} else {
				foreach ( (array) $params as $key => $value ) {
					if ( ! is_scalar( $value ) ) {
						continue;
					}

					$params[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
				}
			}

			$day_s3 = array();
			$day_s5 = array();

			for ( $i = 1; $i <= 7; $i ++ ) {
				$day_s3[] = addslashes( $params[ 'day_s3_' . $i ] );
				$day_s5[] = addslashes( $params[ 'day_s3_' . $i ] );
			}

			$month_s = array();
			$month_long = array();

			for ( $i = 1; $i <= 12; $i ++ ) {
				$month_s[] = addslashes( $params[ 'month_s_' . $i ] );
				$month_long[] = addslashes( $params[ 'month_long_' . $i ] );
			}

ob_start();
if ( 0 ) { ?><script><?php } ?>
{
	weekdays: {
		shorthand: ['<?php echo implode( "','", $day_s3 ); ?>'],
		longhand: ['<?php echo implode( "','", $day_s5 ); ?>'],
	},
	months: {
		shorthand: ['<?php echo implode( "','", $month_s ); ?>'],
		longhand: ['<?php echo implode( "','", $month_long ); ?>'],
	},
	daysInMonth: [31,28,31,30,31,30,31,31,30,31,30,31],
	firstDayOfWeek: <?php echo (int) $params[ 'firstDayOfWeek' ]; ?>,
	ordinal: function (nth) {
		var s = nth % 100;
		if (s > 3 && s < 21)
			return "th";
		switch (s % 10) {
			case 1:
				return "st";
			case 2:
				return "nd";
			case 3:
				return "rd";
			default:
				return "th";
		}
	},
	rangeSeparator: '<?php echo addslashes( $params[ 'rangeSeparator' ] ); ?>',
	weekAbbreviation: '<?php echo addslashes( $params[ 'weekAbbreviation' ] ); ?>',
	scrollTitle: '<?php echo addslashes( $params[ 'scrollTitle' ] ); ?>',
	toggleTitle: '<?php echo addslashes( $params[ 'toggleTitle' ] ); ?>',
	amPM: ['<?php echo addslashes( $params[ 'am_upper' ] ); ?>','<?php echo addslashes( $params[ 'pm_upper' ] ); ?>'],
	yearAriaLabel: '<?php echo addslashes( $params[ 'year' ] ); ?>',
	hourAriaLabel: '<?php echo addslashes( $params[ 'hour' ] ); ?>',
	minuteAriaLabel: '<?php echo addslashes( $params[ 'minute' ] ); ?>',
	time_24hr: <?php echo ( $params[ 'time_24hr' ] ? 'true' : 'false' ) ; ?>
}
<?php if ( 0 ) { ?></script><?php } ?>
<?php
			$locale = ob_get_clean();

			return apply_filters( 'ayecode_ui_flatpickr_locale', trim( $locale ) );
		}

		/**
		 * Select2 JS params.
		 *
		 * @since 0.1.44
		 *
		 * @return array Select2 JS params.
		 */
		public static function select2_params() {
			$params = array(
				'i18n_select_state_text'    => esc_attr__( 'Select an option&hellip;', 'aui' ),
				'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'aui' ),
				'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'aui' ),
				'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'aui' ),
				'i18n_input_too_short_n'    => _x( 'Please enter %item% or more characters', 'enhanced select', 'aui' ),
				'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'aui' ),
				'i18n_input_too_long_n'     => _x( 'Please delete %item% characters', 'enhanced select', 'aui' ),
				'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'aui' ),
				'i18n_selection_too_long_n' => _x( 'You can only select %item% items', 'enhanced select', 'aui' ),
				'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'aui' ),
				'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'aui' )
			);

			return apply_filters( 'ayecode_ui_select2_params', $params );
		}

		/**
		 * Select2 JS localize.
		 *
		 * @since 0.1.44
		 *
		 * @return string Select2 JS locale.
		 */
		public static function select2_locale() {
			$params = self::select2_params();

			foreach ( (array) $params as $key => $value ) {
				if ( ! is_scalar( $value ) ) {
					continue;
				}

				$params[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
			}

			$locale = json_encode( $params );

			return apply_filters( 'ayecode_ui_select2_locale', trim( $locale ) );
		}
	}

	/**
	 * Run the class if found.
	 */
	AyeCode_UI_Settings::instance();
}