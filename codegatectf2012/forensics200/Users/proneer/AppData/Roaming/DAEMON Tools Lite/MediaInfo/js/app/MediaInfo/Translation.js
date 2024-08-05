/**
 * @class MediaInfo.Translation
 */
MediaInfo.Translation = new Class(/** @lends MediaInfo.Translation.prototype */{
	/**
	 * Default language abbreviation
	 * 
	 * @private
	 * @type {String}
	 */
	defaultLanguageAb: 'eng',
	
	/**
	 * Get language abbreviation by windows hex LCID
	 * 
	 * @param {String} windowsLcid
	 * @return {String}
	 */
	getAbByLcid: function(windowsLcid) {
		for (var i in this.languages) {
			if (this.languages[i].windowsLcid == windowsLcid) {
				return this.languages[i].ab;
			}
		}
		
		return this.defaultLanguageAb;
	},
	
	/**
	 * Translate phrase
	 * 
	 * If translate not found returns empty string
	 * 
	 * @param {String} phrase
	 * @return {String}
	 */
	translate: function(phrase) {
		var translateMethod = 'translatePhrase_' + phrase;
		if (typeof this[translateMethod] != 'undefined') {
			return this[translateMethod](phrase);
		}
		
		return this.translateByDict(phrase);
	},
	
	/**
	 * Translate phrase by dictionary
	 * 
	 * If translate not found returns empty string
	 * 
	 * @param {String} phrase
	 * @return {String}
	 */
	translateByDict: function(phrase) {
		phraseWithPrefix = 'mswidget_' + phrase;
		if (typeof MediaInfo.Translation.translations[phraseWithPrefix] == 'undefined') {
			console.log('Error: translation not found. Phrase: ' + phrase + ' System phrase: ' + phraseWithPrefix);
			return '';
		}
		
		return MediaInfo.Translation.translations[phraseWithPrefix];
	},
	
	/**
	 * Translate page
	 * 
	 * @param {HTMLElement} 
	 * @return {Void}
	 */
	translatePage: function(rootElement) {
		var _this = this;
		var phraseConstantPrefix = 'tconstant_';
		
		$('.translate', rootElement).each(function(index, element){
			var phrase;
			
			element = $(element);
			
			if (element.attr('class').indexOf(phraseConstantPrefix) == -1) {
				if (element.html() == '') {
					
				}
				phrase = element.html();
			} else {
				var re = new RegExp(phraseConstantPrefix + '(\\S+)');
				phrase = element.attr('class').match(re)[1];
			}
			
			element.html(
				_this.translate(phrase)
			);
		});
	},
	
	/**
	 * Translate methods
	 */
	
	/**
	 * Translate "no_internet_connection" phrase
	 * 
	 * @param {String} phrase
	 * @return {String}
	 */
	translatePhrase_no_internet_connection: function(phrase){
		var siteUrl = 'http://' + MediaInfo.Ajax.getServerHost() + '/';
		return this.translateByDict(phrase).replace(
			'%s',
			'<a href="#" onclick="' + MediaInfo.Url.wrapWithOpenBrowser(siteUrl) + '">'
				+ MediaInfo.Ajax.getServerHost()
				+ '</a>'
		);
	}
});

/**
 * Available translations
 * 
 * @static
 * @type {Object}
 */
MediaInfo.Translation.translations = {};

/**
 * Available languages
 *
 * @static
 * @type {Array}
 */
MediaInfo.Translation.languages = [];
