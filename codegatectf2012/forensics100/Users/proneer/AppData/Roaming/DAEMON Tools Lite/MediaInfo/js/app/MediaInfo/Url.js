/**
 * URL class
 * 
 * @class 
 */ 
MediaInfo.Url = {
	/**
	 * Open site with default browser
	 * 
	 * Returns false for simple add to "onclick"
	 * 
	 * @static
	 * @param {String} url
	 * @returns {False}
	 */
	openSite: function(url) {
		DTMediaInfo.OpenDefaultBrowser(url);
		
		return false;
	},
	
	/**
	 * Get value of URL param
	 * 
	 * @static
	 * @param {String} name
	 * @param {*} defaultValue
	 * @param {String} requestQuery
	 * @returns {*}
	 */
	getUrlParam: function(name, defaultValue, requestQuery) {
		requestQuery = typeof requestQuery != 'undefined'? requestQuery: location.search;
		
		var re = RegExp(name + '=' + '(.+?)(&|$)');
		var matches = re.exec(requestQuery);
		
		defaultValue = typeof defaultValue == 'undefined'? "": defaultValue;
		return (matches? decodeURI(matches[1]): defaultValue);
	},
	
	/**
	 * Add statistic page param
	 * 
	 * @param {String} url
	 * @param {String} pageName
	 * @returns {String}
	 */
	addStatParamPage: function(url, pageName) {
		url += ((url.indexOf('?') == -1)? '?': '&') + 'mswp=' + pageName;
		return url;
	},
	
	/**
	 * Add statistic link param
	 * 
	 * @param {String} url
	 * @param {String} linkName
	 * @returns {String}
	 */
	addStatParamLink: function(url, linkName) {
		url += ((url.indexOf('?') == -1)? '?': '&') + 'mswl=' + linkName;
		return url;
	},
	
	/**
	 * Add date link param
	 * 
	 * @param {String} url
	 * @returns {String}
	 */
	addStatParamDate: function(url) {
		var now = new Date();
		url += ((url.indexOf('?') == -1)? '?': '&') + 'mswd=' + now.toString('yyyy-MM-dd');
		return url;
	},
	
	/**
	 * Wrap URL with default browser JavaScript method
	 * 
	 * @param {String} url
	 * @returns {String}
	 */
	wrapWithOpenBrowser: function(url) {
		return "return MediaInfo.Url.openSite('" + url.replace("'", "\\'") + "');";
	}
};
