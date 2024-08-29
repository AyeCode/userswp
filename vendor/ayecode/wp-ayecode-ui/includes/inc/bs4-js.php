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
        var aui_select2_params = <?php echo self::select2_locale(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;

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
        jQuery("select.aui-select2").each(function() {
            if (!jQuery(this).hasClass("select2-hidden-accessible")) {
                jQuery(this).select2(select2_args);
            }
        });
    }

    /**
     * A function to convert a time value to a "ago" time text.
     *
     * @param selector string The .class selector
     */
    function aui_time_ago(selector) {
        var aui_timeago_params = <?php echo self::timeago_locale(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;

        var templates = {
            prefix: aui_timeago_params.prefix_ago,
            suffix: aui_timeago_params.suffix_ago,
            seconds: aui_timeago_params.seconds,
            minute: aui_timeago_params.minute,
            minutes: aui_timeago_params.minutes,
            hour: aui_timeago_params.hour,
            hours: aui_timeago_params.hours,
            day: aui_timeago_params.day,
            days: aui_timeago_params.days,
            month: aui_timeago_params.month,
            months: aui_timeago_params.months,
            year: aui_timeago_params.year,
            years: aui_timeago_params.years
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
			<?php if ( ! empty( $flatpickr_locale ) ) { ?>try{flatpickr.localize(<?php echo $flatpickr_locale; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>);}catch(err){console.log(err.message);}<?php } ?>
            jQuery('input[data-aui-init="flatpickr"]:not(.flatpickr-input)').flatpickr();
        }
        $aui_doing_init_flatpickr = false;
    }

    /**
     * Initiate iconpicker on the page.
     */
    $aui_doing_init_iconpicker = false;
    function aui_init_iconpicker(){
        if ( typeof jQuery.fn.iconpicker === "function" && !$aui_doing_init_iconpicker) {
            $aui_doing_init_iconpicker = true;
            jQuery('input[data-aui-init="iconpicker"]:not(.iconpicker-input)').iconpicker();
        }
        $aui_doing_init_iconpicker= false;
    }

    function aui_modal_iframe($title,$url,$footer,$dismissible,$class,$dialog_class,$body_class,responsive){
        if(!$body_class){$body_class = 'p-0';}
        var wClass = 'text-center position-absolute w-100 text-dark overlay overlay-white p-0 m-0 d-none d-flex justify-content-center align-items-center';
        var $body = "", sClass = "w-100 p-0 m-0";
        if (responsive) {
            $body += '<div class="embed-responsive embed-responsive-16by9">';
            wClass += ' h-100';
            sClass += ' embed-responsive-item';
        } else {
            wClass += ' vh-100';
            sClass += ' vh-100';
        }
        $body += '<div class="ac-preview-loading ' + wClass + '" style="left:0;top:0"><div class="spinner-border" role="status"></div></div>';
        $body += '<iframe id="embedModal-iframe" class="' + sClass + '" src="" width="100%" height="100%" frameborder="0" allowtransparency="true"></iframe>';
        if (responsive) {
            $body += '</div>';
        }

        $m = aui_modal($title,$body,$footer,$dismissible,$class,$dialog_class,$body_class);
        jQuery( $m ).on( 'shown.bs.modal', function ( e ) {
            iFrame = jQuery( '#embedModal-iframe') ;

            jQuery('.ac-preview-loading').addClass('d-flex');
            iFrame.attr({
                src: $url
            });

            //resize the iframe once loaded.
            iFrame.load(function() {
                jQuery('.ac-preview-loading').removeClass('d-flex');
            });
        });

        return $m;

    }

    function aui_modal($title,$body,$footer,$dismissible,$class,$dialog_class,$body_class) {
        if(!$class){$class = '';}
        if(!$dialog_class){$dialog_class = '';}
        if(!$body){$body = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';}
        // remove it first
        jQuery('.aui-modal').modal('hide').modal('dispose').remove();
        jQuery('.modal-backdrop').remove();

        var $modal = '';

        $modal += '<div class="modal aui-modal fade shadow bsui '+$class+'" tabindex="-1">'+
            '<div class="modal-dialog modal-dialog-centered '+$dialog_class+'">'+
            '<div class="modal-content border-0 shadow">';

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
        $modal += '<div class="modal-body '+$body_class+'">'+
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

        return jQuery('.aui-modal').modal('hide').modal({
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

        var $modal = '<div class="modal fade aui-carousel-modal bsui" tabindex="-1" role="dialog" aria-labelledby="aui-modal-title" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-xl mw-100"><div class="modal-content bg-transparent border-0 shadow-none"><div class="modal-header"><h5 class="modal-title" id="aui-modal-title"></h5></div><div class="modal-body text-center"><i class="fas fa-circle-notch fa-spin fa-3x"></i></div></div></div></div>';
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
            $carousel += '<div class="carousel-inner">';
            $container.find('.aui-lightbox-image').each(function() {
                var a = this;
                var href = jQuery(a).attr('href');

                $active = $clicked_href == jQuery(this).attr('href') ? 'active' : '';
                $carousel += '<div class="carousel-item '+ $active+'"><div>';

                // image
                var css_height = window.innerWidth > window.innerHeight ? '90vh' : 'auto';
                var img = href ? jQuery(a).find('img').clone().attr('src', href ).attr('sizes', '').removeClass().addClass('mx-auto d-block w-auto mw-100 rounded').css('max-height',css_height).get(0).outerHTML : jQuery(a).find('img').clone().removeClass().addClass('mx-auto d-block w-auto mw-100 rounded').css('max-height',css_height).get(0).outerHTML;
                $carousel += img;
                // captions
                if(jQuery(a).parent().find('.carousel-caption').length ){
                    $carousel += jQuery(a).parent().find('.carousel-caption').clone().removeClass('sr-only').get(0).outerHTML;
                }else if(jQuery(a).parent().find('.figure-caption').length ){
                    $carousel += jQuery(a).parent().find('.figure-caption').clone().removeClass('sr-only').addClass('carousel-caption').get(0).outerHTML;
                }
                $carousel += '</div></div>';
                $i++;
            });

            $container.find('.aui-lightbox-iframe').each(function() {
                var a = this;

                $active = $clicked_href == jQuery(this).attr('href') ? 'active' : '';
                $carousel += '<div class="carousel-item '+ $active+'"><div class="modal-xl mx-auto embed-responsive embed-responsive-16by9">';

                // iframe
                var css_height = window.innerWidth > window.innerHeight ? '95vh' : 'auto';
                var url = jQuery(a).attr('href');
                var iframe = '<iframe class="embed-responsive-item" style="height:'+css_height +'" src="'+url+'?rel=0&amp;showinfo=0&amp;modestbranding=1&amp;autoplay=1" id="video" allow="autoplay"></iframe>';
                var img = iframe ;//.css('height',css_height).get(0).outerHTML;
                $carousel += img;

                $carousel += '</div></div>';
                $i++;
            });
            $carousel += '</div>';

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
        jQuery('.aui-lightbox-image, .aui-lightbox-iframe').off('click').on("click",function(ele) {
            aui_lightbox_embed(this,ele);
        });
    }

    /**
     * Init modal iframe.
     */
    function aui_init_modal_iframe() {
        jQuery('.aui-has-embed, [data-aui-embed="iframe"]').each(function(e){
            if (!jQuery(this).hasClass('aui-modal-iframed') && jQuery(this).data('embed-url')) {
                jQuery(this).addClass('aui-modal-iframed');

                jQuery(this).on("click",function(e1) {
                    aui_modal_iframe('',jQuery(this).data('embed-url'),'',true,'','modal-lg','aui-modal-iframe p-0',true);
                    return false;
                });
            }
        });
    }

    /**
     * Show a toast.
     */
    $aui_doing_toast = false;
    function aui_toast($id,$type,$title,$title_small,$body,$time,$can_close){

        if($aui_doing_toast){setTimeout(function(){
            aui_toast($id,$type,$title,$title_small,$body,$time,$can_close);
        }, 500); return;}

        $aui_doing_toast = true;

        if($can_close == null){$can_close = false;}
        if($time == '' || $time == null ){$time = 3000;}

        // if already setup then just show
        if(document.getElementById($id)){
            jQuery('#'+$id).toast('show');
            setTimeout(function(){ $aui_doing_toast = false; }, 500);
            return;
        }

        var uniqid = Date.now();
        if($id){
            uniqid = $id;
        }

        $op = "";
        $tClass = '';
        $thClass = '';
        $icon = "";

        if ($type == 'success') {
            $op = "opacity:.92;";
            $tClass = 'alert alert-success';
            $thClass = 'bg-transparent border-0 alert-success';
            $icon = "<div class='h5 m-0 p-0'><i class='fas fa-check-circle mr-2'></i></div>";
        } else if ($type == 'error' || $type == 'danger') {
            $op = "opacity:.92;";
            $tClass = 'alert alert-danger';
            $thClass = 'bg-transparent border-0 alert-danger';
            $icon = "<div class='h5 m-0 p-0'><i class='far fa-times-circle mr-2'></i></div>";
        } else if ($type == 'info') {
            $op = "opacity:.92;";
            $tClass = 'alert alert-info';
            $thClass = 'bg-transparent border-0 alert-info';
            $icon = "<div class='h5 m-0 p-0'><i class='fas fa-info-circle mr-2'></i></div>";
        } else if ($type == 'warning') {
            $op = "opacity:.92;";
            $tClass = 'alert alert-warning';
            $thClass = 'bg-transparent border-0 alert-warning';
            $icon = "<div class='h5 m-0 p-0'><i class='fas fa-exclamation-triangle mr-2'></i></div>";
        }


        // add container if not exist
        if(!document.getElementById("aui-toasts")){
            jQuery('body').append('<div class="bsui" id="aui-toasts"><div class="position-fixed aui-toast-bottom-right pr-3 mb-1" style="z-index: 500000;right: 0;bottom: 0;'+$op+'"></div></div>');
        }

        $toast = '<div id="'+uniqid+'" class="toast fade hide shadow hover-shadow '+$tClass+'" style="" role="alert" aria-live="assertive" aria-atomic="true" data-delay="'+$time+'">';
        if($type || $title || $title_small){
            $toast += '<div class="toast-header '+$thClass+'">';
            if($icon ){$toast += $icon;}
            if($title){$toast += '<strong class="mr-auto">'+$title+'</strong>';}
            if($title_small){$toast += '<small>'+$title_small+'</small>';}
            if($can_close){$toast += '<button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close"><span aria-hidden="true">×</span></button>';}
            $toast += '</div>';
        }

        if($body){
            $toast += '<div class="toast-body">'+$body+'</div>';
        }

        $toast += '</div>';

        jQuery('.aui-toast-bottom-right').prepend($toast);
        jQuery('#'+uniqid).toast('show');
        setTimeout(function(){ $aui_doing_toast = false; }, 500);
    }

    /**
     * Animate a number.
     */
    function aui_init_counters(){

        const animNum = (EL) => {

            if (EL._isAnimated) return; // Animate only once!
            EL._isAnimated = true;

            let end = EL.dataset.auiend;
            let start = EL.dataset.auistart;
            let duration = EL.dataset.auiduration ? EL.dataset.auiduration : 2000;
            let seperator = EL.dataset.auisep ? EL.dataset.auisep: '';

            jQuery(EL).prop('Counter', start).animate({
                Counter: end
            }, {
                duration: Math.abs(duration),
                easing: 'swing',
                step: function(now) {
                    const text = seperator ?  (Math.ceil(now)).toLocaleString('en-US') : Math.ceil(now);
                    const html = seperator ? text.split(",").map(n => `<span class="count">${n}</span>`).join(",") : text;
                    if(seperator && seperator!=','){
                        html.replace(',',seperator);
                    }
                    jQuery(this).html(html);
                }
            });
        };

        const inViewport = (entries, observer) => {
            // alert(1);
            entries.forEach(entry => {
                if (entry.isIntersecting) animNum(entry.target);
            });
        };

        jQuery("[data-auicounter]").each((i, EL) => {
            const observer = new IntersectionObserver(inViewport);
            observer.observe(EL);
        });
    }


    /**
     * Initiate all AUI JS.
     */
    function aui_init(){

        // init counters
        aui_init_counters();

        // nav menu submenus
        init_nav_sub_menus();

        // init tooltips
        aui_init_tooltips();

        // init select2
        aui_init_select2();

        // init flatpickr
        aui_init_flatpickr();

        // init iconpicker
        aui_init_iconpicker();

        // init Greedy nav
        aui_init_greedy_nav();

        // Set times to time ago
        aui_time_ago('timeago');

        // init multiple item carousels
        aui_init_carousel_multiple_items();

        // init lightbox embeds
        aui_init_lightbox_embed();

        /* Init modal iframe */
        aui_init_modal_iframe();
    }

    // run on window loaded
    jQuery(window).on("load",function() {
        aui_init();
    });

    /* Fix modal background scroll on iOS mobile device */
    jQuery(function($) {
        var ua = navigator.userAgent.toLowerCase();
        var isiOS = ua.match(/(iphone|ipod|ipad)/);
        if (isiOS) {
            var pS = 0; pM = parseFloat($('body').css('marginTop'));

            $(document).on('show.bs.modal', function() {
                pS = window.scrollY;
                $('body').css({
                    marginTop: -pS,
                    overflow: 'hidden',
                    position: 'fixed',
                });
            }).on('hidden.bs.modal', function() {
                $('body').css({
                    marginTop: pM,
                    overflow: 'visible',
                    position: 'inherit',
                });
                window.scrollTo(0, pS);
            });
        }
    });

    /**
     * Show a "confirm" dialog to the user (using jQuery UI's dialog)
     *
     * @param {string} message The message to display to the user
     * @param {string} okButtonText OPTIONAL - The OK button text, defaults to "Yes"
     * @param {string} cancelButtonText OPTIONAL - The Cancel button text, defaults to "No"
     * @returns {Q.Promise<boolean>} A promise of a boolean value
     */
    var aui_confirm = function (message, okButtonText, cancelButtonText, isDelete, large ) {
        okButtonText = okButtonText || 'Yes';
        cancelButtonText = cancelButtonText || 'Cancel';
        message = message || 'Are you sure?';
        sizeClass = large ? '' : 'modal-sm';
        btnClass = isDelete ? 'btn-danger' : 'btn-primary';

        deferred = jQuery.Deferred();
        var $body = "";
        $body += "<h3 class='h4 py-3 text-center text-dark'>"+message+"</h3>";
        $body += "<div class='d-flex'>";
        $body += "<button class='btn btn-outline-secondary w-50 btn-round' data-dismiss='modal'  onclick='deferred.resolve(false);'>"+cancelButtonText+"</button>";
        $body += "<button class='btn "+btnClass+" ml-2 w-50 btn-round' data-dismiss='modal'  onclick='deferred.resolve(true);'>"+okButtonText+"</button>";
        $body += "</div>";
        $modal = aui_modal('',$body,'',false,'',sizeClass);

        return deferred.promise();
    };

    /**
     * Flip the color scheem on scroll
     * @param $value
     * @param $iframe
     */
    function aui_flip_color_scheme_on_scroll($value, $iframe){
        if(!$value) $value = window.scrollY;
        var navbar = $iframe ?  $iframe.querySelector('.color-scheme-flip-on-scroll') : document.querySelector('.color-scheme-flip-on-scroll');
        if (navbar == null) return;

        let cs_original = navbar.dataset.cso;
        let cs_scroll = navbar.dataset.css;

        if (!cs_scroll && !cs_original) {
            if( navbar.classList.contains('navbar-light') ){
                cs_original = 'navbar-light';
                cs_scroll  = 'navbar-dark';
            }else if( navbar.classList.contains('navbar-dark') ){
                cs_original = 'navbar-dark';
                cs_scroll  = 'navbar-light';
            }

            navbar.dataset.cso = cs_original;
            navbar.dataset.css = cs_scroll;
        }

        if($value > 0 ){
            navbar.classList.remove(cs_original);
            navbar.classList.add(cs_scroll);
        }else{
            navbar.classList.remove(cs_scroll);
            navbar.classList.add(cs_original);
        }
    }

    /**
     * Add a window scrolled data element.
     */
    window.onscroll = function () {
        aui_set_data_scroll()
    };

    /**
     * Set scroll data element.
     */
    function aui_set_data_scroll(){
        document.documentElement.dataset.scroll = window.scrollY;
    }

    // call data scroll function ASAP.
    aui_set_data_scroll();
    aui_flip_color_scheme_on_scroll();

	<?php
	// FSE tweaks.
	if(!empty($_REQUEST['postType']) && $_REQUEST['postType']=='wp_template'){ ?>
    function aui_fse_set_data_scroll() {
        console.log('init scroll');
        let Iframe = document.getElementsByClassName("edit-site-visual-editor__editor-canvas");
        if( Iframe[0] === undefined ){ return; }
        let iframe_doc = Iframe[0].contentWindow ? Iframe[0].contentWindow.document : Iframe[0].contentDocument;
        Iframe[0].contentWindow.onscroll = function () {
            iframe_doc.documentElement.dataset.scroll = Iframe[0].contentWindow.scrollY;
            aui_flip_color_scheme_on_scroll(Iframe[0].contentWindow.scrollY,iframe_doc);
        };
    }

    setTimeout(function(){
        aui_fse_set_data_scroll();
    }, 3000);

    // fire when URL changes also.
    let FSElastUrl = location.href;
    new MutationObserver(() => {
        const url = location.href;
        if (url !== FSElastUrl) {
            FSElastUrl = url;
            aui_fse_set_data_scroll();
            // fire a second time incase of load delays.
            setTimeout(function(){
                aui_fse_set_data_scroll();
            }, 2000);
        }
    }).observe(document, {subtree: true, childList: true});
	<?php } ?>


</script>
