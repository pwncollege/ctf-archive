if(typeof(AC)==="undefined"){AC={}}if(typeof(document.event)==="undefined"){document.event={}
}if(Event.Publisher){Object.extend(document.event,Event.Publisher)}AC.SwapView=Class.create({_view:null,currentContent:null,delegate:null,initialize:function(b){if(typeof b==="string"){this._viewId=b
}else{this._view=$(b);this._resetView()}},view:function(){if(!this._view){this._view=$(this._viewId);
this._resetView()}return this._view},_resetView:function(){if(!this._view){return
}var c=this._view.childNodes,d;while(d=c[0]){this._view.removeChild(d)}this._view.addClassName("swapView")
},setDelegate:function(b){this.delegate=b},setContent:function(b){if(b===this.currentContent){return
}if(this.currentContent&&typeof(this.delegate.willClose)==="function"){this.delegate.willClose(this,this.currentContent)
}if(b&&typeof(this.delegate.isContentLoaded)==="function"){if(!this.delegate.isContentLoaded(this,b)){if(typeof(this.delegate.loadContent)==="function"){this.delegate.loadContent(this,b);
return}}}this.setLoadedContent(b)},setLoadedContent:function(d){if(typeof(this.delegate.willShow)==="function"){d=this.delegate.willShow(this,this.currentContent,d)
}var e=true,f;if(typeof(this.delegate.shouldAnimateContentChange)==="function"){e=this.delegate.shouldAnimateContentChange(this,this.currentContent,d)
}if(e&&typeof(this.delegate.willAnimate)==="function"){this.didAnimate=true;if(this.view()&&d&&this.currentContent!==d){this.view().appendChild(d)
}if(typeof(this.delegate.didAppendContent)==="function"){this.delegate.didAppendContent(this,d)
}f=this.delegate.willAnimate(this,this.currentContent,d,this.didShow.bind(this,d))
}else{this.didAnimate=false;if(this.currentContent!==d){if(this.currentContent&&this.currentContent.parentNode){this.currentContent.parentNode.removeChild(this.currentContent)
}if(d){this.view().appendChild(d)}if(typeof(this.delegate.didAppendContent)==="function"){this.delegate.didAppendContent(this,d)
}}if(d){$(d).setOpacity(1)}this.didShow(d)}},didShow:function(b){if(this.currentContent&&(this.currentContent!==b)&&this.currentContent.parentNode){this.currentContent.parentNode.removeChild(this.currentContent)
}if(typeof(this.delegate.didShow)==="function"){this.delegate.didShow(this,this.currentContent,b)
}this.currentContent=b}});if(typeof(AC.ViewMaster)==="undefined"){AC.ViewMaster={}
}AC.ViewMaster.Viewer=Class.create({view:null,triggerClassName:null,currentSection:null,requestedSection:null,sections:null,orderedSections:null,_locked:false,_didShowInitial:false,options:null,initialize:function(w,r,u,o){if(u){this.triggerClassName=u
}this.sections=$H();this.orderedSections=[];this.options=o||{};this.silentPreviousSelection(this.options.silentPreviousSelection);
this.silentFirstSection(this.options.silentFirstSection);this.triggerEvent=this.options.triggerEvent||"click";
var x=null,q,t;if(w){for(t=0;t<w.length;t++){q=this.addSection(w.item(t));if(!x){x=q
}}}this.view=new AC.SwapView(r);this.view.setDelegate(this);var A=document.location.hash,v,y;
this.sectionRegExp=this.options.sectionRegExp||new RegExp(/#(.*)$/);y=A.match(this.sectionRegExp);
if(y&&y[1]){A=y[1]}if(A!==this.view._viewId){var s=document.getElementsByClassName(this.triggerClassName),z;
for(t=0,z;(z=s[t]);t++){if(z.getAttribute("href").match(new RegExp("#"+A+"(?![_w-])"))){v=this.sectionWithId(A);
if(v){x=v}break}}}if(!v&&typeof this.options.initialId==="string"&&this.options.initialId.length>0){x=this.sectionWithId(this.options.initialId)
}this.show(x);this._boundTriggerClicked=this._triggerClicked.bindAsEventListener(this);
if(typeof this.triggerEvent==="object"){for(var t=0,p;p=this.triggerEvent[t];t++){Event.observe(document,p,this._boundTriggerClicked)
}}else{Event.observe(document,this.triggerEvent,this._boundTriggerClicked)}if(AC.Detector.isIEStrict()){Event.observe(document,"mouseup",this._boundTriggerClicked)
}if(this.options.alwaysUseKeyboardNav===true){this.options.useKeyboardNav=true}if(this.options.useKeyboardNav===true||this.options.escapeToClose===true){this._boundKeyDown=this._keyDown.bindAsEventListener(this);
Event.observe(document,"keydown",this._boundKeyDown)}if(typeof(this.listenForEvent)==="function"){this.selectSectionFromEventHandler=this.selectSectionFromEvent.bind(this);
this.listenForEvent(AC.ViewMaster,"ViewMasterSelectSectionWithIdNotification",true,this.selectSectionFromEventHandler);
this.listenForEvent(AC.ViewMaster,"ViewMasterWillShowNotification",true,this.stopMovieIfItsPlaying);
this.listenForEvent(document.event,"replayMovie",false,this.stopMovieIfItsPlaying.bind(this));
if(this.options.parentSectionId){this.listenForEvent(AC.ViewMaster,"ViewMasterWillCloseNotification",false,function(b){var a=b.event_data.data;
if(this===a.sender){return}if(a.outgoingView&&a.outgoingView.id===this.options.parentSectionId){this.willClose(this.view,this.currentSection)
}})}}},initialSectionFromId:function(b){return this.sectionWithId(b)},sectionWithId:function(h){if(!h){return null
}var g=null;if(h&&this.sections.get(h)){g=this.sections.get(h)}if(g){return g}var e,f=null;
e=document.getElementById(h);if(e===this.view._view){e=null}if(!e){e=document.body.down("a."+this.triggerClassName+"[href*=#"+h+"]")
}if(!e){f=document.getElementsByName(h);if(f&&f.length>0){e=f[0]}if(e===this.view._view){e=null
}}if(e){if(e.tagName.toLowerCase()==="a"){if(Element.hasClassName(e,this.triggerClassName)){g=this.addSection(e)
}}else{g=this.addSection(e)}}return g},indexOfSection:function(b){return this.orderedSections.indexOf(b.id)
},selectSectionFromEvent:function(b){if(b.event_data.data.sender===this){return
}if(b.event_data.data.parentTriggerClassName!==this.triggerClassName){return}this.selectSectionWithIdEvent(b.event_data.data.parentSectionId,b.event_data.data.event)
},selectSectionWithIdEvent:function(l,m){var j=this.sectionWithId(l),n=null,h,o,k=false;
if(j){n=j.triggers();if(n&&n.length>0){for(h=0;(o=n[h]);h++){if(Element.Methods.hasClassName(o,this.triggerClassName)){k=true;
break}}}if(!k){o=document.createElement("a");o.className=this.triggerClassName;
o.href="#"+l;o.style.display="none";document.body.appendChild(o);j._triggers.push(o)
}this.triggerClicked(m,$(o))}},setDelegate:function(b){this.delegate=b;if(this.delegate&&typeof(this.delegate.didShow)==="function"&&this.currentSection&&this.currentSection.isContentLoaded()){this.delegate.didShow(this,this.previousSection,this.currentSection)
}},createSectionForContent:function(b){return new AC.ViewMaster.Section(b,this)
},addSection:function(d){var c=this.createSectionForContent(d);this.sections.set(c.id,c);
this.orderedSections.push(c.id);return c},silentPreviousSelection:function(b){if(typeof(b)=="boolean"){this._silentPreviousSelection=b
}return this._silentPreviousSelection},silentFirstSection:function(b){if(typeof(b)=="boolean"){this._silentFirstSection=b
}return this._silentFirstSection},currentTrigger:function(){return this._currentTrigger
},triggerClicked:function(g,f){f.addClassName("active");this._currentTrigger=f;
if(g&&this.options.silentTriggers){Event.stop(g)}var j=null,h;if(!!f.href.match(/#previous/)){j=this.getPreviousSection();
if(!j){return}}else{if(!!f.href.match(/#next/)){j=this.getNextSection();if(!j){return
}}else{var k=f.href.match(this.sectionRegExp);if(k){h=k[1]}else{h=f.name}j=this.sections.get(h)
}}if(!j){j=this.addSection(f)}if(j.isContentRemote()){if(j.isContentLoaded()&&!!f.href.match(/#previous/)&&!!f.href.match(/#next/)){j.clearTrigger(f)
}if(g){Event.stop(g)}}if(j===this.currentSection){if(g){Event.stop(g)}if(typeof(AC.ViewMaster.dispatchEvent)==="function"){AC.ViewMaster.dispatchEvent("ViewMasterDidShowNotification",{sender:this,outgoingView:this.previousSection,incomingView:this.currentSection,trigger:f})
}return}else{if(!j){return}}setTimeout(this.show.bind(this,j),1)},_triggerClicked:function(e){if(this.options.passive){return
}var f=e.element();if(AC.Detector.isIEStrict()&&e.type==="mouseup"){if(f&&f.nodeName.toUpperCase()==="A"){f=f.down("."+this.triggerClassName)
}}else{while(f&&f.nodeName.toUpperCase()!=="A"&&f.nodeName.toUpperCase()!=="BODY"){f=f.parentNode
}}if(this._silentPreviousSelection!==true&&this._silentFirstSection!==true&&!this._locked){if(f&&f.href&&((previousSelection=f.href.toString().match(/SwapViewPreviousSelection$/))||f.href.toString().match(/SwapViewFirstSection$/))){f=$(f);
if(f.hasClassName(this.triggerClassName)||f.descendantOf(this.view.view())){Event.stop(e);
if(previousSelection){this.showPreviousSelection()}else{this.showFirst()}return
}}}if(f&&f.href&&Element.Methods.hasClassName(f,this.triggerClassName)){if(this._locked){Event.stop(e);
return}if(this.options.parentSectionId&&(typeof(this.stopListeningForEvent)==="function")&&(typeof(this.listenForEvent)==="function")&&(typeof(AC.ViewMaster.dispatchEvent)==="function")){var d=this;
Event.stop(e);this.stopListeningForEvent(AC.ViewMaster,"ViewMasterSelectSectionWithIdNotification",true,this.selectSectionFromEventHandler);
this.listenForEvent(AC.ViewMaster,"ViewMasterDidShowNotification",false,function(a){this.stopListeningForEvent(AC.ViewMaster,"ViewMasterDidShowNotification",false,arguments.callee);
d.triggerClicked(a,f);this.listenForEvent(AC.ViewMaster,"ViewMasterSelectSectionWithIdNotification",true,this.selectSectionFromEventHandler)
});AC.ViewMaster.dispatchEvent("ViewMasterSelectSectionWithIdNotification",{sender:this,parentSectionId:this.options.parentSectionId,parentTriggerClassName:this.options.parentTriggerClassName,event:e,trigger:f})
}else{this.triggerClicked(e,f)}}},_keyDown:function(l){if(!this._locked&&l.keyCode!==Event.KEY_ESC&&l.keyCode!==Event.KEY_LEFT&&l.keyCode!==Event.KEY_RIGHT){return
}var o=(l.target)?l.target:l.srcElement,u=o.getAttribute("contenteditable"),t=true;
if(u==null){t=false}if(t&&u==document.body.getAttribute("contenteditable")){t=false
}if(t&&u=="false"){t=false}if(o.tagName.toLowerCase()=="input"||o.tagName.toLowerCase()=="textarea"||o.tagName.toLowerCase()=="select"||t){return
}var q=document.viewport.getScrollOffsets(),r=document.viewport.getHeight(),n=this.view.view(),p=n.getHeight(),s=n.cumulativeOffset()[1];
if(this.options.alwaysUseKeyboardNav===true||(s>=q[1]&&Math.round(s+(p/2))<(q[1]+r))){if(l.keyCode===Event.KEY_LEFT&&this.options.useKeyboardNav===true){this._currentTrigger="arrow_left";
this.showPrevious();var m="previous"}else{if(l.keyCode===Event.KEY_RIGHT&&this.options.useKeyboardNav===true){this._currentTrigger="arrow_right";
this.showNext();var m="next"}else{if(l.keyCode===Event.KEY_ESC&&this.options.escapeToClose===true){if(this.currentSection.content.down('a[href="#SwapViewFirstSection"]')){l.stop();
this._currentTrigger="esc_key";this.showFirst()}else{if(this.currentSection.content.down('a[href="#SwapViewPreviousSelection"]')){l.stop();
this._currentTrigger="esc_key";this.showPreviousSelection()}}var m="escape"}}}if(typeof m!==undefined){this.view._view.fire("AC.ViewMaster.Viewer:usedKeyboardNav",m);
if(typeof this.__slideshow==="object"&&typeof this.__slideshow.userInteracted==="function"){this.__slideshow.userInteracted()
}}}},isContentLoaded:function(c,d){return d.isContentLoaded()},loadContent:function(c,d){if(d){d.loadContent()
}},_showContentDidLoad:false,contentDidLoad:function(f,d,e){if(d&&d.firstChild){this._showContentDidLoad=true
}this.view.setLoadedContent(f);AC.loadRemoteContent.insertScriptFragment(d);this.scrollSectionToVisible(f);
if(this._showContentDidLoad&&this.delegate&&typeof(this.delegate.didShow)==="function"){this.delegate.didShow(this,this.previousSection,this.currentSection)
}this._showContentDidLoad=false},show:function(f,d){if(this._locked||(!f&&!d)){return
}if(!this.options.alwaysShowSection&&f===this.currentSection){return}this._locked=true;
if(this.delegate&&typeof(this.delegate.willShowSection)==="function"){var e=this.delegate.willShowSection(this,this.previousSection,f);
if(e instanceof AC.ViewMaster.Section){f=e}}this.previousSection=this.currentSection;
this.currentSection=f;this.disablePreviousNextIfNeeded();this.scrollSectionToVisible(f);
this.view.setContent(f)},disablePreviousNextIfNeeded:function(){if(!this.currentSection||typeof this.currentSection==="undefined"){return
}var d=this.indexOfSection(this.currentSection),e=this.orderedSections.length-1,f=this.options.discontinuousPreviousNext;
if(!this.previousTriggers){this.previousTriggers=$$("."+this.triggerClassName+'[href="#previous"]')
}else{this.previousTriggers=this.previousTriggers.concat($$("."+this.triggerClassName+'[href="#previous"]')).uniq()
}this.previousTriggers.each(function(a){if(f===true&&d===0){a.addClassName("disabled")
}else{a.removeClassName("disabled")}});if(!this.nextTriggers){this.nextTriggers=$$("."+this.triggerClassName+'[href="#next"]')
}else{this.nextTriggers=this.nextTriggers.concat($$("."+this.triggerClassName+'[href="#next"]')).uniq()
}this.nextTriggers.each(function(a){if(f===true&&d===e){a.addClassName("disabled")
}else{a.removeClassName("disabled")}})},scrollSectionToVisible:function(d){if(typeof this.options.ensureInView==="boolean"&&this.options.ensureInView){if(this._didShowInitial){if(d._isContentLoaded){var c=d.content.viewportOffset()[1];
if(c<0||c>(document.viewport.getHeight()*0.75)){new Effect.ScrollTo(d.content,{duration:0.3})
}}}else{$(document.body).scrollTo()}return true}return false},__applyOptionHeightFromFirstSection:function(){if(this.options.heightFromFirstSection==true&&!this._heightSet){var b=this.sectionWithId(this.orderedSections[0]);
if(b){this.setHeightFromSection(b)}}},setHeightFromSection:function(c){var d=c.heightOfContent();
if(d>0){this.view.view().style.height=d+"px";this._heightSet=true}return d},__zIndex:1001,__manageZ:function(m){if(this.options.manageZ===true||typeof this.options.manageZ==="number"){var j="",h,l,k,g;
if(!m){j=(typeof this.options.manageZ==="number")?this.options.manageZ:this.__zIndex
}if((g=this.view.view())){j=(!m&&(h=parseInt(g.getAttribute("data-manage-z")))&&isNaN(h)===false)?h:j;
g.style.zIndex=j}if(this.previousSection&&this.previousSection.content){l=(!m&&(l=this.previousSection.getZIndexFromContent()))?l:j;
this.previousSection.content.style.zIndex=l}if(this.currentSection&&this.currentSection.content){k=(!m&&(k=this.currentSection.getZIndexFromContent()))?k:j;
this.currentSection.content.style.zIndex=k}if(this.delegate&&typeof this.delegate.manageZ==="function"){this.delegate.manageZ(this,this.previousSection,this.currentSection,j,l,k)
}}},showFirst:function(){this.show(this.getFirstSection())},getFirstSection:function(){return this.sections.get(this.orderedSections[0])
},showNext:function(){this.show(this.getNextSection())},getNextSection:function(){var c=this.indexOfSection(this.currentSection);
if(this.options.discontinuousPreviousNext===true&&c===this.orderedSections.length-1){return false
}else{var d=(this.orderedSections.length-1)===c?0:c+1;return this.sections.get(this.orderedSections[d])
}},showPrevious:function(){this.show(this.getPreviousSection())},getPreviousSection:function(){var d=this.indexOfSection(this.currentSection);
if(this.options.discontinuousPreviousNext===true&&d===0){return false}else{var c=0===d?this.orderedSections.length-1:d-1;
return this.sections.get(this.orderedSections[c])}},showPreviousSelection:function(){this.show(this.getPreviousSelection())
},getPreviousSelection:function(){if(this.previousSection){return this.previousSection
}var b=this.orderedSections.length;for(i=0;i<b;i++){if(this.orderedSections[i]!=this.currentSection.id){return this.sections.get(this.orderedSections[i])
}}return false},willShow:function(d,f,e){if(this.delegate&&typeof(this.delegate.willShow)==="function"){this.delegate.willShow(this,this.previousSection,this.currentSection)
}if(typeof(AC.ViewMaster.dispatchEvent)==="function"){AC.ViewMaster.dispatchEvent("ViewMasterWillShowNotification",{sender:this,outgoingView:this.previousSection,incomingView:this.currentSection})
}this.__manageZ(false);this._repaintTriggers(this.previousSection,this.currentSection);
if(this._didShowInitial&&e&&e!=this.previousSection){$(e.content).setOpacity(0);
$(e.content).removeClassName("hidden")}if(e){return e.willShow(this)}return null
},willClose:function(d,c){if(this.delegate&&typeof(this.delegate.willClose)==="function"){this.delegate.willClose(this,this.previousSection,this.currentSection)
}if(typeof(AC.ViewMaster.dispatchEvent)==="function"){AC.ViewMaster.dispatchEvent("ViewMasterWillCloseNotification",{sender:this,outgoingView:c})
}if(this.previousSection){this.previousSection.willClose(this)}},shouldAnimateContentChange:function(g,h,e){var f=true;
if(this.delegate&&typeof(this.delegate.shouldAnimateContentChange)==="function"){f=this.delegate.shouldAnimateContentChange(this,this.previousSection,this.currentSection)
}else{f=(typeof this.options.shouldAnimateContentChange==="boolean")?this.options.shouldAnimateContentChange:true
}return(typeof f==="boolean")?f:true},willAnimate:function(g,m,h,k){var j=this.options.animationDuration||0.4;
var l=Math.random()+"Queue";if(!this._didShowInitial&&typeof(k)=="function"){k();
return}if(this.delegate&&typeof this.delegate.willAnimate=="function"){return this.delegate.willAnimate(this,m,h,k,l,j)
}if(this.options.shouldAnimateOpacityAndHeight){return this._animationPlusHeight(g,m,h,k,l,j)
}else{return this._animation(g,m,h,k,l,j)}},_animation:function(p,q,s,t,l,r){var n=p.view(),m=this;
if(n){n.style.position="relative"}if(q){q.style.position="absolute"}if(s){s.style.position="absolute"
}var u=function(){if(n){n.style.position=""}if(q){q.style.position=""}if(s){s.style.position=""
}t()};if(AC.Detector.isCSSAvailable("transition")){if(s){s.setOpacity(0);s.setVendorPrefixStyle("transition","opacity "+r+"s")
}if(q&&m.options.shouldAnimateFadeIn!==true){q.setOpacity(1);q.setVendorPrefixStyle("transition","opacity "+r+"s")
}window.setTimeout(function(){if(s){s.setOpacity(1)}if(q&&m.options.shouldAnimateFadeIn!==true){q.setOpacity(0)
}},100);var o=function(a){if(a.target==s&&a.propertyName=="opacity"){s.removeVendorEventListener("transitionEnd",o,false);
u()}};if(s){s.addVendorEventListener("transitionEnd",o,false)}}else{if(q&&m.options.shouldAnimateFadeIn!==true){return new Effect.Parallel([new Effect.Opacity(q,{sync:true,from:1,to:0}),new Effect.Opacity(s,{sync:true,from:0,to:1})],{duration:r,afterFinish:u,queue:{scope:l}})
}else{return new Effect.Opacity(s,{from:0,to:1,duration:r,afterFinish:u,queue:{scope:l}})
}}},_animationPlusHeight:function(q,s,u,v,n,t){var o=q.view(),y=u.offsetHeight||1,w=o.offsetHeight||1,r=(y/w)*100;
if(o){o.style.position="relative"}if(s){s.style.position="absolute"}if(u){u.style.position="absolute"
}var x=function(){if(o){o.style.position=""}if(s){s.style.position=""}if(u){u.style.position=""
}v()};if(AC.Detector.isCSSAvailable("transition")){u.setOpacity(0);u.setVendorPrefixStyle("transition","opacity "+t+"s");
if(s){s.setOpacity(0)}window.setTimeout(function(){u.setOpacity(1)},100);if(!(AC.Detector.isiPad()||AC.Detector.isMobile())){o.setVendorPrefixStyle("transition","height "+t+"s")
}o.style.height=y+"px";var p=function(a){if(a.target==u&&a.propertyName=="opacity"){u.removeVendorEventListener("transitionEnd",p,false);
x()}};u.addVendorEventListener("transitionEnd",p,false)}else{if(s){return new Effect.Parallel([new Effect.Opacity(s,{sync:true,from:1,to:0}),new Effect.Opacity(u,{sync:true,from:0,to:1}),new Effect.Scale(o,r,{scaleMode:{originalHeight:w,originalWidth:o.offsetWidth},sync:true,scaleX:false,scaleContent:false})],{duration:t,afterFinish:x,queue:{scope:n}})
}else{return new Effect.Parallel([new Effect.Opacity(u,{sync:true,from:0,to:1}),new Effect.Scale(o,r,{scaleMode:{originalHeight:w,originalWidth:o.offsetWidth},sync:true,scaleX:false,scaleContent:false})],{duration:t,afterFinish:x,queue:{scope:n}})
}}},didAppendContent:function(d,c){if(this.delegate&&typeof this.delegate.didAppendContent==="function"){this.delegate.didAppendContent(this,c)
}this.__applyOptionHeightFromFirstSection()},hideSwapViewLinks:function(h){var g=this.getPreviousSelection();
if(!g||this._silentPreviousSelection===true){var f=h.select('a[href$="SwapViewPreviousSelection"]');
if(f.length>0){if(!this._previousSectionLinks){this._previousSectionLinks=[]}for(var e=f.length-1;
e>=0;e--){f[e].style.display="none";this._previousSectionLinks.push(f[e])}}}if(g&&this._silentPreviousSelection!==true&&this._previousSectionLinks&&this._previousSectionLinks.length>0){for(var e=this._previousSectionLinks.length-1;
e>=0;e--){this._previousSectionLinks[e].style.display="";this._previousSectionLinks.splice(e,1)
}}var g=this.getFirstSection();if(!g||g==this.currentSection||this._silentFirstSection===true){var f=h.select('a[href$="SwapViewFirstSection"]');
if(f.length>0){if(!this._firstSectionLinks){this._firstSectionLinks=[]}for(var e=f.length-1;
e>=0;e--){f[e].style.display="none";this._firstSectionLinks.push(f[e])}}}if(g&&g!==this.currentSection&&this._silentFirstSection!==true&&this._firstSectionLinks&&this._firstSectionLinks.length>0){for(var e=this._firstSectionLinks.length-1;
e>=0;e--){this._firstSectionLinks[e].style.display="";this._firstSectionLinks.splice(e,1)
}}},stopMovieIfItsPlaying:function(h){if(AC.ViewMaster.Viewer.allowMultipleVideos()!==true){if(h.event_data.data.incomingView){var e=h.event_data.data.sender,f=h.event_data.data.incomingView,g=false
}else{var e=this,f=h.event_data.data,g=true}if(e!=this||g){if((this.currentSection&&this.currentSection.hasMovie())&&(f&&((typeof(f.hasMovie)=="function"&&f.hasMovie())||(f.content&&f.content.getElementsByClassName("movieLink")[0])))){if(this.options.showPreviousOnStopMovie&&this.getPreviousSelection()){this.showPreviousSelection()
}else{if(this.options.showFirstOnStopMovie&&this.getFirstSection()){this.showFirst()
}else{this.currentSection.stopMovie()}}}}}},didShow:function(d,f,e){if(e){this.hideSwapViewLinks(e)
}this.__manageZ(true);if(this.currentSection){this.currentSection.didShow(this)
}this._didShowInitial=true;this._locked=false;if(this.options.shouldAnimateOpacityAndHeight){window.setTimeout(function(){var b=d.view(),a=e.offsetHeight||0;
b.style.height=a+"px"},35)}if(!this._showContentDidLoad&&this.delegate&&typeof(this.delegate.didShow)=="function"){this.delegate.didShow(this,this.previousSection,this.currentSection)
}if(typeof(AC.ViewMaster.dispatchEvent)=="function"){AC.ViewMaster.dispatchEvent("ViewMasterDidShowNotification",{sender:this,outgoingView:this.previousSection,incomingView:this.currentSection,trigger:this._currentTrigger})
}},_repaintTriggers:function(j,h){if(j){var k=j.triggers();for(var g=0,m;(m=k[g]);
g++){m.removeClassName("active")}k=j.relatedElements();for(var g=0,m;(m=k[g]);g++){m.removeClassName("active")
}}if(h){var l=h.triggers();for(var g=0,m;(m=l[g]);g++){m.addClassName("active")
}l=h.relatedElements();for(var g=0,m;(m=l[g]);g++){m.addClassName("active")}}}});
AC.ViewMaster.Viewer.allowMultipleVideos=function(b){if(typeof(b)=="boolean"){this._allowMultipleVideos=b
}return this._allowMultipleVideos};if(Event.Publisher){Object.extend(AC.ViewMaster,Event.Publisher)
}if(Event.Listener){Object.extend(AC.ViewMaster.Viewer.prototype,Event.Listener)
}AC.ViewMaster.Section=Class.create({content:null,moviePanel:null,controllerPanel:null,movie:null,_movieController:null,movieLink:null,endState:null,hasShown:false,_isContentRemote:false,isContentRemote:function(){return this._isContentRemote
},_isContentLoaded:true,isContentLoaded:function(){return this._isContentLoaded
},_onMoviePlayable:Prototype.EmptyFunction,_onMovieFinished:Prototype.EmptyFunction,id:null,triggers:function(){if(!this._triggers){this._triggers=[];
var h=new RegExp("#"+this.id+"$");if(this.viewMaster.sectionRegExp||this.viewMaster.options.sectionRegExp){h=this.viewMaster.sectionRegExp||this.viewMaster.options.sectionRegExp;
h=h.toString().replace(/^\//,"").replace(/\/$/,"");h=new RegExp(h.replace("(.*)",this.id))
}var j=document.getElementsByClassName(this.viewMaster.triggerClassName);for(var f=0,k;
(k=$(j[f]));f++){if(k.tagName.toLowerCase()!=="a"){continue}if(k.href.match(h)){this._triggers.push(k)
}}var g=this.content.getElementsByClassName(this.viewMaster.triggerClassName);for(var f=0,k;
(k=$(g[f]));f++){if(k.tagName.toLowerCase()!=="a"){continue}if(k.href.match(h)){this._triggers.push(k)
}}}return this._triggers},relatedElements:function(){if(!this._relatedElements){this._relatedElements=document.getElementsByClassName(this.id)
}return this._relatedElements},initialize:function(m,l){this.content=$(m);if(this.content.tagName.toLowerCase()==="a"){var s=this.content.getAttribute("href");
var q=s.split("#");this._contentURL=q[0];var p=window.location.href.split("#");
var r=m.className;var o=document.getElementsByTagName("base")[0];var u=o?o.href:null;
if(q.length===2){this.id=q[1]}if(this._contentURL.length>0&&(!u||this._contentURL!=u)&&(this._contentURL!==p[0])&&(!this._contentURL.startsWith("#")||this._contentURL!==s)){this._isContentRemote=true;
this._isContentLoaded=false}else{var n=$(this.id)||$("MASKED-"+this.id);if(n){this.content=n
}}if(!this.id){this.id=this.content.name}}else{this.id=m.id}if(!this._isContentRemote||this._isContentLoaded){this.content.setAttribute("id","MASKED-"+this.id)
}if(l){this.viewMaster=l}if(!this._isContentRemote&&this._isContentLoaded&&!this.content.hasClassName("content")){var t=this.content.getElementsByClassName("content")[0];
if(t){this.content=t}}this.isMobile=AC.Detector.isMobile()},clearTrigger:function(b){if(b.href===("#"+this.id)){return
}b.href="#"+this.id;b.removeAttribute("id");b.removeAttribute("name");if(!this.viewMaster.options.silentTriggers){document.location.hash=this.id
}},remoteContentDidLoad:function(d,c){this.clearTrigger(this.content);this.content=$(d);
this.content.setAttribute("id","MASKED-"+this.id);this._isContentLoaded=true;this.viewMaster.contentDidLoad(this,c)
},loadContent:function(){if(this._isContentLoaded){var j=this;j.viewMaster.contentDidLoad(j,null)
}else{if(this.content.className.indexOf("imageLink")!==-1){var e=this.viewMaster.options.useHTML5Tags?document.createElement("figure"):document.createElement("div");
if(this.viewMaster.options.imageLinkClasses){try{console.warn('"imageLinkClasses" is deprecated. Use "addSectionIdAsClassName" instead.')
}catch(h){}Element.addClassName(e,this.id)}e.appendChild(this.content.cloneNode(true));
if(!!this.viewMaster.options.imageLinkAutoCaptions){var g=typeof this.viewMaster.options.imageLinkAutoCaptions=="string"?this.viewMaster.options.imageLinkAutoCaptions:"title";
if(this.content.hasAttribute(g)){if(this.viewMaster.options.useHTML5Tags){var k=document.createElement("figcaption")
}else{var k=document.createElement("p");Element.addClassName(k,"caption")}k.innerHTML=this.content.getAttribute(g);
Element.insert(e,k)}}this.remoteContentDidLoad(e)}else{if((this.content.className.indexOf("movieLink")!==-1)||(this.content.className.indexOf("audioLink")!==-1)){var e=this.viewMaster.options.useHTML5Tags?document.createElement("figure"):document.createElement("div");
e.appendChild(this.content.cloneNode(true));this.remoteContentDidLoad(e)}else{AC.loadRemoteContent(this._contentURL,true,true,this.remoteContentDidLoad.bind(this),null,this)
}}}},shouldImportScriptForContentURL:function(f,g,e){var h=false;if(f.hasAttribute){h=f.hasAttribute("src")
}else{src=f.getAttribute("src");h=((src!=null)&&(src!==""))}if(!h){scriptText=f.text;
if(scriptText.search(/.*\.location\.replace\(.*\).*/)!==-1){return false}return true
}else{return true}},mediaType:function(){return this.movieLink?"video/quicktime":"text/html"
},willClose:function(b){this._closeController();this._closeMovie()},willShow:function(){if(!this.hasShown){this.hasShown=true;
if(this.viewMaster.options.addSectionIdAsClassName===true){this.content.addClassName(this.id)
}var d=this.content.getElementsByClassName("imageLink");for(var c=0;c<d.length;
c++){this._loadImage(d[c])}if(!this.moviePanel){this.movieLink=this.content.getElementsByClassName("movieLink")[0];
if(this.movieLink){this.posterLink=this.__getPoster(this.content,this.movieLink);
this._loadMovie()}}}return this.content},__getPoster:function(e,d){var f;if(d&&d.hasAttribute("data-poster")){f=d.readAttribute("data-poster")
}else{var f=e.getElementsByClassName("posterLink")[0];if(f){f=f.href}}return f},_heightOfContent:0,heightOfContent:function(){if(this._heightOfContent===0&&!(this._isContentRemote&&!this._isContentLoaded)){if(!this.content.parentNode){this.content.style.visibility="hidden";
this.viewMaster.view.view().appendChild(this.content);this._heightOfContent=this.content.getOuterDimensions().height;
this.viewMaster.view.view().removeChild(this.content);this.content.style.visibility=""
}else{this._heightOfContent=this.content.getOuterDimensions().height}}return this._heightOfContent
},getZIndexFromContent:function(){return(this.content)?(parseInt(this.content.getAttribute("data-manage-z"))||null):null
},didShow:function(f){var e=this.hasMovie()&&!this.isMobile,d=this.isACMediaAvailable();
if(d){if(e){this._movieControls=this.newMovieController();this._playMovie();if(this._movieController){this._movieController.setControlPanel(this._movieControls);
this.onMovieFinished=this.didFinishMovie.bind(this);this._movieController.setDelegate(this)
}else{this.controllerPanel.innerHTML=""}}else{this._playMovie()}}else{if(e){this._movieController=this.newMovieController();
this.controllerPanel.innerHTML="";this.controllerPanel.appendChild(this._movieController.render())
}this._playMovie();if(e){this._onMoviePlayable=this._movieController.monitorMovie.bind(this._movieController);
this._onMovieFinished=this.didFinishMovie.bind(this);this._movieController.attachToMovie(this.movie,{onMoviePlayable:this._onMoviePlayable,onMovieFinished:this._onMovieFinished})
}}},defaultMovieWidth:function(){return 848},defaultMovieHeight:function(){return 480
},defaultOptions:function(){return{width:this.defaultMovieWidth(),height:this.defaultMovieHeight(),controller:false,posterFrame:null,showlogo:false,autostart:true,cache:true,bgcolor:"white",aggressiveCleanup:false}
},_forceACQuicktime:false,isACMediaAvailable:function(){return(typeof(Media)!="undefined"&&this._forceACQuicktime===false)
},setShouldForceACQuicktime:function(b){this._forceACQuicktime=b},movieControls:function(){return this._movieControls
},newMovieController:function(){if(this.isACMediaAvailable()){return this._movieControls||new Media.ControlsWidget(this.controllerPanel)
}else{return new AC.QuicktimeController()}},_loadImage:function(c){var d=document.createElement("img");
if(c.protocol==="about:"){c.href="/"+c.pathname;c.href=c.href.replace(/^\/blank/,"")
}d.setAttribute("src",c.href);if(!this.viewMaster.options.imageLinkAutoCaptions){d.setAttribute("alt",c.title)
}else{d.setAttribute("alt","")}c.parentNode.replaceChild(d,c)},_loadMovie:function(){var d=this.isACMediaAvailable();
this.moviePanel=$(document.createElement("div"));this.moviePanel.addClassName("moviePanel");
this.movieLink.parentNode.replaceChild(this.moviePanel,this.movieLink);this.controllerPanel=$(document.createElement("div"));
this.controllerPanel.addClassName("controllerPanel");if(d===false){}else{this.moviePanel.appendChild(this.controllerPanel)
}if(d===false){this.moviePanel.parentNode.insertBefore(this.controllerPanel,this.moviePanel.nextSibling)
}else{this.moviePanel.appendChild(this.controllerPanel)}this.endState=$(this.content.getElementsByClassName("endState")[0]);
if(this.endState){this.endState.parentNode.removeChild(this.endState);var c=$(this.endState.getElementsByClassName("replay")[0]);
if(c){c.observe("click",function(a){Event.stop(a);this.replayMovie()}.bindAsEventListener(this))
}}},_playMovie:function(m){if(this.movieLink&&this.moviePanel){var k=this.isACMediaAvailable();
if(!k){this.moviePanel.innerHTML=""}else{if(this.movie&&this.movie.parentNode==this.moviePanel){this.moviePanel.removeChild(this.movie);
this.controllerPanel.hide()}if(this.endState&&this.endState.parentNode==this.moviePanel){this.moviePanel.removeChild(this.endState)
}if(this.controllerPanel&&Element.hasClassName(this.controllerPanel,"inactive")){this.controllerPanel.show();
Element.removeClassName(this.controllerPanel,"inactive")}}if(this.posterLink&&this.posterLink.length>0){var g=this.posterLink
}var j=this.movieLink.getAttribute("href",2).toQueryParams(),h=this.defaultOptions(),l;
if(m==true){j.replay=true}h.posterFrame=g;l=Object.extend(h,j);for(opt in l){l[opt]=(l[opt]==="true")?true:(l[opt]==="false")?false:l[opt]
}if(k===true){this._movieController=Media.create(this.moviePanel,this.movieLink.getAttribute("href",2),l);
if(this._movieController){this.movie=this._movieController.video().object()}}else{this.movie=AC.Quicktime.packageMovie(this.movieLink.id+"movieId",this.movieLink.getAttribute("href",2),l,this.moviePanel);
if(!AC.Quicktime.movieIsFlash){this.moviePanel.appendChild(this.movie)}}if(k===true&&!this.isMobile&&this.movie){this._movieControls.reset();
this.moviePanel.appendChild(this.controllerPanel)}if(typeof(document.event.dispatchEvent)=="function"){document.event.dispatchEvent("didStart",this)
}}},replayMovie:function(){var b=this.isACMediaAvailable();if(typeof(document.event.dispatchEvent)=="function"){document.event.dispatchEvent("replayMovie",this)
}if(b){if(this.moviePanel&&this.endState){this.moviePanel.removeChild(this.endState)
}}this._playMovie(true);if(b){this.controllerPanel.show()}this.controllerPanel.removeClassName("inactive");
if(b){this._movieController.setControlPanel(this._movieControls);this._movieController.setDelegate(this)
}else{this.controllerPanel.stopObserving("click",this._movieController.replay);
this._movieController.replay=null;this._movieController.attachToMovie(this.movie,{onMoviePlayable:this._onMoviePlayable,onMovieFinished:this._onMovieFinished})
}},stopMovie:function(){if(!this.hasMovie()){return}this._closeController();this._closeMovie();
if(this.viewMaster.options.showPreviousOnStopMovie&&this.viewMaster.getPreviousSelection()){this.viewMaster.showPreviousSelection()
}else{if(this.viewMaster.options.showFirstOnStopMovie&&this.viewMaster.getFirstSection()){this.viewMaster.showFirst()
}else{if(this.endState){this.moviePanel.appendChild(this.endState)}else{this.stopMovieWithNoEndState()
}}}},stopMovieWithNoEndState:function(){var b=this;setTimeout(function(){b.viewMaster.showPreviousSelection()
},0)},_closeMovie:function(){if(this.movie&&this.moviePanel){if(!this.isACMediaAvailable()){this.moviePanel.removeChild(this.movie);
this.movie=null;this.moviePanel.innerHTML=""}else{if(AC.Detector.isIEStrict()){this.moviePanel.removeChild(this.movie);
this.controllerPanel.hide()}else{this.moviePanel.innerHTML=""}this.movie=null}}},_closeController:function(){if(this.isACMediaAvailable()){if(this._movieController&&this.hasMovie()&&!this.isMobile){this._movieController.stop();
this._movieController.setControlPanel(null);if(AC.Detector.isIEStrict()){this.controllerPanel.hide()
}this.controllerPanel.addClassName("inactive")}}else{if(this._movieController&&this._movieController.movie&&this.hasMovie()&&!this.isMobile){this._movieController.Stop();
this._movieController.detachFromMovie();this.controllerPanel.addClassName("inactive");
this._movieController.replay=this.replayMovie.bind(this);this.controllerPanel.observe("click",this._movieController.replay)
}}},hasMovie:function(){return !!this.movieLink},isMoviePlaying:function(){if(this._movieController){if(typeof(this._movieController.playing)==="function"){return this._movieController.playing()
}if(typeof(this._movieController.playing)==="boolean"){return this._movieController.playing
}}return false},didFinishMovie:function(){if(!this.hasMovie()){return}if(typeof(document.event.dispatchEvent)=="function"){document.event.dispatchEvent("didFinishMovie",this)
}var b=this;window.setTimeout(function(){b.stopMovie.apply(b)},0)}});AC.ViewMaster.Slideshow=Class.create();
if(Event.Listener){Object.extend(AC.ViewMaster.Slideshow.prototype,Event.Listener)
}if(Event.Publisher){Object.extend(AC.ViewMaster.Slideshow.prototype,Event.Publisher)
}Object.extend(AC.ViewMaster.Slideshow.prototype,{contentController:null,animationTimeout:null,options:null,_playing:false,_active:false,_progress:0,setProgress:function(b){this._progress=b
},progress:function(){return this._progress},initialize:function(f,g,e){this.contentController=f;
this.contentController.__slideshow=this;this.triggerClassName=g;this.options=e||{};
if(this.options.stopOnContentTriggerClick===true&&this.contentController.options.useTouchEvents===true){this.options.stopOnUserInteraction=this.options.stopOnContentTriggerClick
}if(!this.options.addNoListeners){this.listenForEvent(AC.ViewMaster,"ViewMasterWillShowNotification",true,this.willShow);
this.listenForEvent(AC.ViewMaster,"ViewMasterDidShowNotification",true,this.didShow)
}if(this.options.autoplay){if(this.options.autoplay===true){this.start()}else{if(typeof this.options.autoplay==="number"){this.toAutoplay=window.setTimeout(function(){this.start()
}.bind(this),this.options.autoplay)}}}Event.observe(document,"click",this._triggerHandler.bindAsEventListener(this));
var h=this.contentController.view.view();Event.observe(h,"AC.ViewMaster.Slideshow:play",this.play.bindAsEventListener(this));
Event.observe(h,"AC.ViewMaster.Slideshow:stop",this.stop.bindAsEventListener(this))
},start:function(){if(this._active){return}this._active=true;if(this.options.wipeProgress=="always"||this.options.wipeProgress=="on start"){this._progress=0
}this.play(true);this._repaintTriggers();if(typeof(document.event.dispatchEvent)=="function"){document.event.dispatchEvent("didStart",this)
}},stop:function(){this._active=false;this.pause();this._repaintTriggers();if(this.toAutoplay){window.clearTimeout(this.toAutoplay);
delete this.toAutoplay}if(typeof(document.event.dispatchEvent)=="function"){document.event.dispatchEvent("didEnd",this)
}},play:function(b){if(!this._active){return}if(this.options.wipeProgress=="always"||(this.options.wipeProgress=="on play"&&!b)){this._progress=0
}this.animationTimeout=setTimeout(this._update.bind(this),this._heartbeatDelay());
this._playing=true},_update:function(){if(typeof(this.options.onProgress)=="function"){this.options.onProgress(this._progress,this.delay())
}if(this._progress>=this.delay()){this._progress=0;this.next()}else{this._progress+=this._heartbeatDelay();
this.animationTimeout=setTimeout(this._update.bind(this),this._heartbeatDelay())
}},delay:function(){return this.options.delay||5000},_heartbeatDelay:function(){return this.options.heartbeatDelay||100
},pause:function(){clearTimeout(this.animationTimeout);this._playing=false},next:function(){var e=this.contentController.options.discontinuousPreviousNext;
if(this.options.discontinuousPreviousNext!==e){this.contentController.options.discontinuousPreviousNext=this.options.discontinuousPreviousNext
}var g=((typeof this.options.stopAfterReturnToSection=="number"&&this.contentController.indexOfSection(this.contentController.currentSection)==this.options.stopAfterReturnToSection)||(typeof this.options.stopAfterReturnToSection=="string"&&this.contentController.currentSection.id==this.options.stopAfterReturnToSection));
var f=this.options.willEnd&&(this.contentController.getNextSection()==this.contentController.getFirstSection());
if(g||f){if(f){try{console.warn("Instead of AC.ViewMaster.Slideshow.options.willEnd = true, please use AC.ViewMaster.Viewer.options.discontinuousPreviousNext = true.")
}catch(h){}}if(this._returnedToSection||f){this.stop()}else{if(!this._returnedToSection){this._returnedToSection=true
}}}if(this._active){this.contentController.showNext()}this.contentController.options.discontinuousPreviousNext=e;
this.contentController.disablePreviousNextIfNeeded()},previous:function(){this.contentController.showPrevious()
},reset:function(){this.contentController.showFirst();this.setProgress(0)},willShow:function(b){if(b.event_data.data.sender!=this.contentController){return
}this.pause()},didShow:function(b){if(b.event_data.data.sender!=this.contentController){return
}this.play()},_triggerHandler:function(e){var d=e.element();if((this.options.stopOnUserInteraction===true||this.options.stopOnContentTriggerClick)&&(link=e.findElement("a"))&&link.hasClassName(this.contentController.triggerClassName)&&link.href.search(this.contentController.currentSection.id)==-1){if(this.options.stopOnContentTriggerClick){try{console.warn('"stopOnContentTriggerClick" is deprecated. Please use "stopOnUserInteraction" instead.')
}catch(f){}this.stop()}else{this.userInteracted()}return}if(d.hasClassName(this.triggerClassName)&&d.href.match(/#slideshow-toggle/)){Event.stop(e);
if(this._active){this.stop()}else{this.start()}}},userInteracted:function(){if(this.options.stopOnUserInteraction===true){this.stop()
}},_repaintTriggers:function(){if(!this.triggerClassName){return}var c=document.getElementsByClassName(this.triggerClassName);
for(var d=c.length-1;d>=0;d--){this._repaintTrigger(c[d])}},_repaintTrigger:function(d){var c=$(d);
if(this._active){c.addClassName("playing")}else{c.removeClassName("playing")}}});
AC.SlideView=Class.create(AC.SwapView,{_resetView:function(){if(!this._view){return
}this._view.addClassName("swapView")},setLoadedContent:function(d){if(typeof(this.delegate.willShow)==="function"){d=this.delegate.willShow(this,this.currentContent,d)
}var e=true,f;if(typeof(this.delegate.shouldAnimateContentChange)==="function"){e=this.delegate.shouldAnimateContentChange(this,this.currentContent,d)
}if(e&&typeof(this.delegate.willAnimate)==="function"){this.didAnimate=true;if(typeof(this.delegate.didAppendContent)==="function"){this.delegate.didAppendContent(this,d)
}f=this.delegate.willAnimate(this,this.currentContent,d,this.didShow.bind(this,d))
}else{this.didAnimate=false;if(this.currentContent!==d){if(typeof(this.delegate.didAppendContent)==="function"){this.delegate.didAppendContent(this,d)
}}if(d){$(d).setOpacity(1)}this.didShow(d)}},didShow:function(b){if(typeof(this.delegate.didShow)==="function"){this.delegate.didShow(this,this.currentContent,b)
}this.currentContent=b}});AC.ViewMaster.SlideViewer=Class.create(AC.ViewMaster.Viewer,{initialize:function(w,r,u,o){if(u){this.triggerClassName=u
}this.sections=$H();this.orderedSections=[];this.options=o||{};this.silentPreviousSelection(this.options.silentPreviousSelection);
this.silentFirstSection(this.options.silentFirstSection);this.triggerEvent=this.options.triggerEvent||"click";
var x=null,q,t;if(w){for(t=0;t<w.length;t++){q=this.addSection(w.item(t));if(!x){x=q
}}}this.view=new AC.SlideView(r);this.view.setDelegate(this);this.__mask=this.view.view().up();
var A=document.location.hash,v,y;this.sectionRegExp=this.options.sectionRegExp||new RegExp(/#(.*)$/);
y=A.match(this.sectionRegExp);if(y&&y[1]){A=y[1]}if(A!==this.view._viewId){var s=document.getElementsByClassName(this.triggerClassName),z;
for(t=0,z;(z=s[t]);t++){if(z.getAttribute("href").match(new RegExp("#"+A+"(?![_w-])"))){v=this.sectionWithId(A);
if(v){x=v}break}}}if(!v&&typeof this.options.initialId==="string"&&this.options.initialId.length>0){x=this.sectionWithId(this.options.initialId)
}this.show(x);this._boundTriggerClicked=this._triggerClicked.bindAsEventListener(this);
if(typeof this.triggerEvent==="object"){for(var t=0,p;p=this.triggerEvent[t];t++){Event.observe(document,p,this._boundTriggerClicked)
}}else{Event.observe(document,this.triggerEvent,this._boundTriggerClicked)}if(AC.Detector.isIEStrict()){Event.observe(document,"mouseup",this._boundTriggerClicked)
}if(this.options.useKeyboardNav===true||this.options.escapeToClose===true){this._boundKeyDown=this._keyDown.bindAsEventListener(this);
Event.observe(document,"keydown",this._boundKeyDown)}if(this.touchShouldUse()){this.__touchLoadEventDependencies()
}if(typeof(this.listenForEvent)==="function"){this.selectSectionFromEventHandler=this.selectSectionFromEvent.bind(this);
this.listenForEvent(AC.ViewMaster,"ViewMasterSelectSectionWithIdNotification",true,this.selectSectionFromEventHandler);
this.listenForEvent(AC.ViewMaster,"ViewMasterWillShowNotification",true,this.stopMovieIfItsPlaying);
this.listenForEvent(document.event,"replayMovie",false,this.stopMovieIfItsPlaying.bind(this));
if(this.options.parentSectionId){this.listenForEvent(AC.ViewMaster,"ViewMasterWillCloseNotification",false,function(b){var a=b.event_data.data;
if(this===a.sender){return}if(a.outgoingView&&a.outgoingView.id===this.options.parentSectionId){this.willClose(this.view,this.currentSection)
}})}}},touchShouldUse:function(){if(this.options.useTouchEvents===true){if(typeof AC.Detector==="undefined"||!(AC.Detector.isMobile()||AC.Detector.isiPad())){return this.options.useTouchEvents=false
}return true}return this.options.useTouchEvents=false},__touchLoadEventDependencies:function(){if(typeof Element.trackTouches==="function"){this.__touchInitTrackTouches()
}else{if($("swap-view-track-touches-script-tag")===null){var c=document.getElementsByTagName("head")[0];
var d=document.createElement("script");d.type="text/javascript";d.setAttribute("src","http://images.apple.com/global/scripts/pagingview.js");
d.setAttribute("id","swap-view-track-touches-script-tag");c.appendChild(d)}this.__boundTouchInitTrackTouches=this.__touchInitTrackTouches.bindAsEventListener(this);
document.observe("ac:trackTouches:load",this.__boundTouchInitTrackTouches)}},__touchInitTrackTouches:function(){this.options.discontinuousPreviousNext=true;
this.options.continuous=false;this._shouldBeContinuous=false;this.__boundTouchTrackEvents=this.__touchTrackEvents.bindAsEventListener(this);
this.__maskWidth=this.__mask.getWidth()||0;this.view.view().trackTouches(this.__boundTouchTrackEvents,this.__boundTouchTrackEvents,this.__boundTouchTrackEvents,{stopEvent:"horizontal",stopThreshold:10})
},__touchTrackEvents:function(c){var d=this.view.view();d.setVendorPrefixStyle("transition-duration","0");
if(c.startCoords&&c.coords){if(c.difference&&typeof this.__touchTrackedStartOffset!=="undefined"){d.setVendorPrefixTransform(this.__touchTrackingNewLeft(c)+"px")
}else{this.__touchStart(c)}if(c.touches.length===0){this.__touchEnd(c)}}},__touchStart:function(f){var e=this.view.view(),d;
this.__storedShouldAnimateContentChange=this.options.shouldAnimateContentChange;
this.options.shouldAnimateContentChange=false;if(typeof this.__touchAnimateAfterTouchEnd!=="undefined"){this.__touchAnimateAfterTouchEnd(false)
}d=e.translateOffset();if(d===null||typeof d!=="object"){this.__touchTrackedStartOffset=0
}else{this.__touchTrackedStartOffset=d.x}},__touchEnd:function(p){var j=this.view.view(),l=p.difference.abs.x/this.__maskWidth,m=p.difference.current.x/this.__maskWidth,n=this.options.animationDuration||0.4,k,q,o;
if(m>0.4||p.speed>=7){if(p.direction.x==="right"){k=this.getNextSection()}else{if(p.direction.x==="left"){k=this.getPreviousSection()
}}}this.__touchSetTransitionEnd(j,k);if(k===false||typeof k==="undefined"){this._animate(this.__touchTrackedStartOffset,n*l)
}else{q=(k.content.positionedOffset()[0])*-1;if(l>=0.5){n*=0.5}this._animate(q,n)
}if(p.difference.abs.x>5&&typeof this.__slideshow==="object"&&typeof this.__slideshow.userInteracted==="function"){this.__slideshow.userInteracted()
}delete this.__touchTrackedStartOffset},__touchSetTransitionEnd:function(d,e){var f=function(a){if(a!==false){this.show(e)
}this.options.shouldAnimateContentChange=this.__storedShouldAnimateContentChange;
delete this.__storedShouldAnimateContentChange;d.removeVendorEventListener("transitionEnd",this.__touchAnimateAfterTouchEnd,false);
delete this.__touchAnimateAfterTouchEnd};this.__touchAnimateAfterTouchEnd=f.bindAsEventListener(this);
d.addVendorEventListener("transitionEnd",this.__touchAnimateAfterTouchEnd,false)
},__touchTrackingNewLeft:function(g){var f=this.isAtEnd(this.currentSection),e,h;
e=function(a,b){var d,c,k;d=function(j){return(j==1)?1:1-Math.pow(2,-3*j)};k=a/b;
c=parseFloat(d(k)*(b/3));return c};if(f!==false&&(f==="left"&&g.difference.x<0)||(f==="right"&&g.difference.x>0)){h=e(g.difference.abs.x,this.__maskWidth);
if(f==="left"){h*=-1}}else{h=g.difference.x}return this.__touchTrackedStartOffset-h
},isAtEnd:function(c){var d=this.orderedSections.indexOf(c.id);if(d===0){return"left"
}else{if(d===this.orderedSections.length-1){return"right"}}return false},getNextSection:function($super){if(this.options.continuous){this._shouldBeContinuous=true
}return $super()},getPreviousSection:function($super){if(this.options.continuous){this._shouldBeContinuous=true
}return $super()},willShow:function($super,d,f,e){if(this.options.shouldAddActiveClassToContent===true){if(f){f.removeClassName("active")
}if(e){e.content.addClassName("active")}}return $super(d,f,e)},__fixScrollLeft:function(b){if(this.__fixScrollLeftCounter===undefined||(b&&b.type&&b.type==="load")){this.__fixScrollLeftCounter=0
}if(this.__mask.scrollLeft!==0||this.__fixScrollLeftCounter<5){this.__mask.scrollLeft=0;
this.__fixScrollLeftCounter++;window.setTimeout(this.__boundFixScrollLeft,10)}},willAnimate:function($super,g,m,h,j){this.__boundFixScrollLeft=this.__fixScrollLeft.bind(this);
window.setTimeout(this.__boundFixScrollLeft,50);Event.observe(window,"load",this.__boundFixScrollLeft);
var k=g.view().offsetLeft||0,l=-h.offsetLeft||0;if(k!==l){this._didShowInitial=true;
$super(g,m,h,j);this._didShowInitial=false}else{$super(g,m,h,j)}this.willAnimate=$super
},_animate:function(f,d){var e=this.view.view();if(d==0){e.setVendorPrefixStyle("transition","none")
}else{e.setVendorPrefixStyle("transition","-webkit-transform "+d+"s cubic-bezier(0,0,0.25,1)")
}e.setAttribute("left",f);if(AC.Detector.supportsThreeD()){e.setVendorPrefixStyle("transform","translate3d("+f+"px, 0, 0)")
}else{e.setVendorPrefixStyle("transform","translate("+f+"px, 0)")}},_animation:function(u,w,z,A,p,y){var r=u.view(),B=r.offsetLeft||0,s=-z.offsetLeft||0;
z.setOpacity(1);if(this._shouldBeContinuous){var x=this.indexOfSection(u.delegate.currentSection),v=this.indexOfSection(u.delegate.previousSection);
var C=s;if((x===0)&&(v===this.orderedSections.length-1)){s=(w.positionedOffset()[0]+w.getWidth())*-1;
this._continuousCloneElement=this._continuousClone(u,z,s)}else{if((x===this.orderedSections.length-1)&&(v===0)){s=(w.positionedOffset()[0]-w.getWidth())*-1;
this._continuousCloneElement=this._continuousClone(u,z,s)}}}var q=this;if(AC.Detector.isCSSAvailable("transition")&&AC.Detector.isCSSAvailable("transform")){this._animate(s,y);
var t=function(a){if(a.target==r&&a.propertyName.match(/transform$/i)){r.removeVendorEventListener("transitionEnd",t,false);
q._continuousReset(C,u);A()}};r.addVendorEventListener("transitionEnd",t,false)
}else{return new Effect.Move(r,{x:s-B,y:0,duration:y,afterFinish:function(){q._continuousReset(C,u);
A()},queue:{scope:p}})}},_continuousClone:function(e,f,h){if(this._shouldBeContinuous){var g=f.cloneNode(true);
g.id=g.id+"-clone";g.innerHTML=f.innerHTML;g.setStyle("position: absolute; top: 0; left:"+(h*-1)+"px");
e._view.insert(g);return g}else{return false}},_continuousReset:function(c,d){if(this._shouldBeContinuous){d._view.setAttribute("left",c);
if(AC.Detector.isCSSAvailable("transition")&&AC.Detector.isCSSAvailable("transform")){d._view.setVendorPrefixStyle("transition","none");
if(AC.Detector.supportsThreeD()){d._view.setVendorPrefixStyle("transform","translate3d("+c+"px, 0, 0)")
}else{d._view.setVendorPrefixStyle("transform","translate("+c+"px, 0)")}}else{d._view.setStyle("left:"+c+"px")
}delete this._shouldBeContinuous}if(this._continuousCloneElement){if(this._removeContinuousCloneElement){this._continuousCloneElement.remove();
delete this._continuousCloneElement;delete this._removeContinuousCloneElement}else{this._removeContinuousCloneElement=true
}}}});AC.loadRemoteContent=function(m,k,q,l,s,o){if(typeof m!=="string"){return
}if(typeof k!=="boolean"){k=true}if(typeof q!=="boolean"){q=true}var n=arguments.callee;
var p=n._loadArgumentsByUrl[m];if(!p){n._loadArgumentsByUrl[m]={contentURL:m,importScripts:k,importCSS:q,callback:l,context:s,delegate:o};
var r={method:"get",onSuccess:arguments.callee.loadTemplateHTMLFromRequest,onFailure:arguments.callee.failedToadTemplateHTMLFromRequest,onException:function(b,a){throw (a)
}};if(!m.match(/\.json$/)){r.requestHeaders={Accept:"text/xml"};r.onCreate=function(a){a.request.overrideMimeType("text/xml")
}}new Ajax.Request(m,r)}};AC.loadRemoteContent._loadArgumentsByUrl={};AC.loadRemoteContent.loadTemplateHTMLFromRequest=function(F){var D=F.request.url;
var w=arguments.callee;var A=AC.loadRemoteContent._loadArgumentsByUrl[D];var s=window.document;
var y=F.responseXMLValue().documentElement;if(AC.Detector.isIEStrict()){y=y.ownerDocument
}var s=window.document;var x=document.createDocumentFragment();if(A.importScripts){AC.loadRemoteContent.importScriptsFromXMLDocument(y,x,A)
}if(A.importCSS){AC.loadRemoteContent.importCssFromXMLDocumentAtLocation(y,D,A)
}var r=null;var G=null;var B=y.getElementsByTagName("body")[0];if(!B){return}B.normalize();
var G=Element.Methods.childNodeWithNodeTypeAtIndex(B,Node.ELEMENT_NODE,0);if(G){r=s._importNode(G,true);
if(r.cleanSpaces){r.cleanSpaces(true)}}else{if(B.cleanSpaces){B.cleanSpaces(true)
}else{if(typeof B.normalize==="function"){B.normalize()}}var z=B.childNodes;r=s.createDocumentFragment();
var v=/\S/;for(var C=0,E=0;(E=z[C]);C++){var u=s._importNode(E,true);r.appendChild(u)
}}var t=A.callback;t(r,x,A.context)};AC.loadRemoteContent.javascriptTypeValueRegExp=new RegExp("text/javascript","i");
AC.loadRemoteContent.javascriptLanguageValueRegExp=new RegExp("javascript","i");
AC.loadRemoteContent.documentScriptsBySrc=function(){if(!AC.loadRemoteContent._documentScriptsBySrc){AC.loadRemoteContent._documentScriptsBySrc={};
var h=document.getElementsByTagName("script");if(!h||h.length===0){return AC.loadRemoteContent._documentScriptsBySrc
}for(var o=0,j=null;(j=h[o]);o++){var n=j.getAttribute("type");var l=null;var k=j.getAttribute("language");
if(!this.javascriptTypeValueRegExp.test(n)&&!this.javascriptLanguageValueRegExp.test(k)){continue
}if(j.hasAttribute){var m=j.hasAttribute("src")}else{var m=Element.Methods.hasAttribute(j,"src")
}if(m){var l=j.getAttribute("src");AC.loadRemoteContent._documentScriptsBySrc[l]=l
}}}return AC.loadRemoteContent._documentScriptsBySrc};AC.loadRemoteContent.importScriptsFromXMLDocument=function(C,N,x){var K=C.getElementsByTagName("script"),J,I,B,w,M=x.contentURL,y=x.delegate,L=x.context,O=(y&&typeof y.shouldImportScriptForContentURL==="function"),z=navigator.userAgent.toLowerCase(),v=(AC.Detector.isIEStrict()&&parseInt(z.substring(z.lastIndexOf("msie ")+5))<9),H=true;
if(!N){N=document.createDocumentFragment()}var F=AC.loadRemoteContent.documentScriptsBySrc();
for(var A=0,E=null;(E=K[A]);A++){J=E.getAttribute("type");I=null;H=true;B=E.getAttribute("language");
if(!this.javascriptTypeValueRegExp.test(J)&&!this.javascriptLanguageValueRegExp.test(B)){continue
}if(E.hasAttribute){w=E.hasAttribute("src");I=E.getAttribute("src")}else{I=E.getAttribute("src");
w=((I!=null)&&(I!==""))}if(E.getAttribute("id")==="Redirect"||(O&&!y.shouldImportScriptForContentURL(E,M,L))){continue
}if(w){if(!F.hasOwnProperty(I)){var D=document.createElement("script");D.setAttribute("type","text/javascript");
if(v){D.tmp_src=I;D.onreadystatechange=function(){var b=window.event.srcElement,a;
if(!b.isLoaded&&((b.readyState=="complete")||(b.readyState=="loaded"))){a=b.tmp_src;
if(a){b.tmp_src=null;b.src=a;b.isLoaded=false}else{b.onreadystatechange=null;b.isLoaded=true
}}}}else{D.src=I}AC.loadRemoteContent._documentScriptsBySrc[I]=I;N.appendChild(D)
}}else{var D=document.createElement("script");D.setAttribute("type","text/javascript");
if(v){var G=new Function(E.text);D.onreadystatechange=function(){var a=window.event.srcElement;
if(!a.isLoaded&&((a.readyState=="complete")||(a.readyState=="loaded"))){a.onreadystatechange=null;
a.isLoaded=true;G()}}}else{D.text=E.text}AC.loadRemoteContent._documentScriptsBySrc[I]=I;
N.appendChild(D)}}return N};AC.loadRemoteContent.insertScriptFragment=function(m){if(!m){return
}AC.isDomReady=false;Event._domReady.done=false;var n=document.getElementsByTagName("head")[0],k=m.childNodes,h,o,j=function(){var a;
if(!window.event||((a=window.event.srcElement)&&(a.isLoaded||((typeof a.isLoaded==="undefined")&&((a.readyState=="complete")||(a.readyState=="loaded")))))){arguments.callee.loadedCount++;
if(a&&!a.isLoaded){a.onreadystatechange=null;a.isLoaded=true}if(arguments.callee.loadedCount===arguments.callee.loadingCount){Event._domReady()
}}};j.loadedCount=0;j.loadingCount=m.childNodes.length;for(o=0;(h=k[o]);o++){if(h.addEventListener){h.addEventListener("load",j,false)
}else{if(typeof h.onreadystatechange==="function"){var l=h.onreadystatechange;h.onreadystatechange=function(b){var a=window.event.srcElement;
l.call(a);j()}}else{h.onreadystatechange=j}}}n.appendChild(m);n=null};AC.loadRemoteContent.documentLinksByHref=function(){if(!AC.loadRemoteContent._documentLinksByHref){AC.loadRemoteContent._documentLinksByHref={};
var g=document.getElementsByTagName("link");if(!g||g.length===0){return AC.loadRemoteContent._documentLinksByHref
}for(var m=0,k=null;(k=g[m]);m++){var l=k.getAttribute("type");if(k.type.toLowerCase()!=="text/css"){continue
}var j=null;if(k.hasAttribute){var h=k.hasAttribute("href")}else{var h=Element.hasAttribute(k,"href")
}if(h){var j=k.getAttribute("href");AC.loadRemoteContent._documentLinksByHref[j]=j
}}}return AC.loadRemoteContent._documentLinksByHref};AC.loadRemoteContent.__importCssElementInHeadFromLocation=function(o,m,r){var p=(o.tagName.toUpperCase()==="LINK");
if(p){var n=o.getAttribute("type");if(!n||n&&n.toLowerCase()!=="text/css"){return
}var q=o.getAttribute("href");if(!q.startsWith("http")&&!q.startsWith("/")){var k=q;
if(r.pathExtension().length>0){r=r.stringByDeletingLastPathComponent()}q=r.stringByAppendingPathComponent(k)
}if(AC.Detector.isIEStrict()){var s=window.document.createStyleSheet(q,1)}else{var l=window.document.importNode(o,true);
l.href=q}AC.loadRemoteContent.documentLinksByHref()[q]=q}if(!AC.Detector.isIEStrict()||(AC.Detector.isIEStrict()&&!p)){m.insertBefore(l,m.firstChild)
}};AC.loadRemoteContent.importCssFromXMLDocumentAtLocation=function(l,r,m){var k=window.document.getElementsByTagName("head")[0];
var q=[];q.addObjectsFromArray(l.getElementsByTagName("style"));q.addObjectsFromArray(l.getElementsByTagName("link"));
if(q){var p=AC.loadRemoteContent.documentLinksByHref();for(var o=0,n=null;(n=q[o]);
o++){var s=n.getAttribute("href");if(p.hasOwnProperty(s)){continue}this.__importCssElementInHeadFromLocation(n,k,r)
}}};Ajax.Request.prototype._overrideMimeType=null;Ajax.Request.prototype.overrideMimeType=function(b){this._overrideMimeType=b;
if(this.transport.overrideMimeType){this.transport.overrideMimeType(b)}};Ajax.Request.prototype._doesOverrideXMLMimeType=function(){return(this._overrideMimeType==="text/xml")
};Ajax.Response.prototype.responseXMLValue=function(){if(AC.Detector.isIEStrict()){var b=this.transport.responseXML.documentElement;
if(!b&&this.request._doesOverrideXMLMimeType()){this.transport.responseXML.loadXML(this.transport.responseText)
}}return this.transport.responseXML};