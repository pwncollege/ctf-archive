var loc = window.location.toString();
if (loc.indexOf("android.informer.com") >= 0)
{
    document.write("<script type='text/javascript' src='http://android.informer.com/js/cache/hilight_software_android.js?v="+(Math.random() * 10)+"'></script>");
}
else
{
    if (loc.indexOf("mac.informer.com") >= 0 )
        document.write("<script type='text/javascript' src='http://mac.informer.com/js/cache/hilight_software_mac.js?v="+(Math.random() * 10)+"'></script>");
    else
        document.write("<script type='text/javascript' src='http://software.informer.com/js/cache/hilight_software.js?v="+(Math.random() * 10)+"'></script>");
}

document.write("<script type='text/javascript' src='http://img.informer.com/js/connect.js'></script>");
/*document.write("<script type='text/javascript' src='http://img.informer.com/js/joinus.js'></script>");*/

var platform_re = /(software|linux|mac|android)\.informer\.com/
var matches = platform_re.exec(document.domain.toLowerCase())
var platform_domain = 'software'
if (matches != null)
	platform_domain = matches[1];

var btnDownload = document.getElementById("btndownload");
if (btnDownload && platform_domain == 'software')
//	btnDownload.innerHTML = '<a class="w_e" href="http://software.informer.com/go/go2.php" target="_blank">Before you Download:<br />Run a <span>FREE</span> scan for Windows Errors</a>' + btnDownload.innerHTML;
	btnDownload.innerHTML = '<a class="w_e" href="http://software.informer.com/go/go2.php" target="_blank"><b>Before you Continue</b>:<br />Run a <span>FREE</span> scan for Outdated Drivers</a>' + btnDownload.innerHTML;

//add banner for mac users
if(typeof(window['hide_mac_banner']) == 'undefined'){
    hide_mac_banner = false
}
var platform = navigator.platform
//platform = 'mac';
if(platform.substr(0, 3).toLowerCase() == "mac" && !hide_mac_banner)
{
    var section_a = document.getElementById("section_a")
    if(!section_a){
        // for <table> based design
        var mainTable = document.getElementById("main")
        var bannerRow = document.createElement("tr")
        bannerRow.appendChild(document.createElement("td"))
        var banner_col = document.createElement("td")
        banner_col.innerHTML = "<a href=\"http://mac.informer.com/get_client.php\" " +
                "title=\"Download Mac Informer Client!\">" +
                "<img src=\"http://img.informer.com/images/mac_banner_alpha.png\" border=\"0\" " +
                "alt=\"Download Mac Informer Client\"/></a>"
        banner_col.style.padding = "20px 0px 0px 0px"
        bannerRow.appendChild(banner_col)
        var dummy = document.createElement("td")
        dummy.innerHTML = "<img src=\"http://hits.informer.com/log.php?id=359&r=" + Math.round(100000 * Math.random()) + "\" style = \"display: none\" />"
        bannerRow.appendChild(dummy)
        var body = mainTable.getElementsByTagName("tbody")[0]
        rows = new Array()
        for(var i = 0; i < body.childNodes.length; i++){
            var node = body.childNodes[i]
            if(node.nodeType == 1)
                rows.push(node)
        }
        if(window.location.pathname.substring(1)=='register.html')
            body.insertBefore(bannerRow, rows[1])
        else
            body.insertBefore(bannerRow, rows[2])
    } else {
        // for <div> based design
        var a_children = section_a.childNodes;
        var divs = new Array()
        for (var i = 0; i < a_children.length; i++){
            var node = a_children[i]
            if(node.nodeType == 1)
                divs.push(node)
        }
        var container = document.createElement("div")
		if (loc.indexOf("mac.informer.com") >= 0 )
		{
        	container.style.margin = "163px 0px 0px 7px";
		} else {
			//container.style.margin = "85px 0px 0px 7px";
			container.style.margin = "10px 0 0 7px";
		}
        var banner = document.createElement("div")
        banner.innerHTML = "<a href=\"http://mac.informer.com/get_client.php\" " +
                "title=\"Download Mac Informer Client!\">" +
                "<img src=\"http://img.informer.com/images/mac_banner_alpha.png\" border=\"0\" " +
                "alt=\"Download Mac Informer Client\"/></a>"
        var dummy = document.createElement("div")
        dummy.innerHTML = "<img src=\"http://hits.informer.com/log.php?id=359&r=" + Math.round(100000 * Math.random()) + "\" style = \"display: none\" />"
        dummy.style.display = "none"
        container.appendChild(banner)
        container.appendChild(dummy)
        var body = divs[0]
        var body_children =  body.childNodes
        var i = body_children.length
        body.insertBefore(container, body_children[0])
        for(var i = 1; i < body_children.length; i++){
           var node = body_children[i]
           if(node.nodeType == 1)
                node.style.margin = "0px 0px 0px 0px"
        }

	//if (document.getElementById("for_askform"))
	//	document.getElementById("for_askform").style.margin = '0';
    }
}

// merry xmass
/*
var imgs = document.getElementsByTagName('img'); 
for(i=0; i<imgs.length; i++)
{
	if (imgs[i].src.indexOf('logo_si.png')>-1)
	{
		if (loc.indexOf("mac.informer.com") >= 0 )
		{
		//	imgs[i].src = 'http://img.informer.com/images/mac/logo_mac_ny.png';
		//	imgs[i].style.width = '422px';
		//	imgs[i].style.height = '83px';
		}
		else
		{
			//imgs[i].style.position = 'absolute'
			//imgs[i].style.top = '5px';

			imgs[i].src = 'http://img.informer.com/images/logo_si_ny3.png';
			//imgs[i].style.width = '325px';
			//imgs[i].style.height = '68px';
		}
	}
}
*/

