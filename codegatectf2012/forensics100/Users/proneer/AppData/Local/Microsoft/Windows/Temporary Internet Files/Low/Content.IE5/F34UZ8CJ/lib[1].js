function g(id){
	return document.getElementById(id);
}
function addHover(el){
	if (el)
		$(el).addClass("hover");
}
function removeHover(el){
	if (el)
		$(el).removeClass("hover");
}
var _timers = [];
var _act_elements = [];
var _timeout = 100; //can change this setting to hide items slowly or faster

function onItem(el, menu, test_parents, rem_func, add_func){
	if (!menu) menu='';
	var $el = $(el);
	if ($el.hasClass('invis')){
		return false;
	}
	if (_timers[menu])
		clearTimeout(_timers[menu]);
	if (_act_elements[menu] && $el.attr('id') != _act_elements[menu].getAttribute('id') && (!test_parents || !$el.parents("li").is('#'+_act_elements[menu].getAttribute('id')))){
		if (!rem_func) removeHover(_act_elements[menu]);
		else rem_func(_act_elements[menu]);
	}
	if (!add_func && !$el.hasClass('hover')) {
		$el.addClass('hover');
	}
	else if (add_func) {
		add_func(el);
	}
	_act_elements[menu] = el;
}

function outItem(el, menu, tfunc){
	if (!menu) menu='';
	var $el = $(el);
	if ($el.hasClass('invis')){
		return false;
	}
	if (_timers[menu])
		clearTimeout(_timers[menu]);
	if (!tfunc)
		tfunc = function($e){return function(){$e.removeClass('hover');}}($el);
	_timers[menu] = setTimeout(tfunc, _timeout);
}
var $_clicked = [];

function stopBuble(e){
	e = e || window.event;
	e.cancelBubble = true;
	if (e.stopPropagation){ e.stopPropagation(); }
}

function closeall(){
	for (var i in $_clicked){
		$_clicked[i].removeClass('click');
	}
	$_clicked = [];
}

function addClick(el, key, e){
	var $el = $(el);
	var clicked = false;
	if ($_clicked[key]){
		clicked = true;
	}
	closeall();
	if (!clicked){
		$el.addClass('click');
		$_clicked[key] = $el;
	}
	stopBuble(e);
}
previusItem = {'id':-1, 'this':-1 };
function showHideNewsBlock (id, t) {
	var $news = $('#'+id);
	if($news.attr('condition')=='closed') {
		var currentHeight = $('.newsHeight',$news).height();
		$news.attr('condition', 'opened');
		$('.slideArrow',t).css('background-position', '-140px -10px')
		$news.stop().animate({'height':(currentHeight)+'px'}, 200, function(){return function(){}});
	} else {
		$news.attr('condition', 'closed');
		$('.slideArrow',t).css('background-position', '-140px 0')
		$news.stop().animate({'height':'0px'}, 200, function(){return function(){}});
	}
}
function slideListShowHide (id, t) {
	var $pm = $('#'+id);
	if($pm.attr('condition')=='closed') {
		var currentHeight = $('.newsHeight',$pm).height();
		$pm.attr('condition', 'opened');
		$('.slideArrow',t).css('background-position', '-140px -10px')
		$pm.stop().animate({'height':(currentHeight)+'px'}, 200, function(){return function(){}});
		if(previusItem['id']!=-1&&previusItem['this']!=-1) {
			$('#'+previusItem['id']).attr('condition', 'closed');
			$('.slideArrow',previusItem['this']).css('background-position', '-140px 0')
			$('#'+previusItem['id']).stop().animate({'height':'0px'}, 200, function(){return function(){}});
		}
		previusItem['id'] = id;
		previusItem['this'] = t;
	} else {
		$pm.attr('condition', 'closed');
		$('.slideArrow',t).css('background-position', '-140px 0')
		$pm.stop().animate({'height':'0px'}, 200, function(){return function(){}});
		if(previusItem['id']!=-1&&previusItem['this']!=-1) {
			previusItem['id'] = -1;
			previusItem['this'] = -1;
		}
	}
}

