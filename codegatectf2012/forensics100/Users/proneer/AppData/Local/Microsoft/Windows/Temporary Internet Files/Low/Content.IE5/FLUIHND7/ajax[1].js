function AJAXRequest() {
	var xmlObj = false;
	var CBfunc,ObjSelf;
	ObjSelf=this;
	try { xmlObj=new XMLHttpRequest; }
	catch(e) {
		try { xmlObj=new ActiveXObject("MSXML2.XMLHTTP"); }
		catch(e2) {
			try { xmlObj=new ActiveXObject("Microsoft.XMLHTTP"); }
			catch(e3) { xmlObj=false; }
		}
	}
	if (!xmlObj) return false;
	this.method="POST";
	this.url;
	this.async=true;
	this.content="";
	this.callback=function(cbobj) {return;}
	this.send=function() {
		if(!this.method||!this.url||!this.async) return false;
		xmlObj.open (this.method, this.url, this.async);
		if(this.method=="POST") xmlObj.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		xmlObj.onreadystatechange=function() {
			if(xmlObj.readyState==4) {
				if(xmlObj.status==200) {
					ObjSelf.callback(xmlObj);
				}
			}
		}
		if(this.method=="POST") xmlObj.send(this.content);
		else xmlObj.send(null);
	}
}
function unall(){
	var ajaxobj=new AJAXRequest;

	url = "/index.php?act=user.unllocateduser";
	ajaxobj.method="GET";
	ajaxobj.url=url;
	document.getElementById('unalla').disabled = true;
	ajaxobj.callback=function(xmlobj) {
		var text;
		text = xmlobj.responseText;
		if (text == "1") {
			top.mainFrame.location = "index.php?act=user.companyinfo&type=unall";
		} else {
			alert(text);
		}
	}
	ajaxobj.send();
}