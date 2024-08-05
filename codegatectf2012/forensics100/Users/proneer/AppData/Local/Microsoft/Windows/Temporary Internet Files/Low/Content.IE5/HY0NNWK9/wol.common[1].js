
Type.registerNamespace("Wol.Util");
Wol.Util.TypeCheck={StringType:1,NumberType:2,BooleanType:4,isString:function(a)
{
if(a==undefined||a==null)
return false;
else
if(typeof a=="string")
return true;
else
if(a instanceof String)
return true;
else
return false
},isNumber:function(a)
{
if(a==undefined||a==null)
return false;
else
if(typeof a=="number")
return true;
else
if(a instanceof Number)
return true;
else
return false
},isBoolean:function(a)
{
if(a==undefined||a==null)
return false;
else
if(typeof a=="boolean")
return true;
else
if(a instanceof Boolean)
return true;
else
return false
},isOfType:function(c,b)
{
Sys.Debug.assert(arguments.length==2);
if(!Wol.Util.TypeCheck.isNumber(b))
throw Error.argument("type","type must be integer!");
var a=false;
if(b|Wol.Util.TypeCheck.StringType==Wol.Util.TypeCheck.StringType)
{
a=Wol.Util.TypeCheck.isString(c);
if(a==true)
return true
}
if(b|Wol.Util.TypeCheck.StringType==Wol.Util.TypeCheck.NumberType)
{
a=Wol.Util.TypeCheck.isNumber(c);
if(a==true)
return true
}
if(b|Wol.Util.TypeCheck.StringType==Wol.Util.TypeCheck.BooleanType)
{
a=Wol.Util.TypeCheck.isBoolean(c);
if(a==true)
return true
}
return a
}};
Wol.Util.Event=function(a)
{
Sys.Debug.assert(arguments.length<=1);
if(a)
{
Sys.Debug.assert(Wol.Util.TypeCheck.isString(a));
this._name=a
}
else
this._name="GenericEvent"
};
Wol.Util.Event.prototype={get_events:function()
{
if(!this._events)
this._events=new Sys.EventHandlerList;
return this._events
},addHandler:function(a)
{
Sys.Debug.assert(arguments.length==1);
Sys.Debug.assert(a!=undefined&&a!=null);
this.get_events().addHandler(this._name,a)
},removeHandler:function(a)
{
Sys.Debug.assert(arguments.length==1);
Sys.Debug.assert(a!=undefined&&a!=null);
this.get_events().removeHandler(this._name,a)
},raiseEvent:function(a)
{
Sys.Debug.assert(arguments.length<=1);
var b=this.get_events().getHandler(this._name);
if(b)
{
if(a==undefined||a==null)
a=Sys.EventArgs.Empty;
b(this,a)
}
}};
Wol.Util.Event.registerClass("Wol.Util.Event");
Wol.Util.RefCountEvent=function()
{
Wol.Util.RefCountEvent.initializeBase(this);
this._handlerList=[]
};
Wol.Util.RefCountEvent.prototype={get_HandlersCount:function()
{
return this._handlerList.length
},onHandlersChange:new Wol.Util.Event("on"+this._name+"_HandlersChange"),addHandler:function(a)
{
var b=false;
if(this._handlerList&&!Array.contains(this._handlerList,a))
{
Array.add(this._handlerList,a);
b=true
}
var c=Wol.Util.RefCountEvent.callBaseMethod(this,"addHandler",[a]);
b&&
this.onHandlersChange.raiseEvent();
return c
},removeHandler:function(b)
{
var a=false;
if(this._handlerList)
{
Array.remove(this._handlerList,b);
a=true
}
var c=Wol.Util.RefCountEvent.callBaseMethod(this,"removeHandler",[b]);
a&&
this.onHandlersChange.raiseEvent();
return c
}};
Wol.Util.RefCountEvent.registerClass("Wol.Util.RefCountEvent",Wol.Util.Event);
Wol.Util.Tools={cloneProperties:function(c,d,a)
{
Sys.Debug.assert(arguments.length==3);
Sys.Debug.assert(c!=undefined&&c!=null);
Sys.Debug.assert(d!=undefined&&d!=null);
Sys.Debug.assert(a!=undefined&&a!=null&&a.length>=0);
for(var b=0;b<a.length;b++)
d[a[b]]=c[a[b]].clone()
}};
Wol.Util.HashParameters={Regex:new RegExp("([^&#=]*)=([^&]*)","g"),NullValue:"nil",onHashChange:new Wol.Util.RefCountEvent("hashChangeEvent"),Get:function(c)
{
var a=null;
if(c&&c!="")
{
var b=window.location.hash;
if(b&&b!=""&&b!="#")
{
var d=this._FindParamMatch(b,c);
if(d)
a=d[2]
}
}
if(a==Wol.Util.HashParameters.NullValue)
a=null;
return a
},Set:function(d,f)
{
if(d&&d!="")
{
var b=this._EncodeParam(d,f),
a=this._GetHash(),
c=a;
if(a&&a!=""&&a!="#")
{
var e=this._FindParamMatch(a,d);
if(e!=null)
c=this._ReplaceMatch(a,e,b);
else
if(b!="")
c=a+"&"+b
}
else
if(b!="")
c="#"+b;
a!=c&&
this._SetHash(c)
}
},InitializeEvents:function()
{
var a=Wol.Util.HashParameters;
a._hashLastValue=a._GetHash();
var b=function()
{
if(!a._hashEmulationEnabled||a.onHashChange.get_HandlersCount()==0)
{
clearInterval(a._hashEmulationTimer);
a._hashEmulationTimer=null
}
else
if(!a._hashEmulationTimer)
a._hashEmulationTimer=setInterval(a._EvaluateHashChange,a._hashEmulationInterval)
};
a.onHashChange.onHandlersChange.addHandler(b);
var c=function(c)
{
if(a._hashEmulationEnabled)
{
a._hashEmulationEnabled=false;
b()
}
a.onHashChange.raiseEvent(c)
};
$addHandler(window,"hashchange",c)
},_hashEmulationEnabled:true,_hashEmulationInterval:300,_hashEmulationTimer:null,_hashLastValue:null,_EvaluateHashChange:function()
{
var a=Wol.Util.HashParameters,
b=a._GetHash();
if(a._hashLastValue!=b)
{
a._hashLastValue=b;
a.onHashChange.raiseEvent();
return true
}
else
return false
},_GetHash:function()
{
return window.location.hash
},_SetHash:function(a)
{
window.location.hash=a
},_FindParamMatch:function(d,c)
{
var b=this.Regex,
a=null;
b.lastIndex=0;
do
{
a=b.exec(d);
if(this._IsParamMatch(a,c))
break
}while(a!=null);
return a
},_IsParamMatch:function(a,b)
{
return a&&a.length&&a.length==3&&a[1].toLowerCase()==b.toLowerCase()
},_ReplaceMatch:function(b,c,f)
{
var a=b.substr(0,c.index),
e="",
d=c.index+c[0].length;
if(d<b.length)
e=b.substr(d);
if(f==""&&a.charAt(a.length-1)=="&")
a=a.slice(0,-1);
b=a+f+e;
return b
},_EncodeParam:function(c,a)
{
var b="";
if(a&&a!=null&&a!="")
{
a=encodeURI(a);
b=c+"="+a
}
else
b=c+"="+Wol.Util.HashParameters.NullValue;
return b
}};
Wol.Util.CustomEvent=function()
{
var a={_oSourceElement:null};
return {Handlers:{},Attach:function(a,b)
{
if(b!=null&&a!=null)
this.Handlers[a]=b
},Detach:function(a)
{
if(a!=null&&this.Handlers[a]!="undefined")
delete this.Handlers[a]
},Raise:function(b)
{
a._oSourceElement=typeof b!="undefined"?b:null;
for(var c in this.Handlers)
this.Handlers[c](a._oSourceElement)
}}
};
Wol.Util.HashParameters.InitializeEvents();
if(typeof Wol=="undefined")
Wol={};
if(typeof Wol.ContentInstrumentation=="undefined")
Wol.ContentInstrumentation={};
Wol.ContentInstrumentation.Logging={envDomain:null,env:null,rioEnv:null,initLogging:null,tagPrefix:"ms",tagRegex:null,coreTagsRegex:null,isBiSelectorSupported:typeof document.querySelectorAll!="undefined",impressionDelay:8,requestIdTag:"RequestId",coreTags:[],impressionFilter:"*",httpScheme:"http:",maxPartitionKey:"0",sessionDomain:".microsoft.com",clientBiSettingsUri:"ClientBISettings.js",scriptPath:null,customEventField:"CustomEvent",batchImpressions:true,enableMuidCheck:false,muidFrameElement:null,muidTimeout:500,viewDataIsReady:false,scriptIsReady:false,batchBeacons:[],customEventMaps:[],Annotate:function(a,f,g)
{
var b=Wol.ContentInstrumentation.Logging;
try
{
if(a==null||a=="")
throw Error.argument("dataname","Parameter dataname must not be null or empty");
var c=b._getDomTarget([g,this]);
if(c==null)
throw Error.argument("element","Parameter 'element' can only be null if 'this' is a DOM element");
var e=b._getAnnotationName(a);
jQuery(c).attr(e,f)
}
catch(d)
{
b._logError(d)
}
},AnnotateMetatag:function(a,e)
{
var b=Wol.ContentInstrumentation.Logging;
try
{
if(a==null||a=="")
throw Error.argument("dataname","Parameter dataname must not be null or empty");
var d=b._getAnnotationName(a);
b._setAnnotatedMetatag(d,e)
}
catch(c)
{
b._logError(c)
}
},AnnotateMany:function(a,e)
{
var b=Wol.ContentInstrumentation.Logging;
try
{
if(a==null)
throw Error.argument("dictionary","Parameter 'dictionary' must not be null or empty");
for(var c in a)
b.Annotate(c,a[c],e)
}
catch(d)
{
b._logError(d)
}
},AnnotateManyMetaTag:function(a)
{
var b=Wol.ContentInstrumentation.Logging;
try
{
if(a==null)
throw Error.argument("dictionary","Parameter 'dictionary' must not be null or empty");
for(var c in a)
b.AnnotateMetatag(c,a[c])
}
catch(d)
{
b._logError(d)
}
},GetSessionID:function()
{
var b=Wol.ContentInstrumentation.Logging,
a="";
try
{
var d=Wol.Mscom.sessionGuidCookieName;
a=b._getCookieValue(d)
}
catch(c)
{
b._logError("GetSessionID Error:"+c)
}
return a
},SetSessionID:function(c)
{
var d=Wol.ContentInstrumentation.Logging,
a=Wol.Mscom.sessionGuidCookieName,
b=a+"="+escape(c)+";domain="+d.sessionDomain;
document.cookie=b
},GetMuidCookie:function()
{
var b=Wol.ContentInstrumentation.Logging,
a=null;
try
{
var d="MUID";
a=b._getCookieValue(d)
}
catch(c)
{
b._logError("GetMuidCookie Error:"+c)
}
return a
},LogCustomEventsBatch:function(i,e)
{
var a=Wol.ContentInstrumentation.Logging;
if(a.initLogging==true)
try
{
if(i==null||i=="")
throw Error.argument("eventName","Parameter 'eventName' must not be null or empty");
if(e==null||typeof e!="object")
throw Error.argument("eventBatch","Parameter 'eventBatch' must not be a non-null array of json objects");
if(typeof Wol.Mscom=="undefined")
throw new Error("WEDCS scripts have not been successfully loaded");
e=jQuery.makeArray(e);
var o=1800,
m=Wol.Mscom.GetBaseSrcString()+Wol.Mscom.customEventParam,
l="&"+a._getAnnotationName(i)+"=",
k="%1E",
n="%1D",
j="";
jQuery.each(a._getAnnotatedMetatags(),function(b,a)
{
j+=a.shortname+"="+a.value+n
});
var h="",
d=Wol.Mscom.Encode(j);
while(e.length>0)
{
var c=e.pop();
if(typeof c==="object")
{
var b={biDataObject:null,element:null};
b.element=a._getDomTarget([c.element,c]);
b.biDataObject=typeof c.biDataObject==="object"?c.biDataObject:c!==b.element?c:null;
var p=a._getBiDataMap(null,b.biDataObject,b.element),
q="";
for(var f in p.Values)
{
var s=f.indexOf(a.tagPrefix)===0?f.substr(a.tagPrefix.length+1):f;
q+=f+"="+p.Values[f]+n
}
var g=Wol.Mscom.Encode(q);
if(o<g.length)
a.LogCustomBI(i,b.biDataObject,b.element);
else
if(o>=l.length+d.length+g.length+k.length)
d+=k+g;
else
{
h=m+l+d;
a.batchBeacons.push(h);
d=Wol.Mscom.Encode(j)+k+g
}
}
}
if(d!=="")
{
h=m+l+d;
a.batchBeacons.push(h)
}
a._flushQueuedEvents()
}
catch(r)
{
a._logError(r)
}
},LogCustomBI:function(b,f,g)
{
var a=Wol.ContentInstrumentation.Logging;
if(a.initLogging==true)
try
{
if(b==null||b=="")
throw Error.argument("eventName","Parameter 'eventName' must not be null or empty");
if(typeof Wol.Mscom=="undefined"||typeof Wol.Mscom.CustomEvent=="undefined")
throw new Error("WEDCS scripts have not been successfully loaded - or Wol.Mscom.CustomEvent function is not correctly defined");
var e=a._getDomTarget([g,this]),
c=a._getBiDataMap(b,f,e);
c.Push(a.customEventField,b);
a.customEventMaps.push(c);
a._flushQueuedEvents()
}
catch(d)
{
a._logError(d)
}
},_getBiDataMap:function(g,c,d)
{
var b=Wol.ContentInstrumentation.Logging,
a={Values:{},Push:function(e,c)
{
if(c!=null&&typeof c!="undefined"&&c!="")
{
var d=b._getAnnotationName(e,g);
if(!(d in a.Values))
a.Values[d]=c.toString()
}
},PushCaller:function()
{
var b=this;
a.Push(b.name,b.value)
},ToArray:function()
{
var d=[];
for(var c in a.Values)
{
var b=a.Values[c];
typeof b=="string"&&
d.push(c,b)
}
return d
}};
c!=null&&
jQuery.each(c,a.Push);
if(d!=null)
{
var e=jQuery(d),
h=e.map(b._getAnnotatedData),
f=e.parents().map(b._getAnnotatedData);
jQuery.each(h,a.PushCaller);
jQuery.each(f,a.PushCaller)
}
return a
},LogImpressions:function(g)
{
var a=Wol.ContentInstrumentation.Logging;
try
{
var c=a._getDomTarget([g,this]);
if(c==null)
throw Error.argument("element","Parameter 'element' can only be null if 'this' is a DOM element");
var f=a._getAnnotatedComponents(c),
b=jQuery(f).filter(a.impressionFilter);
if(b.length>0)
if(a.batchImpressions===true)
{
var e=b.map(function()
{
return {biDataObject:null,element:this}
});
a.LogCustomEventsBatch("Impression",e)
}
else
a._queueImpressionsEvents(b)
}
catch(d)
{
a._logError(d)
}
},SetEnvironment:function(b,c)
{
var a=Wol.ContentInstrumentation.Logging;
if(b!="")
a.envDomain=b;
if(c==true)
{
a.enableMuidCheck=true;
a._initMuid()
}
},SetTagConfig:function(e,c,d,b)
{
var a=Wol.ContentInstrumentation.Logging;
a.tagPrefix=e;
a.requestIdTag=c;
a.impressionFilter=b;
a.coreTags=jQuery.map(d,function(b)
{
return a.tagPrefix+"."+b
});
a._initRegex()
},SetClientConfig:function(a)
{
var b=Wol.ContentInstrumentation.Logging;
if(a==null||a=="")
a="ClientBISettings.js";
if(a.indexOf(":")<0&&b.scriptPath!=null)
a=b.scriptPath+a;
b.clientBiSettingsUri=a
},Init:function(b)
{
var a=Wol.ContentInstrumentation.Logging;
if(typeof b==="undefined")
b=true;
if(a.initLogging!=false)
a.initLogging=a._getLogEnabledHHSetting()&&a._getLogEnabledMetatagValue();
if(a.initLogging)
{
if(a.envDomain==null||a.envDomain=="")
throw Error.argument("env","WEDCS Environment has not properly initialized");
if(typeof Wol.Mscom=="undefined")
throw new Error("WEDCS protocol scripts have not been successfully loaded or correctly defined");
a.viewDataIsReady=a.enableMuidCheck===false||a.GetMuidCookie()!==null;
var d=false,
c=function()
{
if(d&&a.scriptIsReady)
if(typeof Wol.ContentInstrumentation.BiSettings!="undefined")
if(b)
a.FirePageViewEvent();
else
a._flushQueuedEvents()
};
typeof Ms!="undefined"&&typeof Ms.Wol!="undefined"&&typeof Ms.Wol.BIPreloadDatabag!="undefined"&&
a.AnnotateManyMetaTag(Ms.Wol.BIPreloadDatabag);
var e=new Date;
e.setMinutes(0,0,0);
var f=a.clientBiSettingsUri+"?t="+e.getTime().toString();
jQuery.ajax({url:f,dataType:"script",cache:true,success:function()
{
if(typeof Wol.ContentInstrumentation.BiSettings!="undefined")
{
var b=Wol.ContentInstrumentation.BiSettings;
b.get=function(a,c)
{
return typeof b[a]==="undefined"?c:b[a]
};
a.initLogging=b.get("BILog",a.initLogging);
a.batchImpressions=b.get("BatchImpressions",a.batchImpressions);
a.impressionFilter=b.get("ImpressionFilter",a.impressionFilter);
a.maxPartitionKey=b.get("MaxPartitionKey",a.maxPartitionKey);
a.sessionDomain=b.get("SessionDomain",a.sessionDomain);
a.httpScheme=b.get("BIScheme",a.httpScheme);
if(a.httpScheme==="auto")
a.httpScheme=window.location.protocol;
a.env=a.httpScheme+"//"+a.envDomain;
var d=Wol.Mscom;
d.tagPrefix=(a.tagPrefix+".").toLowerCase();
d.wedcsUriBase=a.env;
d.varClickTracking=b.ClickTracking||true;
var e=function()
{
d.InitRoute(b.ControlCode,b.RoutingCode,a._getPartitionKey());
a.scriptIsReady=true;
c()
},
f=a.GetSessionID();
if(f&&f.length>0)
e();
else
{
d.InitRoute(b.ControlCode,b.RoutingCode,"I");
var g=d.GetBaseSrcString()+d.customEventParam+"&MS.Init=1";
d.SendBeacon(g,e)
}
}
}});
jQuery(document).ready(function()
{
d=true;
c()
})
}
},InitRIO:function(g,r,l,k,h,p)
{
var a=Wol.ContentInstrumentation.Logging;
if(a.initLogging)
try
{
var m=a._getAnnotationName(l),
j=a._getMetaTagSelector(m),
d=jQuery(j).attr("content"),
q=a._getAnnotationName(k),
n=a._getMetaTagSelector(q),
b=a._getAnnotationName(h),
c=jQuery(n).attr("content");
if(!c)
{
var i=a._findBySingleAttribute(document.body,b);
c=jQuery(i).first().attr(b)
}
if(d&&c)
{
a.rioEnv=window.location.protocol+"//"+g+"/RioTracking2.js";
jQuery.getScript(a.rioEnv,function()
{
if(typeof RioTracking!="undefined")
{
RioTracking.guestCellCode=d;
RioTracking.uniqueActionTagCode=c
}
else
a._logError(new Error("RIO tracking client script was downloaded, but not evaluated successfully - RioTracking namespace is not defined."))
});
var e=a._getAnnotationName(p),
o=a._findBySingleAttribute(document.body,e);
jQuery(o).click(function()
{
try
{
var f=jQuery(this),
h=f.attr(e),
d=f.attr(b);
if(!d)
d=f.parents().filter(function()
{
return typeof jQuery(this).attr(b)!="undefined"
}).first().attr(b);
if(!d)
d=c;
RioTracking.clickNoWait(h,d)
}
catch(g)
{
a._logError(g)
}
})
}
}
catch(f)
{
a._logError(f)
}
},FirePageViewEvent:function(d)
{
var a=Wol.ContentInstrumentation.Logging;
try
{
var e=function()
{
if(a.viewDataIsReady===true&&a.scriptIsReady===true)
{
if(d==null)
Wol.Mscom.Init();
else
a._logJsonPageViewEvent(d);
a.LogImpressions(document.body);
a._flushQueuedEvents()
}
};
if(a.enableMuidCheck===true&&a.viewDataIsReady===false&&a.muidFrameElement!==null)
{
var c=false,
b=function()
{
if(c!==true)
{
a.viewDataIsReady=true;
c=true;
e()
}
};
jQuery(a.muidFrameElement).load(b);
setTimeout(b,a.muidTimeout)
}
e()
}
catch(f)
{
a._logError("FirePageView Error: "+f)
}
},FireClickTrackingEvent:function(a)
{
var b=Wol.ContentInstrumentation.Logging;
if(b.initLogging==true)
try
{
if(a==null)
throw Error.argument("event","Parameter 'event' must not be null");
if(typeof Wol.Mscom=="undefined"||typeof Wol.Mscom.ProcessClick=="undefined")
throw new Error("WEDCS scripts have not been successfully loaded - or Wol.Mscom.ProcessClick function is not correctly defined");
Wol.Mscom.ProcessClick(a)
}
catch(c)
{
b._logError(c)
}
},_getPartitionKey:function()
{
var a=Wol.ContentInstrumentation.Logging;
if(a.maxPartitionKey&&a.maxPartitionKey!="")
{
var d=a._getRandPartition(a.maxPartitionKey),
c=d;
try
{
var b=a.GetSessionID();
if(b!=null&&b.length>=a.maxPartitionKey.length)
{
b=b.toLowerCase();
c=b.substring(0,a.maxPartitionKey.length);
c=c<=a.maxPartitionKey.toLowerCase()?c:d
}
}
catch(e)
{
a._logError("Error determining partition key: "+e)
}
return c
}
else
return ""
},_getRandPartition:function(b)
{
try
{
var a=parseInt(b,16);
return Math.floor(Math.random()*(a+1)).toString(16)
}
catch(c)
{
return Math.floor(Math.random()*(15+1)).toString(16)
}
},_getDomTarget:function(b)
{
var a=jQuery.grep(b,function(a)
{
return typeof a!="undefined"&&typeof a.nodeType!="undefined"&&a.nodeType==1
}).shift();
return a
},_getElementsUnder:function(b,a)
{
var c=jQuery(a);
return jQuery(b).filter(function()
{
return c.has(this).length>0
})
},_getAnnotationName:function(b,a)
{
var c=Wol.ContentInstrumentation.Logging;
if(a==null||typeof a!=" string")
a="";
else
a=a+".";
if(c._isBiAnnotation(b))
return b;
else
return c.tagPrefix+"."+a+b
},_getAnnotatedData:function()
{
var a=this,
b=Wol.ContentInstrumentation.Logging;
return jQuery.map(a.attributes,function(c)
{
var d;
if(b._isBiAnnotation(c.name)&&c.value!="")
d={domElement:a,name:c.name,shortname:c.name.substr(b.tagPrefix.length+1),value:c.value};
return d
})
},_getCookieValue:function(e)
{
for(var c=null,
d=document.cookie.split("; "),
b=0;b<d.length;b++)
{
var a=d[b].split("=");
if(a.length===2&&a[0]===e)
{
c=unescape(a[1]);
break
}
}
return c
},_initRegex:function()
{
var a=Wol.ContentInstrumentation.Logging;
a.tagRegex=new RegExp("^"+a.tagPrefix,"i");
a.coreTagsRegex=new RegExp(a._getCoreTagsRegex(),"i")
},_initMuid:function()
{
var a=Wol.ContentInstrumentation.Logging;
try
{
if(a.GetMuidCookie()===null)
{
var c=document.createElement("iframe"),
b=jQuery(c);
b.attr("id","_msnFrame");
b.attr("src","http://analytics.microsoft.com/Sync.html");
b.attr("style","z-index:-1;height:1px;width:1px;display:none;visibility:hidden;");
a.muidFrameElement=c;
document.body.appendChild(a.muidFrameElement)
}
}
catch(d)
{
a._logError("InitMuid Error: "+d)
}
},_isBiAnnotation:function(b)
{
var a=Wol.ContentInstrumentation.Logging.tagRegex;
if(a!=null)
return a.test(b);
else
return false
},_getComponentSelectors:function()
{
var a=Wol.ContentInstrumentation.Logging;
return a._getHasAttributeSelectors(a.coreTags)
},_getCoreTagsRegex:function()
{
var b=Wol.ContentInstrumentation.Logging,
a=b.coreTags;
if(typeof a=="string")
a=[a];
return "^("+jQuery.map(a,function(a)
{
return b._escapeAttributeName(a)
}).join("|")+")$"
},_getHasAttributeSelectors:function(a)
{
var b=Wol.ContentInstrumentation.Logging;
if(typeof a=="string")
a=[a];
return jQuery.map(a,function(a)
{
return "["+b._escapeAttributeName(a)+"]"
}).join(", ")
},_getAnnotatedComponents:function(c)
{
var a=Wol.ContentInstrumentation.Logging,
d=a.isBiSelectorSupported?a._getComponentSelectors():"*",
b=a.isBiSelectorSupported?null:a.coreTagsRegex;
return a._findChildrenByAttributes(c,d,b)
},_findBySingleAttribute:function(d,b)
{
var a=Wol.ContentInstrumentation.Logging,
e=a.isBiSelectorSupported?a._getHasAttributeSelectors(b):"*",
c=a.isBiSelectorSupported?null:new RegExp("^"+a._escapeAttributeName(b)+"$");
return a._findChildrenByAttributes(d,e,c)
},_findChildrenByAttributes:function(c,d,b)
{
var e=Wol.ContentInstrumentation.Logging,
a=e._getElementsUnder(d,c);
if(b!=null)
a=jQuery.grep(a,function(a)
{
if(a.attributes&&a.attributes.length)
for(var c=0;c<a.attributes.length;c++)
if(b.test(a.attributes[c].name))
return true;
return false
});
return a
},_escapeAttributeName:function(a)
{
return a.replace(/\./g,"\\.")
},_getMetaTagSelector:function(a)
{
return "meta[name='"+a+"']"
},_getLogEnabledHHSetting:function()
{
try
{
return !(typeof window.external=="object"&&typeof window.external.NoImplicitFeedback=="boolean"&&window.external.NoImplicitFeedback==true)
}
catch(a)
{
return true
}
},_getLogEnabledMetatagValue:function()
{
var a=Wol.ContentInstrumentation.Logging;
return jQuery(a._getMetaTagSelector("BI.Log")).filter(function()
{
return !new Boolean(jQuery(this).attr("content"))
}).length==0
},_getAnnotatedMetatags:function()
{
var a=Wol.ContentInstrumentation.Logging;
return jQuery("meta[name^='"+a.tagPrefix+"']").map(function()
{
var c=this,
b=jQuery(c),
d=b.attr("name"),
f=b.attr("content"),
e={domElement:c,name:d,shortname:d.substr(a.tagPrefix.length+1),value:f};
return e
})
},_setAnnotatedMetatag:function(c,d)
{
var e=Wol.ContentInstrumentation.Logging,
b=e._getMetaTagSelector(c);
if(jQuery(b).length==0)
{
var a=document.createElement("meta");
a.setAttribute("name",c);
a.setAttribute("content",d);
jQuery("head").get(0).appendChild(a)
}
else
jQuery(b).get(0).attr("content",d)
},_flushQueuedEvents:function()
{
var a=Wol.ContentInstrumentation.Logging;
if(a.scriptIsReady===true)
{
for(var b=0;b<a.batchBeacons.length;b++)
Wol.Mscom.SendBeacon(a.batchBeacons[b]);
a.batchBeacons=[];
for(var b=0;b<a.customEventMaps.length;b++)
{
var c=a.customEventMaps[b];
a._logToWedcs(c.ToArray())
}
a.customEventMaps=[]
}
},_queueImpressionsEvents:function(a)
{
var b=Wol.ContentInstrumentation.Logging;
b._fireDelayedImpression(a,0)
},_fireDelayedImpression:function(a,c)
{
var b=Wol.ContentInstrumentation.Logging;
try
{
var g=b.impressionDelay;
if(a&&a.length>c)
{
var f=a[c];
b.LogCustomBI("Impression",null,f);
var e=function()
{
b._fireDelayedImpression(a,++c)
};
setTimeout(e,g)
}
}
catch(d)
{
b._logError(d)
}
},_initCurrentScriptPath:function()
{
var b=Wol.ContentInstrumentation.Logging;
if(b.scriptPath==null)
{
try
{
var e=document.getElementsByTagName("script"),
c=e[e.length-1].src,
a=c.lastIndexOf("/");
a=a<0?c.lastIndexOf("\\"):a;
a=a<0?0:a;
b.scriptPath=c.substring(0,a)+"/"
}
catch(d)
{
b.scriptPath="/";
b._logError(d)
}
b.clientBiSettingsUri=b.scriptPath+b.clientBiSettingsUri
}
},_logToWedcs:function(a)
{
Wol.Mscom.CustomEvent.apply(this,a)
},_logJsonPageViewEvent:function(f)
{
var b=Wol.ContentInstrumentation.Logging;
if(b.initLogging==true)
{
var a=Wol.Mscom;
a.HandleSession();
a.setEvents();
var d=a.GetBaseSrcString(),
c=b._getBiDataMap(null,f,null);
jQuery.each(b._getAnnotatedMetatags(),function(b,a)
{
c.Push(a.shortname,a.value)
});
var e=a.EncodeCustomEventStr(c.ToArray()),
g=a.EnsureBeaconSrcLength(d+e);
a.SendBeacon(g)
}
},_logError:function(a)
{
if(a!=null&&typeof console!="undefined"&&typeof console.log!="undefined")
{
var b=a.toString();
console.log(b)
}
}};
Wol.ContentInstrumentation.Logging._initCurrentScriptPath();
Wol.Mscom={fl:0,sessionId:"",sessionDuration:1.8e6,sessionCookieName:"MC0",sessionGuidCookieName:"MS0",cookieDisabled:0,metaTags:"",customTags:"",pvInfo:[],clickInfo:"",qs:"",currTimeTicks:0,currTimeAsString:"",imgArray:[],imgArrayIndex:0,customInfo:"",tz:420,slC:"",flC:"",cntC:"",bsC:"",clickedElements:["A","IMG","AREA","INPUT"],pKey:"",routeCode:"",ctrlCode:"",routeParams:"",customEventParam:"&cot=5",tagPrefix:"ms.",clickPrefix:"Click.",debugOn:false,wedcsUriBase:"http://__REPLACE_FQDN__",varClickTracking:0,CustomEvent:function()
{
var a=Wol.Mscom;
try
{
a.HandleSession();
a.customInfo+=a.EncodeCustomEventStr(arguments);
a.customInfo!=""&&
a.Beacon()
}
catch(b)
{
a.debug("CustomEvent:"+b)
}
},EncodeCustomEventStr:function(b)
{
for(var c=Wol.Mscom,
d="",
f=b.length,
a=0;a<f;a=a+2)
{
var e=b[a].toString().toLowerCase();
if(e.indexOf(c.tagPrefix)==0)
d+="&"+c.Encode(e)+"="+(b[a+1]==undefined?"":c.Encode(b[a+1].toString()))
}
return d
},SetPVInfo:function()
{
var a=Wol.Mscom;
a.SetTime();
a.pvInfo.push("tz="+a.tz/-60);
window.varSegmentation!=undefined&&varSegmentation==1&&
a.pvInfo.push("&cs=1");
a.cookieDisabled==1&&
a.pvInfo.push("&cd=1");
a.pvInfo.push("&ti="+a.Encode(document.title));
a.GetSilverLightInfo();
a.GetFlahsInfo();
a.GetCTypeHpInfo();
var b=a.Encode(document.referrer);
b!=null&&b!=""&&
a.pvInfo.push("&r="+b);
a.pvInfo.push("&ts="+a.currTimeAsString);
typeof screen=="object"&&
a.pvInfo.push("&sr="+screen.width+"x"+screen.height);
a.GetBrowserSize()
},GetBrowserSize:function()
{
var a=Wol.Mscom;
if(a.bsC!="")
{
a.pvInfo.push(a.bsC);
return
}
if(document.body.clientWidth!=undefined)
a.bsC="&bs="+document.body.clientWidth+"x"+document.body.clientHeight;
else
if(document.documentElement&&document.documentElement.clientWidth!=undefined)
a.bsC="&bs="+document.documentElement.clientWidth+"x"+document.documentElement.clientHeight;
else
if(window.innerWidth!=undefined)
a.bsC="&bs="+window.innerWidth+"x"+window.innerHeight;
a.bsC!=""&&
a.pvInfo.push(a.bsC)
},GetCTypeHpInfo:function()
{
var a=Wol.Mscom;
try
{
if(a.cntC!="")
{
a.pvInfo.push(a.cntC);
return
}
if(document.body&&document.body.addBehavior)
{
document.body.addBehavior("#default#clientCaps");
if(document.body.connectionType)
a.cntC+="&cnt="+document.body.connectionType;
document.body.addBehavior("#default#homePage");
a.cntC+=document.body.isHomePage(location.href)?"&hp=1":""
}
a.cntC!=""&&
a.pvInfo.push(a.cntC)
}
catch(b)
{
a.debug("GetCTypeHpInfo:"+b)
}
},GetFlahsInfo:function()
{
var a=Wol.Mscom;
if(a.flC!="")
{
a.pvInfo.push(a.flC);
return
}
try
{
var d=(new Date).getYear()-1992;
if(navigator.userAgent.indexOf("MSIE")!=-1)
for(var b=d;b>0;b--)
{
var f=new ActiveXObject("ShockwaveFlash.ShockwaveFlash."+b);
a.flC+="&fi=1";
a.flC+="&fv="+b+".0";
break
}
else
if(navigator.plugins["Shockwave Flash"])
{
a.flC+="&fi=1";
var e=navigator.plugins["Shockwave Flash"];
a.flC+="&fv="+e.description.split(" ")[2]
}
a.pvInfo.push(a.flC)
}
catch(c)
{
a.debug("GetFlahsInfo:"+c)
}
},GetSilverLightInfo:function()
{
var a=Wol.Mscom;
if(a.slC!="")
{
a.pvInfo.push(a.slC);
return
}
if(window.Silverlight!=undefined)
a.slC+="&se=1";
try
{
if(navigator.userAgent.indexOf("MSIE")!=-1)
{
var d=new ActiveXObject("AgControl.AgControl");
if(d)
{
a.slC+="&si=1";
a.slC+="&sv="+a.GetSlvVersion(d)
}
}
else
if(navigator.plugins["Silverlight Plug-In"])
{
var f=navigator.plugins["Silverlight Plug-In"];
a.slC+="&si=1";
var b=f.description;
if(b&&b!=undefined)
{
var c=b.split(".");
b=c[0]+"."+c[1];
a.slC+="&sv="+b
}
}
}
catch(e)
{
a.debug("GetSilverLightInfo:"+e)
}
a.pvInfo.push(a.slC)
},GetSlvVersion:function(d)
{
for(var f=Wol.Mscom,
a="",
e=(new Date).getYear()-2004,
b=e;b>0;b--)
for(var c=9;c>=0;c--)
{
a=b+"."+c;
if(d.IsVersionSupported(a))
return a
}
return a
},HandleSession:function()
{
var a=Wol.Mscom;
a.SetTime();
var c="",
b=document.cookie.indexOf(a.sessionCookieName+"=");
if(b==-1)
{
a.sessionId=a.currTimeAsString;
if(a.cookieDisabled==1)
return;
c=a.sessionCookieName+"="+a.sessionId
}
else
{
var d=b+a.sessionCookieName.length+1,
e=document.cookie.length;
c=a.sessionCookieName+"="+document.cookie.substring(d,e)
}
document.cookie=c;
b=document.cookie.indexOf(a.sessionCookieName+"=");
if(b==-1)
a.cookieDisabled=1
},SetTime:function()
{
var a=Wol.Mscom,
b=new Date;
a.tz=b.getTimezoneOffset();
a.currTimeTicks=Math.max(a.currTimeTicks+1,b.getTime());
a.currTimeAsString=a.currTimeTicks.toString()
},debug:function()
{
var a=Wol.Mscom;
a.debugOn===true&&arguments!=null&&arguments.length>0&&typeof console!="undefined"&&typeof console.log!="undefined"&&
console.log(arguments[0])
},ProcessClick:function(l)
{
var b=Wol.Mscom;
try
{
b.HandleSession();
var f=l||window.event,
a;
if(f)
{
a=f.srcElement||f.target;
while(a.tagName&&b.IsInList(a.tagName)==false)
a=a.parentElement||a.parentNode
}
if(a&&a.tagName)
{
switch(a.tagName)
{
case "A":
var g;
if(document.all)
g=a.innerText||a.innerHTML;
else
g=a.text||a.innerHTML;
b.clickInfo="&cot=1&cn="+b.Encode(g)+"&cid="+b.GetId(a)+"&ct="+(a.href?b.Encode(a.href):"");
b.customTags=b.ReadClickTags(a);
break;
case "IMG":
b.clickInfo="&cot=2&cn="+(a.alt?b.Encode(a.alt):"")+"&cid="+b.GetId(a)+"&ct="+b.GetImageHREF(a);
b.customTags=b.ReadClickTags(a);
break;
case "AREA":
b.clickInfo="&cot=3&cn="+(a.alt?b.Encode(a.alt):"")+"&cid="+b.GetId(a)+"&ct="+(a.href?b.Encode(a.href):"");
b.customTags=b.ReadClickTags(a);
break;
case "INPUT":
var c=a.type||"",
h="";
if(c&&(c=="button"||c=="reset"||c=="submit"||c=="image")||c=="text"&&(f.which||f.keyCode)==13)
{
var j=a.value||a.name||a.alt||a.id;
b.clickInfo="&cot=4&cn="+(j?b.Encode(j):"")+"&cid="+b.GetId(a)+"&ct=";
if(a.form)
{
b.clickInfo+=b.Encode(a.form.action)||b.Encode(window.location.pathname);
for(var e=a.form.elements,
i=1,
d=0;d<e.length;d++)
{
var k=e[d].type;
if(k=="text")
{
h+="&t"+i+"="+b.Encode(e[d].name||e[d].id)+"&v"+i+"="+b.Encode(e[d].value);
i++
}
}
}
else
b.clickInfo+=b.Encode(window.location.pathname);
if(h!="")
b.clickInfo+=h;
b.customTags=b.ReadClickTags(a)
}
break;
default:
b.clickInfo=""
}
b.clickInfo!=""&&
b.Beacon()
}
}
catch(a)
{
b.debug("ProcessClick:"+a)
}
},Beacon:function()
{
var a=Wol.Mscom;
try
{
var d=a.GetFullSrcParts(),
b=d.join("");
b=a.EnsureBeaconSrcLength(b);
a.SendBeacon(b)
}
catch(c)
{
a.debug("Beacon:"+c)
}
},EnsureBeaconSrcLength:function(a)
{
if(a.length>2048)
{
a=a.substring(0,2042);
if(a.lastIndexOf("&")>=0)
a=a.substring(0,a.lastIndexOf("&"));
a=a+"&tr=1"
}
return a
},SendBeacon:function(d,b)
{
var a=Wol.Mscom;
try
{
if(document.images)
{
var c=document.createElement("IMG");
if(b)
{
c.onload=b;
c.onerror=b
}
a.imgArray[a.imgArrayIndex]=c;
a.imgArray[a.imgArrayIndex].src=d;
a.imgArrayIndex++
}
else
{
document.write('<IMG ALT="" BORDER="0" NAME="bImg" WIDTH="1" HEIGHT="1" SRC="'+d+'"/>');
b&&
setTimeout(b,250)
}
}
catch(e)
{
a.debug("SendBeacon:"+e)
}
finally
{
a.clickInfo="";
a.customInfo="";
a.customTags=""
}
},GetBaseSrcParts:function()
{
var a=Wol.Mscom;
try
{
var b=[];
b.push(a.wedcsUriBase+"/trans_pixel.aspx?");
a.routeParams!=""&&
b.push(a.routeParams);
a.pvInfo.length=0;
a.SetPVInfo();
b.push(a.pvInfo.join(""));
return b
}
catch(c)
{
a.debug("GetBaseSrcParts:"+c)
}
},GetFullSrcParts:function()
{
var a=Wol.Mscom;
try
{
var b=a.GetBaseSrcParts();
a.clickInfo!=""&&
b.push(a.clickInfo);
a.InitMeta();
a.metaTags!=""&&
b.push(a.metaTags);
a.customTags!=""&&
b.push(a.customTags);
if(a.customInfo!="")
{
b.push(a.customEventParam);
b.push(a.customInfo)
}
return b
}
catch(c)
{
a.debug("GetFullSrcParts:"+c)
}
},GetBaseSrcString:function()
{
var a=Wol.Mscom;
return a.GetBaseSrcParts().join("")
},GetId:function(a)
{
var b=Wol.Mscom;
if(a)
{
if(a.id==undefined)
return "";
return b.Encode(a.id)
}
return ""
},GetImageHREF:function(a)
{
var c=Wol.Mscom,
b=a;
if(a)
{
a=a.parentElement||a.parentNode;
if(a&&a.tagName=="A")
return a.href?c.Encode(a.href):"";
if(b&&b.src)
return c.Encode(b.src)
}
return ""
},ReadAllTags:function(a)
{
var c=Wol.Mscom,
b=[];
while(a&&a!="undefined")
{
b.push(c.ReadElementTags(a));
a=a.parentElement||a.parentNode
}
return b.join("")
},ReadClickTags:function(c)
{
var b=Wol.Mscom,
d=[];
d.push(b.ReadElementTags(c,b.clickPrefix));
var a=c.parentElement||c.parentNode;
if(typeof a!="undefined"&&a!==null)
var e=b.ReadAllTags(a);
return d.join("")+e
},ReadElementTags:function(b,g)
{
var a=Wol.Mscom,
f="";
if(b)
for(var d in b.attributes)
if(d!=undefined)
if(b.attributes[d]!=null&&b.attributes[d]!=undefined)
{
var c=b.attributes[d].name;
if(c!=null&&c!=undefined)
{
var e=c.toLowerCase();
if(e.indexOf(a.tagPrefix)==0)
{
if(g!=null)
c=a.tagPrefix+g+c.substring(a.tagPrefix.length);
f+="&"+a.Encode(c)+"="+a.Encode(b.attributes[d].value)
}
else
if(e.indexOf("s.")==0||e.indexOf("wt.")==0||e.indexOf("dcs.")==0||e.indexOf("dcsext.")==0)
f+="&"+a.Encode(c)+"="+a.Encode(b.attributes[d].value)
}
}
return f
},IsInList:function(b)
{
var a=Wol.Mscom;
for(var c in a.clickedElements)
if(a.clickedElements[c]==b.toUpperCase())
return true;
return false
},setEvents:function()
{
var a=Wol.Mscom;
a.varClickTracking!=undefined&&a.varClickTracking==1&&
jQuery(document.body).click(a.ProcessClick)
},InitMeta:function()
{
var a=Wol.Mscom;
a.metaTags=a.GetMetaTagsStr()
},GetMetaTagsStr:function()
{
var d=Wol.Mscom,
a;
if(document.all)
a=document.all.tags("meta");
else
if(document.documentElement)
a=document.getElementsByTagName("meta");
var f="";
if(typeof a!="undefined")
for(var e=0;e<a.length;e++)
{
var c=a.item(e);
if(c.name)
{
var b=c.name.toLowerCase();
if(b.indexOf(d.tagPrefix)==0||b.indexOf("s.")==0||b.indexOf("wt.")==0||b.indexOf("dcs.")==0||b.indexOf("dcsext.")==0)
f+="&"+d.Encode(c.name)+"="+d.Encode(c.content)
}
}
return f
},Init:function()
{
var a=Wol.Mscom;
try
{
a.HandleSession();
a.setEvents();
a.Beacon()
}
catch(b)
{
a.debug("Mscom - Init:"+b)
}
},InitRoute:function(c,d,b)
{
var a=Wol.Mscom;
a.ctrlCode=c;
a.routeCode=d;
a.pKey=b;
if(a.pKey&&a.pKey!==""&&a.routeCode&&a.routeCode!==""&&a.ctrlCode&&a.ctrlCode!=="")
a.routeParams="pKey="+a.pKey+"&route="+a.routeCode+"&ctrl="+a.ctrlCode+"&"
},Encode:function(a)
{
return typeof encodeURIComponent=="function"?encodeURIComponent(a):escape(a)
}};
Type.registerNamespace("Wol.Logging");
Wol.Logging.InternalVars=function()
{
this._isEnabled=false;
this._isClickThroughEnabled=true;
this._isLoggerReady=false;
this._shouldIgnoreError=true;
this._loggerReadyEvent=new Wol.Util.Event("loggerReadyEvent");
this._requiredLoggerTemplate=null;
this._commonLoggerTemplate=null
};
var WolLoggingVars=new Wol.Logging.InternalVars;
Wol.Logging={isEnabled:function()
{
return WolLoggingVars._isEnabled
},isClickThroughEnabled:function()
{
return WolLoggingVars._isClickThroughEnabled
},shouldIgnoreError:function()
{
return WolLoggingVars._shouldIgnoreError
},isLoggerReady:function()
{
return WolLoggingVars._isLoggerReady
},loggerReadyEvent:function()
{
return WolLoggingVars._loggerReadyEvent
},requiredLoggerTemplate:function()
{
return WolLoggingVars._requiredLoggerTemplate
},commonLoggerTemplate:function()
{
return WolLoggingVars._commonLoggerTemplate
},getTargetUrlElement:function(a)
{
if(arguments.length!=1)
throw Error.argument();
if(a==undefined||a==null)
return null;
if(a.href!=null&&a.href!=undefined&&a.tagName!=null&&a.tagName!=undefined&&a.tagName.toUpperCase()=="A")
return a;
else
return this.getTargetUrlElement(a.parentElement||a.parentNode)
},getTargetUrl:function(b)
{
var a=this.getTargetUrlElement(b);
if(a==undefined||a==null)
return null;
else
return a.href
},_getKeyValue:function(d,g,f,e)
{
if(d&&g&&e&&f)
{
var a=d.split(e);
if(a&&a.length>0)
for(i=0;i<a.length;i++)
{
var b=a[i].replace(/^\s+|\s+$\g/,""),
c=g+f;
if(b.indexOf(c)==0&&b.length>c.length)
return b.substring(c.length)
}
}
return null
},_getCookie:function(a)
{
var b=document.cookie;
if(b&&a)
return Wol.Logging._getKeyValue(b,a,"=",";");
return null
},_getUserIdCookie:function()
{
var a=Wol.Logging._getCookie("A");
if(a&&a!="")
return Wol.Logging._getKeyValue(a,"I","=","&");
return null
},getVisitor:function()
{
try
{
var a=Wol.Logging._getUserIdCookie();
if(a!=undefined&&a!=null)
a=a.replace(/-/g,"");
return a
}
catch(b)
{
if(WolLoggingVars._shouldIgnoreError)
return null;
else
throw b
}
},_getEncodedLengthForScalar:function(a)
{
if(a==undefined||a==null)
return 0;
else
if(Wol.Util.TypeCheck.isOfType(a,Wol.Util.TypeCheck.StringType|Wol.Util.TypeCheck.NumberType|Wol.Util.TypeCheck.BooleanType))
return escape(a.toString()).length;
else
throw Error.argument("value","_getEncodedLengthForScalar value parameter must be string, int or boolean type!")
},_getEncodedLength:function(a)
{
Sys.Debug.assert(arguments.length==1);
if(a instanceof Array)
{
for(var c=0,
b=0;b<a.length;b++)
c+=Wol.Logging._getEncodedLengthForScalar(a[b])+1;
return c
}
else
return Wol.Logging._getEncodedLengthForScalar(a)
},normalizeToLowerCaseString:function(a)
{
if(a!=null&&a!=undefined)
return a.toString().toLowerCase();
else
return a
}};
Wol.Logging.WhenReady={register:function(a)
{
if(Wol.Logging.isLoggerReady())
a();
else
Wol.Logging.loggerReadyEvent().addHandler(a)
}};
Wol.Logging.Const={MaxAllowedUrlLength:2083,BaseUrlLength:380,LinkClickOverhead:600,PropOverhead:5};
Wol.Logging.Const.FieldName={Channel:"channel",PageName:"pageName",PageUrl:"pageURL",Referrer:"referrer",Site:"prop1",RequestedCulture:"prop2",Title:"prop3",ContentPurpose:"prop4",Provider:"prop5",ContentSet:"prop6",TopicArea:"prop7",ContentType:"prop8",TabAssetUsageId:"prop9",SilverlightVersion:"prop10",FlashVersion:"prop11",TargetUrl:"prop12",InteractionId:"prop13",Application:"prop14",BwcSku:"prop15",OSSku:"prop16",TabControlIds:"prop17",TabAssetUsageIds:"prop18",FailedAssetId:"prop19",StatusCode:"prop20",Lcid:"prop21",TopLevelAssetRev:"prop28",ChildAssetIds:"prop29",TopLevelAssetId:"prop30",SearchResultCount:"prop31",SearchTerm1:"prop32",SearchTerm2:"prop33",SearchTerm3:"prop34",SearchTerm4:"prop35",SearchResultClickPosition:"prop36",IsBestBetPresent:"prop37",IsClickedBestBet:"prop38",QueryId:"prop39",FeedbackId:"prop49",Rating:"prop50",VerboseFeedback1:"eVar1",VerboseFeedback2:"eVar2",VerboseFeedback3:"eVar3",VerboseFeedback4:"eVar4",VerboseFeedback5:"eVar5",VerboseFeedback6:"eVar6",VerboseFeedback7:"eVar7",VerboseFeedback8:"eVar8",VerboseFeedback9:"eVar9",VerboseFeedback10:"eVar10",VerboseFeedback11:"eVar11",VerboseFeedback12:"eVar12",VerboseFeedback13:"eVar13",VerboseFeedback14:"eVar14",VerboseFeedback15:"eVar15",VerboseFeedback16:"eVar16"};
Wol.Logging.Const.InteractionTypeId={Click:0,PageLoad:1,PrimaryNavBar:2,SecondaryNavBar:3,Footer:4,PerformSearch:8,SearchResultClick:9,SearchSiteEscalation:10,ChangeSearchOption:11,SearchSpelling:12,TabClick:13,WauBuyButtonClick:15,WauPageLoad:16,SuperHeroClick:17,HeroMouseOver:18,HeroVideoStart:19,HeroVideoReachEnd:20,HeroVideoStop:21,HeroPageLoad:22,SubHeroClick:23,DownLevelHeroClick:24,TocBookNodeClick:25,Rating:26,VerboseFeedback:27,TertiaryNavBar:28,Header:29,TocTopicClick:30,SilverlightComponentLoaded:31,SilverlightPluginAvailable:32};
Wol.Logging.Const.FieldMaxLength={Channel:100,PageName:100,PageUrl:256,Referrer:255,Prop:100,EVar:100};
Wol.Logging.Const.PropNArray={Length:50,MaxLength:Wol.Logging.Const.FieldMaxLength.Prop,NamePrefix:"prop",NameStartingAt:1};
Wol.Logging.Const.EVarNArray={Length:50,MaxLength:Wol.Logging.Const.FieldMaxLength.EVar,NamePrefix:"eVar",NameStartingAt:1};
Wol.Logging.Const.ElementAttrib={InteractionTypeId:"logginginteractiontypeid",InteractionTypeIdSep:":",AdditionalProp:"loggingAdditionalProp",AdditionalPropSep:"_"};
Wol.Logging.Const.OmnitureWTMappings=[[Wol.Logging.Const.FieldName.Title,"WT.ti"]];
Wol.Logging.LogTypeEnum=function()
{
throw Error.notImplemented()
};
Wol.Logging.LogTypeEnum.prototype={ClickThrough:0,PageLoad:1};
Wol.Logging.LogTypeEnum.registerEnum("Wol.Logging.LogTypeEnum");
Wol.Logging.PropTypeEnum=function()
{
throw Error.notImplemented()
};
Wol.Logging.PropTypeEnum.prototype={Scalar:1,List:2,TableName:3,TableValue:4};
Wol.Logging.PropTypeEnum.registerEnum("Wol.Logging.PropTypeEnum");
Wol.Logging.ResultId=function()
{
throw Error.notImplemented()
};
Wol.Logging.ResultId.prototype={Success:0,GeneralFailure:1,LengthTooLongFailure:2};
Wol.Logging.ResultId.registerEnum("Wol.Logging.ResultId");
Wol.Logging.Prop=function(c,b,a,e,d)
{
Sys.Debug.assert(arguments.length>=2&&arguments.length<=5);
Sys.Debug.assert(Wol.Util.TypeCheck.isString(c));
Sys.Debug.assert(Wol.Util.TypeCheck.isNumber(b));
a!=undefined&&Sys.Debug.assert(Wol.Util.TypeCheck.isBoolean(a));
this.name=c;
this.maxLength=b;
this.isCaseSensitive=a||false;
this.type=e||Wol.Logging.PropTypeEnum.Scalar;
this.keyPropName=d;
this.value=null;
this.encodedLength=0
};
Wol.Logging.Prop.prototype={checkForNonScalarType:function()
{
Sys.Debug.assert(this.type==Wol.Logging.PropTypeEnum.List||this.type==Wol.Logging.PropTypeEnum.TableName||this.type==Wol.Logging.PropTypeEnum.TableValue)
},clone:function()
{
Sys.Debug.assert(arguments.length==0);
var a=new Wol.Logging.Prop(this.name,this.maxLength,this.isCaseSensitive,this.type,this.keyPropName);
if(this.type==Wol.Logging.PropTypeEnum.Scalar||this.value==undefined||this.value==null)
a.value=this.value;
else
{
a.value=[];
for(var b=0;b<this.value.length;b++)
a.value[b]=this.value[b]
}
a.encodedLength=this.encodedLength;
return a
},setValue:function(a,b)
{
Sys.Debug.assert(arguments.length==1||arguments.length==2);
if(this.isCaseSensitive)
this.value=a;
else
if(this.type==Wol.Logging.PropTypeEnum.Scalar)
this.value=Wol.Logging.normalizeToLowerCaseString(a);
else
if(a==undefined||a==null)
this.value=a;
else
{
this.value=new Array(a.length);
for(var c=0;c<a.length;c++)
this.value[c]=Wol.Logging.normalizeToLowerCaseString(a[c])
}
if(b==undefined||b==null||b==0)
this.encodedLength=Wol.Logging._getEncodedLength(this.value);
else
{
Sys.Debug.assert(b>=0);
this.encodedLength=b
}
},getValue:function()
{
Sys.Debug.assert(arguments.length==0);
if(this.type==Wol.Logging.PropTypeEnum.Scalar)
return this.value;
else
if(this.value!=null&&this.value!=undefined)
return this.value.join(",");
else
return null
},append:function(b,a)
{
this.checkForNonScalarType();
Sys.Debug.assert(arguments.length>=1&&arguments.length<=2);
if(this.value==undefined||this.value==null)
{
this.value=[];
this.encodedLength=0
}
if(!this.isCaseSensitive)
b=Wol.Logging.normalizeToLowerCaseString(b);
if(a==undefined||a==null||a==0)
a=Wol.Logging._getEncodedLength(b);
else
Sys.Debug.assert(this.encodedLength>=0);
this.value.push(b);
this.encodedLength+=a+1
},setAt:function(b,a)
{
this.checkForNonScalarType();
Sys.Debug.assert(arguments.length==2);
Sys.Debug.assert(this.value.length>b);
if(!this.isCaseSensitive)
a=Wol.Logging.normalizeToLowerCaseString(a);
var d=Wol.Logging._getEncodedLength(this.value[b]),
c=Wol.Logging._getEncodedLength(a);
this.value[b]=a;
this.encodedLength+=c-d
},indexOf:function(b)
{
this.checkForNonScalarType();
Sys.Debug.assert(arguments.length==1);
if(this.value==null||this.value==undefined)
return -1;
if(!this.isCaseSensitive)
b=Wol.Logging.normalizeToLowerCaseString(b);
for(var a=0;a<this.value.length;a++)
if(b.toString()==this.value[a].toString())
return a;
return -1
}};
Wol.Logging.Prop.registerClass("Wol.Logging.Prop");
Wol.Logging.PropArray=function(d,c,e,b,a)
{
Sys.Debug.assert(arguments.length==4||arguments.length==5);
Sys.Debug.assert(Wol.Util.TypeCheck.isNumber(d));
Sys.Debug.assert(Wol.Util.TypeCheck.isNumber(c));
Sys.Debug.assert(Wol.Util.TypeCheck.isString(e));
Sys.Debug.assert(Wol.Util.TypeCheck.isNumber(b));
a!=undefined&&Sys.Debug.assert(Wol.Util.TypeCheck.isBoolean(a));
Sys.Debug.assert(d>0);
Sys.Debug.assert(c>0);
Sys.Debug.assert(b>=0);
this.arrayLength=d;
this.propMaxLength=c;
this.namePrefix=e;
this.startingNameAppendix=b;
this.data=[];
!a&&this.init()
};
Wol.Logging.PropArray.prototype={init:function()
{
Sys.Debug.assert(arguments.length==0);
for(var a=0,
b=this.startingNameAppendix;a<this.arrayLength;a++,b++)
{
var c=new Wol.Logging.Prop(this.namePrefix+b.toString(),this.propMaxLength);
this.data[a]=c
}
},clone:function()
{
Sys.Debug.assert(arguments.length==0);
for(var b=new Wol.Logging.PropArray(this.arrayLength,this.propMaxLength,this.namePrefix,this.startingNameAppendix,true),
a=0;a<this.data.length;a++)
b.data[a]=this.data[a].clone();
return b
},getAt:function(a)
{
Sys.Debug.assert(arguments.length==1);
if(Wol.Util.TypeCheck.isString(a))
a=Number.parseInvariant(a);
Sys.Debug.assert(Wol.Util.TypeCheck.isNumber(a));
Sys.Debug.assert(a>=this.startingNameAppendix&&a-this.startingNameAppendix<this.arrayLength);
return this.data[a-this.startingNameAppendix]
},findProp:function(a)
{
Sys.Debug.assert(arguments.length==1);
Sys.Debug.assert(Wol.Util.TypeCheck.isString(a));
if(a.indexOf(this.namePrefix)==0)
{
var b=a.substr(this.namePrefix.length);
return this.getAt(b)
}
return null
}};
Wol.Logging.PropArray.registerClass("Wol.Logging.PropArray");
Wol.Logging.LoggerTemplate=function(a)
{
try
{
if(arguments.length>1)
throw Error.argument();
var c=[Wol.Logging.Const.FieldName.Channel,Wol.Logging.Const.FieldName.PageName,Wol.Logging.Const.FieldName.PageUrl,Wol.Logging.Const.FieldName.Referrer],
h=["_props","_eVars"];
if(a!=undefined&&a!=null)
{
if(!(a instanceof Wol.Logging.LoggerTemplate))
throw Error.argumentType("template",typeof a,"Wol.Logging.LoggerTemplate");
this.set_omLogger(a.get_omLogger());
this.set_totalLength(a.get_totalLength());
this._maxEncodedLength=a._maxEncodedLength;
this._nonScalarProps=a._nonScalarProps;
Wol.Util.Tools.cloneProperties(a,this,c);
Wol.Util.Tools.cloneProperties(a,this,h)
}
else
{
this.set_totalLength(0);
this._maxEncodedLength=Wol.Logging.Const.MaxAllowedUrlLength-Wol.Logging.Const.BaseUrlLength;
this._nonScalarProps=[Wol.Logging.Const.FieldName.TabControlIds,Wol.Logging.Const.FieldName.TabAssetUsageIds,Wol.Logging.Const.FieldName.ChildAssetIds];
this[Wol.Logging.Const.FieldName.Channel]=new Wol.Logging.Prop(Wol.Logging.Const.FieldName.Channel,Wol.Logging.Const.FieldMaxLength.Channel);
this[Wol.Logging.Const.FieldName.PageName]=new Wol.Logging.Prop(Wol.Logging.Const.FieldName.PageName,Wol.Logging.Const.FieldMaxLength.PageName);
this[Wol.Logging.Const.FieldName.PageUrl]=new Wol.Logging.Prop(Wol.Logging.Const.FieldName.PageUrl,Wol.Logging.Const.FieldMaxLength.PageUrl);
this[Wol.Logging.Const.FieldName.Referrer]=new Wol.Logging.Prop(Wol.Logging.Const.FieldName.Referrer,Wol.Logging.Const.FieldMaxLength.Referrer);
this._props=new Wol.Logging.PropArray(Wol.Logging.Const.PropNArray.Length,Wol.Logging.Const.PropNArray.MaxLength,Wol.Logging.Const.PropNArray.NamePrefix,Wol.Logging.Const.PropNArray.NameStartingAt);
this._eVars=new Wol.Logging.PropArray(Wol.Logging.Const.EVarNArray.Length,Wol.Logging.Const.EVarNArray.MaxLength,Wol.Logging.Const.EVarNArray.NamePrefix,Wol.Logging.Const.EVarNArray.NameStartingAt);
this._initNonScalarProps()
}
this._allProps=[];
for(var b=0;b<c.length;b++)
this._allProps.push(this[c[b]]);
this._allProps=this._allProps.concat(this._props.data).concat(this._eVars.data);
for(var d=[Wol.Logging.Const.FieldName.Channel,Wol.Logging.Const.FieldName.PageName,Wol.Logging.Const.FieldName.PageUrl,Wol.Logging.Const.FieldName.Referrer,Wol.Logging.Const.FieldName.RequestedCulture,Wol.Logging.Const.FieldName.ContentPurpose,Wol.Logging.Const.FieldName.ContentSet,Wol.Logging.Const.FieldName.TopicArea,Wol.Logging.Const.FieldName.ContentType,Wol.Logging.Const.FieldName.TabAssetUsageId,Wol.Logging.Const.FieldName.TargetUrl,Wol.Logging.Const.FieldName.InteractionId,Wol.Logging.Const.FieldName.Application,Wol.Logging.Const.FieldName.BwcSku,Wol.Logging.Const.FieldName.OSSku,Wol.Logging.Const.FieldName.TabControlIds,Wol.Logging.Const.FieldName.TabAssetUsageIds,Wol.Logging.Const.FieldName.FailedAssetId,Wol.Logging.Const.FieldName.StatusCode,Wol.Logging.Const.FieldName.Lcid,Wol.Logging.Const.FieldName.IsBestBetPresent,Wol.Logging.Const.FieldName.IsClickedBestBet,Wol.Logging.Const.FieldName.QueryId,Wol.Logging.Const.FieldName.FeedbackId],
e={},
b=0;b<d.length;b++)
e[d[b]]=true;
for(var b=0;b<this._allProps.length;b++)
{
var f=this._allProps[b];
f.isCaseSensitive=e[f.name]==true?false:true
}
}
catch(g)
{
this.throwOrIgnore(g)
}
};
Wol.Logging.LoggerTemplate.prototype={get_omLogger:function()
{
return this._omLogger
},set_omLogger:function(a)
{
this._omLogger=a
},get_totalLength:function()
{
return this._totalLength
},set_totalLength:function(a)
{
this._totalLength=a
},setScalar:function(g,a)
{
try
{
if(arguments.length!=2)
throw Error.argument();
var b=this._getAndValidateProp(g,Wol.Logging.PropTypeEnum.Scalar);
if(!Wol.Util.TypeCheck.isOfType(a,Wol.Util.TypeCheck.StringType|Wol.Util.TypeCheck.NumberType|Wol.Util.TypeCheck.BooleanType)&&a!=null)
throw Error.argumentType("propValue",Object.getType(a),String);
if(a!=null)
a=this._replaceInvalidCharsWithSpace(a);
var c=Wol.Logging._getEncodedLengthForScalar(a),
d=c-b.encodedLength;
if(c==0)
d-=Wol.Logging.Const.PropOverhead;
if(b.encodedLength==null||b.encodedLength==undefined||b.encodedLength==0)
d+=Wol.Logging.Const.PropOverhead;
var e=this.get_totalLength()+d;
if(e>this._maxEncodedLength)
return Wol.Logging.ResultId.LengthTooLongFailure;
else
{
b.setValue(a,c);
this.set_totalLength(e);
return Wol.Logging.ResultId.Success
}
}
catch(f)
{
return this.throwOrIgnore(f)
}
},insertIntoList:function(c,a)
{
try
{
if(arguments.length!=2)
throw Error.argument();
if(c==null||c==undefined||c=="")
throw Error.argumentNull("propName","propName must be a valid omniture property name!");
var b=this._getAndValidateProp(c,Wol.Logging.PropTypeEnum.List);
a=this._getValidatedNonScalarPropValue(a);
var h=b.indexOf(a);
if(h>=0)
return Wol.Logging.ResultId.Success;
var d=Wol.Logging._getEncodedLengthForScalar(a),
e=0;
if(b.encodedLength==null||b.encodedLength==undefined||b.encodedLength==0)
e=d+Wol.Logging.Const.PropOverhead;
else
e=d+1;
var f=this.get_totalLength()+e;
if(f>this._maxEncodedLength)
return Wol.Logging.ResultId.LengthTooLongFailure;
else
{
b.append(a,d);
this.set_totalLength(f);
return Wol.Logging.ResultId.Success
}
}
catch(g)
{
return this.throwOrIgnore(g)
}
},insertIntoTable:function(c,e)
{
try
{
if(arguments.length!=2)
throw Error.argument();
if(c==null||c==undefined||c.length<2||!(c instanceof Array))
throw Error.argument("propNames");
if(e==null||e==undefined||e.length<2||!(e instanceof Array))
throw Error.argument("propValues");
if(c.length!=e.length)
throw Error.argument("propNamespropValues","propNames and propValues array length are different!");
for(var b=[],
f=[],
a=0;a<c.length;a++)
{
var h=c[a],
k=e[a];
if(h==null||h==undefined||h=="")
throw Error.argumentNull("propName","propName must be a valid omniture property name!");
var t=this._getAndValidateProp(h,a==0?Wol.Logging.PropTypeEnum.TableName:Wol.Logging.PropTypeEnum.TableValue);
k=this._getValidatedNonScalarPropValue(k);
b[a]=t;
f[a]=k
}
var p=b[0].keyPropName;
for(a=1;a<b.length;a++)
if(b[a].keyPropName!=p)
throw Error.create("Wol.Logging.MultipleKeyPropNamesException");
var m=0;
for(a=0;a<this._nonScalarProps.length;a++)
{
var o=this._get(this._nonScalarProps[a]);
if(o.keyPropName==p)
{
m++;
for(var r=false,
l=0;l<b.length;l++)
if(b[l].name==o.name)
{
r=true;
break
}
if(!r)
throw Error.create("Wol.Logging.MissingPropException")
}
}
if(m!=b.length)
throw Error.create("Wol.Logging.DuplicatePropNamesException");
var s=b[0].indexOf(f[0]),
d;
if(s>=0)
{
var g=[],
n=0;
for(a=1;a<b.length;a++)
{
g[a]=b[a].clone();
g[a].setAt(s,f[a]);
n+=g[a].encodedLength-b[a].encodedLength
}
d=this.get_totalLength()+n;
if(d>this._maxEncodedLength)
return Wol.Logging.ResultId.LengthTooLongFailure;
else
{
for(a=1;a<b.length;a++)
b[a].setValue(g[a].value,g[a].encodedLength);
this.set_totalLength(d);
return Wol.Logging.ResultId.Success
}
}
else
{
var j=0,
i=[];
for(a=0;a<b.length;a++)
{
i[a]=Wol.Logging._getEncodedLengthForScalar(f[a]);
j+=i[a]+1
}
if(b[0].encodedLength==null||b[0].encodedLength==undefined||b[0].encodedLength==0)
j+=b.length*Wol.Logging.Const.PropOverhead;
d=this.get_totalLength()+j;
if(d>this._maxEncodedLength)
return Wol.Logging.ResultId.LengthTooLongFailure;
else
{
for(a=0;a<b.length;a++)
b[a].append(f[a],i[a]);
this.set_totalLength(d);
return Wol.Logging.ResultId.Success
}
}
}
catch(q)
{
return this.throwOrIgnore(q)
}
},throwOrIgnore:function(a)
{
if(Wol.Logging.shouldIgnoreError())
return Wol.Logging.ResultId.GeneralFailure;
else
throw a
},_initNonScalarProps:function()
{
var b=this._props.findProp(Wol.Logging.Const.FieldName.TabControlIds);
b.type=Wol.Logging.PropTypeEnum.TableName;
b.keyPropName=Wol.Logging.Const.FieldName.TabControlIds;
var a=this._props.findProp(Wol.Logging.Const.FieldName.TabAssetUsageIds);
a.type=Wol.Logging.PropTypeEnum.TableValue;
a.keyPropName=Wol.Logging.Const.FieldName.TabControlIds;
var c=this._props.findProp(Wol.Logging.Const.FieldName.ChildAssetIds);
c.type=Wol.Logging.PropTypeEnum.List
},_getValidatedNonScalarPropValue:function(a)
{
if(!Wol.Util.TypeCheck.isOfType(a,Wol.Util.TypeCheck.StringType|Wol.Util.TypeCheck.NumberType|Wol.Util.TypeCheck.BooleanType))
throw Error.argumentType("propValue",Object.getType(a),String);
a=a.toString().replace(/[,]+/g," ");
var b=this._replaceInvalidCharsWithSpace(a);
if(b==""||b==" ")
throw Error.argument("propValue","Empty propValue is not allowed for non-scalar type");
return b
},_getAndValidateProp:function(b,c)
{
if(b==null||b==undefined||b=="")
throw Error.argumentNull("propName","propName must be a valid omniture property name!");
var a=this._get(b);
if(a==undefined||a==null||!(a instanceof Wol.Logging.Prop))
throw Error.argument("Can not find prop for given propName");
else
if(a.type!=c)
throw Error.argument("Invalid property type.");
return a
},_get:function(b)
{
var a=this[b];
if(a&&a instanceof Wol.Logging.Prop)
return a;
a=this._props.findProp(b);
if(a&&a instanceof Wol.Logging.Prop)
return a;
return this._eVars.findProp(b)
},_replaceInvalidCharsWithSpace:function(a)
{
return a.toString().replace(/[\s\u2122]+/g," ")
}};
Wol.Logging.LoggerTemplate.registerClass("Wol.Logging.LoggerTemplate");
Wol.Logging.Logger=function(c,a,b)
{
try
{
if(arguments.length<1||arguments.length>3)
throw Error.argument("invalid number of arguments!");
if(!(c instanceof Wol.Logging.LoggerTemplate))
throw Error.argumentType("template",Object.getType(c),Wol.Logging.LoggerTemplate);
Wol.Logging.Logger.initializeBase(this,[c]);
if(a==undefined||a==null)
a=Wol.Logging.LogTypeEnum.ClickThrough;
if(!Wol.Util.TypeCheck.isNumber(a))
throw Error.argumentType("loggingType",Object.getType(a),Number);
this.loggingType=a;
this.srcElement=b;
if(a==Wol.Logging.LogTypeEnum.ClickThrough&&b!=null&&b!=undefined)
{
this._maxEncodedLength=Wol.Logging.Const.MaxAllowedUrlLength-Wol.Logging.Const.BaseUrlLength-Wol.Logging.Const.LinkClickOverhead;
var e=Wol.Logging.getTargetUrl(b);
this.setScalar(Wol.Logging.Const.FieldName.TargetUrl,e)
}
else
this._maxEncodedLength=Wol.Logging.Const.MaxAllowedUrlLength-Wol.Logging.Const.BaseUrlLength;
if(this.get_totalLength()>this._maxEncodedLength)
throw Error.create("Wol.Logging.LengthTooLongException")
}
catch(d)
{
this.throwOrIgnore(d)
}
};
Wol.Logging.Logger.prototype.logToWT=function()
{
try
{
if(arguments.length>0)
throw Error.argument();
for(var a=[],
d=Wol.Logging.Const.OmnitureWTMappings,
c=0;c<d.length;c++)
{
var b=d[c];
if(b==null||b==undefined)
continue;
var f=this._get(b[0]),
g=f.getValue();
a.push(b[1]);
a.push(g)
}
a.push("WT.dl");
if(this.loggingType==Wol.Logging.LogTypeEnum.PageLoad)
a.push("0");
else
a.push("1");
dcsMultiTrack.apply(this.srcElement,a);
return Wol.Logging.ResultId.Success
}
catch(e)
{
return this.throwOrIgnore(e)
}
};
Wol.Logging.Logger.prototype.log=function()
{
try
{
if(arguments.length>0)
throw Error.argument();
for(var a=this.get_omLogger(),
b=Wol.Logging.ResultId.Success,
c=0;c<this._allProps.length;c++)
{
var e=this._allProps[c];
a[e.name]=e.getValue()
}
if(this.loggingType==Wol.Logging.LogTypeEnum.PageLoad)
{
b=this.logToWT();
a.t()
}
else
if(Wol.Logging.isClickThroughEnabled())
{
b=this.logToWT();
a.tl(this.srcElement,"o","")
}
return b
}
catch(d)
{
return this.throwOrIgnore(d)
}
};
Wol.Logging.Logger.registerClass("Wol.Logging.Logger",Wol.Logging.LoggerTemplate);
Wol.Logging.Internal={enableClickThroughLogging:function()
{
Sys.Debug.assert(arguments.length==0);
if(document.body!=undefined&&document.body!=null)
document.body.onclick=this.onClick
},onClick:function(l)
{
try
{
var c=typeof event!="undefined"?window.event.srcElement:l.target,
g=Wol.Logging.getTargetUrlElement(c);
if(g==null||g==undefined)
return;
c=g;
var f=Wol.Logging.Const.InteractionTypeId.Click,
h=new Wol.Logging.Logger(Wol.Logging.commonLoggerTemplate(),Wol.Logging.LogTypeEnum.ClickThrough,c),
e=Wol.Logging.Internal._findInteractionTypeIdElement(c);
if(e!=null)
{
var b=e.attributes[Wol.Logging.Const.ElementAttrib.InteractionTypeId].value;
if(b!=null&&b!=undefined)
{
if(b.toLowerCase()=="none")
return;
var i=b.indexOf(Wol.Logging.Const.ElementAttrib.InteractionTypeIdSep);
if(i<0)
f=Number.parseInvariant(b);
else
{
f=Number.parseInvariant(b.substr(0,i));
var k=b.substr(i);
if(k.toLowerCase()!=Wol.Logging.Const.ElementAttrib.InteractionTypeIdSep.toLowerCase()+Wol.Logging.Const.ElementAttrib.AdditionalProp.toLowerCase())
throw Error.create("Wol.Logging.InvalidInteractionTypeIdException");
var d=[],
a=c;
while(a!=null&&a!=undefined&&a!=e)
{
d.push(a);
a=a.parentElement||a.parentNode
}
d.push(e);
do
{
a=d.pop();
Wol.Logging.Internal._setPropFromElement(h,a)
}while(d.length>0)
}
}
}
h.setScalar(Wol.Logging.Const.FieldName.InteractionId,f);
h.log()
}
catch(j)
{
Wol.Logging.LoggerTemplate.prototype.throwOrIgnore(j)
}
},createOMLogger:function(b)
{
try
{
if(arguments.length!=1||!Wol.Util.TypeCheck.isString(b))
throw Error.argument("s_account","s_account must be the only parameter, and its type must be string!");
var a=s_gi(b);
a.m_Media_c="='s_media_'+m._in+'_~=new Function(~m.ae(mn,l,\"'+p+'\",~;`H~o.'+f~o.Get~=function(~){var m=this~}^9 p');p=tcf(o)~setTimeout(~x,x!=2?p:-1,o)}~=parseInt(~m.s.d.getElementsByTagName~ersionInfo~'`z_c_il['+m._in+'],~'o','var e,p=~QuickTime~if(~}catch(e){p=~s.wd.addEventListener~m.s.rep(~=new Object~layState~||^D~m.s.wd[f1]~Media~.name~Player '+~s.wd.attachEvent~'a','b',c~;o[f1]~tm.getTime()/1~m.s.isie~.current~,tm=new Date,~p<p2||p-p2>5)~m.e(n,1,o^F~m.close~i.lx~=v+',n,~){this.e(n,~MovieName()~);o[f~i.lo~m.ol~o.controls~load',m.as~==3)~script';x.~,t;try{t=~Version()~else~o.id~){mn=~1;o[f7]=~Position~);m.~(x==~)};m.~&&m.l~l[n])~var m=s~!p){tcf~xc=m.s.~Title()~();~7+'~)}};m.a~\"'+v+';~3,p,o);~5000~return~i.lt~';c2='~Change~n==~',f~);i.~==1)~{p='~4+'=n;~()/t;p~.'+n)}~~`z.m_i('`P'`uopen`6n,l,p,b`7,i`L`Ya='',x;l`Bl)`3!l)l=1`3n&&p){`H!m.l)m.l`L;n=`Km.s.rep(`Kn,\"\\n\",''),\"\\r\",''),'--**--','')`3m.`y`b(n)`3b&&b.id)a=b.id;for (x in m.l)`Hm.l[x]`x[x].a==a)`b(m.l[x].n^Fn=n;i.l=l;i.p=p;i.a=a;i.t=0;i.s`B`V000);`c=0;^A=0;`h=0;i.e='';m.l[n]=i}};`b`6n`e0,-1`wplay`6n,o`7,i;i=`am`1`Ei`3m.l){i=m.l[\"'+`Ki.n,'\"','\\\\\"')+'\"]`3i){`H`c^Gm.e(i.n,3,-1^Fmt=`9i.m,^8)}}'^Fm(`wstop`6n,o`e2,o`we`6n,x,o`7,i=n`x&&m.l[n]?m.l[n]:0`Yts`B`V000),d='--**--'`3i){if `v3||(x!=`c&&(x!=2||`c^G)) {`Hx){`Ho<0&&^A>0){o=(ts-^A)+`h;o=o<i.l?o:i.l-1}o`Bo)`3`v2||x`l&&`h<o)i.t+=o-`h`3x!=3){i.e+=`v1?'S':'E')+o;`c=x;}`p `H`c!=1)`alt=ts;`h=o;m.s.pe='media';m.s.pev3=i.n+d+i.l+d+i.p+d+i.t+d+i.s+d+i.e+`v3?'E'+o:''`us.t(0,'`P^K`p{m.e(n,2,-1`ul[n]=0;m.s.fbr('`P^K}}^9 i};m.ae`6n,l,p,x,o,b){`Hn&&p`7`3!m.l||!m.`ym.open(n,l,p,b`ue(n,x,o^5`6o,t`7,i=`q?`q:o`Q,n=o`Q,p=0,v,c,c1,c2,^1h,x,e,f1,f2`0oc^E3`0t^E4`0s^E5`0l^E6`0m^E7`0c',tcf,w`3!i){`H!m.c)m.c=0;i`0'+m.c;m.c++}`H!`q)`q=i`3!o`Q)o`Q=n=i`3!`i)`i`L`3`i[i])^9;`i[i]=o`3!xc)^1b;tcf`1`F0;try{`Ho.v`D&&o`X`P&&`j)p=1`I0`8`3^0`1`F0`n`5`G`o`3t)p=2`I0`8`3^0`1`F0`n`5V`D()`3t)p=3`I0`8}}v=\"`z_c_il[\"+m._in+\"],o=`i['\"+i+\"']\"`3p^G^HWindows `P `Ro.v`D;c1`dp,l,x=-1,cm,c,mn`3o){cm=o`X`P;c=`j`3cm&&c`rcm`Q?cm`Q:c.URL;l=cm.duration;p=c`X`t;n=o.p`M`3n){`H^D8)x=0`3n`lx=1`3^D1`N2`N4`N5`N6)x=2;}^B`Hx>=0)`2`A}';c=c1+c2`3`W&&xc){x=m.s.d.createElement('script');x.language='j`mtype='text/java`mhtmlFor=i;x.event='P`M^C(NewState)';x.defer=true;x.text=c;xc.appendChild(x`g6]`1c1+'`Hn`l{x=3;'+c2+'}`9`46+',^8)'`g6]()}}`Hp==2)^H`G `R(`5Is`GRegistered()?'Pro ':'')+`5`G`o;f1=f2;c`dx,t,l,p,p2,mn`3o`r`5`f?`5`f:`5URL^3n=`5Rate^3t=`5TimeScale^3l=`5Duration^J=`5Time^J2=`45+'`3n!=`44+'||`Z{x=2`3n!=0)x=1;`p `Hp>=l)x=0`3`Z`22,p2,o);`2`A`Hn>0&&`4^4>=10){`2^7`4^4=0}`4^4++;`4^I`45+'=p;`9^6`42+'(0,0)\",500)}'`U`1`T`g4]=-`s0`U(0,0)}`Hp`l^HReal`R`5V`D^3f1=n+'_OnP`M^C';c1`dx=-1,l,p,mn`3o`r`5^2?`5^2:`5Source^3n=`5P`M^3l=`5Length()/1000;p=`5`t()/1000`3n!=`44+'){`Hn`lx=1`3^D0`N2`N4`N5)x=2`3^D0&&(p>=l||p==0))x=0`3x>=0)`2`A`H^D3&&(`4^4>=10||!`43+')){`2^7`4^4=0}`4^4++;`4^I^B`H`42+')`42+'(o,n)}'`3`O)o[f2]=`O;`O`1`T1+c2)`U`1`T1+'`9^6`41+'(0,0)\",`43+'?500:^8);'+c2`g4]=-1`3`W)o[f3]=`s0`U(0,0^5s`1'e',`El,n`3m.autoTrack&&`C){l=`C(`W?\"OBJECT\":\"EMBED\")`3l)for(n=0;n<l.length;n++)m.a(`y;}')`3`S)`S('on`k);`p `H`J)`J('`k,false)";
a.m_i("Media");
a.charSet="utf-8";
a.trackInlineStats=false;
a.trackDownloadLinks=false;
a.trackExternalLinks=false;
a.loadModule("Media");
a.Media.autoTrack=true;
a.Media.trackVars="";
a.Media.trackEvents="None";
a.visitorID=Wol.Logging.getVisitor();
return a
}
catch(c)
{
return Wol.Logging.LoggerTemplate.prototype.throwOrIgnore(c)
}
},_findInteractionTypeIdElement:function(a)
{
if(a==null||a==undefined)
return null;
if(a.attributes&&a.attributes[Wol.Logging.Const.ElementAttrib.InteractionTypeId])
return a;
return Wol.Logging.Internal._findInteractionTypeIdElement(a.parentElement||a.parentNode)
},_setPropFromElement:function(e,c)
{
var b=Wol.Logging.Const.ElementAttrib.AdditionalProp+Wol.Logging.Const.ElementAttrib.AdditionalPropSep;
b=b.toLowerCase();
for(var a=0;a<c.attributes.length;a++)
if(c.attributes[a].name.toLowerCase().indexOf(b)==0)
{
var d=e.setScalar(c.attributes[a].name.substr(b.length),c.attributes[a].value);
if(d!=Wol.Logging.ResultId.Success)
throw Error.create("Wol.Logging.SetScalarFailedException")
}
}};
var link_expandAllText=!link_expandAllText?"":link_expandAllText,
link_collapseAllText=!link_collapseAllText?"":link_collapseAllText,
clickHandlerFunctionMap={link_expand:ExpandOrCollapseSingleNode,link_collapse:ExpandOrCollapseSingleNode,link_expandAll:ExpandOrCollapseAllNodes,link_collapseAll:ExpandOrCollapseAllNodes,link_image_expand:ExpandOrCollapseSingleNode_Image,link_image_collapse:ExpandOrCollapseSingleNode_Image},
expandCollapse_idAttribute="data-id";
if(document.attachEvent)
document.attachEvent("onclick",ClickHandlerBase);
else
document.addEventListener&&
document.addEventListener("click",ClickHandlerBase,false);
function ClickHandlerBase(a)
{
var c=a!=null&&a.target==null?a.srcElement:a.target;
if(c.attributes["class"]!=null)
{
var b=c.attributes["class"].value;
if(clickHandlerFunctionMap.hasOwnProperty(b))
{
clickHandlerFunctionMap[b](c,b,true);
if(!ReturnFalse(a))
return false
}
}
}
function NoOp()
{
}
function GetElementDistance(c,b)
{
if(c==null||b==null)
return -1;
if(c==b)
return 0;
var d=1,
a=c.parentNode;
while(a!=null&&a!=document)
{
if(a==b)
return d;
d++;
a=a.parentNode
}
return -1
}
function IsElementWithinDistance(c,b,d)
{
var a=GetElementDistance(c,b);
if(a==null||a<0||a>d)
return false;
else
return true
}
function ExpandOrCollapseSingleNode(a,d,f)
{
if(a!=null)
{
var c=jQuery(a).closest(".link_container").parent().closest("div")[0],
b=c.childNodes[1],
g=c.parentNode,
h=jQuery(a).closest(".link_container"),
e=h.find(".link_image_container a img")[0];
if(c!=null&&b!=null)
{
if(d=="link_collapse")
{
SetClassName(a,"link_expand");
SetClassName(b,"expand");
SetClassName(e,"link_image_expand");
f==true&&
SaveCollapseState(a)
}
else
if(d=="link_expand")
{
SetClassName(a,"link_collapse");
SetClassName(b,"collapse");
SetClassName(e,"link_image_collapse");
f==true&&
SaveExpandState(a)
}
UpdateExpandCollapseAllLink(g)
}
}
}
function ExpandOrCollapseSingleNode_Image(a,f,d)
{
if(a!=null)
{
var e=jQuery(a).closest(".link_container"),
b=e.find("a")[1],
c=b.attributes["class"].value;
ExpandOrCollapseSingleNode(b,c,d)
}
}
function ExpandOrCollapseAllNodes(a,c,g)
{
if(a!=null)
{
var h=a.parentNode.childNodes;
if(c=="link_expandAll")
{
SetClassName(a,"link_collapseAll");
SetTextValue(a,link_collapseAllText);
g==true&&
SaveExpandState(a)
}
if(c=="link_collapseAll")
{
SetClassName(a,"link_expandAll");
SetTextValue(a,link_expandAllText);
g==true&&
SaveCollapseState(a)
}
for(i=0;i<h.length;i++)
{
var b=jQuery(h[i]).attr("class");
if(b==undefined||b==null||b=="")
continue;
if(b.indexOf("faqEntry")!=-1||b.indexOf("procedure")!=-1||b.indexOf("section")!=-1)
{
var l=h[i].childNodes;
for(j=0;j<l.length;j++)
{
var k=l[j],
m=k.attributes["class"];
if(m==null)
continue;
var d=m.value;
if(d=="question"||d=="title_procedure ecTitle"||d=="title_section ecTitle")
{
var e=jQuery(k).find(".link_container").children();
if(e&&e.length==2)
{
var o=jQuery(e[0]).find("a img")[0],
f=jQuery(e[1]).find("a")[0];
if(c=="link_expandAll")
{
SetClassName(o,"link_image_expand");
SetClassName(f,"link_expand");
g==true&&
SaveCollapseState(f)
}
else
if(c=="link_collapseAll")
{
SetClassName(o,"link_image_collapse");
SetClassName(f,"link_collapse");
g==true&&
SaveExpandState(f)
}
}
}
if(d=="collapse"||d=="expand")
{
var n=k;
if(c=="link_expandAll")
SetClassName(n,"expand");
else
c=="link_collapseAll"&&
SetClassName(n,"collapse")
}
}
}
}
}
}
function SetClassName(b,a)
{
if(b!=null&&a!=null&&a!="")
b.attributes["class"].value=a
}
function SetTextValue(b,a)
{
if(b!=null&&a!=null&&a!="")
b.innerHTML=a
}
function ExpandCollapseCookieState(b)
{
var a=b.getAttribute(expandCollapse_idAttribute);
if(a==undefined||a=="")
return null;
return Ms.Wol.Cookies.GetCookie(a)
}
function SaveExpandState(a)
{
if(a==null)
return;
var b=jQuery(a).attr(expandCollapse_idAttribute);
b!=undefined&&
Ms.Wol.Cookies.SetCookie(b,Ms.Wol.ExpandCollapse.GetECExpandValue())
}
function SaveCollapseState(a)
{
if(a==null)
return;
var b=jQuery(a).attr(expandCollapse_idAttribute);
b!=undefined&&
Ms.Wol.Cookies.SetCookie(b,Ms.Wol.ExpandCollapse.GetECCollapseValue())
}
function ReturnFalse(a)
{
if(a.preventDefault)
a.preventDefault();
else
return false
}
function DropDown_Changed(e)
{
var b=e.target;
if(b!=null)
{
var c=b.parentNode,
a=null;
while(c!=null&&a==null)
{
a=c.getElementsByTagName("a")[0];
c=c.parentNode
}
if(a!=null)
{
var d=b.options[b.selectedIndex].value;
if(d=="")
{
a.removeAttribute("href");
SetClassName(a,"nohref")
}
else
{
a.href=d;
SetClassName(a,"hashref")
}
}
}
}
if(typeof Ms=="undefined")
Ms={};
if(typeof Ms.Wol=="undefined")
Ms.Wol={};
if(typeof Ms.Wol.ExpandCollapse=="undefined")
Ms.Wol.ExpandCollapse={};
Ms.Wol.ExpandCollapse=function()
{
jQuery(document).ready(b);
var a={_sExpandCookieValue:"e",_sCollapseCookieValue:"c"};
function b()
{
var c=jQuery("a.link_expandAll");
jQuery.each(c,function()
{
ExpandCollapseCookieState(this)==a._sExpandCookieValue&&
ExpandOrCollapseAllNodes(this,"link_expandAll",false)
});
var b=jQuery("a.link_collapseAll");
jQuery.each(b,function()
{
ExpandCollapseCookieState(this)==a._sCollapseCookieValue&&
ExpandOrCollapseAllNodes(this,"link_collapseAll",false)
});
var e=jQuery("a.link_expand");
jQuery.each(e,function()
{
ExpandCollapseCookieState(this)==a._sExpandCookieValue&&
ExpandOrCollapseSingleNode(this,"link_expand",false)
});
var d=jQuery("a.link_collapse");
jQuery.each(d,function()
{
ExpandCollapseCookieState(this)==a._sCollapseCookieValue&&
ExpandOrCollapseSingleNode(this,"link_collapse",false)
})
}
return {GetECExpandValue:function()
{
return a._sExpandCookieValue
},GetECCollapseValue:function()
{
return a._sCollapseCookieValue
}}
}();
if(typeof Sys!="undefined"&&typeof Sys.WebForms!="undefined"&&typeof Sys.WebForms.PageRequestManager!="undefined")
Sys.WebForms.PageRequestManager.getInstance().add_pageLoaded(ButtonKeypress);
else
jQuery(document).ready(ButtonKeypress);
function ButtonKeypress()
{
jQuery("span.button>a").keypress(function(c)
{
if(c.which==32)
{
var a=jQuery(this).attr("href")!="undefined";
if(this.onClick)
if(this.onClick()==false)
a=false;
if(a)
{
if(this.click)
this.click();
else
{
var b=document.createEvent("MouseEvents");
b.initMouseEvent("click",true,true,window,0,0,0,0,0,false,false,false,false,0,null);
this.dispatchEvent(b);
window.location.href=jQuery(this).attr("href")
}
ReturnFalse(c)
}
}
})
}
if(typeof Sys!="undefined"&&typeof Sys.WebForms!="undefined"&&typeof Sys.WebForms.PageRequestManager!="undefined")
Sys.WebForms.PageRequestManager.getInstance().add_pageLoaded(MsWolDropDownPrep);
else
jQuery(document).ready(MsWolDropDownPrep);
function MsWolDropDownPrep()
{
var a=jQuery("select.dropdown_select");
a.change(DropDown_Changed);
jQuery.each(a,function()
{
jQuery(this).trigger("change")
})
}
function UpdateExpandCollapseAllLink(a)
{
if(a!=undefined)
{
var e=jQuery(a).children(".link_expandAll"),
d=jQuery(a).children(".link_collapseAll"),
c=jQuery(a).find("a.link_expand"),
b=jQuery(a).find("a.link_collapse");
c=c.filter(function()
{
return IsElementWithinDistance(jQuery(this)[0],a,7)
});
b=b.filter(function()
{
return IsElementWithinDistance(jQuery(this)[0],a,7)
});
if(jQuery(b).length>0)
{
jQuery(d).attr("class","link_expandAll");
jQuery(d).html(link_expandAllText)
}
else
if(jQuery(c).length>0)
{
jQuery(e).attr("class","link_collapseAll");
jQuery(e).html(link_collapseAllText)
}
}
}
if(typeof Sys!="undefined"&&typeof Sys.WebForms!="undefined"&&typeof Sys.WebForms.PageRequestManager!="undefined")
Sys.WebForms.PageRequestManager.getInstance().add_pageLoaded(MsWolAOBIPatch);
else
jQuery(document).ready(MsWolAOBIPatch);
function MsWolAOBIPatch()
{
var d=navigator.appVersion.split(";")[1]==" MSIE 6.0",
a=navigator.appName=="Safari"||navigator.userAgent.indexOf("Safari")!=-1;
if(d||a)
{
var b=jQuery("div.section_oly-bg-stretch").parent();
b.each(function()
{
var a=jQuery(this),
b=a.children().children("img.embedObject");
b.height()<a.height()&&
b.height(a.height())
})
}
if(a)
{
var c=jQuery("div.wmpObjectDownlevel");
c.each(function()
{
var d=jQuery(this),
i=d.children("a"),
c=i.children("img"),
b=d.parent(),
h=b.parent(),
f=h.parent(),
e=f.parent(),
g=e.parent(),
a=b.children("div.wmpObjectDiv"),
j=a.children("object");
if(g.hasClass("section_oly")&&a.size()>0)
c.height()<a.height()&&
c.height(a.height())
})
}
};
if(typeof Ms=="undefined")
Ms={};
if(typeof Ms.Wol=="undefined")
Ms.Wol={};
if(typeof Ms.Wol.SearchBox=="undefined")
Ms.Wol.SearchBox={};
Ms.Wol.SearchBox.BackgroundBoxSelector="div.SearchQueryBoxBackgroundLevel1,div.HHSearchQueryBoxBackgroundLevel1";
Ms.Wol.SearchBox.InputBoxSelector='input[type="text"]';
$(function()
{
Ms.Wol.SearchBox.OnReadyFunction()
});
$(window).load(function()
{
Ms.Wol.SearchBox.OnLoadFunction()
});
Ms.Wol.SearchBox.OnReadyFunction=function()
{
var c="form.SearchQuery,form.HHSearchQuery",
b='form.SearchQuery input[type="submit"],form.HHSearchQuery input[type="submit"]',
a="a.HighContrastSearchQuerySubmit,a.HHHighContrastSearchQuerySubmit";
$(c).each(function()
{
var a=this,
c=$(a),
b=c.children(Ms.Wol.SearchBox.InputBoxSelector);
if(Ms.Wol.SearchBox.TryAddProperties(b,a))
{
b.focus(function()
{
Ms.Wol.SearchBox.UnloadSearchText(this.backgroundBox)
}).blur(function()
{
Ms.Wol.SearchBox.LoadSearchTextIfEmpty(this,this.backgroundBox)
});
$(a).children(Ms.Wol.SearchBox.BackgroundBoxSelector).focus(function()
{
Ms.Wol.SearchBox.UnloadSearchText(this);
$(this.inputBox).focus()
});
$(a).submit(function()
{
if(this.inputBox.value!="")
return true;
return false
});
var d=function(b)
{
if(b.keyCode==13&&a.inputBox.value!="")
if(typeof Wol!="undefined"&&typeof Wol.ContentInstrumentation!="undefined"&&typeof Wol.ContentInstrumentation.Logging!="undefined")
{
Wol.ContentInstrumentation.Logging.FireClickTrackingEvent(b);
b.preventDefault();
setTimeout(function()
{
c.submit()
},50)
}
};
jQuery(a.inputBox).keypress(d)
}
});
$(a).css("display","inline");
$(b).css("display","none")
};
Ms.Wol.SearchBox.OnLoadFunction=function()
{
$(Ms.Wol.SearchBox.InputBoxSelector).each(function()
{
(typeof this.HasFocus=="undefined"||!this.HasFocus)&&typeof this.backgroundBox!="undefined"&&
Ms.Wol.SearchBox.LoadSearchTextIfEmpty(this,this.backgroundBox)
});
if(typeof Wol!="undefined"&&typeof Wol.ContentInstrumentation!="undefined"&&typeof Wol.ContentInstrumentation.Logging!="undefined"&&typeof Wol.Logging!="undefined"&&typeof Wol.Logging.Const!="undefined")
{
var a=Wol.Logging.Const.ElementAttrib.AdditionalProp+Wol.Logging.Const.ElementAttrib.AdditionalPropSep+Wol.Logging.Const.FieldName.SearchResultClickPosition,
b=".SearchResultItem ["+a+"]";
jQuery(b).each(function()
{
var c=this,
b=jQuery(this).attr(a);
b&&
Wol.ContentInstrumentation.Logging.Annotate("Search.Position",b,c)
})
}
};
Ms.Wol.SearchBox.LoadSearchTextIfEmpty=function(b,a)
{
if(b.value=="")
$(a).css("display","inline");
else
$(a).css("display","none")
};
Ms.Wol.SearchBox.UnloadSearchText=function(a)
{
$(a).css("display","none")
};
Ms.Wol.SearchBox.TryAddProperties=function(a,e)
{
if(typeof a=="undefined"||typeof e=="undefined")
return false;
var c=a.siblings(Ms.Wol.SearchBox.BackgroundBoxSelector);
if(a.length==1&&c.length==1)
{
var b=a.get(0),
d=c.get(0);
e.inputBox=b;
b.backgroundBox=d;
d.inputBox=b;
return true
}
else
return false
};
var wolFeedbackId;
jQuery(document).ready(processDocumentLoaded);
function processDocumentLoaded()
{
var a=jQuery(".feedbackRatingButton:disabled");
if(a)
for(var b=0;b<a.length;b++)
a[b].removeAttribute("disabled");
jQuery(".feedbackCommentVerboseText").bind("paste",processVerboseFeedbackPaste)
}
function processRatingExplanationClick(a)
{
window.open(a)
}
function processRatingClick(b,h,g,e,c,d)
{
if(!Wol.Logging)
{
Sys.Debug.assert(false,"Logging library is not available",true);
return
}
if(!Wol.ContentInstrumentation||!Wol.ContentInstrumentation.Logging)
{
Sys.Debug.assert(false,"ContentInstrumentation library is not available",true);
return
}
wolFeedbackId=createFeedbackId();
if(Wol.Logging.isEnabled())
{
var a=new Wol.Logging.Logger(Wol.Logging.commonLoggerTemplate());
a.setScalar(Wol.Logging.Const.FieldName.InteractionId,Wol.Logging.Const.InteractionTypeId.Rating);
a.setScalar(Wol.Logging.Const.FieldName.FeedbackId,wolFeedbackId);
a.setScalar(Wol.Logging.Const.FieldName.Rating,b);
a.log()
}
Wol.ContentInstrumentation.Logging.LogCustomBI("Rating",{Rating:b,FeedbackId:wolFeedbackId});
var j=jQuery("#"+h)[0],
i=jQuery("#"+g)[0];
if(b=="0")
{
jQuery("#"+e)[0].innerHTML=jQuery("#commentNoPrompt")[0].innerHTML;
jQuery("#"+c)[0].value=jQuery("#commentNoYesString")[0].innerHTML;
jQuery("#"+d)[0].value=jQuery("#commentNoNoString")[0].innerHTML
}
else
if(b=="0.5")
{
jQuery("#"+e)[0].innerHTML=jQuery("#commentSomewhatPrompt")[0].innerHTML;
jQuery("#"+c)[0].value=jQuery("#commentSomewhatYesString")[0].innerHTML;
jQuery("#"+d)[0].value=jQuery("#commentSomewhatNoString")[0].innerHTML
}
j.style.display="none";
i.style.display="block";
var f=jQuery("#commentVerboseText")[0];
f.focus();
return false
}
function processVerboseFeedbackKeyUp()
{
processTextChange()
}
function processVerboseFeedbackChange()
{
processTextChange()
}
function processVerboseFeedbackFocus()
{
processTextChange()
}
function processVerboseFeedbackPaste()
{
window.setTimeout(processTextChange,200)
}
function processTextChange()
{
var f=5,
a=jQuery("#commentVerboseText")[0],
g=parseInt(jQuery("#commentMaxSize")[0].innerHTML),
j=parseInt(jQuery("#commentMaxEncodedSize")[0].innerHTML);
if(a.value.length>g)
a.value=a.value.substr(0,g);
var c=escape(a.value),
i=c.length,
b=j-f;
if(i>b)
{
var d=c.substr(0,b),
e=c.substr(b,f),
h=e.lastIndexOf("%"),
k;
if(h==-1)
d+=e;
else
d+=e.substr(0,h);
a.value=unescape(d)
}
}
function processCommentClick(g,c,e)
{
if(!Wol.Logging)
{
Sys.Debug.assert(false,"Logging library is not available",true);
return
}
if(!Wol.ContentInstrumentation||!Wol.ContentInstrumentation.Logging)
{
Sys.Debug.assert(false,"ContentInstrumentation library is not available",true);
return
}
var b=jQuery("#commentVerboseText")[0];
if(g=="1"&&b.value.length>0)
{
if(Wol.Logging.isEnabled())
{
var a=new Wol.Logging.Logger(Wol.Logging.requiredLoggerTemplate());
a.setScalar(Wol.Logging.Const.FieldName.InteractionId,Wol.Logging.Const.InteractionTypeId.VerboseFeedback);
a.setScalar(Wol.Logging.Const.FieldName.FeedbackId,wolFeedbackId);
setVerboseFeedback(a,b.value);
a.log()
}
Wol.ContentInstrumentation.Logging.LogCustomBI("Feedback",{Verbatim:b.value,FeedbackId:wolFeedbackId})
}
var d=jQuery("#"+c)[0],
f=jQuery("#"+e)[0];
d.style.display="none";
f.style.display="block";
return false
}
function setVerboseFeedback(j,g)
{
var b=25,
f=parseInt(jQuery("#commentMaxSize")[0].innerHTML),
i=Math.ceil(f/b),
c=Math.min(f,g.length);
if(c==0)
return;
var a=0,
d=1;
while(a<c&&d<=i)
{
var e;
if(a+b<=c)
e=b;
else
e=c-a;
var h=g.substr(a,e);
j.setScalar("eVar"+d,h);
a=a+b;
d=d+1
}
}
function createRandomFourDigitHex()
{
var a=Math.floor(Math.random()*65536).toString(16),
b=a.length;
if(b!=4)
if(b==3)
a="0"+a;
else
if(b==2)
a="00"+a;
else
a="000"+a;
return a
}
function createFeedbackId()
{
return createRandomFourDigitHex()+createRandomFourDigitHex()+"-"+createRandomFourDigitHex()+"-"+createRandomFourDigitHex()+"-"+createRandomFourDigitHex()+"-"+createRandomFourDigitHex()+createRandomFourDigitHex()+createRandomFourDigitHex()
};
if(typeof Ms=="undefined")
Ms={};
if(typeof Ms.Wol=="undefined")
Ms.Wol={};
if(typeof Ms.Wol.Nav=="undefined")
Ms.Wol.Nav={};
Ms.Wol.Nav.Status=0;
jQuery(function()
{
var c="#bodyNavBar",
b="#bodyNavBar a",
a="div.menuGroupWrapper:visible";
if(Ms.Wol.Nav.TryAddTransitions())
{
jQuery(b).keydown(function(b)
{
var a=this.Transitions[Ms.Wol.Nav.GetKeyForTransitionFromEvent(b)];
if(typeof a!="undefined")
{
Ms.Wol.Nav.UpdateStyles(this,a);
Ms.Wol.Nav.FocusOnNext(this,a);
return false
}
return true
}).blur(function()
{
if(typeof this.InMenuBlur=="undefined"||!this.InMenuBlur)
{
Ms.Wol.Nav.RemoveKeyboardStyles(false);
Ms.Wol.Nav.Status=0
}
else
this.InMenuBlur=false
});
jQuery(Ms.Wol.Nav.FirstLink).keydown(function(a)
{
if(Ms.Wol.Nav.GetKeyForTransitionFromEvent(a)==Ms.Wol.Nav.GetKeyForTransition(9,true))
{
Ms.Wol.Nav.RemoveKeyboardStyles(false);
Ms.Wol.Nav.Status=0;
return true
}
});
jQuery(Ms.Wol.Nav.LastLink).keydown(function(a)
{
if(Ms.Wol.Nav.GetKeyForTransitionFromEvent(a)==Ms.Wol.Nav.GetKeyForTransition(9,false))
{
Ms.Wol.Nav.RemoveKeyboardStyles(false);
Ms.Wol.Nav.Status=0;
return true
}
});
jQuery(Ms.Wol.Nav.FirstMenuTitleLink).focus(function()
{
if(Ms.Wol.Nav.Status!=1)
{
Ms.Wol.Nav.ApplyKeyboardStyles();
Ms.Wol.Nav.UpdateStyles(this,this)
}
});
jQuery(Ms.Wol.Nav.LastMenuTitleLink).focus(function()
{
if(Ms.Wol.Nav.Status!=1&&jQuery(this).siblings(a).length==0)
{
Ms.Wol.Nav.ApplyKeyboardStyles();
var b=this.Transitions[Ms.Wol.Nav.GetKeyForTransition(38,false)];
if(typeof b!="undefined")
{
Ms.Wol.Nav.UpdateStyles(this,b);
Ms.Wol.Nav.FocusOnNext(this,b)
}
else
Ms.Wol.Nav.UpdateStyles(this,this)
}
});
jQuery(Ms.Wol.Nav.LastLink).focus(function()
{
if(Ms.Wol.Nav.Status!=1)
{
Ms.Wol.Nav.ApplyKeyboardStyles();
Ms.Wol.Nav.UpdateStyles(this,this)
}
});
jQuery(c).mouseover(function()
{
if(Ms.Wol.Nav.Status==1)
{
Ms.Wol.Nav.RemoveKeyboardStyles(true);
Ms.Wol.Nav.Status=2
}
}).mousemove(function()
{
if(Ms.Wol.Nav.Status==1)
{
Ms.Wol.Nav.RemoveKeyboardStyles(true);
Ms.Wol.Nav.Status=2
}
}).mouseout(function()
{
if(Ms.Wol.Nav.Status==1)
{
Ms.Wol.Nav.RemoveKeyboardStyles(true);
Ms.Wol.Nav.Status=0
}
})
}
});
Ms.Wol.Nav.GetKeyForTransitionFromEvent=function(a)
{
var b=null;
if(typeof a.keyCode!="undefined")
b=a.keyCode;
else
if(typeof a.which!="undefined")
b=a.which;
var c=typeof a.shiftKey!="undefined"&&a.shiftKey;
return Ms.Wol.Nav.GetKeyForTransition(b,c)
};
Ms.Wol.Nav.GetKeyForTransition=function(a,b)
{
return String(a)+String(b)
};
Ms.Wol.Nav.AddTransition=function(b,d,c,a)
{
b.Transitions[Ms.Wol.Nav.GetKeyForTransition(d,c)]=a
};
Ms.Wol.Nav.AddBothTransitions=function(b,c,a)
{
Ms.Wol.Nav.AddTransition(b,c,true,a);
Ms.Wol.Nav.AddTransition(b,c,false,a)
};
Ms.Wol.Nav.TryAddTransitions=function()
{
var m="div.navMenu",
l="#bodyNavBar a",
k="menuItemLinkSimHover",
b=jQuery(l).each(function()
{
this.Transitions=Array();
this.originalStyle=this.className;
this.hoverStyle=k
}).get();
if(b.length>0)
{
Ms.Wol.Nav.FirstLink=b[0];
Ms.Wol.Nav.LastLink=b[b.length-1]
}
Ms.Wol.Nav.AddAllTabTransitions(b);
var a=jQuery(m).children().each(function()
{
if(!Ms.Wol.Nav.InitMenuTitle(this))
return false
}).get();
rtl=jQuery(a).css("direction")=="rtl";
for(i=1;i<a.length-1;i++)
{
var g=rtl?a[i+1].menuTitleLink:a[i-1].menuTitleLink,
f=rtl?a[i-1].menuTitleLink:a[i+1].menuTitleLink,
c=jQuery(a[i]).find("a").each(function()
{
this.menuTitle=a[i]
}).get();
Ms.Wol.Nav.AddArrowTransitionsForMenuTitle(c,g,f)
}
if(a.length>0)
{
var j=rtl||a.length<=1,
h=rtl&&a.length>1,
d=a[0].menuTitleLink,
e=a[a.length-1].menuTitleLink;
Ms.Wol.Nav.FirstMenuTitleLink=d;
Ms.Wol.Nav.LastMenuTitleLink=e;
var g=h?a[1].menuTitleLink:e,
f=j?e:a[1].menuTitleLink,
c=jQuery(a[0]).find("a").each(function()
{
this.menuTitle=a[0]
}).get();
Ms.Wol.Nav.AddArrowTransitionsForMenuTitle(c,g,f);
var g=j?d:a[a.length-2].menuTitleLink,
f=h?a[a.length-2].menuTitleLink:d,
c=jQuery(a[a.length-1]).find("a").each(function()
{
this.menuTitle=a[a.length-1]
}).get();
Ms.Wol.Nav.AddArrowTransitionsForMenuTitle(c,g,f)
}
return true
};
Ms.Wol.Nav.AddArrowTransitionsForMenuTitle=function(a,c,b)
{
for(j=1;j<a.length;j++)
{
Ms.Wol.Nav.AddBothTransitions(a[j],37,c);
Ms.Wol.Nav.AddBothTransitions(a[j],39,b);
Ms.Wol.Nav.AddBothTransitions(a[j],38,a[j-1]);
Ms.Wol.Nav.AddBothTransitions(a[j-1],40,a[j])
}
if(a.length>0)
{
Ms.Wol.Nav.AddBothTransitions(a[0],37,c);
Ms.Wol.Nav.AddBothTransitions(a[0],39,b);
Ms.Wol.Nav.AddBothTransitions(a[0],38,a[a.length-1]);
Ms.Wol.Nav.AddBothTransitions(a[a.length-1],40,a[0])
}
};
Ms.Wol.Nav.AddAllTabTransitions=function(a)
{
for(k=1;k<a.length;k++)
{
Ms.Wol.Nav.AddTransition(a[k],9,true,a[k-1]);
Ms.Wol.Nav.AddTransition(a[k-1],9,false,a[k])
}
};
Ms.Wol.Nav.InitMenuTitle=function(a)
{
var i="a.menuTitleLink,a.menuTitleLinkSelected",
h="div.menuGroupWrapper",
g="div.menuTitleUnderline",
e="menuTitleLinkSimHover",
f="menuTitleSimHover";
if(typeof a.menuTitleLink=="undefined")
{
a.originalStyle=a.className;
a.hoverStyle=f;
var c=jQuery(a).children(h),
b=jQuery(a).children(g);
if(c.length==1&&b.length==1)
{
a.menuGroupWrapper=c.get(0);
a.menuGroupWrapper.originalStyle=a.menuGroupWrapper.className;
a.menuGroupWrapper.hoverStyle=a.menuGroupWrapper.className+"SimHover";
a.menuTitleUnderline=b.get(0);
a.menuTitleUnderline.originalStyle=a.menuTitleUnderline.className;
a.menuTitleUnderline.hoverStyle=a.menuTitleUnderline.className+"SimHover"
}
var d=jQuery(a).children(i);
if(d.length==1)
{
a.menuTitleLink=d.get(0);
a.menuTitleLink.originalStyle=a.menuTitleLink.className;
a.menuTitleLink.hoverStyle=e
}
else
return false
}
return true
};
Ms.Wol.Nav.ApplyKeyboardStyles=function()
{
var a="div.navMenu span";
jQuery(a).each(function()
{
this.className=this.hoverStyle
})
};
Ms.Wol.Nav.RemoveKeyboardStyles=function(c)
{
var b="a.menuTitleLinkSimHover,div.menuGroupWrapperSimHover,div.menuTitleUnderlineSimHover,a.menuItemLinkSimHover,span.menuTitleSimHover",
a=jQuery(b);
c&&
a.blur();
a.each(function()
{
this.className=this.originalStyle
})
};
Ms.Wol.Nav.UpdateStyles=function(a,b)
{
if(a.menuTitle==b.menuTitle)
{
a.className=a.originalStyle;
Ms.Wol.Nav.DisplayMenuTitle(a.menuTitle);
b.className=b.hoverStyle
}
else
{
a.className=a.originalStyle;
Ms.Wol.Nav.HideMenuTitle(a.menuTitle);
Ms.Wol.Nav.DisplayMenuTitle(b.menuTitle);
b.className=b.hoverStyle
}
Ms.Wol.Nav.Status=1
};
Ms.Wol.Nav.FocusOnNext=function(a,b)
{
a.InMenuBlur=true;
jQuery(b).focus()
};
Ms.Wol.Nav.DisplayMenuTitle=function(a)
{
a.menuTitleLink.className=a.menuTitleLink.hoverStyle;
if(typeof a.menuGroupWrapper!="undefined")
{
a.menuGroupWrapper.className=a.menuGroupWrapper.hoverStyle;
a.menuTitleUnderline.className=a.menuTitleUnderline.hoverStyle
}
};
Ms.Wol.Nav.HideMenuTitle=function(a)
{
a.menuTitleLink.className=a.menuTitleLink.originalStyle;
if(typeof a.menuGroupWrapper!="undefined")
{
a.menuGroupWrapper.className=a.menuGroupWrapper.originalStyle;
a.menuTitleUnderline.className=a.menuTitleUnderline.originalStyle
}
};
if(typeof Wol=="undefined")
Wol={};
if(typeof Wol.Video=="undefined")
Wol.Video={};
function onPlayerCaptionsActivated(a)
{
if(typeof Wol!="undefined"&&typeof Wol.ContentInstrumentation!="undefined")
{
var b=Wol.Video.GetPlayerElementFromScriptableName(a);
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.ClosedCaption",1,b)
}
}
jQuery(function()
{
typeof Sys!="undefined"&&typeof Sys.WebForms!="undefined"&&typeof Sys.WebForms.PageRequestManager!="undefined"&&
Sys.WebForms.PageRequestManager.getInstance().add_pageLoaded(function()
{
Wol.Video.SetupVideoLogging()
});
Wol.Video.SetupVideoLogging()
});
Wol.Video.SetupVideoLogging=function()
{
if(typeof Wol!="undefined"&&typeof Wol.ContentInstrumentation!="undefined")
{
jQuery("video").each(function()
{
if(typeof this.hasInitialized=="undefined")
{
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.Tech","HTML5",this);
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.Start",false,this);
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.WatchTime",0,this);
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.CompletionRate",0,this);
this.hasInitialized=true;
jQuery(this).bind("canplay",function()
{
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.Length",this.duration,this);
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.Url",this.currentSrc,this)
}).bind("play",function()
{
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.Start",true,this)
}).bind("timeupdate",function()
{
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.WatchTime",this.currentTime,this);
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.CompletionRate",this.duration>0?Math.round(10*(this.currentTime/this.duration))*10:0,this)
}).bind("ended",function()
{
if(typeof this.hasLogged=="undefined")
{
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.WatchTime",this.duration,this);
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.CompletionRate",100,this);
Wol.ContentInstrumentation.Logging.LogCustomBI("Video",null,this);
this.hasLogged=true
}
})
}
});
jQuery("object.VideoPlayer").each(function()
{
if(typeof this.hasInitialized=="undefined"&&typeof this.playerObject!="undefined")
{
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.Tech","Silverlight",this);
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.Start",0,this);
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.WatchTime",0,this);
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.CompletionRate",0,this);
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.FullScreen",0,this);
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.ClosedCaption",0,this);
this.hasInitialized=true;
var a=this.playerObject;
a.addEventListener("FullScreenChanged",function(b)
{
var a=Wol.Video.GetPlayerElementFromScriptableName(b);
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.FullScreen",1,a)
});
a.addEventListener("PlayStateChanged",function(c,a)
{
if(typeof a!="undefined"&&a.Result=="Playing")
{
var b=Wol.Video.GetPlayerElementFromScriptableName(c);
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.Start",1,b)
}
});
a.addEventListener("MediaOpened",function(a)
{
var b=Wol.Video.GetPlayerElementFromScriptableName(a);
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.Length",a.EndPositionSeconds,b);
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.Url",Wol.Video.GetSilverlightPlayerVideoFilePath(a.Name),b)
});
a.addEventListener("MediaEnded",function(b)
{
var a=Wol.Video.GetPlayerElementFromScriptableName(b);
if(typeof a.hasLogged=="undefined")
{
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.WatchTime",b.EndPositionSeconds,a);
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.CompletionRate",100,a);
Wol.ContentInstrumentation.Logging.LogCustomBI("Video",null,a);
a.hasLogged=true
}
})
}
});
var a=function()
{
jQuery("video").each(function()
{
if(typeof this.hasLogged=="undefined")
{
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.WatchTime",this.currentTime,this);
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.CompletionRate",Wol.Video.GetRoundedPercentage(this.currentTime,this.duration),this);
Wol.ContentInstrumentation.Logging.LogCustomBI("Video",null,this);
this.hasLogged=true
}
});
jQuery("object.VideoPlayer").each(function()
{
if(typeof this.hasLogged=="undefined")
{
var a=this.playerObject;
if(a)
{
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.WatchTime",a.PlaybackPositionSeconds,this);
Wol.ContentInstrumentation.Logging.Annotate("MS.Video.CompletionRate",Wol.Video.GetRoundedPercentage(a.PlaybackPositionSeconds,a.EndPositionSeconds),this)
}
Wol.ContentInstrumentation.Logging.LogCustomBI("Video",null,this);
this.hasLogged=true
}
})
};
if(window.addEventListener)
window.addEventListener("beforeunload",a,true);
else
window.attachEvent&&
window.attachEvent("onbeforeunload",a)
}
};
Wol.Video.GetRoundedPercentage=function(b,a)
{
return a>0?Math.round(10*(b/a))*10:0
};
Wol.Video.GetPlayerElementFromScriptableName=function(b)
{
var a=b;
if(typeof a!="string")
a=a.Name;
var c=jQuery('object[data-scriptId="'+a+'"]');
return c.get(0)
};
Wol.Video.GetSilverlightPlayerVideoFilePath=function(a)
{
var b=$('object[data-scriptId="'+a+'"]').children("param[name=InitParams]").attr("value"),
c=b.split("mediaurl=")[1].split(",")[0];
return c
};
if(typeof Wol=="undefined")
Wol={};
if(typeof Wol.Video=="undefined")
Wol.Video={};
function onPlayerReady(a)
{
var b=jQuery('object[data-scriptId="'+a.Name+'"]');
if(b.length==1)
{
b.get(0).playerObject=a;
a.addEventListener("MediaOpened",function(b)
{
var c=Wol.Video.GetPlayerElementFromScriptableName(b),
a=jQuery(c);
if(a.is(".autoplay:visible"))
{
b.Play();
a.removeClass("autoplay")
}
})
}
Wol.Video.SetupVideoLogging()
}
jQuery(function()
{
typeof Sys!="undefined"&&typeof Sys.WebForms!="undefined"&&typeof Sys.WebForms.PageRequestManager!="undefined"&&
Sys.WebForms.PageRequestManager.getInstance().add_pageLoaded(function()
{
$("div.UnresolvedVideoContainer + script").each(function()
{
try
{
eval(this.innerHTML)
}
catch(err)
{
}
});
Wol.Video.SetupVideoAutoPlayAndStop()
});
Wol.Video.SetupVideoAutoPlayAndStop()
});
Wol.Video.SetupVideoAutoPlayAndStop=function()
{
jQuery("video.autoplay:visible").each(function()
{
this.play();
jQuery(this).removeClass("autoplay")
});
jQuery("video").bind("DOMAttrModified",function()
{
var a=jQuery(this);
if(a.is(":visible"))
{
if(a.is(".autoplay"))
{
this.play();
a.removeClass("autoplay")
}
}
else
this.pause()
});
jQuery("object.VideoPlayer").bind("DOMAttrModified",function()
{
var a=jQuery(this);
if(typeof this.playerObject!="undefined")
if(a.is(":visible"))
{
if(a.is(".autoplay"))
{
this.playerObject.Play();
a.removeClass("autoplay")
}
}
else
this.playerObject.Pause()
})
};
Wol.Video.Html5VideoSupported=function()
{
var a=document.createElement("video"),
b=a.canPlayType!=null&&a.canPlayType('video/mp4; codecs="avc1.42E01E, mp4a.40.2"');
return b=="probably"
};
Wol.Video.SilverlightVideoSupported=function(a,b)
{
jQuery.ajax({url:a,async:false,cache:true,dataType:"script"});
return Silverlight.isInstalled(b)
};
if(!window.CanvasHelper)
window.CanvasHelper={};
CanvasHelper.initCanvas=function(g,f,e,d,c)
{
var b=document.getElementById(g),
a=document.getElementById(f);
if(CanvasHelper.isCanvasSupported())
{
jQuery(b).show();
jQuery(a).hide();
e(d,c)
}
else
{
jQuery(a).show();
jQuery(b).hide()
}
};
CanvasHelper.isCanvasSupported=function()
{
var a=document.createElement("canvas");
return !!(a.getContext&&a.getContext("2d"))
};
if(typeof Ms=="undefined")
Ms={};
if(typeof Ms.Wol=="undefined")
Ms.Wol={};
if(typeof Ms.Wol.IFrame=="undefined")
Ms.Wol.IFrame={};
Ms.Wol.IFrame.AddFrameBorder=function()
{
jQuery("iframe.iframeNoBorder").attr("frameborder","0")
};
jQuery(document).ready(Ms.Wol.IFrame.AddFrameBorder);
if(typeof Hub=="undefined")
Hub={};
if(typeof Hub.PostProcessFunctionList=="undefined")
Hub.PostProcessFunctionList=[];
Hub.PostProcessData=function()
{
if(typeof Hub.PostProcessFunctionList=="object")
for(x in Hub.PostProcessFunctionList)
{
postProcessFunction=Hub.PostProcessFunctionList[x];
typeof postProcessFunction=="function"&&
postProcessFunction()
}
};
Hub.AddPostProcessFunction=function(a)
{
typeof a=="function"&&
Hub.PostProcessFunctionList.push(a)
};
var SearchBoxPostProcessEvent=function()
{
var a="div.HHSearchQuery";
jQuery(jQuery(a).get(0)).focus();
Ms.Wol.SearchBox.OnReadyFunction();
Ms.Wol.SearchBox.OnLoadFunction()
},
FeedbackControlPolicyPostProcessEvent=function()
{
typeof window!="undefined"&&typeof window.external!="undefined"&&typeof window.external.NoExplicitFeedback=="boolean"&&window.external.NoExplicitFeedback&&
jQuery("div").remove("#feedbackControlBody")
};
Hub.AddPostProcessFunction(SearchBoxPostProcessEvent);
Hub.AddPostProcessFunction(FeedbackControlPolicyPostProcessEvent)
