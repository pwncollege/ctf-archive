/* Set Cookie */
function setCookieGtype( name, value, expiredays ) {
	var todayDate = new Date();
	todayDate.setDate( todayDate.getDate() + expiredays );
	document.cookie = name + "=" + escape( value ) + "; path=/; expires=" + todayDate.toGMTString() + ";"
}

/* Get Cookie */
function getCookieGtype( name ) {
	var nameOfCookie = name + '=';
	var x = 0;
	while ( x <= document.cookie.length ) {
		var y = (x+nameOfCookie.length);
		if ( document.cookie.substring( x, y ) == nameOfCookie ) {
			if ( (endOfCookie=document.cookie.indexOf( ';', y )) == -1 ) 
				endOfCookie = document.cookie.length;
			return unescape( document.cookie.substring( y, endOfCookie ) );
		}
		x = document.cookie.indexOf( ' ', x ) + 1;
		if ( x == 0 )
		break;
	}
	return '';
}

/* Setting Lang Cookie */
function gtype_setcookie() {    
	returl = document.URL;
	var partStr=returl.split("/");
	gtype = partStr[3];
	setCookieGtype('gtype',gtype,1);
}

/* Setting Lang */
function set_lang() {
	var gtype_gourl = getCookieGtype( 'gtype' );
	var fm = document.lang;

	for(var i=0;i<fm.langtype.length;i++) {
		if(fm.langtype[i].value == gtype_gourl) {
			fm.langtype[i].selected=true;
		}
	}
}

/* Chang Language */
function chg_lang() {
    var fm = document.lang;  
    var sindex = fm.langtype.selectedIndex; 
    clang = fm.langtype.options[sindex].value; 

    var returl = document.URL;
    var partStr=returl.split("/");
    returl = partStr[4]; 
	//alert(returl);
	if(clang == "japan") {
		window.open("http://www.gomplayer.jp/");
		location.reload();
	} else if(clang == "kr") {
		window.open("http://www.gomtv.com/");
		location.reload();
	} else if(clang == "cn") {
		location.href = "http://www.gomplayer.cn/";
	} else {
		location.href = "http://player.gomlab.com/"+clang+"/";
	}
}