/**
 * @class MediaInfo.Page.FileNotFound
 */
MediaInfo.Page.FileNotFound = new Class(/** @lends MediaInfo.Page.FileNotFound.prototype */{
	Extends: MediaInfo.Page.PageAbstract,

	/**
	 * Show page
	 * 
	 * @returns {Void}
	 */
	show: function() {
		// Prepare template
		var templateDirective = {
			'a.logo_link@onclick': function() {
				return MediaInfo.Url.wrapWithOpenBrowser(
					MediaInfo.Url.addStatParamLink(this.homeUrl, 'logo')
				);
			}
	    };
		
		// Prepare template data
		var templateData = {
    		homeUrl: "http://" + MediaInfo.Ajax.getServerHost() + "/"
	    };


		// Render template
		this.renderPage('page-file-not-found', templateDirective, templateData);
		this.showPageBlock('page-file-not-found');
	}
});
