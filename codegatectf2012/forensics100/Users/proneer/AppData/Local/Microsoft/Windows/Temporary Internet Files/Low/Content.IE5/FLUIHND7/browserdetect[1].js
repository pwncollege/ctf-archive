if(typeof(AC)==="undefined"){AC={}}AC.Detector={getAgent:function(){return navigator.userAgent.toLowerCase()
},isMac:function(c){var d=c||this.getAgent();return !!d.match(/mac/i)},isSnowLeopard:function(c){if(typeof console!="undefined"){console.warn('Instead of AC.Detector.isSnowLeopard, please use AC.Detector.macOSAtLeastVersion("10.6").')
}var d=c||this.getAgent();return !!d.match(/mac os x 10_6/i)},macOSVersion:function(g){var h=g||this.getAgent();
if(!this.isMac(h)){return null}var f=h.match(/(mac os x )([\d\._]*)/i);if(f==null){return f
}if(!!f[2].match(/\./)){f=f[2].split(".")}else{f=f[2].split("_")}for(var e=0;e<f.length;
e++){f[e]=parseInt(f[e])}return f},macOSAtLeastVersion:function(h,i){if(typeof h=="undefined"){return false
}var g=this.macOSVersion(i);if(g==null){return false}if(typeof h=="string"){h=h.replace(".","_").split("_")
}for(var j=0;j<h.length;j++){var f=parseInt(g[j]);if(isNaN(f)){f=0}if(parseInt(h[j])>f){return false
}}return true},isWin:function(c){var d=c||this.getAgent();return !!d.match(/win/i)
},winVersion:function(f){var d=f||this.getAgent();if(this.isWin(d)){var e=d.match(/nt\s*([\d\.]*)/);
if(e&&e[1]){return parseFloat(e[1])}return true}return false},winAtLeastVersion:function(f,d){if(typeof f=="undefined"){return false
}f=parseFloat(f);if(f===NaN){return false}var e=this.winVersion(d);if(e===null||e===false||e===true){return false
}return(f<=e)},isWin2k:function(c){var d=c||this.getAgent();return this.isWin(d)&&(d.match(/nt\s*5/i))
},isWinVista:function(c){var d=c||this.getAgent();return this.isWin(d)&&(d.match(/nt\s*6\.0([0-9]{0,2})?/i))
},isWebKit:function(c){if(this._isWebKit===undefined){var d=c||this.getAgent();
this._isWebKit=!!d.match(/AppleWebKit/i);this.isWebKit=function(){return this._isWebKit
}}return this._isWebKit},isSafari2:function(f){if(typeof console!="undefined"){console.warn("Instead of AC.Detector.isSafari2(), please use AC.Detector.isWebKit().")
}var d=f||this.getAgent();if(this._isSafari2===undefined){if(!this.isWebKit(d)){this._isSafari2=false
}else{var e=parseInt(parseFloat(d.substring(d.lastIndexOf("safari/")+7)),10);this._isSafari2=(e>=419)
}this.isSafari2=function(){return this._isSafari2}}return this._isSafari2},isChrome:function(c){if(this._isChrome===undefined){var d=c||this.getAgent();
this._isChrome=!!d.match(/Chrome/i);this.isChrome=function(){return this._isChrome
}}return this._isChrome},isiPhone:function(c){if(typeof console!="undefined"){console.warn("Instead of AC.Detector.isiPhone(), please use AC.Detector.isMobile().")
}var d=c||this.getAgent();return this.isMobile(d)},iPhoneOSVersion:function(k){if(typeof console!="undefined"){console.warn("Instead of AC.Detector.iPhoneOSVersion(), please use AC.Detector.iOSVersion().")
}var l=k||this.getAgent(),h=this.isMobile(l),j,i,g;if(h){var j=l.match(/.*CPU ([\w|\s]+) like/i);
if(j&&j[1]){i=j[1].split(" ");g=i[2].split("_");return g}else{return[1]}}return null
},isiPad:function(c){var d=c||this.getAgent();return !!(this.isWebKit(d)&&d.match(/ipad/i))
},isMobile:function(c){var d=c||this.getAgent();return this.isWebKit(d)&&(d.match(/Mobile/i)&&!this.isiPad(d))
},_iOSVersion:null,iOSVersion:function(){if(this._iOSVersion===null){this._iOSVersion=(AC.Detector.isMobile()||AC.Detector.isiPad())?parseFloat(navigator.userAgent.match(/os ([\d_]*)/i)[1].replace("_",".")):false
}return this._iOSVersion},isOpera:function(c){var d=c||this.getAgent();return !!d.match(/opera/i)
},isIE:function(c){var d=c||this.getAgent();return !!d.match(/msie/i)},isIEStrict:function(c){var d=c||this.getAgent();
return d.match(/msie/i)&&!this.isOpera(d)},isIE8:function(f){var d=f||this.getAgent();
var e=d.match(/msie\D*([\.\d]*)/i);if(e&&e[1]){version=e[1]}return(+version>=8)
},isFirefox:function(c){var d=c||this.getAgent();return !!d.match(/firefox/i)},isiTunesOK:function(c){var d=c||this.getAgent();
if(this.isMac(d)){return true}if(this.winAtLeastVersion(5.1,d)){return true}return false
},_isQTInstalled:undefined,isQTInstalled:function(){if(this._isQTInstalled===undefined){var e=false;
if(navigator.plugins&&navigator.plugins.length){for(var d=0;d<navigator.plugins.length;
d++){var f=navigator.plugins[d];if(f.name.indexOf("QuickTime")>-1){e=true}}}else{if(typeof(execScript)!="undefined"){qtObj=false;
execScript('on error resume next: qtObj = IsObject(CreateObject("QuickTimeCheckObject.QuickTimeCheck.1"))',"VBScript");
e=qtObj}}this._isQTInstalled=e}return this._isQTInstalled},getQTVersion:function(){var f="0";
if(navigator.plugins&&navigator.plugins.length){for(var h=0;h<navigator.plugins.length;
h++){var g=navigator.plugins[h];var e=g.name.match(/quicktime\D*([\.\d]*)/i);if(e&&e[1]){f=e[1]
}}}else{if(typeof(execScript)!="undefined"){ieQTVersion=null;execScript('on error resume next: ieQTVersion = CreateObject("QuickTimeCheckObject.QuickTimeCheck.1").QuickTimeVersion',"VBScript");
if(ieQTVersion){f=ieQTVersion.toString(16);f=[f.charAt(0),f.charAt(1),f.charAt(2)].join(".")
}}}return f},isQTCompatible:function(j,h){function f(c,a){var d=parseInt(c[0],10);
if(isNaN(d)){d=0}var b=parseInt(a[0],10);if(isNaN(b)){b=0}if(d===b){if(c.length>1){return f(c.slice(1),a.slice(1))
}else{return true}}else{if(d<b){return true}else{return false}}}var i=j.split(/\./);
var g=h?h.split(/\./):this.getQTVersion().split(/\./);return f(i,g)},isValidQTAvailable:function(b){return this.isQTInstalled()&&this.isQTCompatible(b)
},isSBVDPAvailable:function(b){return false},_svgAsBackground:null,svgAsBackground:function(f){if(this._svgAsBackground===null){var d=function(){AC.Detector._svgAsBackground=true;
if(typeof(f)=="function"){f()}};var e=document.createElement("img");e.setAttribute("src","data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNzUiIGhlaWdodD0iMjc1Ij48L3N2Zz4%3D");
if(e.complete){e.style.visibility="hidden";e.style.position="absolute";document.body.appendChild(e);
window.setTimeout(function(){AC.Detector._svgAsBackground=false;if(e.width>=100){document.body.removeChild(e);
d()}else{document.body.removeChild(e)}},1)}else{this._svgAsBackground=false;e.onload=d
}}else{if(this._svgAsBackground&&typeof(f)=="function"){f()}}return this._svgAsBackground
},_style:null,_prefixes:null,_preFixes:null,_css:null,isCSSAvailable:function(i){if(!this._style){this._style=document.createElement("browserdetect").style
}if(!this._prefixes){this._prefixes="-webkit- -moz- -o- -ms- -khtml- ".split(" ")
}if(!this._preFixes){this._preFixes="Webkit Moz O ms Khtml ".split(" ")}if(!this._css){this._css={}
}i=i.replace(/([A-Z]+)([A-Z][a-z])/g,"$1-$2").replace(/([a-z\d])([A-Z])/g,"$1-$2").replace(/^(\-*webkit|\-*moz|\-*o|\-*ms|\-*khtml)\-/,"").toLowerCase();
switch(i){case"gradient":if(this._css.gradient!==undefined){return this._css.gradient
}var i="background-image:",l="gradient(linear,left top,right bottom,from(#9f9),to(white));",m="linear-gradient(left top,#9f9, white);";
this._style.cssText=(i+this._prefixes.join(l+i)+this._prefixes.join(m+i)).slice(0,-i.length);
this._css.gradient=(this._style.backgroundImage.indexOf("gradient")!==-1);return this._css.gradient;
case"inset-box-shadow":if(this._css["inset-box-shadow"]!==undefined){return this._css["inset-box-shadow"]
}var i="box-shadow:",j="#fff 0 1px 1px inset;";this._style.cssText=this._prefixes.join(i+j);
this._css["inset-box-shadow"]=(this._style.cssText.indexOf("inset")!==-1);return this._css["inset-box-shadow"];
default:var n=i.split("-"),r=n.length,o,p,q;if(n.length>0){i=n[0];for(p=1;p<r;p++){i+=n[p].substr(0,1).toUpperCase()+n[p].substr(1)
}}o=i.substr(0,1).toUpperCase()+i.substr(1);if(this._css[i]!==undefined){return this._css[i]
}for(q=this._preFixes.length-1;q>=0;q--){if(this._style[this._preFixes[q]+i]!==undefined||this._style[this._preFixes[q]+o]!==undefined){this._css[i]=true;
return true}}return false}return false},_supportsThreeD:false,supportsThreeD:function(){try{this._supportsThreeD=false;
if("styleMedia" in window){this._supportsThreeD=window.styleMedia.matchMedium("(-webkit-transform-3d)")
}else{if("media" in window){this._supportsThreeD=window.media.matchMedium("(-webkit-transform-3d)")
}}if(!this._supportsThreeD){if(!document.getElementById("supportsThreeDStyle")){var d=document.createElement("style");
d.id="supportsThreeDStyle";d.textContent="@media (transform-3d),(-o-transform-3d),(-moz-transform-3d),(-ms-transform-3d),(-webkit-transform-3d) { #supportsThreeD { height:3px } }";
document.querySelector("head").appendChild(d)}if(!(div=document.querySelector("#supportsThreeD"))){div=document.createElement("div");
div.id="supportsThreeD";document.body.appendChild(div)}this._supportsThreeD=(div.offsetHeight===3)
}return this._supportsThreeD}catch(c){return false}},_hasGyro:null,_testingForGyro:false,hasGyro:function(){if(this._hasGyro!==null){return this._hasGyro
}if("DeviceOrientationEvent" in window&&window.DeviceOrientationEvent!==null){if(this._testingForGyro===false){this._testingForGyro=true;
var b=this;this.boundTestingForGyro=function(a){b.testingForGyro(a)};window.addEventListener("deviceorientation",this.boundTestingForGyro,true);
this._testGyroTimeout=window.setTimeout(function(){this._hasGyro=false}.bind(this),250)
}return this._hasGyro}else{return this._hasGyro=false}},testingForGyro:function(b){if(this._hasGyro===false){return this._hasGyro
}else{if(typeof b.gamma!=="undefined"&&typeof b.beta!=="undefined"){this._hasGyro=true
}else{this._hasGyro=false}window.clearTimeout(this._testGyroTimeout);window.removeEventListener("deviceorientation",this.boundTestingForGyro,true);
delete this.boundTestingForGyro}},_isiPadWithGyro:null,isiPadWithGyro:function(){if(this._isiPadWithGyro===false||!this.isiPad()){return false
}else{return this._isiPadWithGyro=this.hasGyro()}},_hasLocalStorage:null,hasLocalStorage:function(){if(this._hasLocalStorage!==null){return this._hasLocalStorage
}try{if(typeof localStorage!=="undefined"&&"setItem" in localStorage){localStorage.setItem("ac_browser_detect","test");
this._hasLocalStorage=true;localStorage.removeItem("ac_browser_detect","test")}else{this._hasLocalStorage=false
}}catch(b){this._hasLocalStorage=false}return this._hasLocalStorage},_hasSessionStorage:null,hasSessionStorage:function(){if(this._hasSessionStorage!==null){return this._hasSessionStorage
}try{if(typeof sessionStorage!=="undefined"&&"setItem" in sessionStorage){sessionStorage.setItem("ac_browser_detect","test");
this._hasSessionStorage=true;sessionStorage.removeItem("ac_browser_detect","test")
}else{this._hasSessionStorage=false}}catch(b){this._hasSessionStorage=false}return this._hasSessionStorage
},_hasCookies:null,hasCookies:function(){if(this._hasCookies!==null){return this._hasCookies
}this._hasCookies=("cookie" in document&&!!navigator.cookieEnabled)?true:false;
return this._hasCookies}};