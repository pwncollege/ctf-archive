var imgserver = "";

//document.write("<script language='javascript' type='text/javascript' src='/kr/site/js/navi.js'></script>");// Navi 포함
//document.write("<script language='javascript' type='text/javascript' src='/kr/site/js/common_path.js'></script>");// main 포함

(function(){
	/*Use Object Detection to detect IE6*/
	var  m = document.uniqueID /*IE*/
	&& document.compatMode  /*>=IE6*/
	&& !window.XMLHttpRequest /*<=IE6*/
	&& document.execCommand ;
	try{
		if(!!m){
			m("BackgroundImageCache", false, true) /* = IE6 only */
		}
	}catch(oh){};
})();


// Flash
function swfprint(objid,furl,fwidth,fheight,transoption,flashvars) {
	var swfString = '<object id="'+ objid +'" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,0,0" width="'+ fwidth +'" height="' + fheight +'" >';
	swfString += '<param name="allowScriptAccess" value="always"/>';
	swfString += '<param name="movie" value="'+ furl +'"/>';
	swfString += '<param name="quality" value="high"/>';
	if (flashvars) swfString += '<param name="flashVars" value="'+ flashvars +'" />';
	if (transoption == "t"){
		swfString += '<param name="wmode" value="transparent" />';
	} else if (transoption == "o"){
		swfString += '<param name="wmode" value="opaque" />';
	}
	swfString += '<![if !IE]>';
	swfString +='<embed name="'+objid+'" src="'+ furl +'"quality="high" wmode="transparent" width="'+ fwidth +'" height="'+ fheight +'" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" />';
	swfString += '<![endif]>';
	swfString +='</object>';
	document.write(swfString);
}



appname = navigator.appName;
useragent = navigator.userAgent;
if(appname == "Microsoft Internet Explorer") appname = "IE";
IE55 = (useragent.indexOf('MSIE 5.5')>0);  //5.5 버전
IE6 = (useragent.indexOf('MSIE 6')>0);     //6.0 버전

function chgSelect() {
	var selectObj = document.getElementsByTagName("select");
	if(appname=="IE" && IE55 || IE6) {
		if(selectObj) {
			for (var i=0; i<selectObj.length; i++) {
				selectObj[i].style.visibility ="hidden";
			}
		}
	}
}
function chgSelectDefaut() {
	var selectObj = document.getElementsByTagName("select");
	if(appname=="IE" && IE55 || IE6) {
		if(selectObj) {
			for (var i=0; i<selectObj.length; i++) {
				selectObj[i].style.visibility ="visible";
			}
		}
	}
}

function resizeLNB(h){
	var objLNB = document.getElementById('LNBBox');
	objLNB.style.height= h+"px";
}

function resizeGNB(h){
	document.getElementById('gnbBox').style.height=h+'px';
	var objsBar=document.getElementById('schBar');
	var objsBar2=document.getElementById('schLayer');

	if(h > 135){
		objsBar.style.display='none';
		objsBar2.style.display='none';
		chgSelect();
	}else{
		objsBar.style.display='block';
		chgSelectDefaut();
	}
}

function resizeMain(h){
	document.getElementById('mFlash').style.height=(h+20)+'px';
}



// POP - Q&A
function showQNA(num) {
	var selectObj=document.getElementById('qList').getElementsByTagName("li");

	for (var i=0; i<selectObj.length; i++) {
		if(num==i){
			selectObj.item(num).className = "on";
		}else{
			selectObj.item(i).className = "";
		}
		document.getElementById('q'+i).style.display= (num==i)?"":"none";
		document.getElementById('a'+i).style.display= (num==i)?"":"none";
	}
}


function TabOver(imgEl) {
	imgEl.src = imgEl.src.replace(".gif", "_over.gif");
}
function TabOut(imgEl) {
	imgEl.src = imgEl.src.replace("_over.gif", ".gif");
}

