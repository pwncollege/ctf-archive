function sendReport(soft_id,f){
	var ajaxobj=new AJAXRequest;
	var email;
	var message;
	
	if(document.getElementById('problem_type')){
		if(document.getElementById('problem_type').selectedIndex==0) f=0;
		else f=1;	
	}
	
	var code=escape(document.getElementById('valicode').value);
	if(document.getElementById('email5')){
		email = document.getElementById('email5').value;
	}
	if(document.getElementById('message')){
		message = document.getElementById('message').value;
	}
	if(email == '' || !IsEmail(email)){
		alert("Please input the correct email address.");
		if(document.getElementById('email5').type == "text"){
			document.getElementById('email5').select();
		}
		return false;
	}
	if(document.getElementById('valicode').value==''){alert('Please type the code shown.');document.getElementById('valicode').select();return false;}
	if(message == ''){
		alert('Please enter your message.');
		document.getElementById('message').focus();
		return false;
	}
	url = "/download_link_report_ok1.php?soft_id="+soft_id+"&email="+email+"&message="+URLEncode1(message)+'&valicode='+code+'&f='+f;
	document.getElementById('message_send').disabled=true;
	ajaxobj.method="GET";
	ajaxobj.url=url;
	ajaxobj.callback=function(xmlobj) {
		document.getElementById('message_send').disabled=false;
		var text;
		text = xmlobj.responseText;
		if (text == "1") {
			if(document.getElementById('email5')){
				if(document.getElementById('email5').type == 'text'){
					document.getElementById('email5').value = '';
				}
			}
			hide();
			if(f==1){
				alert('Thank you for informing us the viruses link, we will try our best to with the virus and reply to you within one business day.');
			}
			else{
			alert("Thank you for informing us the invalid download link, we will try our best to find the downloadable link and reply to you within one business day.");
			}
			return false;
		}else{
			if(text=='Please type the code shown.'){alert('Please type the code shown.');}
			else{
				//hide();return false;
				alert(text);
				}
		}
	}
	ajaxobj.send();
}
function change_problem_type(){
	if(document.getElementById('problem_type').selectedIndex==0){
		document.getElementById('message').value=document.getElementById('link_report_div').innerHTML;
	}else{
		document.getElementById('message').value=document.getElementById('virus_report_div').innerHTML;	
	}
}
function URLEncode1(plaintext )
{
 // The Javascript escape and unescape functions do not correspond
 // with what browsers actually do...
 var SAFECHARS = "0123456789" +     // Numeric
     "ABCDEFGHIJKLMNOPQRSTUVWXYZ" + // Alphabetic
     "abcdefghijklmnopqrstuvwxyz" +
     "-_.!~*'()";     // RFC2396 Mark characters
 var HEX = "0123456789ABCDEF";

 var encoded = "";
 for (var i = 0; i < plaintext.length; i++ ) {
  var ch = plaintext.charAt(i);
     if (ch == " ") {
      encoded += "+";    // x-www-urlencoded, rather than %20
  } else if (SAFECHARS.indexOf(ch) != -1) {
      encoded += ch;
  } else {
      var charCode = ch.charCodeAt(0);
   if (charCode > 255) {
       alert( "Unicode Character '"
                        + ch
                        + "' cannot be encoded using standard URL encoding.\n" +
              "(URL encoding only supports 8-bit characters.)\n" +
        "A space (+) will be substituted." );
    encoded += "+";
   } else {
    encoded += "%";
    encoded += HEX.charAt((charCode >> 4) & 0xF);
    encoded += HEX.charAt(charCode & 0xF);
   }
  }
 } // for
 return encoded;
}

function show(_height){
	
	var ajaxobj=new AJAXRequest;
	if(soft_id == '' || softurl == '' || softname == ''){
		return false;
	}
	url = "/download_link_report1.php?soft_id="+soft_id+"&softurl="+URLEncode1(softurl)+"&softname="+URLEncode1(softname)+"&version="+URLEncode1(version);
	if(_height) url+='&f=1'
	ajaxobj.method="GET";
	ajaxobj.url=url;
	ajaxobj.callback=function(xmlobj) {
		var text;
		text = xmlobj.responseText;
		if (text != "") {
			document.getElementById("id2").innerHTML = text;
			document.getElementById('id1').style.top="0px";
			document.getElementById('id1').style.left=document.body.scrollLeft+"px";
			document.getElementById('id1').style.height=document.body.clientHeight+"px";
			var _top = getScrollTop();
			document.getElementById('id2').style.top = (_top+200)+"px";
			document.getElementById('id1').style.display='block';
			document.getElementById('id2').style.display='block';
			document.getElementById('id2').style.left=((document.body.clientWidth-document.getElementById("id2").offsetWidth)/2)+'px';
			return false;
		}else{
			hide();
			return false;
		}
	}
	ajaxobj.send();
}
function show1(){
	var ajaxobj=new AJAXRequest;
	url = "/error_report_login.php";
	ajaxobj.method="GET";
	ajaxobj.url=url;
	ajaxobj.callback=function(xmlobj) {
		var text;
		text = xmlobj.responseText;
		if (text != "") {
			hide();
			document.getElementById("id2").innerHTML = text;
			document.getElementById('id1').style.top="0px";
			document.getElementById('id1').style.left=document.body.scrollLeft+"px";
			document.getElementById('id1').style.height=document.body.clientHeight+"px";
			var _top = getScrollTop();
			document.getElementById('id2').style.top = (_top+100)+"px";
			document.getElementById('id1').style.display='block';
			document.getElementById('id2').style.display='block';
			document.getElementById('id2').style.left=((document.body.clientWidth-document.getElementById("id2").offsetWidth)/2)+'px';
			return false;
		}else{
			show();
			return false;
		}
	}
	ajaxobj.send();
}
function hide(){
	document.getElementById('id1').style.display = 'none';
	document.getElementById('id2').style.display = 'none';
	return false;
}
function IsEmail(email) {
	var filter=/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/;
	return filter.test(email);
}
function check_form(obj) {
	var email=obj.email;
	if(email.style.display == 'none'){
		if (!IsEmail(email.value)) {
			alert('Please input the correct email address');
			email.focus();
			return false;
		}
	}
	if (obj.password) {
		if(obj.password.value == ''){
			alert('Please input Password');
			obj.password.focus();
			return false;
		}
	}
	document.getElementById('down_link_report').value = location.href;
	return true;
}
function getScrollTop(){
	return document.documentElement.scrollTop+document.body.scrollTop;
	/*
	if (document.compatMode && document.compatMode != "BackCompat")
	{
		return document.documentElement.scrollTop+document.body.scrollTop;
	}
	else
	{
		return document.body.scrollTop;
	}
	*/
}