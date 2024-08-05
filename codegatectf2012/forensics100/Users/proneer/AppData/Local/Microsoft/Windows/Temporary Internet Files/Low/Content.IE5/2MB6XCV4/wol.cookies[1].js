
if(typeof Ms=="undefined")
Ms={};
if(typeof Ms.Wol=="undefined")
Ms.Wol={};
if(typeof Ms.Wol.Cookies=="undefined")
Ms.Wol.Cookies={};
Ms.Wol.Cookies=function()
{
var a={_bCookiesEnabled:null,_sCookieNs:"Ms.Wol.",_GetCookieEnabledState:function()
{
if(a._bCookiesEnabled==null)
{
document.cookie="cookiesEnabled=true;";
a._bCookiesEnabled=document.cookie.indexOf("cookiesEnabled=true")>-1?true:false
}
return a._bCookiesEnabled
},_UniquePageIdAvailable:function()
{
if(typeof PageData!="undefined"&&typeof PageData.TopLevelAssetSystemId!="undefined")
return true;
return false
},_GetUniquePageId:function()
{
if(typeof PageData!="undefined"&&typeof PageData.TopLevelAssetSystemId!="undefined")
return PageData.TopLevelAssetSystemId;
return null
},_BuildCookie:function(d,b)
{
var c=typeof b!="undefined"&&b?"":a._GetUniquePageId()+".";
return a._sCookieNs+c+d
},_GetCurrentDatePlusDays:function(b)
{
var a=new Date;
a.setDate(a.getDate()+b);
return a.toUTCString()
}};
return {SetCookie:function(e,d,c,b)
{
if(typeof b!="undefined"&&b||a._UniquePageIdAvailable()&&a._GetCookieEnabledState()&&e!=null&&d!=null)
{
var f=typeof c=="number"?"; expires="+a._GetCurrentDatePlusDays(c):"",
g="; path=/";
document.cookie=a._BuildCookie(e,b)+"="+escape(d)+f+g
}
},GetCookie:function(f,b)
{
if(typeof b!="undefined"&&b||a._UniquePageIdAvailable()&&a._GetCookieEnabledState()&&f!=null)
for(var g=a._BuildCookie(f,b),
e=document.cookie.split("; "),
h=/^([^=]*)=(.*)/i,
d=0;d<e.length;d++)
{
var c=h.exec(e[d]);
if(c&&g==c[1])
return unescape(c[2])
}
return null
},DeleteCookie:function(a)
{
this.SetCookie(a,"",-1,true)
},CookiesEnabled:function()
{
return a._GetCookieEnabledState()
},CookieStringNotNullEmpty:function(a)
{
if(typeof a!="undefined"&&a!=null&&a!="")
return true;
else
return false
}}
}()
