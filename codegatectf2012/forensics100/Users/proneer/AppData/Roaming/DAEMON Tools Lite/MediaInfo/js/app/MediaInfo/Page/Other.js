/**
 * @class MediaInfo.Page.Other
 */
MediaInfo.Page.Other = new Class(/** @lends MediaInfo.Page.Other.prototype */{
	Extends: MediaInfo.Page.PageAbstract,

	/**
	 * Show page
	 * 
	 * @returns {Void}
	 */
	show: function(options) {
		var otherData = app.imageInfoLoader.getInfo().data;

		// Prepare template
		var templateDirective = {
				'a.game_name': 'otherName',
				'.description_game': 'otherDescription',
				'a.game_name@onclick': function() {
					return MediaInfo.Url.wrapWithOpenBrowser(
						MediaInfo.Url.addStatParamLink(this.otherUrl, 'title')
					);
				},
				'.see_more_button@onclick': function() {
					return MediaInfo.Url.wrapWithOpenBrowser(
						MediaInfo.Url.addStatParamLink(this.otherUrl, 'see_more')
					);
				},
				'.refresh_button@onclick': 'refreshImageAction'
		};

		// Prepare template data
		var templateData = {
			otherName: otherData['name'],
			otherDescription: otherData['description'],
			otherUrl: otherData['url'],
			refreshImageAction: "app.pageManager.refreshImagePage()"
		};

		// Render template
		this.renderPage('page-other', templateDirective, templateData);
		this.showPageBlock('page-other');
	}
});
