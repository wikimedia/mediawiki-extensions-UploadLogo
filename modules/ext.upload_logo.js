$( function () {
	$( '.check-image' ).on( 'click', function () {
		$( '#selected-logo' ).val( $( this ).val() );
	} );

	if ( $( '#logo-file' ).val() ) {
		// $( '#logo-file' ).attr( 'id' ).attr( 'checked', true );
		// $( '#logo-file' ).attr( 'id' ).parent().css( 'background-color', '#fee' );
	}

	var conf = mw.config.get( [ 'wgServer', 'wgScript' ] );

	$( '#change-logo' ).on( 'click', function () {
		var img = $( '.check-image:checked' ).val();
		$.post( conf.wgServer + conf.wgScript, { title: 'Special:Upload_Logo', selected: encodeURIComponent( img ) }, function ( data ) {
			if ( data.result === 'success' ) {
				$( '#p-logo a' ).css( 'background-image', 'none' ).css( 'background-image', 'url( /images/logo/' + data.logofile + ')' );
			}
		}, 'json' );
	} );

	$( '#delete-logo' ).on( 'click', function () {
		var img = $( '.check-image:checked' ).val();
		$.post( conf.wgServer + conf.wgScript, { title: 'Special:Upload_Logo', delete: encodeURIComponent( img ) }, function ( data ) {
			if ( data.result === 'success' ) {
				$( '.check-image:checked' ).parent().remove();
			}
		}, 'json' );
		/*
		var request = new Json.Remote( wgServer + wgScript + '?title=Special:Upload_Logo&delete=' + encodeURIComponent( selectedFile ), {
			method: 'get',
			onComplete: function ( result ) {
				if ( result.deleted ) {
					$$( '.check-image' ).each( function ( another2 ) {
						another2.setProperty( 'disabled', false );
						another2.getParent().setStyle( 'background-color', '#fff' );
					} );
					$( selectedFile ).getParent().remove();
				}
			}
		} ).send(); */
	} );
} );
