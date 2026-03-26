/* global elementorCommon */
/* eslint-disable */

/**
 * "Save in Custom Library" — Elementor editor save-dropdown action.
 *
 * Must be enqueued AFTER elementor-v2-editor-app-bar (declared as
 * a dependency in PHP) and BEFORE elementor-editor-loader-v2 (which mounts the
 * React app). At that window, calling registerAction() writes to the injections
 * Map before useMemo reads it on mount.
 */
( function () {
	var appBar = window.elementorV2 && window.elementorV2.editorAppBar;

	if ( ! appBar || ! appBar.documentOptionsMenu || ! appBar.documentOptionsMenu.registerAction ) {
		return;
	}

	appBar.documentOptionsMenu.registerAction( {
		id: 'document-save-to-analog-custom-library',
		group: 'save',
		priority: 30,
		useProps: function () {
			var icons = window.elementorV2 && window.elementorV2.icons;
			return {
				title: 'Save in Custom Library',
				icon: icons && ( icons.FolderIcon || icons.BookmarkIcon ),
				onClick: openSaveDialog,
			};
		},
	} );

	/**
	 * Dialog for entering template name and confirming save action. Created once and reused on every open to avoid * stacking event listeners.
	 */

	// Widget is created once and reused to avoid stacking event listeners.
	var saveDialog = null;

	function openSaveDialog() {
		if ( ! window.elementorCommon ) {
			return;
		}

		if ( ! saveDialog ) {
			saveDialog = window.elementorCommon.dialogsManager.createWidget( 'confirm', {
				id: 'agwp-cl-save-dialog',
				headerMessage: 'Save in Custom Library',
				message:
					'<div class="agwp-cl-dialog-body">' +
					'<input type="text" id="agwp-cl-template-name" class="agwp-cl-input" placeholder="Enter template name" />' +
					'</div>',
				strings: {
					confirm: 'Save',
					cancel: 'Cancel',
				},
				onConfirm: function () {
					var input = document.getElementById( 'agwp-cl-template-name' );
					var name  = input ? input.value.trim() : '';
					if ( name ) {
						doSave( name );
					}
				},
			} );

			// Attach Enter key handler exactly once after the widget DOM is ready.
			setTimeout( function () {
				var input = document.getElementById( 'agwp-cl-template-name' );
				if ( input ) {
					input.addEventListener( 'keydown', function ( e ) {
						if ( e.key === 'Enter' ) {
							var okBtn = document.querySelector( '.dialog-confirm-ok' );
							if ( okBtn ) {
								okBtn.click();
							}
						}
					} );
				}
			}, 150 );
		}

		saveDialog.show();

		// Clear and focus on every open.
		setTimeout( function () {
			var input = document.getElementById( 'agwp-cl-template-name' );
			if ( input ) {
				input.value = '';
				input.focus();
			}
		}, 150 );
	}

	/**
	 * AJAX save action. Uses the live editor elements data, so unsaved changes are included. Shows a success or error alert based on the response.
	 */

	function doSave( name ) {
		// Use the live editor elements (includes unsaved changes).
		var elements = window.elementor.elements.toJSON();
		var cfg      = window.AGWP_LIBRARY_SAVE || {};

		window.jQuery.ajax( {
			url:  cfg.ajax_url || '',
			type: 'POST',
			data: {
				action:  'agwp_library_direct_save',
				nonce:   cfg.nonce || '',
				title:   name,
				content: JSON.stringify( elements ),
			},
			success: function ( response ) {
				if ( response.success ) {
					showAlert( 'Saved to Custom Library', '"' + name + '" was saved successfully.' );
				} else {
					showAlert( 'Error', ( response.data && response.data.message ) || 'Failed to save.' );
				}
			},
			error: function () {
				showAlert( 'Error', 'Server error. Please try again.' );
			},
		} );
	}

	/**
	 * Right-click context menu — registered at DOM ready when elementor.hooks is available.
	 */
	window.jQuery( document ).ready( function () {
		if ( window.elementor && window.elementor.hooks ) {
			window.elementor.hooks.addFilter( 'elements/context-menu/groups', function ( groups ) {
				groups.push( {
					name: 'analog-custom-library',
					actions: [
						{
							name:      'save-to-analog-custom-library',
							title:     'Save in Custom Library',
							icon:      'eicon-folder',
							isEnabled: function () { return true; },
							callback:  openSaveDialog,
						},
					],
				} );
				return groups;
			} );
		}
	} );

	function showAlert( header, message ) {
		window.elementorCommon.dialogsManager.createWidget( 'alert', {
			id: 'agwp-cl-alert',
			headerMessage: header,
			message: message,
		} ).show();
	}
} )();
