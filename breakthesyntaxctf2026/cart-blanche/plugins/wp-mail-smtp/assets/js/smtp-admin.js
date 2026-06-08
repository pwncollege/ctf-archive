/* globals wp_mail_smtp, jconfirm, ajaxurl */
'use strict';

var WPMailSMTP = window.WPMailSMTP || {};
WPMailSMTP.Admin = WPMailSMTP.Admin || {};

/**
 * WP Mail SMTP Admin area module.
 *
 * @since 1.6.0
 */
WPMailSMTP.Admin.Settings = WPMailSMTP.Admin.Settings || ( function( document, window, $ ) {

	/**
	 * Public functions and properties.
	 *
	 * @since 1.6.0
	 *
	 * @type {object}
	 */
	var app = {

		/**
		 * State attribute showing if one of the plugin settings
		 * changed and was not yet saved.
		 *
		 * @since 1.9.0
		 *
		 * @type {boolean}
		 */
		pluginSettingsChanged: false,

		/**
		 * Start the engine. DOM is not ready yet, use only to init something.
		 *
		 * @since 1.6.0
		 */
		init: function() {

			// Do that when DOM is ready.
			$( app.ready );
		},

		/**
		 * DOM is fully loaded.
		 *
		 * @since 1.6.0
		 */
		ready: function() {

			app.pageHolder = $( '.wp-mail-smtp-tab-settings' );

			app.settingsForm = $( '.wp-mail-smtp-connection-settings-form' );

			// If there are screen options we have to move them.
			$( '#screen-meta-links, #screen-meta' ).prependTo( '#wp-mail-smtp-header-temp' ).show();

			app.bindActions();
			app.cleanQueryParams( [ 'sendlayer_quick_connect_result', 'sendlayer_quick_connect_disconnect_result' ] );

			app.setJQueryConfirmDefaults();

			// Flyout Menu.
			app.initFlyoutMenu();
		},

		/**
		 * Process all generic actions/events, mostly custom that were fired by our API.
		 *
		 * @since 1.6.0
		 */
		bindActions: function() {

			// Mailer selection.
			$( '.wp-mail-smtp-mailer-image', app.settingsForm ).on( 'click', function() {
				$( this ).parents( '.wp-mail-smtp-mailer' ).find( 'input' ).trigger( 'click' );
			} );

			$( '.wp-mail-smtp-mailer input', app.settingsForm ).on( 'click', function() {
				var $input = $( this );

				if ( $input.prop( 'disabled' ) ) {

					// Educational Popup.
					if ( $input.hasClass( 'educate' ) ) {
						app.education.upgradeMailer( $input );
					}

					return false;
				}

				// Deselect the current mailer.
				$( '.wp-mail-smtp-mailer', app.settingsForm ).removeClass( 'active' );

				// Select the correct one.
				$( this ).parents( '.wp-mail-smtp-mailer' ).addClass( 'active' );

				// Hide all mailers options and display for a currently clicked one.
				$( '.wp-mail-smtp-mailer-option', app.settingsForm ).addClass( 'hidden' ).removeClass( 'active' );
				$( '.wp-mail-smtp-mailer-option-' + $( this ).val(), app.settingsForm ).addClass( 'active' ).removeClass( 'hidden' );
			} );

			app.mailers.sendlayer.bindActions();
			app.mailers.smtp.bindActions();

			// Dismiss Pro banner at the bottom of the page.
			$( '#wp-mail-smtp-pro-banner-dismiss', app.pageHolder ).on( 'click', function() {
				$.ajax( {
					url: ajaxurl,
					dataType: 'json',
					type: 'POST',
					data: {
						action: 'wp_mail_smtp_ajax',
						task: 'pro_banner_dismiss',
						nonce: wp_mail_smtp.nonce
					}
				} )
					.always( function() {
						$( '#wp-mail-smtp-pro-banner', app.pageHolder ).fadeOut( 'fast' );
					} );
			} );

			// Dissmis educational notices for certain mailers.
			$( '.js-wp-mail-smtp-mailer-notice-dismiss', app.settingsForm ).on( 'click', function( e ) {
				e.preventDefault();

				var $btn = $( this ),
					$notice = $btn.parents( '.inline-notice' );

				if ( $btn.hasClass( 'disabled' ) ) {
					return false;
				}

				$.ajax( {
					url: ajaxurl,
					dataType: 'json',
					type: 'POST',
					data: {
						action: 'wp_mail_smtp_ajax',
						nonce: wp_mail_smtp.nonce,
						task: 'notice_dismiss',
						notice: $notice.data( 'notice' ),
						mailer: $notice.data( 'mailer' )
					},
					beforeSend: function() {
						$btn.addClass( 'disabled' );
					}
				} )
					.always( function() {
						$notice.fadeOut( 'fast', function() {
							$btn.removeClass( 'disabled' );
						} );
					} );
			} );

			// Show/hide debug output.
			$( '#wp-mail-smtp-debug .error-log-toggle' ).on( 'click', function( e ) {
				e.preventDefault();

				$( '#wp-mail-smtp-debug .error-log' ).slideToggle();
			} );

			// Copy debug output to clipboard.
			$( '#wp-mail-smtp-debug .error-log-copy' ).on( 'click', function( e ) {
				e.preventDefault();

				var $self = $( this );

				// Get error log.
				var $content = $( '#wp-mail-smtp-debug .error-log' );

				// Copy to clipboard.
				if ( ! $content.is( ':visible' ) ) {
					$content.addClass( 'error-log-selection' );
				}
				var range = document.createRange();
				range.selectNode( $content[0] );
				window.getSelection().removeAllRanges();
				window.getSelection().addRange( range );
				document.execCommand( 'Copy' );
				window.getSelection().removeAllRanges();
				$content.removeClass( 'error-log-selection' );

				$self.addClass( 'error-log-copy-copied' );

				setTimeout(
					function() {
						$self.removeClass( 'error-log-copy-copied' );
					},
					1500
				);
			} );

			// Remove mailer connection.
			$( '.js-wp-mail-smtp-provider-remove', app.settingsForm ).on( 'click', function() {
				return confirm( wp_mail_smtp.text_provider_remove );
			} );

			// Copy input text to clipboard.
			$( '.wp-mail-smtp-setting-copy', app.settingsForm ).on( 'click', function( e ) {
				e.preventDefault();

				var target = $( '#' + $( this ).data( 'source_id' ) ).get( 0 );

				target.select();
				document.execCommand( 'Copy' );

				var $buttonIcon = $( this ).find( '.dashicons' );

				$buttonIcon
					.removeClass( 'dashicons-admin-page' )
					.addClass( 'wp-mail-smtp-dashicons-yes-alt-green wp-mail-smtp-success wp-mail-smtp-animate' );

				setTimeout(
					function() {
						$buttonIcon
							.removeClass( 'wp-mail-smtp-dashicons-yes-alt-green wp-mail-smtp-success wp-mail-smtp-animate' )
							.addClass( 'dashicons-admin-page' );
					},
					1000
				);
			} );

			// Notice bar: click on the dissmiss button.
			$( '#wp-mail-smtp-notice-bar' ).on( 'click', '.dismiss', function() {
				var $notice = $( this ).closest( '#wp-mail-smtp-notice-bar' );

				$notice.addClass( 'out' );
				setTimeout(
					function() {
						$notice.remove();
					},
					300
				);

				$.post(
					ajaxurl,
					{
						action: 'wp_mail_smtp_notice_bar_dismiss',
						nonce: wp_mail_smtp.nonce,
					}
				);
			} );

			app.triggerExitNotice();
			app.beforeSaveChecks();

			// Register change event to show/hide plugin supported settings for currently selected mailer.
			$( '.js-wp-mail-smtp-setting-mailer-radio-input', app.settingsForm ).on( 'change', this.processMailerSettingsOnChange );

			// Disable multiple click on the Email Test tab submit button and display a loader icon.
			$( '.wp-mail-smtp-tab-tools-test #email-test-form' ).on( 'submit', function() {
				var $button = $( this ).find( '.wp-mail-smtp-btn' );

				$button.attr( 'disabled', true );
				$button.find( 'span' ).hide();
				$button.find( '.wp-mail-smtp-loading' ).show();
			} );

			$( '#wp-mail-smtp-setting-gmail-one_click_setup_enabled-lite' ).on( 'click', function( e ) {
				e.preventDefault();

				app.education.gmailOneClickSetupUpgrade();
			} );

			$( '#wp-mail-smtp-setting-misc-rate_limit-lite' ).on( 'click', function( e ) {
				e.preventDefault();

				app.education.rateLimitUpgrade();
			} );

			// Obfuscated fields
			$( '.wp-mail-smtp-btn[data-clear-field]' ).on( 'click', function( e ) {
				var $button = $( this );
				var fieldId = $button.attr( 'data-clear-field' );
				var $field = $( `#${fieldId}` );

				$field.prop( 'disabled', false );
				$field.attr( 'name', $field.attr( 'data-name' ) );
				$field.removeAttr( 'value' );
				$field.focus();
				$button.remove();
			} );

			$( '.email_test_tab_removal_notice' ).on( 'click', '.notice-dismiss', function() {
				var $button = $( this );

				$.ajax( {
					url: ajaxurl,
					dataType: 'json',
					type: 'POST',
					data: {
						action: 'wp_mail_smtp_ajax',
						nonce: wp_mail_smtp.nonce,
						task: 'email_test_tab_removal_notice_dismiss',
					},
					beforeSend: function() {
						$button.prop( 'disabled', true );
					},
				} );
			} );
		},

		education: {
			upgradeMailer: function( $input ) {

				var mailerName = $input.data( 'title' ).trim();

				app.education.upgradeModal(
					wp_mail_smtp.education.upgrade_title.replace( /%name%/g, mailerName ),
					wp_mail_smtp.education.upgrade_content.replace( /%name%/g, mailerName ),
					$input.val()
				);
			},

			gmailOneClickSetupUpgrade: function() {

				app.education.upgradeModal(
					wp_mail_smtp.education.gmail.one_click_setup_upgrade_title,
					wp_mail_smtp.education.gmail.one_click_setup_upgrade_content,
					'gmail-one-click-setup'
				);
			},

			rateLimitUpgrade: function() {

				app.education.upgradeModal(
					wp_mail_smtp.education.rate_limit.upgrade_title,
					wp_mail_smtp.education.rate_limit.upgrade_content,
					'rate-limit-setting'
				);
			},

			upgradeModal: function( title, content, upgradeUrlUtmContent ) {

				$.alert( {
					backgroundDismiss: true,
					escapeKey: true,
					animationBounce: 1,
					type: 'blue',
					closeIcon: true,
					title: title,
					icon: '"></i>' + wp_mail_smtp.education.upgrade_icon_lock + '<i class="',
					content: content,
					boxWidth: '550px',
					onOpenBefore: function() {
						this.$btnc.after( '<div class="discount-note">' + wp_mail_smtp.education.upgrade_bonus + wp_mail_smtp.education.upgrade_doc + '</div>' );
						this.$body.addClass( 'wp-mail-smtp-upgrade-mailer-education-modal' );
					},
					buttons: {
						confirm: {
							text: wp_mail_smtp.education.upgrade_button,
							btnClass: 'btn-confirm',
							keys: [ 'enter' ],
							action: function() {
								var appendChar = /(\?)/.test( wp_mail_smtp.education.upgrade_url ) ? '&' : '?',
									upgradeURL = wp_mail_smtp.education.upgrade_url + appendChar + 'utm_content=' + encodeURIComponent( upgradeUrlUtmContent );

								window.open( upgradeURL, '_blank' );
							}
						}
					}
				} );
			}
		},

		/**
		 * Individual mailers specific js code.
		 *
		 * @since 1.6.0
		 */
		mailers: {
			sendlayer: {

				/**
				 * Show a SendLayer connect error modal with message and optional error code.
				 *
				 * @since 4.8.0
				 *
				 * @param {string} message   The error message to display.
				 * @param {string} errorCode The dot-notation error code (optional).
				 */
				showConnectError: function( message, errorCode ) {

					var content = '<p>' + $( '<span>' ).text( message ).html() + '</p>';

					if ( errorCode ) {
						content += '<div class="wp-mail-smtp-error-code-box">' +
							'<code>' + $( '<span>' ).text( errorCode ).html() + '</code>' +
							'<button type="button" class="wp-mail-smtp-error-code-box__copy" title="Copy">' +
								'<svg class="wp-mail-smtp-error-code-box__icon-copy" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M433.941 65.941l-51.882-51.882A48 48 0 0 0 348.118 0H176c-26.51 0-48 21.49-48 48v48H48c-26.51 0-48 21.49-48 48v320c0 26.51 21.49 48 48 48h224c26.51 0 48-21.49 48-48v-48h80c26.51 0 48-21.49 48-48V99.882a48 48 0 0 0-14.059-33.941zM266 464H54a6 6 0 0 1-6-6V150a6 6 0 0 1 6-6h74v224c0 26.51 21.49 48 48 48h96v42a6 6 0 0 1-6 6zm128-96H182a6 6 0 0 1-6-6V54a6 6 0 0 1 6-6h106v88c0 13.255 10.745 24 24 24h88v202a6 6 0 0 1-6 6zm6-256h-64V48h9.632c1.591 0 3.117.632 4.243 1.757l48.368 48.368a6 6 0 0 1 1.757 4.243V112z"/></svg>' +
								'<svg class="wp-mail-smtp-error-code-box__icon-check" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" style="display:none;"><path fill="#0f8a56" d="M256 512c141.4 0 256-114.6 256-256S397.4 0 256 0S0 114.6 0 256S114.6 512 256 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/></svg>' +
							'</button>' +
						'</div>';
					}

					$.alert( {
						backgroundDismiss: true,
						escapeKey: true,
						animationBounce: 1,
						type: 'red',
						closeIcon: true,
						icon: app.getModalIcon( 'times-circle-red' ),
						title: wp_mail_smtp.sendlayer.error_title,
						content: content,
						boxWidth: '450px',
						buttons: {
							confirm: {
								text: wp_mail_smtp.ok_text,
								btnClass: 'wp-mail-smtp-btn wp-mail-smtp-btn-md',
								keys: [ 'enter' ]
							}
						},
						onOpenBefore: function() {
							this.$body.on( 'click', '.wp-mail-smtp-error-code-box__copy', function() {
								var $btn   = $( this );
								var code   = $btn.siblings( 'code' ).text();

								if ( navigator.clipboard ) {
									navigator.clipboard.writeText( code );
								}

								$btn.find( '.wp-mail-smtp-error-code-box__icon-copy' ).hide();
								$btn.find( '.wp-mail-smtp-error-code-box__icon-check' ).show();

								setTimeout( function() {
									$btn.find( '.wp-mail-smtp-error-code-box__icon-check' ).hide();
									$btn.find( '.wp-mail-smtp-error-code-box__icon-copy' ).show();
								}, 2000 );
							} );
						}
					} );
				},

				/**
				 * Start the connect flow via AJAX and handle errors with the modal.
				 *
				 * @since 4.8.0
				 *
				 * @param {object}   connectArgs Extra arguments to pass to the connect endpoint (e.g. { utm_content: '...' }).
				 * @param {Function} onDone      Callback when the request completes (success or error).
				 */
				doConnect: function( connectArgs, onDone ) {

					var self = this;
					var returnUrl    = $( '#wp-mail-smtp-sendlayer-quick-connect-return-url' ).val() || wp_mail_smtp.sendlayer.return_url;
					var connectionId = $( '#wp-mail-smtp-sendlayer-quick-connect-connection-id' ).val() || '';

					$.post( ajaxurl, { // eslint-disable-line camelcase
						action: 'wp_mail_smtp_sendlayer_connect',
						nonce: wp_mail_smtp.sendlayer.connect_nonce,
						return_url: returnUrl, // eslint-disable-line camelcase
						connection_id: connectionId, // eslint-disable-line camelcase
						connect_args: connectArgs || {}, // eslint-disable-line camelcase
					}, function( response ) { // eslint-disable-line complexity
						if ( response.success && response.data.redirect_url ) {
							window.location.href = response.data.redirect_url;
						} else {
							var message   = response.data && response.data.message ? response.data.message : wp_mail_smtp.sendlayer.error_text;
							var errorCode = response.data && response.data.error_code ? response.data.error_code : '';
							self.showConnectError( message, errorCode );
							if ( onDone ) {
								onDone();
							}
						}
					} ).fail( function() {
						self.showConnectError( wp_mail_smtp.sendlayer.server_error, 'plugin.init_connect.ajax_failed' );
						if ( onDone ) {
							onDone();
						}
					} );
				},

				/**
				 * Bind SendLayer-specific UI actions.
				 *
				 * @since 4.8.0
				 */
				bindActions: function() {

					var self = this;

					// Quick Connect button.
					$( '#wp-mail-smtp-sendlayer-connect-btn' ).on( 'click', function( e ) {
						e.preventDefault();

						var $btn = $( this );
						$btn.addClass( 'wp-mail-smtp-btn-loading' );

						self.doConnect( { utm_content: 'Plugin Settings - Quick Connect' }, function() { // eslint-disable-line camelcase
							$btn.removeClass( 'wp-mail-smtp-btn-loading' );
						} );
					} );

					// Change domain link (same flow as Quick Connect).
					$( '#wp-mail-smtp-sendlayer-change-domain' ).on( 'click', function( e ) {
						e.preventDefault();

						var $link = $( this );
						var originalText = $link.text();
						$link.text( wp_mail_smtp.sendlayer.connecting_text );

						self.doConnect( { utm_content: 'Plugin Settings - Quick Connect Change Domain' }, function() { // eslint-disable-line camelcase
							$link.text( originalText );
						} );
					} );

					// Show Quick Connect when API key is removed.
					$( '.wp-mail-smtp-btn[data-clear-field="wp-mail-smtp-setting-sendlayer-api_key"]' ).on( 'click', function() {
						$( '#wp-mail-smtp-setting-row-sendlayer-connect' ).show();
					} );

					// Show API key field and remove the toggle link.
					$( '#wp-mail-smtp-sendlayer-show-api-key' ).on( 'click', function( e ) {
						e.preventDefault();
						$( this ).closest( '.wp-mail-smtp-setting-row' ).remove();
						$( '#wp-mail-smtp-setting-row-sendlayer-api_key' ).show();
					} );

					// SendLayer education banner: Setup button (same flow as Quick Connect).
					$( '#wp-mail-smtp-sendlayer-education-connect-btn' ).on( 'click', function( e ) {
						e.preventDefault();

						var $btn = $( this );
						$btn.addClass( 'wp-mail-smtp-btn-loading' );

						self.doConnect( { utm_content: 'Plugin Settings - Quick Connect Education' }, function() { // eslint-disable-line camelcase
							$btn.removeClass( 'wp-mail-smtp-btn-loading' );
						} );
					} );

					// SendLayer education banner: Dismiss.
					$( '.js-wp-mail-smtp-sendlayer-education-dismiss' ).on( 'click', function( e ) {
						e.preventDefault();

						var $banner = $( this ).closest( '.wp-mail-smtp-sendlayer-education' );

						$banner.fadeOut( 200 );

						$.post( ajaxurl, {
							action: 'wp_mail_smtp_ajax',
							task: 'notice_dismiss',
							notice: 'sendlayer_education',
							nonce: wp_mail_smtp.nonce,
						} );
					} );
				}
			},
			smtp: {
				bindActions: function() {

					// Hide SMTP-specific user/pass when Auth disabled.
					$( '#wp-mail-smtp-setting-smtp-auth' ).on( 'change', function() {
						$( '#wp-mail-smtp-setting-row-smtp-user, #wp-mail-smtp-setting-row-smtp-pass' ).toggleClass( 'inactive' );
					} );

					// Port default values based on encryption type.
					$( '#wp-mail-smtp-setting-row-smtp-encryption input' ).on( 'change', function() {

						var $input = $( this ),
							$smtpPort = $( '#wp-mail-smtp-setting-smtp-port', app.settingsForm );

						if ( 'tls' === $input.val() ) {
							$smtpPort.val( '587' );
							$( '#wp-mail-smtp-setting-row-smtp-autotls' ).addClass( 'inactive' );
						} else if ( 'ssl' === $input.val() ) {
							$smtpPort.val( '465' );
							$( '#wp-mail-smtp-setting-row-smtp-autotls' ).removeClass( 'inactive' );
						} else {
							$smtpPort.val( '25' );
							$( '#wp-mail-smtp-setting-row-smtp-autotls' ).removeClass( 'inactive' );
						}
					} );
				}
			}
		},

		/**
		 * Exit notice JS code when plugin settings are not saved.
		 *
		 * @since 1.9.0
		 */
		triggerExitNotice: function() {

			var $settingPages = $( '.wp-mail-smtp-page-general' );

			// Display an exit notice, if settings are not saved.
			$( window ).on( 'beforeunload', function() {
				if ( app.pluginSettingsChanged ) {
					return wp_mail_smtp.text_settings_not_saved;
				}
			} );

			// Set settings changed attribute, if any input was changed.
			$( ':input:not( #wp-mail-smtp-setting-license-key, .wp-mail-smtp-not-form-input, #wp-mail-smtp-setting-gmail-one_click_setup_enabled, #wp-mail-smtp-setting-outlook-one_click_setup_enabled )', $settingPages ).on( 'change', function() {
				app.pluginSettingsChanged = true;
			} );

			// Clear the settings changed attribute, if the settings are about to be saved.
			$( 'form', $settingPages ).on( 'submit', function() {
				app.pluginSettingsChanged = false;
			} );
		},

		/**
		 * Perform any checks before the settings are saved.
		 *
		 * Checks:
		 * - warn users if they try to save the settings with the default (PHP) mailer selected.
		 *
		 * @since 2.1.0
		 */
		beforeSaveChecks: function() {

			app.settingsForm.on( 'submit', function() {
				if ( $( '.wp-mail-smtp-mailer input:checked', app.settingsForm ).val() === 'mail' ) {
					var $thisForm = $( this );

					$.alert( {
						backgroundDismiss: false,
						escapeKey: false,
						animationBounce: 1,
						type: 'orange',
						icon: '"></i><img src="' + wp_mail_smtp.plugin_url + '/assets/images/font-awesome/exclamation-circle-solid-orange.svg" style="width: 40px; height: 40px;" alt="' + wp_mail_smtp.default_mailer_notice.icon_alt + '"><i class="',
						title: wp_mail_smtp.default_mailer_notice.title,
						content: wp_mail_smtp.default_mailer_notice.content,
						boxWidth: '550px',
						buttons: {
							confirm: {
								text: wp_mail_smtp.default_mailer_notice.save_button,
								btnClass: 'btn-confirm',
								keys: [ 'enter' ],
								action: function() {
									$thisForm.off( 'submit' ).trigger( 'submit' );
								}
							},
							cancel: {
								text: wp_mail_smtp.default_mailer_notice.cancel_button,
								btnClass: 'btn-cancel',
							},
						}
					} );

					return false;
				}
			} );
		},

		/**
		 * On change callback for showing/hiding plugin supported settings for currently selected mailer.
		 *
		 * @since 2.3.0
		 */
		processMailerSettingsOnChange: function() {

			var selectedMailer = $( this ).val();
			var mailerSupportedSettings = wp_mail_smtp.all_mailers_supports[ selectedMailer ];

			for ( var setting in mailerSupportedSettings ) {
				// eslint-disable-next-line no-prototype-builtins
				if ( mailerSupportedSettings.hasOwnProperty( setting ) ) {
					$( '.js-wp-mail-smtp-setting-' + setting, app.settingsForm ).toggle( mailerSupportedSettings[ setting ] );
				}
			}

			// Special case: "from email" (group settings).
			var $mainSettingInGroup = $( '.js-wp-mail-smtp-setting-from_email' );
			var $quickConnectFromEmail = $( '#wp-mail-smtp-setting-row-sendlayer-quick-connect-from_email' );
			var isQuickConnectActive = selectedMailer === 'sendlayer' && $quickConnectFromEmail.length > 0;

			$mainSettingInGroup.toggle(
				! isQuickConnectActive && ( mailerSupportedSettings[ 'from_email' ] || mailerSupportedSettings[ 'from_email_force' ] )
			);

			// Toggle quick connect From Email field and disable inputs when hidden
			// to prevent split fields from being submitted for other mailers.
			$quickConnectFromEmail.toggle( isQuickConnectActive );
			$quickConnectFromEmail.find( 'input' ).prop( 'disabled', ! isQuickConnectActive );

			// Special case: "from name" (group settings).
			$mainSettingInGroup = $( '.js-wp-mail-smtp-setting-from_name' );

			$mainSettingInGroup.toggle(
				mailerSupportedSettings['from_name'] || mailerSupportedSettings['from_name_force']
			);
		},

		/**
		 * Remove transient query params from the URL without a page reload.
		 *
		 * Useful for cleaning up one-time result params after they have been
		 * read and rendered on the current page load.
		 *
		 * @since 4.8.0
		 *
		 * @param {string[]} params List of query parameter names to remove.
		 */
		cleanQueryParams: function( params ) {

			try {
				var url   = new URL( window.location.href );
				var dirty = false;

				params.forEach( function( param ) {
					if ( url.searchParams.has( param ) ) {
						url.searchParams.delete( param );
						dirty = true;
					}
				} );

				if ( dirty ) {
					window.history.replaceState( {}, document.title, url.toString() );
				}
			} catch ( e ) {} // eslint-disable-line no-empty
		},

		/**
		 * Set jQuery-Confirm default options.
		 *
		 * @since 2.9.0
		 */
		setJQueryConfirmDefaults: function() {

			jconfirm.defaults = {
				typeAnimated: false,
				draggable: false,
				animateFromElement: false,
				theme: 'modern',
				boxWidth: '400px',
				useBootstrap: false
			};
		},

		/**
		 * Flyout Menu (quick links).
		 *
		 * @since 3.0.0
		 */
		initFlyoutMenu: function() {

			// Flyout Menu Elements.
			var $flyoutMenu = $( '#wp-mail-smtp-flyout' );

			if ( $flyoutMenu.length === 0 ) {
				return;
			}

			var $head = $flyoutMenu.find( '.wp-mail-smtp-flyout-head' );

			// Click on the menu head icon.
			$head.on( 'click', function( e ) {
				e.preventDefault();
				$flyoutMenu.toggleClass( 'opened' );
			} );

			// Page elements and other values.
			var $wpfooter = $( '#wpfooter' );

			if ( $wpfooter.length === 0 ) {
				return;
			}

			var $overlap = $(
				'.wp-mail-smtp-page-logs-archive, ' +
				'.wp-mail-smtp-tab-tools-action-scheduler, ' +
				'.wp-mail-smtp-page-reports, ' +
				'.wp-mail-smtp-tab-tools-debug-events, ' +
				'.wp-mail-smtp-tab-connections'
			);

			// Hide menu if scrolled down to the bottom of the page or overlap some critical controls.
			$( window ).on( 'resize scroll', _.debounce( function() {

				var wpfooterTop = $wpfooter.offset().top,
					wpfooterBottom = wpfooterTop + $wpfooter.height(),
					overlapBottom = $overlap.length > 0 ? $overlap.offset().top + $overlap.height() + 85 : 0,
					viewTop = $( window ).scrollTop(),
					viewBottom = viewTop + $( window ).height();

				if ( wpfooterBottom <= viewBottom && wpfooterTop >= viewTop && overlapBottom > viewBottom ) {
					$flyoutMenu.addClass( 'out' );
				} else {
					$flyoutMenu.removeClass( 'out' );
				}
			}, 50 ) );

			$( window ).trigger( 'scroll' );
		}
	};

	// Provide access to public functions/properties.
	return app;
}( document, window, jQuery ) );

// Initialize.
WPMailSMTP.Admin.Settings.init();
