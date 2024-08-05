/**
 * @class MediaInfo.Page.Loading
 */
MediaInfo.Page.Loading = new Class(/** @lends MediaInfo.Page.Loading.prototype */{
	Extends: MediaInfo.Page.PageAbstract,
	
	/**
	 * Show page
	 * 
	 * @returns {Void}
	 */
	show: function() {
		if (this.getManager().getCurrentPageName() == 'Loading') {
			return;
		}
		
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

	    // Render page
	    this.renderPage('page-loading', templateDirective, templateData);
	    this.showPageBlock('page-loading');
	}
});
