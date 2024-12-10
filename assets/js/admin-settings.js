/* global ang_settings_data, wp */
( function( $, data, wp ) {
	$( function() {
		const { __ } = wp.i18n;
		const { addQueryArgs } = wp.url;

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
