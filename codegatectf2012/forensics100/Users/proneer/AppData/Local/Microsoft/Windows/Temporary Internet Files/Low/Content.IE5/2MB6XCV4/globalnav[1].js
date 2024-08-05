if(typeof(AC)=="undefined"){AC={}}document.createElement("nav");AC.addEvent=function(b,a,c){if(b.addEventListener){return b.addEventListener(a,c,false)
}else{return b.attachEvent("on"+a,c)}};AC.removeEvent=function(b,a,c){if(b.removeEventListener){return b.removeEventListener(a,c,false)
}else{return b.detachEvent("on"+a,c)}};AC.removeClassName=function(a,b){b=new RegExp(b,"g");
a.className=a.className.replace(b,"").replace(/ +/g," ").replace(/ +$/gm,"").replace(/^ +/gm,"")
};AC.getPreviousSibling=function(a){while(a=a.previousSibling){if(a.nodeType==1){return a
}}};if(typeof(AC.Detector)=="undefined"){AC.Detector={_iOSVersion:null,iOSVersion:function(){if(this._iOSVersion===null){this._iOSVersion=(navigator.userAgent.match(/applewebkit/i)&&(navigator.platform.match(/iphone/i)||navigator.platform.match(/ipod/i)||navigator.platform.match(/ipad/i)))?parseFloat(navigator.userAgent.match(/os ([\d_]*)/i)[1].replace("_",".")):false
}return this._iOSVersion},_svgAsBackground:null,svgAsBackground:function(c){if(this._svgAsBackground===null){var b=function(){AC.Detector._svgAsBackground=true;
if(typeof(c)=="function"){c()}};var a=document.createElement("img");a.setAttribute("src","data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNzUiIGhlaWdodD0iMjc1Ij48L3N2Zz4%3D");
if(a.complete){a.style.visibility="hidden";a.style.position="absolute";document.body.appendChild(a);
window.setTimeout(function(){AC.Detector._svgAsBackground=false;if(a.width>=100){document.body.removeChild(a);
b()}else{document.body.removeChild(a)}},1)}else{this._svgAsBackground=false;a.onload=b
}}else{if(this._svgAsBackground&&typeof(c)=="function"){c()}}return this._svgAsBackground
},_style:null,_prefixes:null,_preFixes:null,_css:null,isCSSAvailable:function(k){if(!this._style){this._style=document.createElement("browserdetect").style
}if(!this._prefixes){this._prefixes="-webkit- -moz- -o- -ms- -khtml- ".split(" ")
}if(!this._preFixes){this._preFixes="Webkit Moz O ms Khtml ".split(" ")}if(!this._css){this._css={}
}k=k.replace(/([A-Z]+)([A-Z][a-z])/g,"$1-$2").replace(/([a-z\d])([A-Z])/g,"$1-$2").replace(/^(\-*webkit|\-*moz|\-*o|\-*ms|\-*khtml)\-/,"").toLowerCase();
switch(k){case"gradient":if(this._css.gradient!==undefined){return this._css.gradient
}var k="background-image:",g="gradient(linear,left top,right bottom,from(#9f9),to(white));",f="linear-gradient(left top,#9f9, white);";
this._style.cssText=(k+this._prefixes.join(g+k)+this._prefixes.join(f+k)).slice(0,-k.length);
this._css.gradient=(this._style.backgroundImage.indexOf("gradient")!==-1);return this._css.gradient;
case"inset-box-shadow":if(this._css["inset-box-shadow"]!==undefined){return this._css["inset-box-shadow"]
}var k="box-shadow:",h="#fff 0 1px 1px inset;";this._style.cssText=this._prefixes.join(k+h);
this._css["inset-box-shadow"]=(this._style.cssText.indexOf("inset")!==-1);return this._css["inset-box-shadow"];
default:var e=k.split("-"),a=e.length,d,c,b;if(e.length>0){k=e[0];for(c=1;c<a;c++){k+=e[c].substr(0,1).toUpperCase()+e[c].substr(1)
}}d=k.substr(0,1).toUpperCase()+k.substr(1);if(this._css[k]!==undefined){return this._css[k]
}for(b=this._preFixes.length-1;b>=0;b--){if(this._style[this._preFixes[b]+k]!==undefined||this._style[this._preFixes[b]+d]!==undefined){this._css[k]=true;
return true}}return false}return false}}}AC.GlobalNav=function(){var a=this,b;this.globalHeader=document.getElementById("globalheader");
this.globalSearch=document.getElementById("sp-searchtext");this.globalStylesheet=document.getElementById("globalheader-stylesheet");
if(this.globalHeader){this.globalHeader.className+=" globalheader-js";AC.Detector.svgAsBackground(function(){a.globalHeader.className+=" svg"
});if(navigator.userAgent.match(/applewebkit/i)){if(!navigator.geolocation){this.globalHeader.className+=" decelerate"
}else{if(navigator.platform.match(/ipad/i)||navigator.platform.match(/iphone/i)||navigator.platform.match(/ipod/i)){this.globalHeader.className+=" ios"
}}if(AC.Detector.iOSVersion()&&AC.Detector.iOSVersion()<=3.2){this.globalHeader.className+=" ios3"
}if(!AC.Detector.isCSSAvailable("inset-box-shadow")||navigator.userAgent.match(/chrome/i)&&navigator.userAgent.match(/windows/i)){this.globalHeader.className+=" noinset"
}}this.enhanceSearch();this.decorateSearchInput();this.vml();this.decorateTabStates();
if(AC.GlobalNav.canEnhance()&&this.globalStylesheet){this.enhancedGlobalStylesheet=this.globalStylesheet.cloneNode(true);
this.enhancedGlobalStylesheet.id="globalheader-enhanced-stylesheet";this.enhancedGlobalStylesheet.href=this.globalStylesheet.href.replace("/navigation.css","/enhanced.css");
this.globalStylesheet.parentNode.appendChild(this.enhancedGlobalStylesheet)}this.loaded()
}};AC.GlobalNav._canEnhance=null;AC.GlobalNav.canEnhance=function(){if(AC.GlobalNav.canEnhance._canEnhance==null){var a=navigator.userAgent.replace(/^.*version\/([\d\.]*) .*$/i,"$1").split(".");
AC.GlobalNav.canEnhance._canEnhance=(AC.Detector.isCSSAvailable("transition-property")&&AC.Detector.isCSSAvailable("gradient")&&(AC.Detector.iOSVersion()===false||AC.Detector.iOSVersion()>=3.2)&&!(navigator.userAgent.match(/applewebkit/i)&&a.length==3&&(a[0]<=4&&a[1]<=0&&a[2]<=2)))
}return AC.GlobalNav.canEnhance._canEnhance};AC.GlobalNav.prototype.enhanceSearch=function(){this.globalSearchForm=document.getElementById("g-search");
if(this.globalSearchForm&&this.globalSearch){if(typeof(searchCountry)=="undefined"){searchCountry="us"
}if(SearchShortcut.geoMap[searchCountry.toUpperCase()].directory){var c=SearchShortcut.geoMap[searchCountry.toUpperCase()].directory
}else{if(searchCountry!="us"){var c="/"+searchCountry.replace(/_/,"")}else{c=""
}}var b={global:"http://www.apple.com"+c+"/search/",ipad:"http://www.apple.com"+c+"/search/",iphone:"http://www.apple.com"+c+"/search/",ipoditunes:"http://www.apple.com"+c+"/search/",mac:"http://www.apple.com"+c+"/search/",store:"http://www.apple.com"+c+"/search/",support:"http://www.info.apple.com/searchredir.html"};
var a=b[searchSection]||"http://www.apple.com/search/";this.globalSearchForm.setAttribute("action",a);
this.globalSearchForm.setAttribute("method","get");this.searchShortcut=searchShortcut=new SearchShortcut(this.globalSearchForm,this.globalSearch);
SearchShortcut.loadXmlToDoc=function(d){searchShortcut.loadXmlToDoc(d)}}};AC.GlobalNav.prototype.decorateSearchInput=function(){if(this.globalSearch){var f,g,b,e,d,c=this,a=true,f=document.getElementById("g-search");
g=document.createDocumentFragment();this.globalSearch.setAttribute("autocomplete","off");
b=document.createElement("input");this.globalSearch.parentNode.replaceChild(b,this.globalSearch);
e=document.createElement("div");e.className="reset";resetEnd=function(h){if(h.target==e&&window.getComputedStyle(e,null)["opacity"]=="0"){e.style.display="none"
}};if(window.addEventListener){e.addEventListener("transitionend",resetEnd,true);
e.addEventListener("transitionEnd",resetEnd,true);e.addEventListener("oTransitionEnd",resetEnd,false);
e.addEventListener("mozTransitionEnd",resetEnd,false);e.addEventListener("webkitTransitionEnd",resetEnd,false)
}if(this.globalSearch.value.length==0){f.className+=" empty"}g.appendChild(this.globalSearch);
g.appendChild(e);d=function(h){a=false;c.globalSearch.value="";if(c.searchShortcut){c.searchShortcut.hideResults()
}window.setTimeout(function(){f.className+=" empty";a=true},10)};AC.addEvent(e,"mousedown",d);
AC.addEvent(this.globalSearch,"focus",function(h){if(a){e.style.display="";window.setTimeout(function(){c.globalHeader.className+=" searchmode"
},10)}});AC.addEvent(this.globalSearch,"blur",function(h){if(a){e.style.display="";
window.setTimeout(function(){AC.removeClassName(c.globalHeader,"searchmode")},10)
}else{a=true}});AC.addEvent(this.globalSearch,"keydown",function(h){var i=h.keyCode;
a=true;if(c.globalSearch.value.length>=0){e.style.display="";window.setTimeout(function(){AC.removeClassName(f,"empty")
},10)}else{if(!f.className.match("empty")){f.className+=" empty"}}if(h.keyCode===27){d(h)
}});if(b){b.parentNode.replaceChild(g,b)}}};AC.GlobalNav.prototype.vml=function(){var e,a,c,b,d;
if(!AC.Detector.isCSSAvailable("border-radius")&&document.namespaces&&this.globalHeader){document.namespaces.add("v","urn:schemas-microsoft-com:vml");
e=document.createDocumentFragment();a=document.createElement("v:roundrect");a.setAttribute("id","globalheader-roundrect");
a.setAttribute("stroked",true);a.setAttribute("strokeColor","#737373");a.setAttribute("arcSize",".1");
e.appendChild(a);c=this.globalHeader.currentStyle.backgroundImage;this.globalHeader.style.backgroundImage="none";
c=c.replace(/url\(["']*([^"']*)["']*\)/,"$1");b=document.createElement("v:fill");
b.setAttribute("id","globalheader-fill");b.setAttribute("type","tile");b.setAttribute("src",c);
a.appendChild(b);d=document.createElement("v:roundrect");d.setAttribute("id","globalheader-shadow");
d.setAttribute("stroked",false);d.setAttribute("fillColor","#999");d.setAttribute("arcSize",".1");
e.appendChild(d);this.globalHeader.appendChild(e)}};AC.GlobalNav.prototype.getPreviousNavItem=function(a){while(a.tagName.toLowerCase()!=="li"){a=a.parentNode
}a=AC.getPreviousSibling(a);if(!a){return false}if(a.tagName.toLowerCase()!=="li"){return false
}a=a.getElementsByTagName("a");if(!a[0]){return false}return a[0]};AC.GlobalNav.prototype.decorateTabStates=function(){this.globalNavItems=this.globalHeader.getElementsByTagName("a");
var a=this,c=this.globalHeader.className.replace(/ .*/,""),b;for(b=this.globalNavItems.length-1;
b>=0;b--){if(this.globalNavItems[b].href.match(c)){this.currentTab=this.globalNavItems[b]
}AC.addEvent(this.globalNavItems[b],"mousedown",function(d){var e=(d.target)?d.target:d.srcElement;
e=a.getPreviousNavItem(e);if(e&&e!==a.currentTab){e.className+=" before"}});AC.addEvent(this.globalNavItems[b],"mouseout",function(d){var e=(d.target)?d.target:d.srcElement;
e=a.getPreviousNavItem(e);if(e&&e!==a.currentTab){AC.removeClassName(e,"before")
}})}if(this.currentTab){this.currentTab=this.getPreviousNavItem(this.currentTab);
this.currentTab.className+=" before"}};AC.GlobalNav.prototype.loaded=function(b){var a=this;
if(this.loadedTimeout){window.clearTimeout(this.loadedTimeout)}if(!this.cancelLoadedTimeout){this.cancelLoadedTimeout=window.setTimeout(function(){a.loaded(true)
},500)}if(!this.testEnhancedLoaded){this.testEnhancedLoaded=document.createElement("div");
this.testEnhancedLoaded.id="globalheader-loaded-test";document.body.appendChild(this.testEnhancedLoaded)
}if(b||this.testEnhancedLoaded.offsetWidth==0){this.globalHeader.className+=" globalheader-loaded"
}else{this.loadedTimeout=window.setTimeout(function(){a.loaded()},10)}};var SearchShortcut=function(d,b){this.searchWrapper=document.getElementById("globalsearch");
this.searchForm=d;this.resultsPanel=document.getElementById("sp-results");this.searchInput=b;
this.addSection();var c=(/applewebkit/i.test(navigator.userAgent)&&/mobile/i.test(navigator.userAgent))||/webos/i.test(navigator.userAgent)||/android/i.test(navigator.userAgent)||/blackberry/i.test(navigator.userAgent)||/windows ce/i.test(navigator.userAgent)||/opera mini/i.test(navigator.userAgent);
if(c||(typeof(deactivateSearchShortcuts)!=="undefined"&&deactivateSearchShortcuts)){return
}if(this.shouldVML()){this.resultsPanel.className+=" sp-results-vml"}this.addSpinner();
this.baseUrl="http://www.apple.com/global/nav/scripts/shortcuts.php";this.minimumCharactersForSearch=0;
this.entryDelay=150;this.currentRequest=false;this.quickLinks=SearchShortcut.geoMap.US.shortcuts;
this.noResults=SearchShortcut.geoMap.US.noResults;if(typeof(searchCountry)!="undefined"&&searchCountry){this.quickLinks=SearchShortcut.geoMap[searchCountry.toUpperCase()].shortcuts||this.quickLinks;
this.noResults=SearchShortcut.geoMap[searchCountry.toUpperCase()].noResults||this.noResults
}var a=this;AC.addEvent(this.searchForm,"submit",function(f){try{f.preventDefault();
f.stopPropagation()}catch(g){}return false});AC.addEvent(document,"mousemove",function(e){if(!a.resultsShowing){return
}a.onMouseMove(e)});AC.addEvent(this.searchInput,"keydown",function(e){a.onKeyDown(e)
});AC.addEvent(this.searchInput,"keyup",function(e){a.onKeyUp(e)});AC.addEvent(this.searchInput,"blur",function(e){a.onBlur(e)
})};SearchShortcut.prototype.shouldVML=function(){return(!AC.Detector.isCSSAvailable("border-radius")&&document.namespaces)
};SearchShortcut.prototype.addSpinner=function(){this.spinner=document.createElement("div");
this.spinner.className="spinner hide";this.searchInput.parentNode.appendChild(this.spinner)
};SearchShortcut.prototype.hideSpinner=function(){this.spinner.className+=" hide"
};SearchShortcut.prototype.showSpinner=function(){AC.removeClassName(this.spinner,"hide")
};SearchShortcut.prototype.addSection=function(){var a=document.getElementById("search-section");
if(!a){a=document.createElement("input");a.id="search-section";a.type="hidden";
a.name="sec";a.value=window.searchSection;this.searchForm.appendChild(a)}else{if(a){a.value=window.searchSection
}}};SearchShortcut.prototype.onKeyDown=function(a){var b=typeof(event)!="undefined"?event.keyCode:a.keyCode;
if(!a){a=event}if(b==13&&!a.altKey){if(this.selected){this.go(this.selected.data.url)
}else{this.hideResults();this.fullSearch()}}else{if(b==9){this.hideResults()}}};
SearchShortcut.prototype.onKeyUp=function(a){var d=typeof(event)!="undefined"?event.keyCode:a.keyCode;
if(!a){a=event}if(d==40&&this.results){try{a.preventDefault();a.stopPropagation()
}catch(c){}if(this.selected&&this.results[this.selected.index+1]){this.selected.deselect();
this.selected=this.results[this.selected.index+1].select()}else{if(!this.selected&&this.results[0]){this.selected=this.results[0].select()
}}}else{if(d==38&&this.results){try{a.preventDefault();a.stopPropagation()}catch(c){}if(this.selected&&this.selected.index>0){this.selected.deselect();
this.selected=this.results[this.selected.index-1].select()}}else{this.selected=false;
var b=this.searchInput.value;b=b.replace(/[%\^\?\!\*\/<>\$]/ig,"").replace(/^\s+/g,"").replace(/\s+$/g,"");
if(b.length>this.minimumCharactersForSearch){this.searchText=b;this.startKeystrokeTimer()
}else{this.hideSpinner();this.hideResults()}}}};SearchShortcut.prototype.onMouseMove=function(a){a=a||window.event;
this.mouseEventTarget=(a.target)?a.target:a.srcElement;if(this.shouldHideOnMouseOut){if(!this.isOverResults()){this.hideResults(a)
}}};SearchShortcut.prototype.isOverResults=function(a){if(!this.mouseEventTarget){return false
}while((this.mouseEventTarget.id!=="sp-results")&&this.mouseEventTarget.parentNode){this.mouseEventTarget=this.mouseEventTarget.parentNode
}return(this.mouseEventTarget.id==="sp-results")};SearchShortcut.prototype.onBlur=function(a){if(this.isOverResults()){this.shouldHideOnMouseOut=true
}if(!this.selected&&!this.isOverResults()){this.hideResults(a)}};SearchShortcut.prototype.startKeystrokeTimer=function(){if(this.timeoutId){window.clearTimeout(this.timeoutId)
}var a=this;this.timeoutId=window.setTimeout(function(){a.commitKeystroke()},this.entryDelay)
};SearchShortcut.prototype.commitKeystroke=function(){this.search(this.searchText)
};SearchShortcut.prototype.fullSearchUrl=function(){var a=this.searchForm.getAttribute("action");
return a};SearchShortcut.prototype.getQueryParameters=function(c){var b=this.searchForm.elements,a,d,e=this.searchForm.getAttribute("action");
this._formValues=[];for(a=b.length-1;a>=0;a--){var d=b[a];if(d.name!=="q"&&e.indexOf(d.name)===-1){this._formValues.push(d.name+"="+d.value);
this._formValues[d.name]=d.name}}var f="?q="+encodeURIComponent(c);if(typeof(searchSection)!="undefined"&&searchSection){f+="&section="+searchSection
}if(typeof(searchCountry)!="undefined"&&searchCountry){f+="&geo="+searchCountry.toLowerCase()
}return f};SearchShortcut.prototype.search=function(c){var a=this.baseUrl+this.getQueryParameters(c);
this.showSpinner();a+="&transport=js";var b=document.getElementsByTagName("head")[0];
script=document.createElement("script");script.id="xdShortcutContainer";script.type="text/javascript";
script.src=a;b.appendChild(script)};SearchShortcut.prototype.loadXmlToDoc=function(b){var a;
if(window.ActiveXObject){a=new ActiveXObject("Microsoft.XMLDOM");a.async="false";
a.loadXML(b)}else{var c=new DOMParser();a=c.parseFromString(b,"text/xml")}this.hideSpinner();
this.term=a.getElementsByTagName("term")[0].firstChild.nodeValue;this.xml=a.getElementsByTagName("search_results")[0];
this.parseResults(this.xml);if(this.results){this.renderResults()}};SearchShortcut.prototype.parseResults=function(d){var c=d.getElementsByTagName("error");
if(c.length>0){this.hideResults();return}else{var f=d.getElementsByTagName("match");
this.results=new Array();for(var e=0;e<f.length;e++){var a=f[e];var b={title:a.getAttribute("title"),url:a.getAttribute("url"),desc:a.getAttribute("copy"),category:a.getAttribute("category"),priority:a.getAttribute("priority"),image:a.getAttribute("image")};
b.url=decodeURIComponent(b.url);this.results.push(b)}}};SearchShortcut.prototype.renderResults=function(){this.resultsShowing=true;
this.resultsPanel.innerHTML="";var g=document.createDocumentFragment(),f=document.createElement("div"),e=document.createElement("div"),d=document.createElement("h3"),c=document.createElement("ul"),b=this.results.length;
f.className="sp-shadow";g.appendChild(f);if(this.shouldVML()){document.namespaces.add("v","urn:schemas-microsoft-com:vml");
roundrect=document.createElement("v:roundrect");roundrect.id="sp-roundrect";roundrect.strokeColor="#fff";
roundrect.fillColor="#fff";roundrect.arcSize=".01";roundrect.appendChild(e);g.appendChild(roundrect)
}else{g.appendChild(e)}if(b===0){this.results[0]={title:this.noResults,url:this.fullSearchUrl()+this.getQueryParameters(this.term)};
c.className="noresults"}else{d.innerHTML=this.quickLinks;e.appendChild(d)}var b=this.results.length;
for(var a=0;a<b;a++){if(a>5){delete this.results[a]}else{this.results[a]=new SearchShortcut.result(a,this.results[a]);
c.appendChild(this.results[a].element)}}e.appendChild(c);this.resultsPanel.appendChild(g);
if(this.shouldVML()){f.style.height=e.offsetHeight+"px";f.style.display="block"
}this.hideAllQuicktimeMovies()};SearchShortcut.prototype.hideResults=function(a,b){this.selected=false;
this.shouldHideOnMouseOut=false;this.resultsPanel.innerHTML="";this.showAllQuicktimeMovies();
this.resultsShowing=false};SearchShortcut.prototype.track=function(d,a){if(typeof(s_gi)=="undefined"||!s_gi){return
}var c="appleglobal";var e="appleussearch";var b=null;if(typeof(searchCountry)!="undefined"&&searchCountry&&searchCountry!="US"){b=SearchShortcut.geoMap[searchCountry.toUpperCase()].code
}if(b){c="apple"+b+"global";e="apple"+b+"search"}s=s_gi(c+","+e);s.linkTrackVars="eVar2,eVar4,prop7,prop10";
s.eVar2="WWW-sc: "+d.toLowerCase();s.prop7="WWW-sc: "+d.toLowerCase();s.eVar4=a;
s.prop10=a;s.tl(this,"o","Shortcut Search")};SearchShortcut.prototype.fullSearch=function(a){var b=this.fullSearchUrl()+this.getQueryParameters(this.searchText);
document.location=b.replace("q=undefined&","")};SearchShortcut.prototype.go=function(a){this.track(this.searchText,a);
document.location=a};SearchShortcut.prototype.shouldHideQuicktimeMovies=function(){var c=navigator.userAgent,b=/opera/i.test(c),f=(/msie/i.test(c)&&!b),d=/firefox/i.test(c),a=/chrome/i.test(c),e=(/applewebkit/i.test(c)&&!a);
win=/windows/i.test(c),mac=/mac/i.test(c);if(mac&&(e||a)){return false}if(win&&(f||e||a)){return false
}return true};SearchShortcut.prototype.hideAllQuicktimeMovies=function(){if(this.shouldHideQuicktimeMovies()){if(typeof(AC)!="undefined"&&typeof(AC.Quicktime)!="undefined"&&typeof(AC.Quicktime.controllers)!="undefined"){function k(e){var i=curtop=0;
if(e.offsetParent){i=e.offsetLeft;curtop=e.offsetTop;while(e=e.offsetParent){i+=e.offsetLeft;
curtop+=e.offsetTop}}return[i,curtop]}function c(t,y,C,G,i,x,A,F){var w=t+C;var D=y+G;
var v=i+A;var B=x+F;var u=Math.max(t,i);var z=Math.max(y,x);var E=Math.min(w,v);
var e=Math.min(D,B);return E>u&&e>z}var a=AC.Quicktime.controllers;var b={width:328,height:448};
var r=k(this.resultsPanel);var q=r[0]-328;var p=r[1];var o=m+b.width;var n=l+b.height;
for(var f=a.length-1;f>=0;f--){var d=a[f].movie;var j=Element.getDimensions(d);
var g=k(d);var m=g[0];var l=g[1];if(c(m,l,j.width,j.height,q,p,b.width,b.height)){this.pausedControllers.push(a[f]);
a[f].Stop();a[f].movie.style.visibility="hidden"}}}else{this.qtm=document.getElementsByTagName("object");
for(var f=0;f<this.qtm.length;f++){if(typeof(this.qtm[f].Stop)!="undefined"){this.qtm[f].Stop()
}try{if(typeof(this.qtm[f].getElementsByTagName("embed")[0].Stop)!="undefined"){this.qtm[f].getElementsByTagName("embed")[0].Stop()
}}catch(h){}this.qtm[f].style.visibility="hidden"}}}};SearchShortcut.prototype.showAllQuicktimeMovies=function(){if(typeof(AC)!="undefined"&&typeof(AC.Quicktime)!="undefined"&&typeof(AC.Quicktime.controllers)!="undefined"){for(var a=this.pausedControllers.length-1;
a>=0;a--){this.pausedControllers[a].movie.style.visibility="visible";if(navigator.userAgent.match(/Firefox/i)){this.pausedControllers[a].movie.style.zIndex="100";
setTimeout(this.pausedControllers[a].Play.bind(this.pausedControllers[a]),100)}else{this.pausedControllers[a].Play()
}}this.pausedControllers=[]}else{if(this.qtm){for(var a=0;a<this.qtm.length;a++){this.qtm[a].style.visibility="visible";
if(typeof(this.qtm[a].Play)!="undefined"){this.qtm[a].Play()}try{if(typeof(this.qtm[a].getElementsByTagName("embed")[0].Play)!="undefined"){this.qtm[a].getElementsByTagName("embed")[0].Play()
}}catch(b){}}}}};SearchShortcut.descriptionCharacters=105;SearchShortcut.titleCharacters=39;
SearchShortcut.result=function(a,b){this.index=a;this.data=b;this.data.truncated={};
if(this.data.desc){this.data.truncated.desc=unescape(this.data.desc);if(this.data.truncated.desc.length>SearchShortcut.descriptionCharacters){this.data.truncated.desc=this.data.truncated.desc.substring(0,this.data.truncated.desc.lastIndexOf(" ",this.descriptionCharacters-11))+"&hellip;"
}}if(this.data.title){this.data.truncated.title=unescape(this.data.title);if(this.data.truncated.title.length>SearchShortcut.titleCharacters){this.data.truncated.title=this.data.truncated.title.substring(0,this.data.truncated.title.lastIndexOf(" ",30))+"&hellip;"
}}this.render()};SearchShortcut.result.prototype.render=function(){var g,e,d,b,f,c,a=this;
g=document.createDocumentFragment();e=document.createElement("li");g.appendChild(e);
if(this.data.url){d=document.createElement("a");d.href=decodeURIComponent(this.data.url);
AC.addEvent(d,"mousedown",function(h){if(searchShortcut){searchShortcut.go(a.data.url)
}});e.appendChild(d)}if(this.data.image){b=new Image();b.src=this.data.image;b.alt=this.data.title;
d.appendChild(b);if(/MSIE (5\.5|6\.)/.test(navigator.userAgent)){b.src="/global/elements/blank.gif";
b.style.filter='progid:DXImageTransform.Microsoft.AlphaImageLoader(src="'+this.data.image+'",sizingMethod="scale")'
}}if(this.data.truncated.title){f=document.createElement("h4");f.innerHTML=this.data.truncated.title;
d.appendChild(f)}if(this.data.truncated.desc){c=document.createElement("p");c.innerHTML=this.data.truncated.desc;
d.appendChild(c)}this.element=e};SearchShortcut.result.prototype.select=function(){this.element.className+=" focus";
return this};SearchShortcut.result.prototype.deselect=function(){AC.removeClassName(this.element,"focus");
return this};SearchShortcut.geoMap={US:{code:"",noResults:"No shortcut found. Search all of apple.com.",viewAll:"View all search results",shortcuts:"Quick Links",searchText:"Search"},ASIA:{code:"asia"},AT:{code:"at",noResults:"Kein Treffer in Kurzsuche. Vollsuche auf apple.com",viewAll:"Alle Suchergebnisse",searchText:"Suchen"},AU:{code:"au"},BE_FR:{code:"bf",noResults:"Pas de résultat. Essayez une recherche apple.com",viewAll:"Afficher tous les résultats",shortcuts:"Raccourcis",searchText:"Rechercher"},BE_NL:{code:"bl",noResults:"Niets gevonden. Zoek opnieuw binnen www.apple.com.",viewAll:"Toon alle zoekresultaten",shortcuts:"Snelkoppelingen",searchText:"Zoek"},BR:{code:"br",noResults:"Não encontrado. Tente a busca em apple.com",viewAll:"Ver todos os resultados da busca",shortcuts:"Links rapidos",searchText:"Buscar"},CA_EN:{code:"ca",directory:"/ca"},CA_FR:{code:"ca",directory:"/ca/fr",viewAll:"Afficher tous les résultats",shortcuts:"Raccourcis",searchText:"Recherche"},CH_DE:{code:"ce",noResults:"Kein Treffer in Kurzsuche. Vollsuche auf apple.com",viewAll:"Alle Suchergebnisse",searchText:"Suchen"},CH_FR:{code:"cr",noResults:"Pas de résultat. Essayez une recherche apple.com",viewAll:"Afficher tous les résultats",shortcuts:"Raccourcis",searchText:"Rechercher"},CN:{code:"cn",directory:".cn",noResults:"找不到快速搜索结果，请尝试 apple.com.cn 的完整搜索",shortcuts:"快速链接",viewAll:"查看所有搜索结果",searchText:"搜索"},DE:{code:"de",viewAll:"Alle Suchergebnisse",noResults:"Kein Treffer in Kurzsuche. Vollsuche auf apple.com",searchText:"Suchen"},DK:{code:"dk",noResults:"Ingen genvej fundet. Prøv at søge på hele apple.com.",viewAll:"Vis alle søgeresultater",shortcuts:"Hurtige henvisninger",searchText:"Søg"},ES:{code:"es",noResults:"Ningún atajo. Búsqueda completa en apple.com",viewAll:"Ver todos los resultados de búsqueda",shortcuts:"Enlaces rápidos",searchText:"Buscar"},FI:{code:"fi",noResults:"Ei oikotietä. Etsi koko apple.com.",viewAll:"Katso hakutulokset",shortcuts:"Pikalinkit",searchText:"Etsi"},FR:{code:"fr",noResults:"Pas de résultat. Essayez une recherche apple.com",viewAll:"Afficher tous les résultats",shortcuts:"Raccourcis",searchText:"Rechercher"},HK:{code:"hk",noResults:"找不到快速搜尋結果，請試試 apple.com 的完整搜尋",viewAll:"檢視所有搜尋結果",shortcuts:"快速連結",searchText:"搜尋"},HK_EN:{code:"hk",directory:"/hk/en"},ID:{code:"id"},IE:{code:"ie"},IN:{code:"in"},IT:{code:"it",noResults:"Nessuna scorciatoia trovata. Provate su apple.com",viewAll:"Mostra tutti i risultati",shortcuts:"Collegamenti rapidi",searchText:"Cerca"},JP:{code:"jp",noResults:"ショートカットは見つかりませんでした。検索はこちら。",viewAll:"すべての検索結果を見る",shortcuts:"クイックリンク",searchText:"Search"},KR:{code:"kr",noResults:"일치하는 검색결과가 없습니다. 다시 검색하기.",shortcuts:"빠른 연결",viewAll:"검색 결과 전체 보기."},LA:{code:"la",noResults:"No se encontraron resultados. Intenta en apple.com.",viewAll:"Ver todos los resultados de la búsqueda",shortcuts:"Enlaces rápidos",searchText:"Buscar"},LAE:{code:"lae",noResults:"No shortcut found. Search all of apple.com.",viewAll:"View all search results",searchText:"Search"},MX:{code:"mx",noResults:"No se encontraron resultados. Intenta en apple.com.",viewAll:"Ver todos los resultados de la búsqueda",shortcuts:"Enlaces rápidos",searchText:"Buscar"},MY:{code:"my"},NL:{code:"nl",noResults:"Niets gevonden. Zoek opnieuw binnen www.apple.com.",viewAll:"Toon alle zoekresultaten",shortcuts:"Snelkoppelingen",searchText:"Zoek"},NO:{code:"no",noResults:"Fant ingen snarvei. Søk på hele apple.com.",viewAll:"Vis alle søkeresultater",shortcuts:"Hurtigkoblinger",searchText:"Søk"},NZ:{code:"nz"},PH:{code:"ph"},PL:{code:"pl",noResults:"Fraza nie została odnaleziona. Użyj apple.com.",viewAll:"Przeglądaj wszystkie wyniki",shortcuts:"Podręczne łącza",searchText:"Szukaj"},PT:{code:"pt",noResults:"Nenhum resultado. Tente pesquisar em apple.com.",viewAll:"Ver todos os resultados de pesquisa",shortcuts:"Ligações rápidas",searchText:"Procurar"},RU:{code:"ru",noResults:"Ссылок нет. Попробуйте расширенный поиск.",viewAll:"Показать все результаты поиска",shortcuts:"Быстрые ссылки",searchText:"Поиск"},SE:{code:"se",noResults:"Ingen genväg hittad. Sök i hela apple.com.",viewAll:"Visa alla sökresultat",shortcuts:"Snabblänkar",searchText:"Sök"},SG:{code:"sg"},TH:{code:"th"},TW:{code:"tw",noResults:"快速搜尋找不到，試試 apple.com 完整搜尋",viewAll:"瀏覽搜索結果",shortcuts:"快速連結",searchText:"搜尋"},UK:{code:"uk"},VN:{code:"vn"},ZA:{code:"za"},PO:null,TR:null,UA:null,RO:null,CZ:null,HU:null,BG:null,HR:null,GR:null,IS:null};