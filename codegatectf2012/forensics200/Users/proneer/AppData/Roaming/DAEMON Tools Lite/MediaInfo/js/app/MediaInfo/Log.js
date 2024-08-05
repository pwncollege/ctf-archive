/**
 * @namespace Log class
 */ 
MediaInfo.Log = {
	/**
	 * Cache key for log messages
	 * 
	 * @static
	 * @private
	 * @type {String}
	 */
	_cacheKey: 'log',
	
	/**
	 * Log message
	 * 
	 * @static
	 * @returns {Void}
	 */
	log: function() {
		// Prepare message
		var messages = [];
		for (var i = 0; i < arguments.length; i++) {
			messages.push(JSON.stringify(arguments[i]));
		}
		var message = messages.join(' ');
		
		// Truncate log data
		var logData = '' + DTMediaInfo.LoadCache(this._cacheKey);
		if (logData.length > 50000) {
			logData = '';
		}
		
		// Save log data + message
		logData += message + "\n";
		DTMediaInfo.SaveCache(this._cacheKey, logData);
	}
};

// Add "console.log" alias
if (typeof console == 'undefined') {
	window.console = {
		log: function(){
			return MediaInfo.Log.log.apply(
				MediaInfo.Log,
				Array.prototype.slice.call(arguments, 0)
			);
		}
	};
}
