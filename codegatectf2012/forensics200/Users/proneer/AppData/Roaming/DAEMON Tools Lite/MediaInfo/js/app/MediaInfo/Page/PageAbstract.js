/**
 * @class MediaInfo.Page.PageAbstract
 */
MediaInfo.Page.PageAbstract = new Class(/** @lends MediaInfo.Page.PageAbstract.prototype */{
	/**
	 * URL to image stumb
	 *
	 * @private
	 */
	_imageStumbUrl: 'img/1.gif',

	/**
	 * Cache path
	 *
	 * @private
	 */
	_cachePath: '../ImageInfoCache',

	/**
	 * Page manager
	 *
	 * @type {MediaInfo.PageManager}
	 */
	_manager: null,

	/**
	 * Show page
	 *
	 * @abstract
	 * @returns {Void}
	 */
	show: function(){
		throw new Error('Called abstract method: show()');
	},

	/**
	 * Set manager
	 *
	 * @param {MediaInfo.PageManager} manager
	 * @returns {MediaInfo.Page.PageAbstract}
	 */
	setManager: function(manager){
		this._manager = manager;
		return this;
	},

	/**
	 * Get manager
	 *
	 * @returns {MediaInfo.PageManager}
	 */
	getManager: function(){
		return this._manager;
	},

	/**
	 * Show page block
	 *
	 * @param {String} blockId
	 * @returns {Void}
	 */
	showPageBlock: function(blockId) {
		$('body').attr('class', blockId);
		$('.page').removeClass('showed');
		$('#' + blockId).addClass('showed');
	},

	/**
	 * Render page
	 *
	 * @param {String} containerId
	 * @param {Object} directives
	 * @param {*} data
	 * @returns {Void}
	 */
	renderPage: function(containerId, directives, data) {
		if ($('#' + containerId).length < 1) {
			throw new Error('Container with id "' + containerId + '" not found');
		}

		var templateId = containerId + '-template';
		if ($('#' + templateId).length < 1) {
			throw new Error('Template with id "' + templateId + '" not found');
		}

		var templateSelector = '#' + templateId + ' .main_div';
		if ($(templateSelector).length < 1) {
			throw new Error('Template wrapper not found: ' + templateSelector);
		}

		this.render(
			templateSelector,
			'#' + containerId,
			directives,
			data
		);
	},

	/**
	 * Render template
	 *
	 * @param {HTMLElement|String} template
	 * @param {HTMLElement|String} container
	 * @param {Object} directives
	 * @param {*} data
	 * @returns {Void}
	 */
	render: function(template, container, directives, data){
		var generator = $(template).compile(directives);
	    $(container).html(
	    	generator(data)
		);
	},

	/**
	 * Get <img /> "src" attribute by picture base64
	 *
	 * For IE6 used URL
	 *
	 * @param {String} pictureBase64
	 * @returns {String}
	 */
	getSrcByPictureBase64: function(pictureBase64) {
		var src;
		if( this.getManager().isBrowserIe6() ) {
			var pictureFile = 'pic_' + MD5(pictureBase64.substring(0, 1000));
			if( ! MediaInfo.Cache.loadBin( pictureFile ) ) {
				pictureBin = DTMediaInfo.Base64Decode(pictureBase64);
				lastSymbol = pictureBin.charAt( pictureBin.length - 1 );
				if( lastSymbol.charCodeAt(0) != 217 ) {
					pictureBin += String.fromCharCode( 217 );
				}
				MediaInfo.Cache.saveBin(pictureFile, pictureBin);
			}
			src = this._cachePath + '/' + pictureFile + '.dat';
		} else {
			src = 'data:image/jpeg;base64,' + pictureBase64;
		}
		return src;
	},

	// Widgets

	/**
	 * Render "tops" widget
	 *
	 * @param {Object} info
	 * @param {HTMLElement|String} container
	 * @param {String} linksStatPage
	 * @returns {Void}
	 */
	renderWidgetTops: function(info, container, linksStatPage, additionalHideCorrection){
		var _this = this;

		var templateDirective = {
			'div.top': {
				'info<-topsInfo': {
					'.top_title@class+': function(){
						return ' ' + this.blockClass;
					},
					'.title': 'info.title',
					'.view_all@onclick': function() {
						return MediaInfo.Url.wrapWithOpenBrowser(
							MediaInfo.Url.addStatParamLink(this.viewAllUrl, linksStatPage)
						);
					},
					'ol li': {
						'topItem<-info.items': {
							'a@onclick': function() {
								return MediaInfo.Url.wrapWithOpenBrowser(
									MediaInfo.Url.addStatParamLink(this.url, linksStatPage)
								);
							},
							'a': 'topItem.name'
						}
					}
				}
			}
		};
		var templateData = {
    		topsInfo: []
	  };

	  if( typeof info.games != "undefined" ) {
	  	templateData.topsInfo.push(
	  		{
					'blockClass': 'games',
					'title': app.translation.translate('top_title_games') + ' →',
					'viewAllUrl': info.games.viewAllUrl,
					'items': info.games.items
	  		}
	  	);
	  }

	  if( typeof info.audio != "undefined" ) {
	  	templateData.topsInfo.push(
	  		{
	        	   'blockClass': 'music',
	        	   'title': app.translation.translate('top_title_music') + ' →',
	        	   'viewAllUrl': info.audio.viewAllUrl,
	        	   'items': info.audio.items
	  		}
	  	);
	  }

		// Hide items for window size
		var topBlocksCount = templateData.topsInfo.length;
		// Get max item count
		var maxItemCount = 0;
		var topInfo;
		for (var i = 0; i < topBlocksCount; i++) {
			topInfo = templateData.topsInfo[i];
			if (maxItemCount > topInfo.items.length) {
				continue;
			}
			maxItemCount = topInfo.items.length;
		}

		// Resize event
		var WidgetTopsResizeTimer;
		$(window).resize(function() {
			if (WidgetTopsResizeTimer) {
				clearTimeout(WidgetTopsResizeTimer);
			}
			WidgetTopsResizeTimer = setTimeout(function(){
				_this.widgetTopsResize(container, topBlocksCount, maxItemCount, additionalHideCorrection );
			}, 300);
		});

		this.render(
			'#widget-tops-template .container',
			container,
			templateDirective,
			templateData
		);

		// Hide items
		this.widgetTopsResize(container, topBlocksCount, maxItemCount, additionalHideCorrection);
	},

	/**
	 * Resize TOP blocks
	 *
	 * @param {HTMLElement|String} context
	 * @param {Number} topBlocksCount
	 * @param {Number} maxItemCount
	 * @returns
	 */
	widgetTopsResize: function(context, topBlocksCount, maxItemCount, additionalHideCorrection ){
		context = $(context);
		additionalHideCorrection = additionalHideCorrection || 0;

		// Options
		var minItemsCount = 3;
		var itemHeight = 20;
		var additionalVerticalIdent = 53 + additionalHideCorrection;
		var maxItemsListHeight = itemHeight * maxItemCount;

		// Calc
		var maxTopsHeight = $(window).height() - context.position().top;
		var maxTopBlockHeight = Math.floor(maxTopsHeight / topBlocksCount);
		var maxItemsListForWindowHeight = maxTopBlockHeight - additionalVerticalIdent;
		var itemsCount = Math.floor(maxItemsListForWindowHeight / itemHeight);
		itemsCount = itemsCount < minItemsCount? minItemsCount: itemsCount;
		var itemsListHeight = itemHeight * itemsCount;
		itemsListHeight = itemsListHeight > maxItemsListHeight? maxItemsListHeight: itemsListHeight;


		// Hide
		$('div.top ol', context).height(itemsListHeight);
	}
});