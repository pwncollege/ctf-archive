/* globals wp_mail_smtp_connect */

/**
 * Connect functionality - Upgrade plugin from Lite to Pro version.
 *
 * @since 2.6.0
 */

'use strict';

var WPMailSMTPConnect = window.WPMailSMTPConnect || ( function( document, window, $ ) {

	/**
	 * Elements reference.
	 *
	 * @since 2.6.0
	 *
	 * @type {object}
	 */
	var el = {
		$connectBtn: $( '#wp-mail-smtp-setting-upgrade-license-button' ),
		$connectKey: $( '#wp-mail-smtp-setting-upgrade-license-key' )
	};

	/**
	 * Public functions and properties.
	 *
	 * @since 2.6.0
	 *
	 * @type {object}
	 */
	var app = {

		/**
		 * Start the engine.
		 *
		 * @since 2.6.0
		 */
		init: function() {

			$( app.ready );
		},

		/**
		 * Document ready.
		 *
		 * @since 2.6.0
		 */
		ready: function() {

			app.events();
		},

		/**
		 * Register JS events.
		 *
		 * @since 2.6.0
		 */
		events: function() {

			app.connectBtnClick();
		},

		/**
		 * Register connect button event.
		 *
		 * @since 2.6.0
		 */
		connectBtnClick: function() {

			el.$connectBtn.on( 'click', function() {
				app.gotoUpgradeUrl();
			} );
		},

		/**
		 * Get the alert arguments in case of Pro already installed.
		 *
		 * @since 2.6.0
		 *
		 * @param {object} res Ajax query result object.
		 *
		 * @returns {object} Alert arguments.
		 */
		proAlreadyInstalled: function( res ) {

			return {
				title: wp_mail_smtp_connect.text.almost_done,
				content: res.data.message,
				icon: '"></i><img src="' + wp_mail_smtp_connect.plugin_url + '/assets/images/font-awesome/check-circle-solid-green.svg" style="width: 40px; height: 40px;"><i class="',
				type: 'green',
				buttons: {
					confirm: {
						text: wp_mail_smtp_connect.text.plugin_activate_btn,
						btnClass: 'btn-confirm',
						keys: [ 'enter' ],
						action: function() {
							window.location.reload();
						},
					},
				},
			};
		},

		/**
		 * Go to upgrade url.
		 *
		 * @since 2.6.0
		 */
		gotoUpgradeUrl: function() {

			var data = {
				action: 'wp_mail_smtp_connect_url',
				key:  el.$connectKey.val(),
				nonce: wp_mail_smtp_connect.nonce,
			};

			$.post( wp_mail_smtp_connect.ajax_url, data )
				.done( function( res ) {
					if ( res.success ) {
						if ( res.data.reload ) {
							$.alert( app.proAlreadyInstalled( res ) );
							return;
						}
						window.location.href = res.data.url;
						return;
					}
					$.alert( {
						title: wp_mail_smtp_connect.text.oops,
						content: res.data.message,
						icon: '"></i><img src="' + wp_mail_smtp_connect.plugin_url + '/assets/images/font-awesome/exclamation-circle-solid-orange.svg" style="width: 40px; height: 40px;"><i class="',
						type: 'orange',
						buttons: {
							confirm: {
								text: wp_mail_smtp_connect.text.ok,
								btnClass: 'btn-confirm',
								keys: [ 'enter' ],
							},
						},
					} );
				} )
				.fail( function( xhr ) {
					app.failAlert( xhr );
				} );
		},

		/**
		 * Alert in case of server error.
		 *
		 * @since 2.6.0
		 *
		 * @param {object} xhr XHR object.
		 */
		failAlert: function( xhr ) {

			$.alert( {
				title: wp_mail_smtp_connect.text.oops,
				content: wp_mail_smtp_connect.text.server_error + '<br>' + xhr.status + ' ' + xhr.statusText + ' ' + xhr.responseText,
				icon: '"></i><img src="' + wp_mail_smtp_connect.plugin_url + '/assets/images/font-awesome/exclamation-circle-regular-red.svg" style="width: 40px; height: 40px;"><i class="',
				type: 'red',
				buttons: {
					confirm: {
						text: wp_mail_smtp_connect.text.ok,
						btnClass: 'btn-confirm',
						keys: [ 'enter' ],
					},
				},
			} );
		},
	};

	// Provide access to public functions/properties.
	return app;

}( document, window, jQuery ) );

// Initialize.
WPMailSMTPConnect.init();
