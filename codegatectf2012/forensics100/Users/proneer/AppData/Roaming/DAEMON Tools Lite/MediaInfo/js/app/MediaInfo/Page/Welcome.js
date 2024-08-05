/**
 * @class MediaInfo.Page.Welcome
 */
MediaInfo.Page.Welcome = new Class(/** @lends MediaInfo.Page.Welcome.prototype */{
	Extends: MediaInfo.Page.PageAbstract,
	
	/**
	 * Show page
	 * 
	 * @returns {Void}
	 */
	show: function() {
		var _this = this;
		
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
	    this.renderPage('page-welcome', templateDirective, templateData);
	    this.showPageBlock('page-welcome');
	    
	    
	    // Load TOPs
		app.imageInfoLoader.getTopsInfo({
			success: function(data){
				_this.renderWidgetTops(
					data,
					$('#page-welcome .tops_block'),
					'welcome_top'
				);
			}
		});
	}
});
