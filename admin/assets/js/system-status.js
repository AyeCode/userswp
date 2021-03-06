jQuery( function ( $ ) {

	var uwpSystemStatus = {
		init: function() {
			$( document.body )
				.on( 'click', 'a.debug-report', this.generateReport )
				.on( 'click', '#copy-for-support', this.copyReport )
				.on( 'aftercopy', '#copy-for-support', this.copySuccess )
		},

		/**
		 * Generate system status report.
		 *
		 * @return {Bool}
		 */
		generateReport: function() {
			var report = '';

			$( '.uwp-status-table thead, .uwp-status-table tbody' ).each( function() {
				if ( $( this ).is( 'thead' ) ) {
					var label = $( this ).find( 'th:eq(0)' ).data( 'export-label' ) || $( this ).text();
					report = report + '\n### ' + $.trim( label ) + ' ###\n\n';
				} else {
					$( 'tr', $( this ) ).each( function() {
						var label       = $( this ).find( 'td:eq(0)' ).data( 'export-label' ) || $( this ).find( 'td:eq(0)' ).text();
						var the_name    = $.trim( label ).replace( /(<([^>]+)>)/ig, '' ); // Remove HTML.

						// Find value
						var $value_html = $( this ).find( 'td:eq(1)' ).clone();
						$value_html.find( '.private' ).remove();
						$value_html.find( '.dashicons-yes' ).replaceWith( '&#10004;' );
						$value_html.find( '.dashicons-no-alt, .dashicons-warning' ).replaceWith( '&#10060;' );

						// Format value
						var the_value   = $.trim( $value_html.text() );
						var value_array = the_value.split( ', ' );

						if ( value_array.length > 1 ) {
							// If value have a list of plugins ','.
							// Split to add new line.
							var temp_line ='';
							$.each( value_array, function( key, line ) {
								temp_line = temp_line + line + '\n';
							});

							the_value = temp_line;
						}

						report = report + '' + the_name + ': ' + the_value + '\n';
					});
				}
			});

			try {
				$( '#debug-report' ).slideDown();
				$( '#debug-report' ).find( 'textarea' ).val( '`' + report + '`' ).focus().select();
				$( this ).fadeOut();
				return false;
			} catch ( e ) {
				console.log( e );
			}

			return false;
		},

		/**
		 * Copy for report.
		 *
		 * @param {Object} evt Copy event.
		 */
		copyReport: function( evt ) {
			uwpClearClipboard();
			uwpSetClipboard( $( '#debug-report' ).find( 'textarea' ).val(), $( this ) );
			evt.preventDefault();
		},

		/**
		 * Display a "Copied!" alert when success copying
		 */
		copySuccess: function() {
			alert('copied!');
		},

		/**
		 * Displays the copy error message when failure copying.
		 */
		copyFail: function() {
			$( '.copy-error' ).removeClass( 'hidden' );
			$( '#debug-report' ).find( 'textarea' ).focus().select();
		}
	};

	uwpSystemStatus.init();

    function uwpSetClipboard( data, $el ) {
        if ( 'undefined' === typeof $el ) {
            $el = jQuery( document );
        }
        var $temp_input = jQuery( '<textarea style="opacity:0">' );
        jQuery( 'body' ).append( $temp_input );
        $temp_input.val( data ).select();

        $el.trigger( 'beforecopy' );
        try {
            document.execCommand( 'copy' );
            $el.trigger( 'aftercopy' );
        } catch ( err ) {
            $el.trigger( 'aftercopyfailure' );
        }

        $temp_input.remove();
    }

    function uwpClearClipboard() {
        uwpSetClipboard( '' );
    }
});