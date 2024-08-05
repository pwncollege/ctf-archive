/**
 * @class MediaInfo.ImageInfoLoader
 */
MediaInfo.ImageInfoLoader = new Class(/** @lends MediaInfo.ImageInfoLoader.prototype */{
	/**
	 * Image hash
	 *
	 * @private
	 * @type {String}
	 */
	_hash: null,
	
	/**
	 * Image info
	 *
	 * @private
	 * @type {Object}
	 */
	_info: null,

	/**
	 * Cache expiry for image data in seconds
	 *
	 * @private
	 * @type {Object}
	 */
	_imageCacheTimeouts: {
		'game': 604800 /*7 days*/,
		'audio': 1209600 /*14 days*/,
		'soft': 604800 /*7 days*/,
		'other': 1209600 /*14 days*/,
		'not-found': 1209600 /*14 days*/
	},
	
	/**
	 * Cache expiry for TOPs info
	 *
	 * @private
	 * @type {Number}
	 */
	_topsInfoTimeout: 86400,
	
	/**
	 * Get cache timeout
	 * 
	 * @param {String} type
	 * @returns {Number}
	 */
	getCacheTimeout: function(type){
		return this._imageCacheTimeouts[type];
	},
	
	/**
	 * Get image cache key
	 *
	 * @param {String} imageHash
	 * @returns {String}
	 */
	getImageCacheKey: function(imageHash) {
		return 'image_' + imageHash;
	},
	
	/**
	 * Load image info
	 *
	 * @param {Object} options Options: callback, imageHash, imageId(optional)
	 * @returns {Void}
	 */
	loadInfo: function(options) {
		var _this = this;

		// Add default options
		options = $.extend({
			imageId: null
		}, options);

		// Load from cache
		var imageCacheKey = this.getImageCacheKey(options.imageHash);
		// Try load from cache
		var imageData = MediaInfo.Cache.load(imageCacheKey);
		if (imageData) {
			this._hash = options.imageHash;
			this._info = imageData;
			options.success.call(this, imageData);
			return;
		}

		// Cache miss, load from server 
		this.loadImageDataFromServer({
			imageId: options.imageId,
			imageHash: options.imageHash,
			success: function(imageData) {
				MediaInfo.Cache.save(
					imageCacheKey,
					imageData,
					_this._imageCacheTimeouts[imageData.typeinfo]
				);

				_this._hash = options.imageHash;
				_this._info = imageData;
				options.success.call(this, imageData);
			},
			error: function() {
				oldImageData = MediaInfo.Cache.load(imageCacheKey, true);
				if (oldImageData) {
					_this._hash = options.imageHash;
					_this._info = oldImageData;
					options.success.call(this, oldImageData);
				} else {
					_this._hash = options.imageHash;
					_this._info = null;
					options.error.call(this, oldImageData);
				}
			}
		});
	},
	
	/**
	 * Reload image info
	 *
	 * @param {Object} options Options: callback, imageHash, imageId(optional)
	 * @returns {Void}
	 */
	reloadInfo: function(options) {
		var _this = this;

		var imageCacheKey = _this.getImageCacheKey(_this._hash);
		
		app.imageInfoLoader.loadImageDataFromServer({
			imageHash: this._hash,
			success: function(imageData) {
				MediaInfo.Cache.save(
					imageCacheKey,
					imageData,
					_this._imageCacheTimeouts[imageData.typeinfo]
				);

				_this._info = imageData;
				options.success.call(_this, imageData);
			},
			error: function() {
				var oldImageData;
				if (_this._hash) {
					oldImageData = MediaInfo.Cache.load(imageCacheKey, true);
					_this._info = oldImageData;
				}
				
				options.error.call(_this, oldImageData);
			}
		});
	},
	
	/**
	 * Load image data from server
	 *
	 * @param {Object} options Options: success, error, imageHash, imageId(optional)
	 * @returns {Void}
	 */
	loadImageDataFromServer: function(options) {
		var _this = this;

		// Add default options
		options = $.extend({
			imageId: null
		}, options);

		// Load from server
		var infoUrl = '/widget/imageinfo/' + encodeURI(options.imageId? options.imageId: options.imageHash);
		MediaInfo.Ajax.ajax({
			url: infoUrl,
			data: {
				'language': MediaInfo.Config.languageAb
			},
			success: options.success,
			error: options.error
		});
	},
	
	/**
	 * Get image info
	 * 
	 * @returns {Object}
	 */
	getInfo: function(){
		if (this._info === null) {
			throw new Error('Image info not loaded');
		}
		
		return this._info;
	},
	
	/**
	 * Get hash
	 * 
	 * @returns {String}
	 */
	getHash: function(){
		if (this._hash === null) {
			throw new Error('Image hash not set');
		}
		
		return this._hash;
	},
	
	/**
	 * Get TOPs info
	 * 
	 * @param {Object} options
	 * @returns {Void}
	 */
	getTopsInfo: function(options){
		var _this = this;
		var topsInfoCacheKey = 'tops_info';
		
		// Add default options
		options = $.extend({
			error: function(){}
		}, options);
		
		// Load from cache
		var topsInfo = MediaInfo.Cache.load(topsInfoCacheKey);
		if (topsInfo) {
			options.success.call(this, topsInfo);
			return; 
		}
		
		// Cache miss, load from server
		var infoUrl = '/widget/gettops';
		MediaInfo.Ajax.ajax({
			url: infoUrl,
			data: {
				'language': MediaInfo.Config.languageAb
			},
			success: function(data){
				MediaInfo.Cache.save(topsInfoCacheKey, data, _this._topsInfoTimeout);
				options.success.call(_this, data);
			},
			error: options.error
		});
	}
});