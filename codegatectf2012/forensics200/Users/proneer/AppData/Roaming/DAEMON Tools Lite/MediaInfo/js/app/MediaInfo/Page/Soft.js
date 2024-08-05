/**
 * @class MediaInfo.Page.Soft
 */
MediaInfo.Page.Soft = new Class({
	Extends: MediaInfo.Page.PageAbstract,

	/**
	 * Show page
	 * 
	 * @returns {Void}
	 */
	show: function(options) {
		var softData = app.imageInfoLoader.getInfo().data;

		// Prepare template
		var templateDirective = {
			'a.game_name': 'softName',
			'.description_game': 'softSummary',
			'a.game_name@onclick': function() {
				return MediaInfo.Url.wrapWithOpenBrowser(
					MediaInfo.Url.addStatParamLink(this.softUrl, 'title')
				);
			},
			'.genre': 'softGenres',
			'.cover img.img_cover@src': 'softPicture',
			'.cover img.img_cover@class+': 'softPictureClass',
			'.see_more_button@onclick': function() {
				return MediaInfo.Url.wrapWithOpenBrowser(
					MediaInfo.Url.addStatParamLink(this.softUrl, 'see_more')
				);
			},
//			'.soft_top_button@onclick': function() {
//				return MediaInfo.Url.wrapWithOpenBrowser(
//					MediaInfo.Url.addStatParamLink(this.softTopUrl, 'soft_top')
//				);
//			},
			'.description_features .inf': {
				'feature<-softFeatures' : {
					'.': 'feature'
				}
			},
			'.refresh_button@onclick': 'refreshImageAction'
	    };

		// Prepare template data
		// Features
		var features = [];
		if (softData['operation_systems']) {
			features.push(app.translation.translate('feature_label_os') + ' ' + softData['operation_systems']);
		}
		if (softData['size']) {
			features.push(app.translation.translate('feature_label_size') + ' ' + softData['size']);
		}
		if (softData['official_site']) {
			features.push(
				app.translation.translate('feature_label_official_site') + ' '
					+ '<a href="#" onclick="' + MediaInfo.Url.wrapWithOpenBrowser(softData['official_site']) + '">'
						+ softData['official_site']
					+ '</a>'
			);
		}
		if (softData['license']) {
			features.push(app.translation.translate('feature_label_license') + ' ' + softData['license']);
		}

	    var templateData = {
    		softName: softData['name'],
    		softSummary: softData['summary'],
    		softUrl: softData['url'],
    		softGenres: softData['genres']? softData['genres'].join(', '): '',
			softPictureClass: ' ' + (softData['picture']? '': 'nocover_soft'),
    		softPicture: softData['picture']? this.getSrcByPictureBase64(softData['picture']): this._imageStumbUrl,
    		softTopUrl: softData['soft_top_url'],
    		softFeatures: features,
    		refreshImageAction: "app.pageManager.refreshImagePage()"
	    };

	    // Render template
	    this.renderPage('page-soft', templateDirective, templateData);
		this.showPageBlock('page-soft');
		if (features.length) {
			$('#page-soft .features_block').show();
		} else {
			$('#page-soft .features_block').hide();
		}
		if (softData['summary'].length) {
			$('#page-soft .description_block').show();
		} else {
			$('#page-soft .description_block').hide();
		}

		// IE fix
		calculateHeaderHeight($('#page-soft'));
	}
});
