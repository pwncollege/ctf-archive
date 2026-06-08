/* global wp_mail_smtp_tools_debug_events, ajaxurl, flatpickr */
/**
 * WPMailSMTP Debug Events functionality.
 *
 * @since 3.0.0
 */

'use strict';

var WPMailSmtpDebugEvents = window.WPMailSmtpDebugEvents || ( function( document, window, $ ) {

	/**
	 * Elements.
	 *
	 * @since 3.0.0
	 *
	 * @type {object}
	 */
	var el = {
		$debugEventsPage: $( '.wp-mail-smtp-tab-tools-debug-events' ),
		$dateFlatpickr: $( '.wp-mail-smtp-filter-date-selector' ),
	};

	/**
	 * Public functions and properties.
	 *
	 * @since 3.0.0
	 *
	 * @type {object}
	 */
	var app = {

		/**
		 * Start the engine.
		 *
		 * @since 3.0.0
		 */
		init: function() {

			$( app.ready );
		},

		/**
		 * Document ready.
		 *
		 * @since 3.0.0
		 */
		ready: function() {

			app.initDateRange();
			app.events();

			// Open debug event popup from the query string.
			var searchParams = new URLSearchParams( location.search );

			if ( searchParams.has( 'debug_event_id' ) ) {
				app.openDebugEventPopup( searchParams.get( 'debug_event_id' ) );
			}
		},

		/**
		 * Register JS events.
		 *
		 * @since 3.0.0
		 */
		events: function() {

			el.$debugEventsPage.on( 'click', '#wp-mail-smtp-reset-filter .reset', app.resetFilter );
			el.$debugEventsPage.on( 'click', '#wp-mail-smtp-delete-all-debug-events-button', app.deleteAllDebugEvents );
			el.$debugEventsPage.on( 'click', '.js-wp-mail-smtp-debug-event-preview', app.eventClicked );
		},

		/**
		 * Init Flatpickr at Date Range field.
		 *
		 * @since 3.0.0
		 */
		initDateRange: function() {

			var langCode = wp_mail_smtp_tools_debug_events.lang_code,
				flatpickrLocale = {
					rangeSeparator: ' - ',
				};

			if (
				flatpickr !== 'undefined' &&
				Object.prototype.hasOwnProperty.call( flatpickr, 'l10ns' ) &&
				Object.prototype.hasOwnProperty.call( flatpickr.l10ns, langCode )
			) {
				flatpickrLocale = flatpickr.l10ns[ langCode ];
				flatpickrLocale.rangeSeparator = ' - ';
			}

			el.$dateFlatpickr.flatpickr( {
				altInput  : true,
				altFormat : 'M j, Y',
				dateFormat: 'Y-m-d',
				locale    : flatpickrLocale,
				mode      : 'range'
			} );
		},

		/**
		 * Reset filter handler.
		 *
		 * @since 3.0.0
		 */
		resetFilter: function() {

			var $form = $( this ).parents( 'form' );

			$form.find( $( this ).data( 'scope' ) ).find( 'input,select' ).each( function() {

				var $this = $( this );
				if ( app.isIgnoredForResetInput( $this ) ) {
					return;
				}
				app.resetInput( $this );
			} );

			// Submit the form.
			$form.submit();
		},

		/**
		 * Reset input.
		 *
		 * @since 3.0.0
		 *
		 * @param {object} $input Input element.
		 */
		resetInput: function( $input ) {

			switch ( $input.prop( 'tagName' ).toLowerCase() ) {
				case 'input':
					$input.val( '' );
					break;
				case 'select':
					$input.val( $input.find( 'option' ).first().val() );
					break;
			}
		},

		/**
		 * Input is ignored for reset.
		 *
		 * @since 3.0.0
		 *
		 * @param {object} $input Input element.
		 *
		 * @returns {boolean} Is ignored.
		 */
		isIgnoredForResetInput: function( $input ) {

			return [ 'submit', 'hidden' ].indexOf( ( $input.attr( 'type' ) || '' ).toLowerCase() ) !== -1 &&
				! $input.hasClass( 'flatpickr-input' );
		},

		/**
		 * Process the click on the delete all debug events button.
		 *
		 * @since 3.0.0
		 *
		 * @param {object} event jQuery event.
		 */
		deleteAllDebugEvents: function( event ) {

			event.preventDefault();

			var $btn = $( event.target );

			$.confirm( {
				backgroundDismiss: false,
				escapeKey: true,
				animationBounce: 1,
				closeIcon: true,
				type: 'orange',
				icon: app.getModalIcon( 'exclamation-circle-solid-orange' ),
				title: wp_mail_smtp_tools_debug_events.texts.notice_title,
				content: wp_mail_smtp_tools_debug_events.texts.delete_all_notice,
				buttons: {
					confirm: {
						text: wp_mail_smtp_tools_debug_events.texts.yes,
						btnClass: 'btn-confirm',
						keys: [ 'enter' ],
						action: function() {
							app.executeAllDebugEventsDeletion( $btn );
						}
					},
					cancel: {
						text: wp_mail_smtp_tools_debug_events.texts.cancel,
						btnClass: 'btn-cancel',
					}
				}
			} );
		},

		/**
		 * Process the click on the event item.
		 *
		 * @since 3.0.0
		 *
		 * @param {object} event jQuery event.
		 */
		eventClicked: function( event ) {

			event.preventDefault();

			app.openDebugEventPopup( $( this ).data( 'event-id' ) );
		},

		/**
		 * Open debug event popup.
		 *
		 * @since 3.5.0
		 *
		 * @param {int} eventId Debug event ID.
		 */
		openDebugEventPopup: function( eventId ) {

			var data = {
				action: 'wp_mail_smtp_debug_event_preview',
				id: eventId,
				nonce: $( '#wp-mail-smtp-debug-events-nonce', el.$debugEventsPage ).val()
			};

			var popup = $.alert( {
				backgroundDismiss: true,
				escapeKey: true,
				animationBounce: 1,
				type: 'blue',
				icon: app.getModalIcon( 'info-circle-blue' ),
				title: false,
				content: wp_mail_smtp_tools_debug_events.loader,
				boxWidth: '550px',
				buttons: {
					confirm: {
						text: wp_mail_smtp_tools_debug_events.texts.close,
						btnClass: 'btn-confirm',
						keys: [ 'enter' ]
					}
				},
				onOpenBefore: function() {
					this.$contentPane.addClass( 'no-scroll' );
				}
			} );

			$.post( ajaxurl, data, function( response ) {
				if ( response.success ) {
					popup.setTitle( response.data.title );
					popup.setContent( response.data.content );
				} else {
					popup.setIcon( app.getModalIcon( 'exclamation-circle-regular-red' ) );
					popup.setType( 'red' );
					popup.setContent( response.data );
				}
			} ).fail( function() {
				popup.setContent( wp_mail_smtp_tools_debug_events.texts.error_occurred );
			} );
		},

		/**
		 * AJAX call for deleting all debug events.
		 *
		 * @since 3.0.0
		 *
		 * @param {object} $btn jQuery object of the clicked button.
		 */
		executeAllDebugEventsDeletion: function( $btn ) {

			$btn.prop( 'disabled', true );

			var data = {
				action: 'wp_mail_smtp_delete_all_debug_events',
				nonce: $( '#wp-mail-smtp-debug-events-nonce', el.$debugEventsPage ).val()
			};

			$.post( ajaxurl, data, function( response ) {
				var message = response.data,
					icon,
					type,
					callback;

				if ( response.success ) {
					icon = 'check-circle-solid-green';
					type = 'green';
					callback = function() {
						location.reload();
						return false;
					};
				} else {
					icon = 'exclamation-circle-regular-red';
					type = 'red';
				}

				app.displayModal( message, icon, type, callback );
				$btn.prop( 'disabled', false );
			} ).fail( function() {
				app.displayModal( wp_mail_smtp_tools_debug_events.texts.error_occurred, 'exclamation-circle-regular-red', 'red' );
				$btn.prop( 'disabled', false );
			} );
		},

		/**
		 * Display the modal with provided text and icon.
		 *
		 * @since 3.0.0
		 *
		 * @param {string}   message        The message to be displayed in the modal.
		 * @param {string}   icon           The icon name from /assets/images/font-awesome/ to be used in modal.
		 * @param {string}   type           The type of the message (red, green, orange, blue, purple, dark).
		 * @param {Function} actionCallback The action callback function.
		 */
		displayModal: function( message, icon, type, actionCallback ) {

			type = type || 'default';
			actionCallback = actionCallback || function() {};

			$.alert( {
				backgroundDismiss: true,
				escapeKey: true,
				animationBounce: 1,
				type: type,
				closeIcon: true,
				title: false,
				icon: icon ? app.getModalIcon( icon ) : '',
				content: message,
				buttons: {
					confirm: {
						text: wp_mail_smtp_tools_debug_events.texts.ok,
						btnClass: 'wp-mail-smtp-btn wp-mail-smtp-btn-md',
						keys: [ 'enter' ],
						action: actionCallback
					}
				}
			} );
		},

		/**
		 * Returns prepared modal icon.
		 *
		 * @since 3.0.0
		 *
		 * @param {string} icon The icon name from /assets/images/font-awesome/ to be used in modal.
		 *
		 * @returns {string} Modal icon HTML.
		 */
		getModalIcon: function( icon ) {

			return '"></i><img src="' + wp_mail_smtp_tools_debug_events.plugin_url + '/assets/images/font-awesome/' + icon + '.svg" style="width: 40px; height: 40px;" alt=""><i class="';
		},
	};

	// Provide access to public functions/properties.
	return app;

}( document, window, jQuery ) );

// Initialize.
WPMailSmtpDebugEvents.init();
