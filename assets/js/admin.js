(function($) {
	$('.analog-notice').on( 'click', 'button.notice-dismiss', function(){
		$.post( ajaxurl, {
			action: 'analog_custom_library_set_admin_notice_viewed',
			key: $( this ).closest('.analog-notice').data( 'key' ),
			nonce: AnalogAdmin.nonce || '',
		} );

		console.log($( this ).closest('.analog-notice').data( 'key' ));
	} );
})(jQuery);
