// context can be - page-album, page-game, page-soft
function calculateHeaderHeight( context ) {
	var currentHeight = 0;
	var currentPaddingTop = 0;
	var itemPadding = 20;
	
	currentHeight = $('.header', context).height();
	currentPaddingTop = $('.scroller', context); 
	currentPaddingTop.css('padding-top',currentHeight+itemPadding+'px');
}