function showHideReviewBlock (id, idAutor, t) {
	var $review = $('#'+id);
	var $autor = $('#'+idAutor);
	if($review.attr('condition')=='closed') {
		var currentHeight = $('.newsHeight',$review).height();
		$review.attr('condition', 'opened');
		$('.slideArrow',t).css('background-position', '-140px -10px')
		$review.stop().animate({'height':(currentHeight)+'px'}, 200, function($r){return function(){$('.reviewAutor',$r).css({'visibility':'visible', 'opacity':0}); $('.reviewAutor',$r).stop().animate({'opacity':1}, 200, function(r){return function(){if($.browser.msie) $('.reviewAutor',r).removeAttr("filter");}}($r));}}($review));
		$autor.stop().animate({'opacity':0}, 200);
	} else {
		$review.attr('condition', 'closed');
		$('.slideArrow',t).css('background-position', '-140px 0')
		$review.stop().animate({'height':'0px'}, 200, function($r){return function(){ $('.reviewAutor',$r).stop().animate({'opacity':0}, 200);}}($review));
		$autor.stop().animate({'opacity':1}, 200, function(id){return function(){if($.browser.msie) document.getElementById(id).style.removeAttribute("filter");}}(idAutor));
	}
}

function showHidePaymentsBlock (id, t) {
	var $pm = $('#'+id);
	if($pm.attr('condition')=='closed') {
		var currentHeight = $('.newsHeight',$pm).height();
		$pm.attr('condition', 'opened');
		$('.slideArrow',t).css('background-position', '-140px -10px')
		$pm.stop().animate({'height':(currentHeight)+'px'}, 200, function(){return function(){}});
		if(previusItem['id']!=-1&&previusItem['this']!=-1) {
			$('#'+previusItem['id']).attr('condition', 'closed');
			$('.slideArrow',previusItem['this']).css('background-position', '-140px 0')
			$('#'+previusItem['id']).stop().animate({'height':'0px'}, 200, function(){return function(){}});
		}
		previusItem['id'] = id;
		previusItem['this'] = t;
	} else {
		$pm.attr('condition', 'closed');
		$('.slideArrow',t).css('background-position', '-140px 0')
		$pm.stop().animate({'height':'0px'}, 200, function(){return function(){}});
		if(previusItem['id']!=-1&&previusItem['this']!=-1) {
			previusItem['id'] = -1;
			previusItem['this'] = -1;
		}
	}
}

function switchTab(id, idContent, compare) {
	if( id == 'tab_2' ) {
		if( $('#screen_gallery').html() == '' ) {
			$('#gallery_loader').removeClass('displayNone');
//			$('#gallery_loader').css({'opacity':0}).animate({opacity:1},500);
				var div = document.getElementById("start_gallery");
				script = document.createElement('script');
				script.id = 'formPrinter';
				script.type = 'text/javascript';
				script.src = 'http://www.daemon-tools.cc/img/new/scripts/gallery-start.js';
				div.appendChild( script );
		}
	}

	var $tab = $('#'+id);
	var $tabContent = $('#'+idContent);
	if(!$tabContent.hasClass('selected')) {
		if(compare==undefined) {
			$('.switcher li').removeClass('selected');
		} else {
			$('.tab', '.compareTabs').removeClass('selected');
		}
		$('.tabContent').removeClass('selected');
		$tab.addClass('selected');
		$tabContent.addClass('selected');
		$('#switcher_title').html($('.tabText',$tab).html());
		$('.tabContent').addClass('height0');
		$('.tabContent').addClass('overflowYHidden');

		$tabContent.css({'opacity':0}); $tabContent.removeClass('height0'); $tabContent.removeClass('overflowYHidden');

		$('#tabs_content').stop().animate({'height':$('.tabContentHeight', $tabContent).height()}, 150, function($tC, idC){
			return function(){
				$tC.animate({'opacity':1}, 150, function(id){
					return function(){
						if($.browser.msie)
						document.getElementById(id).style.removeAttribute("filter");
					}
				}(idC));
				
				var offset = $('#tab_switcher').offset();
				if(offset) {
				$(document).scrollTo( offset.top, 800, {queue:true} );
				}
				
			}
		}($tabContent, idContent));
	}

}
function removeAllTextSelection() {
	var sel ;
	if(document.selection && document.selection.empty){
		document.selection.empty() ;
	} else if(window.getSelection) {
		sel=window.getSelection();
	if(sel && sel.removeAllRanges)
		sel.removeAllRanges() ;
	}
}