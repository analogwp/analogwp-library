/* global elementor, elementorCommon */
/* eslint-disable */

const analogCustomLibrary = window.analogCustomLibrary = window.analogCustomLibrary || {};

"undefined" != typeof jQuery &&
	!(function($) {
		$(function() {
			function modal() {
				const insertIndex = 0 < jQuery(this).parents(".elementor-section-wrap").length ? jQuery(this).parents(".elementor-add-section").index() : -1;

				analogCustomLibrary.insertIndex = insertIndex;

				elementorCommon &&
					(window.analogCustomLibraryModal ||
						((window.analogCustomLibraryModal = elementorCommon.dialogsManager.createWidget(
							"lightbox",
							{
								id: "analog-custom-library-modal",
								headerMessage: "What is this???",
								message: "",
								hide: {
									auto: !1,
									onClick: !1,
									onOutsideClick: !1,
									onOutsideContextMenu: !1,
									onBackgroundClick: !0
								},
								position: {
									my: "center",
									at: "center"
								},
								onShow: function() {
									const content = window.analogCustomLibraryModal.getElements("content");
									content.append('<div id="analog-custom-library" class="wrap"></div>');
									var event = new Event("modal-close");
									$("#analog-custom-library").on(
										"click",
										".close-modal",
										function() {
											document.dispatchEvent(event);
											return window.analogCustomLibraryModal.hide(), !1;
										}
									);
								},
								onHide: function() {}
							}
						)),
						window.analogCustomLibraryModal.getElements("header").remove(),
						window.analogCustomLibraryModal
							.getElements("message")
							.append(window.analogCustomLibraryModal.addElement("content"))),
					window.analogCustomLibraryModal.show());
			}

			window.analogCustomLibraryModal = null;

			const template = $("#tmpl-elementor-add-section");

			if (0 < template.length && typeof elementor !== undefined) {
				let text = template.text();

				(text = text.replace(
					'<div class="elementor-add-section-drag-title',
					'<div class="elementor-add-section-area-button elementor-add-analog-custom-library-button" title="AnalogWP Custom Library">&nbsp;</div> <div class="elementor-add-section-drag-title'
				)),
					template.text(text),
					elementor.on("preview:loaded", function() {
						$(elementor.$previewContents[0].body).on(
							"click",
							".elementor-add-analog-custom-library-button",
							modal
						);
					});
			}
		});
	})(jQuery);
