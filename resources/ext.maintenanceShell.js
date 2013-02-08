( function ( $ ) {

	function err( $shell, errorMsg ) {
		$shell.append(
			$('<p><b>&gt;</b> </p>' )
				.addClass( 'mw-sp-maintenanceShell-shell-error' )
				.append( document.createTextNode( errorMsg ) )
		);
	}

	function init( $pageWrap ) {
		$pageWrap
		.find( '.mw-sp-maintenanceShell-controlfield' )
			.val( '1' )
			.end()
		.find( 'select[name="wpScript"]' )
			.prop( 'required', true )
			.end()
		.find( 'form' )
			.on( 'submit', function ( e ) {
				// We're going the ajax way!
				e.preventDefault();

				// Get data
				var $spinner,
					$tmpWrap,
					$form = $( this ),
					postData = $form.serialize(),
					$inputs = $form.find( ':input' ), // select, input, textarea, button 
					$wrap = $form.closest( '.mw-sp-maintenanceShell' ),
					$shell = $wrap.find( '.mw-sp-maintenanceShell-shell' ).empty();
				if ( !$shell.length ) {
					$shell = $( '<div>' ).addClass( 'mw-sp-maintenanceShell-shell' ).appendTo( $wrap );
				}
				// Remove stale errors from $tmpWrap
				$wrap.find( '.error' ).remove();

				// Disable form and show spinner
				$inputs.prop( 'disabled', true ); // Could store original state, but we don't have disabled fields..
				$spinner = $.createSpinner({ size: 'large', type: 'block' });
				$form.find( 'fieldset' ).eq( 0 ).append( $spinner );

				// So far the only known restriction/limitation: --wiki doens't work
				// (which could be, but is purposely not supported. If you run a
				// wiki farm, you're either expected to have command line access or
				// just access this SpecialPage from the correct wiki)
				if ( postData.indexOf( '--wiki' ) !== -1 ) {
					err( $shell, 'Usage of the --wiki option is not allowed by MaintenanceShell.' );
					$inputs.prop( 'disabled', false );
					$spinner.remove();
					return;
				}

				// Submission
				$.ajax( {
						type: $form.attr( 'method' ) || 'POST',
						url:  $form.attr( 'action' ) || '',
						data: postData,
						cache: false,
						dataType: 'text'
					} )
					.done( function ( data ) {
						// In case of error, the server will respond with a full html page.
						// Extract the SpecialPage wrapper, and replace our current one to show the error.
						// Then re-run our init handlers on the wrapper.
						if ( data.indexOf( 'mw-sp-maintenanceShell' ) !== -1 || data.indexOf( 'controlfield' ) !== -1 ) {
							$tmpWrap = $(data).find( '.mw-sp-maintenanceShell' ).eq( 0 );
							$wrap.replaceWith( $tmpWrap );
							init( $tmpWrap );
						} else {
							$shell.text( data );
						}
					} )
					.fail( function ( jqXHR, textStatus, errorThrown ) {
						err( $shell, errorThrown || 'Request failed.' );
					} )
					.complete( function () {
						// Re-enable form and hide spinner
						$inputs.prop( 'disabled', false );
						$spinner.remove();
					} );

			} );
	}

	// Kick it off
	$( function () {
		init( $( '.mw-sp-maintenanceShell' ) );
	} );

}( jQuery ) );
