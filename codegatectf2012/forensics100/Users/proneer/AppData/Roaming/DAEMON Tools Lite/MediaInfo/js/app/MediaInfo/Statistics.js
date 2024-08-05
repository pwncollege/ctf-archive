/**
 * @class {MediaInfo.Statistics}
 */
MediaInfo.Statistics = new Class(/** @lends MediaInfo.Statistics.prototype */{
	/**
	 * Cache key for store settings
	 */
	_settingsCacheKey: 'statistics',
	
	/**
	 * Send URL
	 */
	_sendUrl: '/widget/savestatistics',
	
	/**
	 * Get default settings
	 * 
	 * @returns {Object}
	 */
	getDefaultSettings: function(){
		return {
			nextSendDate: (new Date).toString(
				Date.CultureInfo.formatPatterns.sortableDateTime
			),
			data: {
				discitemSelection: {}
			}
		};
	},
	
	/**
	 * Get settings
	 * 
	 * @returns {Object}
	 */
	getSettings: function(){
		var cacheSettings = MediaInfo.Cache.load(this._settingsCacheKey) || {};
		
		return $.extend(
			this.getDefaultSettings(),
			cacheSettings
		);
	},
	
	/**
	 * Set settings
	 * 
	 * @param {Object} settings
	 * @returns {Void}
	 */
	setSettings: function(settings){
		settings = $.extend(
			this.getDefaultSettings(),
			settings
		);
		
		MediaInfo.Cache.save(this._settingsCacheKey, settings);
	},
	
	/**
	 * Send if sending scheduled
	 * 
	 * @returns {Void}
	 */
	sendScheduled: function(){
		var currentDate = new Date();
		var nextSendDate = Date.parse(this.getSettings().nextSendDate);
		if (currentDate.isBefore(nextSendDate)) {
			return;
		}
		
		this.send();
	},
	
	/**
	 * Send statistics
	 * 
	 * @returns {Void}
	 */
	send: function() {
		var _this = this;
		var data = this.getSettings().data;
		
		// Check statistics data
		var isStatisticsDataEmpty = true;
		$.each(data, function(index, value){
			if (!_this.isEmpty(value)) {
				isStatisticsDataEmpty = false;
				return false;
			}
		});
		
		if (isStatisticsDataEmpty) {
			this.updateNextSendDate();
			return;
		}
		
		// Send request
//		MediaInfo.Ajax.ajax({
//			url: this._sendUrl,
//			data: data,
//			success: function(){
//				_this.clean();
//				_this.updateNextSendDate();
//			},
//			error: function(){}
//		});
	},
	
	/**
	 * Is value empty?
	 * 
	 * @private
	 * @param mixed value
	 * @returns {Boolean}
	 */
	isEmpty: function(value) {
		if (typeof value == 'undefined') {
			return true;
		}
		
		if ($.isArray(value)) {
			return value.length == 0;
		}
		
		if (typeof value == 'object') {
			return $.isEmptyObject(value);
		}
		
		return false;
	},
	
	/**
	 * Update next send date
	 * 
	 * @private
	 * @returns {Void}
	 */
	updateNextSendDate: function() {
		var nextSendDate = new Date();
		nextSendDate.addDays(1);
		
		this.setSettings({
			nextSendDate: nextSendDate.toString(
				Date.CultureInfo.formatPatterns.sortableDateTime
			)
		});
	},
	
	/**
	 * Clean statistics
	 * 
	 * @returns {Void}
	 */
	clean: function() {
		this.setSettings({
			data: this.getSettings().data
		});
	},
	
	/**
	 * Set discitem selection
	 * 
	 * @param {String} imageHash
	 * @param {String} infoType
	 * @param {Number} discitemId
	 * @returns {Void}
	 */
	setDiscitemSelection: function(imageHash, typeinfo, discitemId){
		var data = this.getSettings().data;
		data.discitemSelection[imageHash] = {
			typeinfo: typeinfo,
			id: discitemId
		};
		
		this.setSettings({
			data: data
		});
	}
});
