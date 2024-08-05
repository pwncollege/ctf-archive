/**
 * Queue for page showing
 * 
 * @class MediaInfo.PageManager.Queue
 */
MediaInfo.PageManager.Queue = new Class({
	/**
	 * Function list
	 * 
	 * @private
	 * @type {Array}
	 */
	_funcs: [],
	
	/**
	 * Is function runned?
	 * 
	 * @private
	 * @type {Boolean}
	 */
	_isFuncRunned: false,
	
	/**
	 * Run last function
	 * 
	 * @private
	 * @returns {Void}
	 */
	runLast: function() {
		var _this = this;
		var lastFunc = this._funcs.pop();
		if (!lastFunc) {
			return;
		}
		
		this._isFuncRunned = true;
		this._funcs = [];
		setTimeout(function(){
			lastFunc.call(_this);
		}, 100);
	},
	
	/**
	 * Run function in turn
	 * 
	 * @param {Function} func
	 * @returns {Void}
	 */
	run: function(func) {
		this._funcs.push(func);
		
		if (this._isFuncRunned) {
			return;
		}

		this.runLast();
	},
	
	/**
	 * On function complete
	 * 
	 * @returns {Void}
	 */
	onFuncComplete: function() {
		this._isFuncRunned = false;
		this.runLast();
	}
});