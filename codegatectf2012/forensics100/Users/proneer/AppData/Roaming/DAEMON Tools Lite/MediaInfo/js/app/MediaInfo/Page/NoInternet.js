/**
 * @class MediaInfo.Page.NoInternet
 */
MediaInfo.Page.NoInternet = new Class(/** @lends MediaInfo.Page.NoInternet.prototype */{
	Extends: MediaInfo.Page.PageAbstract,
	
	/**
	 * Show page
	 * 
	 * @returns {Void}
	 */
	show: function() {
		if (this.getManager().getCurrentPageName() == 'NoInternet') {
			return;
		}

		// Prepare template
		var templateDirective = {
			'a.logo_link@onclick': function() {
				return MediaInfo.Url.wrapWithOpenBrowser(
						MediaInfo.Url.addStatParamLink(this.homeUrl, 'logo')
				);
			},
			'.refresh_button@onclick': 'refreshImageAction'
		};

		// Prepare template data
	    var templateData = {
    		homeUrl: "http://" + MediaInfo.Ajax.getServerHost() + "/",
	    	refreshImageAction: "app.pageManager.refreshImagePage()"
	    };
		
	    this.renderPage('page-no-internet', templateDirective, templateData);
		this.showPageBlock('page-no-internet');
	}
});
