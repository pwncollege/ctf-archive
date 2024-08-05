/*
 * Copyright (C) 2008-2010 APN, LLC. All rights reserved
 *
 * References to external sources embedded in the code.
 * 
 * Author: Vishal V. Shah
 * Description: Toolbar about dialog.
 */
function ATB_onLoad(b){var a=(typeof window.opener.ATB=="undefined")?window.opener.opener.ATB:window.opener.ATB;var g=a.Logger;var i=a.Core;var d=a.Locale;var h=a.Prefs;var f=a.Utils;g.info("Loading about dialog.. Updating locale specific attributes...");d.updateLocaleSpecificAttributes(b,"asktb-about",document,f.getDocObjHostingATB());var e=document.getElementById("asktb-version-label");var c=e.getAttribute("value");c=h.replaceTbMacros(c);e.setAttribute("value",c);g.info("Done loading about dialog. Using versionsStr - ",c);return};