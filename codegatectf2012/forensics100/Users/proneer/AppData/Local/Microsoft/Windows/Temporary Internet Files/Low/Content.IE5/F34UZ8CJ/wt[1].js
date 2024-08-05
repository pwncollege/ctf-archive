// WebTrends SmartSource Data Collector Tag
// Version: 8.6.2
// MS Version: 3.2.5
// Tag Builder Version: 3.0
// Created: 04/01/2011
function WebTrends(){
	var that=this;
	if (typeof(gDcsId)!="undefined"&&gDcsId) this.dcsid = gDcsId;
	else this.dcsid="not_a_valid_dcsid";
	if (typeof(gDomain)!="undefined"&&gDomain) this.domain = gDomain;
	else this.domain = "m.webtrends.com";
	if (typeof(gTimeZone)!="undefined"&&gTimeZone) this.timezone = gTimeZone;
	else this.timezone=-8;
	if (typeof(gFpcDom)!="undefined"&&gFpcDom) this.fpcdom = gFpcDom;
	else {
		if (/microsoft.com$/.test(window.location.hostname)) {
			this.fpcdom=".microsoft.com";
		} else {
			this.fpcdom=window.location.hostname;
		}
	}
	if (typeof(gOffsite)!="undefined"&&gOffsite)
	if (gOffsite==true||gOffsite=="true") this.fpcdom="";
	this.navigationtag="div,table";
	if (typeof(gTrackEvents)!="undefined"&&gTrackEvents&&(gTrackEvents==1||gTrackEvents=="1")) this.trackevents=true;
	else this.trackevents = false;
	this.evi={cookie:"MC1, A",qp:"WT.dcsvid, WT.z_Acookie",crumb:"",sep:""};
	this.enabled=true;
	this.i18n=true;
	this.fpc="WT_FPC";
	this.paidsearchparams="gclid";
	this.DCS={};
	this.WT={};
	this.DCSext={};
	this.DCSdir={};
	this.images=[];
	this.index=0;
	this.qp=[];
	this.exre=(function(){return(window.RegExp?new RegExp("dcs(uri)|(ref)|(aut)|(met)|(sta)|(sip)|(pro)|(byt)|(dat)|(p3p)|(cfg)|(redirect)|(cip)","i"):"");})();
	this.re=(function(){return(window.RegExp?(that.i18n?{"%25":/\%/g,"%23":/\#/g,"%26":/\&/g}:{"%09":/\t/g,"%20":/ /g,"%23":/\#/g,"%26":/\&/g,"%2B":/\+/g,"%3F":/\?/g,"%5C":/\\/g,"%22":/\"/g,"%7F":/\x7F/g,"%A0":/\xA0/g}):"");})();
}
WebTrends.prototype.dcsGetId=function(){
	if (this.enabled&&(document.cookie.indexOf(this.fpc+"=")==-1)&&(document.cookie.indexOf("WTLOPTOUT=")==-1)){
		document.write("<scr"+"ipt type='text/javascript' src='"+"http"+(window.location.protocol.indexOf('https:')==0?'s':'')+"://"+this.domain+"/"+this.dcsid+"/wtid.js"+"'><\/scr"+"ipt>");
	}
}
WebTrends.prototype.dcsGetCookie=function(name){
	var cookies=document.cookie.split("; ");
	var cmatch=[];
	var idx=0;
	var i=0;
	var namelen=name.length;
	var clen=cookies.length;
	for (i=0;i<clen;i++){
		var c=cookies[i];
		if ((c.substring(0,namelen+1))==(name+"=")){
			cmatch[idx++]=c;
		}
	}
	var cmatchCount=cmatch.length;
	if (cmatchCount>0){
		idx=0;
		if ((cmatchCount>1)&&(name==this.fpc)){
			var dLatest=new Date(0);
			for (i=0;i<cmatchCount;i++){
				var lv=parseInt(this.dcsGetCrumb(cmatch[i],"lv"));
				var dLst=new Date(lv);
				if (dLst>dLatest){
					dLatest.setTime(dLst.getTime());
					idx=i;
				}
			}
		}
		return unescape(cmatch[idx].substring(namelen+1));
	}
	else{
		return null;
	}
}
WebTrends.prototype.dcsGetCrumb=function(cval,crumb,sep){
	var aCookie=cval.split(sep||":");
	for (var i=0;i<aCookie.length;i++){
		var aCrumb=aCookie[i].split("=");
		if (crumb==aCrumb[0]){
			return aCrumb[1];
		}
	}
	return null;
}
WebTrends.prototype.dcsGetIdCrumb=function(cval,crumb){
	var id=cval.substring(0,cval.indexOf(":lv="));
	var aCrumb=id.split("=");
	for (var i=0;i<aCrumb.length;i++){
		if (crumb==aCrumb[0]){
			return aCrumb[1];
		}
	}
	return null;
}
WebTrends.prototype.dcsIsFpcSet=function(name,id,lv,ss){
	var c=this.dcsGetCookie(name);
	if (c){
		return ((id==this.dcsGetIdCrumb(c,"id"))&&(lv==this.dcsGetCrumb(c,"lv"))&&(ss==this.dcsGetCrumb(c,"ss")))?0:3;
	}
	return 2;
}
WebTrends.prototype.dcsFPC=function(){
	if (document.cookie.indexOf("WTLOPTOUT=")!=-1){
		return;
	}
	var WT=this.WT;
	var name=this.fpc;
	var dCur=new Date();
	var adj=(dCur.getTimezoneOffset()*60000)+(this.timezone*3600000);
	dCur.setTime(dCur.getTime()+adj);
	var dExp=new Date(dCur.getTime()+315360000000);
	var dSes=new Date(dCur.getTime());
	WT.co_f=WT.vtid=WT.vtvs=WT.vt_f=WT.vt_f_a=WT.vt_f_s=WT.vt_f_d=WT.vt_f_tlh=WT.vt_f_tlv="";
	if (document.cookie.indexOf(name+"=")==-1){
		if ((typeof(gWtId)!="undefined")&&(gWtId!="")){
			WT.co_f=gWtId;
		}
		else if ((typeof(gTempWtId)!="undefined")&&(gTempWtId!="")){
			WT.co_f=gTempWtId;
			WT.vt_f="1";
		}
		else{
			WT.co_f="2";
			var curt=dCur.getTime().toString();
			for (var i=2;i<=(32-curt.length);i++){
				WT.co_f+=Math.floor(Math.random()*16.0).toString(16);
			}
			WT.co_f+=curt;
			WT.vt_f="1";
		}
		if (typeof(gWtAccountRollup)=="undefined"){
			WT.vt_f_a="1";
		}
		WT.vt_f_s=WT.vt_f_d="1";
		WT.vt_f_tlh=WT.vt_f_tlv="0";
	}
	else{
		var c=this.dcsGetCookie(name);
		var id=this.dcsGetIdCrumb(c,"id");
		var lv=parseInt(this.dcsGetCrumb(c,"lv"));
		var ss=parseInt(this.dcsGetCrumb(c,"ss"));
		if ((id==null)||(id=="null")||isNaN(lv)||isNaN(ss)){
			return;
		}
		WT.co_f=id;
		var dLst=new Date(lv);
		WT.vt_f_tlh=Math.floor((dLst.getTime()-adj)/1000);
		dSes.setTime(ss);
		if ((dCur.getTime()>(dLst.getTime()+1800000))||(dCur.getTime()>(dSes.getTime()+28800000))){
			WT.vt_f_tlv=Math.floor((dSes.getTime()-adj)/1000);
			dSes.setTime(dCur.getTime());
			WT.vt_f_s="1";
		}
		if ((dCur.getDay()!=dLst.getDay())||(dCur.getMonth()!=dLst.getMonth())||(dCur.getYear()!=dLst.getYear())){
			WT.vt_f_d="1";
		}
	}
	WT.co_f=escape(WT.co_f);
	WT.vtid=(typeof(this.vtid)=="undefined")?WT.co_f:(this.vtid||"");
	WT.vtvs=(dSes.getTime()-adj).toString();
	var expiry="; expires="+dExp.toGMTString();
	var cur=dCur.getTime().toString();
	var ses=dSes.getTime().toString();
	document.cookie=name+"="+"id="+WT.co_f+":lv="+cur+":ss="+ses+expiry+"; path=/"+(((this.fpcdom!=""))?("; domain="+this.fpcdom):(""));
	var rc=this.dcsIsFpcSet(name,WT.co_f,cur,ses);
	if (rc!=0){
		WT.co_f=WT.vtvs=WT.vt_f_s=WT.vt_f_d=WT.vt_f_tlh=WT.vt_f_tlv="";
		if (typeof(this.vtid)=="undefined"){
			WT.vtid="";
		}
		WT.vt_f=WT.vt_f_a=rc;
    }
}
WebTrends.prototype.dcsQP=function(N){
	if (typeof(N)=="undefined"){
		return "";
	}
	var qry=location.search.substring(1);
	if (qry!=""){
		var pairs=qry.split("&");
		for (var i=0;i<pairs.length;i++){
			var pos=pairs[i].indexOf("=");
			if (pos!=-1){
				if (pairs[i].substring(0,pos)==N){
					this.qp[this.qp.length]=(i==0?"":"&")+pairs[i];
					return pairs[i].substring(pos+1);
				}
			}
		}
	}
	return "";
}
WebTrends.prototype.dcsEvt=function(evt,tag){
	var e=evt.target||evt.srcElement;
	while (e.tagName&&(e.tagName.toLowerCase()!=tag.toLowerCase())){
		e=e.parentElement||e.parentNode;
	}
	return e;
}
WebTrends.prototype.dcsNavigation=function(evt){
	var id="";
	var cname="";
	var elems=this.dcsSplit(this.navigationtag);
	var elen=elems.length;
	var i,e,elem;
	for (i=0;i<elen;i++){
		elem=elems[i];
		if (elem.length){
			e=this.dcsEvt(evt,elem);
			id=(e.getAttribute&&e.getAttribute("id"))?e.getAttribute("id"):"";
			cname=e.className||"";
			if (id.length||cname.length){
				break;
			}
		}
	}
	return id.length?id:cname;
}
WebTrends.prototype.dcsBind=function(event,func){
	if ((typeof(func)=="function")&&document.body){
		if (document.body.addEventListener){
			document.body.addEventListener(event, func.wtbind(this), true);
		}
		else if(document.body.attachEvent){
			document.body.attachEvent("on"+event, func.wtbind(this));
		}
	}
}
WebTrends.prototype.dcsET = function() {
    var e = (navigator.appVersion.indexOf("MSIE") != -1) ? "click" : "mousedown";
    this.dcsBind(e, this.dcsFormButton);
    this.dcsBind("submit", this.dcsFormButton);
    this.dcsBind(e, this.dcsMSLinkTrack);
    this.dcsBind(e, this.dcsMSImageMap);
}
WebTrends.prototype.dcsMultiTrack=function(){
	var args=dcsMultiTrack.arguments?dcsMultiTrack.arguments:arguments;
	if (args.length%2==0){
		this.dcsSetProps(args);
		var dCurrent=new Date();
		this.DCS.dcsdat=dCurrent.getTime();
		this.dcsFPC();
		this.dcsTag();
	}
}
WebTrends.prototype.dcsCleanUp=function(){
	this.DCS={};
	this.WT={};
	this.DCSext={};
	if (arguments.length%2==0){
		this.dcsSetProps(arguments);
	}
}
WebTrends.prototype.dcsSetProps=function(args){
	for (var i=0;i<args.length;i+=2){
		if (args[i].indexOf('WT.')==0){
			this.WT[args[i].substring(3)]=args[i+1];
		}
		else if (args[i].indexOf('DCS.')==0){
			this.DCS[args[i].substring(4)]=args[i+1];
		}
		else if (args[i].indexOf('DCSext.')==0){
			this.DCSext[args[i].substring(7)]=args[i+1];
		}
		else if (args[i].indexOf('DCSdir.')==0){
			this.DCSdir[args[i].substring(7)]=args[i+1];
		}
	}
}
WebTrends.prototype.dcsSplit=function(list){
	var items=list.toLowerCase().split(",");
	var len=items.length;
	for (var i=0;i<len;i++){
		items[i]=items[i].replace(/^\s*/,"").replace(/\s*$/,"");
	}
	return items;
}
WebTrends.prototype.dcsFormButton=function(evt){
	evt=evt||(window.event||"");
	if (evt&&((typeof(evt.which)!="number")||(evt.which==1))){
		var tags=["INPUT","BUTTON"];
		for (var j=0;j<tags.length;j++){
			var e=this.dcsEvt(evt,tags[j]);
			var type=e.type||"";
			if (type&&((type=="submit")||(type=="image")||(type=="button")||(type=="reset"))||((type=="text")&&((evt.which||evt.keyCode)==13))){
				var uri="";
				var ttl="";
				var id=0;
				if (e.form){
					uri=e.form.action||window.location.pathname;
					ttl=e.form.id||e.form.name||e.form.className||"Unknown";
					id=(e.form.method&&(e.form.method.toLowerCase()=="post"))?"27":"26";
				}
				else{
					uri=window.location.pathname;
					ttl=e.name||e.id||"Unknown";
					id=(tags[j].toLowerCase()=="input")?"28":"29";
				}
				if (uri&&ttl&&(evt.keyCode!=9)){
					this.dcsMultiTrack("DCS.dcsuri",uri,"WT.ti","FormButton:"+ttl,"WT.dl",2,"WT.nv",this.dcsNavigation(evt),"DCSext.wtNavigation",this.dcsNavigation(evt));
				}
				this.DCS.dcsuri=this.WT.ti=this.WT.dl=this.WT.nv="";
				break;
			}
		}
	}
}
WebTrends.prototype.dcsEvi=function(){
	var t=this;
	var evi=t.evi;
	var qp=evi.qp;
	var c=t.dcsGetCookie(evi.cookie);
	if (c){
		if ((evi.crumb.length>0)&&(evi.sep.length>0)){
			c=t.dcsGetCrumb(c,evi.crumb,evi.sep);
		}
		if (c){
			if (qp.indexOf("WT.")==0){
				t.WT[qp.substring(3)]=c;
			}
			else if (qp.indexOf("DCS.")==0){
				t.DCS[qp.substring(4)]=c;
			}
			else if (qp.indexOf("DCSext.")==0){
				t.DCSext[qp.substring(7)]=c;
			}
			else{
				t.DCSext[qp]=c;
			}
		}
	}
}
WebTrends.prototype.dcsAdv=function(){
	if (this.trackevents&&(typeof(this.dcsET)=="function")){
		if (window.addEventListener){
			window.addEventListener("load",this.dcsET.wtbind(this),false);
		}
		else if (window.attachEvent){
			window.attachEvent("onload",this.dcsET.wtbind(this));
		}
	}
	this.dcsFPC();
	this.dcsEvi();
	this.dcsMSNvr();
}
WebTrends.prototype.dcsVar=function(){
	var dCurrent=new Date();
	var WT=this.WT;
	var DCS=this.DCS;
	WT.tz=parseInt(dCurrent.getTimezoneOffset()/60*-1)||"0";
	WT.bh=dCurrent.getHours()||"0";
	WT.ul=navigator.appName=="Netscape"?navigator.language:navigator.userLanguage;
	if (typeof(screen)=="object"){
		WT.cd=navigator.appName=="Netscape"?screen.pixelDepth:screen.colorDepth;
		WT.sr=screen.width+"x"+screen.height;
	}
	if (typeof(navigator.javaEnabled())=="boolean"){
		WT.jo=navigator.javaEnabled()?"Yes":"No";
	}
	if (document.title){
		if (window.RegExp){
			var tire=new RegExp("^"+window.location.protocol+"//"+window.location.hostname+"\\s-\\s");
			WT.ti=document.title.replace(tire,"");
		}
		else{
			WT.ti=document.title;
		}
	}
	WT.js="Yes";
	WT.jv=(function(){
		var agt=navigator.userAgent.toLowerCase();
		var major=parseInt(navigator.appVersion);
		var mac=(agt.indexOf("mac")!=-1);
		var ff=(agt.indexOf("firefox")!=-1);
		var ff0=(agt.indexOf("firefox/0.")!=-1);
		var ff10=(agt.indexOf("firefox/1.0")!=-1);
		var ff15=(agt.indexOf("firefox/1.5")!=-1);
		var ff20=(agt.indexOf("firefox/2.0")!=-1);
		var ff3up=(ff&&!ff0&&!ff10&!ff15&!ff20);
		var nn=(!ff&&(agt.indexOf("mozilla")!=-1)&&(agt.indexOf("compatible")==-1));
		var nn4=(nn&&(major==4));
		var nn6up=(nn&&(major>=5));
		var ie=((agt.indexOf("msie")!=-1)&&(agt.indexOf("opera")==-1));
		var ie4=(ie&&(major==4)&&(agt.indexOf("msie 4")!=-1));
		var ie5up=(ie&&!ie4);
		var op=(agt.indexOf("opera")!=-1);
		var op5=(agt.indexOf("opera 5")!=-1||agt.indexOf("opera/5")!=-1);
		var op6=(agt.indexOf("opera 6")!=-1||agt.indexOf("opera/6")!=-1);
		var op7up=(op&&!op5&&!op6);
		var jv="1.1";
		if (ff3up){
			jv="1.8";
		}
		else if (ff20){
			jv="1.7";
		}
		else if (ff15){
			jv="1.6";
		}
		else if (ff0||ff10||nn6up||op7up){
			jv="1.5";
		}
		else if ((mac&&ie5up)||op6){
			jv="1.4";
		}
		else if (ie5up||nn4||op5){
			jv="1.3";
		}
		else if (ie4){
			jv="1.2";
		}
		return jv;
	})();
	WT.ct="unknown";
	if (document.body&&document.body.addBehavior){
		try{
			document.body.addBehavior("#default#clientCaps");
			WT.ct=document.body.connectionType||"unknown";
			document.body.addBehavior("#default#homePage");
			WT.hp=document.body.isHomePage(location.href)?"1":"0";
		}
		catch(e){
		}
	}
	if (document.all){
		WT.bs=document.body?document.body.offsetWidth+"x"+document.body.offsetHeight:"unknown";
	}
	else{
		WT.bs=window.innerWidth+"x"+window.innerHeight;
	}
	WT.fv=(function(){
		var i,flash;
		if (window.ActiveXObject){
			for(i=10;i>0;i--){
				try{
					flash=new ActiveXObject("ShockwaveFlash.ShockwaveFlash."+i);
					return i+".0";
				}
				catch(e){
				}
			}
		}
		else if (navigator.plugins&&navigator.plugins.length){
			for (i=0;i<navigator.plugins.length;i++){
				if (navigator.plugins[i].name.indexOf('Shockwave Flash')!=-1){
					return navigator.plugins[i].description.split(" ")[2];
				}
			}
		}
		return "Not enabled";
	})();
	WT.slv=(function(){
		var slv="Not enabled";
		try{
			if (navigator.userAgent.indexOf('MSIE')!=-1){
				var sli = new ActiveXObject('AgControl.AgControl');
				if (sli){
					slv="Unknown";
				}
			}
			else if (navigator.plugins["Silverlight Plug-In"]){
				slv="Unknown";
			}
		}
		catch(e){
		}
		if (slv!="Not enabled"){
			var i,j,v;
			if ((typeof(Silverlight)=="object")&&(typeof(Silverlight.isInstalled)=="function")){
				for(i=9;i>0;i--){
					for (j=9;j>=0;j--){
						v=i+"."+j;
						if (Silverlight.isInstalled(v)){
							slv=v;
							break;
						}
					}
					if (slv==v){
						break;
					}
				}
			}
		}
		return slv;
	})();
	if (this.i18n){
		if (typeof(document.defaultCharset)=="string"){
			WT.le=document.defaultCharset;
		}
		else if (typeof(document.characterSet)=="string"){
			WT.le=document.characterSet;
		}
		else{
			WT.le="unknown";
		}
	}
	WT.tv="8.6.2";
	WT.dl="0";
	WT.ssl=(window.location.protocol.indexOf('https:')==0)?"1":"0";
	DCS.dcsdat=dCurrent.getTime();
	DCS.dcssip=window.location.hostname;
	DCS.dcsuri=window.location.pathname;
	WT.es=DCS.dcssip+DCS.dcsuri;
	if (window.location.search){
		DCS.dcsqry=window.location.search;
		if (this.qp.length>0){
			for (var i=0;i<this.qp.length;i++){
				var pos=DCS.dcsqry.indexOf(this.qp[i]);
				if (pos!=-1){
					var front=DCS.dcsqry.substring(0,pos);
					var end=DCS.dcsqry.substring(pos+this.qp[i].length,DCS.dcsqry.length);
					DCS.dcsqry=front+end;
				}
			}
		}
	}
	if (DCS.dcsqry){
		var dcsqry=DCS.dcsqry.toLowerCase();
		var params=this.paidsearchparams.length?this.paidsearchparams.toLowerCase().split(","):[];
		for (var i=0;i<params.length;i++){
			if (dcsqry.indexOf(params[i]+"=")!=-1){
				WT.srch="1";
				break;
			}
		}
	}
	if ((window.document.referrer!="")&&(window.document.referrer!="-")){
		if (!(navigator.appName=="Microsoft Internet Explorer"&&parseInt(navigator.appVersion)<4)){
			DCS.dcsref=window.document.referrer;
		}
	}
}
WebTrends.prototype.dcsEscape=function(S, REL){
	if (REL!=""){
		S=S.toString();
		for (var R in REL){
 			if (REL[R] instanceof RegExp){
				S=S.replace(REL[R],R);
 			}
		}
		return S;
	}
	else{
		return escape(S);
	}
}
WebTrends.prototype.dcsA=function(N,V){
    if (this.i18n&&(this.exre!="")&&!this.exre.test(N)){
        if (N=="dcsqry"){
            var newV="";
            var params=V.substring(1).split("&");
            for (var i=0;i<params.length;i++){
                var pair=params[i];
                var pos=pair.indexOf("=");
                if (pos!=-1){
                    var key=pair.substring(0,pos);
                    var val=pair.substring(pos+1);
                    if (i!=0){
                        newV+="%26";
                    }
                    newV+=key+"="+this.dcsEncode(val);
                }
            }
            V=V.substring(0,1)+newV;
        }
        else{
            V=this.dcsEncode(V);
        }
        return "&"+N+"="+V;
    }
    return "&"+N+"="+this.dcsEscape(V, this.re);
}
WebTrends.prototype.dcsEncode=function(S){
	return (typeof(encodeURIComponent)=="function")?encodeURIComponent(S):escape(S);
}
WebTrends.prototype.dcsCreateImage=function(dcsSrc){
	if (document.images){
		this.images[this.index]=new Image();
		this.images[this.index].src=dcsSrc;
		this.index++;
	}
	else{
		document.write('<IMG ALT="" BORDER="0" NAME="DCSIMG" WIDTH="1" HEIGHT="1" SRC="'+dcsSrc+'">');
	}
}
WebTrends.prototype.dcsMeta=function(){
	var elems;
	if (document.all){
		elems=document.all.tags("meta");
	}
	else if (document.documentElement){
		elems=document.getElementsByTagName("meta");
	}
	if (typeof(elems)!="undefined"){
		var length=elems.length;
		for (var i=0;i<length;i++){
			var name=elems.item(i).name;
			var content=elems.item(i).content;
			var equiv=elems.item(i).httpEquiv;
			if (name.length>0){
				if (name.toUpperCase().indexOf("WT.")==0){
					this.WT[name.substring(3)]=content;
				}
				else if (name.toUpperCase().indexOf("DCSEXT.")==0){
					this.DCSext[name.substring(7)]=content;
				}
				else if (name.toUpperCase().indexOf("DCSDIR.")==0){
					this.DCSdir[name.substring(7)]=content;
				}
				else if (name.toUpperCase().indexOf("DCS.")==0){
					this.DCS[name.substring(4)]=content;
				}
			}
		}
	}
	this.dcsMSVar();
}
WebTrends.prototype.dcsTag=function(){
	if (document.cookie.indexOf("WTLOPTOUT=")!=-1){
		return;
	}
	var WT=this.WT;
	var DCS=this.DCS;
	var DCSext=this.DCSext;
	var i18n=this.i18n;
	var P="http"+(window.location.protocol.indexOf('https:')==0?'s':'')+"://"+this.domain+(this.dcsid==""?'':'/'+this.dcsid)+"/dcs.gif?";
	if (i18n){
		WT.dep="";
	}
	for (var N in DCS){
 		if (DCS[N]&&(typeof DCS[N]!="function")){
			P+=this.dcsA(N,DCS[N]);
		}
	}
	var keys=["co_f","vtid","vtvs","vt_f_tlv"];
	for (var i=0;i<keys.length;i++){
		var key=keys[i];
		if (WT[key]){
			P+=this.dcsA("WT."+key,WT[key]);
			delete WT[key];
		}
	}
	for (N in WT){
		if (WT[N]&&(typeof WT[N]!="function")){
			P+=this.dcsA("WT."+N,WT[N]);
		}
	}
	for (N in DCSext){
		if (DCSext[N]&&(typeof DCSext[N]!="function")){
			if (i18n){
				WT.dep=(WT.dep.length==0)?N:(WT.dep+";"+N);
			}
			P+=this.dcsA(N,DCSext[N]);
		}
	}
	if (i18n&&(WT.dep.length>0)){
		P+=this.dcsA("WT.dep",WT.dep);
	}
	if (P.length>2048&&navigator.userAgent.indexOf('MSIE')>=0){
		P=P.substring(0,2040)+"&WT.tu=1";
	}
	this.dcsCreateImage(P);
	this.WT.ad="";
	this.dcsMSSplitTag(P);
	this.dcsMSClearVars();
}
WebTrends.prototype.dcsDebug=function(){
	var t=this;
	var i=t.images[0].src;
	var q=i.indexOf("?");
	var r=i.substring(0,q).split("/");
	var m="<b>Protocol</b><br><code>"+r[0]+"<br></code>";
	m+="<b>Domain</b><br><code>"+r[2]+"<br></code>";
	m+="<b>Path</b><br><code>/"+r[3]+"/"+r[4]+"<br></code>";
	m+="<b>Query Params</b><code>"+i.substring(q+1).replace(/\&/g,"<br>")+"</code>";
	m+="<br><b>Cookies</b><br><code>"+document.cookie.replace(/\;/g,"<br>")+"</code>";
	if (t.w&&!t.w.closed){
		t.w.close();
	}
	t.w=window.open("","dcsDebug","width=500,height=650,scrollbars=yes,resizable=yes");
	t.w.document.write(m);
	t.w.focus();
}
WebTrends.prototype.dcsCollect=function(){
    if (this.enabled){
        this.dcsVar();
        this.dcsMeta();
        this.dcsAdv();
        this.dcsTag();
    }
}

function dcsMultiTrack(){
	if (typeof(_tag)!="undefined"){
		return(_tag.dcsMultiTrack());
	}
}

function dcsDebug(){
	if (typeof(_tag)!="undefined"){
		return(_tag.dcsDebug());
	}
}

Function.prototype.wtbind = function(obj){
	var method=this;
	var temp=function(){
		return method.apply(obj,arguments);
	};
	return temp;
}
WebTrends.prototype.dcsMSVar=function(){
	if (typeof(this.DCSdir.ReferrerAsURI)!="undefined" && this.DCSdir.ReferrerAsURI && typeof(this.DCS.dcsref)!="undefined" && this.DCS.dcsref) {
		this.DCSext.wtEvtSrc=this.DCS.dcsref.match(/(?:\w+:\/\/)?([^?\s]+)\??/)[1];
	} else {
		this.DCSext.wtEvtSrc=this.DCS.dcssip + this.DCS.dcsuri;
	}
	if (typeof(this.WT.sp)!="undefined") {
		this.WT.sv_sp = this.WT.sp;
	}
	this.DCSext.wtDrillDir = this.dcsMSDrillDir();
	try{
		if(navigator.userAgent.indexOf('MSIE')!=-1){
			var wtsli = new ActiveXObject('AgControl.AgControl');
			if(wtsli){this.WT.sli="Installed";}
		} else {
			if (navigator.plugins["Silverlight Plug-In"]){
				this.WT.sli="Installed";
			}
		}
		}
	catch(e){}
	this.WT.sli=this.WT.sli||"Not Installed";
	this.WT.z_locale = this.dcsMSLocaleScrape();
	if (this.dcsGetCookie("MC1")!= null) this.WT.dcsvid=this.dcsGetCrumb(this.dcsGetCookie("MC1"),"GUID","&");
	this.WT.z_anonid=this.dcsMSGetCrumb("A","I","&");
	this.WT.z_rioid=(typeof(wt_GetCurrentCellCode)!="undefined")?wt_GetCurrentCellCode():this.dcsGetCookie("R");
	this.WT.z_msresearch_u=this.dcsMSmsresearchRead("userid");
	this.WT.z_msresearch_s=this.dcsMSmsresearchRead("surveys");
	this.WT.z_MUID=this.dcsGetCookie("MUID");

	if (typeof(this.DCSdir.OnPremiseSDC)!="undefined") {
		this.domain2=this.DCSdir.OnPremiseSDC;
	} else {
		this.domain2='';
	}
	if (typeof(this.DCSdir.OnPremiseDCSID)!="undefined") {
	    this.dcsid2 = this.DCSdir.OnPremiseDCSID;
	} else {
		this.dcsid2=this.dcsid;
	}
}
WebTrends.prototype.dcsMSLocaleScrape=function(){
	this.WT.z_locale = this.dcsMSLocaleFromString(this.WT.z_locale);
	var fromURL = this.dcsMSLocaleFromString(this.DCS.dcsuri);
	var fromQry = this.dcsMSLocaleFromString(this.DCS.dcsqry);
	var fromMAG = this.dcsMSLocaleFromString(this.DCSext.wt_maglocale);
	var fromOffice = this.dcsMSLocaleFromString(this.DCSext.oo_ul);
	var fromDLC = this.dcsMSLocaleFromString(this.DCSext.dsplc);
	var fromMOCP = this.dcsMSLocaleFromString(this.DCSext.msintl_locale);
	var wtLocale = this.WT.z_locale||fromURL||fromQry||fromMAG||fromOffice||fromDLC||fromMOCP;
	return wtLocale;
}
WebTrends.prototype.dcsMSLocaleFromString=function(text){
	var loc = /\b\w\w[-\.\/ _]\w\w\b/;
	var del = /[-\.\/ _]/;
	var ext = '';
	if (loc.test(text)) {
		ext = text.match(loc)+'';
		ext = ext.replace(del, "-");
		ext = ext.toLowerCase();
	}
	return ext;
}
WebTrends.prototype.dcsMSSplitTag=function(P){
	if (typeof(this.domain2)!="undefined" && this.domain2!='') {
		P=P.replace(this.domain, this.domain2);
		P=P.replace(this.dcsid, this.dcsid2);
		this.dcsCreateImage(P);
	}
}
WebTrends.prototype.dcsMSClearVars=function(){
	if (this.DCSdir.ClearVars) {
		var wtClearList = this.DCSdir.ClearVars.split(",");
		wtClearList = wtClearList.concat('WT.z_ea_name', 'WT.z_ea_actionoffer', 'WT.z_ea_targetcampaign', 'WT.mc_id', 'WT.mc_ev', 'WT.si_n', 'WT.si_x', 'WT.si_p', 'WT.z_convert', 'WT.ad', 'WT.ac', 'WT.tx_u', 'WT.tx_s', 'WT.tx_e', 'WT.tx_i', 'WT.tx_id', 'WT.tx_it', 'WT.tx_cartid', 'WT.si_cs');
		for(var i=0;i<wtClearList.length;i++){
			if (wtClearList[i].indexOf('WT.')==0){
				this.WT[wtClearList[i].substring(3)]='';
			}
			else if (wtClearList[i].indexOf('DCS.')==0){
				this.DCS[wtClearList[i].substring(4)]='';
			}
			else if (wtClearList[i].indexOf('DCSext.')==0){
				this.DCSext[wtClearList[i].substring(7)]='';
			}
		}
	}
}
WebTrends.prototype.dcsMSDrillDir=function(){
	var gDirLevels = 5;
	var gFpath = window.location.pathname.substring(window.location.pathname.indexOf('/')+1,window.location.pathname.lastIndexOf('/')+1).toLowerCase();
	if(gFpath==''){gFpath="/";}
	else{
		var gSplit=gFpath.split("/");
		gFpath="";
		for(var i=1;i<gSplit.length&&i<=gDirLevels;i++){
			gFpath+="/";
			for(var b=0;b<i;b++){
					gFpath+=gSplit[b]+"/";
			}
			if(i!=gDirLevels&&i!=gSplit.length-1){
				gFpath+=";";
			}
		}
	}
	return gFpath;
}
WebTrends.prototype.dcsIsOnsite = function(host) {
    if (host.length > 0) {
        host = host.toLowerCase();
        if (host == window.location.hostname.toLowerCase()) {
            return true;
        }
        if (typeof (this.onsitedoms.test) == "function") {
            return this.onsitedoms.test(host);
        }
        else if (this.onsitedoms.length > 0) {
            var doms = this.dcsSplit(this.onsitedoms);
            var len = doms.length;
            for (var i = 0; i < len; i++) {
                if (host == doms[i]) {
                    return true;
                }
            }
        }
    }
    return false;
}
WebTrends.prototype.dcsMSImageMap = function(evt) {
    evt = evt || (window.event || "");
    if (evt && (typeof evt.which != "number" || evt.which == 1)) {
        var e = this.dcsEvt(evt, "AREA");
        var f = this.dcsEvt(evt, "IMG");
        if (e.href && e.protocol && e.protocol.indexOf("http") != -1 && !this.dcsMSLinkTrackException(e)) {
            if ((e.onclick) || (e.onmousedown)) { this.dcsMSSetVarCap(e); }
            var hn = e.hostname ? (e.hostname.split(":")[0]) : "";
            var qry = e.search ? e.search.substring(e.search.indexOf("?") + 1, e.search.length) : "";
            var pth = e.pathname ? ((e.pathname.indexOf("/") != 0) ? "/" + e.pathname : e.pathname) : "/";
            var ti = '';
            var dl = '1';
            if (f.alt) { ti = f.alt; }
            else { if (document.all) { ti = e.title || e.innerText || e.innerHTML || ""; } else { ti = e.title || e.text || e.innerHTML || ""; } }
            hn = this.DCS.setvar_dcssip || hn;
            pth = this.DCS.setvar_dcsuri || pth;
            qry = this.DCS.setvar_dcsqry || qry;
            ti = this.WT.setvar_ti || ti;
            ti = this.dcsTrim(ti);
            dl = this.WT.setvar_dl || dl;
            this.WT.mc_id = this.WT.setvar_mc_id || "";
            this.WT.sp = this.WT.ad = this.DCS.setvar_dcsuri = this.DCS.setvar_dcssip = this.DCS.setvar_dcsqry = this.WT.setvar_ti = this.WT.setvar_mc_id = "";
            if (e.attributes.getNamedItem("cid")) this.DCSext.wt_linkid = e.attributes.getNamedItem("cid").value;
            this.dcsMultiTrack("DCS.dcssip", hn, "DCS.dcsuri", pth, "DCS.dcsqry", this.trimoffsiteparams ? "" : qry, "DCS.dcsref", window.location, "WT.ti", "Img Map:" + ti, "WT.dl", dl, "WT.nv", this.dcsNavigation(evt), "DCSext.wtNavigation", this.dcsNavigation(evt), "WT.sp", "", "WT.ad", "");
            this.DCS.dcsref = this.WT.ti = this.WT.dl = this.WT.nv = "";
        }
    }
}
WebTrends.prototype.dcsMSLinkTrack = function(evt) {
    evt = evt || (window.event || "");
    if (evt && ((typeof (evt.which) != "number") || (evt.which == 1))) {
        var e = this.dcsEvt(evt, "A");
        var f = this.dcsEvt(evt, "IMG");
        if (e.href && e.protocol && e.protocol.indexOf("http") != -1 && !this.dcsMSLinkTrackException(e)) {
            if ((e.onclick) || (e.onmousedown)) { this.dcsMSSetVarCap(e); }
            var hn = e.hostname ? (e.hostname.split(":")[0]) : "";
        	var qry=e.search?e.search.substring(e.search.indexOf("?")+1,e.search.length):"";
        	var pth=e.pathname?((e.pathname.indexOf("/")!=0)?"/"+e.pathname:e.pathname):"/";
        	var ti = '';
        	var dl = '1';
        	if(f.alt){ti=f.alt;}
        	else{if(document.all){ti=e.title||e.innerText||e.innerHTML||"";}else{ti=e.title||e.text||e.innerHTML||"";}}
        	hn=this.DCS.setvar_dcssip||hn;
        	pth=this.DCS.setvar_dcsuri||pth;
        	qry=this.DCS.setvar_dcsqry||qry;
        	ti=this.WT.setvar_ti||ti;
        	dl=this.WT.setvar_dl||dl;
        	ti=this.dcsTrim(ti);
        	this.WT.mc_id=this.WT.setvar_mc_id||"";
        	this.WT.sp=this.WT.ad=this.DCS.setvar_dcsuri=this.DCS.setvar_dcssip=this.DCS.setvar_dcsqry=this.WT.setvar_ti=this.WT.setvar_mc_id="";
        	//GDH 3.1 Added DCSext.wtNavigation
        	this.dcsMultiTrack("DCS.dcssip",hn,"DCS.dcsuri",pth,"DCS.dcsqry",this.trimoffsiteparams?"":qry,"DCS.dcsref",window.location,"WT.ti","Link:"+ti,"WT.dl",dl,"WT.nv",this.dcsNavigation(evt),"DCSext.wtNavigation",this.dcsNavigation(evt),"WT.sp","","WT.ad","");
        	this.DCS.dcssip=this.DCS.dcsuri=this.DCS.dcsqry=this.DCS.dcsref=this.WT.ti=this.WT.dl=this.WT.nv="";
		}
	}
}
WebTrends.prototype.dcsMSLinkTrackException=function(n){
	try {
		var b = 0;
		if (this.DCSdir.gTrackExceptions) {
			var e = this.DCSdir.gTrackExceptions.split(",");
			while (b != 1) {
				if (n.tagName&&n.tagName=="body") {
					b = 1;
					return false
				} else {
					if (n.className) {
						var f = String(n.className).split(" ");
						for (var c = 0; c < e.length; c++) for (var d = 0; d < f.length; d++) if (f[d] == e[c]) {
							b = 1;
							return true
						}
					}
				}
				n = n.parentNode
			}
		} else {
			return false;
		}
	} catch(g){}
}
WebTrends.prototype.dcsMSSetVar=function(){
	var args=dcsSetVar.arguments?dcsSetVar.arguments:arguments;
	if ((args.length%2==0)){
		for (var i=0;i<args.length;i+=2){
			if (args[i].indexOf('WT.')==0){
				if (this.dcsMSSetVarValidate(args[i])){
					this.WT["setvar_"+args[i].substring(3)]=args[i+1];
				}
				else{this.WT[args[i].substring(3)]=args[i+1];}
			}
			else if (args[i].indexOf('DCS.')==0){
				if (this.dcsMSSetVarValidate(args[i])){
					this.DCS["setvar_"+args[i].substring(4)]=args[i+1];
				}
				else{this.DCS[args[i].substring(4)]=args[i+1];}

			}
			else if (args[i].indexOf('DCSext.')==0){
				if (this.dcsMSSetVarValidate(args[i])){
					this.DCSext["setvar_"+args[i].substring(7)]=args[i+1];
				}
				else{this.DCSext[args[i].substring(7)]=args[i+1];}
			}
			else if (args[i].indexOf('DCSdir.')==0){
				if (this.dcsMSSetVarValidate(args[i])){
					this.DCSdir["setvar_"+args[i].substring(7)]=args[i+1];
				}
				else{this.DCSdir[args[i].substring(7)]=args[i+1];}
			}
		}
	}
}
WebTrends.prototype.dcsMSSetVarCap = function(e) {
    if (e.onclick)
        var gCap = e.onclick.toString();
    else if (e.onmousedown)
        var gCap = e.onmousedown.toString();
    var gStart = gCap.substring(gCap.indexOf("dcsSetVar(") + 10, gCap.length) || gCap.substring(gCap.indexOf("_tag.dcsMSSetVar(") + 16, gCap.length);
    var gEnd = gStart.substring(0, gStart.indexOf(");")).replace(/\s"/gi, "").replace(/"/gi, "").replace(/'/gi, "");
    var gSplit = gEnd.split(",");
    if (gSplit.length != -1) {
        for (var i = 0; i < gSplit.length; i += 2) {
            if (gSplit[i].indexOf('WT.') == 0) {
                if (this.dcsMSSetVarValidate(gSplit[i])) {
                    this.WT["setvar_" + gSplit[i].substring(3)] = gSplit[i + 1];
                }
                else { this.WT[gSplit[i].substring(3)] = gSplit[i + 1]; }
            }
            else if (gSplit[i].indexOf('DCS.') == 0) {
                if (this.dcsMSSetVarValidate(gSplit[i])) {
                    this.DCS["setvar_" + gSplit[i].substring(4)] = gSplit[i + 1];
                }
                else { this.DCS[gSplit[i].substring(4)] = gSplit[i + 1]; }

            }
            else if (gSplit[i].indexOf('DCSext.') == 0) {
                if (this.dcsMSSetVarValidate(gSplit[i])) {
                    this.DCSext["setvar_" + gSplit[i].substring(7)] = gSplit[i + 1];
                }
                else { this.DCSext[gSplit[i].substring(7)] = gSplit[i + 1]; }
            }
            else if (gSplit[i].indexOf('DCSdir.') == 0) {
                if (this.dcsMSSetVarValidate(gSplit[i])) {
                    this.DCSdir["setvar_" + gSplit[i].substring(7)] = gSplit[i + 1];
                }
                else { this.DCSdir[gSplit[i].substring(7)] = gSplit[i + 1]; }
            }
        }
    }
}
WebTrends.prototype.dcsMSSetVarValidate=function(validate){
	var wtParamList = "DCS.dcssip,DCS.dcsuri,DCS.dcsqry,WT.ti,WT.mc_id,WT.dl";
	wtParamList = wtParamList.split(",");
	for(var i=0;i<wtParamList.length;i++){
		if(wtParamList[i]==validate){
			return 1;
		}
	}
	return 0;
}
function dcsSetVar(){
	if (typeof(_tag)!="undefined"){
		return(_tag.dcsMSSetVar());
	}
}
WebTrends.prototype.dcsMSNvr=function(){
	if (document.cookie.indexOf("WTLOPTOUT=")!=-1){
		return;
	}
	var cur=new Date();
	var exp=new Date(cur.getTime()+315360000000);
	var cfields={name:"WT_NVR",value:"",expiry:"; expires="+exp.toGMTString(),path:"; path=/",domain:"; domain="+window.location.hostname};
	var cdata=this.dcsMSNvrRead(cfields.name);
	var isnew=this.dcsMSNvrProcess(cdata);
	if (isnew||this.dcsMSNvrFind(cfields.name,",")){
		var c=this.dcsMSNvrCompose(cdata,cfields);
		for (var i=0;i<c.length;i++){
			document.cookie=c[i];
		}
	}
	if (isnew){
		var val=(document.cookie.indexOf(cfields.name+"=")!=-1)?"1":"2";
		for (var i=0;i<cdata.length;i++){
			if (cdata[i][1]){
				this.WT["vt_nvr"+(i+1)]=val;
			}
		}
	}
}
WebTrends.prototype.dcsMSNvrRead=function(name){
	var data=new Array();
	var num=0;
	var re=/,/g;
	var value=this.dcsMSNvrGetCookie(name);
	while(value){
		var levels=value.split(":");
		for (var i=0;i<levels.length;i++){
			var crumbs=levels[i].split("=");
			var level=parseInt(crumbs[0]);
			var paths=crumbs[1].replace(re,"|").split("|");
			if (typeof(data[level])!="object"){
				data[level]=new Array(paths,false);
			}
			else{
				data[level][0]=data[level][0].concat(paths);
			}
		}
		num++;
		value=this.dcsMSNvrGetCookie(name+num);
	}
	return data;
}
WebTrends.prototype.dcsMSNvrFind=function(name,tofind){
	var found=false;
	var num=0;
	var value=this.dcsMSNvrGetCookie(name);
	while(value){
		if (value.indexOf(tofind)!=-1){
			found=true;
			break;
		}
		num++;
		value=this.dcsMSNvrGetCookie(name+num);
	}
	return found;
}
WebTrends.prototype.dcsMSNvrGetCookie=function(name){
	var pos=document.cookie.indexOf(name+"=");
	if (pos!=-1){
		var start=pos+name.length+1;
		var end=document.cookie.indexOf(";",start);
		if (end==-1){
			end=document.cookie.length;
		}
		return document.cookie.substring(start,end);
	}
	return null;
}
WebTrends.prototype.dcsMSNvrProcess=function(data){
	var newv=false;
	var maxlevel=3;
	var curlevel=0;
	var splitp=new Array();
	var p=window.location.pathname;
	var curpath=p.substring(p.indexOf("/")+1,p.lastIndexOf("/")).toLowerCase();
	var badchars={"%09":/\t/g,"%20":/ /g,"%2C":/,/g,"%3B":/;/g};
	var tmp=new String(curpath);
	for (var ch in badchars){
		tmp=tmp.replace(badchars[ch],ch);
	}
	curpath=tmp;

	if (curpath.length>1){
		splitp=curpath.split("/",maxlevel);
		curlevel=splitp.length;
		curpath=splitp.join("/");
	}
	if (data.length>0){
		var found=false;
		if (data.length>curlevel){
			for (var i=0;i<data[curlevel][0].length;i++){
				if (data[curlevel][0][i]==curpath){
					found=true;
					break;
				}
			}
			if (!found){
				data[curlevel][0][data[curlevel][0].length]=curpath;
				data[curlevel][1]=true;
				newv=true;
			}
		}
		else{
			for (var i=0;i<(curlevel+1);i++){
				if (typeof(data[i])!="object"){
					data[i]=new Array([(i==0)?"/":splitp.slice(0,i).join("/")],true);
				}
			}
			newv=true;
		}
	}
	else{
		for (var i=0;i<(curlevel+1);i++){
			data[i]=new Array([(i==0)?"/":splitp.slice(0,i).join("/")],true);
		}
		newv=true;
	}
	return newv;
}
WebTrends.prototype.dcsMSNvrCompose=function(data,fields){
	var cookies=new Array();
	var paths=new Array();
	var num=0;
	var maxnum=10;
	var maxval=4000;
	var maxed=false;
	for (var i=0;(i<data.length)&&!maxed;i++){
		paths=data[i][0];
		var newpath=i+"=";
		for (var j=0;(j<paths.length)&&!maxed;j++){
			newpath+=((j==0)?"":"|")+paths[j];
			if (((fields.name+num).length+fields.value.length+newpath.length+1)<=maxval){
				if ((fields.value.length>0)&&(j==0)){
					fields.value+=":";
				}
				fields.value+=newpath;
			}
			else if (fields.value.length>0){
				cookies[num]=fields.name+(num||"")+"="+fields.value+fields.expiry+fields.path+fields.domain;
				fields.value=i+"="+paths[j];
				if ((++num)>(maxnum-1)){
					maxed=true;
				}
			}
			newpath="";
		}
	}
	if (!maxed){
		cookies[num]=fields.name+(num||"")+"="+fields.value+fields.expiry+fields.path+fields.domain;
	}
	return cookies;
}
WebTrends.prototype.dcsMSmsresearchRead=function(getme){
	var c_val,c_object,userid,surveys;
    c_val=this.dcsGetCookie("msresearch");
    if (c_val){
        // convert JSON string to a JS object
        c_object=eval('('+c_val+')');
        // grab the user id string
        return c_object[getme];
    }
}
WebTrends.prototype.dcsMSGetCrumb=function(cname,crumb,sep){
    var cval=this.dcsGetCookie(cname);
    if (cval!=null) {
        var start = cval.indexOf(crumb+"=");
        if (start!=-1) {
            var end = cval.indexOf(sep,start);
            if (end!=-1) {
                return cval.substring((start+crumb.length+sep.length),end);
            }
            else {
                return cval.substring((start+crumb.length+sep.length),cval.length);
            }
        }
    }
return null;
}
WebTrends.prototype.dcsTrim=function(sString) {
    while (sString.substring(0,1) == ' ') {
        sString = sString.substring(1, sString.length);
    }
    while (sString.substring(sString.length-1, sString.length) == ' ') {
        sString = sString.substring(0,sString.length-1);
    }
return sString;
}
if (typeof(gFpc)!="undefined") {
	var _tag=new WebTrends();
	_tag.dcsGetId();
	_tag.dcsCollect();
	var WT = _tag.WT;
	var DCS = _tag.DCS;
	var DCSext = _tag.DCSext;
}

//End