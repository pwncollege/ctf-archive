/**
 * @class MediaInfo.Page.Album
 */
MediaInfo.Page.Album = new Class(/** @lends MediaInfo.Page.Album.prototype */{
	Extends: MediaInfo.Page.PageAbstract,

	/**
	 * Show page
	 * 
	 * @param {Object} options Keys: albumId
	 * @returns {Void}
	 */
	show: function(options) {
		var albumId = options.albumId? options.albumId: null;
		
		// Save selection
		if (albumId) {
			var selectedAlbumId = MediaInfo.Page.Album.getSelectedAlbumId();
			if (albumId != selectedAlbumId) {
				MediaInfo.Page.Album.setSelectedAlbumId(albumId);
			}
		} else {
			albumId = MediaInfo.Page.Album.getSelectedAlbumId();
		}
		
		var imageInfo = app.imageInfoLoader.getInfo();
		
		// Select album data
		var albumData;
		for (var i in imageInfo.data) {
			if (imageInfo.data[i]['album_id'] == albumId) {
				albumData = imageInfo.data[i];
				break;
			}
		}
		if (!albumData) {
			throw new Error('Invalid selected album id');
		}

		// Prepare template
		var templateDirective = {
			'a.artist': 'artistName',
			'a.artist@onclick': function() {
				return MediaInfo.Url.wrapWithOpenBrowser(
					MediaInfo.Url.addStatParamLink(this.artistUrl, 'title')
				);
			},
			'a.album': 'albumName',
			'a.album@onclick': function() {
				return MediaInfo.Url.wrapWithOpenBrowser(
					MediaInfo.Url.addStatParamLink(this.albumUrl, 'title')
				);
			},
			'.genre': 'albumGenres',
			'.cover img.img_cover@src': 'albumPicture',
			'.cover img.img_cover@class+': 'albumPictureClass',
			'table.tracks tr': {
				'track<-albumTracks' : {
					'@class+':function(arg) {
						return (arg.pos % 2 == 0) ? ' gray' : ' white';
					},
					'td.name_song': 'track.title',
					'td.duration': 'track.duration'
				}
			},
			'.see_more_button@onclick': function() {
				return MediaInfo.Url.wrapWithOpenBrowser(
					MediaInfo.Url.addStatParamLink(this.albumUrl, 'see_more')
				);
			},
			'.select_albums@onclick': 'selectAlbumsAction',
			'.album_top_button@onclick': function() {
				return MediaInfo.Url.wrapWithOpenBrowser(
					MediaInfo.Url.addStatParamLink(this.albumsTopUrl, 'albums_top')
				);
			},
			'.refresh_button@onclick': 'refreshImageAction'
	    };

		// Prepare template data
	    var templateData = {
    		artistName: albumData['artist_name'],
    		artistUrl: albumData['artist_url'],
    		albumName: albumData['album_name'],
    		albumUrl: albumData['album_url'],
    		albumGenres: albumData['album_genres']? albumData['album_genres'].join(', '): '',
    		albumPictureClass: ' ' + (albumData['album_picture']? '': 'nocover_album'),
    		albumPicture: (albumData['album_picture']? this.getSrcByPictureBase64(albumData['album_picture']): this._imageStumbUrl),
    		albumTracks: [],
    		albumsTopUrl: albumData['albums_top_url'],
    		selectAlbumsAction: "app.pageManager.showPage('AlbumList'); return false;",
			refreshImageAction: "app.pageManager.refreshImagePage()"
	    };
	    for (var i = 0; i < albumData['listtracks'].length; i++) {
	    	templateData['albumTracks'].push({
	    		'title': (i + 1) + '. ' + albumData['listtracks'][i]['name'],
	    		'duration': albumData['listtracks'][i]['duration']
	    	});
	    }

	    // Render template
	    this.renderPage('page-album', templateDirective, templateData);
	    if (imageInfo.data.length > 1) {
	    	$('#page-album .select_albums').addClass('showed');
	    }
		this.showPageBlock('page-album');
		// IE fix
		calculateHeaderHeight($('#page-album'));
	}
});


/**
 * Set selected album id
 *
 * @static
 * @param {Number} albumId
 * @return {Void}
 */
MediaInfo.Page.Album.setSelectedAlbumId = function(albumId) {
	app.statistics.setDiscitemSelection(
		app.imageInfoLoader.getHash(),
		'audio',
		albumId
	);
	MediaInfo.Cache.save(
		'image_' + app.imageInfoLoader.getHash() + '_selected_album',
		albumId
	);
};

/**
 * Get selected album id
 *
 * @static
 * @return {Number}
 */
MediaInfo.Page.Album.getSelectedAlbumId = function() {
	return parseInt(MediaInfo.Cache.load('image_' + app.imageInfoLoader.getHash() + '_selected_album'));
};