function menuOver(imgEl) {
	imgEl.src = imgEl.src.replace(".gif", "_on.gif");
}
function menuOut(imgEl) {
	imgEl.src = imgEl.src.replace("_on.gif", ".gif");
}



//Footer - Global Site
function globalSite(){
	btn=document.getElementById('fsbtn');
	lay=document.getElementById('fsBox');

	if(lay.style.display=="none"){
		btn.className = "btn on";
		lay.style.display="block";
	}else{
		btn.className = "btn";
		lay.style.display="none";
	}
}

// Layer Show&Hidden
function showLayer(id){
	var fb = document.getElementById(id);
	if (fb.style.display == 'block'){
		fb.style.display='none';
	}
	else{
		fb.style.display='block';
	}

	/* SELECT HIDDEN */
	var sel = document.getElementsByTagName("select");
	for(i=0;i< sel.length;i++){
		sel[i].style.visibility = "hidden";
	}
}
function hiddenLayer(id){
	document.getElementById(id).style.display='none';
	
	/* SELECT HIDDEN */
	var sel = document.getElementsByTagName("select");
	for(i=0;i< sel.length;i++){
		sel[i].style.visibility = "visible";
	}
}


// PNG
function setPng24(obj) {
	try {
		obj.width=obj.height=1;
		obj.className=obj.className.replace(/\bpng24\b/i,'');
		obj.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+ obj.src +"',sizingMethod='image');";
		obj.src='';
		return '';
	} catch(e) {}
}


// POP
function openPop(val) {
	var url=""; var name=""; var wid=""; var hei=""; var scr="";

	switch(val) {
		case 'threats_print': url = "/site/threats/pop_print.html";name="threats_print";wid=770;hei=600;scr="yes"; break; //Threats & Solutions > Virus Center Print
		case 'home_print': url = "/site/products/pop_print.html";name="home_print";wid=770;hei=600;scr="yes"; break; //Store > Home Products
		case 'suup_product': url = "/site/support/pop_product.html";name="suup_product";wid=396;hei=252;scr="no"; break; //Support >Password Assistance
		case 'suup_email': url = "/site/support/pop_emailcheck.html";name="suup_email";wid=396;hei=207;scr="no"; break; //Support >Password Assistance
		case 'licen_key1': url = "/site/support/pop_licensekey.html";name="licen_key1";wid=396;hei=252;scr="no"; break; //Support >Password Assistance
		case 'licen_key2': url = "/site/support/pop_licensekey02.html";name="licen_key2";wid=396;hei=223;scr="no"; break; //Support >Password Assistance
		case 'licen_key3': url = "/site/support/pop_licensekey03.html";name="licen_key3";wid=396;hei=262;scr="no"; break; //Support >Password Assistance
		case 'suup_email2': url = "/site/support/pop_emailcheck02.html";name="suup_email2";wid=396;hei=226;scr="no"; break; //Support >Password Assistance
		case 'suup_email3': url = "/site/support/pop_emailcheck03.html";name="suup_email3";wid=396;hei=235;scr="no"; break; //Support >Password Assistance
		case 'pop_wait': url = "/site/support/pop_wait.html";name="pop_wait";wid=590;hei=320;scr="no"; break; //Support >Password Assistance
		default : break;
	}

	if(url!=""){
		var winl = (screen.width - wid) / 2;
		var wint = (screen.height - hei) / 2;

		if(wid!=""){
			winprops = "height=" + hei + ", width=" + wid + ", top=" + wint+ ", left=" + winl + ", toolbar=no,status=no,directories=no,scrollbars="+scr +",location=no,resizable=no";
			window.open(url, name, winprops);
		}else{
			winprops = "height=" + screen.height + ", width=" + screen.width + ", top=" + wint+ ", left=" + winl + ", toolbar=no,status=no,directories=no,scrollbars=no,location=no,resizable=yes";
			window.open(url, name, winprops);
		}
	}else{
		alert("준비중입니다.");
		return;
	}
}