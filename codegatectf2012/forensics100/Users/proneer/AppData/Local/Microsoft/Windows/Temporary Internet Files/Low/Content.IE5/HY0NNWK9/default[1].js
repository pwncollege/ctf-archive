	
	function trim(st) {
		while(st && st.indexOf(" ")==0) st = st.substring(1);
		while(st && st.lastIndexOf(" ")==st.length-1) st = st.substring(0, st.length-1);
		return st;
	}

	function allTrim(checkform) {
		var i=0;
		while (i < checkform.length) {
			checkform.elements[i].value=trim(checkform.elements[i].value);
			i++;
		}
	}

	function isNull(field, message, focus)
	{
		try{
			var str = field.value;
			var form_flag = true;
			str.value;
		}
		catch(err){
			var str =field;
			var form_flag = false;
		}

		var error = false;
		if (str.length ==0) error = true;
		if (error == true){
			if(message) alert(message);
			if(form_flag) field.focus();
			return false;
		}
		return true;
	}

	
	function isLength(field, message, lan, flag)
	{
		try{
			var str = field.value;
			var form_flag = true;
			str.value;
		}
		catch(err){
			var str =field;
			var form_flag = false;
		}

		var error = false;
		if (flag){
			if (str.length > lan) error = true;
			
		}
		else{
			if (str.length <= lan) error = true;
		}
		if (error == true){
			if(message) alert(message);
			if(form_flag) field.focus();
			return false;
		}
		return true;
	}

	function isInclusion(field, message, pattern)
	{
		try{
			var str = field.value;
			var form_flag = true;
			str.value;
		}
		catch(err){
			var str =field;
			var form_flag = false;
		}

		var error = false;
		if(str.indexOf(pattern) == -1) error = true;
		if (error == true){
			if(message) alert(message);
			if(form_flag) field.focus();
			return false;
		}
		return true;
	}

	// 이메일 체크
	function isEmail(field, message)
	{
		try{
			var str = field.value;
			var form_flag = true;
			str.value;
		}
		catch(err){
			var str =field;
			var form_flag = false;
		}

		var error = false;
		/** 체크사항
		 - @가 2개이상일 경우
		 - .이 붙어서 나오는 경우
		 -  @.나  .@이 존재하는 경우
		 - 맨처음이.인 경우
		 - @이전에 하나이상의 문자가 있어야 함
		 - @가 하나있어야 함
		 - Domain명에 .이 하나 이상 있어야 함
		 - Domain명의 마지막 문자는 영문자 2~4개이어야 함 **/
		var check1 = /(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)/;
		var check2 = /^[a-zA-Z0-9\-\.\_]+\@[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4})$/;
		if (check1.test(str) || !check2.test(str)) error = true;
		if (error == true){
			if(message) alert(message);
			if(form_flag) field.focus();
			return false;
		}
		return true;
	}

	// str은 한글이어야만 한다.
	function isHangul(field, message)
	{
		try{
			var str = field.value;
			var form_flag = true;
			str.value;
		}
		catch(err){
			var str =field;
			var form_flag = false;
		}

		var error = false;
			strarr = new Array(str.length);
			schar = new Array('/','.','>','<',',','?','}','{',' ','\\','|','(',')','+','=');

			for (i=0; i<str.length; i++) {
				for (j=0; j<schar.length; j++) {
					if (schar[j] ==str.charAt(i)) {
						error = true;
					}
				}
				strarr[i] = str.charAt(i)
				if ((strarr[i] >=0) && (strarr[i] <=9)) {
					error = true;
				} else if ((strarr[i] >='a') && (strarr[i] <='z')) {
					error = true;
				} else if ((strarr[i] >='A') && (strarr[i] <='Z')) {
					error = true;
				} else if ((escape(strarr[i]) > '%60') && (escape(strarr[i]) <'%80') ) {
					error = true;
				}
			}
		if (error == true){
			if(message) alert(message);
			if(form_flag) field.focus();
			return false;
		}
		return true;
	}


	function isNum(field, message)
	{
		try{
			var str = field.value;
			var form_flag = true;
			str.value;
		}
		catch(err){
			var str =field;
			var form_flag = false;
		}

		var error = false;
		var check = /^[0-9]*$/;
		if (!check.test(str)) 
			error = true;
		if (error == true){
			if(message) alert(message);
			if(form_flag) field.focus();
			return false;
		}
		return true;
	}
	
	function isEng(field, message)
	{
		try{
			var str = field.value;
			var form_flag = true;
			str.value;
		}
		catch(err){
			var str =field;
			var form_flag = false;
		}

		var error = false;
		var check = /^[a-zA-Z]*$/;
		if (!check.test(str)) 
			error = true;
		if (error == true){
			if(message) alert(message);
			if(form_flag) field.focus();
			return false;
		}
		return true;
	}

	
	function isNumEng(field, message)
	{
		try{
			var str = field.value;
			var form_flag = true;
			str.value;
		}
		catch(err){
			var str =field;
			var form_flag = false;
		}

		var error = false;
		var check = /^[a-zA-Z0-9]*$/;
		if (!check.test(str)) 
			error = true;
		if (error == true){
			if(message) alert(message);
			if(form_flag) field.focus();
			return false;
		}
		return true;
	}


	function isProductkey(field, message)
	{
		try{
			var str = field.value;
			var form_flag = true;
			str.value;
		}
		catch(err){
			var str =field;
			var form_flag = false;
		}

		var error = false;
		var check = /^([0-9]+[\-]+[0-9]+)*$/;
		if (!check.test(str)) 
			error = true;
		if (error == true){
			if(message) alert(message);
			if(form_flag) field.focus();
			return false;
		}
		return true;
	}



	function lengthen(strIn) {
		strOut = String(strIn);
		if (strOut.length==1) strOut = "0" + strOut;
		return strOut;
	}



	//페이지 이동 함수
	function AnchorPosition(ei)
	{
		var ot=document.getElementById(ei).offsetTop;
		if(navigator.userAgent.indexOf("Safari") == -1) document.documentElement.scrollTop=ot;
		else document.body.scrollTop =ot;
	}


	function converttime(year, month, day, hour, minute, second) {
		month--; // the month begin from 0(January) in Javascript
		var today = new Date();
		var year1 = 0;
		dKR = new Date(year, month, day, hour, minute, second, 0);
		dGMT = new Date(dKR.getTime() + (-540 * 60 * 1000));
		dLocal = new Date(dGMT.getTime() - (today.getTimezoneOffset() * 60 *1000));
		var returnString;
		returnString  = lengthen(dLocal.getMonth()+1);
		returnString += "/";
		returnString += lengthen(dLocal.getDate());
		returnString += "/";
                year1 = dLocal.getYear();
                if (dLocal.getYear() < 200) year1 = year1 + 1900;
                returnString += year1;
		returnString += "<BR>";
		returnString += lengthen(dLocal.getHours());
		returnString += ":";
		returnString += lengthen(dLocal.getMinutes());
		returnString += ":";
		returnString += lengthen(dLocal.getSeconds());

		if (dLocal.getYear() == today.getYear() && dLocal.getMonth() == today.getMonth() && dLocal.getDate() == today.getDate()) {
			returnString = "<font color=#A00020>" + returnString + "</font>";
		}
		return returnString;
	}

	function converttime2(year, month, day, hour, minute, second) {
		month--; // the month begin from 0(January) in Javascript
		var today = new Date();
		var year1 = 0;
		dKR = new Date(year, month, day, hour, minute, second, 0);
		dGMT = new Date(dKR.getTime() + (-540 * 60 * 1000));

		dLocal = new Date(dGMT.getTime() - (today.getTimezoneOffset() * 60 *1000));

		var returnString;
		returnString  = lengthen(dLocal.getMonth()+1);
		returnString += "/";
		returnString += lengthen(dLocal.getDate());
		returnString += "/";
		year1 = dLocal.getYear();
		if (dLocal.getYear() < 200) year1 = year1 + 1900;
		returnString += year1;

		if (dLocal.getYear() == today.getYear() && dLocal.getMonth() == today.getMonth() && dLocal.getDate() == today.getDate()) {
			returnString = "<font color=#A00020>" + returnString + "</font>";
		}
		return returnString;
	}

	function addList(object, text, value) {
		loc=object.length;
		object.options[loc] = new Option(text,value);
		// object.selectedIndex = loc;
	}

