/**
 * @namespace Cache class
 */
MediaInfo.Cache = {
	/**
	 * Encode value to JSON and save cache
	 * 
	 * @param {String} key
	 * @param {*} value
	 * @param {Number} expiryDate Optional
	 * @returns {Void}
	 */
	save: function(key, value, expirySeconds) {
		if (expirySeconds) {
			// Save in expiry format
			
			var expiryDate = new Date();
			expiryDate.addSeconds(expirySeconds);
			value = {
				expiryMode: true,
				expiry: expiryDate.toString(
					Date.CultureInfo.formatPatterns.sortableDateTime
				),
				data: value
			};
		}
		
		value = JSON.stringify(value);
		this.saveBin(key, value);
	},
	
	/**
	 * Save binary
	 * 
	 * @param {String} key
	 * @param {*} value
	 * @param {Date} expiryDate Optional
	 * @returns {Void}
	 */
	saveBin: function(key, value) {
		DTMediaInfo.SaveCache(
			key,
			value
		);
	},
	
	/**
	 * Load cache and decode to JavaScript object
	 * 
	 * @param {String} key
	 * @param {Boolean} noExpiryCheck
	 * @returns {*}
	 */
	load: function(key, noExpiryCheck) {
		var value = this.loadBin(key);
		if (!value) {
			return null;
		}
		
		value = $.parseJSON(value);
		
		if (typeof value.expiryMode != 'undefined') {
			// Used expiry data format
			
			if (!noExpiryCheck) {
				// Check expiry date
				
				var now = new Date();
				var expiryDate = Date.parse(value.expiry);
				
				if (Date.compare(expiryDate, now) != -1) {
					value = value.data;
				} else {
					value = null;
				}
			} else {
				// Skip expiry check
				
				value = value.data;
			}
		}
		
		return value;
	},

	/**
	 * Load binary
	 * 
	 * @param {String} key
	 * @returns {*}
	 */
	loadBin: function(key) {
		return DTMediaInfo.LoadCache(key);
	}
};
