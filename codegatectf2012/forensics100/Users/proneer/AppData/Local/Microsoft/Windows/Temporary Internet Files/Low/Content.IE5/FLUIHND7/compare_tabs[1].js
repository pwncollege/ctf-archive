var tabs = function(id, currentTab) {
	this.id = id;
	this.$tabs = $('#'+this.id);
	this.items = [];
	if(currentTab != undefined)
		this.currentTab = currentTab;
	else
		this.currentTab = 0;
	this.tabs = '';
	this.tabScreenWidth = 0;
	this.$buttBack = '';
	this.$buttNext = '';
	this.allTabsWidth = 0;
	this.$wrapInner = '';
	this.arrowButtonsWidth = 27;
	this.currentBackPoint = this.currentTab;
	this.currentNextPoint = -1;
}
tabs.prototype.addElement = function(obj) {
	this.items[this.items.length] = obj;
}
tabs.prototype.scrollBack = function(speed) {

	if(this.currentBackPoint>0) {
		this.currentBackPoint--;
		this.$wrapInner.animate({'left':this.items[this.currentBackPoint]['backPoint']+'px'}, speed, "linear");
		this.currentNextPoint--;
		this.countTabVisibility();
		this.countArrowsState();
	}
}
tabs.prototype.scrollNext = function(speed) {
	if(this.currentNextPoint<=this.items.length-1) {
		this.$wrapInner.animate({'left':this.items[this.currentNextPoint]['nextPoint']+'px'}, speed, "linear");
		this.currentNextPoint++;
		this.currentBackPoint++;
		this.countTabVisibility();
		this.countArrowsState();
	}
}
tabs.prototype.setBackPoints = function() {
	var w = 0;
	w = w+this.arrowButtonsWidth;
	for(var i = 0; i< this.items.length; i++) {
		this.items[i]['backPoint'] = w;
		w = w-this.items[i]['width'];
	}
}
tabs.prototype.setNextPoints = function() {
	var w = this.tabScreenWidth - (this.allTabsWidth+this.arrowButtonsWidth);
	for(var i = (this.items.length-1); i>= 0; i--) {
		this.items[i]['nextPoint'] = w;
		w = w+this.items[i]['width'];
	}
}
tabs.prototype.countCurrNextPoint = function() {
	var l = 0;
	for(var i = this.currentBackPoint; i<this.items.length; i++) {
		l +=this.items[i]['width'];
		if(l>=this.tabScreenWidth) {
			this.currentNextPoint = i;
			break;
		} else {
			this.currentNextPoint = this.items.length-1;
		}
	}
}
tabs.prototype.countTabVisibility = function() {
	//alert('this.currentBackPoint = '+this.currentBackPoint+'    this.currentNextPoint = '+this.currentNextPoint);
	for(var i = 0; i< this.items.length; i++) {
		this.items[i]['visibility'] = 0;
		//alert('i = '+i+'\nthis.currentBackPoint = '+this.currentBackPoint+'\nthis.currentNextPoint = '+this.currentNextPoint);
		if(i<this.currentBackPoint) {
			this.items[i]['visibility'] = -1;
		}
		if(i>=this.currentNextPoint) {
			this.items[i]['visibility'] = 1;
		}
		//alert('visibility = '+this.items[i]['visibility']);
	}
}
tabs.prototype.countArrowsState = function() {
	if(this.currentBackPoint>0) {
		if(this.$buttBack.hasClass('disableBackButt')) {
			this.$buttBack.removeClass('disableBackButt');
		}
	} else if (this.currentBackPoint==0) {
		if(!this.$buttBack.hasClass('disableBackButt')) {
			this.$buttBack.addClass('disableBackButt');
		}
	}
	if(this.currentNextPoint<=this.items.length-1) {
		if(this.$buttNext.hasClass('disableNextButt')) {
			this.$buttNext.removeClass('disableNextButt');
		}
	} else if (this.currentNextPoint==this.items.length) {
		if(!this.$buttNext.hasClass('disableNextButt')) {
			this.$buttNext.addClass('disableNextButt');
		}
	}
}
tabs.prototype.setPoints = function() {
	this.setBackPoints();
	this.setNextPoints();
	this.countCurrNextPoint();
}
tabs.prototype.showIfHidden = function (n) {
			if(this.items[n]['visibility']==-1) {
				this.scrollBack();
			} else if(this.items[n]['visibility']==1) {
				this.scrollNext();
			}
}
tabs.prototype.createScrolledTabs = function(obj) {
	this.setPoints();
	this.$wrapInner.css({'width': this.allTabsWidth+'px', 'position':'absolute', 'left':this.items[this.currentTab]['backPoint']});
	this.$tabs.css({width:this.tabScreenWidth+'px'})
	this.$buttBack = $('<div class="tabsArrowBack" onDblclick="return false;"></div>');
	this.$buttNext = $('<div class="tabsArrowNext" onDblclick="return false;"></div>');
	this.$buttBack.click(function(t){return function(){t.scrollBack(200);}}(this));
	this.$buttBack.mouseover (function($b){return function(){$b.addClass('butBackHover');}}(this.$buttBack));
	this.$buttBack.mouseout (function($b){return function(){$b.removeClass('butBackHover');}}(this.$buttBack));
	this.$buttNext.click(function(t){return function(){t.scrollNext(200);}}(this));
	this.$buttNext.mouseover (function($b){return function(){$b.addClass('butNextHover');}}(this.$buttNext));
	this.$buttNext.mouseout (function($b){return function(){$b.removeClass('butNextHover');}}(this.$buttNext));
	this.$tabs.append(this.$buttBack);
	this.$tabs.append(this.$buttNext);
}
tabs.prototype.initTabs = function(){
	this.$tabs.addClass('overflowHidden');
	var $temp = this.$tabs.html();
	this.$tabs.html('');
	this.$wrapInner = $('<div id="'+this.id+'_inner" onDblclick="return false;"></div>');
	this.$wrapInner.append($temp);
	this.$tabs.append(this.$wrapInner);
	$('#'+this.id+'_inner .tab').each(function(t) { return function(){t.addElement({'obj':$(this), 'width':parseInt($(this).width()), 'backPoint':-1, 'nextPoint':-1, 'visibility':-10, 'id':$(this).attr('id')});}}(this));
	for(var i = 0; i< this.items.length; i++) {
		this.allTabsWidth += this.items[i]['width'];
	}
	this.tabScreenWidth = parseInt(this.$tabs.width());
	if(this.tabScreenWidth < this.allTabsWidth) {
		this.createScrolledTabs();
	}
	for(var i = 0; i< this.items.length; i++) {
		this.items[i]['obj'].click(function(t, n){return function(){ t.showIfHidden(n);}}(this, i));
	}
	this.countTabVisibility();
	this.countArrowsState();
}
