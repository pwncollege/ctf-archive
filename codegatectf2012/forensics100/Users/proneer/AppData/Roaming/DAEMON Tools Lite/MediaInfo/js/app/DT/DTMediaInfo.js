/**
 * Test mode object for browser
 */
if (typeof DTMediaInfo == 'undefined') {
(function(){
	
/**
 * @namespace DT app functions
 */
var DTMediaInfo = {
	/**
	 * Cache memory storage
	 * 
	 * @static
	 * @private
	 * @type {Object}
	 */
	_cacheMemoryStorage: {}
};

/**
 * Open URL in default browser
 * 
 * @static
 * @param {String} url
 * @returns {Void}
 */
DTMediaInfo.OpenDefaultBrowser = function(url){
	window.open(url, 'DTMediaInfoDefaultBrowserWindow');
};

/**
 * Returns binary data in JavaScript string
 *
 * @static
 * @param {String} base64
 * @returns {String}
 */
DTMediaInfo.Base64Decode = function(base64){};

/**
 * Save cache
 * 
 * @static
 * @param {String} key
 * @param {String} data
 * @returns {Void}
 */
DTMediaInfo.SaveCache = function(key, data){
	console.log('Save "' + key + '" cache to memory');
	
	this._cacheMemoryStorage[key] = data;
};

/**
 * Load cache
 * 
 * @static
 * @param {String} key
 * @returns {String}
 */
DTMediaInfo.LoadCache = function(key){
	console.log('Load "' + key + '" cache from memory');
	
	if (typeof this._cacheMemoryStorage[key] == 'undefined') {
		return null;
	}
	
	return this._cacheMemoryStorage[key];
};

/**
 * Perform an asynchronous HTTP request
 * 
 * Returns request handler
 * Callback params: httpResponseCode, responseBody, handler
 * 
 * @static
 * @param {String} url
 * @param {Number} timeout
 * @param {String} callbackName
 * @returns {String}
 */
DTMediaInfo.HTTPAsyncRequest = function(url, timeout, callbackName){
	var re = new RegExp('\.', 'g');
	var handler = (Math.random() + '' + Math.random() + '' + Math.random()).replace(re, '');
	
	// Remove outer and add empty callback param
	var re = new RegExp('[&\?]?callback=([^&]+)');
	
	// Get original callback
	var m = url.match(re);
	if (typeof m[1] == 'undefined') {
		throw new Error('Required "callback" parameter for HTTP async request URL: ' + url);
	}
	var originalParamCallback = m[1];
	url = url.replace(re, '');
	url = url + (url.indexOf('?') === -1 ? '?': '&') + 'callback=?';
	
	var callComplete = function(jqXHR, data) {
		var responseBody = originalParamCallback + '(' + JSON.stringify(data) + ');';
		var callbackCallSource = callbackName + '(jqXHR.status, responseBody, handler);';
		eval(callbackCallSource);
	};
	
	$.ajax({
		'url': url,
		'method': 'GET',
		'timeout': timeout,
		'dataType': 'json',
		'crossDomain': true,
		'success': function(data, textStatus, jqXHR){
			callComplete(jqXHR, data);
		},
		'error': function(jqXHR){
			callComplete(jqXHR, '');
		}
	});
	
	return handler;
};

/**
 * Abort asynchronous HTTP request
 * 
 * @static
 * @param {String} handler
 * @returns {Void}
 */
DTMediaInfo.HTTPAsyncAbort = function(handler){};

window.DTMediaInfo = DTMediaInfo;

})();
}