//Cookie Start
	function setCookie (name, value, expires, path, domain, secure) {
		// cookie string
		var cs = name + "=" + escape(value) + ( (path) ? "; path=" + path : "")   + ( (expires) ? "; expires=" + expires.toGMTString() : "") + ( (domain) ? "; domain=" + domain : "") + ( (secure) ? "; secure" : "");
			document.cookie = cs;
		//document.cookie = "faq_hash=h; path=/; domain=test.netsarang.com;";
	}

	function getDelayDay(day) {
		expires = new Date();
		expires.setTime(expires.getTime() + 24 * 60 * 60 * day * 1000);
		return expires;
	}

	function getCookie (name)
	{
		// cookie string
		var cs = document.cookie;
		var prefix = name + "=";

		// cookie's Start Index of the information of it.
		var cSI = cs.indexOf(prefix);
		if (cSI == -1) return null;

		// Find cookie's End Index of the information of the cookie.
		var cEI = cs.indexOf(";", cSI + prefix.length);

		// If it din't find the CEI, then set it to the end of the cs
		if (cEI == -1) {
			cEI = cs.length;
		}
		// Decode the value of the cookie's
		return unescape(cs.substring(cSI + prefix.length, cEI));
	}

	function deleteCookie (name, path)
	{
		setCookie (name, "", "", path )
	}

	function byte_length(input)
	{
			var i, j=0;
			for(i=0;i<input.length;i++) {
				val=escape(input.charAt(i)).length;
				if(val==  6) j++;
				j++;
			}
			return j;
	}
	


	//Clipboard 저장기능
	function copyToClipboard(In_txt)
	{
			document.body.focus();
			if(window.clipboardData)
			{
				var clipInput, clipContainer;
				clipInput = document.createElement("textarea");
				clipInput.style.width = "0px";
				clipInput.style.height = "0px";
				clipInput.style.borderStyle = "none";
				clipContainer = document.createElement("div");
				clipContainer.style.position = "absolute";
				clipContainer.style.width = "0px";
				clipContainer.style.height = "0px";
				clipContainer.style.display = "none";
				clipContainer.style.zIndex = "-100";
				clipContainer.appendChild(clipInput);
				document.body.appendChild(clipContainer);
				clipInput.value = In_txt;
				clipContainer.style.display = "";
				clipInput.focus();
				clipInput.select();
			}
			else if(navigator.userAgent.indexOf("Safari") != -1)
			{
				var clipFrame = document.getElementById("clipboardFrame");
				if (!clipFrame)
				{
					clipFrame = document.createElement("iframe");
					clipFrame.id = "clipboardFrame";
					clipFrame.style.display = "none";
					document.body.appendChild(clipFrame);

					var clipDoc = clipFrame.contentDocument;
					clipDoc.body.innerHTML = "<textarea id='clipContainer' />";
					clipDoc.designMode = "On";
					clipDoc.body.contentEditable = true;
				}
				var clipDoc = clipFrame.contentDocument;
				var container = clipDoc.getElementById("clipContainer");
				container.value = In_txt;
				container.focus();
				container.select();
			}
			else if (window.netscape) return;
	}

