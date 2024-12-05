/* global ang_settings_data, wp */
( function( $, data, wp ) {
	$( function() {
		const { __ } = wp.i18n;
		const { addQueryArgs } = wp.url;

		// Process Newsletter.
		function processNewsletter( e ) {
			if ( e.preventDefault ) {
				e.preventDefault();
			}
			const elSubmitBtn = $( '#analog-custom-library-newsletter-submit' );
			let status = __( 'Subscribing', 'custom-library-for-elementor' );
			const angEmail = $( '#analog-custom-library-newsletter-email' ).val();
			elSubmitBtn.text( status );

			$.ajax( {
				url: 'https://analogwp.com/?ang-api=analog-custom-library&request=earlybird-optin',
				cache: ! 1,
				type: 'POST',
				dataType: 'JSON',
				data: {
					email: angEmail,
				},
				error: function() {
					status = __( 'Failed', 'custom-library-for-elementor' );
					elSubmitBtn.text( status );
					setTimeout( function() {
						elSubmitBtn.text( __( 'Subscribe up to newsletter', 'custom-library-for-elementor' ) );
					}, 2000 );
				},
				success: function() {
					status = __( 'Subscribed', 'custom-library-for-elementor' );
					elSubmitBtn.text( status );
					elSubmitBtn.attr( 'disabled', 'disabled' );
				},
			} );

			return false;
		}
		$( '#analog-custom-library-newsletter' ).submit( processNewsletter );

		// Process Plugin Rollback.
		function processPluginRollback( e ) {
			if ( e.preventDefault ) {
				e.preventDefault();
			}

			const version = $( '#ang_rollback_version_select_option' ).val();
			const rollbackUrl = addQueryArgs( data.rollback_url, { version: version } );

			window.location.href = rollbackUrl;
			return false;
		}
		$( '#ang_rollback_version_button' ).on( 'click', processPluginRollback );

		// Edit prompt
		$( function() {
			let changed = false;

			$( 'input, textarea, select, checkbox' ).change( function() {
				changed = true;
			} );

			$( '.analog-custom-library-nav-tab-wrapper a' ).click( function() {
				if ( changed ) {
					window.onbeforeunload = function() {
						return data.i18n_nav_warning;
					};
				} else {
					window.onbeforeunload = '';
				}
			} );

			$( '.submit :input' ).click( function() {
				window.onbeforeunload = '';
			} );
		} );

		// Select all/none
		$( '.ang-custom-library' ).on( 'click', '.select_all', function() {
			$( this )
				.closest( 'td' )
				.find( 'select option' )
				.attr( 'selected', 'selected' );
			$( this )
				.closest( 'td' )
				.find( 'select' )
				.trigger( 'change' );
			return false;
		} );

		$( '.ang-custom-library' ).on( 'click', '.select_none', function() {
			$( this )
				.closest( 'td' )
				.find( 'select option' )
				.removeAttr( 'selected' );
			$( this )
				.closest( 'td' )
				.find( 'select' )
				.trigger( 'change' );
			return false;
		} );

		const collBtn = document.getElementsByClassName( 'collapsible' );
		let i;

		for ( i = 0; i < collBtn.length; i++ ) {
			collBtn[ i ].addEventListener( 'click', function( e ) {
				e.preventDefault();
				this.classList.toggle( 'active' );
				const content = this.nextElementSibling;
				if ( content.style.maxHeight ) {
					content.style.maxHeight = null;
				} else {
					content.style.maxHeight = content.scrollHeight + 'px';
				}
			} );
			if ( i === 0 ) {
				$( collBtn[ i ] ).trigger( 'click' );
			}
		}

		$( 'body' ).on(
			'click',
			'.analog-custom-library-upload-image-btn',
			function (e) {
				e.preventDefault();
				const button     = $( this ),
					customUploader = wp.media(
						{
							title: data.uploader_title,
							library: {
								type: 'image'
							},
							button: {
								text: data.uploader_btn_text // button label text.
							},
							multiple: false // for multiple image selection set to true.
						}
					).on(
						'select',
						function () {
							// it also has "open" and "close" events.
							const attachment       = customUploader.state().get( 'selection' ).first().toJSON();
							const image_element_id = $( button ).attr( 'data-element-id' );
							$( `#${image_element_id}` ).attr( 'src', attachment.url );
							$( button ).next().show();
							$( button ).next().next().val( attachment.id );
						}
					)
						.open();
			}
		);

		// Removing video.
		$( 'body' ).on(
			'click',
			'.analog-custom-library-remove-image-btn',
			function () {
				const default_image = $( this ).attr( 'data-default-image' );
				$( this ).parent().prev().attr( 'src', default_image );
				$( this ).next().val( '' );
				$( this ).hide();
				return false;
			}
		);
	} );
}( jQuery, analog_custom_library_settings_data, wp ) );
