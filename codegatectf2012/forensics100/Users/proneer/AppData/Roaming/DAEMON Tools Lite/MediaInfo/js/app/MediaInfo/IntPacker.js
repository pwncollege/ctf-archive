/**
 * @namespace IntPacker class
 */
MediaInfo.IntPacker = {
	/**
	 * Number base for convert
	 * 
	 * @private
	 * @type {Number}
	 */
	_convertBase: 36,
	
	/**
	 * Pack integer value
	 * 
	 * @param {Number} value
	 * @returns {String}
	 */
	pack: function(value) {
		var leftPart = this.generateRandomChars(4) + '-' + this.generateRandomChars(2);
		var rightPart = this.generateRandomChars(6);
		
        var convertedValue = this.baseConvert(value, 10, this._convertBase);
        return leftPart + convertedValue + '-' + rightPart;
	},
	
	/**
	 * Generate random chars
	 * 
	 * @return integer
	 */
	generateRandomChars: function(strLength){
		var randomChars = [];
		var charCodes;
		
		for (var i = 0; i < strLength; i++) {
			charCodes = [
				this.randomFromTo(48, 57),
			 	this.randomFromTo(65, 90),
		 		this.randomFromTo(97, 122)
			];
			
			randomChars.push(
				String.fromCharCode(charCodes[this.randomFromTo(0,2)])
			);
		}
		
		return randomChars.join('');
	},
	
	/**
	 * Generate random number beetwen numbers
	 * 
	 * @private
	 * @return integer
	 */
	randomFromTo: function(from, to){
		return Math.floor(Math.random() * (to - from + 1) + from);
	},
	
	/**
	 * Converts a number in a string from any base <= 36 to any base <= 36
	 * 
	 * See http://phpjs.org/functions/base_convert
	 *
	 * @private
	 * @return integer
	 */
	baseConvert: function(number, fromBase, toBase){
		return parseInt(number + '', fromBase | 0).toString(toBase | 0);
	}
};
