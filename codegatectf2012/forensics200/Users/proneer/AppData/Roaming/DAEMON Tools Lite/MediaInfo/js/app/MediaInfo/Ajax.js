/**
 * @namespace AJAX class
 */ 
MediaInfo.Ajax = {
	/**
	 * Parameter for replace server host 
	 * 
	 */
	serverHostParamName: 'host',
	
	/**
	 * Parameter for AJAX responces
	 * 
	 */
	testReponsesParamName: 'ajax-responses',
	
	/**
	 * Requests info
	 * 
	 */
	_requestsInfo: {},
	
	/**
	 * Get server host
	 * 
	 * @returns {Void}
	 */
	getServerHost: function() {
		return MediaInfo.Url.getUrlParam(this.serverHostParamName, MediaInfo.Config.host);
	},
	
	/**
	 * Get test response list
	 * 
	 * @returns {Object}
	 */
	getTestResponses: function() {
		var ajaxResponsesParam = MediaInfo.Url.getUrlParam(this.testReponsesParamName);
		if (!ajaxResponsesParam) {
			return {};
		}
		
		var invalidParamMessagePrefix = 'Invalid "' + this.ajaxReponsesParamName + '" request parameter: ';
		try {
			var ajaxResponses = $.parseJSON(ajaxResponsesParam);
		} catch (e) {
			throw new Error(invalidParamMessagePrefix + e.message);
		}
		
		if (typeof ajaxResponses != 'object') {
			throw new Error(invalidParamMessagePrefix + 'required "object" type');
		}
		
		return ajaxResponses;
	},
	
	/**
	 * Get request info
	 * 
	 * @param {String} requestId
	 * @returns {Object|null}
	 */
	getRequestInfo: function(requestId){
		if (typeof this._requestsInfo[requestId] == 'undefined') {
			return null;
		}
		
		return this._requestsInfo[requestId];
	},

	/**
	 * Wrapper for jQuery.ajax()
	 * 
	 * Used test AJAX responses if exists
	 * 
	 * @param {Object|String} optionsOrUrl
	 * @returns {Void}
	 */
	ajax: function() {
		var options;
		
		if (arguments.length > 1) {
			options = arguments[1];
			options.url = arguments[0];
		} else {
			options = arguments[0];
		}
		
		// Apply default options
		options = $.extend(
			{
				timeout: 30000/*30 seconds*/ ,
				data: null,
				success: function(){},
				error: function(){}
			},
			options
		);
		
		// Add data to url
		if (options.data) {
			var separator = options.url.indexOf('?') == -1? '?': '&';
			var params = $.param(options.data);
			if (params) {
				options.url += separator + params;
			}
		}
		
		// Get request result from test responses
		var responses = this.getTestResponses();
		if (typeof responses[options.url] != 'undefined') {
			options.success(responses[options.url]);
			return;
		}
		
		// Add host
		options.url = 'http://' + this.getServerHost() + options.url;
		// Add param callback
		options.url = options.url
						+ (options.url.indexOf('?') === -1 ? '?': '&')
						+ 'callback=MediaInfo.Ajax.onAjaxCallback';
		
		// Create request complete callback
		var requestId = (new Date()).getTime() 
						+ ((Math.random() + Math.random()) + '').replace(new RegExp('\\.', 'g'), '');
		var requestCallbackName = 'MediaInfo_Ajax_Request_' + requestId;
		var requestCallbackSource =
			'window.' + requestCallbackName + ' = function(code, body, handlerrr){'
//				+ 'console.log("calback", code, body, handlerrr); '
				+ 'MediaInfo.Ajax.onAjaxComplete.call(MediaInfo.Ajax, code, body, handlerrr, "' + requestId + '");'
			+ '};';
		eval(requestCallbackSource);
		
		// Save request info
		this._requestsInfo[requestId] = {
			'success': options.success, 
			'error': options.error 
		};
		
//		console.log(options.url);
		
		
		// AJAX query
		handler = DTMediaInfo.HTTPAsyncRequest(options.url, options.timeout, requestCallbackName);
		
		// Save handler
		this._requestsInfo[requestId]['handler'] = handler;
	},
	
	/**
	 * On AJAX response eval
	 * 
	 * @param {*} data
	 * @returns {*}
	 */
	onAjaxCallback: function(data){
		return data;
	},
	
	/**
	 * On AJAX request complete
	 * 
	 * @param {Number} httpResponseCode
	 * @param {String} responseBody
	 * @param {*} handler
	 * @param {String} requestId
	 * @returns {Void}
	 */
	onAjaxComplete: function(httpResponseCode, responseBody, handler, requestId){
		var requestInfo = MediaInfo.Ajax.getRequestInfo(requestId);
		if (httpResponseCode >= 200 && httpResponseCode < 300) {
			var data = eval(responseBody);
			requestInfo.success(data);
		} else {
			requestInfo.error();
		}
	}
};