function getImgSize(imgSrc)
{
var newImg = new Image();
newImg.src = imgSrc;
var height = newImg.height;
var width = newImg.width;
alert ('The image size is '+width+'*'+height);
}



function move_link(link, in_target)
{
	location.href=link;
}


//페이지 이동 함수
	function movepage(obj_name, in_action, in_parameter, in_target)
	{
		var formNode=eval("document."+obj_name);
		if(!formNode){
			formNode = document.createElement("form");
			formNode.setAttribute("name", obj_name);
			formNode.setAttribute("id", obj_name);
			document.body.appendChild(formNode);
		}
		if(in_parameter){
			var parameter=in_parameter.split("&");
			for(var i=0;i<parameter.length;i++)
			{
				if(!parameter[i])
					continue;
				var rows=parameter[i].split("=");
				if(eval("formNode."+rows[0]))
					eval("formNode."+rows[0]).value = rows[1];
				else {
					var inputNode = document.createElement("input");
					inputNode.setAttribute("type", "hidden");
					inputNode.setAttribute("name", rows[0]);
					inputNode.setAttribute("value", rows[1]);
					formNode.appendChild(inputNode);
				}
			}
		}
		if(!in_target) in_target="_top";
		formNode.setAttribute("method", "post");
		formNode.setAttribute("target", in_target);
		formNode.setAttribute("action", in_action);
		formNode.submit();
	}


