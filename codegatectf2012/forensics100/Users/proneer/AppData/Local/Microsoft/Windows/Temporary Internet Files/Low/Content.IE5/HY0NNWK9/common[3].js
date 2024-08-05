/* Top Login Link */
function Login(){
	var returl = document.URL;
	returl = escape(returl);
	document.location.href = 'https://www.gomlab.com/login/login.gom?lang=eng&returl=' + returl;
}

/* Top Logout Link */
function Logout(){
	var returl = document.URL;
	returl = escape(returl);
	document.location.href = 'https://www.gomlab.com/login/logout.gom?lang=eng&returl=' + returl;
}

/* Skin Write */
function goSkinWrite(){
    var fm = document.fm;

	if(fm.title.value == ""){
		alert("Please enter a Skin Name for your skin.");
		fm.title.focus();
		return false;
	}

	if(fm.skinFile.value == ""){
		alert("Please attach a skin file (.gps format).");
		fm.skinFile.focus();
		return false;
	}

	if(fm.screenShot.value == ""){
		alert("Please attach a screen shot of your skin.");
		fm.screenShot.focus();
		return false;
	}

	fm.submit();
	return false;
}

/* Logo Write */
function goLogoWrite(){
    var fm = document.fm;

	if(fm.title.value == ""){
		alert("Please enter a Logo Name for your logo.");
		fm.title.focus();
		return false;
	}

	if(fm.logoFile.value == ""){
		alert("Please attach a logo file.");
		fm.logoFile.focus();
		return false;
	}

	fm.submit();
	return false;
}

/* Skin, Logo Rating */
function procRating(theForm){
	var form = theForm;

	var voteSelected = false;
	for (var i=0;i<form.vote.length;i++) {
		if (form.vote[i].checked) {
			voteSelected = true;
			break;
		}
	}

	if(!voteSelected){
		alert('Select the point');
		form.vote[2].focus();
		return false;
	}

	return true;
}

function lang_move_pds() {
    var fm = document.langpds;  
    var sindex = fm.langtypepds.selectedIndex; 
    langselect = fm.langtypepds.options[sindex].value; 

	//lang =  getCookieGtype( 'gtype' );
	var lang =  "eng";

	var returl = document.URL;

	var partStr=returl.split("/");
	returl = partStr[4];

	var t_point_type = returl.lastIndexOf("lang=");
	if (t_point_type > 0) {
		var returl =  returl.substring(0,t_point_type-1);
	}

	var t_point_type2 = returl.lastIndexOf("?");
	if (t_point_type2 > 0) {
		var gourl_type =  "/"+lang+"/"+returl+"&lang="+lang;
	} else {
		var gourl_type =  "/"+lang+"/"+returl+"?lang="+lang;
	}
	gourl_type = gourl_type + "&alltype="+langselect;

	location.href = gourl_type;
}

