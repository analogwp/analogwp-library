/* global elementorCommon, analog */
const { apiFetch } = wp;
const { __ } = wp.i18n;
import 'regenerator-runtime/runtime';

export async function markFavorite( id, favorite = true, type = 'template' ) {
	return await apiFetch( {
		path: '/agwp-library/v1/mark_favorite',
		method: 'post',
		data: {
			id: id,
			favorite,
			type: type,
		},
	} ).then( response => response );
}

export async function requestTemplateList() {
	return await apiFetch( { path: '/agwp-library/v1/templates' } ).then(
		response => response
	);
}

export async function requestDirectImport( template, withPage = false, kit = false ) {
	return await apiFetch( {
		path: '/agwp-library/v1/import/elementor/direct',
		method: 'post',
		data: {
			template,
			site_id: template.site_id || false,
			with_page: withPage,
			kit,
		},
	} ).then( response => {
		return response;
	} );
}

export async function requestBlockContent( block, method ) {
	return await apiFetch( {
		path: '/agwp-library/v1/blocks/insert',
		method: 'post',
		data: {
			block,
			method,
		},
	} ).then( response => response );
}

export async function getSettings() {
	return await apiFetch( { path: '/agwp-library/v1/get/settings' } ).then(
		response => response
	);
}

export async function requestSettingUpdate( key, value ) {
	if ( ! key ) {
		console.warn('No key/value pair found to update settings.'); // eslint-disable-line
		return false;
	}

	return await apiFetch( {
		path: '/agwp-library/v1/update/settings',
		method: 'POST',
		data: {
			key,
			value,
		},
	} ).then( response => response );
}

export async function requestLicenseInfo( action = 'check' ) {
	return await apiFetch( {
		path: '/agwp-library/v1/license',
		method: 'post',
		data: {
			action,
		},
	} ).then( response => {
		return response;
	} );
}

export async function getLicenseStatus() {
	return await apiFetch( { path: '/agwp-library/v1/license/status' } ).then(
		response => response
	);
}

export async function requestElementorImport( template, kit ) {
	let elementsLength = elementor.elements.length;

	if ( template.version ) {
		if ( parseFloat( AGWP_LIBRARY.version ) < parseFloat( template.version ) ) {
			elementorCommon.dialogsManager.createWidget( 'alert', {
				message: 'This template requires an updated version, please update your plugin to latest version.',
			} ).show();
			return;
		}
	}

	const editorId =
				'undefined' !== typeof ElementorConfig ?
					ElementorConfig.document.id :
					false;

	return await apiFetch( {
		path: '/agwp-library/v1/import/elementor',
		method: 'post',
		data: {
			template_id: template.id,
			editor_post_id: editorId,
			is_pro: template.is_pro,
			site_id: template.site_id || false,
			kit,
		},
	} ).then( data => {
		const parsedTemplate = JSON.parse( data );

		if ( parsedTemplate.errors ) {
			const error = parsedTemplate.errors[ Object.keys( parsedTemplate.errors )[ 0 ] ];

			elementorCommon.dialogsManager.createWidget( 'alert', {
				message: error,
			} ).show();

			return;
		}

		doElementorInsert( parsedTemplate.content );

		window.analogModal.hide();
		setTimeout(function() {
			if ( elementsLength !== 0 ) {
				elementor.reloadPreview();
			}
		});
	} );
}

/**
 * Perform content insertion inside Elementor.
 *
 * @param {object} content Elementor content object with serialized data.
 * @param {string} context Import context, can be 'template' or 'block'.
 *
 * @since 1.5.2
 * @returns void
 */
export function doElementorInsert( content, context = 'template' ) {
	let contextText = __( 'Template', 'ang' );

	if ( context === 'block' ) {
		contextText = __( 'Block', 'ang' );
	}

	let insertIndex = analog.insertIndex || -1;

	if ( typeof $e !== 'undefined' ) {
		const historyId = $e.internal( 'document/history/start-log', {
			type: 'add',
			title: `${ __( 'Add Analog Library', 'ang' ) } ${ contextText }`,
		} );

		for ( let i = 0; i < content.length; i++ ) {
			$e.run( 'document/elements/create', {
				container: elementor.getPreviewContainer(),
				model: content[ i ],
				options: insertIndex >= 0 ? { at: insertIndex++ } : {}
			} );
		}

		$e.internal( 'document/history/end-log', {
			id: historyId,
		} );
	} else {
		const model = new Backbone.Model( {
			getTitle() {
				return 'Test';
			},
		} );

		elementor.channels.data.trigger( 'template:before:insert', model );

		for ( let i = 0; i < json.data.content.length; i++ ) {
			elementor.getPreviewView().addChildElement( content[ i ], insertIndex >= 0 ? { at: insertIndex++ } : null );
		}

		elementor.channels.data.trigger( 'template:after:insert', {} );
	}
}
