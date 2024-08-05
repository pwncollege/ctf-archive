function watch_soft(soft_id) {
	var ajaxobj=new AJAXRequest;
	url= "/user/?act=member.watch_soft&soft_id="+soft_id+"&temp="+Math.random();
	ajaxobj.method="GET";
	ajaxobj.url=url;
	ajaxobj.callback=function(xmlobj) {
		var text;
		var image;

		if (xmlobj.responseText == '1'){
			text = "This software is added to your <a href=\"/user/?act=member.mywatchlist\">Watch List</a>";
		}else{
			text = "Login <a href='/user/?act=member.login'>here</a>.";
		}
		document.getElementById('watch_list_name').innerHTML	= '';
		document.getElementById('watch_list_name1').innerHTML = text;
		document.getElementById('image_add').src = 'http://img.brothersoft.com/v1/img_s/right.gif';
	}
	ajaxobj.send();
}
function rate(rating,soft_id) {
	var ajaxobj=new AJAXRequest;
	url= "/user/?act=member.dorating&soft_id="+soft_id+"&rating="+rating+"&temp="+Math.random();
	ajaxobj.method="GET";
	ajaxobj.url=url;
	document.getElementById('rating').innerHTML='';
	ajaxobj.callback=function(xmlobj) {
		var text;
		var text2;
		var text1;
		var Str;
		var aStr;
		if(xmlobj.responseText == '2'){
			text = 'Login <font color="red"><a href=\'/user/?act=member.login\'>here</a></font>.';
			text1 = '';
			text2 = '';
			document.getElementById('rating1').innerHTML = text1;
		    //document.getElementById('rating').innerHTML = text;
		    document.getElementById('outofrating').innerHTML = text2
		
		}else{
			Str	= xmlobj.responseText;
			aStr = Str.split("|");
			text = '<ul class="star-rating"><li class="current-rating" style="width:'+aStr[0]+'px">Current rating</li><li><a title="1 star out of 5" class="one-star-current"></a></li><li><a title="2 stars out of 5" class="two-stars-current"></a></li><li><a title="3 stars out of 5" class="three-stars-current"></a></li><li><a title="4 stars out of 5" class="four-stars-current"></a></li><li><a title="5 stars out of 5" class="five-stars-current"></a></li></ul>';
			text1 = '<ul class="star-rating"><li class="current-rating" style="width:'+aStr[1]+'px">Current rating</li><li><a title="1 star out of 5" class="one-star-current"></a></li><li><a title="2 stars out of 5" class="two-stars-current"></a></li><li><a title="3 stars out of 5" class="three-stars-current"></a></li><li><a title="4 stars out of 5" class="four-stars-current"></a></li><li><a title="5 stars out of 5" class="five-stars-current"></a></li></ul>';
			text2 = 'out of '+aStr[2]+' votes';
			document.getElementById('rating1').innerHTML = text1;
		    document.getElementById('rating').innerHTML = text;
		    document.getElementById('outofrating').innerHTML = text2;
		}
	}
	ajaxobj.send();
}


function F_voting(rating,soft_id) {
	var ajaxobj=new AJAXRequest;
	url= "/user/?act=Top.dorating&soft_id="+soft_id+"&rating="+rating+"&temp="+Math.random();
	ajaxobj.method="GET";
	ajaxobj.url=url;
	ajaxobj.callback=function(xmlobj) {
		var text;
		var text2;
		var text1;
		var Str;
		var aStr;
		if(xmlobj.responseText == '2'){
			//text = 'Login <font color="red"><a href=\'/user/?act=member.login\'>here</a></font>.';
//			text1 = '';
//			text2 = '';
//			document.getElementById('rating1').innerHTML = text1;
//		    //document.getElementById('rating').innerHTML = text;
//		    document.getElementById('outofrating').innerHTML = text2
		
		}else{
			
			document.getElementById('rating').innerHTML='';
			Str	= xmlobj.responseText;
			aStr = Str.split("|");
			text = '<ul class="star-rating"><li class="current-rating" style="width:'+aStr[0]+'px">Current rating</li><li><a title="1 star out of 5" class="one-star-current"></a></li><li><a title="2 stars out of 5" class="two-stars-current"></a></li><li><a title="3 stars out of 5" class="three-stars-current"></a></li><li><a title="4 stars out of 5" class="four-stars-current"></a></li><li><a title="5 stars out of 5" class="five-stars-current"></a></li></ul>';
			text1 = '<ul class="star-rating"><li class="current-rating" style="width:'+aStr[1]+'px">Current rating</li><li><a title="1 star out of 5" class="one-star-current"></a></li><li><a title="2 stars out of 5" class="two-stars-current"></a></li><li><a title="3 stars out of 5" class="three-stars-current"></a></li><li><a title="4 stars out of 5" class="four-stars-current"></a></li><li><a title="5 stars out of 5" class="five-stars-current"></a></li></ul>';
			text2 = 'out of '+aStr[2]+' votes';
			document.getElementById('rating1').innerHTML = text1;
		    document.getElementById('rating').innerHTML = text;
		    document.getElementById('outofrating').innerHTML = text2;
		}
	}
	ajaxobj.send();
}