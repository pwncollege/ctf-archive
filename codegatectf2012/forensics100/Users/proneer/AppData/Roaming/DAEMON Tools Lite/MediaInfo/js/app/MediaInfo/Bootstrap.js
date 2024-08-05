/**
 * Bootstrap
 * 
 * @class MediaInfo.Bootstrap
 */
MediaInfo.Bootstrap = new Class(/** @lends MediaInfo.Bootstrap# */{
	/**
	 * App namespace
	 * 
	 * @type Object
	 */
	app: {},
	
	/**
	 * Bootstrap application
	 * 
	 * @returns {Void}
	 */
	bootstrap: function(){
		var initMethod;
		var appProperty;
		var initPrefix = 'init';
		for (var property in this) {
			
			if (property.indexOf(initPrefix) !== 0) {
				continue;
			}
			
			
			initMethod = property;
			appProperty = initMethod.substr(initPrefix.length, 1).toLowerCase()
							+ initMethod.substr(initPrefix.length + 1);
			this.app[appProperty] = this[initMethod](this);
		}
		
		$.extend(window.app, this.app);
	},
	
	/**
	 * Bootstrap page manager
	 * 
	 * @returns {MediaInfo.PageManager}
	 */
	initPageManager: function(){
		var pageManager = new MediaInfo.PageManager();
		
		$(window).bind('load', function(){
			app.pageManager.onAppLoad();
		});
		
		return pageManager;
	},
	
	/**
	 * Bootstrap page queue
	 * 
	 * @returns {MediaInfo.PageManager.Queue}
	 */
	initPageManagerQueue: function(){
		var pageManagerQueue = new MediaInfo.PageManager.Queue();
		return pageManagerQueue;
	},
	
	/**
	 * Bootstrap image info loader
	 * 
	 * @returns {MediaInfo.ImageInfoLoader}
	 */
	initImageInfoLoader: function(){
		var imageInfoLoader = new MediaInfo.ImageInfoLoader();
		return imageInfoLoader;
	},
	
	/**
	 * Bootstrap translation
	 * 
	 * @returns {MediaInfo.Translation}
	 */
	initTranslation: function(){
		var translation = new MediaInfo.Translation();
		return translation;
	},
	
	/**
	 * Bootstrap statistics
	 * 
	 * @returns {MediaInfo.Statistics}
	 */
	initStatistics: function(){
		var statistics = new MediaInfo.Statistics();
		
		$(window).bind('load', function(){
			app.statistics.sendScheduled();
		});
		
		return statistics;
	}
});

var app = {
	/**
	 * Bootstrap
	 * 
	 * @type {MediaInfo.Bootstrap}
	 */
	bootstrap: null,
	
	/**
	 * Page manager
	 * 
	 * @type {MediaInfo.PageManager}
	 */
	pageManager: null,
	
	/**
	 * Page queue
	 * 
	 * @type {MediaInfo.PageManager.Queue}
	 */
	pageManagerQueue: null,
		
	/**
	 * Image info loader
	 * 
	 * @type {MediaInfo.ImageInfoLoader}
	 */
	imageInfoLoader: null,
	
	/**
	 * Translation
	 * 
	 * @type {MediaInfo.Translation}
	 */
	translation: null
};
app.bootstrap = new MediaInfo.Bootstrap();
app.bootstrap.bootstrap();