function getElementsByClass(searchClass,node,tag) {
	var classElements = new Array();
	if ( node == null )
		node = document;
	if ( tag == null )
		tag = '*';
	var els = node.getElementsByTagName(tag);
	var elsLen = els.length;
	var pattern = new RegExp("(^|\\s)"+searchClass+"(\\s|$)");
	for (i = 0, j = 0; i < elsLen; i++) {
		if ( pattern.test(els[i].className) ) {
			classElements[j] = els[i];
			j++;
		}
	}
	return classElements;
}



//링크 삭제 기능(print 사용시)
	function removelink(pass_id)
	{
		//일반 링크 삭제
		var form=document.getElementsByTagName("a");
		for(var i=0;i<form.length;i++)
		{
			if(pass_id && form.item(i).id ==pass_id) continue;
			form.item(i).removeAttribute("onclick");
			form.item(i).removeAttribute("href");
		}

		//이미지 링크 삭제
		var form=document.getElementsByTagName("img");
		for(var i=0;i<form.length;i++)
		{
			if(pass_id && form.item(i).id ==pass_id) continue;
			form.item(i).removeAttribute("onclick");
		}

		//btn링크 삭제
		var form=getElementsByClass("btn");
		for(var i=0;i<form.length;i++)
		{
			if(pass_id && form[i].id == pass_id) continue;
			
			if(form[i].tagName == 'INPUT')
			{
				form[i].setAttribute("type","button");
				form[i].removeAttribute("onclick");
			}
			if(form[i].tagName == 'BUTTON')
			{
				form[i].setAttribute("type","button");
				form[i].removeAttribute("onclick");
			}
		}
	}



function change_tap(tapNum, tapCount)
{
	for(var i=1;i<=tapCount;i++)
	{
		var tap_thumb = document.getElementById("tap_thumb"+i);
		var inner_section = document.getElementById("tap"+i);

		if(i == tapNum)
		{
			tap_thumb.className = "on";
			inner_section.className = "tap_body";
		}
		else
		{
			tap_thumb.className = "";
			inner_section.className = "hide";
		}
	}
}


function open_window(link, name, option)
{
	if(option) option =','+option;
	window.open(link, name, 'location=0,menubar=0,resizable=0, scrollbars=0,status=0,toolbar=0'+option);
}



function visible_select_change(field, str, obj)
{
	var temp_arr=str.split("^%^");
	var check_flag = false;
	for(var i=0;i<temp_arr.length;i++)
	{
		if(field.value ==temp_arr[i]){
			check_flag = true;
			break;
		}
	}
	
	if(check_flag == true) 
		visible_change(obj,1);
	else 
		visible_change(obj, -1);
}

