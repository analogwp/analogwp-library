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

		// Removing image.
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

		// Initialize WP Color Picker.
		$( '.color-field' ).wpColorPicker();

		// Update outdated templates.
		$( '.forminp-action-button #update_outdated_templates' ).on('click', function(e) {
			e.preventDefault();
			const button = $( this );
			button.addClass( 'loading' );

			$.post(
				data.update_outdated_templates_url,
				{
					action: data.update_outdated_templates_action,
					action2: data.update_outdated_templates_action,
					_wpnonce: data.update_outdated_templates_nonce,
					_wp_http_referer: window.location.href,
				}
			).done( function( res ) {
				button.removeClass( 'loading' );
				button.addClass( 'success' );
				button.html(data.update_outdated_templates_success_txt);

				// Reset button.
				setTimeout(() => {
					button.removeClass( 'success' );
					button.html(button.data('reset-label'));
				}, 3000);
			} ).fail( function(res) {
				console.log(res);
				button.removeClass( 'loading' );
				button.addClass( 'error' );
				button.html(data.update_outdated_templates_error_txt);

				// Reset button.
				setTimeout(() => {
					button.removeClass( 'error' );
					button.html(button.data('reset-label'));
				}, 3000);
			} );
		})

		// Initialize Select2.
		$( '.ang-custom-library .forminp-multiselect select' ).select2();

		// Process Plugin Rollback.
		function processPluginRollback( e ) {
			if ( e.preventDefault ) {
				e.preventDefault();
			}

			const version = $( '#analog_custom_library_rollback_version_select_option' ).val();
			const rollbackUrl = addQueryArgs( data.rollback_url, { version: version } );

			window.location.href = rollbackUrl;
			return false;
		}
		$( '#analog_custom_library_rollback_version_button' ).on( 'click', processPluginRollback );


		function submitDiscountRequest( e ) {
			e.preventDefault();

			const email = $( this ).find( 'input[name="email"]' ).val();
			const fname = $( this ).find( 'input[name="first_name"]' ).val();
			const lname = $( this ).find( 'input[name="last_name"]' ).val();

			const elSubmitBtn = $( this ).find( 'input[type=submit]' );
			const messageEl = $( this ).find( '.ang-discount-response span' );
			const defaultLabel = elSubmitBtn.data( 'default-label' );
			messageEl.text( '' );
			elSubmitBtn.val( 'Sending...' );

			$.post(
				'https://analogwp.com/?ang-api=cl_bfcm_discount_code',
				{
					email: email,
					first_name: JSON.stringify( fname ),
					last_name: JSON.stringify( lname ),
				}
			).done( function( res ) {
				messageEl.text( res?.message );
				elSubmitBtn.val( defaultLabel );
				elSubmitBtn.attr( 'disabled', 'disabled' );
			} ).fail( function(res) {
				messageEl.text( 'Failed to send, please contact support.' );
				elSubmitBtn.attr( 'disabled', 'disabled' );
				setTimeout( function() {
					messageEl.text( 'Send me the coupon' );
					elSubmitBtn.removeAttr( 'disabled' );
				}, 2000 );
			} );
		}

		$( '#js-ang-custom-library-request-discount' ).on( 'submit', submitDiscountRequest );
	} );
}( jQuery, analog_custom_library_settings_data, wp ) );
