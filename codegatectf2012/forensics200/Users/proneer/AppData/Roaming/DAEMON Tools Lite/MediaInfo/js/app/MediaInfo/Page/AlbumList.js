/**
 * @class MediaInfo.Page.AlbumList
 */
MediaInfo.Page.AlbumList = new Class(/** @lends MediaInfo.Page.AlbumList.prototype */{
	Extends: MediaInfo.Page.PageAbstract,

	/**
	 * Show page
	 * 
	 * @returns {Void}
	 */
	show: function() {
		var albumsData = app.imageInfoLoader.getInfo().data;

		var templateDirective = {
			'table.albums tr': {
				'album<-albums' : {
					'@class+':function(arg) {
						return (arg.pos % 2 == 0) ? ' gray' : ' white';
					},
					'td .title': 'album.title',
					'td label@onclick': 'album.selectAction',
					'input.radio_select@checked': 'album.checked'
				}
			},
			'.back@onclick': 'backAction'
	    };

		// Prepare template data
	    var templateData = {
    		albums:[],
    		backAction: "app.pageManager.showPage('Album');"
	    };
	    for (var i = 0; i < albumsData.length; i++) {
	    	templateData['albums'].push({
	    		'title': albumsData[i]['artist_name'] + ' - ' + albumsData[i]['album_name'],
	    		'selectAction': "app.pageManager.showPage('Album', {albumId: " +  albumsData[i]['album_id'] + "}); return false;",
	    		'checked': (albumsData[i]['album_id'] == MediaInfo.Page.Album.getSelectedAlbumId())? 'checked': false
	    	});
	    }

	    // Clean template
	    $('#page-album-list tr:not(:first-child)').remove();
	    // Render template
	    if (MediaInfo.Page.Album.getSelectedAlbumId()) {
	    	$('.back').addClass('showed');
	    }
	    this.renderPage('page-album-list', templateDirective, templateData);
		this.showPageBlock('page-album-list');
	}
});