function visible_change(obj, hide_flag)
{
	var node =	document.getElementById(obj);
	var node_classname= node.className;
	if(!hide_flag) 
		var hide_flag = node_classname.indexOf("hide");
	var node_classname = trim(node_classname.replace("hide",""));
	if(hide_flag==-1)
		node.className = node_classname+" hide";
	else
		node.className = node_classname;
}


function over_change(obj, hide_flag)
{
	var node =	document.getElementById(obj)
	var node_classname= node.className;
	if(!hide_flag) 
		var hide_flag = node_classname.indexOf("on");
	var node_classname = trim(node_classname.replace("on",""));
	if(hide_flag==-1)
		node.className = node_classname+" on";
	else
		node.className = node_classname;
}


function class_change(obj, chage_class, flag)
{
	var node =	document.getElementById(obj)
	var node_classname= node.className;
	if(!flag) 
		var flag = node_classname.indexOf(chage_class);
	var node_classname = trim(node_classname.replace(chage_class,""));
	if(flag==-1)
		node.className = node_classname+" "+chage_class;
	else
		node.className = node_classname;
}


//이미지뷰어 시작

function _imgViewer(this_layer, url, dec)
	//function _imgViewer(url)
	{
	if(!url)	
		url=this_layer.src;
	//New Item
		var newItem = document.createElement("div");
		newItem.setAttribute("id", "_imgViewerWrap");
		var html ='<div id="_imgViewerBg" style="z-index:9998; position:fixed; top:0; right:0; width:100%; height:100%; background:#fff;"></div><div id="_imgViewerBox" style="display:none; z-index:9999 !important; position:absolute;"><img id="_imgViewerImg" src="'+url+'" title="Click to close image." align="center" style="border:0; cursor:pointer"  onclick="document.getElementById(\'_imgViewerWrap\').style.visibility=\'hidden\'" /></div>';
		newItem.innerHTML= html; //내용작성
		var htmlNode=document.body;
		htmlNode.insertBefore(newItem, htmlNode.firstChild); //body의 최상위 요소로 삽입

		var iebody = document.compatMode && document.compatMode != 'BackCompat' ? document.documentElement : document.body;
		//창크기 확인
		var box_width = (document.all && !window.opera) ? iebody.clientWidth : (document.documentElement.clientWidth || self.innerWidth);
		var box_height = (document.all && !window.opera) ? iebody.clientHeight : self.innerHeight;
		//현재 레이어 위치
		var position_top=(document.all && !window.opera) ? iebody.scrollTop : pageYOffset;
		var position_left=(document.all && !window.opera) ? iebody.scrollLeft : pageXOffset;
		_imgViewer_countdown(60, box_width, box_height, position_top, position_left);
	}
	function _imgViewer_countdown(countdown_number, box_width, box_height, position_top, position_left)
	{
		if(countdown_number < 70){
			countdown_number++;
			if (typeof (document.getElementById('_imgViewerBg').style.opacity) != "undefined") // This is for Firefox, Safari, Chrome, etc.
				document.getElementById('_imgViewerBg').style.opacity = countdown_number/100;
			else if (typeof (document.getElementById('_imgViewerBg').style.filter) != "undefined") // This is for IE.
				document.getElementById('_imgViewerBg').style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=" + countdown_number + ")";
			setTimeout("_imgViewer_countdown("+countdown_number+", "+box_width+", "+box_height+", "+position_top+", "+position_left+")", 50);
		}
		else{
			var img_viewer_box= document.getElementById('_imgViewerBox');
			img_viewer_box.style.display="block";
			var img_viewer_img= document.getElementById('_imgViewerImg');
			var top = Math.round(position_top + (box_height - img_viewer_img.height) / 2);
			var left = Math.round(position_left + (box_width - img_viewer_img.width) / 2);
			img_viewer_box.style.top = top+"px";
			img_viewer_box.style.left = left+"px";
		}
	}	
//이미지뷰어 끝