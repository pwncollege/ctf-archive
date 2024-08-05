/**
 * @class MediaInfo.Page.InvalidImage
 */
MediaInfo.Page.InvalidImage = new Class(/** @lends MediaInfo.Page.InvalidImage.prototype */{
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
		this.renderPage('page-invalid-image', templateDirective, templateData);
		this.showPageBlock('page-invalid-image');
	}
});
