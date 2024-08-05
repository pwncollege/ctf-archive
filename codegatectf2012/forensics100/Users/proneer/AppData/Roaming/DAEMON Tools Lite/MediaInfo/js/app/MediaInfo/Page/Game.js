/**
 * @class MediaInfo.Page.Game
 */
MediaInfo.Page.Game = new Class(/** @lends MediaInfo.Page.Game.prototype */{
	Extends: MediaInfo.Page.PageAbstract,
	
	/**
	 * Show page
	 * 
	 * @returns {Void}
	 */
	show: function() {
		var gameData = app.imageInfoLoader.getInfo().data;
		
		// Prepare template
		var templateDirective = {
			'a.game_name': 'gameName',
			'.description_game': 'gameDescription',
			'a.game_name@onclick': function() {
				return MediaInfo.Url.wrapWithOpenBrowser(
					MediaInfo.Url.addStatParamLink(this.gameUrl, 'title')
				);
			},
			'.genre': 'gameGenres',
			'.cover img.img_cover@src': 'gamePicture',
			'.cover img.img_cover@class+': 'gamePictureClass',
			'.see_more_button@onclick': function() {
				return MediaInfo.Url.wrapWithOpenBrowser(
					MediaInfo.Url.addStatParamLink(this.gameUrl, 'see_more')
				);
			},
			'.games_top_button@onclick': function() {
				return MediaInfo.Url.wrapWithOpenBrowser(
					MediaInfo.Url.addStatParamLink(this.gamesTopUrl, 'games_top')
				);
			},
			'.description_features .inf': {
				'feature<-gameFeatures' : {
					'.': 'feature'
				}
			},
			'.refresh_button@onclick': 'refreshImageAction'
	    };
		
		// Prepare template data
		// Features
		var features = [];
		if (gameData['publisher']) {
			var value;
			if (gameData['publisher']['url']) {
				value = '<a href="#" onclick="' + MediaInfo.Url.wrapWithOpenBrowser(gameData['publisher']['url']) + '">'
							+ gameData['publisher']['name']
						+ '</a>';
			} else {
				value = gameData['publisher']['name'];
			}
			features.push(
				app.translation.translate('feature_label_publisher') + ' ' + value
			);
		}
		if (gameData['developer']) {
			var value;
			if (gameData['developer']['url']) {
				value = '<a href="#" onclick="' + MediaInfo.Url.wrapWithOpenBrowser(gameData['developer']['url']) + '">'
						+ gameData['developer']['name']
						+ '</a>';
			} else {
				value = gameData['developer']['name'];
			}
			features.push(
				app.translation.translate('feature_label_developer') + ' ' + value
			);
		}
		if (gameData['official_site']) {
			features.push(
					app.translation.translate('feature_label_official_site') + ' '
					+ '<a href="#" onclick="' + MediaInfo.Url.wrapWithOpenBrowser(gameData['official_site']) +'">'
						+ gameData['official_site']
					+ '</a>'
			);
		}
		
	    var templateData = {
    		gameName: gameData['name'],
    		gameDescription: gameData['description'],
    		gameUrl: gameData['url'],
    		gameGenres: gameData['genres']? gameData['genres'].join(', '): '',
    		gamePictureClass: ' ' + (gameData['picture']? '': 'nocover_game'),
    		gamePicture: gameData['picture']? this.getSrcByPictureBase64(gameData['picture']): this._imageStumbUrl,
    		gamesTopUrl: gameData['games_top_url'],
    		gameFeatures: features,
    		refreshImageAction: "app.pageManager.refreshImagePage()"
	    };

	    // Render template
	    this.renderPage('page-game', templateDirective, templateData);
		this.showPageBlock('page-game');
		
		if (features.length) {
			$('#page-game .features_block').show();
		} else {
			$('#page-game .features_block').hide();
		}
	    if (gameData['description'].length) {
	    	$('#page-game .description_block').show();
	    } else {
	    	$('#page-game .description_block').hide();
	    }
	    
		// IE fix
		calculateHeaderHeight($('#page-game'));
	}
});
