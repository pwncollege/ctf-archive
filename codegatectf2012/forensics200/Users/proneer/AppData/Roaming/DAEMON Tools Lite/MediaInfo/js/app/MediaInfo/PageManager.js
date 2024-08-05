/**
 * @class {MediaInfo.PageManager}
 */
MediaInfo.PageManager = new Class(/** @lends MediaInfo.PageManager.prototype */{
	/**
	 * Page instances
	 * 
	 * @type {Object}
	 */
	pages: {},
	
	/**
	 * Current page name
	 * 
	 * @type {String}
	 */
	_currentPageName: null,
	
	/**
	 * Current page
	 * 
	 * @type {MediaInfo.Page.PageAbstract}
	 */
	_currentPage: null,

	/**
	 * On application load handler
	 *
	 *  @return {Void}
	 */
	onAppLoad: function() {
		if (!parseInt(MediaInfo.Url.getUrlParam('show_errors'))) {
			this.setupErrorHandler();
		}

		// Translate
		app.translation.translatePage($('body'));

		// Show default page
		if (!this.getCurrentPageName()) {
			this.showPage('Welcome');
		}

		// Show test message
		var testMessage = MediaInfo.Url.getUrlParam('show_message');
		if (testMessage.length > 0) {
			this.showMessage(testMessage);
		}

		// Selected image event for tests
		if (MediaInfo.Url.getUrlParam('test') == 1) {
			this.switchPage('FWYx-I71-aWQ23', 'ABCDEF123456891', 'Ok');
		}
		
		if (this.isTestFeaturesEnabled()) {
			this.showTestFeatures();
		}
	},
	
	/**
	 * Switch page in turn
	 * 
	 * @param {String} imageId
	 * @param {String} imageHash
	 * @param {String} status
	 * @returns {Void}
	 */
	switchPageInTurn: function(imageId, imageHash, status) {
		var _this = this;
		app.pageManagerQueue.run(function(){
			_this.switchPage(imageId, imageHash, status);
		});
	},

	/**
	 * Switch page
	 *
	 * @param {String} imageId
	 * @param {String} imageHash
	 * @param {String} status
	 * @returns {Void}
	 */
	switchPage: function(imageId, imageHash, status) {
		var _this = this;
		
		if ('Ok' == status || ('NetImage' == status && (imageHash || imageId))) {
			this.showPage('Loading');
			app.imageInfoLoader.loadInfo({
				imageId: imageId,
				imageHash: imageHash,
				success: function(info){
					_this.switchPageByInfo(info);
				},
				error: function(){
					_this.showPage('NoInternet');
				}
			});
		} else if ('ImageNotSelected' == status) {
			
			this.showPage('Welcome');
			
		} else if ('RequiredPassword' == status || ('NetImage' == status && (!imageHash && !imageId))) {
			
			this.showPage('RequiredPassword');
			
		} else if ('FileNotFound' == status) {
			
			this.showPage('FileNotFound');
			
		} else if ('InvalidImage' == status) {
			
			this.showPage('InvalidImage');
			
		} else {
			throw new Error(
				'Invalid status or params. Status = ' + status
				+ ' , hash = ' + imageHash
				+ ' , id = ' + imageId
			);
		}
	},
	
	/**
	 * Show page by image data
	 *
	 * @returns {Void}
	 */
	switchPageByInfo: function(imageInfo) {
		var typeToPages = {
			'game': 'Game',
			'soft': 'Soft',
			'other': 'Other',
			'not-found': 'InfoNotFound'
		};
		
		if (imageInfo.typeinfo == 'audio') {
			var selectedAlbumId = MediaInfo.Page.Album.getSelectedAlbumId();
			if (selectedAlbumId) {
				this.showPage('Album', {
					albumId: selectedAlbumId
				});
			} else if (imageInfo.data.length == 0) {
				this.showPage('InfoNotFound');
			} else if (imageInfo.data.length == 1) {
				this.showPage('Album', {
					albumId: imageInfo.data[0]['album_id']
				});
			} else {
				this.showPage('AlbumList');
			}
		} else {
			this.showPage(typeToPages[imageInfo.typeinfo]);
		}
	},
	
	/**
	 * Refresh image page
	 *
	 * @returns {Void}
	 */
	refreshImagePage: function() {
		var _this = this;

		this.showPage('Loading');
		
		app.imageInfoLoader.reloadInfo({
			success: function(imageData){
				_this.switchPageByInfo(imageData);
			},
			error: function(oldImageData){
				if (oldImageData) {
					_this.switchPageByInfo(oldImageData);
					_this.showMessage(app.translation.translate('no_internet_connection'));
				} else {
					_this.showPage('NoInternet');
				}
			}
		});
	},
	
	/**
	 * Show page message
	 *
	 * @param {String} message
	 * @returns {Void}
	 */
	showMessage: function(message) {
		var _this = this;
		
		$('.info-msg .message').html(message);
		if (this.isBrowserIe6()) {
			$('.info-msg').css('display', 'block');
		} else {
			$('.info-msg').show();
		}

		setTimeout(function(){
			if (_this.isBrowserIe6()) {
				$('.info-msg').css('display', 'none');
			} else {
				$('.info-msg').hide('slow');
			}
		}, 5000);
	},
	
	/**
	 * Hide page message
	 *
	 * @param {Boolean} fast
	 * @returns {Void}
	 */
	hideMessage: function(fast) {
		if (this.isBrowserIe6() || fast) {
			$('.info-msg').css('display', 'none');
		} else {
			$('.info-msg').hide('slow');
		}
	},
	
	/**
	 * Show page
	 * 
	 * @param {String} pageName
	 * @returns {Void}
	 */
	showPage: function(pageName, options){
		this.hideMessage(true);
		
		options = typeof options == 'undefined'? {}: options;
		pageName = pageName.substr(0, 1).toUpperCase() + pageName.substr(1);
		
		var page;
		if (typeof this.pages[pageName] != 'undefined') {
			page = this.pages[pageName];
		} else {
			var pageClass = 'MediaInfo.Page.' + pageName;
			page = eval('new ' + pageClass + '()');
			page.setManager(this);
		}
		
		page.show(options);
		this.onAfterShowPage();
		
		this._currentPageName = pageName;
		this._currentPage = page;
	},
	
	/**
	 * Get current page block id
	 * 
	 * @returns {String}
	 */
	getCurrentPageBlockId: function(){
		throw new Error(
			'MediaInfo.PageManager.getCurrentPageBlockId() have error.'
				+ 'For IE _currentPageName is invalid'
		);
		
		return 'page' + this._currentPageName.replace(
			new RegExp('([A-Z])', 'g'),
			function ($1) {
				return "-" + $1.toLowerCase();
			}
		);
	},
	
	/**
	 * Get current page element
	 * 
	 * @returns {HTMLElement}
	 */
	getCurrentPageBlock: function(){
		var blockSelector = '#' + this.getCurrentPageBlockId();
		if (!$(blockSelector).length) {
			throw new Error(
				'Page block not found. Id: ' + this.getCurrentPageBlockId()
			);
		}
		
		return $(blockSelector)[0];
	},
	
	/**
	 * Handler calls after the show page
	 * 
	 * @returns {Void}
	 */
	onAfterShowPage: function(){
		app.pageManagerQueue.onFuncComplete();
	},
	
	/**
	 * Get current page name
	 * 
	 * @returns {String}
	 */
	getCurrentPageName: function(){
		return this._currentPageName;
	},
	
	/**
	 * Is IE6?
	 * 
	 * @private
	 */
	isBrowserIe6: function(){
		return ($.browser.msie && $.browser.version.substr(0, 1) == '6');
	},
	
	/**
	 * Is test features enabled?
	 * 
	 * @private
	 */
	isTestFeaturesEnabled: function(){
		if (MediaInfo.Url.getUrlParam('test_features') == 1) {
			return true;
		}
		
		return !! MediaInfo.Cache.loadBin('test_features');
	},
	
	/**
	 * Show test features
	 * 
	 * @private
	 */
	showTestFeatures: function(){
		var _this = this;
		
		$('.test-features-block').show();
		$('.test-features-block .switch_to_image').submit(function(){
			var imageId = parseInt($('.test-features-block .image_id').val());
			if (imageId == 0) {
				return false;
			}
			
			var packedImageId = MediaInfo.IntPacker.pack(imageId);
			var randomImageHash = MediaInfo.IntPacker.generateRandomChars(32);
			_this.switchPage(packedImageId, randomImageHash, 'Ok');
			
			return false;
		});
	},
	
	/**
	 * Get current page
	 *
	 * @returns string
	 */
	getCurrentPage: function() {
		return this._currentPage;
	},
	
	/**
	 * Setup global error handler
	 *
	 * @returns {Void}
	 */
	setupErrorHandler: function(debugMode) {
		debugMode = !!debugMode;
		var _this = this;

		var errorSended = false;
		window.onerror = function(message, url, line) {
			var serverDataUrlPrefix = 'http://' + MediaInfo.Ajax.getServerHost() + '/widget/imageinfo';

			if (url.indexOf(serverDataUrlPrefix) == 0) {
				return true;
			}

			if (errorSended) {
				return true;
			}

			errorSended = true;

			try {
				var errorData = {
					'message': message,
					'url': url,
					'line': line
				};
				
				if (debugMode) {
					console.log(errorData);
				} else {
					MediaInfo.Ajax.ajax({
						url: '/widget/error',
						data: errorData
					});
				}
			} catch (e) {}

			return true;
		};
	}
});