AC.ViewMaster.Tracker=Class.create();Object.extend(AC.ViewMaster.Tracker.prototype,Event.Listener);
Object.extend(AC.ViewMaster.Tracker.prototype,{count:0,type:"",isReplay:false,ccTime:0,mediaType:"",geoCode:"",movieType:"",overlay:false,interactionCount:0,initialize:function(c,a){this.type=c;
this.options=a||{};this.qtEventSource=document.getElementsByTagName("body")[0];
var b=window.location.pathname;var d=window.location.hostname;if(d.match(/apple.com.cn/)){this.geoCode=" (CN)"
}else{if(!b.match(/^\/(ws|pr|g5|go|ta|wm)\//)){if(b.match(/^\/(\w{2}|befr|benl|chfr|chde|asia|lae)(?=\/)/)){b=b.split("/");
this.geoCode=" ("+b[1].toUpperCase()+")"}}}if(this.geoCode==""){this.geoCode=" (US)"
}if(typeof(AC.OverlayPanel)!="undefined"){if(typeof(AC.OverlayPanel.overlay)!="undefined"){this.listenForEvent(AC.OverlayPanel.overlay,"afterPop",false,this.afterPop);
this.listenForEvent(AC.OverlayPanel.overlay,"afterClose",false,this.afterClose)
}}this.listenForEvent(AC.ViewMaster,"ViewMasterDidShowNotification",false,this.sectionDidChange);
this.listenForEvent(document.event,"replayMovie",false,this.movieDidReplay.bind(this));
this.listenForEvent(document.event,"didFinishMovie",false,this.movieDidEnd);Event.observe(this.qtEventSource,"QuickTime:didStartJogging",this.didStartJogging.bind(this));
Event.observe(this.qtEventSource,"QuickTime:didStopJogging",this.didStopJogging.bind(this));
Event.observe(this.qtEventSource,"QuickTime:begin",this.didBegin.bind(this));Event.observe(this.qtEventSource,"QuickTime:end",this.didEnd.bind(this));
Event.observe(this.qtEventSource,"QuickTime:start",this.didStart.bind(this));Event.observe(this.qtEventSource,"QuickTime:stop",this.didStop.bind(this));
Event.observe(this.qtEventSource,"QuickTime:noCompatibleQTAvailable",this.noCompatibleQTAvailable);
Event.observe(this.qtEventSource,"QuickTime:didSetClosedCaptions",this.didSetClosedCaptions.bind(this))
},setDelegate:function(a){this.delegate=a},pageName:function(a){this._id="";if(a){this._id=this.trackingNameForSection(a)
}else{if(this.viewMaster.currentSection){this._id=this.trackingNameForSection(this.viewMaster.currentSection)
}}this._pageName=AC.Tracking.pageName()+" - "+this._id;if(typeof this._pageName==="string"){this._pageName=this._pageName.replace(/[\'\’\"]/g,"")
}},trackingNameForSection:function(a){var b=a.id.replace("MASKED-","");if(this.delegate&&typeof(this.delegate.trackingNameForSection)==="function"){b=this.delegate.trackingNameForSection(this,b,a)
}return b},isSnowLeopardControllerAvailable:function(){return(typeof(Media)!="undefined")
},didBegin:function(j){if(this.mediaType!="audio"){var c=j.memo.controller;this._pageName=this._pageName.toLowerCase();
this.movieType=this.isSnowLeopardControllerAvailable()?c.movieType():false;try{this._timeScale=(this.isSnowLeopardControllerAvailable())?c.timeScale():c.GetTimeScale();
var h=(this.isSnowLeopardControllerAvailable())?c.duration():c.GetDuration(),a=(this.movieType)?Math.floor(h):Math.floor(h/this._timeScale),g={},d=""
}catch(f){}if(this.isReplay){d="V@R: ";this.isReplay=false}else{d="V@S: "}g.pageName=d+this._pageName;
if(typeof this.type==="undefined"){g.prop13=g.pageName;g.prop4=document.URL.toString().replace(/(#|\?).*/,"");
g.prop33=(typeof(c.videoID)!="undefined")?c.videoID():"";AC.Tracking.trackPage(g);
g.prop13=g.prop3=g.prop4=""}else{s.prop33=(typeof(c.videoID)!="undefined")?c.videoID():"";
s.prop13=d+this._pageName;s.prop4=document.URL.toString().replace(/(#|\?).*/,"");
s.eVar16=s.prop16="Video Plays";s.events="event2";s.Media.trackVars+=",events,prop13,prop4,prop16,eVar16,prop33";
s.Media.trackEvents+=",event2"}if(this.delegate&&typeof this.delegate.QTdidBegin=="function"){g=this.delegate.QTdidBegin(this,g);
var b="";for(var i in g){if(i!="pageName"){b+=","+i;s[i]=g[i]}}s.Media.trackVars+=b
}var k=(this.movieType)?this.movieType:"QuickTime";s.Media.open(this._pageName,a,k);
s.Media.play(this._pageName,"0");s.prop13=s.prop4=s.prop16=s.eVar16=s.events="";
this.mediaType="video"}},didEnd:function(b){if(this.mediaType!="audio"){try{var a=b.memo.controller,c=(this.isSnowLeopardControllerAvailable())?a.time():a.GetTime(),g=(this.isSnowLeopardControllerAvailable())?Math.floor(a.duration()):Math.floor(a.GetDuration()),f=(this.movieType)?Math.floor(c):Math.floor(c/this._timeScale)
}catch(d){}if(f<=g){s.Media.stop(this._pageName,f);s.Media.close(this._pageName)
}}},didStartJogging:function(b){if(this.mediaType!="audio"){try{var a=b.memo.controller,c=(this.isSnowLeopardControllerAvailable())?a.time():a.GetTime(),g=(this.isSnowLeopardControllerAvailable())?a.duration():a.GetDuration(),f=(this.movieType)?Math.floor(c):Math.floor(c/this._timeScale)
}catch(d){}if(f<=g){s.Media.stop(this._pageName,f)}}},didStopJogging:function(b){if(this.mediaType!="audio"){try{var a=b.memo.controller,c=(this.isSnowLeopardControllerAvailable())?a.time():a.GetTime(),g=(this.isSnowLeopardControllerAvailable())?a.duration():a.GetDuration(),f=(this.movieType)?Math.floor(c):Math.floor(c/this._timeScale)
}catch(d){}if(f<=g){s.Media.play(this._pageName,f)}}},didStart:function(b){if(this.mediaType!="audio"){try{var a=b.memo.controller,c=(this.isSnowLeopardControllerAvailable())?a.time():a.GetTime(),g=(this.isSnowLeopardControllerAvailable())?a.duration():a.GetDuration(),f=(this.movieType)?Math.floor(c):Math.floor(c/this._timeScale)
}catch(d){}if(f<=g){s.Media.play(this._pageName,f)}}},didStop:function(b){if(this.mediaType!="audio"){try{var a=b.memo.controller,c=(this.isSnowLeopardControllerAvailable())?a.time():a.GetTime(),g=(this.isSnowLeopardControllerAvailable())?a.duration():a.GetDuration(),f=(this.movieType)?Math.floor(c):Math.floor(c/this._timeScale)
}catch(d){}if(f<=g){s.Media.stop(this._pageName,f)}}},noCompatibleQTAvailable:function(a){var b={};
b.prop6="no QT: "+AC.Tracking.pageName();AC.Tracking.trackClick(b,name,"o",b.prop6)
},didSetClosedCaptions:function(d){var c=d.memo.controller,g=this.isSnowLeopardControllerAvailable()?c.duration():c.GetDuration(),e=d.memo.enabled;
currentTime=this.isSnowLeopardControllerAvailable()?c.time():c.GetTime(),time=(this.movieType)?Math.floor(currentTime):Math.floor(currentTime/this._timeScale);
if(e){this.ccTime=time}else{var b,a;this.ccTime=time-this.ccTime;g=this.isSnowLeopardControllerAvailable()?g:g/this._timeScale;
a=Math.round((this.ccTime/g)*100);if(a>0&&a<11){b="<11"}else{if(a>10&&!a<51){b=">10<51"
}else{if(a>50&&!a<91){b=">50<91"}else{if(a>90){b=">90"}else{a=null}}}}if(a!=null){var f={};
f.pageName=AC.Tracking.pageName()+this.geoCode;f.prop3="cc@o: "+b+" - "+this._pageName;
AC.Tracking.trackClick(f,this,"o",f.prop3)}}},sectionDidChange:function(b){this.viewMaster=b.event_data.data.sender;
var a=b.event_data.data.incomingView;if(a&&!a.content.hasClassName("sneaky")&&(typeof(b.event_data.data.trigger)!="undefined"||window.location.toString().match(a.id)||a.mediaType().match(/video/))){var c={};
this.pageName(a);if(this._id){c.pageName=this._pageName+this.geoCode;this.mediaType="";
if(a.movieLink&&a.movieLink.href){if(a.mediaType().match(/audio\//)){this.mediaType="audio";
c.pageName="A@S: "+c.pageName}else{if(a.mediaType().match(/video\//)){if(this._id!="360"&&this._id!="vr"&&this._id!="qtvr"){this.mediaType="video";
return false}}}c.prop13=c.pageName.replace(/\s*\((\w{2}|befr|benl|chfr|chde|asia|lae)\)/g,"");
c.prop4=a.movieLink.href}if(this.delegate&&typeof(this.delegate.sectionDidChange)=="function"){c=this.delegate.sectionDidChange(this,this.viewMaster,a,this._id,c)
}if(this.interactionCount==0&&this.mediaType==""){c.eVar16=c.prop16="Gallery Interaction";
c.events="event1"}if(this.type=="click"){c.prop3=c.pageName.replace(/\s*\((\w{2}|befr|benl|chfr|chde|asia|lae)\)/g,"");
c.pageName=AC.Tracking.pageName()+this.geoCode;AC.Tracking.trackClick(c,this.viewMaster,"o",c.prop3)
}else{AC.Tracking.trackPage(c)}this.count++;this.interactionCount++}}},movieDidEnd:function(a){var c=a.event_data.data;
var b={};var d=this.trackingNameForSection(c);if(d){b.pageName=AC.Tracking.pageName()+" - "+d+this.geoCode;
if(c.movieLink&&c.movieLink.href){if(this.mediaType=="audio"){b.pageName="A@E: "+b.pageName
}else{if(this.mediaType=="video"){return false}}b.prop13=b.pageName.replace(/\s*\((\w{2}|befr|benl|chfr|chde|asia|lae)\)/g,"")
}if(this.delegate&&typeof(this.delegate.movieDidEnd)=="function"){b=this.delegate.movieDidEnd(this,c,d,b)
}AC.Tracking.trackClick(b,c,"o",b.pageName)}},movieDidReplay:function(a){var c=a.event_data.data;
var b={};var d=this.trackingNameForSection(c);if(d){b.pageName=AC.Tracking.pageName()+" - "+d+this.geoCode;
if(c.movieLink&&c.movieLink.href){if(this.mediaType=="audio"){b.pageName="A@R: "+b.pageName
}else{if(this.mediaType=="video"){this.isReplay=true;return false}}b.prop13=b.pageName.replace(/\s*\((\w{2}|befr|benl|chfr|chde|asia|lae)\)/g,"");
b.prop4=c.movieLink.href}if(this.delegate&&typeof(this.delegate.movieDidReplay)=="function"){b=this.delegate.movieDidReplay(this,c,d,b)
}if(this.type=="click"){b.prop3=b.pageName.replace(/\s*\((\w{2}|befr|benl|chfr|chde|asia|lae)\)/g,"");
b.pageName=AC.Tracking.pageName()+this.geoCode;AC.Tracking.trackClick(b,c,"o",b.prop3)
}else{AC.Tracking.trackPage(b)}}},afterPop:function(a){this.overlay=true;if(this.mediaType!="video"){this.interactionCount=0
}},afterClose:function(a){this.overlay=false}});